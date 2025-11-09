<?php
session_start();
include "koneksi.php";

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $nama_sekolah = trim($_POST['nama_sekolah'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender'] ?? ''; // 'L' atau 'P'

    // Validasi sederhana
    if ($username === '' || $password === '' || $password2 === '') {
        $errors[] = "Username dan password wajib diisi.";
    }
    if ($password !== $password2) {
        $errors[] = "Konfirmasi password tidak sama.";
    }
    if (!in_array($gender, ['L','P'])) {
        $errors[] = "Pilih jenis kelamin (L/P).";
    }

    // Cek username sudah dipakai?
    if (empty($errors)) {
        $stmt = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->fetch_assoc()) {
            $errors[] = "Username sudah digunakan.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Mulai transaksi biar konsisten antara users & peserta
        $koneksi->begin_transaction();
        try {
            // 1) Insert ke users (role default: peserta)
            $stmt1 = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'peserta')");
            $stmt1->bind_param("ss", $username, $password_hash);
            $stmt1->execute();
            $new_user_id = $stmt1->insert_id;
            $stmt1->close();

            // 2) Insert ke peserta (id sama dengan users.id)
            $stmt2 = $koneksi->prepare("
                INSERT INTO peserta (id, full_name, phone, nama_sekolah, jurusan, address, gender, profile_picture)
                VALUES (?, ?, ?, ?, ?, ?, ?, NULL)
            ");
            $stmt2->bind_param("issssss", $new_user_id, $full_name, $phone, $nama_sekolah, $jurusan, $address, $gender);
            $stmt2->execute();
            $stmt2->close();

            // Commit
            $koneksi->commit();

            // Auto-login
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'peserta';

            header("Location: peserta/dashboard.php");
            exit;

        } catch (Exception $e) {
            $koneksi->rollback();
            $errors[] = "Terjadi kesalahan saat membuat akun. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Akun Outbound</title>
    <link rel="stylesheet" href="assets/css/register.css"><!-- pakai css yang sama kalau mau -->
</head>
<body>
<div class="login-box">
    <h2>Buat Akun</h2>

    <?php if (!empty($errors)): ?>
        <div style="color:#b00020;margin-bottom:8px;">
            <?php foreach($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Username*</label><br>
        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br><br>

        <label>Password*</label><br>
        <input type="password" name="password" required><br><br>

        <label>Ulangi Password*</label><br>
        <input type="password" name="password2" required><br><br>

        <label>Nama Lengkap</label><br>
        <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"><br><br>

        <label>No. HP</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"><br><br>

        <label>Nama Sekolah</label><br>
        <input type="text" name="nama_sekolah" value="<?= htmlspecialchars($_POST['nama_sekolah'] ?? '') ?>"><br><br>

        <label>Jurusan</label><br>
        <input type="text" name="jurusan" value="<?= htmlspecialchars($_POST['jurusan'] ?? '') ?>"><br><br>

        <label>Alamat</label><br>
        <textarea name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea><br><br>

        <label>Jenis Kelamin*</label><br>
        <select name="gender" required>
            <option value="">-- pilih --</option>
            <option value="L" <?= (($_POST['gender'] ?? '')==='L'?'selected':'') ?>>Laki-laki</option>
            <option value="P" <?= (($_POST['gender'] ?? '')==='P'?'selected':'') ?>>Perempuan</option>
        </select><br><br>

        <button type="submit">Daftar</button>
    </form>

    <p style="margin-top:12px;">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
</body>
</html>
