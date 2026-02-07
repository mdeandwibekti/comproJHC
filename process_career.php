<?php
require_once 'config.php';

if (isset($_POST['submit_application'])) {
    $job_id    = $_POST['job_id']; // ID dari dropdown atau hidden input
    $name      = $mysqli->real_escape_string($_POST['name']);
    $email     = $mysqli->real_escape_string($_POST['email']);
    $phone     = $mysqli->real_escape_string($_POST['phone']);
    $education = $mysqli->real_escape_string($_POST['education']);
    $address   = $mysqli->real_escape_string($_POST['address']);

    // Proses File CV
    $folder = "uploads/cv/";
    if (!file_exists($folder)) mkdir($folder, 0777, true);

    $file_extension = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION);
    $filename = "CV_" . time() . "_" . str_replace(' ', '_', $name) . "." . $file_extension;
    $target_file = $folder . $filename;

    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
        // Simpan Path relatif agar admin bisa membaca file
        $cv_path = "uploads/cv/" . $filename;

        $query = "INSERT INTO applicants (job_id, name, email, phone, education, address, cv_path, status) 
                  VALUES ('$job_id', '$name', '$email', '$phone', '$education', '$address', '$cv_path', 'Pending')";

        if ($mysqli->query($query)) {
            echo "<script>alert('Sukses! Lamaran Anda telah diterima.'); window.location.href='career.php';</script>";
        } else {
            echo "Database Error: " . $mysqli->error;
        }
    } else {
        echo "Gagal mengunggah file.";
    }
}
?>