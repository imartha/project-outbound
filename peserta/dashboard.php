<?php
include "../utils/auth_check.php";
include "../koneksi.php";
$page_title = "Dashboard Peserta";

$user_id = $_SESSION['user_id'];

// ambil data peserta sesuai user_id
$stmt = $koneksi->prepare("SELECT * FROM peserta WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$peserta = $stmt->get_result()->fetch_assoc();

// cek status pembayaran terakhir
$stmt2 = $koneksi->prepare("SELECT IFNULL(SUM(amount_paid),0) as paid FROM payment_history WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$paid = $stmt2->get_result()->fetch_assoc()['paid'] ?? 0;
?>
<?php include "../templates/header.php"; ?>
<div class="layout">
    <?php include "../templates/sidebar_peserta.php"; ?>
    <main class="content">
        <h2>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?></h2>

        <div class="profile-card">
            <img src="/assets/img/<?= ($peserta['profile_picture'] ?? 'default_male.png') ?>" alt="pp" width="120">
            <div>
                <h3><?= htmlspecialchars($peserta['full_name'] ?? '—') ?></h3>
                <p>HP: <?= htmlspecialchars($peserta['phone'] ?? '—') ?></p>
                <p>Sekolah: <?= htmlspecialchars($peserta['nama_sekolah'] ?? '—') ?></p>
                <p>Gender: <?= ($peserta['gender'] === 'L') ? 'Laki-laki' : 'Perempuan' ?></p>
            </div>
        </div>

        <section>
            <h3>Status Pembayaran</h3>
            <p>Sudah dibayar: <strong>Rp <?= number_format($paid,0,',','.') ?></strong></p>
            <p><a href="/peserta/pembayaran.php">Lakukan Pembayaran / Upload Bukti</a></p>
        </section>

        <section>
            <h3>Ringkasan Jadwal</h3>
            <ul>
                <?php
                $r = $koneksi->query("SELECT tanggal, kegiatan, lokasi FROM jadwal_kegiatan ORDER BY tanggal LIMIT 5");
                while ($row = $r->fetch_assoc()):
                ?>
                <li><?= date('d M Y', strtotime($row['tanggal'])) ?> — <?= htmlspecialchars($row['kegiatan']) ?> (<?= htmlspecialchars($row['lokasi']) ?>)</li>
                <?php endwhile; ?>
            </ul>
        </section>
    </main>
</div>
<?php include "../templates/footer.php"; ?>
