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
$username = $password = '';
$username_err = $password_err = '';

// Memproses data formulir saat formulir diajukan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validasi username
  if (empty(trim($_POST['username']))) {
    $username_err = 'Masukkan username.';
  } else {
    $username = trim($_POST['username']);
  }

  // Validasi password
  if (empty(trim($_POST['password']))) {
    $password_err = 'Masukkan kata sandi.';
  } else {
    $password = trim($_POST['password']);
  }

  // Memeriksa apakah ada kesalahan input sebelum melakukan autentikasi
  if (empty($username_err) && empty($password_err)) {
    // Coba autentikasi pengguna di tabel masyarakat
    $sql_masyarakat = "SELECT id_user, username, password FROM tb_masyarakat WHERE username = ?";
    if ($stmt = $mysqli->prepare($sql_masyarakat)) {
      $stmt->bind_param('s', $param_username);
      $param_username = $username;
      if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
          $stmt->bind_result($id, $username, $hashed_password);
          if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
              session_start();
              $_SESSION['user_id'] = $id;
              $_SESSION['username'] = $username;
              $_SESSION['user_role'] = 'masyarakat';
              header('location: masyarakat/dashboard.php');
            } else {
              $password_err = 'Kata sandi yang Anda masukkan salah.';
            }
          }
        } else {
          // Jika tidak ada pengguna masyarakat yang cocok, coba di tabel petugas
          $sql_petugas = "SELECT id_petugas, username, password, id_level FROM tb_petugas WHERE username = ?";
          if ($stmt_petugas = $mysqli->prepare($sql_petugas)) {
            $stmt_petugas->bind_param('s', $param_username);
            $param_username = $username;
            if ($stmt_petugas->execute()) {
              $stmt_petugas->store_result();
              if ($stmt_petugas->num_rows == 1) {
                $stmt_petugas->bind_result($id_petugas, $username, $hashed_password, $id_level);
                if ($stmt_petugas->fetch()) {
                  if (password_verify($password, $hashed_password)) {
                    session_start();
                    $_SESSION['user_id'] = $id_petugas;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_role'] = ($id_level == 1) ? 'admin' : 'petugas';
                    if ($id_level == 1) {
                      header('location: admin/dashboard.php');
                    } else {
                      header('location: petugas/dashboard.php');
                    }
                  } else {
                    $password_err = 'Kata sandi yang Anda masukkan salah.';
                  }
                }
              } else {
                $username_err = 'Akun dengan username ini tidak ditemukan.';
              }
            } else {
              echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
            }
            $stmt_petugas->close();
          }
        }
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <h2>Login</h2>
  <p>Silakan masuk dengan akun Anda.</p>
  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
      <span class="invalid-feedback"><?php echo $username_err; ?></span>
    </div>
    <div class="form-group">
      <label for="password">Kata Sandi</label>
      <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
      <span class="invalid-feedback"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
      <input type="submit" class="btn btn-primary" value="Login">
        <a href="register.php" class="btn btn-secondary">Daftar</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>