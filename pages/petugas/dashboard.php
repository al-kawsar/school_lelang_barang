<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah petugas masuk atau belum, dan periksa peran petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'petugas') {
  header('location: ../login.php');
  exit;
}

// Inisialisasi variabel
$user_id = $_SESSION['user_id'];

  // Query untuk mendapatkan daftar barang yang dikelola oleh petugas
  $sql = "SELECT b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang, l.status
        FROM tb_barang b
        INNER JOIN tb_lelang l ON b.id_barang = l.id_barang
        ORDER BY b.tgl DESC";


$barang_list = [];

  // if ($stmt = $mysqli->prepare($sql)) {
  //   $stmt->bind_param('i', $user_id);
  //   if ($stmt->execute()) {
  //     $result = $stmt->get_result();
  //     while ($row = $result->fetch_assoc()) {
  //       $barang_list[] = $row;
  //     }
  //   }
  //   $stmt->close();
  // }

  $result = $mysqli->query($sql);

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $barang_list[] = $row;
    }
  }

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Petugas</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Dashboard - Petugas</h2>
    <p>Selamat datang, Petugas!</p>

    <h3>Daftar Barang yang Anda Lelang:</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Tanggal</th>
          <th>Harga Awal (IDR)</th>
          <th>Status Lelang</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($barang_list as $barang) : ?>
          <tr>
            <td><?php echo $barang['nama_barang']; ?></td>
            <td><?php echo $barang['tgl']; ?></td>
            <td><?php echo $barang['harga_awal']; ?></td>
            <td><?php echo $barang['status']; ?></td>
            <td>
              <!-- <a href="edit_auction.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-primary">Edit</a> -->
              <a href="close_auction.php?id_barang=<?php echo $barang['id_barang']; ?>" class="btn btn-danger">Tutup</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php include '../../includes/footer.php'; ?>