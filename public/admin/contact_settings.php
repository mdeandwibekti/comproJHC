<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Tetap Sama) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO settings2 (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
    if ($stmt = $mysqli->prepare($sql)) {
        foreach($_POST as $key => $value){
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

$settings = [];
$result = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'contact_%' OR setting_key LIKE 'social_%'");
if ($result) {
    while($row = $result->fetch_assoc()){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    /* Breadcrumb */
    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .admin-wrapper { 
        background: #fff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.03); 
        padding: 45px; border: 1px solid #f1f5f9; 
    }

    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }

    /* Input Styling */
    .form-label { font-weight: 700; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: flex; align-items: center; }
    .form-label i { margin-right: 8px; color: var(--jhc-red-dark); opacity: 0.8; font-size: 0.9rem; }
    
    .form-control { 
        border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; 
        transition: 0.3s; background-color: #fcfdfe; font-size: 0.95rem;
    }
    .form-control:focus { 
        border-color: var(--jhc-red-dark); box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1); 
        background-color: #fff; 
    }

    /* Section Divider */
    .form-section-title { 
        font-size: 0.85rem; font-weight: 800; color: var(--jhc-red-dark); 
        text-transform: uppercase; letter-spacing: 1.5px; margin: 45px 0 25px 0; 
        display: flex; align-items: center; 
    }
    .form-section-title::after { content: ""; flex: 1; height: 2px; background: linear-gradient(to right, #f1f5f9, transparent); margin-left: 20px; }

    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; color: white !important; 
        border-radius: 14px; padding: 14px 40px; font-weight: 800; border: none; 
        transition: 0.3s; box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); 
    }
    .btn-jhc-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); }

    .info-card { background: #fff5f5; border-radius: 14px; padding: 20px; border-left: 4px solid var(--jhc-red-dark); margin-bottom: 30px; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <span class="current">Info Kontak & Footer</span>
    </div>

    <div class="admin-wrapper">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;">Manajemen Kontak</h2>
                <p class="text-muted small mb-0">Sinkronisasikan informasi alamat, nomor telepon, dan sosial media ke seluruh halaman website.</p>
            </div>
        </div>

        <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-5 mb-4 p-3 fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-lg me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Data Berhasil di Perbaharui!</h6>
                        <span class="small">Perubahan informasi kontak telah diterapkan secara real-time ke website utama.</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form action="contact_settings.php" method="post" id="contactForm">
            <div class="row g-4">
                
                <div class="col-12">
                    <div class="form-section-title">Branding & Identitas</div>
                    <div class="info-card">
                        <label class="form-label"><i class="fas fa-quote-left"></i> Tagline Footer</label>
                        <textarea name="contact_tagline" class="form-control" rows="2" placeholder="Contoh: Memberikan Pelayanan Kesehatan Terbaik dengan Sepenuh Hati..."><?php echo htmlspecialchars($settings['contact_tagline'] ?? ''); ?></textarea>
                        <small class="text-muted mt-2 d-block fst-italic">Teks ini akan tampil di bawah logo RS pada bagian footer.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-phone-alt"></i> Telepon IGD</label>
                    <input type="text" name="contact_igd" class="form-control" placeholder="E.g. (0265) 123456" value="<?php echo htmlspecialchars($settings['contact_igd'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fab fa-whatsapp"></i> WhatsApp RS</label>
                    <input type="text" name="contact_whatsapp" class="form-control" placeholder="E.g. 08123456789" value="<?php echo htmlspecialchars($settings['contact_whatsapp'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email Resmi</label>
                    <input type="email" name="contact_email" class="form-control" placeholder="E.g. info@rsjhc.com" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>

                <div class="col-12">
                    <div class="form-section-title">Media Sosial</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fab fa-facebook-f"></i> Facebook URL</label>
                    <input type="text" name="social_facebook" class="form-control" placeholder="https://facebook.com/rsjhc" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fab fa-instagram"></i> Instagram URL</label>
                    <input type="text" name="social_instagram" class="form-control" placeholder="https://instagram.com/rsjhc" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fab fa-youtube"></i> YouTube URL</label>
                    <input type="text" name="social_youtube" class="form-control" placeholder="https://youtube.com/c/rsjhc" value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>">
                </div>

                <div class="col-12">
                    <div class="form-section-title">Alamat & Lokasi Geografis</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Alamat Lengkap (Teks)</label>
                    <textarea name="contact_address" class="form-control" rows="6" placeholder="Tuliskan alamat lengkap beserta kode pos..."><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-map-marked-alt"></i> Google Maps Embed URL</label>
                    <textarea name="contact_map_url" class="form-control" rows="6" placeholder="Tempelkan atribut 'src' dari iframe Google Maps..."><?php echo htmlspecialchars($settings['contact_map_url'] ?? ''); ?></textarea>
                    <div class="p-3 bg-light rounded-3 border mt-3">
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            <i class="fas fa-lightbulb me-1 text-warning"></i> <b>Cara Ambil URL:</b> Buka Google Maps > Share > Embed a map > Copy atribut <b>src="..."</b> saja.
                        </small>
                    </div>
                </div>

            </div>
            
            <div class="mt-5 pt-4 border-top text-center text-md-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-save me-2"></i> Simpan Seluruh Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>