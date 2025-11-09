<?php
include '../koneksi.php';
include '../utils/auth_check.php';

$id = $_GET['id'] ?? 0;

$stmt = $koneksi->prepare("DELETE FROM jadwal_kegiatan WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: jadwal.php");
    exit;
} else {
    echo "Gagal hapus: " . $stmt->error;
}
$stmt->close();
?>
