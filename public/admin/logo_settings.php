<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Menambahkan 'header_logo_2' ke dalam daftar proses
        $keys_to_process = [
            'header_logo'   => 'header_logo_path',
            'header_logo_2' => 'header_logo_path_2', // Kunci baru untuk logo kedua
            'footer_logo'   => 'footer_logo_path',
            'favicon'       => 'favicon_path'
        ];

        foreach ($keys_to_process as $file_key => $db_key) {
            $path = $_POST['current_' . $db_key] ?? '';

            if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] == 0) {
                $ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid($file_key . '-') . '.' . $ext;
                
                $upload_dir = "../assets/img/branding/"; 
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $upload_dir . $new_filename)) {
                    // Hapus file lama jika ada
                    if (!empty($_POST['current_' . $db_key]) && file_exists("../" . $_POST['current_' . $db_key])) {
                        @unlink("../" . $_POST['current_' . $db_key]);
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

<style>
    :root { --jhc-red-dark: #8a3033; --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); }
    body { background-color: #f8fafb !important; font-family: 'Inter', sans-serif; }
    .admin-wrapper { background: #ffffff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); padding: 40px; border: 1px solid #f1f5f9; }
    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }
    .btn-jhc-save { background: var(--jhc-gradient) !important; color: white !important; border-radius: 14px !important; padding: 12px 35px !important; font-weight: 700; border: none !important; box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); transition: 0.3s; }
    .branding-card { background: #fcfdfe; border: 2px solid #f1f5f9; border-radius: 20px; padding: 25px; transition: 0.3s; height: 100%; }
    .branding-card:hover { border-color: var(--jhc-red-dark); }
    .logo-preview-container { background: #fff; border: 2px dashed #e2e8f0; border-radius: 15px; height: 140px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; overflow: hidden; padding: 10px; position: relative; }
    .logo-preview-container img { max-height: 100%; max-width: 100%; object-fit: contain; }
    .bg-label { position: absolute; bottom: 5px; right: 10px; font-size: 0.55rem; font-weight: 800; text-transform: uppercase; opacity: 0.3; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <span class="current">Logo Settings</span>
    </div>
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h2 style="font-weight: 800; letter-spacing: -1px;">Logo & Branding</h2>
                <p class="text-muted small mb-0">Kelola identitas visual (Dukungan Dual Logo Header).</p>
            </div>
            <button type="submit" form="logoForm" class="btn btn-jhc-save">Simpan Perubahan</button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">Branding berhasil diperbarui.</div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data" id="logoForm">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="branding-card">
                        <label class="form-label mb-2">Logo Header Utama</label>
                        <div class="logo-preview-container">
                            <?php $h1 = $settings['header_logo_path'] ?? ''; ?>
                            <img src="../<?= $h1 ?: 'assets/img/gallery/logo.png'; ?>?v=<?= time(); ?>">
                        </div>
                        <input type="hidden" name="current_header_logo_path" value="<?= $h1; ?>">
                        <input type="file" name="header_logo" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="branding-card">
                        <label class="form-label mb-2">Logo Header Kedua</label>
                        <div class="logo-preview-container">
                            <?php $h2 = $settings['header_logo_path_2'] ?? ''; ?>
                            <?php if($h2): ?>
                                <img src="../<?= $h2; ?>?v=<?= time(); ?>">
                            <?php else: ?>
                                <i class="fas fa-plus fa-2x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="current_header_logo_path_2" value="<?= $h2; ?>">
                        <input type="file" name="header_logo_2" class="form-control form-control-sm">
                        <div class="form-text x-small mt-1" style="font-size: 10px;">Contoh: Logo Akreditasi KARS / Grup.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="branding-card">
                        <label class="form-label mb-2">Logo Footer</label>
                        <div class="logo-preview-container bg-dark">
                            <?php $fl = $settings['footer_logo_path'] ?? ''; ?>
                            <img src="../<?= $fl ?: 'assets/img/gallery/logo.png'; ?>?v=<?= time(); ?>">
                        </div>
                        <input type="hidden" name="current_footer_logo_path" value="<?= $fl; ?>">
                        <input type="file" name="footer_logo" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="branding-card">
                        <label class="form-label mb-2">Favicon Browser</label>
                        <div class="logo-preview-container">
                            <?php $fv = $settings['favicon_path'] ?? ''; ?>
                            <img src="../<?= $fv ?: 'assets/img/favicons/favicon.ico'; ?>?v=<?= time(); ?>" style="width: 48px;">
                        </div>
                        <input type="hidden" name="current_favicon_path" value="<?= $fv; ?>">
                        <input type="file" name="favicon" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>