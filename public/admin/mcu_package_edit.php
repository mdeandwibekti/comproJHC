<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
$title = $description = $image_path = "";
$price = 0;
$display_order = 0;
$title_err = $price_err = $image_err = "";
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validasi Nama Paket
    if (empty(trim($_POST["title"]))) {
        $title_err = "Silakan masukkan nama paket.";
    } else {
        $title = trim($_POST["title"]);
    }

    $description = trim($_POST["description"]);
    $display_order = (int)$_POST['display_order']; 
    $image_path = $_POST['current_image'] ?? '';

    // 2. Bersihkan & Validasi Harga
    $clean_price = preg_replace('/[^0-9]/', '', $_POST["price"]);
    if (empty($clean_price)) {
        $price_err = "Silakan masukkan harga paket.";
    } else {
        $price = $clean_price;
    }

    // 3. Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('mcu_') . '.' . $file_ext;
            $upload_dir = "../assets/img/mcu_packages/";
            
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus gambar lama jika ada penggantian
                if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                    unlink("../" . $_POST['current_image']);
                }
                $image_path = "assets/img/mcu_packages/" . $new_filename;
            } else {
                $image_err = "Gagal mengunggah gambar.";
            }
        } else {
            $image_err = "Format file tidak didukung (Gunakan JPG/PNG/WebP).";
        }
    } else if (empty($id) && empty($image_path)) {
        $image_err = "Wajib mengunggah gambar untuk paket baru.";
    }

    // 4. Simpan ke Database
    if (empty($title_err) && empty($price_err) && empty($image_err)) {
        if ($id === 0) {
            $sql = "INSERT INTO mcu_packages (title, description, price, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssdis", $title, $description, $price, $display_order, $image_path);
        } else {
            $sql = "UPDATE mcu_packages SET title=?, description=?, price=?, display_order=?, image_path=? WHERE id=?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssdisi", $title, $description, $price, $display_order, $image_path, $id);
        }

        if ($stmt->execute()) {
            header("location: mcu_packages.php?saved=true");
            exit();
        } else {
            $db_err = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
} elseif ($id > 0) {
    // Ambil data untuk mode EDIT
    $sql = "SELECT title, description, price, display_order, image_path FROM mcu_packages WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($title, $description, $price, $display_order, $image_path);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = ($id === 0) ? "Tambah Paket MCU" : "Edit Paket MCU";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    .main-wrapper {
        background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05);
    }
    .page-header-jhc { border-left: 5px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }
    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 35px; font-weight: 700; border: none; 
        transition: 0.3s; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }
    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 200px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Kelola paket Medical Check Up untuk ditampilkan di halaman layanan pelanggan.</p>
            </div>
            <a href="mcu_packages.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <?php if(isset($db_err)): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4"><?php echo $db_err; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">

            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Nama Paket MCU <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($title); ?>" placeholder="Contoh: Paket MCU Eksekutif">
                        <div class="invalid-feedback"><?php echo $title_err; ?></div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Paket (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                                <input type="text" name="price" class="form-control form-control-lg <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo number_format((float)$price, 0, ',', '.'); ?>" placeholder="0">
                            </div>
                            <div class="text-danger small mt-1"><?php echo $price_err; ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="display_order" class="form-control form-control-lg" value="<?php echo $display_order; ?>">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Deskripsi & Item Pemeriksaan</label>
                        <textarea name="description" class="form-control" rows="8" 
                                  placeholder="Tuliskan item pemeriksaan (misal: Darah Lengkap, EKG, Rontgen Thorax...)"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Gambar Banner Paket</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-file-medical fa-5x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada gambar diunggah</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Unggah File Baru:</label>
                        <input type="file" name="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $image_err; ?></div>
                        <div class="form-text x-small mt-2">Format: JPG, PNG, WebP. Maks: 5MB.</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Paket MCU
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>