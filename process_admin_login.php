<?php
session_start();
require_once 'config/db.php'; // Pastikan path ini benar dari root

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['admin_login_error'] = 'Username dan password tidak boleh kosong.';
        header('Location: admin_login.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password, role FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            // Login admin berhasil
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];

            header('Location: admin/dashboard.php'); // Arahkan ke dashboard admin
            exit;
        } else {
            $_SESSION['admin_login_error'] = 'Username atau password salah.';
            header('Location: admin_login.php');
            exit;
        }
    } else {
        $_SESSION['admin_login_error'] = 'Username atau password salah.';
        header('Location: admin_login.php');
        exit;
    }

    $stmt->close();
    $conn->close();

} else {
    header('Location: admin_login.php');
    exit;
}
?>