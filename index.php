<?php
session_start();
require_once 'config/db.php';

$login_error_message = '';
if (isset($_SESSION['login_error'])) {
    $login_error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$register_message = '';
$register_message_type = '';
if (isset($_SESSION['register_message'])) {
    $register_message = $_SESSION['register_message'];
    $register_message_type = $_SESSION['register_message_type'];
    unset($_SESSION['register_message']);
    unset($_SESSION['register_message_type']);
}

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$nama_lengkap = $is_logged_in ? ($_SESSION['nama_lengkap'] ?? 'Pengguna') : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa PlayStation Pekanbaru</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Sewa.Play</a>
        <div class="nav-links">
            <a href="#perangkat">Perangkat</a>
            <a href="#harga">Harga</a>
            <a href="#games">Game Populer</a>
            <a href="#cara">Cara Sewa</a>
            <a href="#kontak">Kontak</a>
            <?php if (!$is_logged_in): ?>
                <a href="login.php">Login</a>
                <a href="register.php">Daftar</a>
            <?php else: ?>
                <a href="order.php">Pesan Sekarang</a>
                <a href="akun.php" class="user-icon" title="Akun Saya: <?php echo htmlspecialchars($nama_lengkap); ?>"><i class="fas fa-user-circle"></i></a>
                <a href="logout.php" class="logout-icon" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero" id="beranda">
        <h2 style="color: white;">Sewa PS4 & PS5 <br>Mudah & Murah</h2>
        <p class="pberanda">Lengkap dengan game populer, stik tambahan, dan bisa delivery! Cocok untuk main bareng teman atau acara spesial kamu.</p>
        <?php if ($is_logged_in): ?>
    <a href="order.php" class="btn">Pesan Sekarang</a>
<?php else: ?>
    <a href="#login" class="btn" title="Anda harus login terlebih dahulu">Pesan Sekarang</a>
<?php endif; ?>
    </section>
    <section class="cards-section"  id="perangkat">
        <h2>Perangkat Tersedia</h2>
            <div class="card-wrapper">
                <div class="card card-left" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-content">
                        <span class="premium-tag">PREMIUM</span>
                        <h2>PlayStation 4 Pro</h2>
                        <p>Termasuk 2 stik + 20 game populer. Support HDMI & Internet.</p>
                    </div>
                </div>
                <div class="card card-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-content">
                        <span class="premium-tag">PREMIUM</span>
                        <h2>PlayStation 5 Pro</h2>
                        <p>Termasuk 2 stik dan Game generasi terbaru. Cocok untuk pengalaman gaming yang maksimal.</p>
                    </div>
                </div>

                <div class="card card-right" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-content">
                        <span class="extra-tag">EXTRA</span>
                        <h2>Monitor 24"</h2>
                        <p>Full HD, dapat disewakan bersama atau terpisah dari perangkat.</p>
                    </div>
                </div>
        <div class="card card-comingsoon" data-aos="fade-up" data-aos-delay="400">
            <div class="card-content">
                <span class="comingsoon-tag">COMING SOON</span>
                <h2>Paket VR Experience</h2>
                <p>Nikmati dunia virtual dengan PlayStation VR. Tersedia segera!</p>
            </div>
        </div>
        </div>
    </section>

    <section class="section" id="harga">
    <h2>Paket & Harga</h2>
    <div class="price-cards-container">
        <div class="price-card" data-aos="zoom-in" data-aos-delay="50">
            <h3 class="price-title">Paket Harian</h3>
            <div class="price-detail">
                <p><strong>PS4:</strong> Rp 70.000</p>
                <p><strong>PS5:</strong> Rp 100.000</p>
            </div>
        </div>
        <div class="price-card highlight" data-aos="zoom-in" data-aos-delay="100">
            <h3 class="price-title">Paket Mingguan</h3>
            <div class="price-detail">
                <p><strong>PS4:</strong> Rp 400.000</p>
                <p><strong>PS5:</strong> Rp 600.000</p>
            </div>
            <div class="best-tag">TERPOPULER</div>
        </div>
        <div class="price-card" data-aos="zoom-in" data-aos-delay="150">
            <h3 class="price-title">Paket 3 Hari</h3>
            <div class="price-detail">
                <p><strong>PS4:</strong> Rp 180.000</p>
                <p><strong>PS5:</strong> Rp 250.000</p>
            </div>
        </div>
        <div class="price-card highlight" data-aos="zoom-in" data-aos-delay="200">
            <h3 class="price-title">Paket Acara</h3>
            <div class="price-detail">
                <p>Include proyektor & screen besar.</p>
                <p><strong>Mulai Rp 150.000 / acara</strong></p>
            </div>
            <div class="best-tag">TERPOPULER</div>
        </div>
        <div class="price-card" data-aos="zoom-in" data-aos-delay="250">
            <h3 class="price-title">Sewa Game Fisik</h3>
            <div class="price-detail">
                <p>Mulai <strong>Rp 10.000/hari</strong> per judul.<br>Tersedia banyak genre populer.</p>
            </div>
        </div>
        <div class="price-card" data-aos="zoom-in" data-aos-delay="300">
            <h3 class="price-title">+ Stik Tambahan</h3>
            <div class="price-detail">
                <p>Tambah 1 stik lagi untuk sesi multiplayer lebih seru!</p>
                <p><strong>Rp 15.000 / hari</strong></p>
            </div>
        </div>
        <div class="price-card" data-aos="zoom-in" data-aos-delay="350">
            <h3 class="price-title">+ Monitor 24”</h3>
            <div class="price-detail">
                <p>Full HD monitor untuk kamu yang tidak punya TV/layar di lokasi sewa.</p>
                <p><strong>Rp 30.000 / hari</strong></p>
            </div>
        </div>
        <div class="price-card comingsoon-card" data-aos="zoom-in" data-aos-delay="400">
    <h3 class="price-title">Paket VR Experience</h3>
    <div class="price-detail">
        <p>Rasakan pengalaman bermain di dunia virtual.<br><em>Akan segera tersedia!</em></p>
    </div>
    <div class="comingsoon-overlay">COMING SOON</div>
</div>
</section>
    <section class="game-section" id="games">
        <h2 class="game-section-title">Beberapa Game Kami</h2>
        <div class="game-grid">
            <div class="game-card" data-aos="fade-up">
                <img src="img/astro.jpg" alt="ASTRO BOT">
                <div class="game-title">ASTRO BOT</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/dragonball.jpg" alt="Dragon Ball: Sparking! Zero">
                <div class="game-title">Dragon Ball: Sparking! Zero</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/ittake.jpg" alt="It Takes Two">
                <div class="game-title">It Takes Two</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/hells.jpg" alt="Helldivers 2">
                <div class="game-title">Helldivers 2</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/godofwar.jpg" alt="God Of War: Ragnarok">
                <div class="game-title">God Of War: Ragnarok</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/clair.png" alt="Clair Obscur: Expedition 33">
                <div class="game-title">Clair Obscur: Expedition 33</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/mhwilds.png" alt="Monster Hunter Wilds<">
                <div class="game-title">Monster Hunter Wilds</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/split.jpg" alt="Split Fiction">
                <div class="game-title">Split Fiction</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/Forza.jpg" alt="Forza Horizon 5">
                <div class="game-title">Forza Horizon 5</div>
            </div>
            <div class="game-card" data-aos="fade-up">
                <img src="img/tekken.jpg" alt="Tekken 8">
                <div class="game-title">Tekken 8</div>
            </div>

    </section>
    <hr style="border: 8px solid gold;">
    <section class="section" id="cara">
    <h2>Cara Penyewaan</h2>

    <div class="multi-step-indicator">
        <div class="progress-line"><div class="progress-fill"></div></div>

        <div class="step-item" data-step-id="1">
            <div class="icon-wrapper"><i class="fas fa-user-check"></i></div>
            <div class="step-title">Login / Daftar</div>
        </div>
        <div class="step-item" data-step-id="2">
            <div class="icon-wrapper"><i class="fas fa-shopping-cart"></i></div>
            <div class="step-title">Buat Pesanan</div>
        </div>
        <div class="step-item" data-step-id="3">
            <div class="icon-wrapper"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="step-title">Konfirmasi & Bayar</div>
        </div>
        <div class="step-item" data-step-id="4">
            <div class="icon-wrapper"><i class="fas fa-truck-loading"></i></div>
            <div class="step-title">Pengantaran / Ambil</div>
        </div>
    </div>

    <div class="step-details-container">
        <div class="step-detail-content" id="detail-1">
            <h3>Login atau Daftar Akun</h3>
            <p>Untuk memulai, Anda perlu <a href="login.php">login ke akun</a> yang sudah ada atau <a href="register.php">daftar akun baru</a>. Proses ini cepat dan mudah!</p>
        </div>
        <div class="step-detail-content" id="detail-2">
            <h3>Buat Pesanan Penyewaan</h3>
            <p>Setelah login, kunjungi halaman <a href="order.php">pemesanan</a>. Pilih paket sewa, perangkat, item tambahan, dan game fisik yang Anda inginkan, lalu tentukan tanggal sewa.</p>
        </div>
        <div class="step-detail-content" id="detail-3">
            <h3>Konfirmasi Pesanan & Pembayaran</h3>
            <p>Tim kami akan meninjau pesanan Anda dan segera menghubungi Anda untuk konfirmasi ketersediaan serta detail pembayaran (DP/Lunas). Pastikan nomor HP Anda aktif!</p>
        </div>
        <div class="step-detail-content" id="detail-4">
            <h3>Pengantaran atau Ambil Sendiri</h3>
            <p>Setelah pembayaran dikonfirmasi, perangkat dan game akan diantar ke lokasi Anda atau Anda bisa mengambilnya langsung sesuai kesepakatan. Selamat bermain!</p>
        </div>
    </div>
    </section>
    <section class="contact-section" id="kontak">
        <h2>Hubungi Kami</h2>
        <p class="contact-subtitle">Punya pertanyaan atau butuh bantuan? Jangan ragu hubungi kami melalui platform di bawah ini.</p>
        <div class="contact-wrapper">
            <div class="contact-card" data-aos="fade-right">
                <i class="fab fa-whatsapp"></i>
                <h3>WhatsApp</h3>
                <p>+62 896 2030 7581</p>
                <a href="https://wa.me/6289620307581" target="_blank" class="contact-btn">Chat Sekarang</a>
            </div>
            <div class="contact-card" data-aos="fade-up" data-aos-delay="100">
                <i class="fab fa-instagram"></i>
                <h3>Instagram</h3>
                <p>@sewa.ps.riau</p>
                <a href="https://instagram.com/sewa.ps.riau" target="_blank" class="contact-btn">Follow Kami</a>
            </div>
            <div class="contact-card" data-aos="fade-left" data-aos-delay="200">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>sewa.play@gmail.com</p>
                <a href="mailto:sewa.play@gmail.com" class="contact-btn">Kirim Email</a>
            </div>
        </div>
    </section>
    <hr style="border: 8px solid gold;">
    <?php if (!$is_logged_in): ?>
    <section class="login-section" id="login">
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <?php if (!empty($login_error_message)): ?>
                <p class="message-error"><?php echo htmlspecialchars($login_error_message); ?></p>
            <?php endif; ?>
            <?php if (!empty($register_message)): // Tampilkan pesan registrasi di form login ?>
                <p class="message-<?php echo $register_message_type; ?>"><?php echo htmlspecialchars($register_message); ?></p>
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
    <?php endif; ?>
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3>Sewa.Play</h3>
                <p>Kami menyediakan penyewaan PlayStation dengan paket lengkap dan harga terjangkau. Rasakan pengalaman bermain terbaik tanpa ribet.</p>
            </div>

            <div class="footer-links">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#perangkat">Perangkat</a></li>
                    <li><a href="#harga">Harga</a></li>
                    <li><a href="#games">Game</a></li>
                    <li><a href="#cara">Cara Sewa</a></li>
                    <li><a href="#kontak">Kontak</a></li>
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
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="script.js"></script>
</body>
</html>