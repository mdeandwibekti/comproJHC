<?php
require_once '../config.php';

if (isset($_GET['id'])) {
    $doctor_id = $_GET['id'];

    $stmt = $mysqli->prepare("SELECT * FROM doctors WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
        $stmt->close();
    } else {
        // Handle error if prepare fails
        error_log("MySQLi Prepare Error: " . $mysqli->error);
        $doctor = false;
    }

    if (!$doctor) {
        header('Location: ../index.php#our-doctors');
        exit;
    }
} else {
    header('Location: ../index.php#our-doctors');
    exit;
}

$pageTitle = $doctor['name'] . " - Doctor Profile";
include 'layout/public_header.php';
?>

<section class="py-8">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($doctor['photo_path'] ? '../public/' . $doctor['photo_path'] : '../public/assets/img/gallery/jane.png'); ?>" class="rounded-circle" width="150" height="150" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                            <h2 class="mt-3 mb-1"><?php echo htmlspecialchars($doctor['name']); ?></h2>
                            <p class="text-muted"><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                        </div>
                        <hr>
                        <h4 class="mb-3">Schedule</h4>
                        <p><?php echo nl2br(htmlspecialchars($doctor['schedule'] ?? 'No schedule available.')); ?></p>
                        <hr>
                        <div class="text-center mt-4">
                            <a href="<?php echo BASE_URL; ?>#our-doctors" class="btn btn-primary">Back to Doctors</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'layout/public_footer.php'; ?>