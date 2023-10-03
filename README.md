# Aplikasi Pelelangan Online Berbasis Website Sederhana

Aplikasi Pelelangan Online ini adalah sebuah platform sederhana yang memungkinkan pengguna untuk mengikuti lelang barang, menawar harga, dan mengelola data barang. Aplikasi ini dibangun menggunakan teknologi PHP, MySQL, Bootstrap, dan jQuery.

## Fitur Utama

Aplikasi ini memiliki tiga jenis pengguna utama:

- **Admin**: Pengguna dengan hak akses tertinggi. Mereka dapat mengelola pengguna, barang, dan lelang, serta menghasilkan laporan PDF.
- **Petugas**: Pengguna yang bertanggung jawab mengelola barang dan lelang.
- **Masyarakat**: Pengguna biasa yang dapat mengikuti lelang dan menawar harga.

### Fitur Admin:

- **Login**: Admin dapat masuk ke aplikasi dengan akun mereka.
- **Registrasi**: Admin dapat mendaftarkan akun baru jika diperlukan.
- **Logout**: Admin dapat keluar dari aplikasi.
- **Mengelola Pendataan Barang**: Admin memiliki akses untuk menambah, mengubah, atau menghapus informasi barang yang akan dilelang, termasuk gambar barang.
- **Mengelola Petugas**: Admin dapat melakukan operasi CRUD (Create, Read, Update, Delete) terhadap data petugas.
- **Generate Laporan ke PDF**: Admin dapat membuat laporan dalam format PDF yang berisi informasi tentang barang-barang yang telah dilelang.

### Fitur Petugas:

- **Login**: Petugas dapat masuk ke aplikasi dengan akun mereka.
- **Logout**: Petugas dapat keluar dari aplikasi.
- **Mengelola Pendataan Barang**: Petugas dapat menambahkan, mengubah, atau menghapus informasi barang yang akan dilelang, termasuk gambar barang.
- **Membuka dan Menutup Lelang**: Petugas dapat mengatur status lelang untuk barang-barang tertentu.
- **Generate Laporan ke PDF**: Petugas dapat membuat laporan PDF tentang barang-barang yang telah dilelang.

### Fitur Masyarakat:

- **Login**: Masyarakat dapat masuk ke aplikasi dengan akun mereka.
- **Logout**: Masyarakat dapat keluar dari aplikasi.
- **Registrasi**: Masyarakat dapat mendaftar dengan mengisi formulir yang mencakup nama lengkap, nomor telepon, dan kata sandi.
- **Penawaran / Menawar Harga**: Masyarakat dapat melihat barang-barang yang sedang dilelang, melihat gambar barang, dan menawar harga sesuai keinginan mereka. Setiap penawaran dicatat dalam history_lelang.

## Struktur Database

Aplikasi ini menggunakan database MySQL dengan tabel-tabel berikut:

- `tb_masyarakat`: Menyimpan data pengguna masyarakat seperti nama lengkap, nama pengguna (username), kata sandi, dan nomor telepon.
- `tb_barang`: Berisi informasi tentang barang-barang yang akan dilelang, termasuk gambar, nama barang, tanggal, harga awal dan deskripsi.
- `tb_lelang`: Menyimpan data lelang, termasuk status lelang (dibuka/ditutup), harga akhir, dan kaitannya dengan pengguna dan barang.
- `tb_petugas`: Berisi data petugas dengan informasi seperti nama petugas, nama pengguna (username), kata sandi, dan level akses.
- `tb_level`: Menggambarkan level akses, seperti administrator atau petugas.
- `history_lelang`: Mencatat setiap penawaran harga yang dibuat oleh masyarakat.

## Cara Kerja Aplikasi

### Langkah 1: Registrasi dan Login

- Registrasi: Masyarakat dapat mendaftar dengan mengisi formulir registrasi dengan informasi pribadi mereka.
- Login: Pengguna dapat masuk dengan nama pengguna dan kata sandi mereka.

### Langkah 2: Masyarakat

- Penawaran Harga: Masyarakat dapat melihat barang yang dilelang, melihat gambar barang, dan menawar harga sesuai keinginan mereka. Setiap penawaran dicatat dalam history_lelang.

### Langkah 3: Petugas

- Mengelola Pendataan Barang: Petugas dapat menambahkan, mengubah, atau menghapus informasi barang yang akan dilelang.
- Membuka dan Menutup Lelang: Petugas dapat mengatur status lelang untuk barang-barang tertentu.
- Generate Laporan ke PDF: Petugas dapat membuat laporan PDF tentang barang-barang yang telah dilelang.

### Langkah 4: Admin

- Admin memiliki hak akses yang sama dengan petugas untuk mengelola data barang, dan menghasilkan laporan PDF.
- Admin juga dapat menambahkan, melihat, mengedit, atau menghapus akun petugas seperti proses registrasi masyarakat.

## Cara Menjalankan Aplikasi

1. Clone repositori ini ke mesin lokal Anda.
2. Konfigurasi koneksi database Anda dalam file `config.php`.
3. Import struktur database dan data awal yang terdapat dalam file `lelang_php.sql`.
4. Buka aplikasi di browser Anda dengan mengakses URL lokal (misalnya, `http://localhost/lelang_php`).

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini, Anda dapat melakukan fork repositori ini, lakukan perubahan, dan kirimkan pull request. Kami akan sangat menghargai kontribusi Anda.

## Lisensi

Proyek ini dilisensikan di bawah [Lisensi MIT](LICENSE).
