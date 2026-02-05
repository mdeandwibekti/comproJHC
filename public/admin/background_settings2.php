<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN POST (Harus SEBELUM require layout/header.php) ---
$background_keys = [
    'hero_background_path' => 'Hero Section Background',
    'bg_departments_path' => 'Departments Title BG',
    'bg_about_path' => 'About Us Section BG',
    'bg_doctors_path' => 'Doctors Title BG',
    'bg_news_path' => 'News Title BG',
    'bg_partners_path' => 'Partners Section BG',
    'bg_contact_path' => 'Contact Section BG'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
            
    if ($stmt = $mysqli->prepare($sql)) {
        foreach ($background_keys as $key => $label) {
            $current_path = $_POST['current_' . $key] ?? '';

            // Proses Upload Jika Ada File Baru
            if (isset($_FILES[$key]) && $_FILES[$key]["error"] == 0) {
                $upload_dir = "../assets/img/gallery/"; 
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                $file_ext = strtolower(pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid('bg_') . '.' . $file_ext;

                if (move_uploaded_file($_FILES[$key]["tmp_name"], $upload_dir . $new_filename)) {
                    // Hapus file lama untuk efisiensi server
                    if (!empty($current_path) && file_exists("../" . $current_path)) {
                        unlink("../" . $current_path);
                    }
                    $current_path = "assets/img/gallery/" . $new_filename;
                }
            }
            
            $stmt->bind_param("ss", $key, $current_path);
            $stmt->execute();
        }
        $stmt->close();
    }
    header("Location: background_settings2.php?saved=true");
    exit();
}

// Ambil data settings terbaru
$settings = [];
$set_result = $mysqli->query("SELECT * FROM settings2");
if ($set_result) { 
    while($row = $set_result->fetch_assoc()) { 
        $settings[$row['setting_key']] = $row['setting_value']; 
    } 
}

require_once 'layout/header.php'; 
?>

<style>
    :root {
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
        --jhc-red-dark: #8a3033;
    }

    /* Wrapper Neumorphism sesuai image_bf1502.png */
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

    /* Tombol Gradasi Linear 90 derajat */
    .btn-jhc-save {
        background: var(--jhc-gradient);
        border: none;
        color: white !important;
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 700;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }

    .btn-jhc-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4);
        opacity: 0.95;
    }

    .bg-card {
        border: none;
        border-radius: 15px;
        background: #f8f9fa;
        transition: 0.3s;
    }

    .bg-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .bg-preview-box {
        height: 160px;
        border-radius: 10px;
        overflow: hidden;
        background: #eee;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
    }

    .bg-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1">Background Settings</h3>
                <p class="text-muted small mb-0">Kelola gambar latar belakang untuk setiap bagian halaman website.</p>
            </div>
            <button type="submit" form="bgForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
            </button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class='alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4'>
                <i class="fas fa-check-circle me-2"></i> Peraturan berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form action="background_settings2.php" method="post" enctype="multipart/form-data" id="bgForm">
            <div class="row">
                <?php foreach ($background_keys as $key => $label): ?>
                    <?php $current_value = $settings[$key] ?? ''; ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card bg-card h-100">
                            <div class="card-body p-3">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-3"><?php echo $label; ?></label>
                                
                                <div class="bg-preview-box mb-3">
                                    <?php if($current_value): ?>
                                        <img src="../<?php echo htmlspecialchars($current_value); ?>" alt="Preview">
                                    <?php else: ?>
                                        <div class="text-center">
                                            <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                                            <p class="x-small text-muted mt-2 mb-0">Belum ada gambar</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <input type="hidden" name="current_<?php echo $key; ?>" value="<?php echo htmlspecialchars($current_value); ?>">
                                <div class="input-group input-group-sm">
                                    <input type="file" name="<?php echo $key; ?>" class="form-control border-0 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>