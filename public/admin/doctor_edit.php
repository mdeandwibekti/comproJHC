<?php
require_once "../../config.php";
require_once 'layout/header.php';

$name = $specialty = $schedule = $photo_path = "";
$is_featured = 0;
$department_id = 0;
$page_title = "Add New Doctor";
$icon_header = "fa-plus";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Doctor";
    $icon_header = "fa-user-edit";
    // Ambil data
    $sql = "SELECT name, specialty, schedule, photo_path, is_featured, department_id FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $specialty, $schedule, $photo_path, $is_featured, $department_id);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

// Fetch departments
$departments = [];
$dept_res = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
if($dept_res) { while($row = $dept_res->fetch_assoc()) { $departments[] = $row; } }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    $specialty = trim($_POST["specialty"]);
    $schedule = trim($_POST["schedule"]);
    $department_id = $_POST["department_id"];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $photo_path = $_POST['current_photo'];

    // Handle File Upload
    $upload_dir = "../assets/img/gallery/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $new_filename = uniqid() . '-doc-' . basename($_FILES["photo"]["name"]);
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_dir . $new_filename)) {
            $photo_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO doctors (name, specialty, schedule, department_id, is_featured, photo_path) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE doctors SET name = ?, specialty = ?, schedule = ?, department_id = ?, is_featured = ?, photo_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("sssiis", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path);
        } else {
            $stmt->bind_param("sssiisi", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path, $id);
        }
        
        if ($stmt->execute()) {
            echo "<script>window.location.href='doctors.php?saved=true';</script>";
            exit();
        } else {
            $db_err = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<style>
    :root { --primary-red: #D32F2F; }
    
    .page-header {
        background: white; padding: 1.5rem; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }
    
    .main-card {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-red);
        box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.15);
    }
    
    .form-label { font-weight: 600; color: #444; margin-bottom: 0.5rem; }
    
    .img-preview-box {
        background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;
        padding: 15px; text-align: center; margin-bottom: 10px;
        transition: all 0.3s;
    }
    .img-preview-box:hover { border-color: var(--primary-red); background: #fff5f5; }
    .img-preview-box img { max-height: 150px; object-fit: contain; border-radius: 8px; }

    .btn-save {
        background-color: var(--primary-red); border: none; color: white;
        padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; transition: 0.3s;
    }
    .btn-save:hover { background-color: #b71c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3); }
    
    .btn-cancel {
        background-color: #eff2f5; color: #5e6278; border: none;
        padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600;
    }
    .btn-cancel:hover { background-color: #e9ecef; color: #333; }
</style>

<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="mb-1 text-dark fw-bold"><i class="fas <?php echo $icon_header; ?> me-2 text-danger"></i> <?php echo $page_title; ?></h3>
        <p class="text-muted mb-0 small">Please fill in the form below to add or update doctor information.</p>
    </div>

    <div class="card main-card">
        <div class="card-body p-4">
            
            <?php if(isset($db_err)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $db_err; ?></div>
            <?php endif; ?>

            <form action="doctor_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Doctor Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. Dr. Jane Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Specialty (Spesialis)</label>
                                <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($specialty); ?>" placeholder="e.g. Spesialis Jantung" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="0">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Practice Schedule</label>
                            <textarea name="schedule" class="form-control" rows="3" placeholder="Contoh: Senin - Rabu: 08.00 - 12.00"><?php echo htmlspecialchars($schedule); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch p-0 d-flex align-items-center">
                                <label class="form-check-label me-5 fw-bold text-dark" for="featuredCheck">Show on Homepage?</label>
                                <input class="form-check-input ms-0" type="checkbox" role="switch" name="is_featured" value="1" id="featuredCheck" <?php echo ($is_featured == 1) ? 'checked' : ''; ?> style="width: 3em; height: 1.5em;">
                            </div>
                            <div class="form-text small mt-1">If enabled, this doctor will appear in the "Our Doctors" section on the front page.</div>
                        </div>
                    </div>

                    <div class="col-md-4 border-start">
                        <h6 class="text-muted fw-bold mb-3">Doctor's Photo</h6>
                        
                        <div class="mb-3">
                            <div class="img-preview-box">
                                <?php if(!empty($photo_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($photo_path); ?>" alt="Doctor Photo">
                                <?php else: ?>
                                    <div class="py-4">
                                        <i class="fas fa-user-circle fa-4x text-muted mb-2"></i><br>
                                        <span class="text-muted small">No photo uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo_path); ?>">
                            
                            <label class="form-label small mt-2">Upload New Photo</label>
                            <input type="file" name="photo" class="form-control form-control-sm">
                            <div class="form-text small">Recommended size: Square (e.g. 500x500px).</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="doctors.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>