<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
    if ($stmt = $mysqli->prepare($sql)) {
        foreach($_POST as $key => $value){
            // Hanya simpan field yang diawali dengan 'contact_' untuk keamanan
            if (strpos($key, 'contact_') === 0) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    // Pengalihan sukses tanpa error header
    header("location: contact_settings.php?saved=true");
    exit();
}

// Ambil data settings terbaru dari database
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'contact_%'");
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

    /* Wrapper bergaya Neumorphism sesuai referensi visual */
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

    /* Tombol Gradasi JHC */
    .btn-jhc-save {
        background: var(--jhc-gradient) !important;
        color: white !important;
        border-radius: 12px !important;
        padding: 12px 35px !important;
        font-weight: 700;
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s;
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .form-label { font-weight: 700; color: #555; font-size: 0.85rem; text-transform: uppercase; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1">Informasi Kontak & Footer</h3>
                <p class="text-muted small mb-0">Kelola detail alamat, telepon, dan email yang muncul di seluruh halaman website.</p>
            </div>
            <button type="submit" form="contactForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Perubahan
            </button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Pengaturan kontak berhasil diperbarui!
            </div>
        <?php endif; ?>

        <form action="contact_settings.php" method="post" id="contactForm">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label">Tagline / Deskripsi Singkat Footer</label>
                    <input type="text" name="contact_tagline" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($settings['contact_tagline'] ?? ''); ?>" 
                           placeholder="Misal: Pusat Pelayanan Jantung Terpadu Tasikmalaya">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Alamat Rumah Sakit</label>
                    <textarea name="contact_address" class="form-control" rows="3" 
                              placeholder="Masukkan alamat lengkap RS JHC..."><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon / Hotline</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                        <input type="text" name="contact_phone" class="form-control form-control-lg" 
                               value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" 
                               placeholder="(0265) 3172112">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat Email Resmi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="contact_email" class="form-control form-control-lg" 
                               value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" 
                               placeholder="info@jhc-tasikmalaya.com">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>