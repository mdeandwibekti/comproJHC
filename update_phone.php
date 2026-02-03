<?php
require_once 'config.php';

$new_phone_number = '+62 851-7500-0374';
$key = 'contact_phone';

$sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ss", $key, $new_phone_number);
    if ($stmt->execute()) {
        echo "Phone number updated successfully.";
    } else {
        echo "Error updating phone number: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $mysqli->error;
}

$mysqli->close();
?>