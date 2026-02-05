<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // 1. Simpan Judul Seksi
        $key = 'news_section_title';
        $value = trim($_POST['news_section_title'] ?? '');
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

        // 2. Handle Upload Gambar Latar Belakang
        $db_key_img = 'news_section_bg_image';
        $image_path = $_POST['current_news_section_bg_image'] ?? '';
        
        if (isset($_FILES["news_section_bg_image"]) && $_FILES["news_section_bg_image"]["error"] == 0) {
            $upload_dir = "../assets/img/gallery/"; 
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $file_ext = strtolower(pathinfo($_FILES["news_section_bg_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid('newsbg_') . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES["news_section_bg_image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus file lama jika ada penggantian untuk efisiensi server
                if (!empty($_POST['current_news_section_bg_image']) && file_exists("../" . $_POST['current_news_section_bg_image'])) {
                    unlink("../" . $_POST['current_news_section_bg_image']);
                }
                $image_path = "assets/img/gallery/" . $new_filename;
            }
        }
        
        $stmt->bind_param("ss", $db_key_img, $image_path);
        $stmt->execute();
        $stmt->close();
    }
    
    header("location: news_settings.php?saved=true");
    exit();
}

// Ambil data settings terbaru
$settings = [];
$result = $mysqli->query("SELECT * FROM settings");
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

    /* Wrapper Neumorphism sesuai standar admin JHC */
    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .manage-header {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Tombol Utama Gradasi JHC */
    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; 
        color: white !important; 
        border-radius: 12px !important; 
        padding: 12px 30px !important; 
        font-weight: 700; 
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd;
        border: 2px dashed #ddd;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        transition: 0.3s;
    }
    .img-preview-box img { 
        max-height: 250px; 
        border-radius: 10px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
    }

    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; text-transform: uppercase; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">News Section Settings</h3>
                <p class="text-muted small mb-0">Atur judul dan tampilan latar belakang untuk area berita di halaman depan.</p>
            </div>
            <button type="submit" form="settingsForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Pengaturan
            </button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Pengaturan berita berhasil diperbarui!
            </div>
        <?php endif; ?>

        <form action="news_settings.php" method="post" enctype="multipart/form-data" id="settingsForm">
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Seksi Berita</label>
                        <input type="text" name="news_section_title" class="form-control form-control-lg" 
                               value="<?php echo htmlspecialchars($settings['news_section_title'] ?? ''); ?>" 
                               placeholder="Contoh: Berita & Artikel Kesehatan Terbaru">
                        <div class="form-text mt-2">Judul ini akan muncul paling atas pada bagian berita di homepage.</div>
                    </div>
                </div>

                <div class="col-md-5 border-start">
                    <label class="form-label">Gambar Latar Belakang (Background)</label>
                    <div class="img-preview-box mb-3">
                        <?php 
                        $bg_image = $settings['news_section_bg_image'] ?? '';
                        if(!empty($bg_image)): 
                        ?>
                            <img src="../<?php echo htmlspecialchars($bg_image); ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-image fa-4x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada gambar latar</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="current_news_section_bg_image" value="<?php echo htmlspecialchars($bg_image); ?>">
                    
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Pilih File Baru:</label>
                        <input type="file" name="news_section_bg_image" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2">Rekomendasi: Gambar dengan resolusi tinggi (min. 1920px) atau pola tekstur halus.</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>