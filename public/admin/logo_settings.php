<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Wajib SEBELUM require layout/header.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $keys_to_process = [
            'header_logo' => 'header_logo_path',
            'footer_logo' => 'footer_logo_path',
            'favicon'     => 'favicon_path'
        ];

        foreach ($keys_to_process as $file_key => $db_key) {
            $path = $_POST['current_' . $db_key] ?? '';

            if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] == 0) {
                $ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid($file_key . '-') . '.' . $ext;
                $upload_dir = ($file_key == 'favicon') ? "../assets/img/favicons/" : "../assets/img/gallery/";

                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $upload_dir . $new_filename)) {
                    // Hapus file lama jika ada penggantian
                    if (!empty($_POST['current_' . $db_key]) && file_exists("../" . $_POST['current_' . $db_key])) {
                        unlink("../" . $_POST['current_' . $db_key]);
                    }
                    $path = str_replace('../', '', $upload_dir) . $new_filename;
                }
            }
            $stmt->bind_param("ss", $db_key, $path);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    header("location: logo_settings.php?saved=true");
    exit();
}

// Ambil data settings terbaru
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2");
if ($result) {
    while($row = $result->fetch_assoc()){ $settings[$row['setting_key']] = $row['setting_value']; }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .manage-header {
        border-left: 4px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Logo Preview Box */
    .logo-preview-container {
        background: #f8f9fa;
        border: 2px dashed #ddd;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        margin-bottom: 15px;
        transition: 0.3s;
    }
    
    /* Area abu-abu gelap khusus logo transparan putih */
    .logo-dark-bg { background: #333 !important; }

    .logo-preview-container img { max-height: 80px; object-fit: contain; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 35px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Logo & Branding Settings</h3>
                <p class="text-muted small mb-0">Kelola identitas visual utama Rumah Sakit JHC (Header, Footer, dan Favicon).</p>
            </div>
            <button type="submit" form="logoForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Perubahan
            </button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Pengaturan branding berhasil diperbarui!
            </div>
        <?php endif; ?>
        
        <form action="logo_settings.php" method="post" enctype="multipart/form-data" id="logoForm">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-none bg-light bg-opacity-50 rounded-4 p-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-3">Logo Header</label>
                        <div class="logo-preview-container">
                            <?php $h_logo = $settings['header_logo_path'] ?? ''; ?>
                            <?php if(!empty($h_logo)): ?>
                                <img src="../<?= htmlspecialchars($h_logo); ?>" alt="Header Logo">
                            <?php else: ?>
                                <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_header_logo_path" value="<?= htmlspecialchars($h_logo); ?>">
                        <input type="file" name="header_logo" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2 text-muted">Format: PNG transparan disarankan.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-none bg-light bg-opacity-50 rounded-4 p-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-3">Logo Footer</label>
                        <div class="logo-preview-container logo-dark-bg">
                            <?php $f_logo = $settings['footer_logo_path'] ?? ''; ?>
                            <?php if(!empty($f_logo)): ?>
                                <img src="../<?= htmlspecialchars($f_logo); ?>" alt="Footer Logo">
                            <?php else: ?>
                                <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_footer_logo_path" value="<?= htmlspecialchars($f_logo); ?>">
                        <input type="file" name="footer_logo" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2 text-muted">Gunakan versi putih/terang jika footer berwarna gelap.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-none bg-light bg-opacity-50 rounded-4 p-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-3">Favicon Browser</label>
                        <div class="logo-preview-container d-flex align-items-center justify-content-center">
                            <?php $fav = $settings['favicon_path'] ?? ''; ?>
                            <?php if(!empty($fav)): ?>
                                <div class="bg-white p-2 border rounded shadow-sm">
                                    <img src="../<?= htmlspecialchars($fav); ?>" style="height: 32px; width: 32px;">
                                </div>
                            <?php else: ?>
                                <i class="fas fa-globe fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_favicon_path" value="<?= htmlspecialchars($fav); ?>">
                        <input type="file" name="favicon" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2 text-muted">Rekomendasi: .ico atau .png (32x32px).</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>