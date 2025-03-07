<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Pelelangan Online</title>
  <!-- Tautan ke berkas CSS -->
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/style.css">
  <!-- Tautan ke berkas JavaScript -->
  <script src="/assets/js/jquery.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/script.js"></script>
</head>
<body class="d-flex flex-column h-100">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="/">Pelelangan Online</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php
          // Periksa apakah pengguna sudah login atau belum
        if (isset($_SESSION['user_id'])) {
            // Jika sudah login, tampilkan tautan Logout
          echo '<li class="nav-item">
          <a class="nav-link" href="/pages/logout.php">Logout</a>
          </li>';
        } else {
            // Jika belum login, tampilkan tautan Login dan Registrasi
          echo '<li class="nav-item">
          <a class="nav-link" href="/pages/login.php">Login</a>
          </li>
          <li class="nav-item">
          <a class="nav-link" href="/pages/register.php">Registrasi</a>
          </li>';
        }
        ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Container utama dengan padding-top untuk kompensasi navbar fixed -->
<main class="flex-shrink-0 pt-5 mt-4">
  <div class="container">
      <!-- Ini adalah bagian utama konten aplikasi yang akan diisi oleh halaman-halaman lain -->