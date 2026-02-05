<?php
require_once "../../config.php";
require_once 'layout/header.php';

$settings = [];
$result = $mysqli->query("SELECT * FROM settings");
if ($result) {
    while($row = $result->fetch_assoc()){
        if(isset($row['setting_key']) && isset($row['setting_value'])){
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Save Title
        $key = 'news_section_title';
        $value = trim($_POST['news_section_title'] ?? '');
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

        // Save Image
        $key_img = 'news_section_bg_image';
        $current_image_path = $_POST['current_news_section_bg_image'] ?? '';
        
        if (isset($_FILES["news_section_bg_image"]) && $_FILES["news_section_bg_image"]["error"] == 0) {
            $new_filename = uniqid() . '-bg-' . basename($_FILES["news_section_bg_image"]["name"]);
            $upload_dir = "../assets/img/gallery/"; 
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES["news_section_bg_image"]["tmp_name"], $upload_dir . $new_filename)) {
                $current_image_path = "assets/img/gallery/" . $new_filename;
            }
        }
        
        $stmt->bind_param("ss", $key_img, $current_image_path);
        $stmt->execute();
        $stmt->close();
    }
    
    echo "<script>window.location.href='news_settings.php?saved=true';</script>";
    exit();
}
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
        padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600;
        text-decoration: none; display: inline-block;
    }
    .btn-cancel:hover { background-color: #e9ecef; color: #333; }
</style>

<div class="container-fluid py-4">
    <div class="page-header">
        <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-sliders-h me-2 text-danger"></i> News Section Settings</h3>
        <p class="text-muted mb-0 small">Customize the appearance of the news section on the homepage.</p>
    </div>

    <?php if(isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Settings saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-4">
            <form action="news_settings.php" method="post" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-4">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="news_section_title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($settings['news_section_title'] ?? ''); ?>" placeholder="e.g. Latest News & Articles">
                            <div class="form-text text-muted">This title will appear at the top of the news section on the homepage.</div>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="text-muted fw-bold mb-3">Background Image</h6>
                        
                        <div class="mb-3">
                            <div class="img-preview-box">
                                <?php 
                                $bg_image_path = $settings['news_section_bg_image'] ?? '';
                                if(!empty($bg_image_path)): 
                                ?>
                                    <img src="../<?php echo htmlspecialchars($bg_image_path); ?>" alt="Background Preview">
                                <?php else: ?>
                                    <div class="py-5">
                                        <i class="fas fa-image fa-4x text-muted mb-2 opacity-50"></i><br>
                                        <span class="text-muted small">No background image set</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="current_news_section_bg_image" value="<?php echo htmlspecialchars($bg_image_path); ?>">
                            
                            <label class="form-label small mt-2">Upload New Background</label>
                            <input type="file" name="news_section_bg_image" class="form-control form-control-sm">
                            <div class="form-text small">Recommended: High resolution subtle pattern or image.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="dashboard.php" class="btn btn-cancel">Back to Dashboard</a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>