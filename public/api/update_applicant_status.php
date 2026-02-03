<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../config.php';
require '../../vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : 0;
    $status = $_POST['status'] ?? '';

    if ($applicant_id > 0 && in_array($status, ['Pending', 'Diterima', 'Ditolak'])) {
        // Update status in database
        $stmt = $mysqli->prepare("UPDATE applicants SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $applicant_id);
        
        if ($stmt->execute()) {
            $message = "Status updated successfully.";


            // Send email if status is Diterima or Ditolak
            if ($status === 'Diterima' || $status === 'Ditolak') {
                $stmt_email = $mysqli->prepare("SELECT name, email, c.job_title FROM applicants a JOIN careers c ON a.job_id = c.id WHERE a.id = ?");
                $stmt_email->bind_param("i", $applicant_id);
                $stmt_email->execute();
                $result = $stmt_email->get_result();
                if ($result->num_rows > 0) {
                    $applicant = $result->fetch_assoc();
                    
                    $mail = new PHPMailer(true);

                    try {
                        //Server settings
                        $mail->SMTPDebug = 2; // Disable debug output
                        $mail->isSMTP();
                        $mail->Host       = 'mail.rsjantungtasikmalaya.my.id'; // Set the SMTP server to send through
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'info@rsjantungtasikmalaya.my.id'; // SMTP username
                        $mail->Password   = 'jhctasik2022'; // SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable SSL encryption
                        $mail->Port       = 465; // TCP port to connect to

                        //Recipients
                        $mail->setFrom('info@rsjantungtasikmalaya.my.id', 'RS JHC Tasik'); // Change this to your 'from' address and name
                        $mail->addAddress($applicant['email'], $applicant['name']);

                        // Content
                        $mail->isHTML(false);
                        $mail->Subject = "Update on Your Job Application for " . $applicant['job_title'];
                        
                        if ($status === 'Diterima') {
                            $mail->Body = "Dear " . $applicant['name'] . ",\n\nCongratulations! Your application for the position of " . $applicant['job_title'] . " has been accepted. We will contact you shortly with further details.\n\nBest regards,\nJHC";
                        } else { // Ditolak
                            $mail->Body = "Dear " . $applicant['name'] . ",\n\nThank you for your interest in the " . $applicant['job_title'] . " position. After careful consideration, we have decided to move forward with other candidates.\n\nWe wish you the best in your job search.\n\nBest regards,\nJHC";
                        }

                        $mail->send();
                        $message .= ' Email notification sent to applicant.';
                    } catch (Exception $e) {
                        $message .= " However, failed to send email notification. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
                $stmt_email->close();
            }
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$mysqli->close();
?>
