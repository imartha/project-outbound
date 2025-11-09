<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_peserta.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

/* ============== Ambil total biaya dari biaya_settings ============== */
$settings = ['total_biaya' => 0];
if ($res = $koneksi->query("SELECT total_biaya FROM biaya_settings WHERE id=1")) {
  if ($res->num_rows) $settings = $res->fetch_assoc();
  $res->free();
}
$total_biaya = (int)($settings['total_biaya'] ?? 0);

/* ============== Data riwayat pembayaran user ini ============== */
$sql = "SELECT id, amount_paid, transfer_date, payment_method, transfer_proof_path, status, created_at
        FROM payment_history
        WHERE user_id = ?
        ORDER BY created_at DESC";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* ============== Hitung total approved & sisa ============== */
$sqlSum = "SELECT 
              COALESCE(SUM(CASE WHEN status='approved' THEN amount_paid END),0) AS total_approved
           FROM payment_history
           WHERE user_id = ?";
$stmt2 = $koneksi->prepare($sqlSum);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$agg = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$total_approved = (int)($agg['total_approved'] ?? 0);
$sisa = max(0, $total_biaya - $total_approved);

/* ============== Helper tampilan ============== */
function rupiah($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function badgeStatus($s){
  $s = strtolower(trim((string)$s));
  switch ($s){
    case 'approved': return '<span class="badge badge-success">APPROVED</span>';
    case 'pending':  return '<span class="badge badge-warning">PENDING</span>';
    case 'rejected': return '<span class="badge badge-danger">REJECTED</span>';
    default:         return '<span class="badge badge-secondary">'.htmlspecialchars(strtoupper($s)).'</span>';
  }
}
?>

<link rel="stylesheet" href="../assets/css/riwayat_pembayaran.css">

<div class="main-content">
  <div class="container">
    <div class="section-header">
      <h3>Riwayat Pembayaran</h3>
      <a href="pembayaran.php" class="btn-utama">Lakukan Pembayaran</a>
    </div>

    <!-- Ringkasan -->
    <div class="info-cards">
      <div class="icard">
        <h5>Total Biaya</h5>
        <div class="val biru"><?= rupiah($total_biaya) ?></div>
      </div>
      <div class="icard">
        <h5>Sudah Dibayar (Approved)</h5>
        <div class="val hijau"><?= rupiah($total_approved) ?></div>
      </div>
      <div class="icard">
        <h5>Sisa Pembayaran</h5>
        <div class="val merah"><?= rupiah($sisa) ?></div>
      </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Jumlah Pembayaran</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Bukti Transfer</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="5" class="text-center">Belum ada riwayat pembayaran.</td></tr>
          <?php else: ?>
            <?php while($row = $result->fetch_assoc()):
              // Tanggal: pakai transfer_date jika ada, fallback ke created_at
              $tgl = $row['transfer_date'] ?: substr($row['created_at'], 0, 10);

              // Mapping metode sesuai logika baru (cicilan bebas)
              $metode = ($row['payment_method']==='penuh') ? 'Lunas (Bayar Sisa)' : (($row['payment_method']==='cicilan') ? 'Cicilan' : ucfirst($row['payment_method']));

              // Amankan path bukti
              $proofName = $row['transfer_proof_path'] ? basename($row['transfer_proof_path']) : '';
              $publicPath = $proofName ? '../uploads/' . $proofName : '';
              $absPath = $proofName ? realpath(__DIR__ . '/../uploads/' . $proofName) : '';
              $isExist = $absPath && is_file($absPath);
              $isPdf = $proofName && preg_match('/\.pdf$/i', $proofName);
            ?>
              <tr>
                <td><?= htmlspecialchars(date('d-m-Y', strtotime($tgl))) ?></td>
                <td><?= rupiah($row['amount_paid']) ?></td>
                <td><?= htmlspecialchars($metode) ?></td>
                <td><?= badgeStatus($row['status']) ?></td>
                <td>
                  <?php if ($isExist): ?>
                    <a href="<?= htmlspecialchars($publicPath) ?>" target="_blank" class="link-proof">
                      <?= $isPdf ? 'Lihat PDF' : 'Lihat Gambar' ?>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
