<?php
require_once "../../config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Default data untuk Tambah Baru
$data = [
    'name' => '', 'category' => 'Poliklinik', 'display_order' => 0, 
    'description' => '', 'special_skills' => '', 'additional_info' => '',
    'icon_path' => '', 'image_path' => '',
    'btn_text' => 'Buat Janji Temu', 'btn_link' => '#' // Default untuk fitur tombol baru
];

// Jika ID ada, ambil data untuk diedit
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $data = $row;
    }
}

// Proses Simpan Data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id              = intval($_POST['id']);
    $name            = $_POST['name'];
    $category        = $_POST['category'];
    $display_order   = intval($_POST['display_order']);
    $description     = $_POST['description'] ?? '';
    $special_skills  = $_POST['special_skills'] ?? '';
    $additional_info = $_POST['additional_info'] ?? '';
    $btn_text        = $_POST['btn_text'] ?: 'Buat Janji Temu';
    $btn_link        = $_POST['btn_link'] ?: '#';
    
    $icon_path  = $_POST['old_icon'];
    $image_path = $_POST['old_image'];

    $upload_dir = "../../assets/img/departments/"; // Pastikan path benar ke folder assets
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // 1. Handle Upload Ikon
    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] == 0) {
        $ext = pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION);
        $icon_name = "icon_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $upload_dir . $icon_name)) {
            if (!empty($_POST['old_icon']) && file_exists("../../" . $_POST['old_icon'])) {
                unlink("../../" . $_POST['old_icon']);
            }
            $icon_path = "assets/img/departments/" . $icon_name;
        }
    }

    // 2. Handle Upload Gambar Utama
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $img_name = "img_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $img_name)) {
            if (!empty($_POST['old_image']) && file_exists("../../" . $_POST['old_image'])) {
                unlink("../../" . $_POST['old_image']);
            }
            $image_path = "assets/img/departments/" . $img_name;
        }
    }

    if ($id > 0) {
        // Update data yang sudah ada
        $sql = "UPDATE departments SET name=?, category=?, icon_path=?, image_path=?, display_order=?, description=?, special_skills=?, additional_info=?, btn_text=?, btn_link=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssisssssi", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $additional_info, $btn_text, $btn_link, $id);
    } else {
        // Insert data baru
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
    
    .card-custom { border-radius: 20px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .btn-jhc-save { background: var(--jhc-gradient); color: white !important; border-radius: 12px; padding: 12px 35px; font-weight: 700; border: none; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); }
</style>

<div class="container-fluid py-4">
    <div class="card card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0"><?= $id > 0 ? 'Edit' : 'Tambah'; ?> Departemen & Layanan</h4>
            <a href="departments.php" class="btn btn-outline-secondary btn-sm rounded-pill">Kembali</a>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="old_icon" value="<?= htmlspecialchars($data['icon_path'] ?? ''); ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['image_path'] ?? ''); ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="form-section-title">Informasi Dasar</div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Departemen / Layanan</label>
                        <input type="text" name="name" class="form-control form-control-lg" value="<?= htmlspecialchars($data['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Lengkap</label>
                        <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keahlian Khusus / Sub-Layanan</label>
                        <textarea name="special_skills" class="form-control" rows="3" placeholder="Contoh: Operasi CABG, Prosedur Kateterisasi..."><?= htmlspecialchars($data['special_skills'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-section-title">Konfigurasi Tombol Call-to-Action (CTA)</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teks Tombol</label>
                            <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($data['btn_text'] ?? 'Buat Janji Temu'); ?>" placeholder="Misal: Chat WhatsApp IGD">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Link Tujuan</label>
                            <input type="text" name="btn_link" class="form-control" value="<?= htmlspecialchars($data['btn_link'] ?? '#'); ?>" placeholder="Misal: https://wa.me/628xxx">
                            <small class="text-muted italic">Gunakan link WhatsApp atau link internal halaman.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-section-title">Klasifikasi</div>
                    <div class="mb-3 p-3 bg-light rounded-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="category" class="form-select border-0 shadow-sm">
                            <option value="Poliklinik" <?= ($data['category'] ?? '') == 'Poliklinik' ? 'selected' : ''; ?>>Poliklinik (Rawat Jalan)</option>
                            <option value="Layanan" <?= ($data['category'] ?? '') == 'Layanan' ? 'selected' : ''; ?>>Layanan Unggulan (Fasilitas)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Urutan Tampilan</label>
                        <input type="number" name="display_order" class="form-control" value="<?= $data['display_order'] ?? 0; ?>">
                    </div>

                    <div class="form-section-title">Media Visual</div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ikon Layanan (PNG/SVG)</label>
                        <div class="mb-2 p-2 border rounded bg-white text-center">
                            <?php if(!empty($data['icon_path'])): ?>
                                <img src="../../<?= $data['icon_path']; ?>" style="max-height: 50px;">
                            <?php else: ?>
                                <small class="text-muted">Belum ada ikon</small>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="icon_file" class="form-control form-control-sm">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Gambar/Foto Fasilitas</label>
                        <?php if(!empty($data['image_path'])): ?>
                            <div class="mb-2">
                                <img src="../../<?= $data['image_path']; ?>" class="img-fluid rounded shadow-sm border">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image_file" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-3 border-top text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$mysqli->close(); 
require_once 'layout/footer.php'; 
?>