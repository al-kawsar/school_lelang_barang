<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah petugas masuk atau belum
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}

// Query untuk mendapatkan daftar petugas
$sql = "SELECT * FROM tb_petugas";

$petugas_list = [];

if ($result = $mysqli->query($sql)) {
  while ($row = $result->fetch_assoc()) {
    $petugas_list[] = $row;
  }
  $result->free();
}
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Petugas - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="d-flex flex-column h-100">
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Manage Petugas - Admin</h2>
    <p>Selamat datang, Admin!</p>
    <div class="my-2">
      <a href="add_user.php" class="btn btn-primary btn-sm">Tambah Petugas</a>
    </div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Nama Lengkap</th>
          <th>Nama Pengguna</th>
          <th>Level Akses</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($petugas_list as $petugas) : ?>
          <tr>
            <td><?php echo $petugas['nama_petugas']; ?></td>
            <td><?php echo $petugas['username']; ?></td>
            <td><?php echo ($petugas['id_level'] == 1) ? 'Administrator' : 'Petugas'; ?></td>
            <td>
              <a href="edit_user.php?id=<?php echo $petugas['id_petugas']; ?>" class="btn btn-primary btn-sm">Edit</a>
              <a href="delete_user.php?id=<?php echo $petugas['id_petugas']; ?>" class="btn btn-danger btn-sm">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php include '../../includes/footer.php'; ?>
</body>

</html>