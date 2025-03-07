<!-- Improved Navbar with modern design -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
      <i class="bi bi-hammer me-2"></i>
      Pelelangan Online
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
      <?php
        // Check if user is logged in
      if (isset($_SESSION['user_id'])) {
          // Variables for common menu items
        $dashboardItem = '<li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="dashboard.php">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        </li>';
        $logoutItem = '<li class="nav-item">
        <a class="nav-link d-flex align-items-center text-danger" href="/pages/logout.php">
        <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
        </li>';

          // Display navigation based on user role
        switch ($_SESSION['user_role']) {
          case 'admin':
          echo $dashboardItem;
          echo '<li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="manage_items.php">
          <i class="bi bi-box-seam me-1"></i> Kelola Barang
          </a>
          </li>
          <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="manage_users.php">
          <i class="bi bi-people me-1"></i> Kelola Pengguna
          </a>
          </li>
          <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="generate_report.php">
          <i class="bi bi-file-earmark-text me-1"></i> Generate Laporan
          </a>
          </li>';
          echo $logoutItem;
          break;

          case 'petugas':
          echo $dashboardItem;
          echo '<li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="manage_items.php">
          <i class="bi bi-box-seam me-1"></i> Kelola Barang
          </a>
          </li>
          <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="manage_auctions.php">
          <i class="bi bi-currency-exchange me-1"></i> Kelola Lelang
          </a>
          </li>
          <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="generate_report.php">
          <i class="bi bi-file-earmark-text me-1"></i> Generate Laporan
          </a>
          </li>';
          echo $logoutItem;
          break;

          case 'masyarakat':
          echo $dashboardItem;
          echo '<li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="view_auctions.php">
          <i class="bi bi-gift me-1"></i> Lihat Lelang
          </a>
          </li>';
          echo $logoutItem;
          break;
        }
      } else {
          // Display options for users who are not logged in
        echo '<li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="index.php">
        <i class="bi bi-house-door me-1"></i> Beranda
        </a>
        </li>
        <li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="login.php">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </a>
        </li>
        <li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="register.php">
        <i class="bi bi-person-plus me-1"></i> Register
        </a>
        </li>';
      }
      ?>
    </ul>
  </div>
</div>
</nav>