<?php
session_start();
require_once 'config/db.php'; // Sertakan file koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_input = $_POST['username'] ?? ''; // Menggunakan 'username' dari form login sebagai input email/username
    $password_input = $_POST['password'] ?? '';

    if (empty($email_input) || empty($password_input)) {
        $_SESSION['login_error'] = 'Email/Username dan password tidak boleh kosong.';
        header('Location: index.php');
        exit;
    }

    // Siapkan query untuk mencari penyewa berdasarkan email
    $stmt = $conn->prepare("SELECT id_penyewa, nama_lengkap, password FROM penyewa WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) { // Buka block untuk 'email ditemukan'
        $penyewa = $result->fetch_assoc();
        if (password_verify($password_input, $penyewa['password'])) { // Buka block untuk 'password benar'
            // Login berhasil
            $_SESSION['logged_in'] = true;
            $_SESSION['id_penyewa'] = $penyewa['id_penyewa'];
            $_SESSION['nama_lengkap'] = $penyewa['nama_lengkap'];
            $_SESSION['email'] = $email_input;

            header('Location: index.php');
            exit;
        } else { // Tutup 'if password_verify' dan buka 'else' untuk password salah
            // Password salah
            $_SESSION['login_error'] = 'Email/Username atau password salah.';
            header('Location: login.php');
            exit;
        }
    } else { // Tutup 'if num_rows === 1' dan buka 'else' untuk email tidak ditemukan
        // Email tidak ditemukan
        $_SESSION['login_error'] = 'Email/Username atau password salah.'; // Pesan bisa disamakan untuk keamanan
        header('Location: login.php');
        exit;
    }

    $stmt->close();
    $conn->close();

} else {
    // Jika diakses langsung tanpa POST request
    header('Location: login.php');
    exit;
}
?>