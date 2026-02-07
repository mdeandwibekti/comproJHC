<?php
require_once '../../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applicant_id'])) {
    $id = (int)$_POST['applicant_id'];

    // 1. Ambil path file CV dulu sebelum datanya dihapus
    $stmt = $mysqli->prepare("SELECT cv_path FROM applicants WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $full_path = "../../" . $data['cv_path'];

        // 2. Hapus data dari database
        $del_stmt = $mysqli->prepare("DELETE FROM applicants WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // 3. Hapus file fisik jika ada
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            echo json_encode(['success' => true, 'message' => 'Data pelamar dan berkas berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data dari database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Request tidak valid.']);
}