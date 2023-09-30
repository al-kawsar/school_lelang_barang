<?php
// Konfigurasi database
$host = "127.0.0.1"; // Ganti dengan nama host database Anda
$username = "root"; // Ganti dengan nama pengguna database Anda
$password = ""; // Ganti dengan kata sandi database Anda
$database = "lelang_php"; // Ganti dengan nama database Anda

// Membuat koneksi ke database
$connection = mysqli_connect($host, $username, $password, $database);

// Periksa apakah koneksi berhasil
if (!$connection) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}
