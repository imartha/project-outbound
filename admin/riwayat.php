<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

$query = "SELECT ph.id, u.username, ph.amount_paid, ph.transfer_date, ph.payment_method
          FROM payment_history ph
          JOIN users u ON ph.user_id = u.id
          ORDER BY ph.transfer_date DESC";
$result = $koneksi->query($query);
?>
<link rel="stylesheet" href="../assets/css/riwayat.css">

  <div class="content">
  <div class="header-section">
    <h2>Riwayat Pembayaran</h2>
    <a href="cetak_riwayat.php" target="_blank" class="btn-cetak">🖨️ Cetak PDF</a>

  </div>

  <table class="tbl">
    <thead>
      <tr>
        <th>Username</th>
        <th>Jumlah</th>
        <th>Tanggal</th>
        <th>Metode</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td>Rp <?= number_format($row['amount_paid'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($row['transfer_date']) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4" class="no-data">Belum ada pembayaran.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
