<?php
require_once "../../config.php";

<<<<<<< HEAD
// --- LOGIKA PROSES DILETAKKAN DI PALING ATAS (SEBELUM HEADER) ---

// Inisialisasi Variabel
=======
// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
$title = $description = $image_path = "";
$price = 0;
$display_order = 0;
$title_err = $price_err = $image_err = "";
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

<<<<<<< HEAD
// Cek Mode Edit (Ambil ID dari URL)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit MCU Package";
}

// --- PROSES SUBMIT (POST) ---
=======
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
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

<<<<<<< HEAD
    // Bersihkan format harga (hapus Rp, titik, koma)
    $clean_price = str_replace(['Rp', '.', ','], '', $_POST["price"]);
    if (is_numeric($clean_price)) {
        $price = $clean_price;
=======
    // 2. Bersihkan & Validasi Harga
    $clean_price = preg_replace('/[^0-9]/', '', $_POST["price"]);
    if (empty($clean_price)) {
        $price_err = "Silakan masukkan harga paket.";
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
    } else {
        $price = $clean_price;
    }

    // 3. Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
<<<<<<< HEAD
            $new_filename = uniqid() . '-mcu.' . $file_ext;
            // Path Upload (Folder 'assets' di luar 'admin')
            $upload_dir = "../assets/img/mcu_packages/";
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
=======
            $new_filename = uniqid('mcu_') . '.' . $file_ext;
            $upload_dir = "../assets/img/mcu_packages/";
            
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus gambar lama jika ada penggantian
                if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                    unlink("../" . $_POST['current_image']);
                }
                $image_path = "assets/img/mcu_packages/" . $new_filename;
            } else {
<<<<<<< HEAD
                $image_err = "Gagal mengupload gambar.";
=======
                $image_err = "Gagal mengunggah gambar.";
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
            }
        } else {
            $image_err = "Format file tidak didukung (Gunakan JPG/PNG/WebP).";
        }
<<<<<<< HEAD
    } 
    // Jika Mode Tambah Baru tapi tidak ada gambar
    else if (empty($id) && empty($image_path)) {
        $image_err = "Wajib upload gambar untuk paket baru.";
=======
    } else if (empty($id) && empty($image_path)) {
        $image_err = "Wajib mengunggah gambar untuk paket baru.";
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
    }

    // 4. Simpan ke Database
    if (empty($title_err) && empty($price_err) && empty($image_err)) {
        if ($id === 0) {
            $sql = "INSERT INTO mcu_packages (title, description, price, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
<<<<<<< HEAD
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssdis", $title, $description, $price, $display_order, $image_path);
            }
=======
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssdis", $title, $description, $price, $display_order, $image_path);
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
        } else {
            $sql = "UPDATE mcu_packages SET title=?, description=?, price=?, display_order=?, image_path=? WHERE id=?";
<<<<<<< HEAD
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssdisi", $title, $description, $price, $display_order, $image_path, $id);
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                // REDIRECT BERHASIL (Aman karena belum ada HTML)
                header("location: mcu_packages.php?saved=true");
                exit();
            } else {
                $db_error = "Error Execute SQL: " . $stmt->error;
            }
            $stmt->close();
=======
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssdisi", $title, $description, $price, $display_order, $image_path, $id);
        }

        if ($stmt->execute()) {
            header("location: mcu_packages.php?saved=true");
            exit();
        } else {
            $db_err = "Database error: " . $stmt->error;
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
        }
        $stmt->close();
    }
<<<<<<< HEAD
}

// --- AMBIL DATA UNTUK EDIT (GET) ---
if ($_SERVER["REQUEST_METHOD"] != "POST" && !empty($id)) {
=======
} elseif ($id > 0) {
    // Ambil data untuk mode EDIT
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
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

<<<<<<< HEAD
// --- BARU PANGGIL HEADER DI SINI ---
require_once 'layout/header.php';
?>

<style>
    :root { --primary-red: #D32F2F; }
    
    .page-header {
        background: white; padding: 1.5rem; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }
    
    .main-card {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .form-control:focus {
        border-color: var(--primary-red);
        box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.15);
    }
    
    .form-label { font-weight: 600; color: #444; margin-bottom: 0.5rem; }
    
    .img-preview-box {
        background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;
        padding: 15px; text-align: center; margin-bottom: 10px;
        transition: all 0.3s;
    }
    .img-preview-box:hover { border-color: var(--primary-red); background: #fff5f5; }
    .img-preview-box img { max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 8px; }

    .btn-save {
        background-color: var(--primary-red); border: none; color: white;
        padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; transition: 0.3s;
    }
    .btn-save:hover { background-color: #b71c1c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3); }
    
    .btn-cancel {
        background-color: #eff2f5; color: #5e6278; border: none;
        padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none;
    }
    .btn-cancel:hover { background-color: #e9ecef; color: #333; }
</style>

<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="mb-1 text-dark fw-bold">
            <i class="fas <?php echo ($id ? 'fa-edit' : 'fa-plus'); ?> me-2 text-danger"></i> 
            <?php echo $page_title; ?>
        </h3>
        <p class="text-muted mb-0 small">Create or update Medical Check Up package details.</p>
    </div>

    <?php if(isset($db_error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $db_error; ?></div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-4">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>" placeholder="e.g. Paket Basic, Paket Executive" required>
                            <div class="invalid-feedback"><?php echo $title_err; ?></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">Rp</span>
                                        <input type="text" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(number_format((float)$price, 0, ',', '.')); ?>" placeholder="500.000" required>
                                    </div>
                                    <div class="invalid-feedback d-block small"><?php echo $price_err; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description / Benefits</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="List benefits or details of the package..."><?php echo htmlspecialchars($description); ?></textarea>
                            <div class="form-text small">You can list features like: "Consultation, Lab Test, X-Ray", etc.</div>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="text-muted fw-bold mb-3">Package Image</h6>
                        
                        <div class="mb-3">
                            <div class="img-preview-box w-100">
                                <?php if(!empty($image_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="Package Image">
                                <?php else: ?>
                                    <div class="py-5 text-muted">
                                        <i class="fas fa-image fa-3x mb-2 opacity-50"></i><br>
                                        <span class="small">No image uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                            
                            <label class="form-label small mt-2">Upload New Image</label>
                            <input type="file" name="image" class="form-control form-control-sm <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                            <div class="invalid-feedback"><?php echo $image_err; ?></div>
                            <div class="form-text small">Recommended size: Square or Landscape (e.g., 800x600px).</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="mcu_packages.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i> Save Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>
=======
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
>>>>>>> ded37e853b3e87e529866fe41612853b0c724fef
