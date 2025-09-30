<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "project_outbound_db";

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
