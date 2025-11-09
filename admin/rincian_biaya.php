<?php
// admin/rincian_biaya_admin.php
include '../koneksi.php';
include '../utils/auth_check.php'; // jika ada
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// --- Helper: ambil urutan terakhir
function get_next_sort_order(mysqli $db) {
  $q = $db->query("SELECT IFNULL(MAX(sort_order),0) AS mx FROM fasilitas_biaya");
  $row = $q ? $q->fetch_assoc() : ['mx'=>0];
  return ((int)$row['mx']) + 1;
}

/* ====== HANDLE ACTION ====== */
// Simpan pengaturan biaya (harga + bank + no rek + a/n)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
  $total_biaya = (int)($_POST['total_biaya'] ?? 0);
  $bank_name   = trim($_POST['bank_name'] ?? '');
  $no_rek      = trim($_POST['no_rek'] ?? '');
  $atas_nama   = trim($_POST['atas_nama'] ?? '');

  $stmt = $koneksi->prepare("UPDATE biaya_settings SET total_biaya=?, bank_name=?, no_rek=?, atas_nama=? WHERE id=1");
  $stmt->bind_param('isss', $total_biaya, $bank_name, $no_rek, $atas_nama);
  $stmt->execute();
  $stmt->close();

  header("Location: rincian_biaya.php?msg=saved");
  exit;
}

// Tambah item rincian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
  $item_text = trim($_POST['item_text'] ?? '');
  if ($item_text !== '') {
    $sort = get_next_sort_order($koneksi);
    $stmt = $koneksi->prepare("INSERT INTO fasilitas_biaya (item_text, sort_order) VALUES (?, ?)");
    $stmt->bind_param('si', $item_text, $sort);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: rincian_biaya.php?msg=added");
  exit;
}

// Hapus item rincian
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  $stmt = $koneksi->prepare("DELETE FROM fasilitas_biaya WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  header("Location: rincian_biaya.php?msg=deleted");
  exit;
}

/* ====== GET DATA UNTUK FORM ====== */
$settings = [
  'total_biaya' => 0,
  'bank_name'   => '',
  'no_rek'      => '',
  'atas_nama'   => ''
];

$res = $koneksi->query("SELECT total_biaya, bank_name, no_rek, atas_nama FROM biaya_settings WHERE id=1");
if ($res && $res->num_rows) {
  $settings = $res->fetch_assoc();
  $res->free();
}

$fasilitas = [];
$res2 = $koneksi->query("SELECT id, item_text FROM fasilitas_biaya ORDER BY sort_order ASC, id ASC");
while ($row = $res2->fetch_assoc()) { $fasilitas[] = $row; }
$res2 && $res2->free();
?>

<link rel="stylesheet" href="../assets/css/biaya_admin.css">

<div class="content">
  <h2>Kelola Rincian Biaya Outbound</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert">
      <?php
        $m = $_GET['msg'];
        if ($m === 'saved') echo "Pengaturan berhasil disimpan.";
        elseif ($m === 'added') echo "Item rincian berhasil ditambahkan.";
        elseif ($m === 'deleted') echo "Item rincian berhasil dihapus.";
      ?>
    </div>
  <?php endif; ?>

  <!-- Form Pengaturan Utama -->
  <div class="card">
    <h3>Pengaturan Utama</h3>
    <form method="post" action="">
      <div class="form-row">
        <label>Total Biaya (Rp)</label>
        <input type="number" name="total_biaya" value="<?= htmlspecialchars((string)$settings['total_biaya']) ?>" required>
      </div>
      <div class="form-row">
        <label>Nama Bank</label>
        <input type="text" name="bank_name" value="<?= htmlspecialchars($settings['bank_name']) ?>" required>
      </div>
      <div class="form-row">
        <label>Nomor Rekening</label>
        <input type="text" name="no_rek" value="<?= htmlspecialchars($settings['no_rek']) ?>" required>
      </div>
      <div class="form-row">
        <label>a/n Rekening</label>
        <input type="text" name="atas_nama" value="<?= htmlspecialchars($settings['atas_nama']) ?>" required>
      </div>
      <div class="form-actions">
        <button type="submit" name="save_settings" class="btn-primary">Simpan Pengaturan</button>
      </div>
    </form>
  </div>

  <!-- Kelola Item Rincian Biaya -->
  <div class="card">
    <h3>Rincian (Fasilitas / Isi Biaya)</h3>

    <!-- Form tambah item -->
    <form method="post" action="" class="inline-form">
      <input type="text" name="item_text" placeholder="Tulis item rincian baru..." required>
      <button type="submit" name="add_item" class="btn-secondary">Tambah</button>
    </form>

    <!-- Tabel daftar item -->
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:60px;">#</th>
            <th>Item Rincian</th>
            <th style="width:120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($fasilitas)): ?>
            <tr><td colspan="3" style="text-align:center;">Belum ada item.</td></tr>
          <?php else: $no=1; foreach ($fasilitas as $f): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($f['item_text']) ?></td>
              <td>
                <a class="btn-danger" href="?del=<?= (int)$f['id'] ?>" onclick="return confirm('Hapus item ini?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>