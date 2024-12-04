<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    $errors = [];

    // Validasi panjang password
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    // Validasi konfirmasi password
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai.";
    }

    // Cek apakah username sudah ada
    $check_username = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $check_username);

    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Username sudah terdaftar.";
    }

    // Jika tidak ada error, simpan data admin baru
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Query untuk menyimpan data admin
        $query = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header("Location: login_admin.php");
            exit();
        } else {
            $errors[] = "Gagal melakukan registrasi. Coba lagi.";
        }
    }
}
?>

<!-- Form Register Admin -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="src/logo-poltekkes.jpg">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Register Admin</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($errors) && !empty($errors)) { ?>
                            <div class="alert alert-danger">
                                <?php foreach($errors as $error) { echo "<p>$error</p>"; } ?>
                            </div>
                        <?php } ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <p>Belum punya akun? <a href="login_admin.php">Daftar disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
