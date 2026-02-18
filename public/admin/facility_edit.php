<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA (Tetap Sama) ---
$name = $description = $image_path = "";
$display_order = 0;
$category = ""; 
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, description, display_order, image_path FROM facilities WHERE id = ?";
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST["display_order"];
    $image_path = $_POST['current_image'];
    $category = ""; 

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '-facility.' . $file_ext;
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
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Kategori Utama" : "Edit Kategori Utama";
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

    .form-control {
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: #fcfdfe;
    }

    .form-control:focus {
        border-color: var(--jhc-red-dark);
        box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1);
        background-color: #fff;
    }

    /* Button Styling */
    .btn-jhc-save { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 14px; 
        padding: 14px 35px; 
        font-weight: 800; 
        border: none; 
        transition: 0.3s; 
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.25); 
    }

    .btn-jhc-save:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 12px 25px rgba(138, 48, 51, 0.35); 
    }

    .btn-back-jhc {
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
    
    .btn-back-jhc:hover {
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

    .img-preview-box:hover {
        border-color: var(--jhc-red-dark);
        background: #fff;
    }

    .img-preview-box img { 
        max-height: 250px; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        border: 3px solid #fff;
    }

    .alert-info-minimal {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 15px;
        font-size: 0.85rem;
        color: #475569;
        border-left: 4px solid #cbd5e1;
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
            <span class="current"><?php echo empty($id) ? 'Tambah Kategori' : 'Edit Kategori'; ?></span>
        </div>
        <div class="page-header-jhc d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;"><?php echo $page_title_text; ?></h2>
                <p class="text-muted small mb-0">Kelola identitas visual dan deskripsi untuk <b>Kategori Utama</b> fasilitas.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="facilities.php" class="btn-back-jhc">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Nama Kategori Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" placeholder="E.g. Instalasi Gawat Darurat (IGD)" required>
                        <div class="form-text mt-2" style="font-size: 0.8rem;">Nama ini akan muncul sebagai judul utama pada kartu fasilitas di beranda.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Berikan ringkasan mengenai fasilitas ini..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
                            <div class="form-text mt-2" style="font-size: 0.8rem;">Gunakan angka untuk mengatur posisi kartu (1, 2, 3...).</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert-info-minimal">
                            <i class="fas fa-info-circle me-2"></i> <b>Catatan:</b> Kategori utama tidak memiliki "Parent". Jika Anda ingin menambahkan item ke dalam kategori ini, gunakan menu <b>Tambah Sub-Item</b> di halaman utama manajemen.
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label">Gambar Sampul Kategori</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5">
                                <i class="fas fa-cloud-upload-alt fa-4x text-muted opacity-25"></i>
                                <p class="small text-muted mt-3 mb-0 fw-bold">Pratinjau Gambar Utama</p>
                                <p class="x-small text-muted opacity-75">Belum ada file yang diunggah.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                    <div class="p-2">
                        <input type="file" name="image" class="form-control">
                        <div class="mt-3 p-3 rounded-3 bg-light border">
                            <div class="d-flex align-items-center text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-compress-arrows-alt me-2"></i>
                                <span>Rekomendasi: Rasio 4:3 (Min. 800x600px). Format JPG/PNG.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-center text-lg-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-check-circle me-2"></i> Simpan Kategori Utama
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>