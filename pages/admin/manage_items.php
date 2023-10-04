<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah admin masuk atau belum, dan periksa peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}

// Inisialisasi variabel
$user_id = $_SESSION['user_id'];
$barang_err = '';

// Proses ketika formulir ditambahkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil data dari formulir
  $nama_barang = $_POST['nama_barang'];
  $tgl = $_POST['tgl'];
  $harga_awal = $_POST['harga_awal'];
  $deskripsi_barang = $_POST['deskripsi_barang'];

  // Mengunggah gambar
  $gambar_barang = $_FILES['gambar_barang']['name'];
  $gambar_tmp = $_FILES['gambar_barang']['tmp_name'];
  $gambar_dir = '../../uploads/';

  // Memindahkan gambar ke direktori upload
  move_uploaded_file($gambar_tmp, $gambar_dir . $gambar_barang);

  // Query untuk menambahkan barang ke dalam tabel tb_barang
  $sql = "INSERT INTO tb_barang (nama_barang, tgl, harga_awal, deskripsi_barang, gambar, id_petugas) VALUES (?, ?, ?, ?, ?, ?)";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('ssisii', $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $gambar_barang, $user_id);
    if ($stmt->execute()) {
      header('location: manage_items.php');
      exit;
    } else {
      $barang_err = 'Terjadi kesalahan. Silakan coba lagi nanti.';
    }
    $stmt->close();
  }
}

// Query untuk mendapatkan daftar barang yang dimiliki oleh admin
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
  <title>Manage Items - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Manage Items - Admin</h2>
    <p>Selamat datang, Admin!</p>

    <h3>Tambah Barang:</h3>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="nama_barang" class="form-control" placeholder="Nama Barang" required>
      </div>
      <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tgl" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Harga Awal (IDR)</label>
        <input type="number" name="harga_awal" class="form-control" placeholder="Harga Awal" required>
      </div>
      <div class="form-group">
        <label>Deskripsi Barang</label>
        <textarea name="deskripsi_barang" class="form-control" rows="4" placeholder="Deskripsi Barang" required></textarea>
      </div>
      <div class="form-group">
        <label>Gambar Barang</label>
        <input type="file" name="gambar_barang" class="form-control-file" accept="image/*" required>
      </div>
      <span class="text-danger"><?php echo $barang_err; ?></span>
      <button type="submit" class="btn btn-primary">Tambahkan</button>
    </form>

    <h3>Daftar Barang yang Anda Kelola:</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Gambar</th>
          <th>Nama Barang</th>
          <th>Tanggal</th>
          <th>Harga Awal (IDR)</th>
          <th>Deskripsi Barang</th>
          <th>Aksi</th>
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
            <td>
              <a href="edit_item.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-primary btn-sm">Edit</a>
              <a href="delete_item.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-danger btn-sm">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php include '../../includes/footer.php'; ?>