<?php 
require_once 'config.php'; 

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --jhc-soft-bg: #f8f9fa;
        }

        body {
            background-color: var(--jhc-soft-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Tombol Kembali ke Dashboard Floating */
        .top-nav-admin {
            background: white;
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* Hero Header */
        .career-header {
            background: var(--jhc-gradient);
            color: white;
            padding: 80px 0;
            border-radius: 0 0 50px 50px;
            margin-bottom: -50px;
            box-shadow: 0 10px 30px rgba(138, 48, 51, 0.2);
        }

        /* Job Card Styling */
        .card-job {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.03) !important;
            overflow: hidden;
            background: #ffffff;
        }

        .card-job:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(138, 48, 51, 0.1) !important;
        }

        .job-icon {
            width: 60px;
            height: 60px;
            background: rgba(138, 48, 51, 0.05);
            color: var(--jhc-red-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            font-size: 1.5rem;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .card-job:hover .job-icon {
            background: var(--jhc-gradient);
            color: white;
        }

        .job-title {
            color: #2d3436;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 1.25rem;
        }

        .job-desc {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        /* Button Styling */
        .btn-apply {
            background: var(--jhc-gradient);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            text-decoration: none;
        }

        .btn-apply:hover {
            color: white;
            opacity: 0.9;
            box-shadow: 0 8px 15px rgba(138, 48, 51, 0.3);
        }

        .btn-dashboard {
            background: white;
            color: var(--jhc-red-dark);
            border: 2px solid var(--jhc-red-dark);
            font-weight: 700;
            border-radius: 50px;
            padding: 8px 20px;
            transition: 0.3s;
        }

        .btn-dashboard:hover {
            background: var(--jhc-red-dark);
            color: white;
        }

        .badge-status {
            background: #e6f4ea;
            color: #1e7e34;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .empty-state {
            padding: 100px 0;
            color: #b2bec3;
        }
    </style>
</head>
<body>

<div class="top-nav-admin">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand text-dark fw-bold" href="index.php" style="text-decoration: none;">
            <i class="fas fa-hospital text-danger me-2"></i> RS JHC
        </a>
        <a href="index.php" class="btn btn-link text-decoration-none mb-4 text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke dashboard
        </a>
    </div>
</div>

<div class="career-header text-center">
    <div class="container">
        <h1 class="fw-bold display-5 mb-3">Bergabung Bersama JHC</h1>
        <p class="lead opacity-75">Wujudkan dedikasi medis Anda bersama tim profesional RS JHC Tasikmalaya.</p>
    </div>
</div>

<div class="container pb-5" style="margin-top: 20px;">
    <div class="row g-4 justify-content-center">
        <?php
        $jobs = $mysqli->query("SELECT * FROM careers WHERE status = 'Open' ORDER BY id DESC");

        if ($jobs && $jobs->num_rows > 0):
            while($job = $jobs->fetch_assoc()):
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 card-job">
                <div class="card-body p-4 p-xl-5">
                    <span class="badge-status"><i class="fas fa-check-circle me-1"></i> Rekrutmen Aktif</span>
                    <div class="job-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h5 class="job-title"><?= htmlspecialchars($job['job_title']); ?></h5>
                    
                    <p class="job-desc">
                        <?= nl2br(substr(htmlspecialchars($job['description']), 0, 150)); ?>...
                    </p>
                    
                    <a href="apply.php?job_id=<?= $job['id']; ?>" class="btn btn-apply">
                        Lamar Posisi Ini <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
        <div class="col-md-8 text-center empty-state">
            <div class="mb-4">
                <i class="fas fa-briefcase fa-5x opacity-25"></i>
            </div>
            <h4 class="text-dark fw-bold">Belum Ada Lowongan Aktif</h4>
            <p>Terima kasih atas minat Anda. Saat ini kami belum membuka posisi baru.<br>Silakan cek kembali di lain waktu.</p>
            <a href="index.php" class="btn btn-outline-danger rounded-pill px-4 mt-3">Kembali ke Beranda</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>