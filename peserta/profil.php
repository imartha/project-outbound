<?php
include '../koneksi.php';
include '../utils/auth_check.php';
include '../templates/header.php';
include '../templates/sidebar_peserta.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sesi login tidak ditemukan. Silakan login ulang.'); window.location='../login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data peserta
$query = $koneksi->prepare("SELECT * FROM peserta WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<script>alert('Data peserta tidak ditemukan.'); window.location='dashboard.php';</script>";
    exit;
}

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = $_POST['full_name'];
    $phone       = $_POST['phone'];
    $nama_sekolah= $_POST['nama_sekolah'];
    $jurusan     = $_POST['jurusan'];
    $address     = $_POST['address'];
    $gender      = $_POST['gender'];
    $profile_picture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $file_name;
        }
    }

    $update = $koneksi->prepare("UPDATE peserta 
        SET full_name=?, phone=?, nama_sekolah=?, jurusan=?, address=?, gender=?, profile_picture=? 
        WHERE id=?");
    $update->bind_param("sssssssi", $full_name, $phone, $nama_sekolah, $jurusan, $address, $gender, $profile_picture, $user_id);

    if ($update->execute()) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui profil.');</script>";
    }
}
?>

<link rel="stylesheet" href="../assets/css/profil_peserta.css?v=<?php echo time(); ?>">

<div class="profile-container">
  <div class="profile-card">
    <div class="profile-photo">
<img src="<?= htmlspecialchars(
      !empty($user['profile_picture'])
                ? '../uploads/'.$user['profile_picture']
                : ($user['gender']==='P'
                ? '../assets/images/profile_female.png'
                : '../assets/images/profile_male.png')
  ) ?>"
  alt="Foto Profil">
      <label class="upload-btn">
        Ganti Foto
        <input type="file" name="profile_picture" accept="image/*">
      </label>
    </div>

    <div class="profile-info">
      <h3><?= htmlspecialchars($user['full_name']) ?></h3>
      <p><strong>No. HP:</strong> <?= htmlspecialchars($user['phone']) ?></p>
      <p><strong>Sekolah:</strong> <?= htmlspecialchars($user['nama_sekolah']) ?></p>
      <p><strong>Jurusan:</strong> <?= htmlspecialchars($user['jurusan']) ?></p>
      <p><strong>Alamat:</strong> <?= htmlspecialchars($user['address']) ?></p>
    </div>
  </div>

  <form id="editForm" method="POST" enctype="multipart/form-data" class="edit-form">
    <h3>Informasi Pribadi</h3>

    <div class="form-grid">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
      </div>

      <div class="form-group">
        <label>No. HP</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
      </div>

      <div class="form-group">
        <label>Nama Sekolah</label>
        <input type="text" name="nama_sekolah" value="<?= htmlspecialchars($user['nama_sekolah']) ?>" required>
      </div>

      <div class="form-group">
        <label>Jurusan</label>
        <input type="text" name="jurusan" value="<?= htmlspecialchars($user['jurusan']) ?>" required>
      </div>

      <div class="form-group">
        <label>Jenis Kelamin</label>
        <select name="gender">
          <option value="L" <?= $user['gender'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="P" <?= $user['gender'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>

      <div class="form-group full-width">
        <label>Alamat</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
      </div>
    </div>

    <button type="submit" class="save-btn">Simpan Perubahan</button>
  </form>
</div>
