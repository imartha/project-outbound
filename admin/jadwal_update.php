<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

$id = $_GET['id'] ?? 0;

// --- CEK QUERY ---
$stmt = $koneksi->prepare("SELECT * FROM jadwal_kegiatan WHERE id=?");
if (!$stmt) {
    die("Error prepare SELECT: " . $koneksi->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$jadwal = $result->fetch_assoc();

if (!$jadwal) {
    echo "Jadwal tidak ditemukan!";
    exit;
}

// --- UPDATE DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['tanggal'];
    $waktu      = $_POST['waktu'];
    $kegiatan   = $_POST['kegiatan'];
    $lokasi     = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];

    $stmt = $koneksi->prepare("UPDATE jadwal_kegiatan SET tanggal=?, waktu=?, kegiatan=?, lokasi=?, keterangan=?, updated_at=NOW() WHERE id=?");
    if (!$stmt) {
        die("Error prepare UPDATE: " . $koneksi->error);
    }

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

<link rel="stylesheet" href="../assets/css/jadwal_admin.css">

<div class="content">
  <h2>Update Jadwal Kegiatan</h2>

  <div class="form-wrapper">
    <form method="post">
      <label for="tanggal">Tanggal:</label>
      <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($jadwal['tanggal']) ?>" required>

      <label for="waktu">Waktu:</label>
      <input type="time" id="waktu" name="waktu" value="<?= htmlspecialchars($jadwal['waktu']) ?>" required>

      <label for="kegiatan">Kegiatan:</label>
      <input type="text" id="kegiatan" name="kegiatan" value="<?= htmlspecialchars($jadwal['kegiatan']) ?>" required>

      <label for="lokasi">Lokasi:</label>
      <input type="text" id="lokasi" name="lokasi" value="<?= htmlspecialchars($jadwal['lokasi']) ?>" required>

      <label for="keterangan">Keterangan:</label>
      <textarea id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($jadwal['keterangan']) ?></textarea>

      <div class="form-actions">
        <button type="submit" class="btn-submit">Update</button>
        <a href="jadwal.php" class="btn-cancel">Batal</a>
      </div>
    </form>
  </div>
</div>
