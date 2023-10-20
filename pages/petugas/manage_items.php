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
  $nama_barang = $_POST['nama_barang'];
  // $tgl = date('Y-m-d', strtotime($_POST['tgl']));
  $tgl = date('Y-m-d');
  // $harga_awal = $_POST['harga_awal'];
  $harga_awal = str_replace(".", "", $_POST['harga_awal']);
  $deskripsi_barang = $_POST['deskripsi_barang'];

  // Mengunggah gambar
  $gambar_barang = $_FILES['gambar_barang']['name'];
  $gambar_tmp = $_FILES['gambar_barang']['tmp_name'];
  $gambar_dir = '../../uploads/';
  // Generate a unique name for the uploaded image
  $timestamp = time(); // Get the current timestamp
  $unique_image_name = $timestamp . '_' . $_FILES['gambar_barang']['name']; // Combine timestamp and the original image name

  // Move the image to the upload directory with the unique name
  move_uploaded_file($_FILES['gambar_barang']['tmp_name'], $gambar_dir . $unique_image_name);

  // Query untuk menambahkan barang ke dalam tabel tb_barang
  $sql = "INSERT INTO tb_barang (nama_barang, gambar, tgl, harga_awal, deskripsi_barang) VALUES (?, ?, ?, ?, ?)";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('sssss', $nama_barang, $unique_image_name, $tgl, $harga_awal, $deskripsi_barang);
    if ($stmt->execute()) {
      header('location: manage_items.php');
      exit;
    } else {
      $barang_err = 'Terjadi kesalahan. Silakan coba lagi nanti.';
    }
    $stmt->close();
  }
}

  // Query untuk mendapatkan daftar barang yang dimiliki oleh petugas
  $sql = "SELECT b.id_barang, b.gambar, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
        FROM tb_barang b
        -- INNER JOIN tb_lelang l ON b.id_barang = l.id_barang
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
  <title>Manage Items - Petugas</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Manage Items - Petugas</h2>
    <p>Selamat datang, Petugas!</p>

    <h3>Tambah Barang:</h3>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="nama_barang" class="form-control" placeholder="Nama Barang" required>
      </div>
      <!-- <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tgl" class="form-control" required>
      </div> -->
      <div class="form-group">
        <label>Harga Awal (IDR)</label>
        <input type="text" name="harga_awal" class="form-control" placeholder="Harga Awal" required>
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
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const hargaInput = document.querySelector('input[name="harga_awal"]');

      hargaInput.addEventListener("input", function(e) {
        // Menghilangkan semua karakter selain angka
        let angka = e.target.value.replace(/\D/g, "");

        // Format angka dengan pemisah ribuan
        e.target.value = formatRibuan(angka);
      });

      function formatRibuan(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }
    });
  </script>
  <?php include '../../includes/footer.php'; ?>