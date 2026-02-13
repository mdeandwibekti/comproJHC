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
    $stmt->close(); // Tutup statement setelah selesai pakai
    unset($stmt);   // Hapus variabel agar tidak bentrok di bawah
}

// --- 2. PROSES SIMPAN (CREATE / UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id             = intval($_POST['id']);
    $name           = trim($_POST['name']);
    $category       = $_POST['category'];
    $display_order  = intval($_POST['display_order']);
    $description    = $_POST['description'] ?? '';
    $special_skills = $_POST['special_skills'] ?? '';
    $additional_info = $_POST['additional_info'] ?? '';
    
    // Ambil data tombol (jika kosong, isi default)
    $btn_text       = !empty($_POST['btn_text']) ? $_POST['btn_text'] : 'Buat Janji Temu';
    $btn_link       = !empty($_POST['btn_link']) ? $_POST['btn_link'] : '#';
    
    $icon_path      = $_POST['old_icon'];
    $image_path     = $_POST['old_image'];

    // Folder tujuan upload (Naik 2 level dari public/admin)
    $upload_dir = "../../assets/img/departments/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // A. Handle Upload Ikon
    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
        
        if(in_array($ext, $allowed)){
            $icon_name = "icon_" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $upload_dir . $icon_name)) {
                // Hapus file lama
                if (!empty($_POST['old_icon']) && file_exists("../../" . $_POST['old_icon'])) {
                    unlink("../../" . $_POST['old_icon']);
                }
                $icon_path = "assets/img/departments/" . $icon_name;
            }
        }
    }

    // B. Handle Upload Gambar Utama
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];

        if(in_array($ext, $allowed)){
            $img_name = "img_" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $img_name)) {
                // Hapus file lama
                if (!empty($_POST['old_image']) && file_exists("../../" . $_POST['old_image'])) {
                    unlink("../../" . $_POST['old_image']);
                }
                $image_path = "assets/img/departments/" . $img_name;
            }
        }
    }

    // C. Simpan ke Database
    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE departments SET name=?, category=?, icon_path=?, image_path=?, display_order=?, description=?, special_skills=?, additional_info=?, btn_text=?, btn_link=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssisssssi", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $additional_info, $btn_text, $btn_link, $id);
    } else {
        // INSERT
        $sql = "INSERT INTO departments (name, category, icon_path, image_path, display_order, description, special_skills, additional_info, btn_text, btn_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssisssss", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $additional_info, $btn_text, $btn_link);
    }

    if ($stmt->execute()) {
        header("Location: departments.php?status=saved");
        exit();
    } else {
        $error = "Gagal menyimpan data: " . $stmt->error;
    }
    $stmt->close();
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .card-custom { 
        border-radius: 20px; border: none; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        background: #fff;
    }

    .form-section-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--jhc-red-dark);
        font-weight: 800;
        margin-top: 30px;
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
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.9; }

    .img-preview {
        background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 10px;
        padding: 10px; text-align: center; margin-bottom: 10px;
    }
    .img-preview img { max-height: 80px; object-fit: contain; }
</style>

<div class="container-fluid py-4">
    <div class="card card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0"><?= $id > 0 ? 'Edit' : 'Tambah'; ?> Departemen & Layanan</h4>
                <p class="text-muted small mb-0">Kelola informasi layanan kesehatan dan fasilitas rumah sakit.</p>
            </div>
            <a href="departments.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="old_icon" value="<?= htmlspecialchars($data['icon_path'] ?? ''); ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['image_path'] ?? ''); ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="form-section-title mt-0">Informasi Dasar</div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Departemen / Layanan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg" value="<?= htmlspecialchars($data['name']); ?>" required placeholder="Contoh: Instalasi Gawat Darurat">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Lengkap</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Jelaskan secara rinci tentang layanan ini..."><?= htmlspecialchars($data['description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keahlian Khusus / Sub-Layanan</label>
                        <textarea name="special_skills" class="form-control" rows="3" placeholder="Contoh: Operasi CABG, Bedah Jantung Anak (Pisahkan dengan koma)"><?= htmlspecialchars($data['special_skills']); ?></textarea>
                        <small class="text-muted">Akan ditampilkan sebagai poin-poin keunggulan di dalam modal detail.</small>
                    </div>

                    <div class="form-section-title">Konfigurasi Tombol Aksi (CTA)</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Teks Tombol</label>
                            <input type="text" name="btn_text" class="form-control" 
                                   value="<?= htmlspecialchars($data['btn_text']); ?>" 
                                   placeholder="Contoh: Buat Janji Temu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Link Tujuan</label>
                            <input type="text" name="btn_link" class="form-control" 
                                   value="<?= htmlspecialchars($data['btn_link']); ?>" 
                                   placeholder="Contoh: https://wa.me/628... atau booking.php">
                            <small class="text-primary fst-italic" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i> Gunakan link lengkap (http/https) untuk eksternal.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bg-light p-4 rounded-4 h-100">
                        <div class="form-section-title mt-0">Klasifikasi</div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="category" class="form-select border-0 shadow-sm py-2">
                                <option value="Poliklinik" <?= ($data['category'] == 'Poliklinik') ? 'selected' : ''; ?>>Poliklinik (Rawat Jalan)</option>
                                <option value="Layanan" <?= ($data['category'] == 'Layanan') ? 'selected' : ''; ?>>Layanan Unggulan</option>
                                <option value="Penunjang" <?= ($data['category'] == 'Penunjang') ? 'selected' : ''; ?>>Penunjang Medis</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Urutan Tampil</label>
                            <input type="number" name="display_order" class="form-control border-0 shadow-sm" value="<?= $data['display_order']; ?>">
                            <small class="text-muted">Angka kecil tampil duluan.</small>
                        </div>

                        <div class="form-section-title">Media Visual</div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ikon Layanan</label>
                            <?php if(!empty($data['icon_path'])): ?>
                                <div class="img-preview">
                                    <img src="../../<?= htmlspecialchars($data['icon_path']); ?>" alt="Icon">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="icon_file" class="form-control form-control-sm">
                            <small class="text-muted">Format: PNG, SVG, JPG.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Foto Fasilitas (Opsional)</label>
                            <?php if(!empty($data['image_path'])): ?>
                                <div class="img-preview">
                                    <img src="../../<?= htmlspecialchars($data['image_path']); ?>" alt="Image" style="width: 100%; height: auto; border-radius: 5px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image_file" class="form-control form-control-sm">
                            <small class="text-muted">Format: JPG, PNG, WEBP.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-3 border-top text-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// Tidak perlu tutup mysqli lagi jika sudah di layout footer, tapi untuk aman:
if(isset($mysqli)) $mysqli->close(); 
require_once 'layout/footer.php'; 
?>