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
            $nama_perangkat = trim($_POST['nama_perangkat'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $tipe_perangkat = $_POST['tipe_perangkat'] ?? '';
            $status_ketersediaan = $_POST['status_ketersediaan'] ?? 'Tersedia'; // Default Tersedia

            $errors = [];

            if (empty($nama_perangkat)) {
                $errors[] = "Nama perangkat tidak boleh kosong.";
            }
            if (empty($tipe_perangkat) || !in_array($tipe_perangkat, ['PS4', 'PS5', 'Monitor', 'VR'])) {
                $errors[] = "Tipe perangkat tidak valid.";
            }

            // Cek duplikasi nama perangkat
            $stmt_check = $conn->prepare("SELECT id_perangkat FROM perangkat WHERE nama_perangkat = ?");
            $stmt_check->bind_param("s", $nama_perangkat);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Nama perangkat sudah ada.";
            }
            $stmt_check->close();

            if (empty($errors)) {
                $stmt_insert = $conn->prepare("INSERT INTO perangkat (nama_perangkat, deskripsi, tipe_perangkat, status_ketersediaan) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nama_perangkat, $deskripsi, $tipe_perangkat, $status_ketersediaan);

                if ($stmt_insert->execute()) {
                    $_SESSION['admin_message'] = "Perangkat '{$nama_perangkat}' berhasil ditambahkan.";
                    $_SESSION['admin_message_type'] = 'success';
                } else {
                    $_SESSION['admin_message'] = "Gagal menambahkan perangkat: " . $stmt_insert->error;
                    $_SESSION['admin_message_type'] = 'error';
                }
                $stmt_insert->close();
            } else {
                $_SESSION['admin_message'] = "Gagal menambahkan perangkat: " . implode("<br>", $errors);
                $_SESSION['admin_message_type'] = 'error';
            }
            break;

        case 'update_status':
            $id_perangkat = $_POST['id_perangkat'] ?? null;
            $new_status = $_POST['new_status'] ?? null;

            if (!$id_perangkat || !in_array($new_status, ['Tersedia', 'Disewa', 'Rusak'])) {
                $_SESSION['admin_message'] = 'Data tidak valid untuk update status perangkat.';
                $_SESSION['admin_message_type'] = 'error';
                break; // Keluar dari switch
            }

            $stmt = $conn->prepare("UPDATE perangkat SET status_ketersediaan = ? WHERE id_perangkat = ?");
            $stmt->bind_param("si", $new_status, $id_perangkat);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Status perangkat #{$id_perangkat} berhasil diupdate menjadi '{$new_status}'.";
                $_SESSION['admin_message_type'] = 'success';
            } else {
                $_SESSION['admin_message'] = "Gagal mengupdate status perangkat: " . $stmt->error;
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
    header('Location: perangkat.php');
    exit;
} else {
    header('Location: perangkat.php');
    exit;
}
?>