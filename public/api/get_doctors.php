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
    
    // Query mengambil data dokter berdasarkan department_id yang diklik dari modal
    // Pastikan tabel 'doctors' memiliki kolom 'department_id' sebagai relasi ke tabel 'departments'
    $sql = "SELECT name, specialty, photo_path FROM doctors WHERE department_id = ? ORDER BY name ASC";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Fix path foto jika kosong agar tidak merusak tampilan modal
            if (empty($row['photo_path'])) {
                $row['photo_path'] = 'assets/img/gallery/jane.png'; // Foto default yang sudah Anda tentukan
            }
            $data[] = $row;
        }
        
        // Mengembalikan data dalam format JSON untuk diproses oleh JavaScript di modal
        echo json_encode($data);
    } else {
        // Mengembalikan array kosong jika query gagal disiapkan
        echo json_encode([]);
    }
} else {
    // Mengembalikan array kosong jika parameter dept_id tidak ditemukan
    echo json_encode([]);
}
?>