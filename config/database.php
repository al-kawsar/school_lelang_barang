<?php
// Include file konfigurasi
include_once 'config.php';

// Membuat koneksi ke database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi ke database gagal: " . $mysqli->connect_error);
}

// Fungsi untuk menjalankan query SQL
function executeQuery($sql)
{
    global $mysqli;
    return $mysqli->query($sql);
}

// Fungsi untuk mendapatkan hasil query sebagai array
function fetchArray($result)
{
    return $result->fetch_array();
}

// Fungsi untuk mendapatkan hasil query sebagai associative array
function fetchAssoc($result)
{
    return $result->fetch_assoc();
}
