<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN POST (Diletakkan SEBELUM output apapun) ---
$background_keys = [
    'hero_background_path' => 'Hero Section',
    'bg_departments_path' => 'Departments Title',
    'bg_about_path' => 'About Us Section',
    'bg_doctors_path' => 'Doctors Title',
    'bg_news_path' => 'News Title',
    'bg_partners_path' => 'Partners Section',
    'bg_contact_path' => 'Contact Section'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
    if ($stmt = $mysqli->prepare($sql)) {
        foreach ($background_keys as $key => $label) {
            $current_path = $_POST['current_' . $key] ?? '';

            if (isset($_FILES[$key]) && $_FILES[$key]["error"] == 0) {
                $new_filename = uniqid() . '-' . basename($_FILES[$key]["name"]);
                // Jalur fisik untuk upload
                $upload_dir = "../assets/img/gallery/"; 
                
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES[$key]["tmp_name"], $upload_dir . $new_filename)) {
                    // Hapus file lama jika ada
                    if (!empty($current_path) && file_exists("../" . $current_path)) {
                        unlink("../" . $current_path);
                    }
                    // Path yang disimpan di database (relative untuk sisi public)
                    $current_path = "assets/img/gallery/" . $new_filename;
                }
            }
            
            $stmt->bind_param("ss", $key, $current_path);
            $stmt->execute();
        }
        $stmt->close();
    }
    // Redirect setelah sukses (Header bisa dikirim karena belum ada HTML yang keluar)
    header("Location: background_settings2.php?saved=true");
    exit();
}

// --- OUTPUT HTML DIMULAI DISINI ---
require_once 'layout/header.php'; 
?>

<style>
    .btn-jhc-save {
        background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-jhc-save:hover {
        opacity: 0.9;
        color: white;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.4);
    }
    .bg-preview {
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Page Background Settings</h3>
        <button type="submit" form="bgForm" class="btn-jhc-save">
            <i class="fas fa-save me-2"></i> Save All Changes
        </button>
    </div>
    <hr>

    <?php if(isset($_GET['saved'])): ?>
        <div class='alert alert-success alert-dismissible fade show'>
            <i class="fas fa-check-circle me-2"></i> Settings saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <form action="background_settings2.php" method="post" enctype="multipart/form-data" id="bgForm">
        <div class="row">
            <?php foreach ($background_keys as $key => $label): ?>
                <?php $current_value = $settings[$key] ?? ''; ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <label class="fw-bold mb-0"><?php echo htmlspecialchars($label); ?></label>
                        </div>
                        <div class="card-body text-center">
                            <?php if($current_value): ?>
                                <img src="../<?php echo htmlspecialchars($current_value); ?>" class="img-fluid bg-preview mb-3 border">
                            <?php else: ?>
                                <div class="bg-light py-4 rounded mb-3 border">
                                    <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                                    <p class="text-muted small mt-2">No image set</p>
                                </div>
                            <?php endif; ?>
                            
                            <input type="hidden" name="current_<?php echo $key; ?>" value="<?php echo htmlspecialchars($current_value); ?>">
                            <div class="input-group input-group-sm">
                                <input type="file" name="<?php echo $key; ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<?php require_once 'layout/footer.php'; ?>