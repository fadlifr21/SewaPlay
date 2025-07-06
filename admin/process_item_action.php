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
            $nama_item = trim($_POST['nama_item'] ?? '');
            $harga_satuan = filter_var($_POST['harga_satuan'] ?? '', FILTER_VALIDATE_FLOAT); // Validasi float
            $satuan_item = trim($_POST['satuan_item'] ?? '');
            $status_ketersediaan = $_POST['status_ketersediaan'] ?? 'Tersedia';

            $errors = [];

            if (empty($nama_item)) {
                $errors[] = "Nama item tidak boleh kosong.";
            }
            if ($harga_satuan === false || $harga_satuan < 0) { // Cek jika bukan angka atau negatif
                $errors[] = "Harga satuan tidak valid.";
            }
            if (empty($satuan_item)) {
                $errors[] = "Satuan item tidak boleh kosong.";
            }

            // Cek duplikasi nama item
            $stmt_check = $conn->prepare("SELECT id_item FROM ItemTambahan WHERE nama_item = ?");
            $stmt_check->bind_param("s", $nama_item);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Nama item sudah ada.";
            }
            $stmt_check->close();

            if (empty($errors)) {
                $stmt_insert = $conn->prepare("INSERT INTO ItemTambahan (nama_item, harga_satuan, satuan_item, status_ketersediaan) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("sdss", $nama_item, $harga_satuan, $satuan_item, $status_ketersediaan); // 'd' untuk double/decimal

                if ($stmt_insert->execute()) {
                    $_SESSION['admin_message'] = "Item '{$nama_item}' berhasil ditambahkan.";
                    $_SESSION['admin_message_type'] = 'success';
                } else {
                    $_SESSION['admin_message'] = "Gagal menambahkan item: " . $stmt_insert->error;
                    $_SESSION['admin_message_type'] = 'error';
                }
                $stmt_insert->close();
            } else {
                $_SESSION['admin_message'] = "Gagal menambahkan item: " . implode("<br>", $errors);
                $_SESSION['admin_message_type'] = 'error';
            }
            break;

        case 'update_status':
            $id_item = $_POST['id_item'] ?? null;
            $new_status = $_POST['new_status'] ?? null;

            if (!$id_item || !in_array($new_status, ['Tersedia', 'Tidak Tersedia'])) {
                $_SESSION['admin_message'] = 'Data tidak valid untuk update status item.';
                $_SESSION['admin_message_type'] = 'error';
                break;
            }

            $stmt = $conn->prepare("UPDATE ItemTambahan SET status_ketersediaan = ? WHERE id_item = ?");
            $stmt->bind_param("si", $new_status, $id_item);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Status item #{$id_item} berhasil diupdate menjadi '{$new_status}'.";
                $_SESSION['admin_message_type'] = 'success';
            } else {
                $_SESSION['admin_message'] = "Gagal mengupdate status item: " . $stmt->error;
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
    header('Location: items.php');
    exit;
} else {
    header('Location: items.php');
    exit;
}
?>