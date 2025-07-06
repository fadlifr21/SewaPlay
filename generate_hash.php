<?php
$plaintext_password = 'admin123'; // Ganti dengan password yang ingin Anda gunakan
$hashed_password = password_hash($plaintext_password, PASSWORD_DEFAULT);
echo $hashed_password;
?>