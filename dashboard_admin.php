<?php
session_start();
require_once 'config/database.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Proses penambahan kandidat
if (isset($_POST['add_kandidat'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $visi_misi = mysqli_real_escape_string($conn, $_POST['visi_misi']);
    
    // Proses upload foto
    $foto = $_FILES['foto']['name'];
    $target_dir = "uploads/"; // Pastikan folder ini ada dan dapat ditulis
    $target_file = $target_dir . basename($foto);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah file gambar adalah gambar sebenarnya
    $check = getimagesize($_FILES['foto']['tmp_name']);
    if ($check === false) {
        $error_message = "File yang diupload bukan gambar.";
        $uploadOk = 0;
    }

    // Cek ukuran file
    if ($_FILES['foto']['size'] > 50000000) { // 500KB
        $error_message = "Maaf, ukuran file terlalu besar.";
        $uploadOk = 0;
    }

    // Izinkan format file tertentu
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $error_message = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        $uploadOk = 0;
    }

    // Cek apakah $uploadOk diatur ke 0 oleh kesalahan
    if ($uploadOk == 0) {
        // Tidak ada yang dilakukan, kesalahan sudah ditangani
    } else {
        // Jika semuanya baik-baik saja, coba untuk mengupload file
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Simpan data kandidat ke database
            $query = "INSERT INTO kandidat (nama, prodi, foto, visi_misi) VALUES ('$nama', '$prodi', '$target_file', '$visi_misi')";
            if (mysqli_query($conn, $query)) {
                $success_message = "Kandidat berhasil ditambahkan.";
            } else {
                $error_message = "Terjadi kesalahan saat menambahkan kandidat: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Maaf, terjadi kesalahan saat mengupload file.";
        }
    }
}

// Ambil data kandidat dan total voting
$query_kandidat = "SELECT * FROM kandidat";
$result_kandidat = mysqli_query($conn, $query_kandidat);

// Ambil total voting untuk setiap kandidat
$query_voting = "SELECT kandidat_id, COUNT(*) as total_voting FROM voting GROUP BY kandidat_id";
$result_voting = mysqli_query($conn, $query_voting);

// Menyimpan total voting dalam array
$total_voting = [];
while ($voting = mysqli_fetch_assoc($result_voting)) {
    $total_voting[$voting['kandidat_id']] = $voting['total_voting'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Selamat Datang, Admin</h2>
                <hr>

                <!-- Menambahkan Kandidat -->
                <h3>Tambah Kandidat</h3>
                <?php if (isset($success_message)) { ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php } ?>
                <?php if (isset($error_message)) { ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php } ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kandidat</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="prodi" class="form-label">Prodi</label>
                        <select class="form-control" id="prodi" name="prodi" required>
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
                        <label for="visi_misi" class="form-label">Visi dan Misi</label>
                        <textarea class="form-control" id="visi_misi" name="visi_misi" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="add_kandidat" class="btn btn-primary">Tambah Kandidat</button>
                </form>

                <hr>

                <!-- Melihat Kandidat dan Total Voting -->
                <h3>Kandidat dan Total Voting</h3>
                <div class="row">
                    <?php while($kandidat = mysqli_fetch_assoc($result_kandidat)) { ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo $kandidat['foto']; ?>" class="card-img-top" alt="Foto Kandidat">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $kandidat['nama']; ?></h5>
                                    <p class="card-text"><?php echo $kandidat['visi_misi']; ?></p>
                                    <p><strong>Total Voting: </strong>
                                        <?php echo isset($total_voting[$kandidat['id']]) ? $total_voting[$kandidat['id']] : 0; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
