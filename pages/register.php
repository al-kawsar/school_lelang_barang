<?php
// Sisipkan konfigurasi dan file database
require_once '../config/config.php';
require_once '../config/database.php';

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
  // Redirect pengguna sesuai peran ke halaman dashboard yang sesuai
  if ($_SESSION['user_role'] == 'admin') {
    header("Location: admin/dashboard.php");
    exit();
  } elseif ($_SESSION['user_role'] == 'petugas') {
    header("Location: petugas/dashboard.php");
    exit();
  } elseif ($_SESSION['user_role'] == 'masyarakat') {
    header("Location: masyarakat/dashboard.php");
    exit();
  }
}

// Inisialisasi variabel
$username = $password = $confirm_password = $nama_lengkap = $telp = '';
$username_err = $password_err = $confirm_password_err = '';

// Memproses data formulir saat formulir diajukan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validasi username
  if (empty(trim($_POST['username']))) {
    $username_err = 'Masukkan username.';
  } else {
    // Periksa apakah username sudah digunakan
    $sql = "SELECT id_user FROM tb_masyarakat WHERE username = ?";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('s', $param_username);
      $param_username = trim($_POST['username']);
      if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
          $username_err = 'Username ini sudah digunakan.';
        } else {
          $username = trim($_POST['username']);
        }
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }

  // Validasi password
  if (empty(trim($_POST['password']))) {
    $password_err = 'Masukkan kata sandi.';
  } elseif (strlen(trim($_POST['password'])) < 6) {
    $password_err = 'Kata sandi minimal harus terdiri dari 6 karakter.';
  } else {
    $password = trim($_POST['password']);
  }

  // Validasi konfirmasi password
  if (empty(trim($_POST['confirm_password']))) {
    $confirm_password_err = 'Konfirmasi kata sandi.';
  } else {
    $confirm_password = trim($_POST['confirm_password']);
    if ($password != $confirm_password) {
      $confirm_password_err = 'Kata sandi tidak cocok.';
    }
  }

  // Validasi nama lengkap
  if (empty(trim($_POST['nama_lengkap']))) {
    $nama_lengkap_err = 'Masukkan nama lengkap Anda.';
  } else {
    $nama_lengkap = trim($_POST['nama_lengkap']);
  }

  // Validasi nomor telepon
  if (empty(trim($_POST['telp']))) {
    $telp_err = 'Masukkan nomor telepon Anda.';
  } else {
    $telp = trim($_POST['telp']);
  }

  // Cek kesalahan input sebelum menyisipkan ke database
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
    // Siapkan pernyataan sisipan
    $sql = "INSERT INTO tb_masyarakat (nama_lengkap, username, password, telp) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('ssss', $param_nama_lengkap, $param_username, $param_password, $param_telp);
      $param_nama_lengkap = $nama_lengkap;
      $param_username = $username;
      $param_password = password_hash($password, PASSWORD_DEFAULT);
      $param_telp = $telp;
      if ($stmt->execute()) {
        header('location: login.php');
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}
?>
<?php include '../includes/header.php'; ?>
<!-- <!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi - Aplikasi Pelelangan Online</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body> -->

<div class="container mt-4">
  <h2>Registrasi</h2>
  <p>Silakan isi formulir registrasi di bawah ini untuk bergabung dengan Aplikasi Pelelangan Online.</p>
  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <div class="form-group">
      <label for="nama_lengkap">Nama Lengkap</label>
      <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control <?php echo (!empty($nama_lengkap_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nama_lengkap; ?>">
      <span class="invalid-feedback"><?php echo $nama_lengkap_err; ?></span>
    </div>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
      <span class="invalid-feedback"><?php echo $username_err; ?></span>
    </div>
    <div class="form-group">
      <label for="password">Kata Sandi</label>
      <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
      <span class="invalid-feedback"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
      <label for="confirm_password">Konfirmasi Kata Sandi</label>
      <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
      <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
    </div>
    <div class="form-group">
      <label for="telp">Nomor Telepon</label>
      <input type="text" name="telp" id="telp" class="form-control <?php echo (!empty($telp_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $telp; ?>">
      <span class="invalid-feedback"><?php echo $telp_err; ?></span>
    </div>
    <div class="form-group">
      <input type="submit" class="btn btn-primary" value="Daftar">
      <a href="login.php" class="btn btn-secondary">Login</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>

<!-- <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>

</html> -->