<?php
require_once "../../config.php";

// --- 1. AMBIL NAMA-NAMA DARI FASILITAS UTAMA UNTUK DROPDOWN ---
// Fasilitas Utama adalah yang kolom 'category'-nya kosong/NULL
$existing_categories = [];
$cat_query = "SELECT name FROM facilities WHERE category IS NULL OR category = '' ORDER BY name ASC";
$cat_result = $mysqli->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $existing_categories[] = $row['name'];
    }
}

// --- 2. LOGIKA PEMROSESAN DATA ---
$name = $category = $description = $image_path = "";
$display_order = 0;
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// Ambil data untuk mode EDIT
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, category, description, display_order, image_path FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $name = $row['name'];
                $category = $row['category'];
                $description = $row['description'];
                $image_path = $row['image_path'];
                $display_order = $row['display_order'];
            }
        }
        $stmt->close();
    }
}

// Proses SIMPAN (Insert/Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $category = $_POST["category"]; // Nama Kategori Utama (Misal: Kamar Perawatan)
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST["display_order"];
    $image_path = $_POST['current_image'];

    // Handle Upload Foto
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid('sub_fac_') . '.' . $file_ext;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                @unlink("../" . $_POST['current_image']);
            }
            $image_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO facilities (name, category, description, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE facilities SET name = ?, category = ?, description = ?, display_order = ?, image_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("sssis", $name, $category, $description, $display_order, $image_path);
        } else {
            $stmt->bind_param("sssisi", $name, $category, $description, $display_order, $image_path, $id);
        }
        
        if ($stmt->execute()) {
            header("location: facilities.php?saved=true");
            exit();
        }
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Input Isi Fasilitas" : "Edit Isi Fasilitas";
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033; 
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); 
        --admin-bg: #f8fafb;
    }

    body {
        background-color: var(--admin-bg) !important;
        font-family: 'Inter', sans-serif;
    }

    .main-wrapper { 
        background: #ffffff; 
        border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 45px; 
        margin-top: 20px; 
        border: 1px solid #f1f5f9; 
    }

    .page-header-jhc { 
        border-left: 6px solid var(--jhc-red-dark); 
        padding-left: 24px; 
        margin-bottom: 40px; 
    }

    /* Form Styling */
    .form-label { 
        font-weight: 700; 
        color: #475569; 
        margin-bottom: 0.8rem; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 1px;
    }

    .form-control, .form-select {
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: #fcfdfe;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--jhc-red-dark);
        box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1);
        background-color: #fff;
    }

    /* Button Styling */
    .btn-save { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 14px; 
        padding: 14px 35px; 
        font-weight: 800; 
        border: none; 
        transition: 0.3s; 
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.25); 
    }

    .btn-save:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 12px 25px rgba(138, 48, 51, 0.35); 
    }

    .btn-back {
        background: #ffffff;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-weight: 700;
        padding: 10px 24px;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-back:hover {
        background: #f8fafb;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    /* Image Preview Styling */
    .img-preview-box { 
        background: #fcfdfe; 
        border: 2px dashed #e2e8f0; 
        border-radius: 20px; 
        padding: 30px; 
        text-align: center; 
        transition: 0.3s;
    }

    .img-preview-box img { 
        max-height: 250px; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        border: 3px solid #fff;
    }

    .category-note {
        background: #fff9f0;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.8rem;
        color: #92400e;
        border-left: 4px solid #f59e0b;
        margin-top: 10px;
    }

    .breadcrumb-jhc {
    font-size: 0.9rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    }
    .breadcrumb-jhc a {
        text-decoration: none;
        color: #6c757d; /* Warna abu-abu Dashboard */
        transition: 0.3s;
    }
    .breadcrumb-jhc a:hover {
        color: var(--jhc-red-dark);
    }
    .breadcrumb-jhc .separator {
        color: #dee2e6;
    }
    .breadcrumb-jhc .current {
        color: var(--jhc-red-dark); /* Warna merah JHC */
        font-weight: 700;
    }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="breadcrumb-jhc">
            <a href="dashboard.php">Dashboard</a> 
            <span class="separator">/</span> 
            <a href="facilities.php">Manajemen Fasilitas</a>
            <span class="separator">/</span> 
            <span class="current"><?php echo empty($id) ? 'Tambah Sub-Item' : 'Edit Sub-Item'; ?></span>
        </div>
        <div class="page-header-jhc d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;"><?php echo $page_title_text; ?></h2>
                <p class="text-muted small mb-0">Hubungkan item fasilitas spesifik ke dalam <b>Kategori Utama</b>.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="facilities.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="current_image" value="<?= $image_path; ?>">

            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Hubungkan ke Kategori Utama <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="" disabled <?= empty($category) ? 'selected' : ''; ?>>-- Pilih Kategori Utama --</option>
                            <?php if (!empty($existing_categories)): ?>
                                <?php foreach ($existing_categories as $cat_name): ?>
                                    <option value="<?= htmlspecialchars($cat_name); ?>" <?= ($category == $cat_name) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($cat_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="category-note">
                            <i class="fas fa-info-circle me-1"></i> <b>Penting:</b> Item ini akan dikelompokkan di bawah nama fasilitas utama yang Anda pilih.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nama Item Spesifik <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="E.g. Kamar VVIP Suite" value="<?= htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Fasilitas</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Jelaskan detail, keunggulan, atau kelengkapan fasilitas ini..."><?= htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="display_order" class="form-control" value="<?= $display_order; ?>">
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label">Foto Item Fasilitas</label>
                    <div class="img-preview-box mb-3">
                        <?php if($image_path): ?>
                            <img src="../<?= $image_path; ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5">
                                <i class="fas fa-image fa-4x text-muted opacity-25"></i>
                                <p class="small text-muted mt-3 mb-0 fw-bold">Pratinjau Foto Item</p>
                                <p class="x-small text-muted opacity-75">Belum ada foto yang diunggah.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-2">
                        <input type="file" name="image" class="form-control">
                        <div class="mt-3 p-3 rounded-3 bg-light border">
                            <div class="d-flex align-items-center text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-compress-arrows-alt me-2"></i>
                                <span>Rekomendasi: Format JPG/PNG (Max 5MB).</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-center text-lg-end">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-check-circle me-2"></i> Simpan Perubahan Item
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>