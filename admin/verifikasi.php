<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Kalau ada aksi
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi === 'setujui') {
        $koneksi->query("UPDATE payment_history SET status='approved' WHERE id=$id");
    } elseif ($aksi === 'tolak') {
        $koneksi->query("UPDATE payment_history SET status='rejected' WHERE id=$id");
    }
    header("Location: verifikasi.php");
    exit;
}

// Ambil data pembayaran
$query = "SELECT ph.id, u.username, ph.amount_paid, 
                 (SELECT SUM(amount_paid) 
                  FROM payment_history 
                  WHERE user_id = u.id AND status='approved') as total_sudah_dibayar,
                 ph.payment_method, ph.transfer_date, ph.transfer_proof_path, ph.status
          FROM payment_history ph
          JOIN users u ON ph.user_id = u.id
          ORDER BY ph.transfer_date DESC";
$result = $koneksi->query($query);

if (!$result) {
    die("Query error: " . $koneksi->error);
}
?>

<div class="content content--tight">
    <h2>Verifikasi Pembayaran</h2>
    <table class="tbl">
        <thead>
            <tr>
                <th>Nama Peserta</th>
                <th>Jumlah Pembayaran</th>
                <th>Total Sudah Dibayar</th>
                <th>Metode</th>
                <th>Tanggal Transaksi</th>
                <th>Bukti</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td>Rp <?= number_format($row['amount_paid'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($row['total_sudah_dibayar'] ?? 0, 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td><?= $row['transfer_date'] ?></td>
                    <td>
                        <?php if ($row['transfer_proof_path']): ?>
                            <a href="../uploads/<?= $row['transfer_proof_path'] ?>" target="_blank">Lihat Bukti</a>
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="verifikasi.php?aksi=setujui&id=<?= $row['id'] ?>" onclick="return confirm('Setujui pembayaran ini?')">✅ Setujui</a> | 
                            <a href="verifikasi.php?aksi=tolak&id=<?= $row['id'] ?>" onclick="return confirm('Tolak pembayaran ini?')">❌ Tolak</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
