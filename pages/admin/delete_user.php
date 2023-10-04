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
$id_petugas = null;
$confirmation = false;
$delete_err = '';

// Periksa apakah ada parameter id dalam URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
  $id_petugas = trim($_GET['id']);
}

// Proses ketika formulir dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $confirmation = true;

  if (isset($_POST['confirm']) && $_POST['confirm'] == 'Yes') {
    // Persiapkan pernyataan DELETE
    $sql = "DELETE FROM tb_petugas WHERE id_petugas = ?";

    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('i', $id_petugas);

      if ($stmt->execute()) {
        header('location: manage_petugas.php');
        exit;
      } else {
        $delete_err = 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }

      $stmt->close();
    }
  } else {
    header('location: manage_petugas.php');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Petugas - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Delete Petugas - Admin</h2>

    <?php if (!$confirmation) : ?>
      <p>Apakah Anda yakin ingin menghapus petugas ini?</p>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id_petugas; ?>">
        <button type="submit" name="confirm" value="Yes" class="btn btn-danger">Yes</button>
        <a href="manage_petugas.php" class="btn btn-secondary">No</a>
      </form>
    <?php endif; ?>

    <?php if ($delete_err) : ?>
      <div class="alert alert-danger mt-3"><?php echo $delete_err; ?></div>
    <?php endif; ?>
  </div>

  <?php include '../../includes/footer.php'; ?>
</body>

</html>