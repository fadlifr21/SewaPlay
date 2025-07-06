<?php
session_start();
require_once 'config/db.php'; // Pastikan file ini berisi koneksi database $conn

// Fungsi untuk membersihkan dan memvalidasi input
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = 'Anda harus login untuk membuat pesanan.';
    header('Location: index.php');
    exit;
}

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_penyewa = $_SESSION['id_penyewa'];

    // Ambil dan validasi data dari form
    $tgl_mulai_sewa = validate_input($_POST['tgl_mulai_sewa']);
    $tgl_selesai_sewa = validate_input($_POST['tgl_selesai_sewa']);
    $id_paket = validate_input($_POST['id_paket']);
    $id_perangkat = isset($_POST['id_perangkat']) && $_POST['id_perangkat'] !== '' ? validate_input($_POST['id_perangkat']) : null; // id_perangkat bisa NULL
    $metode_pengantaran = validate_input($_POST['metode_pengantaran']);
    $catatan = isset($_POST['catatan']) ? validate_input($_POST['catatan']) : null; // catatan bisa NULL

    // Validasi tanggal
    if (empty($tgl_mulai_sewa) || empty($tgl_selesai_sewa)) {
        $_SESSION['order_message'] = 'Tanggal mulai dan tanggal selesai sewa harus diisi.';
        $_SESSION['order_message_type'] = 'error';
        header('Location: order.php');
        exit;
    }

    $start_date = new DateTime($tgl_mulai_sewa);
    $end_date = new DateTime($tgl_selesai_sewa);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set time to 00:00:00 for accurate date comparison

    if ($start_date < $today) {
        $_SESSION['order_message'] = 'Tanggal mulai sewa tidak boleh di masa lalu.';
        $_SESSION['order_message_type'] = 'error';
        header('Location: order.php');
        exit;
    }

    if ($end_date < $start_date) {
        $_SESSION['order_message'] = 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai sewa.';
        $_SESSION['order_message_type'] = 'error';
        header('Location: order.php');
        exit;
    }

    // Hitung durasi sewa dalam hari (tetap dihitung untuk kalkulasi harga)
    $interval = $start_date->diff($end_date);
    $durasi_sewa_hari = $interval->days + 1; // +1 untuk menghitung hari terakhir juga

    $total_biaya_sewa = 0; // Inisialisasi total biaya pesanan

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Masukkan data pesanan ke tabel 'pemesanan'
        // Kolom 'total_harga' akan diupdate setelah semua detail dihitung
        // Kolom 'timestamp_pesanan' akan menggunakan DEFAULT CURRENT_TIMESTAMP() dari DB
        $stmt_insert_order = $conn->prepare("INSERT INTO pemesanan (id_penyewa, tgl_mulai_sewa, tgl_selesai_sewa, status_pemesanan, metode_pengantaran, catatan, total_harga) VALUES (?, ?, ?, 'Pending', ?, ?, ?)");
        $stmt_insert_order->bind_param("issssd", $id_penyewa, $tgl_mulai_sewa, $tgl_selesai_sewa, $metode_pengantaran, $catatan, $total_biaya_sewa);
        $stmt_insert_order->execute();
        $id_pemesanan_baru = $conn->insert_id; // Ambil ID pesanan yang baru saja dibuat
        $stmt_insert_order->close();

        // 2. Dapatkan detail paket untuk menghitung total harga awal dan masukkan ke detailpemesanan
        $stmt_paket = $conn->prepare("SELECT harga_ps4, harga_ps5, harga_spesifik, satuan_harga FROM paketSewa WHERE id_paket = ?");
        $stmt_paket->bind_param("i", $id_paket);
        $stmt_paket->execute();
        $result_paket = $stmt_paket->get_result();
        $paket_detail = $result_paket->fetch_assoc();
        $stmt_paket->close();

        if (!$paket_detail) {
            throw new Exception('Paket sewa tidak ditemukan.');
        }

        $harga_paket_per_satuan = 0;
        // Tentukan harga paket berdasarkan tipe perangkat jika berlaku
        if ($id_perangkat) {
            $stmt_perangkat_tipe = $conn->prepare("SELECT tipe_perangkat FROM perangkat WHERE id_perangkat = ?");
            $stmt_perangkat_tipe->bind_param("i", $id_perangkat);
            $stmt_perangkat_tipe->execute();
            $result_perangkat_tipe = $stmt_perangkat_tipe->get_result();
            $perangkat_tipe_row = $result_perangkat_tipe->fetch_assoc();
            $stmt_perangkat_tipe->close();

            if ($perangkat_tipe_row) {
                if ($perangkat_tipe_row['tipe_perangkat'] == 'PS4' && isset($paket_detail['harga_ps4'])) {
                    $harga_paket_per_satuan = $paket_detail['harga_ps4'];
                } elseif ($perangkat_tipe_row['tipe_perangkat'] == 'PS5' && isset($paket_detail['harga_ps5'])) {
                    $harga_paket_per_satuan = $paket_detail['harga_ps5'];
                } else {
                    // Jika paket tidak spesifik PS4/PS5 atau perangkat bukan PS4/PS5, gunakan harga_spesifik
                    $harga_paket_per_satuan = $paket_detail['harga_spesifik'];
                }
            }
        } else {
            // Jika tidak ada perangkat yang dipilih, gunakan harga_spesifik paket
            $harga_paket_per_satuan = $paket_detail['harga_spesifik'];
        }

        $subtotal_paket = $harga_paket_per_satuan;
        if ($paket_detail['satuan_harga'] === 'per hari') {
            $subtotal_paket = $harga_paket_per_satuan * $durasi_sewa_hari;
        }

        // Masukkan detail paket ke tabel 'detailpemesanan'
        $stmt_insert_detail_paket = $conn->prepare("INSERT INTO detailpemesanan (id_pemesanan, id_paket, id_perangkat, jumlah, harga_per_item, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $jumlah_paket = 1; // Selalu 1 untuk paket
        $stmt_insert_detail_paket->bind_param("iiiidd", $id_pemesanan_baru, $id_paket, $id_perangkat, $jumlah_paket, $harga_paket_per_satuan, $subtotal_paket);
        $stmt_insert_detail_paket->execute();
        $stmt_insert_detail_paket->close();
        $total_biaya_sewa += $subtotal_paket;

        // 3. Masukkan item tambahan ke tabel 'detailpemesanan' (jika ada)
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $stmt_insert_detail_item = $conn->prepare("INSERT INTO detailpemesanan (id_pemesanan, id_item, jumlah, harga_per_item, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['items'] as $index => $itemId) {
                $jumlah = isset($_POST['jumlah_items'][$index]) ? intval($_POST['jumlah_items'][$index]) : 0;
                if ($itemId && $jumlah > 0) {
                    $stmt_item_harga = $conn->prepare("SELECT harga_satuan FROM ItemTambahan WHERE id_item = ?");
                    $stmt_item_harga->bind_param("i", $itemId);
                    $stmt_item_harga->execute();
                    $result_item_harga = $stmt_item_harga->get_result();
                    $item_harga_row = $result_item_harga->fetch_assoc();
                    $stmt_item_harga->close();
                    $harga_satuan_item = $item_harga_row['harga_satuan'] ?? 0;
                    $subtotal_item = $harga_satuan_item * $jumlah;

                    $stmt_insert_detail_item->bind_param("iiidd", $id_pemesanan_baru, $itemId, $jumlah, $harga_satuan_item, $subtotal_item);
                    $stmt_insert_detail_item->execute();
                    $total_biaya_sewa += $subtotal_item;
                }
            }
            $stmt_insert_detail_item->close();
        }

        // 4. Masukkan game fisik ke tabel 'detailpemesanan' (jika ada)
        if (isset($_POST['games']) && is_array($_POST['games'])) {
            $stmt_insert_detail_game = $conn->prepare("INSERT INTO detailpemesanan (id_pemesanan, id_game, jumlah, harga_per_item, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['games'] as $index => $gameId) {
                $jumlah_game = isset($_POST['jumlah_games'][$index]) ? intval($_POST['jumlah_games'][$index]) : 0;
                if ($gameId && $jumlah_game > 0) {
                    $stmt_game_harga = $conn->prepare("SELECT harga_sewa_per_hari FROM game WHERE id_game = ?");
                    $stmt_game_harga->bind_param("i", $gameId);
                    $stmt_game_harga->execute();
                    $result_game_harga = $stmt_game_harga->get_result();
                    $game_harga_row = $result_game_harga->fetch_assoc();
                    $stmt_game_harga->close();
                    $harga_sewa_per_hari_game = $game_harga_row['harga_sewa_per_hari'] ?? 0;
                    // Subtotal game dihitung harga per hari * jumlah game * durasi sewa
                    $subtotal_game = $harga_sewa_per_hari_game * $jumlah_game * $durasi_sewa_hari;

                    $stmt_insert_detail_game->bind_param("iiidd", $id_pemesanan_baru, $gameId, $jumlah_game, $harga_sewa_per_hari_game, $subtotal_game);
                    $stmt_insert_detail_game->execute();
                    $total_biaya_sewa += $subtotal_game;
                }
            }
            $stmt_insert_detail_game->close();
        }

        // 5. Update total_harga di tabel 'pemesanan'
        $stmt_update_total = $conn->prepare("UPDATE pemesanan SET total_harga = ? WHERE id_pemesanan = ?");
        $stmt_update_total->bind_param("di", $total_biaya_sewa, $id_pemesanan_baru);
        $stmt_update_total->execute();
        $stmt_update_total->close();

        $conn->commit();
        $_SESSION['last_order_total'] = $total_biaya_sewa;
        $_SESSION['order_message'] = 'Pesanan berhasil dibuat! Kami akan segera menghubungi Anda untuk konfirmasi.';
        $_SESSION['order_message_type'] = 'success';
        header('Location: order.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['order_message'] = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
        $_SESSION['order_message_type'] = 'error';
        header('Location: order.php');
        exit;
    }

} else {
    // Jika bukan metode POST, redirect kembali
    header('Location: order.php');
    exit;
}

$conn->close();
?>