<?php
// Include configuration and database files
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
  // Redirect user according to role to appropriate dashboard
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

// Initialize variables
$username = $password = '';
$username_err = $password_err = '';
$login_failed = false;

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validate username
  if (empty(trim($_POST['username']))) {
    $username_err = 'Masukkan username.';
  } else {
    $username = trim($_POST['username']);
  }

  // Validate password
  if (empty(trim($_POST['password']))) {
    $password_err = 'Masukkan kata sandi.';
  } else {
    $password = trim($_POST['password']);
  }

  // Check for input errors before authenticating
  if (empty($username_err) && empty($password_err)) {
    // Try to authenticate user in masyarakat table
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
              $login_failed = true;
            }
          }
        } else {
          // If no matching masyarakat user, try in petugas table
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
                    $login_failed = true;
                  }
                }
              } else {
                $username_err = 'Akun dengan username ini tidak ditemukan.';
                $login_failed = true;
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

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
          <div class="text-center mb-4">
            <i class="bi bi-hammer text-primary" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2">Pelelangan Online</h2>
            <p class="text-muted">Masuk ke akun Anda</p>
          </div>

          <?php if($login_failed): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              Login gagal. Periksa kembali username dan kata sandi Anda.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-person-fill"></i>
                </span>
                <input type="text" name="username" id="username"
                class="form-control border-start-0 <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                value="<?php echo $username; ?>" placeholder="Masukkan username Anda">
                <?php if(!empty($username_err)): ?>
                  <div class="invalid-feedback"><?php echo $username_err; ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label">Kata Sandi</label>
                <a href="forgot_password.php" class="text-decoration-none small">Lupa kata sandi?</a>
              </div>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password" name="password" id="password"
                class="form-control border-start-0 <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                placeholder="Masukkan kata sandi Anda">
                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                  <i class="bi bi-eye"></i>
                </button>
                <?php if(!empty($password_err)): ?>
                  <div class="invalid-feedback"><?php echo $password_err; ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary py-2">Login</button>
              <a href="register.php" class="btn btn-light py-2">Belum punya akun? Daftar sekarang</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    } else {
      passwordInput.type = 'password';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    }
  });
</script>

<?php include '../includes/footer.php'; ?>