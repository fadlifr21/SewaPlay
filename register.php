<?php
session_start();

$message = '';
$message_type = '';

if (isset($_SESSION['register_message'])) {
    $message = $_SESSION['register_message'];
    $message_type = $_SESSION['register_message_type'];
    unset($_SESSION['register_message']);
    unset($_SESSION['register_message_type']);
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Sewa PlayStation Pekanbaru</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
            <a href="login.php">Login</a>
            <a href="register.php">Daftar</a>
        </div>
    </nav>

    <section class="register-section">
        <div class="register-container">
            <h2>Daftar Akun Baru</h2>
            <?php if (!empty($message)): ?>
                <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <form class="register-form" method="POST" action="process_register.php">
                <div class="input-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="input-group">
                    <label for="nomor_hp">Nomor Telepon (WhatsApp)</label>
                    <input type="text" id="nomor_hp" name="nomor_hp" placeholder="Contoh: 081234567890" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Buat password (min. 6 karakter)" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
                </div>
                 <div class="input-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" placeholder="Masukkan alamat lengkap Anda" rows="3" required></textarea>
                </div>
                <div class="input-group">
                    <label for="nomor_ktp">Nomor KTP</label>
                    <input type="text" id="nomor_ktp" name="nomor_ktp" placeholder="Masukkan nomor KTP Anda" required>
                </div>
                <button type="submit" class="btn register-btn">Daftar Sekarang</button>
                <div class="form-footer">
                    <span>Sudah punya akun? <a href="login.php">Login di sini</a></span>
                </div>
            </form>
        </div>
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
                    <li><a href="index.php#beranda">Beranda</a></li>
                    <li><a href="index.php#perangkat">Perangkat</a></li>
                    <li><a href="index.php#harga">Harga</a></li>
                    <li><a href="index.php#games">Game</a></li>
                    <li><a href="index.php#cara">Cara Sewa</a></li>
                    <li><a href="index.php#kontak">Kontak</a></li>
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
    <script src="script.js"></script>
</body>
</html>