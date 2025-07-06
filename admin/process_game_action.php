<?php
session_start();
require_once '../config/db.php'; // Pastikan path ini benar dari dalam folder 'admin'

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['admin_login_error'] = 'Anda harus login sebagai admin.';
    header('Location: ../admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $judul_game = trim($_POST['judul_game'] ?? '');
            $harga_sewa_per_hari = filter_var($_POST['harga_sewa_per_hari'] ?? '', FILTER_VALIDATE_FLOAT);
            $path_gambar = trim($_POST['path_gambar'] ?? '');
            $status_ketersediaan = $_POST['status_ketersediaan'] ?? 'Tersedia';

            $errors = [];

            // Validasi input
            if (empty($judul_game)) {
                $errors[] = "Judul game tidak boleh kosong.";
            }
            // Filter_var akan mengembalikan false jika input bukan float/angka, jadi cek itu
            if ($harga_sewa_per_hari === false || $harga_sewa_per_hari < 0) {
                $errors[] = "Harga sewa per hari tidak valid. Masukkan angka positif.";
            }
            if (empty($status_ketersediaan) || !in_array($status_ketersediaan, ['Tersedia', 'Disewa', 'Tidak Tersedia'])) {
                $errors[] = "Status ketersediaan game tidak valid.";
            }

            // Cek duplikasi judul game
            $stmt_check = $conn->prepare("SELECT id_game FROM game WHERE judul_game = ?");
            $stmt_check->bind_param("s", $judul_game);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Judul game sudah ada.";
            }
            $stmt_check->close();

            // Jika tidak ada error validasi
            if (empty($errors)) {
                // Siapkan path_gambar untuk database: jika kosong, simpan sebagai NULL
                $path_gambar_for_db = empty($path_gambar) ? null : $path_gambar;

                $stmt_insert = $conn->prepare("INSERT INTO game (judul_game, path_gambar, harga_sewa_per_hari, status_ketersediaan) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssds", $judul_game, $path_gambar_for_db, $harga_sewa_per_hari, $status_ketersediaan); // 'd' untuk decimal/float

                if ($stmt_insert->execute()) {
                    $_SESSION['admin_message'] = "Game '{$judul_game}' berhasil ditambahkan.";
                    $_SESSION['admin_message_type'] = 'success';
                } else {
                    $_SESSION['admin_message'] = "Gagal menambahkan game: " . $stmt_insert->error;
                    $_SESSION['admin_message_type'] = 'error';
                }
                $stmt_insert->close();
            } else {
                $_SESSION['admin_message'] = "Gagal menambahkan game: " . implode("<br>", $errors);
                $_SESSION['admin_message_type'] = 'error';
            }
            break;

        case 'update_status':
            $id_game = $_POST['id_game'] ?? null;
            $new_status = $_POST['new_status'] ?? null;

            // Validasi ID dan status
            if (!$id_game || !in_array($new_status, ['Tersedia', 'Disewa', 'Tidak Tersedia'])) {
                $_SESSION['admin_message'] = 'Data tidak valid untuk update status game.';
                $_SESSION['admin_message_type'] = 'error';
                break; // Keluar dari switch
            }

            $stmt = $conn->prepare("UPDATE game SET status_ketersediaan = ? WHERE id_game = ?");
            $stmt->bind_param("si", $new_status, $id_game); // 's' for string, 'i' for integer

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Status game #{$id_game} berhasil diupdate menjadi '{$new_status}'.";
                $_SESSION['admin_message_type'] = 'success';
            } else {
                $_SESSION['admin_message'] = "Gagal mengupdate status game: " . $stmt->error;
                $_SESSION['admin_message_type'] = 'error';
            }
            $stmt->close();
            break;

        default:
            $_SESSION['admin_message'] = 'Aksi tidak dikenal.';
            $_SESSION['admin_message_type'] = 'error';
            break;
    }

    $conn->close();
    header('Location: games.php'); // Redirect kembali ke halaman manajemen game
    exit;
} else {
    // Jika diakses langsung tanpa POST request
    header('Location: games.php');
    exit;
}
?>