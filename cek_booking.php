<?php
session_start();
$conn = new mysqli("localhost", "root", "", "project");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$query = "
    SELECT 
        r.nama_ruangan, r.lokasi,
        p.tanggal_peminjaman, p.waktu_mulai, p.waktu_selesai,
        ps.status_persetujuan, ps.berkas
    FROM Peminjaman p
    JOIN Ruangan r ON r.id_ruangan = p.id_ruangan
    JOIN Persetujuan ps ON ps.id_user = p.id_user AND ps.id_ruangan = p.id_ruangan
    WHERE p.id_user = '$id_user'
    ORDER BY p.tanggal_peminjaman DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Booking</title>
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
        .table {
            margin-top: 2rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            font-weight: bold;
        }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="header">
        <h2>Status Booking Anda</h2>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Ruangan</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Status</th>
                <th>Berkas</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($data = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($data['nama_ruangan']) ?> (<?= htmlspecialchars($data['lokasi']) ?>)</td>
                    <td><?= htmlspecialchars($data['tanggal_peminjaman']) ?></td>
                    <td><?= htmlspecialchars($data['waktu_mulai']) ?> - <?= htmlspecialchars($data['waktu_selesai']) ?></td>
                    <td>
                        <?php
                        switch ($data['status_persetujuan']) {
                            case 'disetujui': echo '<span class="badge bg-success">Disetujui</span>'; break;
                            case 'ditolak': echo '<span class="badge bg-danger">Ditolak</span>'; break;
                            default: echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($data['berkas']): ?>
                            <a href="uploads/<?= htmlspecialchars($data['berkas']) ?>" target="_blank">Lihat PDF</a>
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="userDashboard.php" class="btn btn-secondary">Kembali</a>
</div>

</body>
</html>
