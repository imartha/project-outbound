<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

/* ===== Aksi Approve / Reject ===== */
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

/* ===== Keyword pencarian nama lengkap peserta ===== */
$q = trim($_GET['q'] ?? '');

/* Base query: join ke peserta agar bisa pakai full_name */
$sqlBase = "
SELECT 
    ph.id,
    p.full_name,
    u.username,
    ph.amount_paid,
    (SELECT COALESCE(SUM(amount_paid),0)
       FROM payment_history
      WHERE user_id = u.id AND status='approved') AS total_sudah_dibayar,
    ph.payment_method,
    ph.transfer_date,
    ph.transfer_proof_path,
    ph.status
FROM payment_history ph
JOIN users u   ON ph.user_id = u.id
LEFT JOIN peserta p ON p.id = u.id   -- ganti ke: ON p.user_id = u.id jika itu skemamu
";

/* Urutan: pending dulu, lalu terbaru */
$order = " ORDER BY (ph.status='pending') DESC, ph.transfer_date DESC";

/* Eksekusi: pakai prepared statement jika ada keyword */
if ($q !== '') {
    $sql = $sqlBase . " WHERE p.full_name LIKE ? " . $order;
    $stmt = $koneksi->prepare($sql);
    $like = "%{$q}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = $sqlBase . $order;
    $result = $koneksi->query($sql);
}

if (!$result) {
    die("Query error: " . $koneksi->error);
}
?>

<link rel="stylesheet" href="../assets/css/verifikasi.css">

<div class="content content--tight">
    <h2>Verifikasi Pembayaran</h2>

 <form method="get" action="" class="form-cari">
  <input type="text" name="q" placeholder="Cari nama peserta (full name)..." value="<?= htmlspecialchars($q) ?>">
  <button type="submit" class="btn-cari">Cari</button>
  <?php if ($q !== ''): ?>
    <a href="verifikasi.php" class="btn-reset">Reset</a>
  <?php endif; ?>
</form>


    <table class="tbl">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jumlah</th>
                <th>Dibayar</th>
                <th>Metode</th>
                <th>Tanggal</th>
                <th>Bukti</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name'] ?? $row['username']) ?></td>
                    <td>Rp <?= number_format((float)$row['amount_paid'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format((float)($row['total_sudah_dibayar'] ?? 0), 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td><?= htmlspecialchars($row['transfer_date']) ?></td>
                    <td>
                        <?php if (!empty($row['transfer_proof_path'])): ?>
                            <a href="../uploads/<?= rawurlencode($row['transfer_proof_path']) ?>" target="_blank">Lihat Bukti</a>
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="verifikasi.php?aksi=setujui&id=<?= (int)$row['id'] ?>" 
                               onclick="return confirm('Setujui pembayaran ini?')">✅ Setujui</a> | 
                            <a href="verifikasi.php?aksi=tolak&id=<?= (int)$row['id'] ?>" 
                               onclick="return confirm('Tolak pembayaran ini?')">❌ Tolak</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align:center;">
                    <?= $q !== '' ? 'Tidak ada pembayaran dengan nama tersebut.' : 'Tidak ada pembayaran.' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
