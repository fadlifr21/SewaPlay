<?php
session_start();
require_once '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['admin_login_error'] = 'Anda harus login sebagai admin.';
    header('Location: ../admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'confirm', 'complete', 'cancel'

    if (!$order_id || !$action) {
        $_SESSION['admin_message'] = 'Aksi tidak valid.';
        $_SESSION['admin_message_type'] = 'error';
        header('Location: dashboard.php');
        exit;
    }

    $conn->begin_transaction();
    try {
        $update_successful = false;
        $message = '';
        $message_type = '';
        $status_to_set = '';

        // Ambil ID Perangkat dan ID Game yang terkait dengan pesanan
        // Kumpulkan semua ID yang mungkin perlu diubah statusnya
        $stmt_get_related_ids = $conn->prepare("SELECT id_perangkat, id_game FROM detailPemesanan WHERE id_pemesanan = ?");
        $stmt_get_related_ids->bind_param("i", $order_id);
        $stmt_get_related_ids->execute();
        $result_related_ids = $stmt_get_related_ids->get_result();
        $related_perangkat_ids = [];
        $related_game_ids = [];
        while($row = $result_related_ids->fetch_assoc()) {
            if ($row['id_perangkat'] !== null) {
                $related_perangkat_ids[] = $row['id_perangkat'];
            }
            if ($row['id_game'] !== null) {
                $related_game_ids[] = $row['id_game'];
            }
        }
        $stmt_get_related_ids->close();


        switch ($action) {
            case 'confirm':
                $status_to_set = 'Dikonfirmasi';
                $stmt = $conn->prepare("UPDATE pemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?");
                $stmt->bind_param("si", $status_to_set, $order_id);
                if ($stmt->execute()) {
                    $update_successful = true;
                    $message = "Pesanan #{$order_id} berhasil dikonfirmasi.";
                    $message_type = 'success';
                }
                break;

            case 'complete':
                $status_to_set = 'Selesai';
                $stmt = $conn->prepare("UPDATE pemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?");
                $stmt->bind_param("si", $status_to_set, $order_id);
                if ($stmt->execute()) {
                    $update_successful = true;
                    $message = "Pesanan #{$order_id} berhasil diselesaikan.";
                    $message_type = 'success';

                    // Ubah status perangkat terkait menjadi 'Tersedia'
                    if (!empty($related_perangkat_ids)) {
                        // Gunakan IN clause untuk update banyak ID sekaligus, lebih efisien
                        $placeholders = implode(',', array_fill(0, count($related_perangkat_ids), '?'));
                        $types = str_repeat('i', count($related_perangkat_ids)); // Semua adalah integer
                        $stmt_update_perangkat = $conn->prepare("UPDATE perangkat SET status_ketersediaan = 'Tersedia' WHERE id_perangkat IN ({$placeholders})");
                        $stmt_update_perangkat->bind_param($types, ...$related_perangkat_ids);
                        if (!$stmt_update_perangkat->execute()) {
                            throw new Exception("Gagal mengupdate status perangkat.");
                        }
                        $stmt_update_perangkat->close();
                    }

                    // Ubah status game terkait menjadi 'Tersedia'
                    if (!empty($related_game_ids)) {
                        $placeholders = implode(',', array_fill(0, count($related_game_ids), '?'));
                        $types = str_repeat('i', count($related_game_ids));
                        $stmt_update_game = $conn->prepare("UPDATE game SET status_ketersediaan = 'Tersedia' WHERE id_game IN ({$placeholders})");
                        $stmt_update_game->bind_param($types, ...$related_game_ids);
                        if (!$stmt_update_game->execute()) {
                            throw new Exception("Gagal mengupdate status game.");
                        }
                        $stmt_update_game->close();
                    }
                }
                break;

            case 'cancel':
                $status_to_set = 'Dibatalkan';
                $stmt = $conn->prepare("UPDATE pemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?");
                $stmt->bind_param("si", $status_to_set, $order_id);
                if ($stmt->execute()) {
                    $update_successful = true;
                    $message = "Pesanan #{$order_id} berhasil dibatalkan.";
                    $message_type = 'success';

                    // Ubah status perangkat terkait menjadi 'Tersedia' jika dibatalkan
                    if (!empty($related_perangkat_ids)) {
                        $placeholders = implode(',', array_fill(0, count($related_perangkat_ids), '?'));
                        $types = str_repeat('i', count($related_perangkat_ids));
                        $stmt_update_perangkat = $conn->prepare("UPDATE perangkat SET status_ketersediaan = 'Tersedia' WHERE id_perangkat IN ({$placeholders})");
                        $stmt_update_perangkat->bind_param($types, ...$related_perangkat_ids);
                        if (!$stmt_update_perangkat->execute()) {
                            throw new Exception("Gagal mengupdate status perangkat setelah pembatalan.");
                        }
                        $stmt_update_perangkat->close();
                    }

                    // Ubah status game terkait menjadi 'Tersedia' jika dibatalkan
                    if (!empty($related_game_ids)) {
                        $placeholders = implode(',', array_fill(0, count($related_game_ids), '?'));
                        $types = str_repeat('i', count($related_game_ids));
                        $stmt_update_game = $conn->prepare("UPDATE game SET status_ketersediaan = 'Tersedia' WHERE id_game IN ({$placeholders})");
                        $stmt_update_game->bind_param($types, ...$related_game_ids);
                        if (!$stmt_update_game->execute()) {
                            throw new Exception("Gagal mengupdate status game setelah pembatalan.");
                        }
                        $stmt_update_game->close();
                    }
                }
                break;

            default:
                throw new Exception("Aksi tidak dikenal.");
        }

        if ($update_successful) {
            $conn->commit();
            $_SESSION['admin_message'] = $message;
            $_SESSION['admin_message_type'] = $message_type;
        } else {
            throw new Exception("Gagal memperbarui status pesanan.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['admin_message'] = "Error: " . $e->getMessage();
        $_SESSION['admin_message_type'] = 'error';
    }

    $conn->close();
    header('Location: dashboard.php');
    exit;

} else {
    header('Location: dashboard.php');
    exit;
}
?>