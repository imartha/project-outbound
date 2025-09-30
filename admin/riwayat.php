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

<div class="content">
    <h2>Riwayat Pembayaran</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Username</th>
            <th>Jumlah</th>
            <th>Tanggal</th>
            <th>Metode</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td>Rp <?= number_format($row['amount_paid'], 0, ',', '.') ?></td>
                <td><?= $row['transfer_date'] ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
