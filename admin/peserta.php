<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Ambil kata kunci pencarian dari query string
$q = trim($_GET['q'] ?? '');

// Query data peserta: jika ada q, filter berdasarkan full_name
if ($q !== '') {
    $stmt = $koneksi->prepare("SELECT * FROM peserta WHERE full_name LIKE ? ORDER BY id ASC");
    $like = "%{$q}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Tanpa filter (default)
    $result = $koneksi->query("SELECT * FROM peserta ORDER BY id ASC");
}
?>

<link rel="stylesheet" href="../assets/css/peserta_admin.css">

<div class="content">
    <h2>Kelola Peserta</h2>

<form method="get" action="" class="form-cari">
  <input type="text" name="q" placeholder="Cari nama peserta..." value="<?= htmlspecialchars($q) ?>">
  <button type="submit" class="btn-cari">Cari</button>
  <?php if ($q !== ''): ?>
    <a href="?" class="btn-reset">Reset</a>
  <?php endif; ?>
  <a href="peserta_create.php" class="btn-tambah">+ Tambah Peserta</a>
</form>

<div class="tbl-wrap">
  <table class="tbl">
    <thead>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Sekolah</th>
      <th>Jurusan</th>
      <th>No HP</th>
      <th>Kelamin</th>
      <th>Aksi</th>
    </tr>
    </thead>
    <?php 
      $no = 1;
      if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['nama_sekolah']) ?></td>
            <td><?= htmlspecialchars($row['jurusan']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td>
              <?php if (($row['gender'] ?? '') === 'L'): ?>
                <span class="badge-sex badge-L">L</span>
              <?php else: ?>
                <span class="badge-sex badge-P">P</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="peserta_update.php?id=<?= $row['id'] ?>" class="btn-update">Update</a>
              <a href="peserta_delete.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus peserta ini?')">Delete</a>
            </td>
          </tr>
    <?php endwhile; else: ?>
      <tr>
        <td colspan="7" style="text-align:center; padding:14px;">
          <?= $q !== '' ? 'Tidak ada peserta dengan nama yang cocok.' : 'Belum ada data peserta.' ?>
        </td>
      </tr>
    <?php endif; ?>
  </table>
</div>
