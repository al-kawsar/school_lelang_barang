<?php
// Sisipkan konfigurasi dan file database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Periksa apakah pengguna masuk atau belum, dan periksa peran pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'masyarakat') {
    header('location: ../login.php');
    exit;
}

// Query untuk mendapatkan daftar barang yang sedang dilelang dengan status "dibuka"
$sql = "SELECT b.id_barang, b.gambar, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang, 
IFNULL(MAX(h.penawaran_harga), b.harga_awal) AS harga_tertinggi
FROM tb_barang b
LEFT JOIN history_lelang h ON b.id_barang = h.id_barang
INNER JOIN tb_lelang l ON b.id_barang = l.id_barang AND l.status = 'dibuka'
GROUP BY b.id_barang, b.nama_barang, b.tgl, b.harga_awal, b.deskripsi_barang
ORDER BY b.tgl ASC";



// Eksekusi query
$result = $mysqli->query($sql);

// Inisialisasi variabel
$barangs = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barangs[] = $row;
    }
}

// Tutup koneksi database
$result->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang Dilelang - Masyarakat</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="d-flex flex-column h-100">
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Daftar Barang Dilelang - Masyarakat</h2>
        <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Gambar Barang</th>
                    <th>Nama Barang</th>
                    <th>Tanggal Lelang</th>
                    <th>Harga Awal</th>
                    <th>Harga Tertinggi Saat Ini</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barangs as $barang) : ?>
                    <tr>
                        <td><img src="../../uploads/<?php echo $barang['gambar']; ?>" alt="<?php echo $barang['nama_barang']; ?>" class="img-thumbnail" width="100"></td>
                        <td><?php echo $barang['nama_barang']; ?></td>
                        <td><?php echo $barang['tgl']; ?></td>
                        <td>Rp <?php echo number_format($barang['harga_awal'], 0, ",", "."); ?></td>
                        <td>Rp <?php echo number_format($barang['harga_tertinggi'], 0, ",", "."); ?></td>
                        <td><?php echo $barang['deskripsi_barang']; ?></td>
                        <td><a href="place_bid.php?id_barang=<?php echo $barang['id_barang']; ?>" class="btn btn-primary">Tawar Harga</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include '../../includes/footer.php'; ?>