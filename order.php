<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = 'Anda harus login untuk membuat pesanan.';
    header('Location: index.php');
    exit;
}

$id_penyewa = $_SESSION['user_id'] ?? 0;
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Pengguna';

$message = '';
$message_type = '';

if (isset($_SESSION['order_message'])) {
    $message = $_SESSION['order_message'];
    $message_type = $_SESSION['order_message_type'];
    unset($_SESSION['order_message']);
    unset($_SESSION['order_message_type']);
}

$stmt_paket = $conn->prepare("SELECT id_paket, nama_paket, harga_ps4, harga_ps5, harga_spesifik, satuan_harga FROM paketSewa WHERE status_paket = 'Aktif'");
$stmt_paket->execute();
$result_paket = $stmt_paket->get_result();
$pakets = [];
while ($row = $result_paket->fetch_assoc()) {
    $pakets[] = $row;
}
$stmt_paket->close();

$stmt_perangkat = $conn->prepare("SELECT id_perangkat, nama_perangkat, tipe_perangkat FROM perangkat WHERE status_ketersediaan = 'Tersedia'");
$stmt_perangkat->execute();
$result_perangkat = $stmt_perangkat->get_result();
$perangkats = [];
while ($row = $result_perangkat->fetch_assoc()) {
    $perangkats[] = $row;
}
$stmt_perangkat->close();

$stmt_item = $conn->prepare("SELECT id_item, nama_item, harga_satuan, satuan_item FROM ItemTambahan WHERE status_ketersediaan = 'Tersedia'");
$stmt_item->execute();
$result_item = $stmt_item->get_result();
$items = [];
while ($row = $result_item->fetch_assoc()) {
    $items[] = $row;
}
$stmt_item->close();

