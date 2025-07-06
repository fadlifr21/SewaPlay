<?php
session_start();
require_once '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

$message = '';
$message_type = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    $message_type = $_SESSION['admin_message_type'];
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
}

// Ambil data game dari database
$sql = "SELECT * FROM game ORDER BY judul_game";
$result_game = $conn->query($sql);
$games = [];
if ($result_game->num_rows > 0) {
    while ($row = $result_game->fetch_assoc()) {
        $games[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Game - Admin</title>
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

    <section class="admin-content-section">
        <h2>Manajemen Game Fisik</h2>

        <?php if (!empty($message)): ?>
            <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="add-form-container">
            <h3>Tambah Game Baru</h3>
            <form action="process_game_action.php" method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <div class="input-group">
                    <label for="judul_game">Judul Game</label>
                    <input type="text" id="judul_game" name="judul_game" placeholder="Contoh: The Last of Us Part II" required>
                </div>
                <div class="input-group">
                    <label for="harga_sewa_per_hari">Harga Sewa Per Hari</label>
                    <input type="number" id="harga_sewa_per_hari" name="harga_sewa_per_hari" step="0.01" min="0" placeholder="Contoh: 10000.00" required>
                </div>
                <div class="input-group">
                    <label for="path_gambar">Path Gambar (Opsional)</label>
                    <input type="text" id="path_gambar" name="path_gambar" placeholder="Contoh: img/game_cover.jpg">
                    <small class="small-text-info">Masukkan path relatif dari root proyek Anda (misal: `img/nama_file.jpg`).</small>
                </div>
                <div class="input-group">
                    <label for="status_ketersediaan_game">Status Awal Ketersediaan</label>
                    <select id="status_ketersediaan_game" name="status_ketersediaan" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Disewa">Disewa</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select>
                </div>
                <button type="submit">Tambah Game</button>
            </form>
        </div>

        <hr class="separator-line">

        <h3>Daftar Game</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Game</th>
                    <th>Harga Sewa/Hari</th>
                    <th>Gambar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($games)): ?>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($game['id_game']); ?></td>
                            <td><?php echo htmlspecialchars($game['judul_game']); ?></td>
                            <td>Rp <?php echo number_format($game['harga_sewa_per_hari'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if (!empty($game['path_gambar'])): ?>
                                    <img src="../<?php echo htmlspecialchars($game['path_gambar']); ?>" alt="Cover Game" class="game-img-preview">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($game['status_ketersediaan'])); ?>">
                                <?php echo htmlspecialchars($game['status_ketersediaan']); ?>
                            </span></td>
                            <td>
                                <form action="process_game_action.php" method="POST" class="action-form">
                                    <input type="hidden" name="id_game" value="<?php echo htmlspecialchars($game['id_game']); ?>">
                                    <select name="new_status">
                                        <option value="Tersedia" <?php echo ($game['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Disewa" <?php echo ($game['status_ketersediaan'] == 'Disewa') ? 'selected' : ''; ?>>Disewa</option>
                                        <option value="Tidak Tersedia" <?php echo ($game['status_ketersediaan'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                    </select>
                                    <button type="submit" name="action" value="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Belum ada game yang terdaftar.</td></tr>
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