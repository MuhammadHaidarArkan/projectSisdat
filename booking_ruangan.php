<?php
session_start();
$conn = new mysqli("localhost", "root", "", "project");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

// Ambil data ruangan & fasilitas
$ruangan_result = $conn->query("SELECT * FROM Ruangan");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_user = $_SESSION['id_user'];
    $id_ruangan = $_POST['id_ruangan'];
    $tanggal = $_POST['tanggal'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $berkas_name = "";

    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['berkas']['tmp_name'];
        $berkas_name = uniqid() . '_' . basename($_FILES['berkas']['name']);
        move_uploaded_file($tmp_name, "uploads/" . $berkas_name);
    }

    $id_persetujuan = uniqid("pjt_");
    $id_peminjaman = uniqid("pinj_");

    $conn->query("INSERT INTO Persetujuan (id_persetujuan, id_user, id_ruangan, berkas, status_persetujuan) VALUES ('$id_persetujuan', '$id_user', '$id_ruangan', '$berkas_name', 'menunggu')");
    $conn->query("INSERT INTO Peminjaman (id_peminjaman, id_ruangan, id_user, tanggal_peminjaman, waktu_mulai, waktu_selesai) VALUES ('$id_peminjaman', '$id_ruangan', '$id_user', '$tanggal', '$waktu_mulai', '$waktu_selesai')");

    echo "<script>alert('Pengajuan berhasil! Menunggu persetujuan admin.'); window.location.href='userDashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Ruangan</title>
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
        .form-section {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 1rem;
        }
        .btn {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="header">
        <h2>Form Booking Ruangan</h2>
    </div>

    <div class="form-section">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="id_ruangan" class="form-label">Pilih Ruangan</label>
                <select name="id_ruangan" id="id_ruangan" class="form-select" required>
                    <option value="">-- Pilih Ruangan --</option>
                    <?php while ($ruang = $ruangan_result->fetch_assoc()): ?>
                        <option value="<?= $ruang['id_ruangan'] ?>">
                            <?= $ruang['nama_ruangan'] ?> (<?= $ruang['lokasi'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload Berkas (PDF)</label>
                <input type="file" name="berkas" accept="application/pdf" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajukan</button>
            <a href="userDashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

</body>
</html>
