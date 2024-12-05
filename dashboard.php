<?php
session_start();
require_once 'config/database.php';


if (!isset($_SESSION['nama']) || !isset($_SESSION['prodi'])) {
    header("Location: login.php");
    exit();
}


$prodi = mysqli_real_escape_string($conn, $_SESSION['prodi']);


$query_kandidat = "SELECT * FROM kandidat WHERE prodi = '$prodi'";
$result_kandidat = mysqli_query($conn, $query_kandidat);


if (!$result_kandidat) {
    $error_message = "Database query failed: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HIMA | Poltekkes Kemenkes Semarang</title>
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

        html, body {
            height: 100%; 
            margin: 0; 
        }

        body {
            display: flex;
            flex-direction: column; 
            background-color: var(--bg-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--text-dark);
        }

        body {
        background-image: url('src/kampus.jpg'); /* Ganti dengan path gambar kamu */
        background-size: cover; /* Supaya gambar memenuhi seluruh layar */
        background-position: center; /* Posisikan di tengah */
        background-repeat: no-repeat; /* Jangan ulang gambar */
        background-attachment: fixed; /* Biar gambar tetap diam saat di-scroll */
        font-family: 'Inter', sans-serif;
        color: var(--text-dark);
        }

        .voting-header {
            background: linear-gradient(135deg, var(--accent-color), #4338ca);
            color: white;
            padding: 2rem 0;
            margin-bottom: 1rem
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); 
        }

        .display-5 {
            font-size: 2rem; 
        }

        .display-6 {
            font-size: 1.5rem; 
        }

        .display-7 {
            font-size: 1.2rem; 
        }

        .lead {
            font-size: 1rem; 
            margin-top: 0.5rem; 
        }

        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .candidate-card {
            background: white;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        }

        .candidate-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        .candidate-card-image {
            height: 300px; 
            width: 100%;
            object-fit: cover; 
            transition: all 0.3s ease;
        }

        .candidate-card:hover .candidate-card-image {
            filter: grayscale(0%) contrast(110%);
        }

        .candidate-info {
            padding: 1rem;
        }

        .btn-vote {
            background-color: var(--accent-color);
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-vote:hover {
            background-color: var(--accent-hover);
            transform: scale(1.05);
        }

        .vision-mission {
            padding: 1rem;
            text-align: justify; /* Rata kiri */
        }

        .vision-mission h6 {
            font-weight: bold;
            margin-bottom: 1rem;
        }

        hr {
            border-top: 3px solid var(--text-light);
            opacity: 1;
            margin: 0;
        }

        footer {
            background: linear-gradient(135deg, var(--accent-color), #4338ca); 
            color: white; 
            position: relative;
            bottom: 0; 
            width: 100%;
        }

        footer p {
            margin: 0;
            padding: 10px 0;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .candidate-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="voting-header text-center">
        <div class="container">
            <h1 class="display-5 fw-bold">Pemilihan Gubernur Himpunan Mahasiswa</h1>
            <h4 class="display-6 fw-bold">Jurusan Analis Kesehatan</h4>
            <h5 class="display-7 fw-bold">Poltekkes Kemenkes Semarang</h5>
            <p class="lead">Welcome, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?> | <?php echo htmlspecialchars($_SESSION['prodi']); ?></strong></p>
        </div>
    </header>

    <div class="container flex-grow-1">
        <div class="row">
            <?php if ($result_kandidat && mysqli_num_rows($result_kandidat) > 0): ?>
                <?php while($kandidat = mysqli_fetch_assoc($result_kandidat)) { ?>
                    <div class="col-md-4">
                        <div class="candidate-card mb-4">
                            <img src="<?php echo htmlspecialchars($kandidat['foto']); ?>" 
                                 class="card-img-top candidate-card-image" 
                                 alt="Kandidat <?php echo htmlspecialchars($kandidat['nama']); ?>">
                            <hr>
                            <div class="candidate-info">
                                <h5 class="card-title text-center mb-3"><?php echo htmlspecialchars($kandidat['nama']); ?></h5>
                                
                                <div class="vision-mission mb-3">
                                    <h6 class="text-muted">Vision</h6>
                                    <?php 
                                        $visi_points = explode(".", $kandidat['visi'] ?? 'No vision stated');
                                    ?>
                                    <ul>
                                        <?php foreach ($visi_points as $point): ?>
                                            <?php if (!empty(trim($point))): ?>
                                                <li><?php echo htmlspecialchars(trim($point)) . '.'; ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>

                                    <h6 class="text-muted mt-2">Mission</h6>
                                    <?php 
                                        $misi_points = explode(".", $kandidat['misi'] ?? 'No mission stated');
                                    ?>
                                    <ul>
                                        <?php foreach ($misi_points as $point): ?>
                                            <?php if (!empty(trim($point))): ?>
                                                <li><?php echo htmlspecialchars(trim($point)) . '.'; ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="text-center">
                                    <a href="voting.php?kandidat_id=<?php echo intval($kandidat['id']); ?>" 
                                       class="btn btn-vote btn-primary px-4">
                                        Vote Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No candidates available for your study program.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center py-3" style="position: fixed; bottom: 0; width: 100%;">
        <p>&copy; 2024 Himpunan Mahasiswa Poltekes Kemenkes Semarang. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
