<?php
session_start();
require_once '../config/db.php';

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

// Ambil data perangkat
$sql = "SELECT * FROM perangkat ORDER BY tipe_perangkat, nama_perangkat";
$result_perangkat = $conn->query($sql);
$perangkats = [];
if ($result_perangkat->num_rows > 0) {
    while ($row = $result_perangkat->fetch_assoc()) {
        $perangkats[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perangkat - Admin</title>
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
        <h2>Manajemen Perangkat</h2>

        <?php if (!empty($message)): ?>
            <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="add-form-container">
            <h3>Tambah Perangkat Baru</h3>
            <form action="process_perangkat_action.php" method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <div class="input-group">
                    <label for="nama_perangkat">Nama Perangkat</label>
                    <input type="text" id="nama_perangkat" name="nama_perangkat" placeholder="Contoh: PlayStation 5 #002" required>
                </div>
                <div class="input-group">
                    <label for="deskripsi_perangkat">Deskripsi</label>
                    <textarea id="deskripsi_perangkat" name="deskripsi" placeholder="Deskripsi singkat perangkat" rows="2"></textarea>
                </div>
                <div class="input-group">
                    <label for="tipe_perangkat">Tipe Perangkat</label>
                    <select id="tipe_perangkat" name="tipe_perangkat" required>
                        <option value="">Pilih Tipe</option>
                        <option value="PS4">PS4</option>
                        <option value="PS5">PS5</option>
                        <option value="Monitor">Monitor</option>
                        <option value="VR">VR</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="status_ketersediaan_baru">Status Awal Ketersediaan</label>
                    <select id="status_ketersediaan_baru" name="status_ketersediaan" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Disewa">Disewa</option>
                        <option value="Rusak">Rusak</option>
                    </select>
                </div>
                <button type="submit">Tambah Perangkat</button>
            </form>
        </div>

        <hr class="separator-line">

        <h3>Daftar Perangkat</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Perangkat</th>
                    <th>Tipe</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($perangkats)): ?>
                    <?php foreach ($perangkats as $perangkat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($perangkat['id_perangkat']); ?></td>
                            <td><?php echo htmlspecialchars($perangkat['nama_perangkat']); ?></td>
                            <td><?php echo htmlspecialchars($perangkat['tipe_perangkat']); ?></td>
                            <td><?php echo htmlspecialchars($perangkat['deskripsi']); ?></td>
                            <td><span class="status-badge status-<?php echo htmlspecialchars($perangkat['status_ketersediaan']); ?>">
                                <?php echo htmlspecialchars($perangkat['status_ketersediaan']); ?>
                            </span></td>
                            <td>
                                <form action="process_perangkat_action.php" method="POST" class="action-form">
                                    <input type="hidden" name="id_perangkat" value="<?php echo htmlspecialchars($perangkat['id_perangkat']); ?>">
                                    <select name="new_status">
                                        <option value="Tersedia" <?php echo ($perangkat['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Disewa" <?php echo ($perangkat['status_ketersediaan'] == 'Disewa') ? 'selected' : ''; ?>>Disewa</option>
                                        <option value="Rusak" <?php echo ($perangkat['status_ketersediaan'] == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                    </select>
                                    <button type="submit" name="action" value="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Belum ada perangkat yang terdaftar.</td></tr>
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