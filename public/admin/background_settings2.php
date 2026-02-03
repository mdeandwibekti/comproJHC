<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Settings are already fetched in header.php into the $settings array

// Define the background settings we want to manage
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

            // Check if a file is uploaded for the current key
            if (isset($_FILES[$key]) && $_FILES[$key]["error"] == 0) {
                $new_filename = uniqid() . '-' . basename($_FILES[$key]["name"]);
                $upload_dir = "../assets/img/gallery/"; // A general place for backgrounds
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES[$key]["tmp_name"], $upload_dir . $new_filename)) {
                    // Delete old file if it exists and is different
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
    header("location: background_settings2.php?saved=true");
    exit();
}
?>
<div class="container-fluid">
    <h3>Page Background Settings</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Settings saved successfully.</div>"; ?>
    
    <form action="background_settings2.php" method="post" enctype="multipart/form-data">
        <div class="row">
            <?php foreach ($background_keys as $key => $label): ?>
                <?php $current_value = $settings[$key] ?? ''; ?>
                <div class="col-md-6">
                    <div class="form-group mb-4 p-3 border rounded">
                        <label class="fw-bold"><?php echo htmlspecialchars($label); ?></label><br>
                        <?php if($current_value): ?>
                            <img src="../<?php echo htmlspecialchars($current_value); ?>" width="200" class="img-thumbnail mb-2"><br>
                        <?php else: ?>
                            <p class="text-muted">No image set.</p>
                        <?php endif; ?>
                        <input type="hidden" name="current_<?php echo $key; ?>" value="<?php echo htmlspecialchars($current_value); ?>">
                        <input type="file" name="<?php echo $key; ?>" class="form-control-file mt-2">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save All Settings">
        </div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>