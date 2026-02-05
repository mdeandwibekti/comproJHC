<?php
require_once "../../config.php";

// --- LOGIKA PHP DILETAKKAN DI ATAS SEBELUM HEADER.PHP ---

// Inisialisasi Variabel
$name = $url = $logo_path = "";
$name_err = $logo_err = "";
$page_title = "Add New Partner";
$id = 0;

// Cek Mode Edit (Ambil ID dari URL)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Partner";
}

// --- PROSES SUBMIT (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    // 1. Validasi Nama
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    $url = trim($_POST["url"]);
    $logo_path = $_POST['current_logo'] ?? '';

    // 2. Handle Upload Logo
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES["logo"]["name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '-partner.' . $ext;
            // Pastikan path upload benar (Naik satu folder dari 'admin' ke 'public', lalu ke 'assets')
            $upload_dir = "../assets/img/partners/";
            
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_path)) {
                // Hapus logo lama jika ada
                if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                    unlink("../" . $logo_path);
                }
                $logo_path = "assets/img/partners/" . $new_filename;
            } else {
                $logo_err = "Gagal mengupload gambar.";
            }
        } else {
            $logo_err = "Format file tidak valid (Hanya JPG, PNG, GIF).";
        }
    }

    // 3. Simpan ke Database
    if (empty($name_err) && empty($logo_err)) {
        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO partners (name, url, logo_path) VALUES (?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sss", $name, $url, $logo_path);
            }
        } else {
            // UPDATE
            $sql = "UPDATE partners SET name = ?, url = ?, logo_path = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sssi", $name, $url, $logo_path, $id);
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                // REDIRECT BERHASIL (Dilakukan sebelum ada HTML yang dicetak)
                header("location: partners.php?saved=true");
                exit();
            } else {
                $db_error = "Error SQL: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// --- AMBIL DATA UNTUK FORM EDIT (GET) ---
// Hanya jalankan jika bukan POST request (agar inputan user tidak tertimpa saat error)
if ($_SERVER["REQUEST_METHOD"] != "POST" && !empty($id)) {
    $sql = "SELECT name, url, logo_path FROM partners WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $url, $logo_path);
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
    .img-preview-box img { max-height: 150px; object-fit: contain; }

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
        <p class="text-muted mb-0 small">Add or update corporate partner information.</p>
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
                            <label class="form-label">Partner Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. Asuransi Kesehatan Jaya">
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Website URL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                                <input type="text" name="url" class="form-control" value="<?php echo htmlspecialchars($url); ?>" placeholder="https://example.com">
                            </div>
                            <small class="text-muted">Optional. Enter the full URL starting with https://</small>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="text-muted fw-bold mb-3">Partner Logo</h6>
                        
                        <div class="mb-3">
                            <div class="img-preview-box w-100">
                                <?php if(!empty($logo_path)): ?>
                                    <img src="../<?php echo htmlspecialchars($logo_path); ?>" alt="Logo Preview">
                                <?php else: ?>
                                    <div class="py-4">
                                        <i class="fas fa-image fa-3x text-muted mb-2 opacity-50"></i><br>
                                        <span class="text-muted small">No logo uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="current_logo" value="<?php echo htmlspecialchars($logo_path); ?>">
                            
                            <label class="form-label small mt-2">Upload New Logo</label>
                            <input type="file" name="logo" class="form-control form-control-sm <?php echo (!empty($logo_err)) ? 'is-invalid' : ''; ?>">
                            <div class="invalid-feedback"><?php echo $logo_err; ?></div>
                            <div class="form-text small">Recommended size: 200x100 pixels (PNG/JPG). Transparent background preferred.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="partners.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i> Save Partner
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