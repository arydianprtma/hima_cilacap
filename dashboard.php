<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

// Ambil kandidat sesuai prodi
$prodi = $_SESSION['prodi'];
$query_kandidat = "SELECT * FROM kandidat WHERE prodi = '$prodi'";
$result_kandidat = mysqli_query($conn, $query_kandidat);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Selamat Datang, <?php echo $_SESSION['nama']; ?></h2>
                <h4>Prodi: <?php echo $_SESSION['prodi']; ?></h4>
                <hr>
                <h3>Kandidat Gubernur HIMA</h3>
                <div class="row">
                    <?php while($kandidat = mysqli_fetch_assoc($result_kandidat)) { ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo $kandidat['foto']; ?>" class="card-img-top" alt="Foto Kandidat">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $kandidat['nama']; ?></h5>
                                    <p class="card-text"><?php echo $kandidat['visi_misi']; ?></p>
                                    <a href="voting.php?kandidat_id=<?php echo $kandidat['id']; ?>" class="btn btn-primary">Pilih</a>
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