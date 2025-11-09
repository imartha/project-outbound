<?php
include '../templates/header.php';
include '../templates/sidebar_peserta.php';
include '../koneksi.php';

$settings = $koneksi->query("SELECT total_biaya, bank_name, no_rek, atas_nama FROM biaya_settings WHERE id=1")->fetch_assoc();
$fasilitas = $koneksi->query("SELECT item_text FROM fasilitas_biaya ORDER BY sort_order ASC, id ASC");
?>

<link rel="stylesheet" href="../assets/css/rincian_biaya.css">
<div class="main-content">
  <div class="container">
    <h3>Rincian Biaya Outbound</h3>

    <div class="rincian-card">
      <p class="muted">Total Biaya Pendaftaran</p>
      <div class="biaya-total">
        <span class="harga">Rp <?= number_format((int)$settings['total_biaya'],0,',','.') ?></span>
        <span class="keterangan">(sudah termasuk fasilitas di bawah)</span>
      </div>

      <ul class="fasilitas">
        <?php while($row = $fasilitas->fetch_assoc()): ?>
          <li><?= htmlspecialchars($row['item_text']) ?></li>
        <?php endwhile; ?>
      </ul>

      <div class="metode-pembayaran">
        <h4>Metode Pembayaran</h4>
        <div class="payment-box">
          <p><strong>Bank <?= htmlspecialchars($settings['bank_name']) ?></strong></p>
          <p>Nomor Rekening: <span class="rekening"><?= htmlspecialchars($settings['no_rek']) ?></span></p>
          <p>a/n <?= htmlspecialchars($settings['atas_nama']) ?></p>
          <p class="note">Setelah transfer, unggah bukti pembayaran di halaman <a href="pembayaran.php">pembayaran</a>.</p>
        </div>
      </div>
    </div>
  </div>
</div>
