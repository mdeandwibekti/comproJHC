<?php
// Hubungkan ke database (Sesuaikan path config jika perlu)
// Jika config.php ada di folder root utama (sejajar dengan index.php), maka naik 2 level
if (file_exists("../../config.php")) {
    require_once "../../config.php";
} elseif (file_exists("../../../config.php")) {
    require_once "../../../config.php";
}

header('Content-Type: application/json');

// Pastikan ada parameter dept_id
if (isset($_GET['dept_id'])) {
    $dept_id = intval($_GET['dept_id']);
    
    // Pastikan tabel 'doctors' memiliki kolom 'department_id'
    $sql = "SELECT name, specialty, photo_path FROM doctors WHERE department_id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Fix path foto jika kosong
            if (empty($row['photo_path'])) {
                $row['photo_path'] = 'assets/img/gallery/jane.png'; // Foto default
            }
            $data[] = $row;
        }
        
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>