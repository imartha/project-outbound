<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// CREATE
if (isset($_POST['tambah'])) {
    $nama_item = $_POST['nama_item'];
    $jumlah_biaya = $_POST['jumlah_biaya'];
    $satuan = $_POST['satuan'];
    $keterangan = $_POST['keterangan'];
    $tipe_biaya = $_POST['tipe_biaya'];
    $urutan = $_POST['urutan'];

    $sql = "INSERT INTO rincian_biaya (nama_item, jumlah_biaya, satuan, keterangan, tipe_biaya, urutan)
            VALUES ('$nama_item','$jumlah_biaya','$satuan','$keterangan','$tipe_biaya','$urutan')";
    $koneksi->query($sql);
    header("Location: rincian_biaya.php");
    exit;
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $koneksi->query("DELETE FROM rincian_biaya WHERE id='$id'");
    header("Location: rincian_biaya.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama_item = $_POST['nama_item'];
    $jumlah_biaya = $_POST['jumlah_biaya'];
    $satuan = $_POST['satuan'];
    $keterangan = $_POST['keterangan'];
    $tipe_biaya = $_POST['tipe_biaya'];
    $urutan = $_POST['urutan'];

    $sql = "UPDATE rincian_biaya 
            SET nama_item='$nama_item', jumlah_biaya='$jumlah_biaya', satuan='$satuan', keterangan='$keterangan', tipe_biaya='$tipe_biaya', urutan='$urutan'
            WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: rincian_biaya.php");
    exit;
}

// READ
$result = $koneksi->query("SELECT * FROM rincian_biaya ORDER BY urutan ASC");
?>

<div class="content">
    <h2>Kelola Rincian Biaya</h2>

    <!-- Tambah Biaya -->
    <form method="post" class="form-biaya">
        <input type="text" name="nama_item" placeholder="Nama Item" required>
        <input type="number" name="jumlah_biaya" placeholder="Jumlah" required>
        <input type="text" name="satuan" placeholder="Satuan" required>
        <input type="text" name="keterangan" placeholder="Keterangan">
        <select name="tipe_biaya" required>
            <option value="utama">Utama</option>
            <option value="tambahan">Tambahan</option>
            <option value="termasuk">Termasuk</option>
        </select>
        <input type="number" name="urutan" placeholder="Urutan" required>
        <button type="submit" name="tambah">Tambah</button>
    </form>
    <br>

    <!-- Tabel Biaya -->
    <table class="tbl">
        <tr>
            <th>Nama Item</th>
            <th>Jumlah</th>
            <th>Satuan</th>
            <th>Keterangan</th>
            <th>Tipe</th>
            <th>Urutan</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_item']) ?></td>
                <td>Rp <?= number_format($row['jumlah_biaya'],0,',','.') ?></td>
                <td><?= htmlspecialchars($row['satuan']) ?></td>
                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                <td><?= htmlspecialchars($row['tipe_biaya']) ?></td>
                <td><?= $row['urutan'] ?></td>
                <td>
                    <!-- Edit -->
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="text" name="nama_item" value="<?= htmlspecialchars($row['nama_item']) ?>" required>
                        <input type="number" name="jumlah_biaya" value="<?= $row['jumlah_biaya'] ?>" required>
                        <input type="text" name="satuan" value="<?= htmlspecialchars($row['satuan']) ?>" required>
                        <input type="text" name="keterangan" value="<?= htmlspecialchars($row['keterangan']) ?>">
                        <select name="tipe_biaya">
                            <option value="utama" <?= $row['tipe_biaya']=='utama'?'selected':'' ?>>Utama</option>
                            <option value="tambahan" <?= $row['tipe_biaya']=='tambahan'?'selected':'' ?>>Tambahan</option>
                            <option value="termasuk" <?= $row['tipe_biaya']=='termasuk'?'selected':'' ?>>Termasuk</option>
                        </select>
                        <input type="number" name="urutan" value="<?= $row['urutan'] ?>" required>
                        <button type="submit" name="update">Update</button>
                    </form>

                    <!-- Hapus -->
                    <a href="rincian_biaya.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>