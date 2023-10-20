<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
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
  exit;
}

// Inisialisasi variabel
$user_id = $_SESSION['user_id'];
$barang_err = '';
$barang_id = $harga_penawaran = '';

// Memproses penawaran harga saat formulir diajukan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil harga awal dari database
  $sql_harga_awal = "SELECT harga_awal FROM tb_barang WHERE id_barang = ?";
  if ($stmt_harga_awal = $mysqli->prepare($sql_harga_awal)) {
    $stmt_harga_awal->bind_param('i', $_POST['barang_id']);
    if ($stmt_harga_awal->execute()) {
      $stmt_harga_awal->store_result();
      if ($stmt_harga_awal->num_rows == 1) {
        $stmt_harga_awal->bind_result($harga_awal);
        $stmt_harga_awal->fetch();
      } else {
        // Handle kesalahan jika harga awal tidak ditemukan
        echo 'Oops! Terjadi kesalahan saat mengambil harga awal.';
      }
      $stmt_harga_awal->close();
    }
  }

  // Validasi Harga Penawaran
  if (empty(trim($_POST['barang_id']))) {
    $barang_err = 'Pilih barang untuk menawar harga.';
  } else {
    $barang_id = trim($_POST['barang_id']);
  }

  if (empty(trim($_POST['harga_penawaran']))) {
    $barang_err = 'Masukkan harga penawaran.';
  } else {
    $harga_penawaran = str_replace(".", "", $_POST['harga_penawaran']);
  }

  if (!is_numeric($harga_penawaran) || $harga_penawaran <= 0) {
    $barang_err = 'Harga penawaran harus merupakan angka positif.';
  } elseif ($harga_penawaran < $harga_awal) {
    $barang_err = 'Harga penawaran tidak boleh kurang dari harga awal.';
  } else {
    // Validasi Harga Tertinggi Saat Ini
    $sql_harga_tertinggi = "SELECT MAX(penawaran_harga) AS harga_tertinggi FROM history_lelang WHERE id_barang = ?";
    if ($stmt_harga_tertinggi = $mysqli->prepare($sql_harga_tertinggi)) {
      $stmt_harga_tertinggi->bind_param('i', $barang_id);
      if ($stmt_harga_tertinggi->execute()) {
        $stmt_harga_tertinggi->store_result();
        if ($stmt_harga_tertinggi->num_rows == 1) {
          $stmt_harga_tertinggi->bind_result($harga_tertinggi_saat_ini);
          $stmt_harga_tertinggi->fetch();

          if ($harga_penawaran <= $harga_tertinggi_saat_ini) {
            $barang_err = 'Harga penawaran harus lebih tinggi daripada harga tertinggi saat ini.';
          }
        } else {
          // Handle kesalahan jika harga tertinggi saat ini tidak ditemukan
          echo 'Oops! Terjadi kesalahan saat mengambil harga tertinggi saat ini.';
        }
        $stmt_harga_tertinggi->close();
      }
    }
  }

  // Query untuk mendapatkan id_lelang berdasarkan id_barang
  $sql_get_lelang_id = "SELECT id_lelang FROM tb_lelang WHERE id_barang = ?";
  if ($stmt_get_lelang_id = $mysqli->prepare($sql_get_lelang_id)) {
    $stmt_get_lelang_id->bind_param(
      'i',
      $barang_id
    ); // Gunakan $barang_id yang sesuai
    if ($stmt_get_lelang_id->execute()) {
      $stmt_get_lelang_id->store_result();
      if ($stmt_get_lelang_id->num_rows == 1) {
        $stmt_get_lelang_id->bind_result($id_lelang);
        $stmt_get_lelang_id->fetch();
      } else {
        // Handle kesalahan jika id_lelang tidak ditemukan
        echo 'Oops! Terjadi kesalahan saat mengambil id_lelang.';
      }
      $stmt_get_lelang_id->close();
    }
  }


  // Memeriksa apakah ada kesalahan input sebelum melakukan penawaran
  if (empty($barang_err)) {
    // Masukkan penawaran harga ke dalam history_lelang
    $sql = "INSERT INTO history_lelang (id_lelang, id_barang, id_user, penawaran_harga) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param('iiii', $id_lelang, $barang_id, $user_id, $harga_penawaran);
      if ($stmt->execute()) {
        // Penawaran berhasil, alihkan kembali ke dashboard
        // $id_barang = $_POST['barang_id'];
        header("location: place_bid.php?id_barang=" . $barang_id);
        exit;
      } else {
        echo 'Oops! Terjadi kesalahan. Silakan coba lagi nanti.';
      }
      $stmt->close();
    }
  }
}

