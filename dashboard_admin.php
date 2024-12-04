<?php
session_start();
require_once 'config/database.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Inisialisasi variabel untuk pesan
$success_message = '';
$error_message = '';

// Proses penambahan kandidat
if (isset($_POST['add_kandidat'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $visi = mysqli_real_escape_string($conn, $_POST['visi']);
    $misi = mysqli_real_escape_string($conn, $_POST['misi']);
    
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
    if ($_FILES['foto']['size'] > 500000) { // 500KB
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
            $query = "INSERT INTO kandidat (nama, prodi, foto, visi, misi) VALUES ('$nama', '$prodi', '$target_file', '$visi', '$misi')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['success_message'] = "Kandidat berhasil ditambahkan.";
                // Kosongkan inputan setelah berhasil menambah kandidat
                $_POST['nama'] = '';
                $_POST['prodi'] = '';
                $_POST['visi'] = '';
                $_POST['misi'] = '';
            } else {
                $error_message = "Terjadi kesalahan saat menambahkan kandidat: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Maaf, terjadi kesalahan saat mengupload file. Pastikan folder 'uploads/' ada dan dapat ditulis.";
        }
    }
}

// Proses penghapusan kandidat
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $query = "DELETE FROM kandidat WHERE id = $delete_id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Kandidat berhasil dihapus.";
    } else {
        $error_message = "Terjadi kesalahan saat menghapus kandidat: " . mysqli_error($conn);
    }
}

// Proses pengeditan kandidat
if (isset($_POST['edit_kandidat'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $visi = mysqli_real_escape_string($conn, $_POST['visi']);
    $misi = mysqli_real_escape_string($conn, $_POST['misi']);
    
    // Cek apakah foto baru diupload
    if ($_FILES['foto']['name']) {
        $foto = $_FILES['foto']['name'];
        $target_file = $target_dir . basename($foto);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $query = "UPDATE kandidat SET nama='$nama', prodi='$prodi', foto='$target_file', visi='$visi', misi='$misi' WHERE id=$id";
        } else {
            $error_message = "Terjadi kesalahan saat mengupload foto.";
        }
    } else {
        // Jika tidak ada foto baru, update tanpa mengubah foto
        $query = "UPDATE kandidat SET nama='$nama', prodi='$prodi', visi='$visi', misi='$misi' WHERE id=$id";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Kandidat berhasil diperbarui.";
        $edit_success = true; // Set status edit berhasil
    } else {
        $error_message = "Terjadi kesalahan saat memperbarui kandidat: " . mysqli_error($conn);
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
                <?php if (isset($_SESSION['success_message'])) { ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php } ?>
                <?php if ($error_message) { ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php } ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kandidat</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prodi" class="form-label">Prodi</label>
                        <select class="form-control" id="prodi" name="prodi" required>
                            <option value="Teknologi Bank Darah" <?php echo (isset($_POST['prodi']) && $_POST['prodi'] == 'Teknologi Bank Darah') ? 'selected' : ''; ?>>Teknologi Bank Darah</option>
                            <option value="Teknologi Laboratorium Medis" <?php echo (isset($_POST['prodi']) && $_POST['prodi'] == 'Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Teknologi Laboratorium Medis</option>
                            <option value="Sarjana Terapan Teknologi Laboratorium Medis" <?php echo (isset($_POST['prodi']) && $_POST['prodi'] == 'Sarjana Terapan Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Sarjana Terapan Teknologi Laboratorium Medis</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto Kandidat</label>
                        <input type="file" class="form-control" id="foto" name="foto" required>
                    </div>
                    <div class="mb-3">
                        <label for="visi" class="form-label">Visi</label>
                        <textarea class="form-control" id="visi" name="visi" rows="2" required><?php echo isset($_POST['visi']) ? $_POST['visi'] : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="misi" class="form-label">Misi</label>
                        <textarea class="form-control" id="misi" name="misi" rows="2" required><?php echo isset($_POST['misi']) ? $_POST['misi'] : ''; ?></textarea>
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
                                    <p class="card-text"><strong>Visi:</strong> <?php echo isset($kandidat['visi']) ? $kandidat['visi'] : 'Tidak ada visi'; ?></p>
                                    <p class="card-text"><strong>Misi:</strong> <?php echo isset($kandidat['misi']) ? $kandidat['misi'] : 'Tidak ada misi'; ?></p>
                                    <p><strong>Total Voting: </strong>
                                        <?php echo isset($total_voting[$kandidat['id']]) ? $total_voting[$kandidat['id']] : 0; ?>
                                    </p>
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $kandidat['id']; ?>">Edit</button>
                                    <a href="dashboard_admin.php?delete_id=<?php echo $kandidat['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kandidat ini?');">Hapus</a>
                                </div>
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
                                                <select class="form-control" id="prodi" name="prodi" required>
                                                    <option value="Teknologi Bank Darah" <?php echo ($kandidat['prodi'] == 'Teknologi Bank Darah') ? 'selected' : ''; ?>>Teknologi Bank Darah</option>
                                                    <option value="Teknologi Laboratorium Medis" <?php echo ($kandidat['prodi'] == 'Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Teknologi Laboratorium Medis</option>
                                                    <option value="Sarjana Terapan Teknologi Laboratorium Medis" <?php echo ($kandidat['prodi'] == 'Sarjana Terapan Teknologi Laboratorium Medis') ? 'selected' : ''; ?>>Sarjana Terapan Teknologi Laboratorium Medis</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="foto" class="form-label">Foto Kandidat (Kosongkan jika tidak ingin mengubah)</label>
                                                <input type="file" class="form-control" id="foto" name="foto">
                                            </div>
                                            <div class="mb-3">
                                                <label for="visi" class="form-label">Visi</label>
                                                <textarea class="form-control" id="visi" name="visi" rows="2" required><?php echo isset($kandidat['visi']) ? $kandidat['visi'] : ''; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="misi" class="form-label">Misi</label>
                                                <textarea class="form-control" id="misi" name="misi" rows="2" required><?php echo isset($kandidat['misi']) ? $kandidat['misi'] : ''; ?></textarea>
                                            </div>
                                            <button type="submit" name="edit_kandidat" class="btn btn-primary">Perbarui Kandidat</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
