<?php
require_once 'config.php';

if (isset($_POST['submit_application'])) {
    $job_id    = $_POST['job_id'];
    $name      = $_POST['name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $education = $_POST['education'];
    $address   = $_POST['address'];

    $folder = "public/uploads/cv/";
    
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    $file_extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
    $clean_name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    $filename = "CV_" . time() . "_" . $clean_name . "." . $file_extension;
    $target_file = $folder . $filename;

    if ($file_extension !== 'pdf') {
        echo "<script>alert('Gagal! Hanya file PDF yang diperbolehkan.'); window.history.back();</script>";
        exit;
    }

    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
        $cv_path_db = "uploads/cv/" . $filename;

        $query = "INSERT INTO applicants (job_id, name, email, phone, education, address, cv_path, status, applied_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("issssss", $job_id, $name, $email, $phone, $education, $address, $cv_path_db);

        if ($stmt->execute()) {
            echo "<script>alert('Sukses! Lamaran Anda telah diterima.'); window.location.href='career.php';</script>";
        } else {
            unlink($target_file);
            echo "Database Error: " . $mysqli->error;
        }
        $stmt->close();

    } else {
        echo "Gagal mengunggah file ke folder tujuan. Pastikan izin folder (permission) benar.";
    }
}
?>