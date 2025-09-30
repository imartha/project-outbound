<?php
include '../koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $koneksi->query("DELETE FROM peserta WHERE id='$id'");
}
header("Location: peserta.php");
exit;
