<?php
$koneksi = mysqli_connect("localhost", "root", "", "project");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>