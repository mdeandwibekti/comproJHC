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
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    if ($stmt = $mysqli->prepare($sql)) {
        foreach($_POST as $key => $value){
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }
    header("location: contact_settings2.php?saved=true");
    exit();
}
?>
<div class="container-fluid">
    <h3>Edit Contact Information</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Settings saved successfully.</div>"; ?>
    <form action="contact_settings2.php" method="post">
        <div class="form-group"><label>Tagline in Footer</label><input type="text" name="contact_tagline" class="form-control" value="<?php echo htmlspecialchars($settings['contact_tagline'] ?? ''); ?>"></div>
        <div class="form-group mt-3"><label>Address</label><textarea name="contact_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea></div>
        <div class="form-group mt-3"><label>Phone</label><input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>"></div>
        <div class="form-group mt-3"><label>Email</label><input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>"></div>
        <div class="form-group mt-4"><input type="submit" class="btn btn-primary" value="Save Settings"></div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>