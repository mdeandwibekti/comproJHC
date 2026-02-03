<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Fetch all settings for the form
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'popup_%'");
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Save title, content, and status
        $params = [
            'popup_title' => $_POST['popup_title'],
            'popup_content' => $_POST['popup_content'],
            'popup_status' => $_POST['popup_status']
        ];

        foreach ($params as $key => $value) {
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }

        // Handle image upload
        if (isset($_FILES["popup_image"]) && $_FILES["popup_image"]["error"] == 0) {
            $target_dir = __DIR__ . "/../assets/img/popups/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $image_info = getimagesize($_FILES["popup_image"]["tmp_name"]);
            if ($image_info === false) {
                die("File is not an image.");
            }

            $new_filename = uniqid() . '-' . basename($_FILES["popup_image"]["name"]);
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $target_file)) {
                // Delete old image if it exists
                if (!empty($settings['popup_image_path'])) {
                    $old_image_path = __DIR__ . '/../' . $settings['popup_image_path'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }

                // Save new image path to database
                $key = 'popup_image_path';
                $value = 'assets/img/popups/' . $new_filename;
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
        }
        
        $stmt->close();
    }
    $mysqli->close();
    
    header("location: popup_settings.php?saved=true");
    exit();
}
?>
<div class="container-fluid">
    <h3>Promotional Popup Settings</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Settings saved successfully.</div>"; ?>
    <form action="popup_settings.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Popup Title</label>
            <input type="text" name="popup_title" class="form-control" value="<?php echo htmlspecialchars($settings['popup_title'] ?? ''); ?>">
        </div>
        <div class="form-group mt-3">
            <label>Popup Content</label>
            <textarea name="popup_content" class="form-control" rows="5"><?php echo htmlspecialchars($settings['popup_content'] ?? ''); ?></textarea>
        </div>
        <div class="form-group mt-3">
            <label>Popup Image</label>
            <input type="file" name="popup_image" class="form-control">
            <?php if (!empty($settings['popup_image_path'])): ?>
                <div class="mt-2">
                    <small>Current Image:</small><br>
                    <img src="../<?php echo htmlspecialchars($settings['popup_image_path']); ?>" height="100" alt="Popup Image">
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group mt-3">
            <label>Popup Status</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="popup_status" id="status_active" value="active" <?php echo (($settings['popup_status'] ?? '') == 'active') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status_active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="popup_status" id="status_inactive" value="inactive" <?php echo (($settings['popup_status'] ?? 'inactive') == 'inactive') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status_inactive">Inactive</label>
                </div>
            </div>
        </div>
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save Settings">
        </div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>