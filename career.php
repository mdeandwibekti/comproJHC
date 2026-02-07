<?php 
// 1. Pastikan koneksi dipanggil di paling atas
require_once 'config.php'; 

// Cek koneksi untuk memastikan tidak ada error database tersembunyi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karir - JHC Tasikmalaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    
    <style>
        .card-job {
            transition: all 0.3s ease;
            border-radius: 15px;
        }
        .card-job:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .job-icon {
            width: 50px;
            height: 50px;
            background: #eef5f9;
            color: #1B71A1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Lowongan Tersedia</h2>
        <p class="text-muted">Temukan peluang karir dan bergabunglah dengan tim medis profesional kami.</p>
    </div>

    <div class="row g-4">
        <?php
        // 2. Query untuk mengambil data lowongan
        $jobs = $mysqli->query("SELECT * FROM careers WHERE status = 'Open' ORDER BY id DESC");

        if ($jobs && $jobs->num_rows > 0):
            while($job = $jobs->fetch_assoc()):
        ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 card-job">
                <div class="card-body p-4">
                    <div class="job-icon">
                        <i class="fas fa-user-md fa-lg"></i>
                    </div>
                    <h5 class="fw-bold text-primary mb-2"><?= htmlspecialchars($job['job_title']); ?></h5>
                    
                    <p class="text-muted small mb-4">
                        <?= substr(htmlspecialchars($job['description']), 0, 120); ?>...
                    </p>
                    
                    <a href="apply.php?job_id=<?= $job['id']; ?>" class="btn btn-primary rounded-pill w-100 fw-bold">
                        Lamar Sekarang <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
        <div class="col-12 text-center py-5">
            <div class="opacity-50 mb-3">
                <i class="fas fa-folder-open fa-4x"></i>
            </div>
            <h5 class="text-muted">Saat ini belum ada lowongan yang dibuka.</h5>
            <a href="index.php" class="btn btn-link">Kembali ke Beranda</a>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>