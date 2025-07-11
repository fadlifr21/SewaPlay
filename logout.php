<?php
session_start(); // Mulai sesi

// Hancurkan semua variabel sesi
$_SESSION = array();

// Hapus cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman login atau beranda
header('Location: index.php'); // Atau 'index.html'
exit;
?>