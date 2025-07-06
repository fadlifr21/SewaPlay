<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['admin_login_error'] = 'Anda harus login sebagai admin.';
    header('Location: ../admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $nama_paket = trim($_POST['nama_paket'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $harga_ps4 = filter_var($_POST['harga_ps4'] ?? '', FILTER_VALIDATE_FLOAT); // Bisa null
            $harga_ps5 = filter_var($_POST['harga_ps5'] ?? '', FILTER_VALIDATE_FLOAT); // Bisa null
            $harga_spesifik = filter_var($_POST['harga_spesifik'] ?? '', FILTER_VALIDATE_FLOAT); // Bisa null
            $satuan_harga = trim($_POST['satuan_harga'] ?? '');
            $status_paket = $_POST['status_paket'] ?? 'Aktif';

            $errors = [];

            if (empty($nama_paket)) {
                $errors[] = "Nama paket tidak boleh kosong.";
            }
            if (empty($satuan_harga)) {
                $errors[] = "Satuan harga tidak boleh kosong.";
            }

            // Validasi harga (setidaknya salah satu harga PS4/PS5/Spesifik harus valid jika diisi)
            $is_price_valid = false;
            if ($harga_ps4 !== false && $harga_ps4 >= 0) $is_price_valid = true;
            if ($harga_ps5 !== false && $harga_ps5 >= 0) $is_price_valid = true;
            if ($harga_spesifik !== false && $harga_spesifik >= 0) $is_price_valid = true;

            // Jika semua harga opsional kosong DAN tidak ada harga spesifik, anggap error
            if ($harga_ps4 === false && $harga_ps5 === false && $harga_spesifik === false) {
                 $errors[] = "Setidaknya satu harga (PS4, PS5, atau Spesifik) harus diisi dengan nilai yang valid.";
            }


            // Cek duplikasi nama paket
            $stmt_check = $conn->prepare("SELECT id_paket FROM paketSewa WHERE nama_paket = ?");
            $stmt_check->bind_param("s", $nama_paket);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Nama paket sudah ada.";
            }
            $stmt_check->close();

            if (empty($errors)) {
                // Konversi harga false menjadi null agar bisa disimpan di kolom DECIMAL NULLable
                $harga_ps4_db = ($harga_ps4 === false) ? null : $harga_ps4;
                $harga_ps5_db = ($harga_ps5 === false) ? null : $harga_ps5;
                $harga_spesifik_db = ($harga_spesifik === false) ? null : $harga_spesifik;

                $stmt_insert = $conn->prepare("INSERT INTO paketSewa (nama_paket, deskripsi, harga_ps4, harga_ps5, harga_spesifik, satuan_harga, status_paket) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("ssdddss", $nama_paket, $deskripsi, $harga_ps4_db, $harga_ps5_db, $harga_spesifik_db, $satuan_harga, $status_paket); // 'd' untuk decimal/double, 's' untuk string

                if ($stmt_insert->execute()) {
                    $_SESSION['admin_message'] = "Paket '{$nama_paket}' berhasil ditambahkan.";
                    $_SESSION['admin_message_type'] = 'success';
                } else {
                    $_SESSION['admin_message'] = "Gagal menambahkan paket: " . $stmt_insert->error;
                    $_SESSION['admin_message_type'] = 'error';
                }
                $stmt_insert->close();
            } else {
                $_SESSION['admin_message'] = "Gagal menambahkan paket: " . implode("<br>", $errors);
                $_SESSION['admin_message_type'] = 'error';
            }
            break;

        case 'update_status':
            $id_paket = $_POST['id_paket'] ?? null;
            $new_status = $_POST['new_status'] ?? null;

            if (!$id_paket || !in_array($new_status, ['Aktif', 'Tidak Aktif'])) {
                $_SESSION['admin_message'] = 'Data tidak valid untuk update status paket.';
                $_SESSION['admin_message_type'] = 'error';
                break;
            }

            $stmt = $conn->prepare("UPDATE paketSewa SET status_paket = ? WHERE id_paket = ?");
            $stmt->bind_param("si", $new_status, $id_paket);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Status paket #{$id_paket} berhasil diupdate menjadi '{$new_status}'.";
                $_SESSION['admin_message_type'] = 'success';
            } else {
                $_SESSION['admin_message'] = "Gagal mengupdate status paket: " . $stmt->error;
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
    header('Location: pakets.php');
    exit;
} else {
    header('Location: pakets.php');
    exit;
}
?>