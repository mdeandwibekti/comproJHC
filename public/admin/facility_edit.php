<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi variabel
$name = $description = $image_path = "";
$display_order = 0;
$page_title = "Add New Facility";
$icon_header = "fa-plus";
$id = null;

// Cek apakah ini mode Edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Facility";
    $icon_header = "fa-edit";
    
    $sql = "SELECT * FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $name = $row['name'];
                $description = $row['description'];
                $image_path = $row['image_path'];
                $display_order = $row['display_order'];
            }
        }
        $stmt->close();
    }
}

// Proses Simpan Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $display_order = trim($_POST["display_order"]);
    $image_path = $_POST['current_image'];

    // Proses Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
        $upload_dir = "../assets/img/gallery/";
        
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            $image_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO facilities (name, description, display_order, image_path) VALUES (?, ?, ?, ?)";
    } else {
        $sql = "UPDATE facilities SET name = ?, description = ?, display_order = ?, image_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("ssis", $name, $description, $display_order, $image_path);
        } else {
            $stmt->bind_param("ssisi", $name, $description, $display_order, $image_path, $id);
        }
        
        if ($stmt->execute()) {
            echo "<script>window.location.href='facilities.php';</script>";
            exit();
        } else {
            $db_error = "Something went wrong. Please try again later.";
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

    .form-control:focus {
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
    .img-preview-box img { max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 8px; }

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
        <p class="text-muted mb-0 small">Please fill in the form below to add or update facility details.</p>
    </div>

    <div class="card main-card">
        <div class="card-body p-4">
            
            <?php if(isset($db_error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $db_error; ?></div>
            <?php endif; ?>

            <form action="facility_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label">Facility Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. VIP Room, MRI Scanner" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Describe the facility..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="text-muted fw-bold mb-3">Facility Image</h6>
                        
                        <div class="mb-3">
                            <div class="img-preview-box">
                                <?php if(!empty($image_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="Facility Image">
                                <?php else: ?>
                                    <div class="py-5">
                                        <i class="fas fa-image fa-4x text-muted mb-2 opacity-50"></i><br>
                                        <span class="text-muted small">No image uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                            
                            <label class="form-label small mt-2">Upload New Image</label>
                            <input type="file" name="image" class="form-control form-control-sm">
                            <div class="form-text small">Recommended size: 800x600px (Landscape).</div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="facilities.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>