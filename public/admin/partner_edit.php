<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
$name = $url = $logo_path = "";
$name_err = $logo_err = "";
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validasi Nama Partner
    if (empty(trim($_POST["name"]))) {
        $name_err = "Silakan masukkan nama partner.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    $url = trim($_POST["url"]);
    $logo_path = $_POST['current_logo'] ?? '';

    // 2. Handle Upload Logo Baru
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('partner_') . '.' . $ext;
            $upload_dir = "../assets/img/partners/";
            
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus logo lama jika ada penggantian
                if (!empty($_POST['current_logo']) && file_exists("../" . $_POST['current_logo'])) {
                    unlink("../" . $_POST['current_logo']);
                }
                $logo_path = "assets/img/partners/" . $new_filename;
            } else {
                $logo_err = "Gagal mengunggah logo ke server.";
            }
        } else {
            $logo_err = "Format file tidak valid (Gunakan JPG, PNG, atau WebP).";
        }
    }

    // 3. Simpan ke Database
    if (empty($name_err) && empty($logo_err)) {
        if ($id === 0) {
            $sql = "INSERT INTO partners (name, url, logo_path) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sss", $name, $url, $logo_path);
        } else {
            $sql = "UPDATE partners SET name = ?, url = ?, logo_path = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssi", $name, $url, $logo_path, $id);
        }

        if ($stmt->execute()) {
            header("location: partners.php?saved=true");
            exit();
        }
        $stmt->close();
    }
} elseif ($id > 0) {
    // Mode EDIT: Ambil data awal
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

require_once 'layout/header.php';
$page_title_text = ($id === 0) ? "Tambah Partner Baru" : "Edit Partner";
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

    /* Tombol Utama Gradasi JHC */
    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 35px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 25px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 120px; object-fit: contain; }
    
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Kelola logo instansi, asuransi, atau perusahaan rekanan RS JHC.</p>
            </div>
            <a href="partners.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_logo" value="<?php echo htmlspecialchars($logo_path); ?>">
            
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Nama Instansi / Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($name); ?>" placeholder="Contoh: BPJS Kesehatan, PT. Telkom">
                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Alamat Website (URL)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-link"></i></span>
                            <input type="text" name="url" class="form-control form-control-lg border-start-0" 
                                   value="<?php echo htmlspecialchars($url); ?>" placeholder="https://www.rekanan.com">
                        </div>
                        <div class="form-text mt-2 small">Opsional: Tautan ini akan aktif jika logo partner diklik oleh pengunjung.</div>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Logo Partner</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($logo_path)): ?>
                            <img src="../<?php echo htmlspecialchars($logo_path); ?>" alt="Partner Logo">
                            <p class="small text-muted mt-3 mb-0">Logo Aktif</p>
                        <?php else: ?>
                            <div class="py-4 text-muted">
                                <i class="fas fa-handshake fa-4x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada logo diunggah</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Pilih File Logo Baru:</label>
                        <input type="file" name="logo" class="form-control form-control-sm shadow-sm <?php echo (!empty($logo_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $logo_err; ?></div>
                        <div class="form-text x-small mt-2">Format: PNG transparan disarankan. Maks: 2MB.</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan Partner
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>