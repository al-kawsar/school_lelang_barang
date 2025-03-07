<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';
// Periksa apakah admin masuk atau belum
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('location: ../login.php');
  exit;
}
// Dapatkan informasi tentang jumlah pengguna, barang, dan lelang yang tersedia
$sql_user_count = "SELECT COUNT(*) AS total_users FROM tb_masyarakat";
$sql_barang_count = "SELECT COUNT(*) AS total_barang FROM tb_barang";
$sql_lelang_count = "SELECT COUNT(*) AS total_lelang FROM tb_lelang";
$sql_petugas_count = "SELECT COUNT(*) AS total_petugas FROM tb_petugas";
$total_users = 0;
$total_barang = 0;
$total_lelang = 0;
$total_petugas = 0;
// Ambil total pengguna
if ($result = $mysqli->query($sql_user_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_users = $row['total_users'];
  }
  $result->free();
}
// Ambil total barang
if ($result = $mysqli->query($sql_barang_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_barang = $row['total_barang'];
  }
  $result->free();
}
// Ambil total lelang
if ($result = $mysqli->query($sql_lelang_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_lelang = $row['total_lelang'];
  }
  $result->free();
}
// Ambil total petugas
if ($result = $mysqli->query($sql_petugas_count)) {
  if ($row = $result->fetch_assoc()) {
    $total_petugas = $row['total_petugas'];
  }
  $result->free();
}

// Ambil data lelang terbaru (5 data terakhir)
$sql_recent_auctions = "SELECT l.*, b.nama_barang, b.harga_awal, m.nama_lengkap AS penawar
FROM tb_lelang l
LEFT JOIN tb_barang b ON l.id_barang = b.id_barang
LEFT JOIN tb_masyarakat m ON l.id_user = m.id_user
ORDER BY l.tgl_lelang DESC LIMIT 5";
$recent_auctions = [];
if ($result = $mysqli->query($sql_recent_auctions)) {
  while ($row = $result->fetch_assoc()) {
    $recent_auctions[] = $row;
  }
  $result->free();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Admin</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .stat-card {
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
      border: none;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .stat-card .card-body {
      padding: 1.5rem;
    }
    .stat-card .icon {
      font-size: 2.5rem;
      opacity: 0.8;
    }
    .stat-card .card-title {
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.3rem;
      color: #6c757d;
    }
    .stat-card .card-text {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0;
    }
    .quick-action {
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }
    .quick-action:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .recent-table {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .recent-table thead {
      background-color: #f8f9fa;
    }
    .admin-heading {
      position: relative;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }
    .admin-heading::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 3px;
      background-color: #007bff;
    }
    .admin-welcome {
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body class="d-flex flex-column h-100">
  <?php include '../../includes/navbar.php'; ?>

  <main class="flex-shrink-0 pt-5 mt-4">
    <div class="container">
      <div class="admin-welcome">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h2 class="admin-heading mb-2">Dashboard Admin</h2>
            <p class="lead mb-0">Selamat datang di panel admin. Kelola seluruh sistem lelang dari sini.</p>
          </div>
          <div class="col-md-4 text-md-end">
            <span class="badge bg-primary p-2">Login sebagai: Admin</span>
          </div>
        </div>
      </div>

      <!-- Statistik -->
      <div class="row mb-4">
        <div class="col-md-3 mb-3">
          <div class="stat-card card bg-primary text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-white-50">Total Pengguna</h5>
                <p class="card-text"><?php echo $total_users; ?></p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="stat-card card bg-success text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-white-50">Total Barang</h5>
                <p class="card-text"><?php echo $total_barang; ?></p>
              </div>
              <div class="icon">
                <i class="fas fa-box"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="stat-card card bg-info text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-white-50">Total Lelang</h5>
                <p class="card-text"><?php echo $total_lelang; ?></p>
              </div>
              <div class="icon">
                <i class="fas fa-gavel"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="stat-card card bg-warning text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-white-50">Total Petugas</h5>
                <p class="card-text"><?php echo $total_petugas; ?></p>
              </div>
              <div class="icon">
                <i class="fas fa-user-tie"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Aksi Cepat -->
      <h4 class="mb-3">Aksi Cepat</h4>
      <div class="row mb-4">
        <div class="col-md-3 mb-3">
          <div class="quick-action card h-100">
            <div class="card-body text-center">
              <i class="fas fa-plus-circle text-primary mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Tambah Barang</h5>
              <a href="barang/tambah.php" class="btn btn-sm btn-primary mt-2">Tambah</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="quick-action card h-100">
            <div class="card-body text-center">
              <i class="fas fa-user-plus text-success mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Tambah Petugas</h5>
              <a href="petugas/tambah.php" class="btn btn-sm btn-success mt-2">Tambah</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="quick-action card h-100">
            <div class="card-body text-center">
              <i class="fas fa-list text-info mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Kelola Lelang</h5>
              <a href="lelang/index.php" class="btn btn-sm btn-info mt-2">Lihat</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="quick-action card h-100">
            <div class="card-body text-center">
              <i class="fas fa-file-alt text-secondary mb-3" style="font-size: 2rem;"></i>
              <h5 class="card-title">Laporan</h5>
              <a href="laporan/index.php" class="btn btn-sm btn-secondary mt-2">Lihat</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Lelang Terbaru -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card recent-table">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Lelang Terbaru</h5>
              <a href="lelang/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th>Barang</th>
                      <th>Tanggal</th>
                      <th>Harga Awal</th>
                      <th>Harga Akhir</th>
                      <th>Penawar</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($recent_auctions)): ?>
                      <tr>
                        <td colspan="6" class="text-center py-3">Belum ada data lelang</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($recent_auctions as $auction): ?>
                        <tr>
                          <td><?php echo $auction['nama_barang']; ?></td>
                          <td><?php echo date('d M Y', strtotime($auction['tgl_lelang'])); ?></td>
                          <td>Rp <?php echo number_format($auction['harga_awal']); ?></td>
                          <td>Rp <?php echo number_format($auction['harga_akhir'] ?: 0); ?></td>
                          <td><?php echo $auction['penawar'] ?: '-'; ?></td>
                          <td>
                            <?php if ($auction['status'] == 'dibuka'): ?>
                              <span class="badge bg-success">Dibuka</span>
                            <?php elseif ($auction['status'] == 'ditutup'): ?>
                              <span class="badge bg-danger">Ditutup</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Pending</span>
                            <?php endif; ?>
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

  <script src="../../assets/js/jquery.min.js"></script>
  <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>