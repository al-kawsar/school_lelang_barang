<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah admin masuk atau belum
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}

// Dapatkan informasi tentang jumlah pengguna, barang, dan lelang yang tersedia
$sql_user_count = "SELECT COUNT(*) AS total_users FROM tb_masyarakat";
$sql_barang_count = "SELECT COUNT(*) AS total_barang FROM tb_barang";
$sql_lelang_count = "SELECT COUNT(*) AS total_lelang FROM tb_lelang";
$sql_petugas_count = "SELECT COUNT(*) AS total_petugas FROM tb_petugas";

$total_users = 0;
$total_barang = 0;
$total_lelang = 0;
$total_petugas = 0;

// Ambil total pengguna
if ($result = $mysqli->query($sql_user_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_users = $row['total_users'];
  }
  $result->free();
}

// Ambil total barang
if ($result = $mysqli->query($sql_barang_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_barang = $row['total_barang'];
  }
  $result->free();
}

// Ambil total lelang
if ($result = $mysqli->query($sql_lelang_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_lelang = $row['total_lelang'];
  }
  $result->free();
}

// Ambil total petugas
if ($result = $mysqli->query($sql_petugas_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_petugas = $row['total_petugas'];
  }
  $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Dashboard - Admin</h2>
    <p>Selamat datang, Admin!</p>

    <div class="row">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Jumlah Pengguna</h5>
            <p class="card-text"><?php echo $total_users; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Jumlah Barang</h5>
            <p class="card-text"><?php echo $total_barang; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Jumlah Lelang</h5>
            <p class="card-text"><?php echo $total_lelang; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Jumlah Petugas</h5>
            <p class="card-text"><?php echo $total_petugas; ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>