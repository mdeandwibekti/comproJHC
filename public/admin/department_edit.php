<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA ---
$name = $category = $description = $special_skills = $additional_info = $icon_path = $icon_hover_path = "";
$display_order = 0;
$name_err = "";
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// Ambil data jika mode Edit
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, category, description, special_skills, additional_info, icon_path, icon_hover_path, display_order FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $category, $description, $special_skills, $additional_info, $icon_path, $icon_hover_path, $display_order);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

// Proses Simpan (Insert/Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"] ?? ''); 
    $special_skills = trim($_POST["special_skills"] ?? ''); 
    $additional_info = trim($_POST["additional_info"] ?? ''); 
    $display_order = (int)$_POST["display_order"];
    $icon_path = $_POST['current_icon'];
    $icon_hover_path = $_POST['current_icon_hover'];

    // Handle File Upload Ikon Utama
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
            $sql = "INSERT INTO departments (name, category, description, special_skills, additional_info, icon_path, icon_hover_path, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE departments SET name=?, category=?, description=?, special_skills=?, additional_info=?, icon_path=?, icon_hover_path=?, display_order=? WHERE id=?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssssssi", $name, $category, $description, $special_skills, $additional_info, $icon_path, $icon_hover_path, $display_order);
            } else {
                $stmt->bind_param("sssssssii", $name, $category, $description, $special_skills, $additional_info, $icon_path, $icon_hover_path, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                header("location: departments.php?status=saved");
                exit();
            }
            $stmt->close();
        }
    }
}

require_once 'layout/header.php';
$page_title_form = empty($id) ? "Tambah Unit Baru" : "Edit Unit & Layanan";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

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
        margin-bottom: 40px;
    }

    .form-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--jhc-red-dark);
        font-weight: 800;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .form-section-title::after { content: ""; flex: 1; height: 1px; background: #eee; margin-left: 15px; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 35px; font-weight: 700; border: none; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); transition: 0.3s; 
    }
    .btn-jhc-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4); }

    .img-preview-box { 
        background: #fcfcfc; border: 2px dashed #e0e0e0; 
        border-radius: 15px; padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 70px; object-fit: contain; }
    .img-preview-box:hover { border-color: var(--jhc-red-dark); background: #fff9f9; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?= $page_title_form; ?></h3>
                <p class="text-muted small mb-0">Lengkapi detail layanan untuk ditampilkan di portal publik RS JHC.</p>
            </div>
            <a href="departments.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <form action="department_edit.php<?= $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="form-section-title">Informasi Dasar</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Nama Poliklinik / Layanan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg shadow-sm <?= (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($name); ?>" placeholder="Contoh: Poliklinik Jantung" required>
                            <div class="invalid-feedback"><?= $name_err; ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select name="category" class="form-select form-select-lg shadow-sm">
                                <option value="Poliklinik" <?= ($category == 'Poliklinik') ? 'selected' : ''; ?>>Poliklinik</option>
                                <option value="Layanan" <?= ($category == 'Layanan') ? 'selected' : ''; ?>>Layanan Unggulan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title mt-5">Konten Publik</div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Deskripsi Utama</label>
                        <textarea name="description" class="form-control shadow-sm" rows="5" placeholder="Jelaskan secara umum mengenai unit/layanan ini..."><?= htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Keahlian Khusus & Fasilitas</label>
                            <textarea name="special_skills" class="form-control shadow-sm" rows="5" placeholder="Contoh: &#10;• Operasi Katarak&#10;• Laser Mata&#10;• USG 4D"><?= htmlspecialchars($special_skills ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Informasi Tambahan</label>
                            <textarea name="additional_info" class="form-control shadow-sm" rows="5" placeholder="Contoh: &#10;• Melayani BPJS&#10;• Jam operasional 08:00 - 20:00"><?= htmlspecialchars($additional_info ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 border-start ps-lg-5">
                    <div class="form-section-title">Visual & Urutan</div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Ikon Departemen</label>
                        <div class="img-preview-box mb-3">
                            <?php if(!empty($icon_path)): ?>
                                <img src="../<?= htmlspecialchars($icon_path); ?>" alt="Preview">
                            <?php else: ?>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted opacity-25"></i>
                                <p class="small text-muted mb-0 mt-2">Belum ada ikon</p>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_icon" value="<?= $icon_path; ?>">
                        <input type="file" name="icon" class="form-control form-control-sm border-0 bg-light shadow-sm">
                        <div class="form-text x-small">Format: PNG/SVG transparan, max 500kb.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Nomor Urut Tampilan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-sort-numeric-down text-muted"></i></span>
                            <input type="number" name="display_order" class="form-control" value="<?= $display_order; ?>">
                        </div>
                        <div class="form-text x-small">Urutan terkecil akan tampil paling depan.</div>
                    </div>

                    <input type="hidden" name="current_icon_hover" value="<?= $icon_hover_path; ?>">
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-check-circle me-2"></i> Simpan Konten Departemen
                </button>
            </div>
        </form>
    </div>
</div>

<?php $mysqli->close(); require_once 'layout/footer.php'; ?>