// Kode untuk mengambil data barang dan memunculkan di tampilan detail barang
$barang_id = $_GET['id_barang']; // Mengambil parameter id dari URL

// Query untuk mendapatkan detail barang yang akan ditawar berdasarkan id_barang
$sql = "SELECT b.id_barang, b.nama_barang, b.gambar, b.tgl, b.harga_awal, b.deskripsi_barang,
        IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
        FROM tb_barang b
        LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
        LEFT JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
        WHERE b.id_barang = ?
        GROUP BY b.id_barang, b.nama_barang, b.gambar, b.tgl, b.harga_awal, b.deskripsi_barang
        ORDER BY b.tgl ASC";

if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $barang_id);
  if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id_barang, $nama_barang, $gambar, $tgl, $harga_awal, $deskripsi_barang, $harga_tertinggi);
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
  <style>
    .vertical-stepper {
      list-style: none;
      padding: 0;
    }

    .vertical-stepper .step {
      display: flex;
      align-items: center;
      border: 1px solid #ddd;
      padding: 10px;
      margin: 5px 0;
      border-radius: 5px;
      background-color: #f9f9f9;
    }

    .step-number {
      font-weight: bold;
      margin-right: 10px;
    }

    .step-title {
      flex: 1;
    }
  </style>

</head>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Penawaran Harga - Masyarakat</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
    <h4>Detail Barang</h4>
    <table class="table">
      <tr>
        <th>Gambar</th>
        <td><img src="../../uploads/<?php echo $gambar; ?>" class="img-thumbnail" width="100"></td>
      </tr>
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
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id_barang=<?php echo $id_barang; ?>" method="post">
      <input type="hidden" name="barang_id" value="<?php echo $id_barang; ?>"> <!-- Tambahkan input tersembunyi untuk id_barang -->
      <div class="form-group">
        <label for="harga_penawaran">Harga Penawaran</label>
        <input type="text" name="harga_penawaran" class="form-control" placeholder="Masukkan Harga Penawaran" value="<?php echo $harga_penawaran; ?>">
        <span class="text-danger"><?php echo $barang_err; ?></span>
      </div>
      <button type="submit" class="btn btn-primary">Tawar Harga</button>
    </form>
    <h4>Riwayat Penawaran</h4>
    <ul class="vertical-stepper">
      <?php
      // Query untuk mendapatkan riwayat penawaran yang sudah ada
      $sql_riwayat = "SELECT u.username, h.penawaran_harga FROM history_lelang h
                  JOIN tb_masyarakat u ON h.id_user = u.id_user
                  WHERE h.id_barang = ?
                  ORDER BY h.penawaran_harga DESC";

      if ($stmt_riwayat = $mysqli->prepare($sql_riwayat)) {
        $stmt_riwayat->bind_param('i', $barang_id);
        if ($stmt_riwayat->execute()) {
          $stmt_riwayat->store_result();
          if ($stmt_riwayat->num_rows > 0) {
            $stmt_riwayat->bind_result($username, $penawaran_harga);
            while ($stmt_riwayat->fetch()) {
              echo '<li>';
              echo '<div class="step">';
              echo '<span class="step-number">Rp' . number_format($penawaran_harga, 0, ",", ".") . '</span>';
              echo '<span class="step-title">' . $username . '</span>';
              echo '</div>';
              echo '</li>';
            }
          } else {
            echo '<li><div class="step">Belum ada riwayat penawaran.</div></li>';
          }
        }
        $stmt_riwayat->close();
      }
      ?>
    </ul>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const hargaInput = document.querySelector('input[name="harga_penawaran"]');

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