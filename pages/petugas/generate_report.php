<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah petugas masuk atau belum, dan periksa peran petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'petugas') {
  header('location: ../login.php');
  exit;
}

// Query untuk mendapatkan daftar barang yang dikelola oleh petugas
$sql = "SELECT b.nama_barang, b.tgl, b.harga_awal, l.harga_akhir, m.nama_lengkap, m.telp
        FROM tb_barang b
        LEFT JOIN tb_lelang l ON b.id_barang = l.id_barang
        LEFT JOIN tb_masyarakat m ON l.id_user = m.id_user
        ORDER BY b.tgl ASC";

$barang_list = [];

if ($stmt = $mysqli->prepare($sql)) {
  if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $barang_list[] = $row;
    }
  }
  $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generate Report - Petugas</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Generate Report - Petugas</h2>
    <p>Selamat datang, Petugas!</p>

    <div id="pdf-content">
      <h3>Daftar Barang yang Anda Kelola:</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Nama Barang</th>
            <th>Tanggal Lelang</th>
            <th>Harga Awal (IDR)</th>
            <th>Harga Akhir (IDR)</th>
            <th>Pemenang Lelang</th>
            <th>No. Telepon</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($barang_list as $barang) : ?>
            <?php if ($barang['harga_akhir'] !== null && $barang['nama_lengkap'] !== null && $barang['telp'] !== null) : ?>
              <tr>
                <td><?php echo $barang['nama_barang']; ?></td>
                <td><?php echo $barang['tgl']; ?></td>
                <td><?php echo number_format($barang['harga_awal'], 0, ",", "."); ?></td>
                <td><?php echo number_format($barang['harga_akhir'], 0, ",", "."); ?></td>
                <td><?php echo $barang['nama_lengkap']; ?></td>
                <td><?php echo $barang['telp']; ?></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <button class="btn btn-primary" onclick="generatePDF()">Generate PDF</button>
  </div>
  <script>
    function generatePDF() {
      var printContent = document.getElementById("pdf-content").innerHTML;
      var originalContent = document.body.innerHTML;
      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
    }
  </script>
  <?php include '../../includes/footer.php'; ?>