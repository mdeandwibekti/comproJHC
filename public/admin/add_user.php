<?php
// Pastikan path config sesuai dengan struktur folder Anda
require_once "../../config.php"; 

$username = "admin";
$password = "123456"; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$message = "";
$status = "";

// 1. Cek apakah username sudah ada untuk menghindari error duplikasi
$check_user = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
$check_user->bind_param("s", $username);
$check_user->execute();
$check_user->store_result();

if($check_user->num_rows > 0) {
    $status = "warning";
    $message = "User <b>'$username'</b> sudah ada di database.";
} else {
    // 2. Jika belum ada, lakukan INSERT
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";

    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("ss", $username, $hashed_password);
        if($stmt->execute()){
            $status = "success";
            $message = "User berhasil dibuat!<br>User: <b>$username</b><br>Pass: <b>$password</b>";
        } else {
            $status = "danger";
            $message = "Terjadi kesalahan: " . $stmt->error;
        }
        $stmt->close();
    }
}
$check_user->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup User - Admin JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-setup {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        .card-header-jhc {
            background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border: none;
        }
        .btn-jhc {
            background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
            color: white !important;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-jhc:hover { transform: translateY(-2px); opacity: 0.9; }
    </style>
</head>
<body>

<div class="card card-setup">
    <div class="card-header-jhc">
        <i class="fas fa-user-shield fa-3x mb-3"></i>
        <h4 class="mb-0">Setup Administrator</h4>
    </div>
    <div class="card-body p-4 text-center">
        <div class="alert alert-<?php echo $status; ?> border-0 shadow-sm mb-4">
            <i class="fas <?php echo ($status == 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
        </div>
        
        <?php if($status == "success"): ?>
            <p class="text-muted small mb-4">Silakan hapus file <code>add_user.php</code> setelah berhasil untuk menjaga keamanan server Anda.</p>
            <a href="index.php" class="btn btn-jhc">Ke Halaman Login</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-secondary rounded-pill px-4">Kembali</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>