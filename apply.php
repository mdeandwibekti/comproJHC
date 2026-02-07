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
    <title>Lamar Posisi <?= htmlspecialchars($job_name); ?> - JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f7f9; }
        .apply-container { max-width: 800px; margin: 50px auto; }
        .form-control-modern { border-radius: 10px; padding: 12px; border: 1px solid #dee2e6; }
        .btn-jhc { background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); border: none; color: white; }
        .btn-jhc:hover { opacity: 0.9; color: white; }
    </style>
</head>
<body>

<div class="container apply-container">
    <a href="career.php" class="btn btn-link text-decoration-none mb-3 text-muted">
        <i class="fas fa-chevron-left me-2"></i> Kembali
    </a>
    
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-body p-5">
            <h3 class="fw-bold mb-1" style="color: #8a3033;">Lamar Posisi:</h3>
            <h4 class="mb-4 text-dark"><?= htmlspecialchars($job_name); ?></h4>
            <hr>

            <form action="process_career.php" method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="job_id" value="<?= $job_id; ?>">

                <div class="col-md-6">
                    <label class="form-label fw-bold small">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control form-control-modern" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small">No. WhatsApp</label>
                    <input type="text" name="phone" class="form-control form-control-modern" placeholder="08..." required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">Email</label>
                    <input type="email" name="email" class="form-control form-control-modern" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">Pendidikan Terakhir</label>
                    <input type="text" name="education" class="form-control form-control-modern" placeholder="Contoh: S1 Keperawatan" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">Alamat Lengkap</label>
                    <textarea name="address" class="form-control form-control-modern" rows="3" required></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold small">Upload CV (PDF, Maks 2MB)</label>
                    <input type="file" name="cv_file" class="form-control form-control-modern" accept=".pdf" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small text-secondary">Posisi yang Dilamar</label>
                    
                    <?php if (!empty($job_id)): ?>
                        <input type="text" class="form-control form-control-modern bg-light" value="<?= htmlspecialchars($job_name); ?>" readonly>
                        <input type="hidden" name="job_id" value="<?= $job_id; ?>">
                    <?php else: ?>
                        <select name="job_id" class="form-select form-control-modern" required>
                            <option value="">-- Pilih Posisi --</option>
                            <?php 
                            $q_jobs = $mysqli->query("SELECT id, job_title FROM careers WHERE status = 'Open'");
                            while($j = $q_jobs->fetch_assoc()):
                            ?>
                                <option value="<?= $j['id']; ?>"><?= $j['job_title']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" name="submit_application" class="btn btn-jhc w-100 py-3 rounded-pill fw-bold shadow">
                        Kirim Lamaran <i class="fas fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>