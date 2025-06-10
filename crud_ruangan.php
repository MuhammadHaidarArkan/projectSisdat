<?php
session_start();
$conn = new mysqli("localhost", "root", "", "project");

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hapus ruangan
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM Fasilitas_Ruangan WHERE id_ruangan = '$id'");
    $conn->query("DELETE FROM Ruangan WHERE id_ruangan = '$id'");
    header("Location: crud_ruangan.php");
    exit;
}

// Tambah ruangan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    if (isset($_POST['nama_ruangan'], $_POST['lokasi'], $_POST['kapasitas'], $_POST['status'])) {
        $nama = $_POST['nama_ruangan'];
        $lokasi = $_POST['lokasi'];
        $kapasitas = $_POST['kapasitas'];
        $status = $_POST['status'];

        // Tambahkan ruangan tanpa menyertakan id_ruangan
        $conn->query("INSERT INTO Ruangan (nama_ruangan, lokasi, kapasitas, status) 
                      VALUES ('$nama', '$lokasi', $kapasitas, '$status')");

        // Ambil id_ruangan yang baru saja ditambahkan
        $id_ruangan = $conn->insert_id;

        // Tambahkan fasilitas jika ada
        if (isset($_POST['fasilitas'])) {
            foreach ($_POST['fasilitas'] as $fasilitas) {
                $conn->query("INSERT INTO Fasilitas_Ruangan (id_ruangan, Fasilitas) 
                              VALUES ('$id_ruangan', '$fasilitas')");
            }
        }

        header("Location: crud_ruangan.php");
        exit;
    }
}

// Update ruangan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id_ruangan'];
    $nama = $_POST['nama_ruangan'];
    $lokasi = $_POST['lokasi'];
    $kapasitas = $_POST['kapasitas'];
    $status = $_POST['status'];

    $conn->query("UPDATE Ruangan SET nama_ruangan='$nama', lokasi='$lokasi', kapasitas=$kapasitas, status='$status' WHERE id_ruangan='$id'");

    // Hapus fasilitas lama & insert yang baru
    $conn->query("DELETE FROM Fasilitas_Ruangan WHERE id_ruangan = '$id'");
    if (isset($_POST['fasilitas'])) {
        foreach ($_POST['fasilitas'] as $fasilitas) {
            $conn->query("INSERT INTO Fasilitas_Ruangan (id_ruangan, Fasilitas) 
                          VALUES ('$id', '$fasilitas')");
        }
    }

    header("Location: crud_ruangan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Ruangan</title>
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
        .form-section {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 1rem;
        }
        .btn {
            font-weight: bold;
        }
        .table {
            margin-top: 2rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <a href="adminDashboard.php" class="btn btn-secondary mb-3">← Kembali ke Dashboard Admin</a>
    <div class="container">
        <h2 class="mb-4">Manajemen Ruangan</h2>

        <!-- Form tambah atau edit -->
        <?php if (isset($_GET['edit'])): ?>
            <?php
                $id_edit = $_GET['edit'];
                $res = $conn->query("SELECT * FROM Ruangan WHERE id_ruangan = '$id_edit'");
                $row = $res->fetch_assoc();
                $fasilitas_db = [];
                $res_fasilitas = $conn->query("SELECT Fasilitas FROM Fasilitas_Ruangan WHERE id_ruangan = '$id_edit'");
                while ($f = $res_fasilitas->fetch_assoc()) {
                    $fasilitas_db[] = $f['Fasilitas'];
                }
            ?>
            <div class="form-section">
                <form method="POST" class="mb-4">
                    <input type="hidden" name="id_ruangan" value="<?= $id_edit ?>">
                    <div class="mb-3">
                        <label>Nama Ruangan</label>
                        <input type="text" name="nama_ruangan" class="form-control" value="<?= $row['nama_ruangan'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" value="<?= $row['lokasi'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control" value="<?= $row['kapasitas'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="tersedia" <?= $row['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="tidak_tersedia" <?= $row['status'] == 'tidak_tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fasilitas</label><br>
                        <?php
                        $all_fasilitas = ['Proyektor', 'AC', 'Whiteboard', 'Mic', 'Sound System'];
                        foreach ($all_fasilitas as $fas):
                        ?>
                            <label class="me-2">
                                <input type="checkbox" name="fasilitas[]" value="<?= $fas ?>" <?= in_array($fas, $fasilitas_db) ? 'checked' : '' ?>> <?= $fas ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Update Ruangan</button>
                    <a href="crud_ruangan.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        <?php else: ?>
            <div class="form-section">
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label>Nama Ruangan</label>
                        <input type="text" name="nama_ruangan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="tidak_tersedia">Tidak Tersedia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fasilitas</label><br>
                        <?php
                        foreach (['Proyektor', 'AC', 'Whiteboard', 'Mic', 'Sound System'] as $fas):
                        ?>
                            <label class="me-2">
                                <input type="checkbox" name="fasilitas[]" value="<?= $fas ?>"> <?= $fas ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-success">Tambah Ruangan</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Tabel Ruangan -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Lokasi</th>
                    <th>Kapasitas</th>
                    <th>Status</th>
                    <th>Fasilitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ruangans = $conn->query("SELECT * FROM Ruangan");
                while ($r = $ruangans->fetch_assoc()):
                    $id = $r['id_ruangan'];
                    $fasilitas = [];
                    $fs = $conn->query("SELECT Fasilitas FROM Fasilitas_Ruangan WHERE id_ruangan = '$id'");
                    while ($row = $fs->fetch_assoc()) {
                        $fasilitas[] = $row['Fasilitas'];
                    }
                ?>
                <tr>
                    <td><?= $r['nama_ruangan'] ?></td>
                    <td><?= $r['lokasi'] ?></td>
                    <td><?= $r['kapasitas'] ?></td>
                    <td><?= $r['status'] ?></td>
                    <td><?= implode(", ", $fasilitas) ?></td>
                    <td>
                        <a href="crud_ruangan.php?edit=<?= $id ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="crud_ruangan.php?delete=<?= $id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