$stmt_game = $conn->prepare("SELECT id_game, judul_game, harga_sewa_per_hari FROM game WHERE status_ketersediaan = 'Tersedia'");
$stmt_game->execute();
$result_game = $stmt_game->get_result();
$games = [];
while ($row = $result_game->fetch_assoc()) {
    $games[] = $row;
}
$stmt_game->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan Baru - Sewa PlayStation Pekanbaru</title>
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
            <a href="order.php">Pesan Sekarang</a>
            <a href="akun.php" class="user-icon" title="Akun Saya: <?php echo htmlspecialchars($nama_lengkap); ?>"><i class="fas fa-user-circle"></i></a>
            <a href="logout.php" class="logout-icon" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <section class="order-section">
        <div class="order-container">
            <h2>Buat Pesanan Penyewaan</h2>
            <?php if (!empty($message)): ?>
                <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php
            if (isset($_SESSION['last_order_total'])) {
                echo '<p class="message-success">Total Biaya Pesanan Anda: Rp ' . number_format($_SESSION['last_order_total'], 0, ',', '.') . '</p>';
                unset($_SESSION['last_order_total']);
            }
            ?>

            <form class="order-form" method="POST" action="process_order.php">
                <input type="hidden" name="id_penyewa" value="<?php echo htmlspecialchars($id_penyewa); ?>">

                <div class="input-group">
                    <label for="nama_penyewa">Nama Penyewa</label>
                    <input type="text" id="nama_penyewa" name="nama_penyewa" value="<?php echo htmlspecialchars($nama_lengkap); ?>" disabled>
                    <small style="color: #666; font-size: 0.85em; display: block; margin-top: 5px;">Nama ini diambil dari akun Anda.</small>
                </div>

                <div class="input-group">
                    <label for="tgl_mulai_sewa">Tanggal Mulai Sewa</label>
                    <input type="date" id="tgl_mulai_sewa" name="tgl_mulai_sewa" required>
                </div>
                <div class="input-group">
                    <label for="tgl_selesai_sewa">Tanggal Selesai Sewa</label>
                    <input type="date" id="tgl_selesai_sewa" name="tgl_selesai_sewa" required>
                </div>

                <div class="input-group">
                    <label for="id_paket">Pilih Paket Sewa</label>
                    <select id="id_paket" name="id_paket" onchange="updatePerangkatOptions()">
                        <option value="">-- Pilih Paket Sewa --</option>
                        <?php foreach ($pakets as $paket): ?>
                            <option value="<?php echo htmlspecialchars($paket['id_paket']); ?>"
                                data-harga-ps4="<?php echo htmlspecialchars($paket['harga_ps4'] ?? ''); ?>"
                                data-harga-ps5="<?php echo htmlspecialchars($paket['harga_ps5'] ?? ''); ?>"
                                data-harga-spesifik="<?php echo htmlspecialchars($paket['harga_spesifik'] ?? ''); ?>"
                                data-satuan="<?php echo htmlspecialchars($paket['satuan_harga']); ?>">
                                <?php echo htmlspecialchars($paket['nama_paket']); ?>
                                (<?php
                                    if ($paket['harga_ps4'] && $paket['harga_ps5']) {
                                        echo "PS4: Rp " . number_format($paket['harga_ps4']) . " | PS5: Rp " . number_format($paket['harga_ps5']);
                                    } elseif ($paket['harga_spesifik']) {
                                        echo "Rp " . number_format($paket['harga_spesifik']);
                                    }
                                ?> / <?php echo htmlspecialchars($paket['satuan_harga']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label for="id_perangkat">Pilih Perangkat (Opsional, tergantung Paket)</label>
                    <select id="id_perangkat" name="id_perangkat">
                        <option value="">-- Pilih Perangkat --</option>
                        <?php foreach ($perangkats as $perangkat): ?>
                            <option value="<?php echo htmlspecialchars($perangkat['id_perangkat']); ?>" data-tipe-perangkat="<?php echo htmlspecialchars($perangkat['tipe_perangkat']); ?>">
                                <?php echo htmlspecialchars($perangkat['nama_perangkat']); ?> (<?php echo htmlspecialchars($perangkat['tipe_perangkat']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; font-size: 0.85em; display: block; margin-top: 5px;">Pilih perangkat yang sesuai dengan paket sewa (jika ada).</small>
                </div>

                <div class="input-group">
                    <label>Item Tambahan (Opsional)</label>
                    <div id="item-tambahan-container" class="item-selection-grid">
                    </div>
                    <button type="button" id="add-item-btn" class="add-item-btn">Tambah Item Tambahan</button>
                </div>

                <div class="input-group">
                    <label>Sewa Game Fisik (Opsional)</label>
                    <div id="game-selection-container" class="item-selection-grid">
                    </div>
                    <button type="button" id="add-game-btn" class="add-item-btn" style="background-color: #007bff;">Tambah Game</button>
                </div>
                <div class="input-group">
                    <label for="metode_pengantaran">Metode Pengantaran</label>
                    <select id="metode_pengantaran" name="metode_pengantaran" required>
                        <option value="">Pilih Metode Pengantaran</option>
                        <option value="Ambil Sendiri">Ambil Sendiri</option>
                        <option value="Antar Jemput">Antar Jemput</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="catatan">Catatan Tambahan (Opsional)</label>
                    <textarea id="catatan" name="catatan" placeholder="Contoh: PS5 + 2 Stik + Monitor. Untuk acara ulang tahun." rows="4"></textarea>
                </div>

                <button type="submit" class="btn order-btn">Kirim Pesanan</button>
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
    <script>
        const allPerangkats = <?php echo json_encode($perangkats); ?>;
        const allItems = <?php echo json_encode($items); ?>;
        const allGames = <?php echo json_encode($games); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const addItemBtn = document.getElementById('add-item-btn');
            const itemContainer = document.getElementById('item-tambahan-container');
            const addGameBtn = document.getElementById('add-game-btn');
            const gameContainer = document.getElementById('game-selection-container');

            addItemBtn.addEventListener('click', () => {
                addItemRow();
            });

            addGameBtn.addEventListener('click', () => {
                addGameRow();
            });

            function addItemRow() {
                const newRow = document.createElement('div');
                newRow.classList.add('item-row');
                newRow.innerHTML = `
                    <select name="items[]" required>
                        <option value="">Pilih Item Tambahan</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo htmlspecialchars($item['id_item']); ?>"
                                data-harga="<?php echo htmlspecialchars($item['harga_satuan']); ?>"
                                data-satuan="<?php echo htmlspecialchars($item['satuan_item']); ?>">
                                <?php echo htmlspecialchars($item['nama_item']); ?> (Rp <?php echo number_format($item['harga_satuan']); ?> / <?php echo htmlspecialchars($item['satuan_item']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="jumlah_items[]" value="1" min="1" required style="width: 80px;">
                    <button type="button" class="remove-item-btn">X</button>
                `;
                itemContainer.appendChild(newRow);

                newRow.querySelector('.remove-item-btn').addEventListener('click', (event) => {
                    event.target.closest('.item-row').remove();
                });
            }

            function addGameRow() {
                const newRow = document.createElement('div');
                newRow.classList.add('item-row');
                newRow.innerHTML = `
                    <select name="games[]" required>
                        <option value="">Pilih Game</option>
                        <?php foreach ($games as $game): ?>
                            <option value="<?php echo htmlspecialchars($game['id_game']); ?>"
                                data-harga="<?php echo htmlspecialchars($game['harga_sewa_per_hari']); ?>">
                                <?php echo htmlspecialchars($game['judul_game']); ?> (Rp <?php echo number_format($game['harga_sewa_per_hari']); ?> / hari)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="jumlah_games[]" value="1" min="1" required style="width: 80px;">
                    <button type="button" class="remove-item-btn">X</button>
                `;
                gameContainer.appendChild(newRow);

                newRow.querySelector('.remove-item-btn').addEventListener('click', (event) => {
                    event.target.closest('.item-row').remove();
                });
            }
        });

        function updatePerangkatOptions() {
            const paketSelect = document.getElementById('id_paket');
            const perangkatSelect = document.getElementById('id_perangkat');
            const selectedOption = paketSelect.options[paketSelect.selectedIndex];
            const selectedPackageName = selectedOption.textContent.toLowerCase();

            perangkatSelect.innerHTML = '<option value="">-- Pilih Perangkat --</option>';

            let allowedDeviceTypes = [];

            if (selectedPackageName.includes('ps4')) {
                allowedDeviceTypes.push('PS4');
            }
            if (selectedPackageName.includes('ps5')) {
                allowedDeviceTypes.push('PS5');
            }
            if (selectedPackageName.includes('acara')) {
                allowedDeviceTypes.push('Monitor', 'Proyektor');
            }
            if (allowedDeviceTypes.length === 0) {
                 allowedDeviceTypes.push('PS4', 'PS5', 'Monitor', 'VR');
            }


            allPerangkats.forEach(perangkat => {
                if (allowedDeviceTypes.includes(perangkat.tipe_perangkat)) {
                    const option = document.createElement('option');
                    option.value = perangkat.id_perangkat;
                    option.textContent = `${perangkat.nama_perangkat} (${perangkat['tipe_perangkat']})`;
                    perangkatSelect.appendChild(option);
                }
            });
        }

        updatePerangkatOptions();
    </script>
</body>
</html>