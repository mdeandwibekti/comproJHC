<?php
require_once 'config.php';

// 1. Tangkap ID Departemen dari URL
$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
$doctors_data = [];
$dept_name = "Semua Dokter";

if ($dept_id > 0) {
    // 2. Ambil Nama Departemen untuk Judul
    $stmt_dept = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt_dept->bind_param("i", $dept_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result()->fetch_assoc();
    if($res_dept) $dept_name = $res_dept['name'];

    // 3. Query Dokter berdasarkan ID Departemen
    $stmt = $mysqli->prepare("SELECT * FROM doctors WHERE department_id = ?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $doctors_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $result = $mysqli->query("SELECT * FROM doctors");
    $doctors_data = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tim Dokter - <?= htmlspecialchars($dept_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --jhc-red: #8a3033;
            --jhc-red-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --jhc-bg: #fdfdfd;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--jhc-bg);
            color: #333;
        }

        /* Hero Section */
        .hero-section {
            background: var(--jhc-red-gradient);
            padding: 80px 0;
            border-radius: 0 0 60px 60px;
            color: white;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(138, 48, 51, 0.2);
        }

        /* Doctor Card Styling */
        .doctor-card {
            border: none;
            border-radius: 30px;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 30px 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            text-align: center;
        }

        .doctor-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(138, 48, 51, 0.12);
        }

        /* Image Wrapper sesuai gambar referensi */
        .doctor-img-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
            position: relative;
            padding: 8px;
            background: linear-gradient(to bottom, #fceeee, #ffffff);
            border-radius: 50%;
        }

        .doctor-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .doctor-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #2d3436;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .doctor-specialty {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--jhc-red);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .btn-profile {
            background: var(--jhc-red-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: 700;
            font-size: 0.9rem;
            width: 100%;
            transition: 0.3s;
        }

        .btn-profile:hover {
            color: white;
            box-shadow: 0 8px 20px rgba(138, 48, 51, 0.3);
            transform: scale(1.02);
        }

        /* Modal Customization */
        .modal-content {
            border-radius: 35px;
            border: none;
        }
        .modal-header {
            background: var(--jhc-red);
            color: white;
            border-radius: 35px 35px 0 0;
            padding: 25px;
        }
        .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

        .schedule-badge {
            background: #fff5f5;
            color: var(--jhc-red);
            border: 1px solid #ffe3e3;
            padding: 8px 15px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<header class="hero-section">
    <div class="container text-center">
        <a href="index.php" class="text-white text-decoration-none mb-3 d-inline-block opacity-75 hover-opacity-100">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
        </a>
        <h1 class="display-5 fw-bold mb-2">Tim Dokter Spesialis</h1>
        <p class="opacity-75 fs-5">Layanan Poliklinik <?= htmlspecialchars($dept_name); ?></p>
    </div>
</header>

<main class="container pb-5">
    <div class="row g-4 justify-content-center">
        <?php if (!empty($doctors_data)): ?>
            <?php foreach($doctors_data as $doc): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card doctor-card h-100">
                        <div class="doctor-img-container">
                            <img src="public/<?= htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/default-doctor.png'); ?>" alt="<?= htmlspecialchars($doc['name']); ?>">
                        </div>
                        
                        <div class="doctor-name"><?= htmlspecialchars($doc['name']); ?></div>
                        <div class="doctor-specialty"><?= htmlspecialchars($doc['specialty']); ?></div>
                        
                        <button class="btn btn-profile mt-auto" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalDetailDokter" 
                                data-name="<?= htmlspecialchars($doc['name']); ?>" 
                                data-specialty="<?= htmlspecialchars($doc['specialty']); ?>" 
                                data-desc="<?= htmlspecialchars($doc['description']); ?>" 
                                data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                                data-schedule="<?= htmlspecialchars($doc['schedule'] ?? 'Hubungi petugas kami untuk jadwal lengkap.'); ?>">
                            Lihat Profil
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-user-md fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Maaf, dokter pada poli ini belum tersedia.</h4>
                <a href="index.php" class="btn btn-profile w-auto px-4 mt-3">Lihat Layanan Lain</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="modalDetailDokter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> Profil Lengkap</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="position-relative mb-4" style="margin-top: -80px;">
                    <img id="mdl-img" src="" class="rounded-circle border border-5 border-white shadow-lg" 
                         style="width: 140px; height: 140px; object-fit: cover; background: #fff;">
                </div>
                
                <h3 id="mdl-name" class="fw-bold text-dark mb-1"></h3>
                <p id="mdl-specialty" class="text-danger fw-bold text-uppercase small mb-4"></p>
                
                <div class="text-start mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Biografi</h6>
                    <p id="mdl-desc" class="text-muted small" style="line-height: 1.7;"></p>
                </div>
                
                <div class="text-start">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Jadwal Praktik</h6>
                    <div id="mdl-schedule" class="schedule-badge w-100"></div>
                </div>

                <div class="d-grid mt-4">
                    <a href="booking.php" class="btn btn-profile py-3">
                        <i class="fas fa-calendar-check me-2"></i> Buat Janji Temu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modalDetail = document.getElementById('modalDetailDokter');
    modalDetail.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        
        document.getElementById('mdl-name').innerText = btn.getAttribute('data-name');
        document.getElementById('mdl-specialty').innerText = btn.getAttribute('data-specialty');
        document.getElementById('mdl-desc').innerText = btn.getAttribute('data-desc') || "Informasi profil belum ditambahkan.";
        document.getElementById('mdl-schedule').innerText = btn.getAttribute('data-schedule');
        document.getElementById('mdl-img').src = btn.getAttribute('data-img');
    });
</script>

</body>
</html>