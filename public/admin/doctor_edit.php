<?php
require_once "../../config.php";
require_once 'layout/header.php';

$name = $specialty = $photo_path = "";
$department_id = 0;
$is_featured = 0;
$name_err = "";
$page_title = "Add New Doctor";
$id = null;

// Cek apakah mode Edit (Ada ID di URL)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Doctor";
}

// Fetch departments for the dropdown (Tabel: departments)
$departments_result = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    // Validasi Nama
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    $specialty = trim($_POST["specialty"]);
    $department_id = !empty($_POST["department_id"]) ? $_POST["department_id"] : NULL;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Ambil foto lama dulu (default)
    $photo_path = $_POST['current_photo'];

    // Handle photo upload (Jika ada file baru diupload)
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["photo"]["name"]);
        $upload_dir = "../assets/img/gallery/"; // Sesuaikan folder dengan doctor_edit sebelumnya
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_path)) {
            // Path untuk disimpan di database
            $photo_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($name_err)) {
        if (empty($id)) {
            // INSERT (Tabel: doctors) - Tanpa schedule
            $sql = "INSERT INTO doctors (name, specialty, department_id, is_featured, photo_path) VALUES (?, ?, ?, ?, ?)";
        } else {
            // UPDATE (Tabel: doctors) - Tanpa schedule
            $sql = "UPDATE doctors SET name = ?, specialty = ?, department_id = ?, is_featured = ?, photo_path = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("ssiis", $name, $specialty, $department_id, $is_featured, $photo_path);
            } else {
                $stmt->bind_param("ssiisi", $name, $specialty, $department_id, $is_featured, $photo_path, $id);
            }
            
            if ($stmt->execute()) {
                // Redirect ke doctors.php
                header("location: doctors.php?saved=true");
                exit();
            } else {
                echo "Something went wrong. Please try again later. Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Database error: " . $mysqli->error;
        }
    }
}

// Ambil data dokter jika sedang mode Edit
if (!empty($id)) {
    // Tabel: doctors
    $sql = "SELECT name, specialty, department_id, is_featured, photo_path FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($name, $specialty, $department_id, $is_featured, $photo_path);
                $stmt->fetch();
            } else {
                // Jika ID tidak ditemukan
                header("location: doctors.php");
                exit();
            }
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <form action="doctor_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="form-group">
            <label>Doctor Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        
        <div class="form-group mt-3">
            <label>Specialty</label>
            <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($specialty); ?>">
        </div>
        
        <div class="form-group mt-3">
            <label>Department</label>
            <select name="department_id" class="form-control">
                <option value="">Select Department</option>
                <?php
                if ($departments_result && $departments_result->num_rows > 0) {
                    // Reset pointer data departments jika perlu
                    $departments_result->data_seek(0);
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
            <?php if($photo_path): ?>
                <img src="../<?php echo htmlspecialchars($photo_path); ?>" width="100" class="img-thumbnail mb-2"><br>
            <?php endif; ?>
            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo_path); ?>">
            <input type="file" name="photo" class="form-control-file">
        </div>

        <div class="form-check mt-3">
            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($is_featured == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_featured">Feature this doctor on the homepage</label>
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="doctors.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>