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
$barang_id = $_GET['id']; // Ambil ID barang dari parameter URL
$barang_err = '';

// Fungsi untuk mengunggah gambar
function uploadImage($file)
{
  $targetDir = '../../uploads/';
  $targetFile = $targetDir . basename($file['name']);
  $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  // Cek apakah file gambar atau bukan
  $check = getimagesize($file['tmp_name']);
  if ($check !== false) {
    // Cek jenis file
    if ($imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
      // Pindahkan file ke direktori yang ditentukan
      if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $targetFile;
      } else {
        return false;
      }
    }
  }
  return false;
}

// Fungsi untuk menghasilkan nama file unik dengan format "timestamp+nameImage"
function generateUniqueFileName($file)
{
  $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
  $uniqueName = time() . '_' . $file['name']; // Menggunakan time() sebagai timestamp
  return $uniqueName;
}

// Fungsi untuk menghapus gambar lama
function deleteOldImage($file_path)
{
  if (file_exists($file_path)) {
    unlink($file_path);
  }
}

// Query untuk mendapatkan informasi barang yang akan diubah
$sql = "SELECT id_barang, nama_barang, tgl, harga_awal, deskripsi_barang, gambar FROM tb_barang WHERE id_barang = ?";
$nama_barang = '';
$tgl = '';
$harga_awal = '';
$deskripsi_barang = '';
$gambar_barang = '';

if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $barang_id);
  if ($stmt->execute()) {
    $stmt->bind_result($barang_id, $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $gambar_barang);
    $stmt->fetch();
    $stmt->close();
  } else {
    header('location: manage_items.php');
    exit;
  }
} else {
  header('location: manage_items.php');
  exit;
}

// Saat Anda mengunggah gambar baru, simpan nama gambar lama
$gambar_lama = $gambar_barang;

// Proses ketika formulir diubah
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil data dari formulir
  $nama_barang = $_POST['nama_barang'];
  // $tgl = $_POST['tgl'];
  $tgl = date('Y-m-d');
  // $harga_awal = $_POST['harga_awal'];
  $harga_awal = str_replace(".", "", $_POST['harga_awal']);
  $deskripsi_barang = $_POST['deskripsi_barang'];
  $gambar_barang = $_FILES['gambar_barang'];

  // Cek apakah ada gambar yang diunggah
  if ($gambar_barang['error'] === 0) {
    // Buat nama unik untuk file gambar
    $gambar_path = generateUniqueFileName($gambar_barang);

    // Pindahkan file ke direktori yang ditentukan
    if (move_uploaded_file($gambar_barang['tmp_name'], '../../uploads/' . $gambar_path)) {
      // Hapus gambar lama jika nama gambar baru tidak sama dengan gambar lama
      if ($gambar_lama !== $gambar_path) {
        deleteOldImage('../../uploads/' . $gambar_lama);
      }

      // Query untuk mengupdate informasi barang, termasuk gambar
      $sql = "UPDATE tb_barang SET nama_barang = ?, tgl = ?, harga_awal = ?, deskripsi_barang = ?, gambar = ? WHERE id_barang = ?";

      if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('ssisss', $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $gambar_path, $barang_id);
        if ($stmt->execute()) {
          header('location: manage_items.php');
          exit;
        } else {
          $barang_err = 'Terjadi kesalahan. Silakan coba lagi nanti.';
        }
        $stmt->close();
      }
    } else {
      $barang_err = 'Gagal mengunggah gambar.';
    }
  } else {
    // Query untuk mengupdate informasi barang tanpa gambar
    $sql = "UPDATE tb_barang SET nama_barang = ?, tgl = ?, harga_awal = ?, deskripsi_barang = ? WHERE id_barang = ?";

    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('ssiss', $nama_barang, $tgl, $harga_awal, $deskripsi_barang, $barang_id);
      if ($stmt->execute()) {
        header('location: manage_items.php');
        exit;
      } else {
        $barang_err = 'Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Item - Administrator</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Edit Item - Administrator</h2>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $barang_id; ?>" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Gambar Barang</label>
        <img src="../../uploads/<?php echo $gambar_barang; ?>" alt="Gambar Barang" class="img-thumbnail" style="max-width: 200px;">
        <input type="file" name="gambar_barang" class="form-control">
      </div>
      <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="nama_barang" class="form-control" value="<?php echo $nama_barang; ?>" required>
      </div>
      <!-- <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tgl" class="form-control" value="<?php echo $tgl; ?>" required>
      </div> -->
      <div class="form-group">
        <label>Harga Awal (IDR)</label>
        <input type="text" name="harga_awal" class="form-control" value="<?php echo $harga_awal; ?>" required>
      </div>
      <div class="form-group">
        <label>Deskripsi Barang</label>
        <textarea name="deskripsi_barang" class="form-control" rows="4" required><?php echo $deskripsi_barang; ?></textarea>
      </div>
      <span class="text-danger"><?php echo $barang_err; ?></span>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>

    <a href="manage_items.php" class="btn btn-secondary mt-2">Kembali</a>
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