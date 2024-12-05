<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_kandidat'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $visi = mysqli_real_escape_string($conn, $_POST['visi']);
    $misi = mysqli_real_escape_string($conn, $_POST['misi']);
    
    $foto = $_FILES['foto']['name'];
    $target_dir = "uploads/"; 
    $target_file = $target_dir . uniqid() . '_' . basename($foto); 
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['foto']['tmp_name']);
    if ($check === false) {
        $_SESSION['error_message'] = "File yang diupload bukan gambar.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($_FILES['foto']['size'] > 500000) { // 500KB
        $_SESSION['error_message'] = "Maaf, ukuran file terlalu besar.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Save candidate data to the database
        $query = "INSERT INTO kandidat (nama, prodi, foto, visi, misi) VALUES ('$nama', '$prodi', '$target_file', '$visi', '$misi')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Kandidat berhasil ditambahkan.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat menambahkan kandidat: " . mysqli_error($conn);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Maaf, terjadi kesalahan saat mengupload file. Pastikan folder 'uploads/' ada dan dapat ditulis.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $query_foto = "SELECT foto FROM kandidat WHERE id = $delete_id";
    $result_foto = mysqli_query($conn, $query_foto);
    $foto_kandidat = mysqli_fetch_assoc($result_foto);
    
    $query = "DELETE FROM kandidat WHERE id = $delete_id";
    if (mysqli_query($conn, $query)) {
        // Delete photo from server
        if (file_exists($foto_kandidat['foto'])) {
            unlink($foto_kandidat['foto']);
        }
        
        $_SESSION['success_message'] = "Kandidat berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menghapus kandidat: " . mysqli_error($conn);
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_kandidat'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $visi = mysqli_real_escape_string($conn, $_POST['visi']);
    $misi = mysqli_real_escape_string($conn, $_POST['misi']);
    
    if ($_FILES['foto']['name']) {
        $foto = $_FILES['foto']['name'];
        $target_dir = "uploads/"; // Use the same directory
        $target_file = $target_dir . uniqid() . '_' . basename($foto);
        
        $query_old_foto = "SELECT foto FROM kandidat WHERE id = $id";
        $result_old_foto = mysqli_query($conn, $query_old_foto);
        $old_foto = mysqli_fetch_assoc($result_old_foto);
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Delete old photo if exists
            if (file_exists($old_foto['foto'])) {
                unlink($old_foto['foto']);
            }
            
            $query = "UPDATE kandidat SET nama='$nama', prodi='$prodi', foto='$target_file', visi='$visi', misi='$misi' WHERE id=$id";
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat mengupload foto.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        // If no new photo, update without changing the photo
        $query = "UPDATE kandidat SET nama='$nama', prodi='$prodi', visi='$visi', misi='$misi' WHERE id=$id";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Kandidat berhasil diperbarui.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat memperbarui kandidat: " . mysqli_error($conn);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get candidate data and total voting
$query_kandidat = "SELECT * FROM kandidat";
$result_kandidat = mysqli_query($conn, $query_kandidat);

// Get total voting for each candidate
$query_voting = "SELECT kandidat_id, COUNT(*) as total_voting FROM voting GROUP BY kandidat_id";
$result_voting = mysqli_query($conn, $query_voting);

// Store total voting in an array
$total_voting = [];
while ($voting = mysqli_fetch_assoc($result_voting)) {
    $total_voting[$voting['kandidat_id']] = $voting['total_voting'];
}

// Get messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear messages from session after displaying
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="src/logo-poltekkes-Photoroom.png">
    <style>
        :root {
            --bg-primary: #f9fafb;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
        }

        body {
            background-color: var(--bg-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--text-dark);
            background-image: url('src/kampus.jpg'); /* Ganti dengan path gambar kamu */
            background-size: cover; /* Supaya gambar memenuhi seluruh layar */
            background-position: center; /* Posisikan di tengah */
            background-repeat: no-repeat; /* Jangan ulang gambar */
            background-attachment: fixed; /* Biar gambar tetap diam saat di-scroll */
        }

        h3 {
        background-color: rgba(255, 255, 255, 0.8); /* Warna putih semi-transparan */
        color: #1f2937; /* Warna teks gelap agar kontras */
        padding: 5px 15px; /* Memberikan ruang di sekitar teks */
        border-radius: 8px; /* Sudut melengkung */
        display: inline-block; /* Supaya hanya selebar teks */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Bayangan lembut */
        font-weight: bold; /* Supaya lebih menonjol */
        font-size: 1.5rem; /* Ukuran teks yang sesuai */
        }

        .header {
            background: linear-gradient(135deg, var(--accent-color), #e79cff);
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
        }

        .btn-logout {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-logout:hover {
            background-color: #2563eb;
            transform: scale(1.05);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .footer {
            background: linear-gradient(135deg, #e79cff, #4338ca);
            color: white;
            text-align: center;
            padding: 1rem;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .card img {
            object-fit: cover; /* Memastikan gambar menyesuaikan dengan ukuran card */
            height: 200px; /* Menetapkan tinggi gambar */
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-body{
            flex-grow: 1;
            display: flex;
            flex-direction: column; /* Mengatur isi body card secara vertikal */
            justify-content: space-between; /* Menjaga jarak antara elemen */
        }


        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: scale(1.05);
        }

        .alert {
            margin-bottom: 20px;
        }

        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: bold;
        }

        .container {
        display: flex;           /* Aktifkan flexbox */
        flex-direction: column;  /* Elemen disusun secara vertikal */
        align-items: start;      /* Elemen rata kiri */
        gap: 10px;               /* Jarak antara tombol dan teks */
        }

        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h2>Selamat Datang,<strong> <?php echo htmlspecialchars($_SESSION['nama']); ?> | Admin</strong> </h2>
            <a href="logout_admin.php" class="btn btn-danger">Log Out</a>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <!-- Tombol untuk Menambah Kandidat -->
                <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addCandidateModal">Tambah Kandidat</button>

                <!-- Card untuk Menampilkan Kandidat -->
                <div class="container">
                    <h3>Kandidat dan Total Voting</h3>
                </div>
                <div class="row">
                    <?php 
                    // Reset pointer result set
                    mysqli_data_seek($result_kandidat, 0);
                    while($kandidat = mysqli_fetch_assoc($result_kandidat)) { 
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo $kandidat['foto']; ?>" class="card-img-top" alt="Foto Kandidat">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $kandidat['nama']; ?></h5>
                                    <p class="card-text"><strong>Prodi:</strong> <?php echo $kandidat['prodi']; ?></p>
                                    <p class="card-text"><strong>Visi:</strong> <?php echo $kandidat['visi']; ?></p>
                                    <p class="card-text"><strong>Misi:</strong> <?php echo $kandidat['misi']; ?></p>
                                    <p><strong>Total Voting: </strong>
                                        <?php echo isset($total_voting[$kandidat['id']]) ? $total_voting[$kandidat['id']] : 0; ?>
                                    </p>
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $kandidat['id']; ?>">Edit</button>
                                    <a href="?delete_id=<?php echo $kandidat['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kandidat ini?');">Hapus</a>
                                </div>
                            </div>

                            <!-- Modal Edit Kandidat -->
                            <div class="modal fade" id="editModal<?php echo $kandidat['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit Kandidat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="id" value="<?php echo $kandidat['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="nama" class="form-label">Nama Kandidat</label>
                                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $kandidat['nama']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="prodi" class="form-label">Prodi</label>
                                                    <select class="form-select" id="prodi" name="prodi" required>
                                                        <option value="Teknologi Bank Darah" <?php echo ($kandidat['prodi'] == 'Teknologi Bank Darah') ? 'selected' : ''; ?>>Teknologi Bank Darah</option>
                                                        <option value="Teknologi Laboratorium Medis" <?php echo ($kandidat['prodi'] == 'Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Teknologi Laboratorium Medis</option>
                                                        <option value="Sarjana Terapan Teknologi Laboratorium Medis" <?php echo ($kandidat['prodi'] == 'Sarjana Terapan Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Sarjana Terapan Teknologi Laboratorium Medis</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="foto" class="form-label">Foto Kandidat (Kosongkan jika tidak ingin mengganti)</label>
                                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="visi" class="form-label">Visi</label>
                                                    <textarea class="form-control" id="visi" name="visi" rows="2" required><?php echo $kandidat['visi']; ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="misi" class="form-label">Misi</label>
                                                    <textarea class="form-control" id="misi" name="misi" rows="2" required><?php echo $kandidat['misi']; ?></textarea>
                                                </div>
                                                <button type="submit" name="edit_kandidat" class="btn btn-primary">Perbarui Kandidat</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Menambah Kandidat -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCandidateModalLabel">Tambah Kandidat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Kandidat</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="prodi" class="form-label">Prodi</label>
                            <select class="form-select" id="prodi" name="prodi" required>
                                <option value="Teknologi Bank Darah">Teknologi Bank Darah</option>
                                <option value="Teknologi Laboratorium Medis">Teknologi Laboratorium Medis</option>
                                <option value="Sarjana Terapan Teknologi Laboratorium Medis">Sarjana Terapan Teknologi Laboratorium Medis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Kandidat</label>
                            <input type="file" class="form-control" id="foto" name="foto" required>
                        </div>
                        <div class="mb-3">
                            <label for="visi" class="form-label">Visi</label>
                            <textarea class="form-control" id="visi" name="visi" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="misi" class="form-label">Misi</label>
                            <textarea class="form-control" id="misi" name="misi" rows="2" required></textarea>
                        </div>
                        <button type="submit" name="add_kandidat" class="btn btn-primary">Tambah Kandidat</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Himpunan Mahasiswa Poltekes Kemenkes Semarang. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
