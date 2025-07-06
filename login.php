<?php
session_start();

$error_message = '';

if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
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
    <title>Login - Sewa PlayStation Pekanbaru</title>
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
            <a href="index.php#kontak">Kontak</a>
            <a href="login.php">Login</a>
            <a href="register.php">Daftar</a>
        </div>
    </nav>

    <section class="login-section">
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <?php if (!empty($error_message)): ?>
                <p class="message-error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form class="login-form" method="POST" action="process_login.php">
                <div class="input-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username or email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn login-btn">Login</button>
                <div class="form-footer">
                    <a href="#" class="forgot-password">Forgot Password?</a>
                    <span>Don't have an account? <a href="register.php" class="signup-link">Sign Up</a></span>
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