<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_peserta.php';

/* =========================
   AMBIL KONFIGURASI BIAYA
========================= */
$settings = [
  'total_biaya' => 0,
  'bank_name'   => '',
  'no_rek'      => '',
  'atas_nama'   => ''
];

if ($res = $koneksi->query("SELECT total_biaya, bank_name, no_rek, atas_nama FROM biaya_settings WHERE id=1")) {
  if ($res->num_rows) $settings = $res->fetch_assoc();
  $res->free();
}
$total_biaya = (int)($settings['total_biaya'] ?? 0);

/* =========================
   DATA USER & PROGRES BAYAR
========================= */
$user_id = (int)($_SESSION['user_id'] ?? 0);

// total approved
$q = $koneksi->prepare("SELECT IFNULL(SUM(amount_paid),0) AS total FROM payment_history WHERE user_id=? AND status='approved'");
$q->bind_param('i', $user_id);
$q->execute();
$sumRes = $q->get_result()->fetch_assoc();
$q->close();
$sudah_dibayar = (int)($sumRes['total'] ?? 0);

$sisa = max(0, $total_biaya - $sudah_dibayar);

// status terakhir
$q2 = $koneksi->prepare("SELECT status FROM payment_history WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$q2->bind_param('i', $user_id);
$q2->execute();
$stRes = $q2->get_result()->fetch_assoc();
$q2->close();
$status = $stRes['status'] ?? ($sudah_dibayar > 0 ? 'proses' : 'belum bayar');

/* =========================
   HANDLE SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payment_method = $_POST['payment_method'] ?? '';
  $transfer_date  = $_POST['transfer_date'] ?? '';
  $status_bayar   = 'pending';

  // Nominal yang dikirim user (jika cicilan)
  $amount_input = isset($_POST['amount_paid']) ? (int)$_POST['amount_paid'] : 0;

  // Validasi nominal berdasar metode
  if ($payment_method === 'penuh') {
    // Bayar sisa saja (biar tidak overpay)
    $amount_paid = $sisa;
  } elseif ($payment_method === 'cicilan') {
    // Cicilan bebas: >0 dan <= sisa
    if ($amount_input <= 0 || $amount_input > $sisa) {
      echo "<script>alert('Nominal cicilan tidak valid. Harus > 0 dan tidak melebihi sisa.'); history.back();</script>";
      exit;
    }
    $amount_paid = $amount_input;
  } else {
    echo "<script>alert('Pilih metode pembayaran.'); history.back();</script>";
    exit;
  }

  if ($sisa <= 0) {
    echo "<script>alert('Tagihan sudah lunas.'); window.location='pembayaran.php';</script>";
    exit;
  }

  // Upload bukti transfer
  $target_dir = "../uploads/";
  if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

  $orig_name  = basename($_FILES["transfer_proof"]["name"] ?? '');
  $ext        = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
  $allowed    = ['jpg','jpeg','png','pdf'];

  if (!$orig_name || !in_array($ext, $allowed)) {
    echo "<script>alert('Format bukti harus jpg/jpeg/png/pdf.'); history.back();</script>";
    exit;
  }

  $file_name   = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/','', $orig_name);
  $target_file = $target_dir . $file_name;

  if (!move_uploaded_file($_FILES["transfer_proof"]["tmp_name"], $target_file)) {
    echo "<script>alert('Gagal mengunggah bukti transfer.'); history.back();</script>";
    exit;
  }

  // Simpan ke DB
  $stmt = $koneksi->prepare("INSERT INTO payment_history (user_id, amount_paid, transfer_date, payment_method, transfer_proof_path, status)
                             VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("idssss", $user_id, $amount_paid, $transfer_date, $payment_method, $file_name, $status_bayar);
  $stmt->execute();
  $stmt->close();

  echo "<script>alert('Pembayaran berhasil dikirim. Menunggu verifikasi admin.'); window.location='pembayaran.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Pembayaran Outbound PKL</title>
  <link rel="stylesheet" href="../assets/css/pembayaran.css">
</head>
<body>
<div class="main-content">
  <div class="container">
    <h3>Pembayaran Outbound PKL</h3>

    <!-- STATUS -->
    <?php if ($status === 'pending'): ?>
      <div class="alert alert-warning">Menunggu verifikasi admin. Mohon tunggu konfirmasi.</div>
    <?php elseif ($status === 'rejected'): ?>
      <div class="alert alert-danger">Pembayaran ditolak. Silakan unggah ulang bukti pembayaran.</div>
    <?php elseif ($sisa > 0): ?>
      <div class="alert alert-danger">Tagihan belum lunas. Silakan lakukan pembayaran berikutnya.</div>
    <?php else: ?>
      <div class="alert alert-success">Pembayaran sudah lunas. Terima kasih!</div>
    <?php endif; ?>

    <!-- RINCIAN PEMBAYARAN -->
    <div class="rincian-box">
      <h4>Rincian Pembayaran</h4>
      <div class="rincian-row">
        <div><strong>Total Biaya:</strong><br><span class="text-blue">Rp <?= number_format($total_biaya, 0, ',', '.') ?></span></div>
        <div><strong>Sudah Dibayar:</strong><br><span class="text-green">Rp <?= number_format($sudah_dibayar, 0, ',', '.') ?></span></div>
      </div>
      <div class="rincian-row">
        <div><strong>Sisa Pembayaran:</strong><br><span class="text-red">Rp <?= number_format($sisa, 0, ',', '.') ?></span></div>
        <div><strong>Status:</strong><br>
          <?php if ($sisa <= 0): ?>
            <span class="badge badge-success">LUNAS</span>
          <?php else: ?>
            <span class="badge badge-warning"><?= strtoupper($status) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- FORM PEMBAYARAN -->
    <?php if ($sisa > 0): ?>
      <div class="form-box">
        <h4>Form Pembayaran</h4>
        <form method="POST" enctype="multipart/form-data" onsubmit="return syncAmountBeforeSubmit()">
          <label>Metode Pembayaran</label>
          <select name="payment_method" id="payment_method" required onchange="toggleAmount()">
            <option value="">-- Pilih Metode --</option>
            <option value="penuh">Bayar Lunas (otomatis: sisa Rp <?= number_format($sisa, 0, ',', '.') ?>)</option>
            <option value="cicilan">Cicilan (isi nominal sendiri)</option>
          </select>

          <!-- Nominal: muncul hanya jika cicilan -->
          <div id="cicilan_group" style="display:none;">
            <label>Nominal Cicilan</label>
            <input
              type="number"
              name="amount_paid"
              id="amount_paid"
              min="1"
              max="<?= $sisa ?>"
              placeholder="Contoh: 50000"
              inputmode="numeric"
            >
            <small>Sisa saat ini: Rp <?= number_format($sisa,0,',','.') ?> (maksimal cicilan = sisa)</small>
          </div>

          <label>Tanggal Transfer</label>
          <input type="date" name="transfer_date" required>

          <label>Upload Bukti Transfer (jpg/jpeg/png/pdf)</label>
          <input type="file" name="transfer_proof" accept=".jpg,.jpeg,.png,.pdf" required>

          <button type="submit" class="btn-submit">Kirim Pembayaran</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleAmount() {
  const select = document.getElementById('payment_method');
  const group  = document.getElementById('cicilan_group');
  if (select.value === 'cicilan') {
    group.style.display = 'block';
  } else {
    group.style.display = 'none';
  }
}

// Pastikan saat submit, jika pilih cicilan harus isi nominal valid
function syncAmountBeforeSubmit() {
  const select = document.getElementById('payment_method');
  if (select.value === 'cicilan') {
    const input = document.getElementById('amount_paid');
    const val = parseInt(input.value || '0', 10);
    const max = parseInt(input.getAttribute('max'), 10);
    if (!val || val <= 0 || val > max) {
      alert('Nominal cicilan tidak valid. Harus > 0 dan tidak melebihi sisa.');
      return false;
    }
  }
  return true;
}
</script>
</body>
</html>
