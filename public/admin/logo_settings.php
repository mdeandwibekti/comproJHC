<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
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
                
                // Gunakan folder seragam agar mudah diakses
                $upload_dir = "../assets/img/branding/"; 
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $upload_dir . $new_filename)) {
                    if (!empty($_POST['current_' . $db_key]) && file_exists("../" . $_POST['current_' . $db_key])) {
                        unlink("../" . $_POST['current_' . $db_key]);
                    }
                    $path = "assets/img/branding/" . $new_filename;
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

$settings = [];
$result = $mysqli->query("SELECT * FROM settings2");
if ($result) {
    while($row = $result->fetch_assoc()){ $settings[$row['setting_key']] = $row['setting_value']; }
}

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    /* Breadcrumb Styling */
    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; transition: 0.3s; }
    .breadcrumb-jhc a:hover { color: var(--jhc-red-dark); }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .admin-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 40px; border: 1px solid #f1f5f9;
    }

    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }

    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; color: white !important; 
        border-radius: 14px !important; padding: 12px 35px !important; 
        font-weight: 700; border: none !important;
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); transition: 0.3s; 
    }
    .btn-jhc-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); }

    /* Logo Card Visuals */
    .branding-card {
        background: #fcfdfe; border: 2px solid #f1f5f9; border-radius: 20px;
        padding: 25px; transition: 0.3s; height: 100%;
    }
    .branding-card:hover { border-color: var(--jhc-red-dark); background: #fff; }

    .logo-preview-container {
        background: #fff; border: 2px dashed #e2e8f0; border-radius: 15px;
        height: 160px; display: flex; align-items: center; justify-content: center;
        margin-bottom: 20px; overflow: hidden; padding: 15px; position: relative;
    }
    
    .logo-dark-bg { background: #1a202c !important; border-color: #2d3748 !important; }
    .logo-preview-container img { max-height: 100%; max-width: 100%; object-fit: contain; z-index: 2; }
    .bg-label { position: absolute; bottom: 8px; right: 12px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; opacity: 0.3; color: #64748b; }
    .logo-dark-bg .bg-label { color: #fff; opacity: 0.2; }

    .form-control-sm { border-radius: 10px; padding: 8px 12px; border: 2px solid #f1f5f9; }
    .form-control-sm:focus { border-color: var(--jhc-red-dark); box-shadow: none; }
    
    .form-label { font-weight: 800; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <span class="current">Logo & Branding</span>
    </div>

    <div class="admin-wrapper">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;">Logo & Branding</h2>
                <p class="text-muted small mb-0">Kelola identitas visual RS JHC agar tetap konsisten di seluruh platform.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button type="submit" form="logoForm" class="btn btn-jhc-save">
                    <i class="fas fa-sync-alt me-2"></i> Update Branding
                </button>
            </div>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-5 mb-4 p-3 fade show">
                <div class="d-flex align-items-center"><i class="fas fa-check-circle fa-lg me-3"></i> Identitas visual berhasil diperbarui dan disinkronkan.</div>
            </div>
        <?php endif; ?>
        
        <form action="logo_settings.php" method="post" enctype="multipart/form-data" id="logoForm">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="branding-card">
                        <label class="form-label mb-3"><i class="fas fa-window-maximize me-2"></i> Logo Header</label>
                        <div class="logo-preview-container">
                            <span class="bg-label">Light Background</span>
                            <?php $h_logo = $settings['header_logo_path'] ?? ''; ?>
                            <?php if(!empty($h_logo)): ?>
                                <img src="../<?= htmlspecialchars($h_logo); ?>?v=<?= time(); ?>">
                            <?php else: ?>
                                <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_header_logo_path" value="<?= htmlspecialchars($h_logo); ?>">
                        <input type="file" name="header_logo" class="form-control form-control-sm">
                        <div class="form-text x-small mt-2">Disarankan: PNG Transparan (Warna Gelap/Full Color).</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="branding-card">
                        <label class="form-label mb-3"><i class="fas fa-window-minimize me-2"></i> Logo Footer</label>
                        <div class="logo-preview-container logo-dark-bg">
                            <span class="bg-label">Dark Background</span>
                            <?php $f_logo = $settings['footer_logo_path'] ?? ''; ?>
                            <?php if(!empty($f_logo)): ?>
                                <img src="../<?= htmlspecialchars($f_logo); ?>?v=<?= time(); ?>">
                            <?php else: ?>
                                <i class="fas fa-image fa-3x text-white opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_footer_logo_path" value="<?= htmlspecialchars($f_logo); ?>">
                        <input type="file" name="footer_logo" class="form-control form-control-sm">
                        <div class="form-text x-small mt-2">Disarankan: Logo versi Putih/Terang (Mono White).</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="branding-card">
                        <label class="form-label mb-3"><i class="fas fa-globe me-2"></i> Favicon Browser</label>
                        <div class="logo-preview-container">
                            <span class="bg-label">Browser Tab</span>
                            <?php $fav = $settings['favicon_path'] ?? ''; ?>
                            <?php if(!empty($fav)): ?>
                                <div class="bg-white p-3 border rounded-circle shadow-sm" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <img src="../<?= htmlspecialchars($fav); ?>?v=<?= time(); ?>" style="width: 48px; height: 48px;">
                                </div>
                            <?php else: ?>
                                <i class="fas fa-compass fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_favicon_path" value="<?= htmlspecialchars($fav); ?>">
                        <input type="file" name="favicon" class="form-control form-control-sm">
                        <div class="form-text x-small mt-2">Format: .ico atau .png (Ukuran 32x32px atau 64x64px).</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>