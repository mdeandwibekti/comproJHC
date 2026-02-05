<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi variabel (Disesuaikan dengan database asli)
$name = $category = $description = $icon_path = $icon_hover_path = "";
$display_order = 0;
$name_err = "";
$page_title = "Add New Department";
$icon_header = "fa-plus";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Department";
    $icon_header = "fa-pen";
    
    // Query diperbaiki: Menghapus kolom 'education' dan 'expertise' yang tidak ada
    $sql = "SELECT name, category, description, icon_path, icon_hover_path, display_order FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $category, $description, $icon_path, $icon_hover_path, $display_order);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"] ?? ''); 
    $display_order = trim($_POST["display_order"]);
    
    $icon_path = $_POST['current_icon'];
    $icon_hover_path = $_POST['current_icon_hover'];

    // Handle File Uploads
    $upload_dir = "../assets/img/icons/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES["icon"]) && $_FILES["icon"]["error"] == 0) {
        $new_filename = uniqid() . '-icon-' . basename($_FILES["icon"]["name"]);
        if (move_uploaded_file($_FILES["icon"]["tmp_name"], $upload_dir . $new_filename)) {
            $icon_path = "assets/img/icons/" . $new_filename;
        }
    }

    if (isset($_FILES["icon_hover"]) && $_FILES["icon_hover"]["error"] == 0) {
        $new_filename = uniqid() . '-hover-' . basename($_FILES["icon_hover"]["name"]);
        if (move_uploaded_file($_FILES["icon_hover"]["tmp_name"], $upload_dir . $new_filename)) {
            $icon_hover_path = "assets/img/icons/" . $new_filename;
        }
    }

    if (empty($name)) {
        $name_err = "Please enter a name.";
    } else {
        // Logika SQL diperbaiki: Menghapus kolom yang tidak ada
        if (empty($id)) {
            $sql = "INSERT INTO departments (name, category, description, icon_path, icon_hover_path, display_order) VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE departments SET name = ?, category = ?, description = ?, icon_path = ?, icon_hover_path = ?, display_order = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssssi", $name, $category, $description, $icon_path, $icon_hover_path, $display_order);
            } else {
                $stmt->bind_param("sssssii", $name, $category, $description, $icon_path, $icon_hover_path, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                echo "<script>window.location.href='departments.php?saved=true';</script>";
                exit();
            } else {
                $db_err = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    .page-header { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--jhc-red-dark); margin-bottom: 2rem; }
    .main-card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .btn-save { background: var(--jhc-gradient); border: none; color: white; padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; transition: 0.3s; }
    .btn-save:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(138, 48, 51, 0.3); }
    .img-preview-box { background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; padding: 15px; text-align: center; margin-bottom: 10px; }
    .img-preview-box img { max-height: 60px; object-fit: contain; }
</style>

<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="mb-1 text-dark fw-bold"><i class="fas <?php echo $icon_header; ?> me-2" style="color: var(--jhc-red-dark);"></i> <?php echo $page_title; ?></h3>
        <p class="text-muted mb-0 small">Kelola informasi layanan atau poliklinik rumah sakit.</p>
    </div>

    <div class="card main-card">
        <div class="card-body p-4">
            <?php if(isset($db_err)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $db_err; ?></div>
            <?php endif; ?>

            <form action="department_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nama Layanan/Poli <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
                                <div class="invalid-feedback"><?php echo $name_err; ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="category" class="form-select">
                                    <option value="Poliklinik" <?php echo ($category == 'Poliklinik') ? 'selected' : ''; ?>>Poliklinik</option>
                                    <option value="Layanan" <?php echo ($category == 'Layanan') ? 'selected' : ''; ?>>Layanan Unggulan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Tuliskan deskripsi layanan di sini..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Urutan Tampilan</label>
                                <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 border-start">
                        <h6 class="text-muted fw-bold mb-3 text-uppercase small">Pengaturan Ikon</h6>
                        <div class="mb-4">
                            <label class="form-label small">Ikon Normal</label>
                            <div class="img-preview-box">
                                <?php if(!empty($icon_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($icon_path); ?>" alt="Icon">
                                <?php else: ?>
                                    <i class="fas fa-image fa-2x text-muted opacity-25"></i>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="current_icon" value="<?php echo htmlspecialchars($icon_path); ?>">
                            <input type="file" name="icon" class="form-control form-control-sm">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Ikon Hover (Opsional)</label>
                            <div class="img-preview-box">
                                <?php if(!empty($icon_hover_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($icon_hover_path); ?>" alt="Hover Icon">
                                <?php else: ?>
                                    <i class="fas fa-mouse-pointer fa-2x text-muted opacity-25"></i>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="current_icon_hover" value="<?php echo htmlspecialchars($icon_hover_path); ?>">
                            <input type="file" name="icon_hover" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="departments.php" class="btn btn-light px-4 rounded-pill fw-bold">Batal</a>
                    <button type="submit" class="btn btn-save"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $mysqli->close(); require_once 'layout/footer.php'; ?>