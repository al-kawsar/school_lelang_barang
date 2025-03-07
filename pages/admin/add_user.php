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
$nama_petugas_err = $id_level_err = '';

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
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($id_level_err) && empty($nama_petugas_err)) {
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
<html lang="en" class="h-100">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Petugas - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .card {
      border-radius: 10px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      margin-bottom: 2rem;
    }
    .card-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: 10px 10px 0 0;
    }
    .form-control {
      border-radius: 5px;
      padding: 0.75rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .btn-action {
      border-radius: 5px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
    }
    .btn-toolbar {
      display: flex;
      justify-content: space-between;
    }
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
    .password-container {
      position: relative;
    }
    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    .form-control.with-icon {
      padding-left: 45px;
    }
    .progress {
      height: 6px;
      margin-top: 10px;
    }
    .invalid-feedback {
      font-size: 85%;
      margin-top: 0.5rem;
    }
  </style>
</head>

<body class="d-flex flex-column h-100">
  <?php include '../../includes/navbar.php'; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">
              <i class="fas fa-user-plus mr-2"></i> Tambah Petugas
            </h3>
          </div>
          <div class="card-body">
            <?php if (!empty($add_err)) : ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $add_err; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="addUserForm">

              <!-- Nama Petugas -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-id-card mr-1"></i> Nama Petugas
                </label>
                <div class="position-relative">
                  <i class="fas fa-user input-icon"></i>
                  <input type="text" name="nama_petugas" class="form-control with-icon <?php echo (!empty($nama_petugas_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nama_petugas; ?>" placeholder="Masukkan nama lengkap" required>
                  <?php if (!empty($nama_petugas_err)) : ?>
                    <div class="invalid-feedback"><?php echo $nama_petugas_err; ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Username -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-at mr-1"></i> Username
                </label>
                <div class="position-relative">
                  <i class="fas fa-user-tag input-icon"></i>
                  <input type="text" name="username" class="form-control with-icon <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Masukkan username" required>
                  <?php if (!empty($username_err)) : ?>
                    <div class="invalid-feedback"><?php echo $username_err; ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Password -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-lock mr-1"></i> Password
                </label>
                <div class="password-container">
                  <i class="fas fa-key input-icon"></i>
                  <input type="password" name="password" id="password" class="form-control with-icon <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>" placeholder="Minimal 6 karakter" required>
                  <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                  <?php if (!empty($password_err)) : ?>
                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                  <?php endif; ?>
                </div>
                <div class="progress mt-2" id="password-strength">
                  <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="form-text text-muted" id="password-feedback">Password harus memiliki setidaknya 6 karakter</small>
              </div>

              <!-- Konfirmasi Password -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-check-circle mr-1"></i> Konfirmasi Password
                </label>
                <div class="password-container">
                  <i class="fas fa-key input-icon"></i>
                  <input type="password" name="confirm_password" id="confirm_password" class="form-control with-icon <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>" placeholder="Masukkan ulang password" required>
                  <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                  <?php if (!empty($confirm_password_err)) : ?>
                    <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                  <?php endif; ?>
                </div>
                <small class="form-text text-muted" id="match-feedback"></small>
              </div>

              <!-- Level -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-user-shield mr-1"></i> Level
                </label>
                <div class="position-relative">
                  <i class="fas fa-layer-group input-icon"></i>
                  <select name="id_level" class="form-control with-icon <?php echo (!empty($id_level_err)) ? 'is-invalid' : ''; ?>" required>
                    <option value="">Pilih Level Akses</option>
                    <?php
                    while ($row_level = $result_level->fetch_assoc()) {
                      echo "<option value='" . $row_level['id_level'] . "'>" . $row_level['level'] . "</option>";
                    }
                    ?>
                  </select>
                  <?php if (!empty($id_level_err)) : ?>
                    <div class="invalid-feedback"><?php echo $id_level_err; ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Tombol Aksi -->
              <div class="btn-toolbar mt-4">
                <a href="manage_users.php" class="btn btn-secondary btn-action">
                  <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-action">
                  <i class="fas fa-save mr-1"></i> Tambahkan Petugas
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>

  <!-- JavaScript untuk Bootstrap dan fungsi-fungsi lainnya -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
      const confirmPasswordInput = document.getElementById('confirm_password');
      const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPasswordInput.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    // Password strength meter
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      let strength = 0;
      const progressBar = document.querySelector('#password-strength .progress-bar');
      const feedback = document.getElementById('password-feedback');

      // Length check
      if (password.length >= 6) {
        strength += 25;
      }

      // Uppercase check
      if (/[A-Z]/.test(password)) {
        strength += 25;
      }

      // Number check
      if (/[0-9]/.test(password)) {
        strength += 25;
      }

      // Special character check
      if (/[^A-Za-z0-9]/.test(password)) {
        strength += 25;
      }

      // Update progress bar
      progressBar.style.width = strength + '%';

      // Update color based on strength
      if (strength < 25) {
        progressBar.className = 'progress-bar bg-danger';
        feedback.innerHTML = 'Password sangat lemah';
        feedback.className = 'form-text text-danger';
      } else if (strength < 50) {
        progressBar.className = 'progress-bar bg-warning';
        feedback.innerHTML = 'Password lemah';
        feedback.className = 'form-text text-warning';
      } else if (strength < 75) {
        progressBar.className = 'progress-bar bg-info';
        feedback.innerHTML = 'Password cukup kuat';
        feedback.className = 'form-text text-info';
      } else {
        progressBar.className = 'progress-bar bg-success';
        feedback.innerHTML = 'Password kuat';
        feedback.className = 'form-text text-success';
      }
    });

    // Password match check
    document.getElementById('confirm_password').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      const feedback = document.getElementById('match-feedback');

      if (confirmPassword === '') {
        feedback.innerHTML = '';
      } else if (password === confirmPassword) {
        feedback.innerHTML = 'Password cocok';
        feedback.className = 'form-text text-success';
      } else {
        feedback.innerHTML = 'Password tidak cocok';
        feedback.className = 'form-text text-danger';
      }
    });
  </script>
</body>
</html>