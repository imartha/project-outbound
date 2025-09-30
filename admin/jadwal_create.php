<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['tanggal'];
    $waktu      = $_POST['waktu'];
    $kegiatan   = $_POST['kegiatan'];
    $lokasi     = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];

    $stmt = $koneksi->prepare("INSERT INTO jadwal (tanggal, waktu, kegiatan, lokasi, keterangan) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $tanggal, $waktu, $kegiatan, $lokasi, $keterangan);

    if ($stmt->execute()) {
        header("Location: jadwal.php");
        exit;
    } else {
        echo "Gagal menyimpan: " . $stmt->error;
    }
    $stmt->close();
}
?>

<div class="content">
    <h2>Tambah Jadwal</h2>
    <form method="post">
        <label>Tanggal:</label>
        <input type="date" name="tanggal" required><br><br>

        <label>Waktu:</label>
        <input type="time" name="waktu" required><br><br>

        <input type="text" name="kegiatan" placeholder="Nama Kegiatan" required><br><br>
        <input type="text" name="lokasi" placeholder="Lokasi" required><br><br>
        <textarea name="keterangan" placeholder="Keterangan" rows="3"></textarea><br><br>

        <button type="submit">Simpan</button>
        <a href="jadwal.php" style="margin-left:10px;">Batal</a>
    </form>
</div>
