<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah pengguna masuk atau belum, dan periksa peran pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'petugas') {
  header('location: ../login.php');
  exit;
}

if (!isset($_GET['id_barang'])) {
  header('Location: view_auctions.php');
  exit;
}

// Kode untuk mengambil data barang dan memunculkan di tampilan detail barang
$barang_id = $_GET['id_barang']; // Mengambil parameter id dari URL

// Query untuk mendapatkan detail barang yang akan ditawar berdasarkan id_barang
$sql = "SELECT b.id_barang, b.nama_barang, b.gambar, b.tgl, b.harga_awal, b.deskripsi_barang,
        IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
        FROM tb_barang b
        LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
        LEFT JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
        WHERE b.id_barang = ?
        GROUP BY b.id_barang, b.nama_barang, b.gambar, b.tgl, b.harga_awal, b.deskripsi_barang
        ORDER BY b.tgl ASC";

if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $barang_id);
  if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id_barang, $nama_barang, $gambar, $tgl, $harga_awal, $deskripsi_barang, $harga_tertinggi);
      $stmt->fetch();
    } else {
      // Barang tidak ditemukan
      header('location: view_auctions.php');
      exit;
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
  <title>Detail Auction - Petugas</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .vertical-stepper {
      list-style: none;
      padding: 0;
    }

    .vertical-stepper .step {
      display: flex;
      align-items: center;
      border: 1px solid #ddd;
      padding: 10px;
      margin: 5px 0;
      border-radius: 5px;
      background-color: #f9f9f9;
    }

    .step-number {
      font-weight: bold;
      margin-right: 10px;
    }

    .step-title {
      flex: 1;
    }
  </style>

</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Detail Auction - Petugas</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
    <h4>Detail Barang</h4>
    <table class="table">
      <tr>
        <th>Gambar</th>
        <td><img src="../../uploads/<?php echo $gambar; ?>" class="img-thumbnail" width="100"></td>
      </tr>
      <tr>
        <th>Nama Barang</th>
        <td><?php echo $nama_barang; ?></td>
      </tr>
      <tr>
        <th>Tanggal Lelang</th>
        <td><?php echo $tgl; ?></td>
      </tr>
      <tr>
        <th>Harga Awal</th>
        <td>Rp <?php echo number_format($harga_awal, 0, ",", "."); ?></td>
      </tr>
      <tr>
        <th>Harga Tertinggi Saat Ini</th>
        <td>Rp <?php echo number_format($harga_tertinggi, 0, ",", "."); ?></td>
      </tr>
    </table>
    <h4>Riwayat Penawaran</h4>
    <ul class="vertical-stepper">
      <?php
      // Query untuk mendapatkan riwayat penawaran yang sudah ada
      $sql_riwayat = "SELECT u.username, h.penawaran_harga FROM history_lelang h
                  JOIN tb_masyarakat u ON h.id_user = u.id_user
                  WHERE h.id_barang = ?
                  ORDER BY h.penawaran_harga DESC";

      if ($stmt_riwayat = $mysqli->prepare($sql_riwayat)) {
        $stmt_riwayat->bind_param('i', $barang_id);
        if ($stmt_riwayat->execute()) {
          $stmt_riwayat->store_result();
          if ($stmt_riwayat->num_rows > 0) {
            $stmt_riwayat->bind_result($username, $penawaran_harga);
            while ($stmt_riwayat->fetch()) {
              echo '<li>';
              echo '<div class="step">';
              echo '<span class="step-number">Rp' . number_format($penawaran_harga, 0, ",", ".") . '</span>';
              echo '<span class="step-title">' . $username . '</span>';
              echo '</div>';
              echo '</li>';
            }
          } else {
            echo '<li><div class="step">Belum ada riwayat penawaran.</div></li>';
          }
        }
        $stmt_riwayat->close();
      }
      ?>
    </ul>
  </div>

  <?php include '../../includes/footer.php'; ?>