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
$barang_err = '';

// Proses ketika formulir ditambahkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil data dari formulir
  $lelang_barang_id = $_POST['lelang_barang'];

  // Query untuk menambahkan barang ke dalam tabel tb_lelang dengan status 'dibuka'
  $sql = "INSERT INTO tb_lelang (id_barang, tgl_lelang, id_petugas, status) VALUES (?, CURDATE(), ?, 'dibuka')";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('ii', $lelang_barang_id, $user_id);
    if ($stmt->execute()) {
      header('location: manage_auctions.php');
      exit;
    } else {
      $barang_err = 'Terjadi kesalahan. Silakan coba lagi nanti.';
    }
    $stmt->close();
  }
}

// Query untuk mendapatkan daftar barang yang dimiliki oleh petugas
$sql = "SELECT b.id_barang, b.gambar, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang, l.id_petugas
        FROM tb_barang b
        INNER JOIN tb_lelang l ON b.id_barang = l.id_barang
        ORDER BY b.tgl ASC";

$barang_list = [];

if ($result = $mysqli->query($sql)) {
  while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
  }
  $result->free();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Auctions - Petugas</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Manage Auctions - Petugas</h2>
    <p>Selamat datang, Petugas!</p>

    <h3>Tambah Barang ke Lelang:</h3>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="form-group">
        <label>Pilih Barang yang akan Dilelang</label>
        <select name="lelang_barang" class="form-control">
          <?php foreach ($barang_list as $barang) : ?>
            <option value="<?php echo $barang['id_barang']; ?>"><?php echo $barang['nama_barang']; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <span class="text-danger"><?php echo $barang_err; ?></span>
      <button type="submit" class="btn btn-primary">Tambahkan ke Lelang</button>
    </form>

    <h3>Daftar Barang yang Anda Kelola:</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Tanggal</th>
          <th>Harga Awal (IDR)</th>
          <th>Deskripsi Barang</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($barang_list as $barang) : ?>
          <tr>
            <td><img src="../../uploads/<?php echo $barang['gambar']; ?>" alt="<?php echo $barang['nama_barang']; ?>" class="img-thumbnail" width="100"></td>
            <td><?php echo $barang['nama_barang']; ?></td>
            <td><?php echo $barang['tgl']; ?></td>
            <td><?php echo $barang['harga_awal']; ?></td>
            <td><?php echo $barang['deskripsi_barang']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php include '../../includes/footer.php'; ?>

</body>

</html>