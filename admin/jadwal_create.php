<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// PROSES SIMPAN DATA BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['tanggal'];
    $waktu      = $_POST['waktu'];
    $kegiatan   = $_POST['kegiatan'];
    $lokasi     = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];

    $stmt = $koneksi->prepare("INSERT INTO jadwal_kegiatan (tanggal, waktu, kegiatan, lokasi, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    if (!$stmt) {
        die("Error prepare INSERT: " . $koneksi->error);
    }

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

<style>
/* === Gaya yang sama seperti halaman update === */
.content {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    margin: 40px auto;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    font-family: "Poppins", sans-serif;
}

.content h2 {
    text-align: center;
    color: #333;
    margin-bottom: 25px;
}

form label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #444;
}

form input, form textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    transition: 0.2s;
}

form input:focus, form textarea:focus {
    border-color: #007BFF;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
    outline: none;
}

button {
    background-color: #007BFF;
    border: none;
    padding: 10px 20px;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}

button:hover {
    background-color: #0056b3;
}

a.cancel-link {
    text-decoration: none;
    color: #555;
    margin-left: 15px;
    font-weight: 500;
    transition: color 0.2s;
}

a.cancel-link:hover {
    color: #000;
}
</style>

<div class="content">
    <h2>Tambah Jadwal</h2>
    <form method="post" action="">
        <label>Tanggal:</label>
        <input type="date" name="tanggal" required>

        <label>Waktu:</label>
        <input type="time" name="waktu" required>

        <label>Nama Kegiatan:</label>
        <input type="text" name="kegiatan" placeholder="Nama Kegiatan" required>

        <label>Lokasi:</label>
        <input type="text" name="lokasi" placeholder="Lokasi" required>

        <label>Keterangan:</label>
        <textarea name="keterangan" placeholder="Keterangan" rows="3"></textarea>

        <button type="submit">Simpan</button>
        <a href="jadwal.php" class="cancel-link">Batal</a>
    </form>
</div>
