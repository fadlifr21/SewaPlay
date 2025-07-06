<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id_penyewa = $_SESSION['id_penyewa'] ?? 0; 
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '-';
$nomor_hp = $_SESSION['nomor_hp'] ?? '-';
$alamat = $_SESSION['alamat'] ?? '-';
$nomor_ktp = $_SESSION['nomor_ktp'] ?? '-';

$riwayat_pesanan = [];
if ($id_penyewa > 0) {
    $stmt = $conn->prepare("SELECT id_pemesanan, tgl_mulai_sewa, tgl_selesai_sewa, total_harga, status_pemesanan FROM pemesanan WHERE id_penyewa = ? ORDER BY timestamp_pesanan DESC");
    $stmt->bind_param("i", $id_penyewa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $riwayat_pesanan = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}
$is_logged_in = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Sewa.Play</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Sewa.Play</a>
        <div class="nav-links">
            <a href="index.php#perangkat">Perangkat</a>
            <a href="index.php#harga">Harga</a>
            <a href="index.php#games">Game Populer</a>
            <a href="index.php#cara">Cara Sewa</a>
            <a href="order.php">Pesan Sekarang</a>
            <a href="akun.php" class="user-icon" title="Akun Saya: <?php echo htmlspecialchars($nama_lengkap); ?>"><i class="fas fa-user-circle"></i></a>
            <a href="logout.php" class="logout-icon" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <main class="account-section">
        <div class="account-container">
            <h2 class="account-title">Akun Saya</h2>
            
            <div class="account-card profile-details">
                <h3><i class="fas fa-id-card"></i> Detail Profil</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Nama Lengkap</strong>
                        <span><?php echo htmlspecialchars($nama_lengkap); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Email</strong>
                        <span><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Nomor HP</strong>
                        <span><?php echo htmlspecialchars($nomor_hp); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Nomor KTP</strong>
                        <span><?php echo htmlspecialchars($nomor_ktp); ?></span>
                    </div>
                    <div class="detail-item full-width">
                        <strong>Alamat</strong>
                        <span class="alamat"><?php echo nl2br(htmlspecialchars($alamat)); ?></span>
                    </div>
                </div>
            </div>

            <div class="account-card order-history">
                <h3><i class="fas fa-history"></i> Riwayat Pesanan</h3>
                <div class="table-responsive">
                    <table class="order-history-table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal Sewa</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($riwayat_pesanan)): ?>
                                <?php foreach ($riwayat_pesanan as $pesanan): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($pesanan['id_pemesanan']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($pesanan['tgl_mulai_sewa'])) . ' - ' . date('d M Y', strtotime($pesanan['tgl_selesai_sewa'])); ?></td>
                                        <td>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo str_replace(' ', '', $pesanan['status_pemesanan']); ?>">
                                                <?php echo htmlspecialchars($pesanan['status_pemesanan']); ?>
                                            </span>
                                        </td>
                                        <td><a href="order_detail.php?id=<?php echo $pesanan['id_pemesanan']; ?>" class="btn-view-detail">Lihat Detail</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-orders-message">
                                        <i class="fas fa-box-open"></i>
                                        <p>Anda belum memiliki riwayat pesanan.</p>
                                        <a href="order.php" class="btn-order-now">Buat Pesanan Sekarang!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand"><h3>Sewa.Play</h3><p>Kami menyediakan penyewaan PlayStation dengan paket lengkap dan harga terjangkau.</p></div>
            <div class="footer-links"><h4>Navigasi</h4><ul><li><a href="index.php">Beranda</a></li><li><a href="order.php">Pesan</a></li><li><a href="#kontak">Kontak</a></li></ul></div>
            <div class="footer-social"><h4>Ikuti Kami</h4><div class="social-icons"><a href="#" target="_blank"><i class="fab fa-instagram"></i></a><a href="#" target="_blank"><i class="fab fa-whatsapp"></i></a></div></div>
        </div>
        <div class="footer-bottom"><p>© 2025 Sewa.Play. All rights reserved.</p></div>
    </footer>
</body>
</html>