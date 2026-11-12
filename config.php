<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kampus";

// 1. Koneksi awal ke server MySQL (tanpa memilih database dulu)
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// 2. Buat database jika belum ada
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $db");

// 3. Gunakan database tersebut
mysqli_select_db($conn, $db);

// 4. Buat tabel tugas jika belum ada
$sql_tabel = "CREATE TABLE IF NOT EXISTS tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    nama_mahasiswa VARCHAR(100) NOT NULL,
    mata_kuliah VARCHAR(100) NOT NULL,
    judul_tugas VARCHAR(150) NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql_tabel);
?>