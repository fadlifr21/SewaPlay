<?php
session_start();

// Hancurkan semua variabel sesi admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Hancurkan sesi jika tidak ada sesi user yang aktif
if (empty($_SESSION)) {
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Arahkan kembali ke halaman login admin
header('Location: admin_login.php');
exit;
?>