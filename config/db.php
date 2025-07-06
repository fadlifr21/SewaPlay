<?php
// Konfigurasi Database
define('DB_SERVER', 'localhost'); // Biasanya localhost
define('DB_USERNAME', 'root');   // Ganti dengan username database Anda
define('DB_PASSWORD', '');       // Ganti dengan password database Anda
define('DB_NAME', 'db_play'); // Ganti dengan nama database yang Anda buat

// Aktifkan reporting untuk kesalahan MySQLi yang lebih ketat
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Buat koneksi database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Periksa koneksi (meskipun mysqli_report akan melempar Exception jika gagal)
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>