<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Fetch all settings
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2");
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
    if ($stmt = $mysqli->prepare($sql)) {
        // Header Logo
        $header_logo_path = $_POST['current_header_logo_path'];
        if (isset($_FILES["header_logo"]) && $_FILES["header_logo"]["error"] == 0) {
            $new_filename = uniqid() . '-' . basename($_FILES["header_logo"]["name"]);
            $upload_dir = "../assets/img/gallery/"; // Assuming gallery is suitable for logos
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES["header_logo"]["tmp_name"], $upload_dir . $new_filename)) {
                $header_logo_path = "assets/img/gallery/" . $new_filename;
            }
        }
        $key = 'header_logo_path';
        $value = $header_logo_path;
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

        // Footer Logo
        $footer_logo_path = $_POST['current_footer_logo_path'];
        if (isset($_FILES["footer_logo"]) && $_FILES["footer_logo"]["error"] == 0) {
            $new_filename = uniqid() . '-' . basename($_FILES["footer_logo"]["name"]);
            $upload_dir = "../assets/img/gallery/"; // Assuming gallery is suitable for logos
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES["footer_logo"]["tmp_name"], $upload_dir . $new_filename)) {
                $footer_logo_path = "assets/img/gallery/" . $new_filename;
            }
        }
        $key = 'footer_logo_path';
        $value = $footer_logo_path;
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

        // Favicon
        $favicon_path = $_POST['current_favicon_path'];
        if (isset($_FILES["favicon"]) && $_FILES["favicon"]["error"] == 0) {
            $new_filename = uniqid() . '-' . basename($_FILES["favicon"]["name"]);
            $upload_dir = "../assets/img/favicons/";
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES["favicon"]["tmp_name"], $upload_dir . $new_filename)) {
                $favicon_path = "assets/img/favicons/" . $new_filename;
            }
        }
        $key = 'favicon_path';
        $value = $favicon_path;
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();

    }
    header("location: logo_settings.php?saved=true");
    exit();
}
?>
<div class="container-fluid">
    <h3>Logo Settings</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Logo settings saved successfully.</div>"; ?>
    <form action="logo_settings.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Header Logo</label><br>
            <?php 
            $header_logo_path = $settings['header_logo_path'] ?? '';
            if($header_logo_path): 
            ?><img src="/comprojhc/public/<?php echo htmlspecialchars($header_logo_path); ?>" width="150" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_header_logo_path" value="<?php echo htmlspecialchars($header_logo_path); ?>">
            <input type="file" name="header_logo" class="form-control-file">
        </div>
        
        <div class="form-group mt-4">
            <label>Footer Logo</label><br>
            <?php 
            $footer_logo_path = $settings['footer_logo_path'] ?? '';
            if($footer_logo_path): 
            ?><img src="/comprojhc/public/<?php echo htmlspecialchars($footer_logo_path); ?>" width="150" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_footer_logo_path" value="<?php echo htmlspecialchars($footer_logo_path); ?>">
            <input type="file" name="footer_logo" class="form-control-file">
        </div>

        <div class="form-group mt-4">
            <label>Favicon</label><br>
            <?php 
            $favicon_path = $settings['favicon_path'] ?? '';
            if($favicon_path): 
            ?><img src="/comprojhc/public/<?php echo htmlspecialchars($favicon_path); ?>" width="32" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_favicon_path" value="<?php echo htmlspecialchars($favicon_path); ?>">
            <input type="file" name="favicon" class="form-control-file">
            <small class="form-text text-muted">Upload a .png or .ico file. Recommended size: 32x32 pixels.</small>
        </div>

        <div class="form-group mt-4"><input type="submit" class="btn btn-primary" value="Save Settings"></div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>