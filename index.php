<?php
// Sisipkan konfigurasi dan file database
require_once 'config/config.php';
require_once 'config/database.php';
// Fungsi untuk mendapatkan daftar barang yang sedang dilelang
function getAuctionItems()
{
  $sql = "SELECT lb.*, b.nama_barang, b.harga_awal, b.deskripsi_barang, b.gambar
  FROM tb_lelang lb
  INNER JOIN tb_barang b ON lb.id_barang = b.id_barang
  WHERE lb.status = 'dibuka'";
  $result = executeQuery($sql);
  $items = array();
  while ($row = fetchAssoc($result)) {
    $items[] = $row;
  }
  return $items;
}
// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
  // Jika sudah login, redirect ke dashboard sesuai peran pengguna
  if ($_SESSION['user_role'] == 'admin') {
    header("Location: pages/admin/dashboard.php");
    exit();
  } elseif ($_SESSION['user_role'] == 'petugas') {
    header("Location: pages/petugas/dashboard.php");
    exit();
  } elseif ($_SESSION['user_role'] == 'masyarakat') {
    header("Location: pages/masyarakat/dashboard.php");
    exit();
  }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-3">
  <h2>Selamat datang di Aplikasi Pelelangan Online</h2>
  <p>Di sini Anda dapat mengikuti lelang barang dan menawar harga sesuai keinginan Anda.</p>

  <h3 class="mb-3">Barang yang Sedang Dilelang</h3>
  <div class="row">
    <?php
    // Mendapatkan daftar barang yang sedang dilelang
    $auctionItems = getAuctionItems();
    if (empty($auctionItems)) {
      // Tampilkan pesan jika tidak ada barang yang tersedia
      echo '<div class="col-12">
      <div class="alert alert-info">
      Tidak ada barang yang sedang dilelang saat ini.
      </div>
      </div>';
    } else {
      foreach ($auctionItems as $item) {
        if ($item['status'] == 'dibuka') {
          // Tampilkan hanya barang dengan status "dibuka"
          echo '<div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
          <img src="uploads/' . $item['gambar'] . '" class="card-img-top" alt="' . $item['nama_barang'] . '" style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
          <h5 class="card-title">' . $item['nama_barang'] . '</h5>
          <p class="card-text"><strong>Harga Awal:</strong> Rp ' . number_format($item['harga_awal']) . '</p>
          <p class="card-text text-truncate">' . $item['deskripsi_barang'] . '</p>
          <a href="pages/login.php?id=' . $item['id_barang'] . '" class="btn btn-primary mt-auto">Lihat Detail</a>
          </div>
          </div>
          </div>';
        }
      }
    }
    ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>