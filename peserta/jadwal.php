<?php
session_start();
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_peserta.php';

$page_title = "Jadwal Kegiatan Peserta";

// Ambil jadwal urut berdasarkan tanggal dan waktu
$jadwal = $koneksi->query("
  SELECT tanggal, waktu, kegiatan, lokasi, keterangan
  FROM jadwal_kegiatan
  ORDER BY tanggal ASC, waktu ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="../assets/css/jadwal_peserta.css">
</head>

<body>
<div class="main-content">
  <h3 class="subjudul">Jadwal Kegiatan</h3>

  <?php
  if ($jadwal && $jadwal->num_rows > 0):
      $tanggal_sebelumnya = null;
      while ($row = $jadwal->fetch_assoc()):
          $tanggal = date('l, d F Y', strtotime($row['tanggal'])); // format tanggal
          
          // Jika tanggal berubah, tampilkan pemisah tanggal
          if ($tanggal != $tanggal_sebelumnya):
              if ($tanggal_sebelumnya !== null) echo "</tbody></table>"; // tutup tabel sebelumnya
  ?>
              <div class="tanggal-separator"><?= htmlspecialchars($tanggal) ?></div>
              <div class="table-wrap">
              <table class="table-jadwal">
                <thead>
                  <tr>
                    <th>Waktu</th>
                    <th>Kegiatan</th>
                    <th>Lokasi</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
  <?php
          endif;
  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['waktu']) ?></td>
                    <td><?= htmlspecialchars($row['kegiatan']) ?></td>
                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                  </tr>
  <?php
          $tanggal_sebelumnya = $tanggal;
      endwhile;
      echo "</tbody></table></div>"; // tutup tabel terakhir
  else:
  ?>
    <p class="no-data">Belum ada jadwal kegiatan.</p>
  <?php endif; ?>
</div>
</body>
</html>
