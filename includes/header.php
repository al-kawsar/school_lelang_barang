<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Pelelangan Online</title>
  <!-- Tautan ke berkas CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
  <!-- Tautan ke berkas JavaScript -->
  <script src="<?php echo BASE_URL; ?>/assets/js/jquery.min.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/bootstrap.min.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="<?php echo BASE_URL; ?>">Pelelangan Online</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <?php
          // Periksa apakah pengguna sudah login atau belum
          if (isset($_SESSION['user_id'])) {
            // Jika sudah login, tampilkan tautan Logout
            echo '<li class="nav-item">
          <a class="nav-link" href="' . BASE_URL . 'pages/logout.php">Logout</a>
        </li>';
          } else {
            // Jika belum login, tampilkan tautan Login dan Registrasi
            echo '<li class="nav-item">
          <a class="nav-link" href="' . BASE_URL . 'pages/login.php">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="' . BASE_URL . 'pages/register.php">Registrasi</a>
        </li>';
          }
          ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Container utama -->
  <div class="container mt-4">
    <!-- Ini adalah bagian utama konten aplikasi yang akan diisi oleh halaman-halaman lain -->
    <!-- Anda dapat menambahkan kode HTML di sini sesuai dengan kebutuhan halaman -->