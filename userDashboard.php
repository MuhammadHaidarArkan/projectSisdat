<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$nama = $_SESSION['nama_user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .card {
            transition: transform 0.2s;
            border-radius: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-title {
            font-weight: bold;
            color: #1f2937;
        }
        .btn {
            font-weight: bold;
        }
        .logout-btn {
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="header">
        <h2>Selamat Datang, <?= htmlspecialchars($nama) ?></h2>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Booking Ruangan</h5>
                    <p class="card-text">Ajukan peminjaman ruangan dan upload rencana penggunaan dalam bentuk PDF.</p>
                    <a href="booking_ruangan.php" class="btn btn-primary">Ajukan Booking</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Cek Status Booking</h5>
                    <p class="card-text">Lihat status pengajuan ruangan yang telah kamu ajukan (menunggu, disetujui, atau ditolak).</p>
                    <a href="cek_booking.php" class="btn btn-warning">Lihat Status</a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="index.php" class="btn btn-danger logout-btn">Logout</a>
    </div>
</div>

</body>
</html>
