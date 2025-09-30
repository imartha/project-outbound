<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Ambil data peserta (urutan lama ke baru)
$result = $koneksi->query("SELECT * FROM peserta ORDER BY id ASC");
?>

<div class="content">
    <h2>Kelola Peserta</h2>
    <a href="peserta_create.php" class="btn-tambah">+ Tambah Peserta</a>
    <br><br>

    <table class="tbl">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Sekolah</th>
            <th>Jurusan</th>
            <th>No HP</th>
            <th>Kelamin</th>
            <th>Aksi</th>
        </tr>
        <?php 
        $no = 1; // nomor dimulai dari 1
        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['nama_sekolah']) ?></td>
                <td><?= htmlspecialchars($row['jurusan']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= $row['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                <td>
                    <a href="peserta_update.php?id=<?= $row['id'] ?>" class="btn-update">Update</a>
                    <a href="peserta_delete.php?id=<?= $row['id'] ?>" class="btn-delete"
                       onclick="return confirm('Yakin hapus peserta ini?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</div>
