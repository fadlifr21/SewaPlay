<?php
session_start();
require_once 'config/db.php'; // Sertakan file koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari formulir
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nomor_hp = trim($_POST['nomor_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $nomor_ktp = trim($_POST['nomor_ktp'] ?? '');

    $errors = [];

    // Validasi input
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap tidak boleh kosong.";
    }
    if (empty($nomor_hp)) {
        $errors[] = "Nomor telepon tidak boleh kosong.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $nomor_hp)) { // Contoh validasi nomor HP
        $errors[] = "Nomor telepon tidak valid.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    if (empty($alamat)) {
        $errors[] = "Alamat tidak boleh kosong.";
    }
    if (empty($nomor_ktp)) {
        $errors[] = "Nomor KTP tidak boleh kosong.";
    } elseif (!preg_match("/^[0-9]{16}$/", $nomor_ktp)) { // Nomor KTP di Indonesia umumnya 16 digit
        $errors[] = "Nomor KTP tidak valid (harus 16 digit angka).";
    }


    // Jika tidak ada error validasi awal
    if (empty($errors)) {
        // Cek apakah email, nomor HP, atau nomor KTP sudah terdaftar
        $stmt_check = $conn->prepare("SELECT id_penyewa FROM penyewa WHERE email = ? OR nomor_hp = ? OR nomor_ktp = ?");
        $stmt_check->bind_param("sss", $email, $nomor_hp, $nomor_ktp);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "Email, Nomor Telepon, atau Nomor KTP sudah terdaftar.";
        }
        $stmt_check->close();
    }

    // Jika masih tidak ada error, lanjutkan pendaftaran
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_insert = $conn->prepare(
            "INSERT INTO penyewa (nama_lengkap, nomor_hp, email, password, alamat, nomor_ktp) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt_insert->bind_param("ssssss", $nama_lengkap, $nomor_hp, $email, $hashed_password, $alamat, $nomor_ktp);

        if ($stmt_insert->execute()) {
            $_SESSION['register_message'] = "Registrasi berhasil! Silakan login.";
            $_SESSION['register_message_type'] = 'success';
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Terjadi kesalahan saat registrasi. Silakan coba lagi. Error: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }

    // Jika ada error, simpan pesan error ke sesi dan arahkan kembali ke register.php
    if (!empty($errors)) {
        $_SESSION['register_message'] = implode("<br>", $errors); // Gabungkan semua error
        $_SESSION['register_message_type'] = 'error';
        header('Location: register.php');
        exit;
    }

} else {
    // Jika diakses langsung tanpa POST request, arahkan kembali ke register.php
    header('Location: register.php');
    exit;
}

$conn->close();
?>