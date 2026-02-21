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
    'icon_path' => '', 
    'btn_text' => 'Buat Janji Temu', 
    'btn_link' => '#' 
];

// --- 1. AMBIL DATA (READ) ---
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT id, name, category, display_order, description, special_skills, icon_path, btn_text, btn_link FROM departments WHERE id = ?");
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

    // Lokasi folder upload (Pastikan folder public/assets/img/departments tersedia)
    $upload_dir = "../../public/assets/img/departments/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // --- LOGIKA UPLOAD IKON ---
    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION));
        $icon_name = "icon_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $upload_dir . $icon_name)) {
            // Hapus ikon lama jika ada di server
            if (!empty($_POST['old_icon']) && file_exists("../../public/" . $_POST['old_icon'])) {
                @unlink("../../public/" . $_POST['old_icon']);
            }
            $icon_path = "assets/img/departments/" . $icon_name;
        }
    }

    if ($id > 0) {
        $sql = "UPDATE departments SET name=?, category=?, icon_path=?, display_order=?, description=?, special_skills=?, btn_text=?, btn_link=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssissssi", $name, $category, $icon_path, $display_order, $description, $special_skills, $btn_text, $btn_link, $id);
    } else {
        $sql = "INSERT INTO departments (name, category, icon_path, display_order, description, special_skills, btn_text, btn_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssissss", $name, $category, $icon_path, $display_order, $description, $special_skills, $btn_text, $btn_link);
    }

    if ($stmt->execute()) {
        header("Location: departments.php?msg=saved");
        exit();
    }
    $stmt->close();
}

require_once 'layout/header.php';
?>

<style>
    :root { --jhc-red: #8a3033; --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); }
    .form-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); overflow: hidden; }
    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; }
    .form-control, .form-select { border-radius: 10px; border: 2px solid #f1f3f5; padding: 0.7rem; transition: 0.3s; }
    .form-control:focus { border-color: var(--jhc-red); box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1); }
    
    .input-group-title { font-size: 0.75rem; text-transform: uppercase; color: var(--jhc-red); font-weight: 800; display: flex; align-items: center; margin: 2rem 0 1rem; }
    .input-group-title::after { content: ""; flex: 1; height: 1.5px; background: #f1f1f1; margin-left: 15px; }

    .media-card { background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 15px; padding: 25px; transition: 0.3s; text-align: center; }
    .icon-preview-box { width: 80px; height: 80px; background: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 0 auto 15px; border: 1px solid #eee; }
    .icon-preview-box img { width: 50px; height: 50px; object-fit: contain; }
    
    .btn-save-jhc { background: var(--jhc-gradient); color: white !important; border-radius: 12px; padding: 0.8rem 3rem; font-weight: 700; border: none; transition: 0.3s; width: 100%; }
    .btn-save-jhc:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(138, 48, 51, 0.3); }
</style>

<div class="container py-4">
    <div class="form-wrapper p-4 p-lg-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark"><?= $id > 0 ? 'Edit' : 'Tambah'; ?> Departemen</h3>
                <p class="text-muted small mb-0">Kelola visual ikon dan informasi layanan rumah sakit.</p>
            </div>
            <a href="departments.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Kembali</a>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="old_icon" value="<?= htmlspecialchars($data['icon_path']); ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="input-group-title mt-0">Detail Layanan</div>
                    <div class="mb-3">
                        <label class="form-label">Nama Poliklinik / Unit</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']); ?>" required placeholder="Contoh: Spesialis Bedah Jantung">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="Poliklinik" <?= ($data['category'] == 'Poliklinik') ? 'selected' : ''; ?>>Poliklinik</option>
                                <option value="Layanan" <?= ($data['category'] == 'Layanan') ? 'selected' : ''; ?>>Layanan Unggulan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-control" value="<?= $data['display_order']; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Jelaskan detail layanan atau poliklinik di sini..."><?= htmlspecialchars($data['description']); ?></textarea>
                    </div>

                    <div class="input-group-title">Navigasi Tombol</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teks Tombol (CTA)</label>
                            <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($data['btn_text']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link/WhatsApp</label>
                            <input type="text" name="btn_link" class="form-control" value="<?= htmlspecialchars($data['btn_link']); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="input-group-title mt-0">Identitas Visual</div>
                    
                    <div class="media-card">
                        <div class="icon-preview-box">
                            <?php 
                            $full_icon_path = "../../public/" . $data['icon_path'];
                            if(!empty($data['icon_path']) && file_exists($full_icon_path)): ?>
                                <img src="../../public/<?= htmlspecialchars($data['icon_path']); ?>?v=<?= time() ?>" alt="Icon">
                            <?php else: ?>
                                <i class="fas fa-image fa-2x text-light"></i>
                            <?php endif; ?>
                        </div>
                        <h6 class="fw-bold mb-1">Ikon Unit</h6>
                        <p class="text-muted small mb-3">Gunakan format PNG transparan atau SVG (Max 1MB)</p>
                        
                        <input type="file" name="icon_file" class="form-control form-control-sm">
                        
                        <?php if(!empty($data['icon_path'])): ?>
                            <div class="mt-3 p-2 bg-white rounded border">
                                <code class="small text-muted" style="word-break: break-all;"><?= $data['icon_path'] ?></code>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-save-jhc">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
if(isset($mysqli)) $mysqli->close(); 
require_once 'layout/footer.php'; 
?>