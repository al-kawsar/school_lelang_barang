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
$id_petugas = '';
$nama_petugas = '';
$username = '';
$id_level = '';
$nama_petugas_err = '';
$username_err = '';
$id_level_err = '';

// Periksa apakah ada parameter id dalam URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
  // Persiapkan pernyataan SELECT
  $sql = "SELECT id_petugas, nama_petugas, username, id_level FROM tb_petugas WHERE id_petugas = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('i', $param_id);
    $param_id = trim($_GET['id']);

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $id_petugas = $row['id_petugas'];
        $nama_petugas = $row['nama_petugas'];
        $username = $row['username'];
        $id_level = $row['id_level'];
      } else {
        // Jika id_petugas tidak valid
        header('location: manage_users.php');
        exit;
      }
    } else {
      echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
    }

    $stmt->close();
  }
}

// Proses data formulir setelah dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validasi Nama Petugas
  if (empty(trim($_POST['nama_petugas']))) {
    $nama_petugas_err = 'Silakan masukkan nama petugas.';
  } else {
    $nama_petugas = trim($_POST['nama_petugas']);
  }

  // Validasi Nama Pengguna
  if (empty(trim($_POST['username']))) {
    $username_err = 'Silakan masukkan nama pengguna.';
  } else {
    $username = trim($_POST['username']);
  }

  // Validasi Level Akses
  if (empty(trim($_POST['id_level']))) {
    $id_level_err = 'Silakan pilih level akses.';
  } else {
    $id_level = trim($_POST['id_level']);
  }

  // Periksa apakah tidak ada kesalahan validasi sebelum menyisipkan ke dalam database
  if (empty($nama_petugas_err) && empty($username_err) && empty($id_level_err)) {
    // Persiapkan pernyataan UPDATE
    $sql = "UPDATE tb_petugas SET nama_petugas = ?, username = ?, id_level = ? WHERE id_petugas = ?";

    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('ssii', $param_nama_petugas, $param_username, $param_id_level, $param_id);

      $param_nama_petugas = $nama_petugas;
      $param_username = $username;
      $param_id_level = $id_level;
      $param_id = $id_petugas;

      if ($stmt->execute()) {
        header('location: manage_users.php');
        exit;
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
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
  <title>Edit Petugas - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Edit Petugas - Admin</h2>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id_petugas); ?>" method="post">
      <div class="form-group">
        <label>Nama Petugas</label>
        <input type="text" name="nama_petugas" class="form-control <?php echo (!empty($nama_petugas_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nama_petugas; ?>">
        <span class="invalid-feedback"><?php echo $nama_petugas_err; ?></span>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
        <span class="invalid-feedback"><?php echo $username_err; ?></span>
      </div>
      <div class="form-group">
        <label>Level Akses</label>
        <select name="id_level" class="form-control <?php echo (!empty($id_level_err)) ? 'is-invalid' : ''; ?>">
          <option value="1" <?php echo ($id_level == 1) ? 'selected' : ''; ?>>Administrator</option>
          <option value="2" <?php echo ($id_level == 2) ? 'selected' : ''; ?>>Petugas</option>
        </select>
        <span class="invalid-feedback"><?php echo $id_level_err; ?></span>
      </div>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
  </div>

  <?php include '../../includes/footer.php'; ?>
</body>

</html>