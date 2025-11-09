<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_admin.php';

// Tambah admin baru (PAKAI HASH)
if (isset($_POST['tambah'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        // Cek duplikasi username
        $cek = $koneksi->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $cek->close();
            echo "<script>alert('Username sudah terdaftar. Gunakan username lain.'); window.location='kelola_admin.php';</script>";
            exit;
        }
        $cek->close();

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $koneksi->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, 'admin', NOW())");
        $stmt->bind_param("ss", $username, $hash);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: kelola_admin.php");
    exit;
}

// Hapus admin
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kelola_admin.php");
    exit;
}

// Ambil semua admin
$result = $koneksi->query("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Admin</title>
  <link rel="stylesheet" href="../assets/css/kelola_admin.css?v=<?= time() ?>">
</head>
<body>
  <div id="kelola-admin" class="container">
    <h2>Kelola Admin</h2>

    <!-- Form Tambah Admin -->
    <form method="POST" autocomplete="off">
      <input type="text" name="username" placeholder="Username baru" required>
      <input type="password" name="password" placeholder="Password baru" required>
      <button type="submit" name="tambah" class="btn-tambah">+ Tambah Admin</button>
    </form>

    <!-- Tabel Admin -->
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <?php if ($row['username'] !== ($_SESSION['username'] ?? '')): // cegah hapus diri sendiri ?>
                    <a href="?hapus=<?= (int)$row['id'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin hapus admin ini?')">Hapus</a>
                  <?php else: ?>
                    <span class="you-pill">(Anda)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="no-data">Belum ada admin terdaftar.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
