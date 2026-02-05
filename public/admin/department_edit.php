<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA (Harus SEBELUM require layout/header.php) ---
$name = $category = $description = $icon_path = $icon_hover_path = "";
$display_order = 0;
$name_err = "";
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    // Query diperbaiki: Hanya mengambil kolom yang benar-benar ada di database
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
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"] ?? ''); 
    $display_order = (int)$_POST["display_order"];
    $icon_path = $_POST['current_icon'];
    $icon_hover_path = $_POST['current_icon_hover'];

    // Handle File Uploads (Ikon Normal)
    if (isset($_FILES["icon"]) && $_FILES["icon"]["error"] == 0) {
        $upload_dir = "../assets/img/icons/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $new_filename = uniqid() . '-icon-' . basename($_FILES["icon"]["name"]);
        if (move_uploaded_file($_FILES["icon"]["tmp_name"], $upload_dir . $new_filename)) {
            if (!empty($_POST['current_icon']) && file_exists("../" . $_POST['current_icon'])) unlink("../" . $_POST['current_icon']);
            $icon_path = "assets/img/icons/" . $new_filename;
        }
    }

    if (empty($name)) {
        $name_err = "Silakan masukkan nama layanan.";
    } else {
        if (empty($id)) {
            $sql = "INSERT INTO departments (name, category, description, icon_path, icon_hover_path, display_order) VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE departments SET name=?, category=?, description=?, icon_path=?, icon_hover_path=?, display_order=? WHERE id=?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssssi", $name, $category, $description, $icon_path, $icon_hover_path, $display_order);
            } else {
                $stmt->bind_param("sssssii", $name, $category, $description, $icon_path, $icon_hover_path, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                header("location: departments.php?saved=true");
                exit();
            }
            $stmt->close();
        }
    }
}

require_once 'layout/header.php';
$page_title_form = empty($id) ? "Tambah Layanan Baru" : "Edit Layanan / Poli";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper Neumorphism */
    .main-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    .btn-jhc-save { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 12px; 
        padding: 12px 30px; 
        font-weight: 700; 
        border: none; 
        transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box { 
        background: #fdfdfd; 
        border: 2px dashed #ddd; 
        border-radius: 15px; 
        padding: 20px; 
        text-align: center; 
    }
    .img-preview-box img { max-height: 80px; object-fit: contain; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_form; ?></h3>
                <p class="text-muted small mb-0">Kelola data spesialisasi medis dan unit pelayanan rumah sakit.</p>
            </div>
            <a href="departments.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="department_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Kategori</label>
                            <select name="category" class="form-select form-select-lg">
                                <option value="Poliklinik" <?php echo ($category == 'Poliklinik') ? 'selected' : ''; ?>>Poliklinik</option>
                                <option value="Layanan" <?php echo ($category == 'Layanan') ? 'selected' : ''; ?>>Layanan Unggulan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Deskripsi Layanan</label>
                        <textarea name="description" class="form-control" rows="8" placeholder="Tuliskan detail layanan di sini..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="mt-4" style="max-width: 200px;">
                        <label class="form-label fw-bold text-muted small text-uppercase">Urutan Tampilan</label>
                        <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
                    </div>
                </div>

                <div class="col-md-4 border-start ps-md-4">
                    <h6 class="text-muted fw-bold mb-3 small text-uppercase">Pengaturan Visual</h6>
                    
                    <div class="mb-4">
                        <label class="form-label small text-muted">Ikon Utama</label>
                        <div class="img-preview-box mb-2">
                            <?php if(!empty($icon_path)): ?>
                                <img src="../<?php echo htmlspecialchars($icon_path); ?>" alt="Icon">
                            <?php else: ?>
                                <i class="fas fa-image fa-2x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_icon" value="<?php echo $icon_path; ?>">
                        <input type="file" name="icon" class="form-control form-control-sm shadow-sm">
                    </div>

                    <div>
                        <label class="form-label small text-muted">Ikon Hover (Opsional)</label>
                        <div class="img-preview-box mb-2">
                            <?php if(!empty($icon_hover_path)): ?>
                                <img src="../<?php echo htmlspecialchars($icon_hover_path); ?>" alt="Hover">
                            <?php else: ?>
                                <i class="fas fa-mouse-pointer fa-2x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_icon_hover" value="<?php echo $icon_hover_path; ?>">
                        <input type="file" name="icon_hover" class="form-control form-control-sm shadow-sm">
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php $mysqli->close(); require_once 'layout/footer.php'; ?>