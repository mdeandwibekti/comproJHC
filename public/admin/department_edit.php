<?php
require_once "../../config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = [
    'name' => '', 'category' => 'Poliklinik', 'display_order' => 0, 
    'description' => '', 'special_skills' => '', 'additional_info' => '',
    'icon_path' => '', 'image_path' => ''
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
    $id             = intval($_POST['id']);
    $name           = $_POST['name'];
    $category       = $_POST['category'];
    $display_order  = intval($_POST['display_order']);
    $description    = $_POST['description'];
    $special_skills = $_POST['special_skills'];
    $additional_info = $_POST['additional_info'];
    
    $icon_path  = $_POST['old_icon'];
    $image_path = $_POST['old_image'];

    $upload_dir = "../assets/img/departments/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // 1. Handle Upload Ikon
    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] == 0) {
        $ext = pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION);
        $icon_name = "icon_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $upload_dir . $icon_name)) {
            if (!empty($_POST['old_icon']) && file_exists("../" . $_POST['old_icon'])) unlink("../" . $_POST['old_icon']);
            $icon_path = "assets/img/departments/" . $icon_name;
        }
    }

    // 2. Handle Upload Gambar Utama
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $img_name = "img_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $img_name)) {
            if (!empty($_POST['old_image']) && file_exists("../" . $_POST['old_image'])) unlink("../" . $_POST['old_image']);
            $image_path = "assets/img/departments/" . $img_name;
        }
    }

    if ($id > 0) {
        $sql = "UPDATE departments SET name=?, category=?, icon_path=?, image_path=?, display_order=?, description=?, special_skills=?, additional_info=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssisssi", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $additional_info, $id);
    } else {
        $sql = "INSERT INTO departments (name, category, icon_path, image_path, display_order, description, special_skills, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssisss", $name, $category, $icon_path, $image_path, $display_order, $description, $special_skills, $additional_info);
    }

    if ($stmt->execute()) {
        header("Location: departments.php?status=saved");
        exit();
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
    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
        <h4 class="fw-bold mb-4"><?= $id > 0 ? 'Edit' : 'Tambah'; ?> Departemen</h4>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="old_icon" value="<?= $data['icon_path']; ?>">
            <input type="hidden" name="old_image" value="<?= $data['image_path']; ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen / Layanan</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($data['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keahlian Khusus (Pisahkan dengan koma)</label>
                        <textarea name="special_skills" class="form-control" rows="2"><?= htmlspecialchars($data['special_skills']); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mb-3 p-3 bg-light rounded">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="Poliklinik" <?= $data['category'] == 'Poliklinik' ? 'selected' : ''; ?>>Poliklinik</option>
                            <option value="Layanan" <?= $data['category'] == 'Layanan' ? 'selected' : ''; ?>>Layanan Unggulan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="display_order" class="form-control" value="<?= $data['display_order']; ?>">
                    </div>

                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Ikon (PNG/SVG)</label>
                        <?php if($data['icon_path']): ?>
                            <div class="mb-2"><img src="../<?= $data['icon_path']; ?>" width="40"></div>
                        <?php endif; ?>
                        <input type="file" name="icon_file" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gambar Utama / Foto</label>
                        <?php if($data['image_path']): ?>
                            <div class="mb-2"><img src="../<?= $data['image_path']; ?>" class="img-fluid rounded border"></div>
                        <?php endif; ?>
                        <input type="file" name="image_file" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="departments.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-5" style="background: #8a3033; border: none;">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<?php $mysqli->close(); require_once 'layout/footer.php'; ?>