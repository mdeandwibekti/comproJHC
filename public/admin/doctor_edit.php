<?php
require_once "../../config.php";
require_once 'layout/header.php';

$name = $specialty = $schedule = $photo_path = "";
$is_featured = 0;
$department_id = 0;
$page_title = "Add New Doctor";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Doctor";
    // Ambil data termasuk schedule
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

// Fetch departments for dropdown
$departments = [];
$dept_res = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
if($dept_res) { while($row = $dept_res->fetch_assoc()) { $departments[] = $row; } }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    $specialty = trim($_POST["specialty"]);
    $schedule = trim($_POST["schedule"]); // Ambil input jadwal
    $department_id = $_POST["department_id"];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $photo_path = $_POST['current_photo'];

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $new_filename = uniqid() . '-doc-' . basename($_FILES["photo"]["name"]);
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
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
            header("location: doctors.php?saved=true");
            exit();
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3><hr>
    <form action="doctor_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Doctor Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Specialty (Spesialis)</label>
                    <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($specialty); ?>" required>
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <label>Jadwal Praktik (Schedule)</label>
            <textarea name="schedule" class="form-control" rows="3" placeholder="Contoh: Senin - Rabu: 08.00 - 12.00, Jumat: 13.00 - 15.00"><?php echo htmlspecialchars($schedule); ?></textarea>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="0">Select Department</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featuredCheck" <?php echo ($is_featured == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="featuredCheck">Show on Homepage (Featured)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <label>Photo</label><br>
            <?php if(!empty($photo_path)): ?>
                <img src="../<?php echo htmlspecialchars($photo_path); ?>" width="100" class="img-thumbnail mb-2"><br>
            <?php endif; ?>
            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo_path); ?>">
            <input type="file" name="photo" class="form-control-file">
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="doctors.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>