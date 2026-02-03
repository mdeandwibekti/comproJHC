<?php
require_once "../../config.php";

header('Content-Type: application/json');

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $category = trim($_POST["category"]);
    $message = trim($_POST["message"]);

    if (empty($name) || empty($email) || empty($message)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $sql = "INSERT INTO appointments (name, phone, email, category, message) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssss", $name, $phone, $email, $category, $message);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Thank you for your message. We will get back to you shortly.';
            } else {
                $response['success'] = false;
                $response['message'] = 'Something went wrong. Please try again later.';
            }
            $stmt->close();
        } else {
            $response['success'] = false;
            $response['message'] = 'Database error. Please try again later.';
        }
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>