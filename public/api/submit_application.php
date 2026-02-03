
<?php
require_once '../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $education = $_POST['education'] ?? '';

    if ($job_id <= 0 || empty($name) || empty($address) || empty($phone) || empty($email) || empty($education)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }

    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/cv/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx'];

        if (in_array($file_ext, $allowed_ext)) {
            $filename = uniqid('cv_', true) . '.' . $file_ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['cv']['tmp_name'], $filepath)) {
                $cv_path_for_db = 'uploads/cv/' . $filename;

                $stmt = $mysqli->prepare("INSERT INTO applicants (job_id, name, address, phone, email, education, cv_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $job_id, $name, $address, $phone, $email, $education, $cv_path_for_db);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, and DOCX are allowed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'CV upload failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$mysqli->close();
?>
