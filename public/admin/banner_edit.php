<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA (Harus SEBELUM require layout/header.php) ---
$title = $description = $image_path = "";
$display_order = 0;
$title_err = $image_err = "";
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi Judul
    if (empty(trim($_POST["title"]))) {
        $title_err = "Silakan masukkan judul banner.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST['display_order'];
    $image_path = $_POST['current_image'];

    // Handle Upload Gambar Baru
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/banners/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid('banner_') . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            // Hapus file lama jika ada penggantian
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                unlink("../" . $_POST['current_image']);
            }
            $image_path = "assets/img/banners/" . $new_filename;
        } else {
            $image_err = "Gagal mengunggah gambar.";
        }
    } else if (empty($image_path) && empty($id)) { 
        $image_err = "Harap pilih gambar untuk banner baru.";
    }

    // Simpan ke Database
    if (empty($title_err) && empty($image_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO banners (image_path, title, description, display_order) VALUES (?, ?, ?, ?)";
        } else {
            $sql = "UPDATE banners SET image_path = ?, title = ?, description = ?, display_order = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssi", $image_path, $title, $description, $display_order);
            } else {
                $stmt->bind_param("sssii", $image_path, $title, $description, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                header("location: banners.php?saved=true");
                exit();
            }
            $stmt->close();
        }
    }
} elseif (!empty($id)) {
    // Ambil data untuk mode EDIT
    $sql = "SELECT image_path, title, description, display_order FROM banners WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($image_path, $title, $description, $display_order);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Banner Baru" : "Edit Banner";
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
        border-left: 4px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    .btn-jhc-save { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 50px; 
        padding: 10px 35px; 
        font-weight: 700; 
        border: none; 
        transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-container {
        border: 2px dashed #ddd;
        border-radius: 15px;
        padding: 15px;
        background: #fdfdfd;
        text-align: center;
    }
    .img-preview-container img {
        max-height: 250px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Atur konten gambar dan teks untuk slider utama di halaman depan.</p>
            </div>
            <a href="banners.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="banner_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted">Judul Banner <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($title); ?>" placeholder="Masukkan judul utama banner...">
                        <div class="invalid-feedback"><?php echo $title_err; ?></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted">Deskripsi Pendek</label>
                        <textarea name="description" class="form-control" rows="6" 
                                  placeholder="Tuliskan penjelasan singkat yang muncul di bawah judul..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Urutan Tampilan</label>
                        <div class="input-group" style="max-width: 200px;">
                            <span class="input-group-text bg-light"><i class="fas fa-sort-numeric-down"></i></span>
                            <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
                        </div>
                        <small class="text-muted">Angka lebih kecil akan muncul lebih awal.</small>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label fw-bold text-muted">Gambar Banner</label>
                    <div class="img-preview-container mb-3">
                        <?php if($image_path): ?>
                            <img src="../<?php echo $image_path; ?>" class="img-fluid mb-2">
                            <p class="small text-muted mt-2">Gambar saat ini aktif</p>
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-images fa-4x mb-3 opacity-25"></i><br>Belum ada gambar
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Pilih File Baru (Rekomendasi: 1920x1080px)</label>
                        <input type="file" name="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $image_err; ?></div>
                        <div class="form-text x-small mt-2">Format: JPG, JPEG, PNG. Maks: 5MB.</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-save me-2"></i> Simpan Banner
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>