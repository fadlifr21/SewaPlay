<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

$admin_username = $_SESSION['admin_username'];

$sql = "SELECT
            p.id_pemesanan,
            py.nama_lengkap,
            py.nomor_hp,
            py.email,
            p.tgl_mulai_sewa,
            p.tgl_selesai_sewa,
            p.status_pemesanan,
            p.metode_pengantaran,
            p.total_harga,
            p.timestamp_pesanan
        FROM pemesanan p
        JOIN penyewa py ON p.id_penyewa = py.id_penyewa
        ORDER BY p.timestamp_pesanan DESC";
$result_pemesanan = $conn->query($sql);
$pemesanans = [];
if ($result_pemesanan->num_rows > 0) {
    while ($row = $result_pemesanan->fetch_assoc()) {
        $pemesanans[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sewa PlayStation</title>
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
    
    <section class="admin-dashboard-section">
        <h2>Daftar Pesanan</h2>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Penyewa</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pemesanans)): ?>
                    <?php foreach ($pemesanans as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id_pemesanan']); ?></td>
                            <td><?php echo htmlspecialchars($order['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($order['nomor_hp']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo htmlspecialchars($order['tgl_mulai_sewa']); ?></td>
                            <td><?php echo htmlspecialchars($order['tgl_selesai_sewa']); ?></td>
                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                            <td><span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($order['status_pemesanan'])); ?>">
                                <?php echo htmlspecialchars($order['status_pemesanan']); ?>
                            </span></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($order['status_pemesanan'] === 'Pending' || $order['status_pemesanan'] === 'Menunggu Pembayaran'): ?>
                                        <form action="process_order_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id_pemesanan']); ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn-confirm">Konfirmasi</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($order['status_pemesanan'] === 'Dikonfirmasi'): ?>
                                        <form action="process_order_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id_pemesanan']); ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="btn-complete">Selesai</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($order['status_pemesanan'] !== 'Selesai' && $order['status_pemesanan'] !== 'Dibatalkan'): ?>
                                         <form action="process_order_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id_pemesanan']); ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn-cancel">Batalkan</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="order_detail.php?id=<?php echo htmlspecialchars($order['id_pemesanan']); ?>" class="btn" style="background-color:#007bff;">Detail</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">Belum ada pesanan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <footer class="admin-site-footer">
        <div class="footer-bottom admin-footer">
            <p>&copy; 2025 Sewa.Play Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>