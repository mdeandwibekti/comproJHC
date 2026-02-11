<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data lama untuk referensi penghapusan file jika ada upload baru
    $old_image_path = '';
    $res = $mysqli->query("SELECT setting_value FROM settings2 WHERE setting_key = 'popup_image_path'");
    if ($row_old = $res->fetch_assoc()) {
        $old_image_path = $row_old['setting_value'];
    }

    // 2. Simpan Title, Content, dan Status
    $sql = "INSERT INTO settings2 (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    $stmt = $mysqli->prepare($sql);
    
    $params = [
        'popup_title'   => $_POST['popup_title'] ?? '',
        'popup_content' => $_POST['popup_content'] ?? '',
        'popup_status'  => $_POST['popup_status'] ?? 'inactive'
    ];

    foreach ($params as $key => $val) {
        // PERBAIKAN: Gunakan variabel $key dan $val, bukan string langsung
        $stmt->bind_param("ss", $key, $val);
        $stmt->execute();
    }

    // 3. Handle Upload Gambar
    if (isset($_FILES["popup_image"]) && $_FILES["popup_image"]["error"] == 0) {
        $upload_dir = "../assets/img/popups/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = $_FILES["popup_image"]["name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            if ($_FILES["popup_image"]["size"] < 2 * 1024 * 1024) {
                $new_filename = 'popup_' . time() . '.' . $ext;
                $target_file = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $target_file)) {
                    // Hapus file fisik lama jika ada
                    if (!empty($old_image_path) && file_exists("../" . $old_image_path)) {
                        unlink("../" . $old_image_path);
                    }

                    // Update path di database
                    $db_key = 'popup_image_path';
                    $db_path = 'assets/img/popups/' . $new_filename;
                    $stmt->bind_param("ss", $db_key, $db_path);
                    $stmt->execute();
                }
            }
        }
    }
    
    $stmt->close();
    header("location: popup_settings2.php?saved=true");
    exit();
}

// Ambil data settings terbaru
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
    :root { --jhc-red: #8a3033; --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); }
    .admin-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border: 1px solid rgba(0,0,0,0.05); }
    .manage-header { border-left: 5px solid var(--jhc-red); padding-left: 20px; margin-bottom: 30px; }
    .btn-jhc-save { background: var(--jhc-gradient); color: white !important; border-radius: 12px; padding: 12px 35px; font-weight: 700; border: none; transition: 0.3s; }
    .btn-jhc-save:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(138, 48, 51, 0.3); }
    .img-preview-box { background: #f8f9fa; border: 2px dashed #ddd; border-radius: 15px; padding: 20px; text-align: center; overflow: hidden; min-height: 200px; display: flex; align-items: center; justify-content: center; }
    .img-preview-box img { max-height: 250px; border-radius: 10px; object-fit: contain; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="d-flex justify-content-between align-items-center manage-header">
                <div>
                    <h3 class="fw-bold mb-1">Promotional Popup</h3>
                    <p class="text-muted small mb-0">Konten yang muncul saat beranda dibuka.</p>
                </div>
                <button type="submit" class="btn btn-jhc-save"><i class="fas fa-save me-2"></i> Simpan</button>
            </div>

            <?php if(isset($_GET['saved'])): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i> Pengaturan berhasil disimpan!</div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Promo</label>
                        <input type="text" name="popup_title" class="form-control" value="<?= htmlspecialchars($settings['popup_title'] ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Deskripsi / Konten</label>
                        <textarea name="popup_content" class="form-control" rows="5"><?= htmlspecialchars($settings['popup_content'] ?? ''); ?></textarea>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <label class="form-label d-block mb-2">Status Popup</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="popup_status" id="active" value="active" <?= ($settings['popup_status'] ?? '') == 'active' ? 'checked' : ''; ?>>
                            <label class="form-check-label text-success fw-bold" for="active">AKTIF</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="popup_status" id="inactive" value="inactive" <?= ($settings['popup_status'] ?? 'inactive') == 'inactive' ? 'checked' : ''; ?>>
                            <label class="form-check-label text-muted fw-bold" for="inactive">MATI</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label">Preview Gambar</label>
                    <div class="img-preview-box mb-3" id="previewContainer">
                        <?php if (!empty($settings['popup_image_path'])): ?>
                            <img src="../<?= htmlspecialchars($settings['popup_image_path']); ?>?t=<?= time(); ?>" class="img-fluid shadow-sm">
                        <?php else: ?>
                            <div class="text-muted"><i class="fas fa-image fa-3x mb-2"></i><br>Belum ada gambar</div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="popup_image" class="form-control" id="imageInput" accept="image/*">
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('imageInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('previewContainer').innerHTML = `<img src="${URL.createObjectURL(file)}" class="img-fluid shadow-sm">`;
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>