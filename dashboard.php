<?php
session_start();

// Jika pengguna belum login, arahkan kembali ke halaman login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Pengguna'; // Ambil nama_lengkap dari sesi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sewa PlayStation Pekanbaru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .dashboard-section {
            padding: 80px 40px;
            text-align: center;
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .dashboard-section h2 {
            color: #003893;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        .dashboard-section p {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 30px;
        }
        .logout-btn {
            background-color: #e60023; /* Warna merah untuk logout */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 1.1em;
            box-shadow: 0 6px 20px rgba(230, 0, 35, 0.3);
        }
        .logout-btn:hover {
            background-color: #cc001f;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(230, 0, 35, 0.45);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Sewa.Play</div>
        <div class="nav-links">
            <a href="index.php#beranda">Beranda</a>
            <a href="index.php#perangkat">Perangkat</a>
            <a href="index.php#harga">Harga</a>
            <a href="index.php#games">Game Populer</a>
            <a href="index.php#cara">Cara Sewa</a>
            <a href="index.php#kontak">Kontak</a>
            <a href="order.php">Pesan Sekarang</a> <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <section class="dashboard-section">
        <h2>Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</h2>
        <p>Anda berhasil login. Ini adalah halaman dashboard Anda.</p>
        <p>ID Penyewa Anda: <?php echo htmlspecialchars($_SESSION['id_penyewa']); ?></p>
        <p>Email Anda: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        <a href="logout.php" class="logout-btn">Logout</a>
    </section>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3>Sewa.Play</h3>
                <p>Kami menyediakan penyewaan PlayStation dengan paket lengkap dan harga terjangkau. Rasakan pengalaman bermain terbaik tanpa ribet.</p>
            </div>

            <div class="footer-links">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="index.html#beranda">Beranda</a></li>
                    <li><a href="index.html#perangkat">Perangkat</a></li>
                    <li><a href="index.html#harga">Harga</a></li>
                    <li><a href="index.html#games">Game</a></li>
                    <li><a href="index.html#cara">Cara Sewa</a></li>
                    <li><a href="index.html#kontak">Kontak</a></li>
                </ul>
            </div>

            <div class="footer-social">
                <h4>Ikuti Kami</h4>
                <div class="social-icons">
                    <a href="https://instagram.com/sewa.ps.riau" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/62812XXXXXXX" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="mailto:sewa.playstation@gmail.com"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 Sewa.Play. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>