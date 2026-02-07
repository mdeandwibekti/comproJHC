<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mulai Output Buffering untuk mencegah karakter liar merusak JSON
ob_start();

require_once '../../config.php';
require '../../vendor/autoload.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : 0;
    $status = $_POST['status'] ?? '';

    // Validasi Input
    if ($applicant_id > 0 && in_array($status, ['Diterima', 'Ditolak'])) {
        
        // 1. Update status di database
        $stmt = $mysqli->prepare("UPDATE applicants SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $applicant_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Status berhasil diperbarui menjadi $status.";

            // 2. Ambil data pelamar untuk kirim email
            $stmt_email = $mysqli->prepare("
                SELECT a.name, a.email, c.job_title 
                FROM applicants a 
                JOIN careers c ON a.job_id = c.id 
                WHERE a.id = ?
            ");
            $stmt_email->bind_param("i", $applicant_id);
            $stmt_email->execute();
            $result = $stmt_email->get_result();

            if ($result->num_rows > 0) {
                $applicant = $result->fetch_assoc();
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'mail.rsjantungtasikmalaya.my.id';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'info@rsjantungtasikmalaya.my.id';
                    $mail->Password   = 'jhctasik2022';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                    $mail->Port       = 465;
                    $mail->CharSet    = 'UTF-8';

                    // Recipients
                    $mail->setFrom('info@rsjantungtasikmalaya.my.id', 'RS JHC Tasikmalaya');
                    $mail->addAddress($applicant['email'], $applicant['name']);

                    // Content - Menggunakan format HTML agar lebih profesional
                    $mail->isHTML(true);
                    $mail->Subject = "Update Lamaran Pekerjaan: " . $applicant['job_title'];
                    
                    if ($status === 'Diterima') {
                        $mail->Body = "
                            <h3>Halo, {$applicant['name']}!</h3>
                            <p>Selamat! Lamaran Anda untuk posisi <b>{$applicant['job_title']}</b> telah <b>Diterima</b>.</p>
                            <p>Tim HRD kami akan menghubungi Anda segera untuk proses selanjutnya.</p>
                            <br>
                            <p>Salam hangat,<br><b>RS JHC Tasikmalaya</b></p>";
                    } else {
                        $mail->Body = "
                            <h3>Halo, {$applicant['name']}.</h3>
                            <p>Terima kasih atas minat Anda untuk posisi <b>{$applicant['job_title']}</b>.</p>
                            <p>Setelah meninjau berkas Anda, saat ini kami belum dapat melanjutkan lamaran Anda ke tahap berikutnya.</p>
                            <p>Tetap semangat dan sukses untuk karir Anda ke depannya.</p>
                            <br>
                            <p>Salam,<br><b>RS JHC Tasikmalaya</b></p>";
                    }

                    $mail->send();
                    $response['message'] .= ' Notifikasi email berhasil dikirim.';
                } catch (Exception $e) {
                    $response['message'] .= ' Namun, email gagal dikirim. Error: ' . $mail->ErrorInfo;
                }
            }
            $stmt_email->close();
        } else {
            $response['message'] = 'Gagal memperbarui database: ' . $mysqli->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Data tidak valid atau status tidak diizinkan.';
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

// Bersihkan output buffer dan kirim JSON
ob_end_clean();
echo json_encode($response);
$mysqli->close();