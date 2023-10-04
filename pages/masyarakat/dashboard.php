<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah pengguna masuk atau belum, dan periksa peran pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'masyarakat') {
  header('location: ../login.php');
  exit;
}

// Inisialisasi variabel
$user_id = $_SESSION['user_id'];
$barang_err = '';
$barang_id = $harga_penawaran = '';

// Memproses penawaran harga saat formulir diajukan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (empty(trim($_POST['barang_id']))) {
    $barang_err = 'Pilih barang untuk menawar harga.';
  } else {
    $barang_id = trim($_POST['barang_id']);
  }

  if (empty(trim($_POST['harga_penawaran']))) {
    $barang_err = 'Masukkan harga penawaran.';
  } else {
    $harga_penawaran = trim($_POST['harga_penawaran']);
  }

  // Memeriksa apakah ada kesalahan input sebelum melakukan penawaran
  if (empty($barang_err)) {
    // Masukkan penawaran harga ke dalam history_lelang
    $sql = "INSERT INTO history_lelang (id_lelang, id_barang, id_user, penawaran_harga) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('iiii', $barang_id, $barang_id, $user_id, $harga_penawaran);
      if ($stmt->execute()) {
        // Penawaran berhasil, alihkan kembali ke dashboard
        header('location: dashboard.php');
        exit;
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}

// Query untuk mendapatkan daftar barang yang sedang dilelang dengan status "dibuka"
// $sql = "SELECT b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang, 
//         IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
//         FROM tb_barang b
//         LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
//         INNER JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
//         WHERE b.tgl >= CURDATE()
//         GROUP BY b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
//         ORDER BY b.tgl ASC";

$sql = "SELECT b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang, 
        IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
        FROM tb_barang b
        LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
        INNER JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
        GROUP BY b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
        ORDER BY b.tgl ASC";


$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Masyarakat</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Dashboard - Masyarakat</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
    <h4>Daftar Barang yang Dilelang</h4>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Tanggal Lelang</th>
          <th>Harga Awal</th>
          <th>Harga Tertinggi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . $row['nama_barang'] . "</td>";
          echo "<td>" . $row['tgl'] . "</td>";
          echo "<td>Rp " . number_format($row['harga_awal'], 0, ",", ".") . "</td>";
          echo "<td>Rp " . number_format($row['harga_tertinggi'], 0, ",", ".") . "</td>";
          echo "<td>";
          echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
          echo '<input type="hidden" name="barang_id" value="' . $row['id_barang'] . '">';
          echo '<div class="form-group">';
          echo '<input type="text" name="harga_penawaran" class="form-control" placeholder="Penawaran Harga">';
          echo '</div>';
          echo '<button type="submit" class="btn btn-primary">Tawar Harga</button>';
          echo '</form>';
          echo "</td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
    <span class="text-danger"><?php echo $barang_err; ?></span>
  </div>

  <?php include '../../includes/footer.php'; ?>