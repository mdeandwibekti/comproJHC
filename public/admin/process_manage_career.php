<?php
require_once '../../config.php';

if (isset($_POST['save_career'])) {
    $title = $_POST['job_title'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $stmt = $mysqli->prepare("INSERT INTO careers (job_title, description, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $desc, $status);

    if ($stmt->execute()) {
        header("Location: manage_careers.php?status=success");
    } else {
        echo "Error: " . $mysqli->error;
    }
}