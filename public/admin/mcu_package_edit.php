<?php
require_once "../../config.php";

// --- LOGIKA PROSES DILETAKKAN DI PALING ATAS (SEBELUM HEADER) ---

// Inisialisasi Variabel
$title = $description = $image_path = "";
$price = 0;
$display_order = 0;
$title_err = $price_err = $image_err = "";
$page_title = "Add New MCU Package";
$id = 0;

// Cek Mode Edit (Ambil ID dari URL)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit MCU Package";
}

// --- PROSES SUBMIT (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    // 1. Validasi Input
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }

    $description = trim($_POST["description"]);
    $display_order = (int)$_POST['display_order']; 
    $image_path = $_POST['current_image'] ?? '';

    // Bersihkan format harga (hapus Rp, titik, koma)
    $clean_price = str_replace(['Rp', '.', ','], '', $_POST["price"]);
    if (is_numeric($clean_price)) {
        $price = $clean_price;
    } else {
        $price_err = "Invalid price format. Use numbers only.";
    }

    // 2. Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES["image"]["name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid() . '-mcu.' . $file_ext;
            // Path Upload (Folder 'assets' di luar 'admin')
            $upload_dir = "../assets/img/mcu_packages/";
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                $image_path = "assets/img/mcu_packages/" . $new_filename;
            } else {
                $image_err = "Gagal mengupload gambar.";
            }
        } else {
            $image_err = "Tipe file tidak valid. Hanya JPG, PNG, GIF diperbolehkan.";
        }
    } 
    // Jika Mode Tambah Baru tapi tidak ada gambar
    else if (empty($id) && empty($image_path)) {
        $image_err = "Wajib upload gambar untuk paket baru.";
    }

    // 3. Eksekusi Query
    if (empty($title_err) && empty($price_err) && empty($image_err)) {
        
        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO mcu_packages (title, description, price, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssdis", $title, $description, $price, $display_order, $image_path);
            }
        } else {
            // UPDATE
            $sql = "UPDATE mcu_packages SET title=?, description=?, price=?, display_order=?, image_path=? WHERE id=?";
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
        }
    }
}

// --- AMBIL DATA UNTUK EDIT (GET) ---
if ($_SERVER["REQUEST_METHOD"] != "POST" && !empty($id)) {
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