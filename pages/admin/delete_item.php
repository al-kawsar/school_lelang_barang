<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah admin masuk atau belum, dan periksa peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}

// Inisialisasi pesan kesalahan
$barang_id = $_GET['id'];
$barang_err = '';

// Proses ketika permintaan penghapusan dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Query untuk menghapus barang dari tabel tb_barang
  $sql_delete_barang = "DELETE FROM tb_barang WHERE id_barang = ?";

  if ($stmt = $mysqli->prepare($sql_delete_barang)) {
    $stmt->bind_param('i', $barang_id);
    if ($stmt->execute()) {
      header('location: manage_items.php');
      exit;
    } else {
      $barang_err = 'Terjadi kesalahan saat menghapus barang. Silakan coba lagi nanti.';
    }
    $stmt->close();
  }
}

// Query untuk mendapatkan informasi barang yang akan dihapus
$sql_get_barang = "SELECT id_barang, nama_barang, tgl, harga_awal, deskripsi_barang, gambar FROM tb_barang WHERE id_barang = ?";

if ($stmt = $mysqli->prepare($sql_get_barang)) {
  $stmt->bind_param('i', $barang_id);
  if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id_barang, $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $gambar_barang);
      $stmt->fetch();
    } else {
      // Barang tidak ditemukan
      header('location: manage_items.php');
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
  <title>Delete Item - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Delete Item - Admin</h2>

    <p>Anda akan menghapus barang dengan detail berikut:</p>
    <table class="table">
      <tbody>
        <tr>
          <th>Nama Barang</th>
          <td><?php echo $nama_barang; ?></td>
        </tr>
        <tr>
          <th>Tanggal</th>
          <td><?php echo $tgl; ?></td>
        </tr>
        <tr>
          <th>Harga Awal (IDR)</th>
          <td><?php echo $harga_awal; ?></td>
        </tr>
        <tr>
          <th>Deskripsi Barang</th>
          <td><?php echo $deskripsi_barang; ?></td>
        </tr>
        <tr>
          <th>Gambar</th>
          <td><img src="../../uploads/<?php echo $gambar_barang; ?>" alt="<?php echo $nama_barang; ?>" width="200"></td>
        </tr>
      </tbody>
    </table>

    <p class="text-danger"><?php echo $barang_err; ?></p>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_barang; ?>" method="post">
      <input type="hidden" name="id_barang" value="<?php echo $id_barang; ?>">
      <button type="submit" class="btn btn-danger">Hapus Barang</button>
      <a href="manage_items.php" class="btn btn-secondary">Kembali</a>
    </form>
  </div>

  <?php include '../../includes/footer.php'; ?>