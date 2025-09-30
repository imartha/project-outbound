<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Ambil ID peserta
if (!isset($_GET['id'])) {
    header("Location: peserta.php");
    exit;
}
$id = intval($_GET['id']);

// Ambil data peserta berdasarkan ID
$stmt = $koneksi->prepare("SELECT * FROM peserta WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$peserta = $res->fetch_assoc();

if (!$peserta) {
    echo "<p>Peserta tidak ditemukan.</p>";
    exit;
}
$stmt->close();

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama    = trim($_POST['full_name']);
    $sekolah = trim($_POST['nama_sekolah']);
    $jurusan = trim($_POST['jurusan']);
    $phone   = trim($_POST['phone']);
    $gender  = trim($_POST['gender']);

    $stmt = $koneksi->prepare("UPDATE peserta SET full_name=?, nama_sekolah=?, jurusan=?, phone=?, gender=? WHERE id=?");
    $stmt->bind_param("sssssi", $nama, $sekolah, $jurusan, $phone, $gender, $id);

    if ($stmt->execute()) {
        header("Location: peserta.php");
        exit;
    } else {
        echo "Gagal mengupdate: " . $stmt->error;
    }
    $stmt->close();
}
?>

<div class="content">
    <h2>Update Peserta</h2>
    <form method="post">
        <input type="text" name="full_name" value="<?= htmlspecialchars($peserta['full_name']) ?>" required><br><br>
        <input type="text" name="nama_sekolah" value="<?= htmlspecialchars($peserta['nama_sekolah']) ?>" required><br><br>
        <input type="text" name="jurusan" value="<?= htmlspecialchars($peserta['jurusan']) ?>" required><br><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($peserta['phone']) ?>" required><br><br>

        <label>Kelamin:</label><br>
        <select name="gender" required>
            <option value="L" <?= $peserta['gender'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= $peserta['gender'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select><br><br>

        <button type="submit" name="update">Update</button>
        <a href="peserta.php" style="margin-left:10px;">Batal</a>
    </form>
</div>
