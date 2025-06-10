<?php
session_start();
$conn = new mysqli("localhost", "root", "", "project");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangani aksi approve / reject / cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_persetujuan = $_POST['id_persetujuan'];
    $new_status = $_POST['status_persetujuan'];

    $update = $conn->prepare("UPDATE Persetujuan SET status_persetujuan = ? WHERE id_persetujuan = ?");
    $update->bind_param('ss', $new_status, $id_persetujuan);
    $update->execute();
}

// Ambil semua data persetujuan dengan join untuk info tambahan
$result = $conn->query("SELECT p.*, u.username, r.nama_ruangan, pm.waktu_mulai, pm.waktu_selesai, pm.tanggal_peminjaman FROM Persetujuan p
                        JOIN User u ON p.id_user = u.id_user
                        JOIN Ruangan r ON p.id_ruangan = r.id_ruangan
                        JOIN Peminjaman pm ON pm.id_ruangan = p.id_ruangan AND pm.id_user = p.id_user
                        ORDER BY p.status_persetujuan DESC, p.id_persetujuan DESC");

if ($result->num_rows > 0) {
    $persetujuan = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Tidak ada data persetujuan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Peminjaman</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 2rem;
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
<body>
    <div class="container">
        <div class="header">
            <h2>Daftar Persetujuan Peminjaman Ruangan</h2>
            <a href="adminDashboard.php" class="btn btn-secondary mb-3">&larr; Kembali ke Dashboard Admin</a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama User</th>
                    <th>Nama Ruangan</th>
                    <th>Tanggal Peminjaman</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Berkas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persetujuan as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['username']) ?></td>
                        <td><?= htmlspecialchars($p['nama_ruangan']) ?></td>
                        <td><?= htmlspecialchars($p['tanggal_peminjaman']) ?></td>
                        <td><?= htmlspecialchars($p['waktu_mulai']) ?></td>
                        <td><?= htmlspecialchars($p['waktu_selesai']) ?></td>
                        <td>
                            <?php if ($p['berkas']): ?>
                                <a href="uploads/<?= htmlspecialchars($p['berkas']) ?>" target="_blank">Lihat PDF</a>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </td>
                        <td><?= ucfirst($p['status_persetujuan']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="id_persetujuan" value="<?= $p['id_persetujuan'] ?>">
                                <?php if ($p['status_persetujuan'] === 'menunggu'): ?>
                                    <button name="status_persetujuan" value="disetujui" class="btn btn-success btn-sm">Setujui</button>
                                    <button name="status_persetujuan" value="ditolak" class="btn btn-danger btn-sm">Tolak</button>
                                <?php else: ?>
                                    <button name="status_persetujuan" value="menunggu" class="btn btn-warning btn-sm">Batalkan</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
