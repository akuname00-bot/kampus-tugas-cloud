<?php
$host = "localhost";
$user = "root";
$pass = ""; // Isi password database di server masing-masing
$db   = "db_kampus";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>