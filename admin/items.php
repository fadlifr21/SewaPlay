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

// Ambil data item tambahan
$sql = "SELECT * FROM ItemTambahan ORDER BY nama_item";
$result_item = $conn->query($sql);
$items = [];
if ($result_item->num_rows > 0) {
    while ($row = $result_item->fetch_assoc()) {
        $items[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Item Tambahan - Admin</title>
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
        <h2>Manajemen Item Tambahan</h2>

        <?php if (!empty($message)): ?>
            <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="add-form-container">
            <h3>Tambah Item Tambahan Baru</h3>
            <form action="process_item_action.php" method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <div class="input-group">
                    <label for="nama_item">Nama Item</label>
                    <input type="text" id="nama_item" name="nama_item" placeholder="Contoh: Stik PS5 DualSense" required>
                </div>
                <div class="input-group">
                    <label for="harga_satuan">Harga Satuan</label>
                    <input type="number" id="harga_satuan" name="harga_satuan" step="0.01" min="0" placeholder="Contoh: 15000.00" required>
                </div>
                <div class="input-group">
                    <label for="satuan_item">Satuan Item</label>
                    <input type="text" id="satuan_item" name="satuan_item" placeholder="Contoh: per hari, per unit" required>
                </div>
                <div class="input-group">
                    <label for="status_ketersediaan_item">Status Awal Ketersediaan</label>
                    <select id="status_ketersediaan_item" name="status_ketersediaan" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select>
                </div>
                <button type="submit">Tambah Item</button>
            </form>
        </div>

        <hr class="separator-line">

        <h3>Daftar Item Tambahan</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Item</th>
                    <th>Harga Satuan</th>
                    <th>Satuan</th>
                    <th>Status Ketersediaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['id_item']); ?></td>
                            <td><?php echo htmlspecialchars($item['nama_item']); ?></td>
                            <td>Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($item['satuan_item']); ?></td>
                            <td><span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($item['status_ketersediaan'])); ?>">
                                <?php echo htmlspecialchars($item['status_ketersediaan']); ?>
                            </span></td>
                            <td>
                                <form action="process_item_action.php" method="POST" class="action-form">
                                    <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($item['id_item']); ?>">
                                    <select name="new_status">
                                        <option value="Tersedia" <?php echo ($item['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Tidak Tersedia" <?php echo ($item['status_ketersediaan'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                    </select>
                                    <button type="submit" name="action" value="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Belum ada item tambahan yang terdaftar.</td></tr>
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