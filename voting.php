<?php
session_start();
require_once 'config/database.php';


if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

// Proses voting
if (isset($_GET['kandidat_id'])) {
    $kandidat_id = intval($_GET['kandidat_id']);
    $nim = $_SESSION['nim'];

    // Cek apakah sudah voting
    $cek_voting = "SELECT * FROM voting WHERE nim = '$nim'";
    $result_cek = mysqli_query($conn, $cek_voting);

    if (mysqli_num_rows($result_cek) == 0) {
        // Tambah suara
        $query_vote = "INSERT INTO voting (nim, kandidat_id) VALUES ('$nim', $kandidat_id)";
        if (mysqli_query($conn, $query_vote)) {
            $success = "Terima kasih, suara Anda telah tercatat!";
        } else {
            $error = "Gagal melakukan voting.";
        }
    } else {
        $error = "Anda sudah melakukan voting sebelumnya.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="src/logo-poltekkes-Photoroom.png">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Konfirmasi Voting</h3>
                    </div>
                    <div class="card-body text-center">
                        <?php if(isset($success)) { ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <a href="logout.php" class="btn btn-primary">Logout</a>
                        <?php } elseif(isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>