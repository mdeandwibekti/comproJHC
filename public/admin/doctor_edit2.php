<?php
require_once "../../config.php";
require_once 'layout/header.php';

$name = $specialty = $photo_path = $schedule = "";
$department_id = 0;
$is_featured = 0;
$name_err = "";
$page_title = "Add New Doctor";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Doctor";
}

// Fetch departments for the dropdown
$departments_result = $mysqli->query("SELECT id, name FROM departments2 ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }
    $specialty = trim($_POST["specialty"]);
    $schedule = trim($_POST["schedule"]);
    $department_id = $_POST["department_id"];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $photo_path = $_POST['current_photo'];

    // Handle photo upload
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["photo"]["name"]);
        $upload_dir = "../assets/img/doctors/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $upload_path = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_path)) {
            $photo_path = "assets/img/doctors/" . $new_filename;
        }
    }

    if (empty($name_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO doctors2 (name, specialty, schedule, department_id, is_featured, photo_path) VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE doctors2 SET name = ?, specialty = ?, schedule = ?, department_id = ?, is_featured = ?, photo_path = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssisi", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path);
            } else {
                $stmt->bind_param("sssisii", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path, $id);
            }
            
            if ($stmt->execute()) {
                header("location: doctors2.php?saved=true");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

if (!empty($id)) {
    $sql = "SELECT name, specialty, schedule, department_id, is_featured, photo_path FROM doctors2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($name, $specialty, $schedule, $department_id, $is_featured, $photo_path);
            $stmt->fetch();
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <form action="doctor_edit2.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="form-group">
            <label>Doctor Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Specialty</label>
            <input type="text" name="specialty" class="form-control" value="<?php echo $specialty; ?>">
        </div>
        <div class="form-group mt-3">
            <label>Schedule</label>
            <textarea name="schedule" class="form-control" rows="5"><?php echo $schedule; ?></textarea>
        </div>
        <div class="form-group mt-3">
            <label>Department</label>
            <select name="department_id" class="form-control">
                <option value="">Select Department</option>
                <?php
                if ($departments_result->num_rows > 0) {
                    while($dept = $departments_result->fetch_assoc()) {
                        $selected = ($dept['id'] == $department_id) ? 'selected' : '';
                        echo '<option value="' . $dept['id'] . '" ' . $selected . '>' . htmlspecialchars($dept['name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group mt-3">
            <label>Photo</label><br>
            <?php if($photo_path): ?><img src="../<?php echo $photo_path; ?>" width="100" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_photo" value="<?php echo $photo_path; ?>">
            <input type="file" name="photo" class="form-control-file">
        </div>

        <div class="form-check mt-3">
            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($is_featured == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_featured">Feature this doctor on the homepage</label>
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="doctors2.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>