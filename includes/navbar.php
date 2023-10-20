<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Pelelangan Online</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <?php
        // Periksa apakah pengguna sudah login atau belum
        if (isset($_SESSION['user_id'])) {
          // Jika sudah login, tampilkan tautan sesuai peran pengguna
          if ($_SESSION['user_role'] == 'admin') {
            // Tautan untuk admin
            echo '<li class="nav-item">
                                <a class="nav-link" href="dashboard.php">Dashboard</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="manage_items.php">Kelola Barang</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="manage_users.php">Kelola Pengguna</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="generate_report.php">Generate Laporan</a>
                              </li>
                              <li class="nav-item">
                              <a class="nav-link" href="' . BASE_URL . '/pages/logout.php">Logout</a>
                              </li>';
          } elseif ($_SESSION['user_role'] == 'petugas') {
            // Tautan untuk petugas
            echo '<li class="nav-item">
                                <a class="nav-link" href="dashboard.php">Dashboard</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="manage_items.php">Kelola Barang</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="manage_auctions.php">Kelola Lelang</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="generate_report.php">Generate Laporan</a>
                              </li>
                              <li class="nav-item">
                              <a class="nav-link" href="' . BASE_URL . '/pages/logout.php">Logout</a>
                              </li>';
          } elseif ($_SESSION['user_role'] == 'masyarakat') {
            // Tautan untuk masyarakat
            echo '<li class="nav-item">
                                <a class="nav-link" href="dashboard.php">Dashboard</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" href="view_auctions.php">Lihat Lelang</a>
                              </li>
                              <li class="nav-item">
                              <a class="nav-link" href="' . BASE_URL . '/pages/logout.php">Logout</a>
                              </li>';
          }
        }
        ?>
      </ul>
    </div>
  </div>
</nav>