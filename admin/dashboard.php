<?php
include "../utils/auth_check.php";
include "../koneksi.php";
$page_title = "Admin Dashboard";

/* ===== Statistik ===== */
$total_peserta = 0;
$total_payment = 0;
$total_tercatat = 0;
$total_pending = 0;

if ($res = $koneksi->query("SELECT COUNT(*) AS cnt FROM peserta")) {
  $row = $res->fetch_assoc(); 
  $total_peserta = (int)($row['cnt'] ?? 0);
  $res->free();
}

if ($res = $koneksi->query("SELECT IFNULL(SUM(amount_paid),0) AS tot FROM payment_history WHERE status='approved'")) {
  $row = $res->fetch_assoc(); 
  $total_payment = (int)($row['tot'] ?? 0);
  $res->free();
}

if ($res = $koneksi->query("SELECT COUNT(*) AS cnt FROM payment_history")) {
  $row = $res->fetch_assoc(); 
  $total_tercatat = (int)($row['cnt'] ?? 0);
  $res->free();
}

/* === Tambahan: Menunggu Verifikasi === */
if ($res = $koneksi->query("SELECT COUNT(*) AS cnt FROM payment_history WHERE status='pending'")) {
  $row = $res->fetch_assoc(); 
  $total_pending = (int)($row['cnt'] ?? 0);
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

  <link rel="stylesheet" href="../assets/css/dashboard_admin.css">
</head>
<body>

<?php include "../templates/header.php"; ?>

<div class="layout">
  <?php include "../templates/sidebar_admin.php"; ?>

  <main class="content">
    <div class="content-wrap">
      <h2>Ringkasan Dashboard</h2>

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
          <strong><?= number_format($total_tercatat, 0, ',', '.') ?></strong>
        </div>
        <div class="card card-warning">
          Menunggu Verifikasi:
          <strong><?= number_format($total_pending, 0, ',', '.') ?></strong>
        </div>
      </div>

     