<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

$id = $_GET['id'] ?? 0;
$stmt = $koneksi->prepare("SELECT * FROM jadwal WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$jadwal = $result->fetch_assoc();

if (!$jadwal) {
    echo "Jadwal tidak ditemukan!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['tanggal'];
    $waktu      = $_POST['waktu'];
    $kegiatan   = $_POST['kegiatan'];
    $lokasi     = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];

    $stmt = $koneksi->prepare("UPDATE jadwal SET tanggal=?, waktu=?, kegiatan=?, lokasi=?, keterangan=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssssi", $tanggal, $waktu, $kegiatan, $lokasi, $keterangan, $id);

    if ($stmt->execute()) {
        header("Location: jadwal.php");
        exit;
    } else {
        echo "Gagal update: " . $stmt->error;
    }
    $stmt->close();
}
?>

<div class="content">
    <h2>Update Jadwal</h2>
    <form method="post">
        <label>Tanggal:</label>
        <input type="date" name="tanggal" value="<?= $jadwal['tanggal'] ?>" required><br><br>

        <label>Waktu:</label>
        <input type="time" name="waktu" value="<?= $jadwal['waktu'] ?>" required><br><br>

        <input type="text" name="kegiatan" value="<?= htmlspecialchars($jadwal['kegiatan']) ?>" required><br><br>
        <input type="text" name="lokasi" value="<?= htmlspecialchars($jadwal['lokasi']) ?>" required><br><br>
        <textarea name="keterangan" rows="3"><?= htmlspecialchars($jadwal['keterangan']) ?></textarea><br><br>

        <button type="submit">Update</button>
        <a href="jadwal.php" style="margin-left:10px;">Batal</a>
    </form>
</div>
