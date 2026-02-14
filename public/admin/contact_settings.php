<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
    if ($stmt = $mysqli->prepare($sql)) {
        foreach($_POST as $key => $value){
            // Filter field untuk keamanan
            if (strpos($key, 'contact_') === 0 || strpos($key, 'social_') === 0) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    header("location: contact_settings.php?saved=true");
    exit();
}

// Ambil data settings terbaru
$settings = [];
$result = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'contact_%' OR setting_key LIKE 'social_%'");
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
    .admin-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border: 1px solid rgba(0,0,0,0.05); }
    .manage-header { border-left: 4px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .btn-jhc-save { background: var(--jhc-gradient) !important; color: white !important; border-radius: 12px; padding: 12px 35px; font-weight: 700; border: none; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); transition: 0.3s; }
    .form-section-title { font-size: 0.75rem; font-weight: 800; color: var(--jhc-red-dark); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; display: flex; align-items: center; }
    .form-section-title::after { content: ""; flex: 1; height: 1px; background: #eee; margin-left: 15px; }
    .form-label { font-weight: 700; color: #555; font-size: 0.8rem; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1">Manajemen Footer & Kontak</h3>
                <p class="text-muted small mb-0">Sesuaikan informasi yang tampil pada bagian bawah (footer) website utama.</p>
            </div>
            <button type="submit" form="contactForm" class="btn btn-jhc-save"><i class="fas fa-save me-2"></i> Simpan</button>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Sinkronisasi footer berhasil!
            </div>
        <?php endif; ?>

        <form action="contact_settings.php" method="post" id="contactForm">
            <div class="row g-4">
                
                <div class="col-12">
                    <div class="form-section-title">Branding Footer</div>
                    <label class="form-label">Tagline Footer</label>
                    <textarea name="contact_tagline" class="form-control" rows="2"><?php echo htmlspecialchars($settings['contact_tagline'] ?? ''); ?></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Telepon IGD (Gawat Darurat)</label>
                    <input type="text" name="contact_igd" class="form-control" value="<?php echo htmlspecialchars($settings['contact_igd'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp RS</label>
                    <input type="text" name="contact_whatsapp" class="form-control" value="<?php echo htmlspecialchars($settings['contact_whatsapp'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Resmi</label>
                    <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>

                <div class="col-12 mt-5">
                    <div class="form-section-title">Media Sosial</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Facebook URL</label>
                    <input type="text" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Instagram URL</label>
                    <input type="text" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">YouTube URL</label>
                    <input type="text" name="social_youtube" class="form-control" value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>">
                </div>

                <div class="col-12 mt-5">
                    <div class="form-section-title">Alamat & Lokasi Peta</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alamat Lengkap (Teks)</label>
                    <textarea name="contact_address" class="form-control" rows="5"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Google Maps Iframe URL</label>
                    <textarea name="contact_map_url" class="form-control" rows="5" placeholder="Masukkan URL dari src iframe Google Maps..."><?php echo htmlspecialchars($settings['contact_map_url'] ?? ''); ?></textarea>
                    <small class="text-muted italic">Tempelkan link 'src' saja dari kode embed Google Maps.</small>
                </div>

            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>