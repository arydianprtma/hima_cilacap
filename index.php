<?php
require_once 'config/database.php';

// Definisi prodi sesuai permintaan
$daftar_prodi = [
    'Teknologi Bank Darah',
    'Teknologi Laboratorium Medis', 
    'Sarjana Terapan Teknologi Laboratorium Medis'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);

    // Validasi input
    $errors = [];

    // Cek NIM sudah terdaftar
    $cek_nim = "SELECT * FROM mahasiswa WHERE nim = '$nim'";
    $result_nim = mysqli_query($conn, $cek_nim);

    if (mysqli_num_rows($result_nim) > 0) {
        $errors[] = "NIM sudah terdaftar";
    }

    // Jika tidak ada error, lakukan registrasi
    if (empty($errors)) {
        $query = "INSERT INTO mahasiswa (nama, nim, prodi) 
                  VALUES ('$nama', '$nim', '$prodi')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $errors[] = "Gagal melakukan registrasi: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Registrasi Mahasiswa</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        // Tampilkan pesan sukses
                        if(isset($success)) {
                            echo "<div class='alert alert-success'>$success</div>";
                        }
                        
                        // Tampilkan error
                        if(!empty($errors)) {
                            echo "<div class='alert alert-danger'>";
                            foreach($errors as $error) {
                                echo "<p class='mb-0'>$error</p>";
                            }
                            echo "</div>";
                        }
                        ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIM</label>
                                <input type="text" name="nim" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prodi</label>
                                <select name="prodi" class="form-control" required>
                                    <option value="">Pilih Prodi</option>
                                    <?php foreach($daftar_prodi as $prodi): ?>
                                        <option value="<?php echo $prodi; ?>"><?php echo $prodi; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
