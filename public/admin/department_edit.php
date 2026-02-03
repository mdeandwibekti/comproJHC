<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi variabel
$name = $description = $icon_path = $icon_hover_path = "";
$display_order = 0;
$name_err = "";
$page_title = "Add New Department";
$id = null;

// Cek mode Edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Department";
    
    $sql = "SELECT name, description, icon_path, icon_hover_path, display_order FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $description, $icon_path, $icon_hover_path, $display_order);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

// Proses Simpan (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    // Gunakan null coalescing operator (??) untuk menangani description kosong
    $description = trim($_POST["description"] ?? ''); 
    $display_order = trim($_POST["display_order"]);
    
    $icon_path = $_POST['current_icon'];
    $icon_hover_path = $_POST['current_icon_hover'];

    // Handle Upload Icon Utama
    if (isset($_FILES["icon"]) && $_FILES["icon"]["error"] == 0) {
        $new_filename = uniqid() . '-icon-' . basename($_FILES["icon"]["name"]);
        $upload_dir = "../assets/img/icons/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (move_uploaded_file($_FILES["icon"]["tmp_name"], $upload_dir . $new_filename)) {
            $icon_path = "assets/img/icons/" . $new_filename;
        }
    }

    // Handle Upload Icon Hover
    if (isset($_FILES["icon_hover"]) && $_FILES["icon_hover"]["error"] == 0) {
        $new_filename = uniqid() . '-hover-' . basename($_FILES["icon_hover"]["name"]);
        $upload_dir = "../assets/img/icons/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (move_uploaded_file($_FILES["icon_hover"]["tmp_name"], $upload_dir . $new_filename)) {
            $icon_hover_path = "assets/img/icons/" . $new_filename;
        }
    }

    if (empty($name)) {
        $name_err = "Please enter a name.";
    } else {
        if (empty($id)) {
            // Insert
            $sql = "INSERT INTO departments (name, description, icon_path, icon_hover_path, display_order) VALUES (?, ?, ?, ?, ?)";
        } else {
            // Update
            $sql = "UPDATE departments SET name = ?, description = ?, icon_path = ?, icon_hover_path = ?, display_order = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("ssssi", $name, $description, $icon_path, $icon_hover_path, $display_order);
            } else {
                $stmt->bind_param("ssssii", $name, $description, $icon_path, $icon_hover_path, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                // Redirect kembali ke departments.php
                header("location: departments.php?saved=true");
                exit();
            } else {
                echo "Something went wrong. Please try again later. Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <form action="department_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="form-group">
            <label>Department Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>

        <div class="form-group mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>

        <div class="form-group mt-3">
            <label>Display Order</label>
            <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Icon (Normal)</label><br>
                    <?php if(!empty($icon_path)): ?>
                        <img src="../<?php echo htmlspecialchars($icon_path); ?>" width="50" class="img-thumbnail mb-2" style="background: #ccc;"><br>
                    <?php endif; ?>
                    <input type="hidden" name="current_icon" value="<?php echo htmlspecialchars($icon_path); ?>">
                    <input type="file" name="icon" class="form-control-file">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Icon (On Hover)</label><br>
                    <?php if(!empty($icon_hover_path)): ?>
                        <img src="../<?php echo htmlspecialchars($icon_hover_path); ?>" width="50" class="img-thumbnail mb-2" style="background: #ccc;"><br>
                    <?php endif; ?>
                    <input type="hidden" name="current_icon_hover" value="<?php echo htmlspecialchars($icon_hover_path); ?>">
                    <input type="file" name="icon_hover" class="form-control-file">
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="departments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>