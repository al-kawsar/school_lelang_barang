<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah pengguna masuk atau belum, dan periksa peran pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'masyarakat') {
  header('location: ../login.php');
  exit;
}

if (!isset($_GET['id_barang'])) {
  header('Location: view_auctions.php');
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

$barang_id = $_GET['id_barang']; // Mengambil parameter id dari URL

// Query untuk mendapatkan detail barang yang akan ditawar berdasarkan id_barang
$sql = "SELECT b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang,
        IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
        FROM tb_barang b
        LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
        LEFT JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
        WHERE b.id_barang = ?
        GROUP BY b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
        ORDER BY b.tgl ASC";

if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $barang_id);
  if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id_barang, $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $harga_tertinggi);
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
  <title>Penawaran Harga - Masyarakat</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Penawaran Harga - Masyarakat</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
    <h4>Detail Barang</h4>
    <table class="table">
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
    <h4>Penawaran Harga</h4>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <input type="hidden" name="barang_id" value="<?php echo $id_barang; ?>">
      <div class="form-group">
        <label for="harga_penawaran">Harga Penawaran</label>
        <input type="text" name="harga_penawaran" class="form-control" placeholder="Masukkan Harga Penawaran" value="<?php echo $harga_penawaran; ?>">
        <span class="text-danger"><?php echo $barang_err; ?></span>
      </div>
      <button type="submit" class="btn btn-primary">Tawar Harga</button>
    </form>
  </div>

  <?php include '../../includes/footer.php'; ?>