<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/sidebar_peserta.php';
include '../templates/header.php';

// Ambil data user
$user_id = intval($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Peserta';

// Ambil data peserta
$peserta_query = $koneksi->query("SELECT * FROM peserta WHERE id = {$user_id} LIMIT 1");
$peserta = ($peserta_query && $peserta_query->num_rows > 0) ? $peserta_query->fetch_assoc() : null;
$nama = $peserta['full_name'] ?? $username;

// Ambil data pembayaran
$payRes = $koneksi->query("
    SELECT 
        (SELECT IFNULL(SUM(amount_paid), 0) 
         FROM payment_history 
         WHERE user_id = {$user_id} AND status='approved') AS total_bayar,
        (SELECT status 
         FROM payment_history 
         WHERE user_id = {$user_id} 
         ORDER BY created_at DESC LIMIT 1) AS last_status
");
$payRow = $payRes ? $payRes->fetch_assoc() : ['total_bayar' => 0, 'last_status' => null];
$total_bayar = $payRow['total_bayar'] ?? 0;
$status_pembayaran = $payRow['last_status'] ?? 'Belum Ada Pembayaran';

$total_biaya = 200000;
$sisa = $total_biaya - $total_bayar;
?>

<link rel="stylesheet" href="../assets/css/dashboard_peserta.css">

<main class="content">
  <h2>Halo, <?= htmlspecialchars($nama) ?> 👋</h2>
  <p>Selamat datang di halaman dashboard peserta Outbound PKL LPK Cipta Tungga Indonesia.</p>

  <!-- CARD INFORMASI -->
  <div class="cards">
    <div class="card">
      <strong>Informasi Profil</strong>
      <p><?= htmlspecialchars($peserta['full_name'] ?? '-') ?></p>
    </div>

    <div class="card">
      <strong>Sudah Dibayar</strong>
      <p>Rp <?= number_format($total_bayar, 0, ',', '.') ?></p>
    </div>

    <div class="card">
      <strong>Status Bayar</strong>
      <?php if ($sisa <= 0): ?>
        <span class="status-badge status-lunas">LUNAS ✅</span>
      <?php elseif ($status_pembayaran === 'pending'): ?>
        <span class="status-badge status-pending">Menunggu Verifikasi</span>
      <?php elseif ($status_pembayaran === 'rejected'): ?>
        <span class="status-badge status-reject">Ditolak ❌</span>
      <?php else: ?>
        <span class="status-badge status-none">Belum Bayar</span>
      <?php endif; ?>
    </div>
  </div>
</main>
  <!-- PENJELASAN KEGIATAN -->
  <section>
    <h3>Tentang Kegiatan Outbound</h3>
    <p>
      Kegiatan <strong>Outbound PKL</strong> ini bertujuan untuk meningkatkan kerja sama, kepemimpinan,
      serta membangun semangat kebersamaan antar peserta. Melalui kegiatan outdoor dan tantangan kelompok,
      peserta diharapkan dapat mengembangkan soft skill, memperkuat solidaritas, dan mendapatkan pengalaman
      baru yang menyenangkan di alam terbuka.
    </p>

    <div class="gallery">
      <div class="img-box"><img src="../assets/images/outbound2.jpg" alt="Kegiatan 1"></div>
      <div class="img-box"><img src="../assets/images/outbound3.jpg" alt="Kegiatan 2"></div>
      <div class="img-box"><img src="../assets/images/outbound4.jpg" alt="Kegiatan 3"></div>
      <div class="img-box"><img src="../assets/images/outbound1.jpg" alt="Kegiatan 4"></div>
    </div>
  </section>

