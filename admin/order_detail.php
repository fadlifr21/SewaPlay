<?php
session_start();
require_once '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit;
}

$order_data = null;
$detail_items = [];

// Ambil data pesanan utama
$stmt_order = $conn->prepare("
    SELECT
        p.id_pemesanan,
        py.nama_lengkap,
        py.nomor_hp,
        py.email,
        py.alamat,
        py.nomor_ktp,
        p.tgl_mulai_sewa,
        p.tgl_selesai_sewa,
        p.status_pemesanan,
        p.metode_pengantaran,
        p.catatan,
        p.total_harga,
        p.timestamp_pesanan
    FROM pemesanan p
    JOIN penyewa py ON p.id_penyewa = py.id_penyewa
    WHERE p.id_pemesanan = ?
");
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
if ($result_order->num_rows === 1) {
    $order_data = $result_order->fetch_assoc();
}
$stmt_order->close();

// Ambil detail pesanan
if ($order_data) {
    $stmt_detail = $conn->prepare("
        SELECT
            dp.id_detail,
            dp.jumlah,
            dp.harga_per_item,
            dp.subtotal,
            pr.nama_perangkat,
            pr.tipe_perangkat,
            pr.deskripsi AS perangkat_deskripsi,
            ps.nama_paket,
            ps.deskripsi AS paket_deskripsi,
            ps.satuan_harga AS paket_satuan,
            it.nama_item,
            it.satuan_item AS item_satuan,
            g.judul_game,
            g.harga_sewa_per_hari AS game_harga_sewa
        FROM detailPemesanan dp
        LEFT JOIN perangkat pr ON dp.id_perangkat = pr.id_perangkat
        LEFT JOIN paketSewa ps ON dp.id_paket = ps.id_paket
        LEFT JOIN ItemTambahan it ON dp.id_item = it.id_item
        LEFT JOIN game g ON dp.id_game = g.id_game
        WHERE dp.id_pemesanan = ?
    ");
    $stmt_detail->bind_param("i", $order_id);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    while ($row = $result_detail->fetch_assoc()) {
        $detail_items[] = $row;
    }
    $stmt_detail->close();
}

$conn->close();

if (!$order_data) {
    $_SESSION['admin_message'] = 'Pesanan tidak ditemukan.';
    $_SESSION['admin_message_type'] = 'error';
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo htmlspecialchars($order_id); ?> - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="admin-navbar">
        <div class="logo">Admin.Sewa.Play</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="perangkat.php">Perangkat</a>
            <a href="items.php">Item Tambahan</a>
            <a href="pakets.php">Paket Sewa</a>
            <a href="games.php">Game</a>
            <a href="../admin_logout.php">Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a>
        </div>
    </nav>

    <section class="detail-section">
        <h2>Detail Pesanan #<?php echo htmlspecialchars($order_data['id_pemesanan']); ?></h2>

        <?php if (!empty($_SESSION['admin_message'])): ?>
            <p class="message-<?php echo $_SESSION['admin_message_type']; ?>"><?php echo htmlspecialchars($_SESSION['admin_message']); ?></p>
            <?php
            unset($_SESSION['admin_message']);
            unset($_SESSION['admin_message_type']);
            ?>
        <?php endif; ?>

        <div class="detail-group">
            <h3>Informasi Pesanan</h3>
            <div class="detail-row"><span class="label">Status:</span> <span class="value status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($order_data['status_pemesanan'])); ?>"><?php echo htmlspecialchars($order_data['status_pemesanan']); ?></span></div>
            <div class="detail-row"><span class="label">Tanggal Pesan:</span> <span class="value"><?php echo date('d M Y H:i', strtotime($order_data['timestamp_pesanan'])); ?></span></div>
            <div class="detail-row"><span class="label">Periode Sewa:</span> <span class="value"><?php echo date('d M Y', strtotime($order_data['tgl_mulai_sewa'])); ?> s/d <?php echo date('d M Y', strtotime($order_data['tgl_selesai_sewa'])); ?></span></div>
            <div class="detail-row"><span class="label">Metode Antar:</span> <span class="value"><?php echo htmlspecialchars($order_data['metode_pengantaran']); ?></span></div>
            <div class="detail-row"><span class="label">Total Harga:</span> <span class="value">Rp <?php echo number_format($order_data['total_harga'], 0, ',', '.'); ?></span></div>
            <div class="detail-row"><span class="label">Catatan:</span> <span class="value"><?php echo nl2br(htmlspecialchars($order_data['catatan'])); ?></span></div>
        </div>

        <div class="detail-group">
            <h3>Detail Penyewa</h3>
            <div class="detail-row"><span class="label">Nama Lengkap:</span> <span class="value"><?php echo htmlspecialchars($order_data['nama_lengkap']); ?></span></div>
            <div class="detail-row"><span class="label">Email:</span> <span class="value"><?php echo htmlspecialchars($order_data['email']); ?></span></div>
            <div class="detail-row"><span class="label">Nomor HP:</span> <span class="value"><?php echo htmlspecialchars($order_data['nomor_hp']); ?></span></div>
            <div class="detail-row"><span class="label">Alamat:</span> <span class="value"><?php echo htmlspecialchars($order_data['alamat']); ?></span></div>
            <div class="detail-row"><span class="label">Nomor KTP:</span> <span class="value"><?php echo htmlspecialchars($order_data['nomor_ktp']); ?></span></div>
        </div>

        <div class="detail-group">
            <h3>Item yang Disewa</h3>
            <?php if (!empty($detail_items)): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Nama</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail_items as $item): ?>
                            <tr>
                                <td>
                                    <?php
                                        if (isset($item['id_paket']) && $item['id_paket'] !== null) {
                                            echo "Paket";
                                        } elseif (isset($item['id_perangkat']) && $item['id_perangkat'] !== null) {
                                            echo "Perangkat";
                                        } elseif (isset($item['id_item']) && $item['id_item'] !== null) {
                                            echo "Item Tambahan";
                                        } elseif (isset($item['id_game']) && $item['id_game'] !== null) {
                                            echo "Game Fisik";
                                        } else {
                                            echo "-";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (isset($item['nama_paket']) && $item['nama_paket'] !== null) {
                                            echo htmlspecialchars($item['nama_paket']);
                                        } elseif (isset($item['nama_perangkat']) && $item['nama_perangkat'] !== null) {
                                            echo htmlspecialchars($item['nama_perangkat']);
                                        } elseif (isset($item['nama_item']) && $item['nama_item'] !== null) {
                                            echo htmlspecialchars($item['nama_item']);
                                        } elseif (isset($item['judul_game']) && $item['judul_game'] !== null) {
                                            echo htmlspecialchars($item['judul_game']);
                                        } else {
                                            echo "-";
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['jumlah']); ?></td>
                                <td>
                                    Rp <?php
                                    if (isset($item['harga_per_item'])) { // 'harga_per_item' should always exist now
                                        echo number_format($item['harga_per_item'], 0, ',', '.');
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada detail item untuk pesanan ini.</p>
            <?php endif; ?>
        </div>

        <div class="action-buttons" style="justify-content: flex-start;">
            <a href="dashboard.php" class="btn" style="background-color: #6c757d;">Kembali ke Dashboard</a>
            <?php if ($order_data['status_pemesanan'] === 'Pending' || $order_data['status_pemesanan'] === 'Menunggu Pembayaran'): ?>
                <form action="process_order_action.php" method="POST" class="action-form">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_data['id_pemesanan']); ?>">
                    <input type="hidden" name="action" value="confirm">
                    <button type="submit" class="btn-confirm">Konfirmasi Pesanan</button>
                </form>
            <?php endif; ?>
            <?php if ($order_data['status_pemesanan'] === 'Dikonfirmasi'): ?>
                <form action="process_order_action.php" method="POST" class="action-form">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_data['id_pemesanan']); ?>">
                    <input type="hidden" name="action" value="complete">
                    <button type="submit" class="btn-complete">Selesaikan Pesanan</button>
                </form>
            <?php endif; ?>
            <?php if ($order_data['status_pemesanan'] !== 'Selesai' && $order_data['status_pemesanan'] !== 'Dibatalkan'): ?>
                 <form action="process_order_action.php" method="POST" class="action-form">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_data['id_pemesanan']); ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="btn-cancel">Batalkan Pesanan</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <footer class="admin-site-footer">
        <div class="footer-bottom admin-footer">
            <p>&copy; 2025 Sewa.Play Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>