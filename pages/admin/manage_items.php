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
$success_msg = '';

// Proses ketika formulir ditambahkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil data dari formulir
  $nama_barang = $_POST['nama_barang'];
  $tgl = date('Y-m-d');
  $harga_awal = str_replace(".", "", $_POST['harga_awal']);
  $deskripsi_barang = $_POST['deskripsi_barang'];

  // Validasi input
  if (empty($nama_barang) || empty($harga_awal) || empty($deskripsi_barang)) {
    $barang_err = 'Semua field harus diisi!';
  } else {
    // Mengunggah gambar
    $gambar_barang = $_FILES['gambar_barang']['name'];
    $gambar_tmp = $_FILES['gambar_barang']['tmp_name'];
    $gambar_dir = '../../uploads/';

    // Cek apakah ada file yang diupload
    if (!empty($gambar_barang)) {
      // Generate a unique name for the uploaded image
      $timestamp = time(); // Get the current timestamp
      $unique_image_name = $timestamp . '_' . $_FILES['gambar_barang']['name']; // Combine timestamp and the original image name

      // Move the image to the upload directory with the unique name
      if (move_uploaded_file($_FILES['gambar_barang']['tmp_name'], $gambar_dir . $unique_image_name)) {
        // Query untuk menambahkan barang ke dalam tabel tb_barang
        $sql = "INSERT INTO tb_barang (nama_barang, gambar, tgl, harga_awal, deskripsi_barang) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
          $stmt->bind_param('sssss', $nama_barang, $unique_image_name, $tgl, $harga_awal, $deskripsi_barang);
          if ($stmt->execute()) {
            $success_msg = "Barang berhasil ditambahkan!";
          } else {
            $barang_err = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi nanti.';
          }
          $stmt->close();
        }
      } else {
        $barang_err = 'Gagal mengunggah gambar. Silakan coba lagi.';
      }
    } else {
      $barang_err = 'Gambar harus diunggah!';
    }
  }
}

