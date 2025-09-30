<?php
include "../utils/auth_check.php";
include "../koneksi.php";
$page_title = "Admin Dashboard";

/* ===== Statistik ===== */
$total_peserta = 0;
$total_payment = 0;
$pending = 0;

if ($res = $koneksi->query("SELECT COUNT(*) AS cnt FROM peserta")) {
  $row = $res->fetch_assoc(); $total_peserta = (int)($row['cnt'] ?? 0);
  $res->free();
}

if ($res = $koneksi->query("SELECT IFNULL(SUM(amount_paid),0) AS tot FROM payment_history")) {
  $row = $res->fetch_assoc(); $total_payment = (int)($row['tot'] ?? 0);
  $res->free();
}

/* Jika punya kolom status di payment_history, ganti WHERE 1 sesuai kebutuhan */
if ($res = $koneksi->query("SELECT COUNT(*) AS cnt FROM payment_history WHERE 1")) {
  $row = $res->fetch_assoc(); $pending = (int)($row['cnt'] ?? 0);
  $res->free();
}

/* ===== Peserta (urut ASC sesuai input) ===== */
$latest = $koneksi->query("
  SELECT id, full_name, phone, nama_sekolah
  FROM peserta
  ORDER BY id ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?? "Outbound Management" ?></title>

  <!-- CSS utama dashboard -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/dashboard_admin.css">
</head>
<body>

<?php include "../templates/header.php"; ?>

<div class="layout">
  <?php include "../templates/sidebar_admin.php"; ?>

  <main class="content">
    <div class="content-wrap">
      <h2>Selamat datang, Admin</h2>

      <div class="cards">
        <div class="card">
          Total Peserta:
          <strong><?= number_format($total_peserta, 0, ',', '.') ?></strong>
        </div>
        <div class="card">
          Total Pemasukan:
          <strong>Rp <?= number_format($total_payment, 0, ',', '.') ?></strong>
        </div>
        <div class="card">
          Pembayaran Tercatat:
          <strong><?= number_format($pending, 0, ',', '.') ?></strong>
        </div>
      </div>

      <h3>Daftar Peserta</h3>
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Telepon</th>
            <th>Sekolah</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($latest && $latest->num_rows > 0): ?>
            <?php $i = 1; while ($row = $latest->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['nama_sekolah']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;color:#667085;">
                Belum ada data peserta.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

</body>
</html>
