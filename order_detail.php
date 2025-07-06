<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id_pemesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id_penyewa_session = $_SESSION['id_penyewa'] ?? 0;

if (!$id_pemesanan) {
    die("Error: ID Pesanan tidak valid.");
}

$stmt_main = $conn->prepare(
    "SELECT p.*, pen.nama_lengkap, pen.email, pen.nomor_hp, pen.alamat
     FROM pemesanan p
     JOIN penyewa pen ON p.id_penyewa = pen.id_penyewa
     WHERE p.id_pemesanan = ?"
);
$stmt_main->bind_param("i", $id_pemesanan);
$stmt_main->execute();
$result_main = $stmt_main->get_result();
$order = $result_main->fetch_assoc();
$stmt_main->close();

if (!$order || $order['id_penyewa'] != $id_penyewa_session) {
    die("Akses ditolak. Anda tidak memiliki izin untuk melihat pesanan ini.");
}

$stmt_details = $conn->prepare(
    "SELECT dp.*, per.nama_perangkat, pak.nama_paket, item.nama_item, game.judul_game
     FROM detailpemesanan dp
     LEFT JOIN perangkat per ON dp.id_perangkat = per.id_perangkat
     LEFT JOIN paketsewa pak ON dp.id_paket = pak.id_paket
     LEFT JOIN itemtambahan item ON dp.id_item = item.id_item
     LEFT JOIN game game ON dp.id_game = game.id_game
     WHERE dp.id_pemesanan = ?"
);
$stmt_details->bind_param("i", $id_pemesanan);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
$order_details = $result_details->fetch_all(MYSQLI_ASSOC);
$stmt_details->close();
$conn->close();

$nama_lengkap = $_SESSION['nama_lengkap'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo htmlspecialchars($order['id_pemesanan']); ?> - Sewa.Play</title>
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

    <main class="order-detail-section">
        <div class="order-detail-container">
            <div class="detail-header">
                <h2>Detail Pesanan #<?php echo htmlspecialchars($order['id_pemesanan']); ?></h2>
                <span class="status-badge status-<?php echo str_replace(' ', '', $order['status_pemesanan']); ?>">
                    <?php echo htmlspecialchars($order['status_pemesanan']); ?>
                </span>
            </div>

            <div class="detail-card">
                <h3><i class="fas fa-info-circle"></i> Ringkasan Pesanan</h3>
                <div class="detail-grid">
                    <div class="detail-item"><strong>Tanggal Pesan: </strong><span><?php echo date('d F Y, H:i', strtotime($order['timestamp_pesanan'])); ?></span></div>
                    <div class="detail-item"><strong>Metode Pengantaran: </strong><span><?php echo htmlspecialchars($order['metode_pengantaran']); ?></span></div>
                    <div class="detail-item"><strong>Tanggal Mulai Sewa: </strong><span><?php echo date('d F Y', strtotime($order['tgl_mulai_sewa'])); ?></span></div>
                    <div class="detail-item"><strong>Tanggal Selesai Sewa: </strong><span><?php echo date('d F Y', strtotime($order['tgl_selesai_sewa'])); ?></span></div>
                </div>
            </div>

            <div class="detail-card">
                <h3><i class="fas fa-box-open"></i> Rincian Item</h3>
                <div class="table-responsive">
                    <table class="items-detail-table">
                        <thead>
                            <tr>
                                <th>Deskripsi Item</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_details as $detail): ?>
                                <tr>
                                    <td>
                                        <?php
                                        if (!empty($detail['nama_perangkat'])) {
                                            echo '<b>' . htmlspecialchars($detail['nama_perangkat']) . '</b>';
                                            if (!empty($detail['nama_paket'])) {
                                                echo ' <small>(' . htmlspecialchars($detail['nama_paket']) . ')</small>';
                                            }
                                        } elseif (!empty($detail['nama_item'])) {
                                            echo 'Item Tambahan: <b>' . htmlspecialchars($detail['nama_item']) . '</b>';
                                        } elseif (!empty($detail['judul_game'])) {
                                            echo 'Sewa Game: <b>' . htmlspecialchars($detail['judul_game']) . '</b>';
                                        } else {
                                            echo 'Item tidak dikenal';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($detail['jumlah']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($detail['harga_per_item'], 0, ',', '.'); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="detail-grid-footer">
                <div class="detail-card">
                    <h3><i class="fas fa-user-circle"></i> Detail Penyewa</h3>
                    <div class="detail-item"><strong>Nama:</strong><span><?php echo htmlspecialchars($order['nama_lengkap']); ?></span></div>
                    <div class="detail-item"><strong>Nomor HP:</strong><span><?php echo htmlspecialchars($order['nomor_hp']); ?></span></div>
                    <div class="detail-item full-width"><strong>Alamat Pengantaran:</strong><span class="alamat"><?php echo nl2br(htmlspecialchars($order['alamat'])); ?></span></div>
                     <?php if (!empty($order['catatan'])): ?>
                        <div class="detail-item full-width"><strong>Catatan Tambahan:</strong><span class="alamat"><?php echo nl2br(htmlspecialchars($order['catatan'])); ?></span></div>
                    <?php endif; ?>
                </div>
                <div class="detail-card total-summary">
                    <h3><i class="fas fa-dollar-sign"></i> Total Pembayaran</h3>
                    <div class="total-amount">
                        <span>Total Harga</span>
                        <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                    </div>
                    <p class="payment-info">Silakan tunggu konfirmasi dari admin kami melalui WhatsApp untuk detail pembayaran.</p>
                </div>
            </div>
             <a href="akun.php" class="btn-back-to-account">‹ Kembali ke Riwayat Pesanan</a>
        </div>
    </main>

    <footer class="site-footer">
        </footer>
</body>
</html>