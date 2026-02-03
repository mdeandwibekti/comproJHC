<?php
require_once "../../config.php";

header('Content-Type: application/json');

$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

if ($department_id > 0) {
    $sql = "SELECT name, specialty, photo_path FROM doctors WHERE department_id = ? ORDER BY name ASC";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctors = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($doctors);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Query failed.']);
    }
} else {
    echo json_encode([]);
}

$mysqli->close();
?>