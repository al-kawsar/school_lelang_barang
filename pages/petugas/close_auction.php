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

  // Cek apakah tindakan formulir sudah dilakukan
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_barang'])) {
    // Ambil ID barang dari formulir
    $id_barang = $_POST['id_barang'];

    // Langkah 1: Dapatkan harga tertinggi dari tabel history_lelang
    $sql_harga_tertinggi = "SELECT MAX(penawaran_harga) AS harga_tertinggi FROM history_lelang WHERE id_barang = ?";
    $harga_tertinggi = 0;

    if ($stmt = $mysqli->prepare($sql_harga_tertinggi)) {
      $stmt->bind_param('i', $id_barang);
      if ($stmt->execute()) {
        $stmt->bind_result($harga_tertinggi);
        $stmt->fetch();
      }
      $stmt->close();
    }

    // Langkah 2: Dapatkan id_user yang memenangkan lelang berdasarkan harga tertinggi
    $id_user_pemenang = 0;

    if ($harga_tertinggi > 0) {
      $sql_pemenang = "SELECT id_user FROM history_lelang WHERE id_barang = ? AND penawaran_harga = ?";
      if ($stmt = $mysqli->prepare($sql_pemenang)) {
        $stmt->bind_param('ii', $id_barang, $harga_tertinggi);
        if ($stmt->execute()) {
          $stmt->bind_result($id_user_pemenang);
          $stmt->fetch();
        }
        $stmt->close();
      }
    }

    // Langkah 3: Perbarui data di tabel tb_lelang
    if ($id_user_pemenang > 0) {
      $sql_update_lelang = "UPDATE tb_lelang SET harga_akhir = ?, id_user = ?, status = 'ditutup' WHERE id_barang = ?";
      if ($stmt = $mysqli->prepare($sql_update_lelang)) {
        $stmt->bind_param('iii', $harga_tertinggi, $id_user_pemenang, $id_barang);
        if ($stmt->execute()) {
          // Jika pembaruan berhasil, ambil status terbaru dari lelang
          $sql_get_status = "SELECT status FROM tb_lelang WHERE id_barang = ?";
          if ($stmt_status = $mysqli->prepare($sql_get_status)) {
            $stmt_status->bind_param('i', $id_barang);
            if ($stmt_status->execute()) {
              $stmt_status->bind_result($status);
              $stmt_status->fetch();
              $stmt_status->close();
            }
          }
          header('location: dashboard.php');
          exit;
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
    <title>Close Auction - Petugas</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
  </head>

  <body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
      <h2>Close Auction - Petugas</h2>

      <?php
      // Tampilkan pesan berdasarkan status lelang
      if (isset($_POST['id_barang'])) {
        if ($id_user_pemenang > 0) {
          echo '<h3>Lelang telah ditutup.</h3>';
        } else {
          echo '<h3>Tidak ada pemenang lelang.</h3>';
        }
      } else {
        // Jika tindakan formulir belum dilakukan, tampilkan formulir
        echo '<h3>Anda yakin ingin menutup lelang ini?</h3>';
        echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">';
        echo '<input type="hidden" name="id_barang" value="' . $_GET['id_barang'] . '">';
        echo '<button type="submit" class="btn btn-danger">Tutup Lelang</button>';
        echo '</form>';
      }
      ?>

      <a href="dashboard.php" class="btn btn-secondary mt-2">Kembali</a>
    </div>

    <?php include '../../includes/footer.php'; ?>