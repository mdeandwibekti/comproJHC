<?php 
require_once 'config.php'; 

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Set timezone agar perhitungan deadline akurat
date_default_timezone_set('Asia/Jakarta');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karir - RS JHC Tasikmalaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --jhc-soft-bg: #f8f9fa;
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: var(--jhc-soft-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* --- NAVBAR STYLE (Mirip Index.php User) --- */
        .navbar {
            padding: 0.75rem 0;
            min-height: 80px;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: var(--transition-smooth);
            border-bottom: 3px solid var(--jhc-red-dark); /* Aksen Merah Bawah */
        }

        .navbar .container {
            max-width: 1320px;
            padding: 0 1.25rem;
        }

        /* Logo Besar & Animasi */
        .navbar-brand img {
            height: 65px; /* Ukuran Besar sesuai Index */
            width: auto;
            transition: var(--transition-smooth);
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
        }

        /* Efek Bergerak saat Disentuh (Hover) */
        .navbar-brand:hover img {
            transform: scale(1.05);
            filter: drop-shadow(0 4px 12px rgba(200, 16, 46, 0.25));
        }

        /* --- BUTTON STYLE (Sama dengan Home/Apply Job) --- */
        .btn-janji {
            background: var(--jhc-gradient) !important;
            color: white !important;
            border-radius: 50px;
            padding: 10px 25px;
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

        .btn-janji:hover, 
        .btn-janji:active,
        .btn-janji:focus {
            background: white !important;
            color: var(--jhc-red-dark) !important;
            border-color: var(--jhc-red-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 16, 46, 0.3);
        }

        .btn-janji:hover i,
        .btn-janji:active i {
            color: var(--jhc-red-dark) !important;
        }

        /* --- CAREER HEADER --- */
        .career-header {
            background: var(--jhc-gradient);
            color: white;
            padding: 80px 0;
            border-radius: 0 0 50px 50px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(138, 48, 51, 0.2);
            margin-top: 90px; /* Spacer karena navbar fixed */
        }

        /* Job Card Styling */
        .card-job {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border-radius: 20px;
            border: none !important;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-job:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(138, 48, 51, 0.1) !important;
        }

        .job-icon {
            width: 55px;
            height: 55px;
            background: rgba(138, 48, 51, 0.05);
            color: var(--jhc-red-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 1.4rem;
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
            margin-bottom: 8px;
            font-size: 1.25rem;
        }

        .job-desc {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .badge-status {
            background: #e6f4ea;
            color: #1e7e34;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .deadline-tag {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .urgent-flash {
            color: #d63031;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .empty-state {
            padding: 100px 0;
            color: #b2bec3;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="index.php">
            <img src="public/assets/img/gallery/JHC_Logo.png" alt="JHC Logo" onerror="this.src='assets/img/gallery/JHC_Logo.png';">
        </a>
        
        <a href="index.php" class="btn btn-janji">
            <i class="fas fa-home me-2"></i> Kembali ke Home
        </a>
    </div>
</nav>

<div class="career-header text-center">
    <div class="container">
        <h1 class="fw-bold display-5 mb-3">Karir & Rekrutmen</h1>
        <p class="lead opacity-75">Bergabunglah bersama tim medis profesional kami di RS JHC Tasikmalaya.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <?php
        // Mengambil data lowongan yang statusnya Open
        $jobs = $mysqli->query("SELECT * FROM careers WHERE status = 'open' ORDER BY id DESC");

        if ($jobs && $jobs->num_rows > 0):
            while($job = $jobs->fetch_assoc()):
                
                // --- LOGIKA DEADLINE ---
                $has_deadline = !empty($job['deadline']) && $job['deadline'] != '0000-00-00';
                $is_expired = false;
                $is_urgent = false;
                $deadline_label = "";

                if ($has_deadline) {
                    $deadline_date = strtotime($job['deadline']);
                    $today = strtotime(date('Y-m-d'));
                    $days_diff = ($deadline_date - $today) / 86400;

                    if ($days_diff < 0) {
                        $is_expired = true; // Tanggal sudah lewat
                    } elseif ($days_diff <= 7) {
                        $is_urgent = true;
                        $deadline_label = "Sisa " . round($days_diff) . " Hari Lagi!";
                    } else {
                        $deadline_label = "Batas: " . date('d M Y', $deadline_date);
                    }
                }

                // Jangan tampilkan jika sudah kedaluwarsa
                if ($is_expired) continue;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm card-job">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge-status">Open</span>
                        <?php if ($has_deadline): ?>
                            <span class="deadline-tag <?= $is_urgent ? 'urgent-flash fw-bold' : 'text-muted' ?>">
                                <i class="far fa-clock me-1"></i> <?= $deadline_label ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="job-icon">
                        <i class="fas fa-user-md"></i>
                    </div>

                    <h5 class="job-title"><?= htmlspecialchars($job['job_title']); ?></h5>
                    
                    <div class="d-flex align-items-center mb-3 text-muted small">
                        <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($job['location']); ?></span>
                        <span><i class="far fa-calendar-alt me-1"></i> <?= date('d/m/y', strtotime($job['post_date'])); ?></span>
                    </div>

                    <p class="job-desc flex-grow-1">
                        <?= nl2br(htmlspecialchars($job['description'])); ?>
                    </p>

                    <a href="apply.php?job_id=<?= $job['id']; ?>" class="btn btn-janji w-100">
                        Lamar Sekarang <i class="fas fa-paper-plane ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
        <div class="col-12 text-center empty-state">
            <i class="fas fa-search fa-4x mb-4 opacity-25"></i>
            <h4 class="text-dark fw-bold">Belum Ada Lowongan Tersedia</h4>
            <p>Saat ini belum ada posisi yang dibuka. Silakan cek kembali secara berkala.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>