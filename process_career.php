<?php
require_once 'config.php';

if (isset($_POST['submit_application'])) {
    // 1. Ambil Data Form
    $job_id    = $_POST['job_id'];
    $name      = $_POST['name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $education = $_POST['education'];
    $address   = $_POST['address'];

    // 2. Konfigurasi Folder Penyimpanan
    // Kita gunakan path relatif 'public/uploads/cv/' agar folder tidak terduplikasi
    $folder = "public/uploads/cv/";
    
    // Pastikan folder ada, jika tidak ada baru dibuat
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // 3. Proses Penamaan File
    $file_extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
    // Membersihkan nama file dari karakter aneh agar aman di server
    $clean_name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    $filename = "CV_" . time() . "_" . $clean_name . "." . $file_extension;
    $target_file = $folder . $filename;

    // 4. Validasi Ekstensi (Hanya PDF yang disarankan)
    if ($file_extension !== 'pdf') {
        echo "<script>alert('Gagal! Hanya file PDF yang diperbolehkan.'); window.history.back();</script>";
        exit;
    }

    // 5. Eksekusi Upload
    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
        
        // Simpan path ke database TANPA awalan 'public/' jika admin berada di dalam folder public.
        // Namun, jika Anda mengikuti kode admin sebelumnya, kita simpan: "uploads/cv/namafile.pdf"
        $cv_path_db = "uploads/cv/" . $filename;

        // 6. Simpan ke Database menggunakan Prepared Statement (Lebih Aman)
        $query = "INSERT INTO applicants (job_id, name, email, phone, education, address, cv_path, status, applied_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("issssss", $job_id, $name, $email, $phone, $education, $address, $cv_path_db);

        if ($stmt->execute()) {
            echo "<script>alert('Sukses! Lamaran Anda telah diterima.'); window.location.href='career.php';</script>";
        } else {
            // Jika database gagal, hapus file yang sudah terlanjur terupload agar tidak sampah
            unlink($target_file);
            echo "Database Error: " . $mysqli->error;
        }
        $stmt->close();

    } else {
        echo "Gagal mengunggah file ke folder tujuan. Pastikan izin folder (permission) benar.";
    }
}
?>