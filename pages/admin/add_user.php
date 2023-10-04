<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah admin masuk atau belum
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}

// Inisialisasi variabel
$username = $password = $confirm_password = $nama_petugas = '';
$id_level = $username_err = $password_err = $confirm_password_err = $add_err = '';

// Query untuk mendapatkan daftar level
$sql_level = "SELECT id_level, level FROM tb_level";
$result_level = $mysqli->query($sql_level);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validasi Nama Petugas
  if (empty(trim($_POST['nama_petugas']))) {
    $nama_petugas_err = 'Silakan masukkan nama petugas.';
  } else {
    $nama_petugas = trim($_POST['nama_petugas']);
  }

  // Validasi Username
  if (empty(trim($_POST['username']))) {
    $username_err = 'Silakan masukkan username.';
  } else {
    $sql = "SELECT id_petugas FROM tb_petugas WHERE username = ?";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('s', $param_username);
      $param_username = trim($_POST['username']);
      if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
          $username_err = 'Username sudah digunakan.';
        } else {
          $username = trim($_POST['username']);
        }
      } else {
        $add_err = 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }

  // Validasi Password
  if (empty(trim($_POST['password']))) {
    $password_err = 'Silakan masukkan password.';
  } elseif (strlen(trim($_POST['password'])) < 6) {
    $password_err = 'Password harus memiliki setidaknya 6 karakter.';
  } else {
    $password = trim($_POST['password']);
  }

  // Validasi Konfirmasi Password
  if (empty(trim($_POST['confirm_password']))) {
    $confirm_password_err = 'Silakan konfirmasi password.';
  } else {
    $confirm_password = trim($_POST['confirm_password']);
    if ($password != $confirm_password) {
      $confirm_password_err = 'Konfirmasi password tidak cocok.';
    }
  }

  // Validasi Level
  if (empty($_POST['id_level'])) {
    $id_level_err = 'Silakan pilih level.';
  } else {
    $id_level = $_POST['id_level'];
  }

  // Periksa semua error validasi sebelum memasukkan data baru
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($id_level_err)) {
    // Persiapkan pernyataan INSERT
    $sql = "INSERT INTO tb_petugas (nama_petugas, username, password, id_level) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('sssi', $param_nama_petugas, $param_username, $param_password, $id_level);
      $param_nama_petugas = $nama_petugas;
      $param_username = $username;
      $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash password
      if ($stmt->execute()) {
        header('location: manage_users.php');
        exit;
      } else {
        $add_err = 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Petugas - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Tambah Petugas - Admin</h2>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="form-group">
        <label>Nama Petugas</label>
        <input type="text" name="nama_petugas" class="form-control <?php echo (!empty($nama_petugas_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nama_petugas; ?>" required>
        <span class="invalid-feedback"><?php echo $nama_petugas_err; ?></span>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" required>
        <span class="invalid-feedback"><?php echo $username_err; ?></span>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>" required>
        <span class="invalid-feedback"><?php echo $password_err; ?></span>
      </div>
      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>" required>
        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
      </div>
      <div class="form-group">
        <label>Level</label>
        <select name="id_level" class="form-control <?php echo (!empty($id_level_err)) ? 'is-invalid' : ''; ?>" required>
          <option value="">Pilih Level</option>
          <?php
          while ($row_level = $result_level->fetch_assoc()) {
            echo "<option value='" . $row_level['id_level'] . "'>" . $row_level['level'] . "</option>";
          }
          ?>
        </select>
        <span class="invalid-feedback"><?php echo $id_level_err; ?></span>
      </div>
      <span class="text-danger"><?php echo $add_err; ?></span>
      <button type="submit" class="btn btn-primary">Tambahkan</button>
    </form>
  </div>

  <?php include '../../includes/footer.php'; ?>