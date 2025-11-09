<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Ambil data jadwal
$result = $koneksi->query("SELECT * FROM jadwal_kegiatan ORDER BY tanggal ASC, waktu ASC");
?>

<link rel="stylesheet" href="../assets/css/jadwal_admin.css">

<div class="content">
    <h2>Kelola Jadwal</h2>
    <a href="jadwal_create.php" class="btn-tambah">+ Tambah Jadwal</a>
    <br><br>

    <table class="tbl">
        <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Kegiatan</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <?php 
        if ($result && $result->num_rows > 0):
            $no = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= date('H:i', strtotime($row['waktu'])) ?></td>
                    <td><?= htmlspecialchars($row['kegiatan']) ?></td>
                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                    <td>
                        <a href="jadwal_update.php?id=<?= $row['id'] ?>" class="btn-update">Update</a>
                        <a href="jadwal_delete.php?id=<?= $row['id'] ?>" class="btn-delete"
                           onclick="return confirm('Yakin hapus jadwal ini?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; 
        else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">Belum ada jadwal</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
