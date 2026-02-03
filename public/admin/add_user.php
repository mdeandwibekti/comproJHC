<?php
require_once "../../config.php"; // Sesuaikan path config kamu

$username = "admin";
$password = "123456"; // Password yang kamu mau
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password) VALUES (?, ?)";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ss", $username, $hashed_password);
    if($stmt->execute()){
        echo "User berhasil dibuat. Silakan login dengan user: admin, pass: 123456";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$mysqli->close();
?>