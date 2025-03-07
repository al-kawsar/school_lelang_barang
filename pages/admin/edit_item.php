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

// Format harga untuk tampilan
$formatted_harga = number_format($harga_awal, 0, ',', '.');

?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Item - Administrator</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .card {
      border-radius: 10px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      margin-bottom: 2rem;
    }
    .card-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: 10px 10px 0 0;
    }
    .preview-container {
      position: relative;
      margin-bottom: 1rem;
      text-align: center;
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 5px;
    }
    .img-preview {
      max-height: 250px;
      object-fit: contain;
    }
    .custom-file-upload {
      border: 1px solid #ccc;
      display: inline-block;
      padding: 6px 12px;
      cursor: pointer;
      background-color: #f8f9fa;
      border-radius: 5px;
      transition: all 0.3s;
    }
    .custom-file-upload:hover {
      background-color: #e9ecef;
    }
    .btn-action {
      border-radius: 5px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
    }
    .form-control {
      border-radius: 5px;
      padding: 0.75rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .btn-toolbar {
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>

<body class="d-flex flex-column h-100">
  <?php include '../../includes/navbar.php'; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">
              <i class="fas fa-edit mr-2"></i> Edit Item
            </h3>
          </div>
          <div class="card-body">
            <?php if (!empty($barang_err)) : ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $barang_err; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $barang_id; ?>" method="post" enctype="multipart/form-data">

              <!-- Gambar Preview dan Upload -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-image mr-1"></i> Gambar Barang
                </label>
                <div class="preview-container">
                  <img src="../../uploads/<?php echo $gambar_barang; ?>" alt="Gambar Barang" class="img-preview img-fluid rounded">
                </div>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="gambar-upload" name="gambar_barang" accept="image/*">
                  <label class="custom-file-label" for="gambar-upload">Pilih gambar baru...</label>
                </div>
                <small class="form-text text-muted">
                  <i class="fas fa-info-circle mr-1"></i> Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.
                </small>
              </div>

              <!-- Nama Barang -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-tag mr-1"></i> Nama Barang
                </label>
                <input type="text" name="nama_barang" class="form-control" value="<?php echo $nama_barang; ?>" placeholder="Masukkan nama barang" required>
              </div>

              <!-- Harga Awal -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-money-bill-wave mr-1"></i> Harga Awal
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp</span>
                  </div>
                  <input type="text" name="harga_awal" class="form-control" value="<?php echo $formatted_harga; ?>" placeholder="Masukkan harga awal" required>
                </div>
              </div>

              <!-- Deskripsi Barang -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-align-left mr-1"></i> Deskripsi Barang
                </label>
                <textarea name="deskripsi_barang" class="form-control" rows="5" placeholder="Masukkan deskripsi detail barang" required><?php echo $deskripsi_barang; ?></textarea>
              </div>

              <!-- Tombol Aksi -->
              <div class="btn-toolbar mt-4">
                <a href="manage_items.php" class="btn btn-secondary btn-action">
                  <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-action">
                  <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>

  <!-- JavaScript untuk Bootstrap dan fungsi-fungsi lainnya -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    // Update nama file yang dipilih
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
      const fileName = e.target.files[0].name;
      const nextSibling = e.target.nextElementSibling;
      nextSibling.innerText = fileName;

      // Preview gambar yang dipilih
      if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.querySelector('.img-preview').setAttribute('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
      }
    });

    // Format harga dengan pemisah ribuan
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
</body>
</html>