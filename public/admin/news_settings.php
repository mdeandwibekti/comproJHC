<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Fetch all settings from table 'settings'
$settings = [];
$result = $mysqli->query("SELECT * FROM settings");
if ($result) {
    while($row = $result->fetch_assoc()){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tabel 'settings'
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Handle title
        $key = 'news_section_title';
        $value = trim($_POST[$key]);
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

        // Handle image upload
        $current_image_path = $_POST['current_news_section_bg_image'];
        
        if (isset($_FILES["news_section_bg_image"]) && $_FILES["news_section_bg_image"]["error"] == 0) {
            $new_filename = uniqid() . '-' . basename($_FILES["news_section_bg_image"]["name"]);
            $upload_dir = "../assets/img/gallery/"; 
            
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            if (move_uploaded_file($_FILES["news_section_bg_image"]["tmp_name"], $upload_dir . $new_filename)) {
                $current_image_path = "assets/img/gallery/" . $new_filename;
            }
        }
        
        $key = 'news_section_bg_image';
        $value = $current_image_path;
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
        
        $stmt->close();
    }
    
    // Redirect ke news_settings.php
    header("location: news_settings.php?saved=true");
    exit();
}
?>

<div class="container-fluid">
    <h3>Edit News Section Settings</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Settings saved successfully.</div>"; ?>
    
    <form action="news_settings.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Section Title</label>
            <input type="text" name="news_section_title" class="form-control" value="<?php echo htmlspecialchars($settings['news_section_title'] ?? ''); ?>">
        </div>
        
        <div class="form-group mt-3">
            <label>Background Image</label><br>
            <?php 
            $bg_image_path = $settings['news_section_bg_image'] ?? '';
            if($bg_image_path): 
            ?>
                <img src="../<?php echo htmlspecialchars($bg_image_path); ?>" width="200" class="img-thumbnail mb-2"><br>
            <?php endif; ?>
            <input type="hidden" name="current_news_section_bg_image" value="<?php echo htmlspecialchars($bg_image_path); ?>">
            <input type="file" name="news_section_bg_image" class="form-control-file">
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save Settings">
        </div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>