// Query untuk mendapatkan daftar barang yang dimiliki oleh admin
$sql = "SELECT b.id_barang, b.gambar, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
FROM tb_barang b
        ORDER BY b.tgl DESC"; // Mengurutkan dari yang terbaru

        $barang_list = [];

        if ($result = $mysqli->query($sql)) {
          while ($row = $result->fetch_assoc()) {
            $barang_list[] = $row;
          }
          $result->free();
        }

        ?>

        <!DOCTYPE html>
        <html lang="en" class="h-100">

        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Kelola Barang - Administrator</title>
          <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
          <link rel="stylesheet" href="../../assets/css/style.css">
          <style>
            .card {
              border-radius: 10px;
              box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
              overflow: hidden;
            }

            .card-header {
              background-color: #f8f9fa;
              border-bottom: 1px solid rgba(0, 0, 0, 0.05);
              font-weight: bold;
            }

            .table-responsive {
              overflow-x: auto;
            }

            .btn-action {
              margin: 2px;
            }

            .form-card {
              margin-bottom: 30px;
            }

            .img-preview {
              width: 100%;
              height: 120px;
              object-fit: cover;
              border-radius: 5px;
              display: none;
            }

            .item-image {
              width: 80px;
              height: 80px;
              object-fit: cover;
              border-radius: 5px;
            }

            .alert {
              border-radius: 8px;
            }

            .page-title {
              position: relative;
              padding-bottom: 10px;
              margin-bottom: 20px;
            }

            .page-title:after {
              content: '';
              position: absolute;
              left: 0;
              bottom: 0;
              width: 50px;
              height: 3px;
              background-color: #007bff;
            }

            .description-cell {
              max-width: 250px;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }

            .input-group-text {
              background-color: #f8f9fa;
            }

            .table th {
              background-color: #f8f9fa;
            }
          </style>
        </head>

        <body class="d-flex flex-column h-100">
          <?php include '../../includes/navbar.php'; ?>

          <main class="flex-shrink-0 pt-5 mt-4">
            <div class="container">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title mb-0">Kelola Barang</h2>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../admin/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kelola Barang</li>
                  </ol>
                </nav>
              </div>

              <?php if (!empty($success_msg)) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <?php if (!empty($barang_err)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-exclamation-circle me-2"></i><?php echo $barang_err; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <div class="row">
                <div class="col-lg-4 mb-4">
                  <div class="card form-card">
                    <div class="card-header py-3">
                      <i class="fas fa-plus-circle me-2"></i>Tambah Barang Baru
                    </div>
                    <div class="card-body">
                      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" id="formBarang">
                        <div class="mb-3">
                          <label for="nama_barang" class="form-label">Nama Barang</label>
                          <input type="text" name="nama_barang" id="nama_barang" class="form-control" placeholder="Masukkan nama barang" required>
                        </div>
                        <div class="mb-3">
                          <label for="harga_awal" class="form-label">Harga Awal</label>
                          <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_awal" id="harga_awal" class="form-control" placeholder="Masukkan harga awal" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="deskripsi_barang" class="form-label">Deskripsi Barang</label>
                          <textarea name="deskripsi_barang" id="deskripsi_barang" class="form-control" rows="4" placeholder="Masukkan deskripsi barang" required></textarea>
                        </div>
                        <div class="mb-3">
                          <label for="gambar_barang" class="form-label">Gambar Barang</label>
                          <input type="file" name="gambar_barang" id="gambar_barang" class="form-control" accept="image/*" required onchange="previewImage()">
                          <div class="mt-2">
                            <img id="preview" class="img-preview">
                          </div>
                        </div>
                        <div class="d-grid mt-4">
                          <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Barang
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="col-lg-8">
                  <div class="card">
                    <div class="card-header py-3">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <i class="fas fa-list me-2"></i>Daftar Barang
                        </div>
                        <span class="badge bg-primary"><?php echo count($barang_list); ?> Total Barang</span>
                      </div>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                          <thead>
                            <tr>
                              <th>Gambar</th>
                              <th>Nama Barang</th>
                              <th>Tanggal</th>
                              <th>Harga Awal</th>
                              <th>Deskripsi</th>
                              <th class="text-center">Aksi</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (empty($barang_list)) : ?>
                              <tr>
                                <td colspan="6" class="text-center py-3">Belum ada data barang</td>
                              </tr>
                            <?php else : ?>
                              <?php foreach ($barang_list as $barang) : ?>
                                <tr>
                                  <td>
                                    <img src="../../uploads/<?php echo $barang['gambar']; ?>" alt="<?php echo $barang['nama_barang']; ?>" class="item-image">
                                  </td>
                                  <td><?php echo $barang['nama_barang']; ?></td>
                                  <td><?php echo date('d M Y', strtotime($barang['tgl'])); ?></td>
                                  <td>Rp <?php echo number_format($barang['harga_awal'], 0, ",", "."); ?></td>
                                  <td class="description-cell" title="<?php echo $barang['deskripsi_barang']; ?>"><?php echo $barang['deskripsi_barang']; ?></td>
                                  <td class="text-center">
                                    <a href="edit_item.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-primary btn-sm btn-action" title="Edit">
                                      <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_item.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-danger btn-sm btn-action" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                      <i class="fas fa-trash"></i>
                                    </a>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </main>

          <?php include '../../includes/footer.php'; ?>

          <script>
            document.addEventListener("DOMContentLoaded", function() {
              const hargaInput = document.querySelector('#harga_awal');

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

            function previewImage() {
              const preview = document.getElementById('preview');
              const fileInput = document.getElementById('gambar_barang');
              const file = fileInput.files[0];

              if (file) {
                preview.style.display = 'block';
                preview.src = URL.createObjectURL(file);
              } else {
                preview.style.display = 'none';
                preview.src = '';
              }
            }
          </script>
        </body>
        </html>