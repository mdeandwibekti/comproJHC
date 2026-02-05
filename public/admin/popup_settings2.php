<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // 1. Ambil data lama untuk referensi penghapusan file
        $old_settings = [];
        $res = $mysqli->query("SELECT * FROM settings2 WHERE setting_key = 'popup_image_path'");
        $row_old = $res->fetch_assoc();
        $old_image_path = $row_old['setting_value'] ?? '';

        // 2. Simpan Title, Content, dan Status
        $params = [
            'popup_title'   => $_POST['popup_title'],
            'popup_content' => $_POST['popup_content'],
            'popup_status'  => $_POST['popup_status']
        ];

        foreach ($params as $key => $value) {
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }

        // 3. Handle Upload Gambar
        if (isset($_FILES["popup_image"]) && $_FILES["popup_image"]["error"] == 0) {
            $upload_dir = "../assets/img/popups/";
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $ext = strtolower(pathinfo($_FILES["popup_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid('popup_') . '.' . $ext;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $target_file)) {
                // Hapus file fisik lama jika ada
                if (!empty($old_image_path) && file_exists("../" . $old_image_path)) {
                    unlink("../" . $old_image_path);
                }

                // Update path di database
                $db_path = 'assets/img/popups/' . $new_filename;
                $stmt->bind_param("ss", $k = 'popup_image_path', $db_path);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    header("location: popup_settings2.php?saved=true");
    exit();
}

// Ambil data settings terbaru untuk ditampilkan di form
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'popup_%'");
if ($result) {
    while($row = $result->fetch_assoc()){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
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

    /* Tombol Utama Gradasi JHC */
    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; 
        color: white !important; 
        border-radius: 12px !important; 
        padding: 12px 35px !important; 
        font-weight: 700; 
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 200px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; text-transform: uppercase; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }

    /* Custom Radio Status */
    .status-toggle {
        background: #f8f9fa; padding: 10px 20px; border-radius: 12px; display: inline-block;
    }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Promotional Popup Settings</h3>
                <p class="text-muted small mb-0">Kelola pengumuman atau promo yang muncul secara otomatis saat pengunjung membuka website.</p>
            </div>
            <button type="submit" form="popupForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Pengaturan
            </button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Pengaturan popup berhasil diperbarui!
            </div>
        <?php endif; ?>

        <form action="popup_settings2.php" method="post" enctype="multipart/form-data" id="popupForm">
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Popup</label>
                        <input type="text" name="popup_title" class="form-control form-control-lg" 
                               value="<?php echo htmlspecialchars($settings['popup_title'] ?? ''); ?>" 
                               placeholder="Contoh: Promo Ramadhan Sehat">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Isi Pesan / Konten</label>
                        <textarea name="popup_content" class="form-control" rows="6" 
                                  placeholder="Tuliskan detail pengumuman di sini..."><?php echo htmlspecialchars($settings['popup_content'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label d-block">Status Aktivasi</label>
                        <div class="status-toggle border">
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input" type="radio" name="popup_status" id="status_active" value="active" <?php echo (($settings['popup_status'] ?? '') == 'active') ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold text-success" for="status_active">AKTIF</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="popup_status" id="status_inactive" value="inactive" <?php echo (($settings['popup_status'] ?? 'inactive') == 'inactive') ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold text-muted" for="status_inactive">NON-AKTIF</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 border-start">
                    <label class="form-label">Gambar Popup</label>
                    <div class="img-preview-box mb-3">
                        <?php if (!empty($settings['popup_image_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($settings['popup_image_path']); ?>" alt="Popup Preview" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-bullhorn fa-4x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada gambar promo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Unggah Gambar Baru:</label>
                        <input type="file" name="popup_image" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2">Format: JPG, PNG, WebP. Pastikan rasio gambar proporsional untuk tampilan popup.</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>