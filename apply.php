<?php 
require_once 'config.php';

// Ambil ID dari URL
$job_id = isset($_GET['job_id']) ? $mysqli->real_escape_string($_GET['job_id']) : '';

$job_name = "Pendaftaran Karir";
if ($job_id) {
    $res = $mysqli->query("SELECT job_title FROM careers WHERE id = '$job_id'");
    if ($data = $res->fetch_assoc()) {
        $job_name = $data['job_title'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamar Posisi <?= htmlspecialchars($job_name); ?> - RS JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { 
            background: #f8f9fa; 
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        /* Header Accent */
        .top-accent {
            height: 6px;
            background: var(--jhc-gradient);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .apply-container { max-width: 850px; margin: 60px auto; }

        .card-apply {
            border: none;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .form-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 8px;
        }

        .form-control-modern { 
            border-radius: 12px; 
            padding: 12px 15px; 
            border: 1px solid #e0e0e0;
            background-color: #fcfcfc;
            transition: all 0.3s ease;
        }

        .form-control-modern:focus {
            border-color: var(--jhc-red-light);
            box-shadow: 0 0 0 4px rgba(189, 48, 48, 0.1);
            background-color: #fff;
        }

        /* --- BUTTON STYLE (Sama dengan Home) --- */
        .btn-janji {
            background: var(--jhc-gradient) !important;
            color: white !important;
            border-radius: 50px;
            padding: 12px 25px;
            font-size: 0.9rem;
            font-weight: 700;
            border: 2px solid transparent !important;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Hover & Active State: Jadi Putih */
        .btn-janji:hover, 
        .btn-janji:active,
        .btn-janji:focus {
            background: white !important;
            color: var(--jhc-red-dark) !important;
            border-color: var(--jhc-red-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 16, 46, 0.3);
        }

        /* Ubah warna icon saat dihover */
        .btn-janji:hover i,
        .btn-janji:active i {
            color: var(--jhc-red-dark) !important;
        }

        .job-badge {
            display: inline-block;
            background: rgba(138, 48, 51, 0.1);
            color: var(--jhc-red-dark);
            padding: 6px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 30px;
            font-weight: 800;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 4px;
            background: var(--jhc-gradient);
            border-radius: 10px;
        }

        .upload-box {
            border: 2px dashed #e0e0e0;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            background: #fafafa;
        }
    </style>
</head>
<body>

<div class="top-accent"></div>

<div class="container apply-container px-3">
    <div class="mb-4">
        <a href="career.php" class="btn btn-janji">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Lowongan
        </a>
    </div>
    
    <div class="card card-apply">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-5">
                <span class="job-badge">Formulir Rekrutmen JHC</span>
                <h2 class="section-title text-center mx-auto d-table">Lamar Posisi <?= htmlspecialchars($job_name); ?></h2>
                <p class="text-muted small">Silakan lengkapi data diri Anda dengan benar untuk proses verifikasi.</p>
            </div>

            <form action="process_career.php" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><i class="fas fa-user me-2"></i>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control form-control-modern" placeholder="Masukkan nama sesuai KTP" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold"><i class="fab fa-whatsapp me-2"></i>No. WhatsApp Aktif</label>
                        <input type="text" name="phone" class="form-control form-control-modern" placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold"><i class="fas fa-envelope me-2"></i>Alamat Email</label>
                        <input type="email" name="email" class="form-control form-control-modern" placeholder="nama@email.com" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold"><i class="fas fa-graduation-cap me-2"></i>Pendidikan Terakhir</label>
                        <input type="text" name="education" class="form-control form-control-modern" placeholder="Contoh: Profesi Ners / D3 Rekam Medis" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Alamat Domisili</label>
                        <textarea name="address" class="form-control form-control-modern" rows="3" placeholder="Alamat lengkap saat ini..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold"><i class="fas fa-file-pdf me-2"></i>Curriculum Vitae (CV)</label>
                        <div class="upload-box">
                            <input type="file" name="cv_file" class="form-control form-control-modern mb-2" accept=".pdf" required>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                format file <b>.PDF</b> dengan ukuran maksimal <b>2MB</b>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold"><i class="fas fa-briefcase me-2"></i>Konfirmasi Lowongan</label>
                        <?php if (!empty($job_id)): ?>
                            <div class="form-control-modern bg-light d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span><?= htmlspecialchars($job_name); ?></span>
                            </div>
                            <input type="hidden" name="job_id" value="<?= $job_id; ?>">
                        <?php else: ?>
                            <select name="job_id" class="form-select form-control-modern" required>
                                <option value="">-- Pilih Posisi yang Ingin Dilamar --</option>
                                <?php 
                                $q_jobs = $mysqli->query("SELECT id, job_title FROM careers WHERE status = 'Open'");
                                while($j = $q_jobs->fetch_assoc()):
                                ?>
                                    <option value="<?= $j['id']; ?>"><?= $j['job_title']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-12 mt-5">
                        <button type="submit" name="submit_application" class="btn btn-janji w-100 rounded-pill fw-bold shadow-lg">
                            KIRIM LAMARAN SEKARANG <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                        <p class="text-center text-muted mt-3" style="font-size: 0.75rem;">
                            Dengan menekan tombol di atas, Anda menyatakan bahwa data yang diisi adalah benar dan dapat dipertanggungjawabkan.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>