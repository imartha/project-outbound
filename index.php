<?php
session_start();

// Jika belum login, arahkan ke login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek role user
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: peserta/dashboard.php");
}
exit;
?>
