<?php
$password_plain = 'admin123';
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
echo "Password '{$password_plain}' yang di-hash adalah: <br>";
echo "<strong>" . $password_hashed . "</strong>";
// Contoh hash: $2y$10$wN31Xb.hP.eO.p.7.R.j.u/M3.Q.hQ.l.N.p.0.O.C.u.q.Q.q.q.Q.q.Q.q.q.q.Q.q.Q.q.Q.q.Q.q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q.Q.q