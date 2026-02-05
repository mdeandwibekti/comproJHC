<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA (Harus SEBELUM require layout/header.php) ---
$name = $description = $image_path = "";
$display_order = 0;
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// 1. Jika Mode Edit, Ambil Data Fasilitas
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

// 2. Proses Simpan Data (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST["display_order"];
    $image_path = $_POST['current_image'];

    // Handle Upload Gambar Baru
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '-facility.' . $file_ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            // Hapus file lama jika ada penggantian untuk menghemat storage
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                unlink("../" . $_POST['current_image']);
            }
            $image_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO facilities (name, description, display_order, image_path) VALUES (?, ?, ?, ?)";
    } else {
        $sql = "UPDATE facilities SET name = ?, description = ?, display_order = ?, image_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("ssis", $name, $description, $display_order, $image_path);
        } else {
            $stmt->bind_param("ssisi", $name, $description, $display_order, $image_path, $id);
        }
        
        if ($stmt->execute()) {
            // Redirect aman menggunakan PHP Header
            header("location: facilities.php?saved=true");
            exit();
        } else {
            $db_error = "Terjadi kesalahan database: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Mulai Tampilan
require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Fasilitas Baru" : "Edit Detail Fasilitas";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
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

    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 30px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 220px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Kelola informasi fasilitas medis, ruangan, dan peralatan unggulan RS JHC.</p>
            </div>
            <a href="facilities.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="facility_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label text-muted small">Nama Fasilitas / Alat <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($name); ?>" placeholder="Contoh: Kamar VIP Guntur, Alat CT Scan..." required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small">Deskripsi Fasilitas</label>
                        <textarea name="description" class="form-control" rows="8" placeholder="Jelaskan detail fasilitas atau keunggulan alat medis ini..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Urutan Tampilan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-sort-numeric-down text-muted"></i></span>
                                <input type="number" name="display_order" class="form-control border-start-0" value="<?php echo htmlspecialchars($display_order); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label text-muted small">Foto / Ilustrasi Fasilitas</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="Facility Image" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-building fa-5x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada foto diunggah</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Pilih File Baru (Rekomendasi: 800x600px):</label>
                        <input type="file" name="image" class="form-control form-control-sm shadow-sm">
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan Fasilitas
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>