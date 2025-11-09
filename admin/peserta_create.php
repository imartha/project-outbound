<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $nama    = trim($_POST['full_name']);
    $sekolah = trim($_POST['nama_sekolah']);
    $jurusan = trim($_POST['jurusan']);
    $phone   = trim($_POST['phone']);
    $gender  = trim($_POST['gender']);

    $stmt = $koneksi->prepare("INSERT INTO peserta (full_name, nama_sekolah, jurusan, phone, gender) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $sekolah, $jurusan, $phone, $gender);

    if ($stmt->execute()) {
        header("Location: peserta.php");
        exit;
    } else {
        echo "Gagal menyimpan: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Peserta</title>
<link rel="stylesheet" href="../assets/css/peserta_admin.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="content">
    <h2>Tambah Peserta</h2>
    <form method="post">
        <input type="text" name="full_name" placeholder="Nama" required><br><br>
        <input type="text" name="nama_sekolah" placeholder="Sekolah" required><br><br>
        <input type="text" name="jurusan" placeholder="Jurusan" required><br><br>
        <input type="text" name="phone" placeholder="No HP" required><br><br>

        <label>Kelamin:</label><br>
        <select name="gender" required>
            <option value=""> Jenis Kelamin </option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select><br><br>

        <div class="form-actions">
    <button type="submit" name="simpan">Simpan</button>
    <a href="peserta.php">Batal</a>
</div>

    </form>
</div>
</body>
</html>
