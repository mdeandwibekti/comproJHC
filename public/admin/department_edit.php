<?php
require_once "../../config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Default data untuk Tambah Baru
$data = [
    'name' => '', 
    'category' => 'Poliklinik', 
    'display_order' => 0, 
    'description' => '', 
    'special_skills' => '', 
    'additional_info' => '',
    'icon_path' => '', 
    'image_path' => '',
    'btn_text' => 'Buat Janji Temu', 
    'btn_link' => '#' 
];

// --- 1. AMBIL DATA (READ) ---
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $data = $row;
    }
    $stmt->close();
}

// --- 2. PROSES SIMPAN (CREATE / UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id             = intval($_POST['id']);
    $name           = trim($_POST['name']);
    $category       = $_POST['category'];
    $display_order  = intval($_POST['display_order']);
    $description    = $_POST['description'] ?? '';
    $special_skills = $_POST['special_skills'] ?? '';
    
    $btn_text       = !empty($_POST['btn_text']) ? $_POST['btn_text'] : 'Buat Janji Temu';
    $btn_link       = !empty($_POST['btn_link']) ? $_POST['btn_link'] : '#';
    
    $icon_path      = $_POST['old_icon'];
    $image_path     = $_POST['old_image'];

    $upload_dir = "../../assets/img/departments/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION));
        $icon_name = "icon_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $upload_dir . $icon_name)) {
            if (!empty($_POST['old_icon']) && file_exists("../../" . $_POST['old_icon'])) {
                @unlink("../../" . $_POST['old_icon']);
            }
            $icon_path = "assets/img/departments/" . $icon_name;
        }
    }

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $img_name = "img_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $img_name)) {
            if (!empty($_POST['old_image']) && file_exists("../../" . $_POST['old_image'])) {
                @unlink("../../" . $_POST['old_image']);
            }
            $image_path = "assets/img/departments/" . $img_name;
        }
    }

    if ($id > 0) {
        $sql = "UPDATE departments SET name=?, category=?, icon_path=?, image_path=?, display_order=?, description=?, special_skills=?, btn_text=?, btn_link=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssissssi", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $btn_text, $btn_link, $id);
    } else {
        $sql = "INSERT INTO departments (name, category, icon_path, image_path, display_order, description, special_skills, btn_text, btn_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssissss", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $btn_text, $btn_link);
    }

    if ($stmt->execute()) {
        header("Location: departments.php?status=saved");
        exit();
    }
    $stmt->close();
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
    }

    .form-wrapper {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .form-header {
        background: #fcfcfc;
        border-bottom: 1px solid #f1f1f1;
        padding: 2rem;
    }

    /* Modern Inputs */
    .form-label { font-weight: 700; color: #444; font-size: 0.9rem; margin-bottom: 0.6rem; }
    .form-control, .form-select {
        border-radius: 12px; border: 2.5px solid #f1f3f5; padding: 0.8rem 1rem;
        transition: 0.3s; background: #fcfcfc;
    }
    .form-control:focus {
        border-color: var(--jhc-red); background: #fff; box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1);
    }

    /* Section Title */
    .input-group-title {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px;
        color: var(--jhc-red); font-weight: 800; display: flex; align-items: center;
        margin: 2.5rem 0 1.5rem;
    }
    .input-group-title::after { content: ""; flex: 1; height: 1.5px; background: #f1f1f1; margin-left: 15px; }

    /* Media Preview Card */
    .media-card {
        background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 18px;
        padding: 20px; transition: 0.3s;
    }
    .media-card:hover { border-color: var(--jhc-red); background: #fff; }
    
    .icon-preview-box {
        width: 60px; height: 60px; background: white; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 15px;
    }
    .icon-preview-box img { width: 35px; height: 35px; object-fit: contain; }

    .image-preview-full {
        width: 100%; border-radius: 12px; margin-bottom: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .btn-save-jhc {
        background: var(--jhc-gradient); color: white !important;
        border-radius: 14px; padding: 1rem 3rem; font-weight: 700;
        border: none; box-shadow: 0 10px 25px rgba(138, 48, 51, 0.3);
        transition: 0.3s;
    }
    .btn-save-jhc:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(138, 48, 51, 0.4); }
</style>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4 ms-2">
        <ol class="breadcrumb" style="font-size: 0.85rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="departments.php" class="text-muted text-decoration-none">Departemen</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Form Input</li>
        </ol>
    </nav>

    <div class="form-wrapper">
        <div class="form-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold m-0 text-dark"><?= $id > 0 ? 'Update' : 'Registrasi'; ?> Departemen</h3>
                <p class="text-muted small mb-0">Lengkapi data untuk publikasi layanan ke website utama.</p>
            </div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="p-4 p-lg-5">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="old_icon" value="<?= htmlspecialchars($data['icon_path'] ?? ''); ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['image_path'] ?? ''); ?>">

            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="input-group-title mt-0">General Information</div>
                    
                    <div class="mb-4">
                        <label class="form-label">Nama Resmi Departemen / Layanan</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']); ?>" required placeholder="Contoh: Klinik Spesialis Jantung">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Uraian Profil</label>
                        <textarea name="description" class="form-control" rows="8" placeholder="Tuliskan deskripsi lengkap di sini..."><?= htmlspecialchars($data['description']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Poin Keunggulan (Specialties)</label>
                        <input type="text" name="special_skills" class="form-control" value="<?= htmlspecialchars($data['special_skills']); ?>" placeholder="Operasi Katup, Pemasangan Ring, dll (Gunakan koma)">
                        <small class="text-muted mt-2 d-block fst-italic">Pisahkan tiap poin dengan tanda koma.</small>
                    </div>

                    <div class="input-group-title">Call to Action Button</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Label Tombol</label>
                            <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($data['btn_text']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link / WhatsApp URL</label>
                            <input type="text" name="btn_link" class="form-control" value="<?= htmlspecialchars($data['btn_link']); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="input-group-title mt-0">Classification</div>
                    
                    <div class="card bg-light border-0 p-4 rounded-4 mb-5">
                        <div class="mb-4">
                            <label class="form-label">Kategori Layanan</label>
                            <select name="category" class="form-select border-0 shadow-sm">
                                <option value="Poliklinik" <?= ($data['category'] == 'Poliklinik') ? 'selected' : ''; ?>>🏥 Poliklinik (Rawat Jalan)</option>
                                <option value="Layanan" <?= ($data['category'] == 'Layanan') ? 'selected' : ''; ?>>⭐️ Layanan Unggulan</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Urutan Prioritas (Display Order)</label>
                            <input type="number" name="display_order" class="form-control border-0 shadow-sm" value="<?= $data['display_order']; ?>">
                        </div>
                    </div>

                    <div class="input-group-title">Assets & Media</div>

                    <div class="media-card mb-4">
                        <label class="form-label d-block mb-3">Ikon Representasi</label>
                        <div class="d-flex align-items-center">
                            <div class="icon-preview-box me-4">
                                <?php if(!empty($data['icon_path'])): ?>
                                    <img src="../../<?= htmlspecialchars($data['icon_path']); ?>" alt="Icon">
                                <?php else: ?>
                                    <i class="fas fa-image text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="icon_file" class="form-control form-control-sm border-0 bg-white">
                                <small class="text-muted d-block mt-2">Format: SVG atau PNG (Transparan)</small>
                            </div>
                        </div>
                    </div>

                    <div class="media-card">
                        <label class="form-label d-block mb-3">Foto Visual Fasilitas</label>
                        <?php if(!empty($data['image_path'])): ?>
                            <img src="../../<?= htmlspecialchars($data['image_path']); ?>" class="image-preview-full" alt="Feature Image">
                        <?php endif; ?>
                        <input type="file" name="image_file" class="form-control form-control-sm border-0 bg-white">
                        <small class="text-muted d-block mt-2">Format: JPG, WEBP. Maks 2MB.</small>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-center">
                <button type="submit" class="btn btn-save-jhc">
                    <i class="fas fa-check-circle me-2"></i> Publikasikan Data
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
if(isset($mysqli)) $mysqli->close(); 
require_once 'layout/footer.php'; 
?>