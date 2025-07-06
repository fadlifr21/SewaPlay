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

// Ambil data paket sewa
$sql = "SELECT * FROM paketSewa ORDER BY nama_paket";
$result_paket = $conn->query($sql);
$pakets = [];
if ($result_paket->num_rows > 0) {
    while ($row = $result_paket->fetch_assoc()) {
        $pakets[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Paket Sewa - Admin</title>
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
        <h2>Manajemen Paket Sewa</h2>

        <?php if (!empty($message)): ?>
            <p class="message-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="add-form-container">
            <h3>Tambah Paket Sewa Baru</h3>
            <form action="process_paket_action.php" method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <div class="input-group">
                    <label for="nama_paket">Nama Paket</label>
                    <input type="text" id="nama_paket" name="nama_paket" placeholder="Contoh: Paket Malam" required>
                </div>
                <div class="input-group">
                    <label for="deskripsi_paket">Deskripsi</label>
                    <textarea id="deskripsi_paket" name="deskripsi" placeholder="Deskripsi singkat paket" rows="2"></textarea>
                </div>
                <div class="price-inputs">
                    <div class="input-group">
                        <label for="harga_ps4">Harga PS4 (Opsional)</label>
                        <input type="number" id="harga_ps4" name="harga_ps4" step="0.01" min="0" placeholder="Contoh: 70000.00">
                    </div>
                    <div class="input-group">
                        <label for="harga_ps5">Harga PS5 (Opsional)</label>
                        <input type="number" id="harga_ps5" name="harga_ps5" step="0.01" min="0" placeholder="Contoh: 100000.00">
                    </div>
                </div>
                <div class="input-group">
                    <label for="harga_spesifik">Harga Spesifik (Opsional, untuk Paket Acara, dll)</label>
                    <input type="number" id="harga_spesifik" name="harga_spesifik" step="0.01" min="0" placeholder="Contoh: 150000.00">
                    <small class="small-text-info">Isi ini jika paket memiliki harga tunggal, abaikan harga PS4/PS5.</small>
                </div>
                <div class="input-group">
                    <label for="satuan_harga">Satuan Harga</label>
                    <input type="text" id="satuan_harga" name="satuan_harga" placeholder="Contoh: per hari, per acara" required>
                </div>
                <div class="input-group">
                    <label for="status_paket_baru">Status Paket</label>
                    <select id="status_paket_baru" name="status_paket" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit">Tambah Paket</button>
            </form>
        </div>

        <hr class="separator-line">

        <h3>Daftar Paket Sewa</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Paket</th>
                    <th>Harga PS4</th>
                    <th>Harga PS5</th>
                    <th>Harga Spesifik</th>
                    <th>Satuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pakets)): ?>
                    <?php foreach ($pakets as $paket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($paket['id_paket']); ?></td>
                            <td><?php echo htmlspecialchars($paket['nama_paket']); ?></td>
                            <td><?php echo $paket['harga_ps4'] ? 'Rp ' . number_format($paket['harga_ps4'], 0, ',', '.') : '-'; ?></td>
                            <td><?php echo $paket['harga_ps5'] ? 'Rp ' . number_format($paket['harga_ps5'], 0, ',', '.') : '-'; ?></td>
                            <td><?php echo $paket['harga_spesifik'] ? 'Rp ' . number_format($paket['harga_spesifik'], 0, ',', '.') : '-'; ?></td>
                            <td><?php echo htmlspecialchars($paket['satuan_harga']); ?></td>
                            <td><span class="status-badge status-<?php echo htmlspecialchars($paket['status_paket']); ?>">
                                <?php echo htmlspecialchars($paket['status_paket']); ?>
                            </span></td>
                            <td>
                                <form action="process_paket_action.php" method="POST" class="action-form">
                                    <input type="hidden" name="id_paket" value="<?php echo htmlspecialchars($paket['id_paket']); ?>">
                                    <select name="new_status">
                                        <option value="Aktif" <?php echo ($paket['status_paket'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="Tidak Aktif" <?php echo ($paket['status_paket'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                    <button type="submit" name="action" value="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">Belum ada paket sewa yang terdaftar.</td></tr>
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