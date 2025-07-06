<?php
session_start();

// Inisialisasi variabel untuk pesan error
$error_message = '';

// Periksa apakah ada pesan error dari process_admin_login.php
if (isset($_SESSION['admin_login_error'])) {
    $error_message = $_SESSION['admin_login_error'];
    unset($_SESSION['admin_login_error']); // Hapus pesan error setelah ditampilkan
}

// Jika admin sudah login, arahkan ke dashboard admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sewa PlayStation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <section class="admin-login-section">
        <div class="admin-login-container">
            <h2>Admin Login</h2>
            <?php if (!empty($error_message)): ?>
                <p class="message-error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form class="admin-login-form" method="POST" action="process_admin_login.php">
                <div class="input-group">
                    <label for="admin_username">Username</label>
                    <input type="text" id="admin_username" name="username" placeholder="Masukkan username admin" required>
                </div>
                <div class="input-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="password" placeholder="Masukkan password admin" required>
                </div>
                <button type="submit" class="btn login-btn">Login sebagai Admin</button>
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
</body>
</html>