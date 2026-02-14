<?php
require_once 'config.php';

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
$doctors_data = [];
$dept_name = "Tim Dokter Spesialis";

if ($dept_id > 0) {
    $stmt_dept = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt_dept->bind_param("i", $dept_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result()->fetch_assoc();
    if($res_dept) $dept_name = "Poliklinik " . $res_dept['name'];

    $stmt = $mysqli->prepare("SELECT * FROM doctors WHERE department_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $doctors_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dept_name); ?> - RS JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --jhc-red: #8a3033; 
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        }
        
        body { background-color: #fcfcfc; font-family: 'Inter', sans-serif; color: #333; }
        
        .hero-header { 
            background: var(--jhc-gradient); 
            color: white; padding: 80px 0; 
            border-radius: 0 0 50px 50px;
        }

        .doctor-card { 
            border: none; border-radius: 25px; transition: all 0.3s ease; 
            background: white; padding: 30px 20px;
        }
        .doctor-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

        .img-wrapper { width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; padding: 5px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .btn-jhc { background: var(--jhc-gradient); color: white !important; border-radius: 50px; font-weight: 700; border: none; }

        /* PERBAIKAN MODAL AGAR LEBIH PANJANG & TIDAK TERPOTONG */
        .modal-content { 
            border-radius: 35px; 
            border: none; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }

        .modal-body { 
            padding: 3rem 2.5rem 3.5rem !important; /* Padding bawah diperbesar */
        }

        .modal-profile-img {
            width: 160px; height: 160px;
            object-fit: cover; border-radius: 50%;
            border: 8px solid #fff;
            margin-top: -110px; /* Menyesuaikan posisi melayang */
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .info-box {
            background-color: #f8f9fa;
            border-radius: 25px;
            padding: 25px;
            margin-top: 25px;
            margin-bottom: 35px; /* Memberi ruang sebelum tombol */
            text-align: left;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 8px;
            display: block;
        }

        .info-text { color: #444; line-height: 1.6; margin-bottom: 20px; }
        .info-text:last-child { margin-bottom: 0; }
    </style>
</head>
<body>

<header class="hero-header text-center shadow-sm">
    <div class="container">
        <a href="index.php" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
            <i class="fas fa-chevron-left"></i> Kembali ke Beranda
        </a>
        <h1 class="fw-bold"><?= htmlspecialchars($dept_name); ?></h1>
        <p class="opacity-75">Temukan jadwal dan profil dokter spesialis terbaik kami.</p>
    </div>
</header>

<main class="container py-5">
    <div class="row g-4 justify-content-center">
        <?php if (!empty($doctors_data)): ?>
            <?php foreach($doctors_data as $doc): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card doctor-card h-100 shadow-sm text-center">
                        <div class="img-wrapper">
                            <img src="public/<?= htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/default-doctor.png'); ?>" alt="Foto Dokter">
                        </div>
                        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($doc['name']); ?></h6>
                        <p class="text-danger small fw-bold text-uppercase mb-4" style="font-size: 0.7rem;"><?= htmlspecialchars($doc['specialty']); ?></p>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#modalDetail" 
                                data-id="<?= $doc['id']; ?>"
                                data-name="<?= htmlspecialchars($doc['name']); ?>"
                                data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                                data-desc="<?= htmlspecialchars($doc['description']); ?>"
                                data-schedule="<?= htmlspecialchars($doc['schedule']); ?>">
                                <i class="far fa-address-card me-1"></i> Profil
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-user-md fa-4x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">Jadwal dokter belum tersedia untuk poliklinik ini.</h5>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center">
                <img id="mdl-img" src="" class="modal-profile-img shadow">
                
                <h3 id="mdl-name" class="fw-bold text-dark mt-3 mb-4"></h3>
                
                <div class="info-box">
                    <div class="mb-4">
                        <span class="info-label"><i class="fas fa-info-circle me-1"></i> Tentang Dokter</span>
                        <p id="mdl-desc" class="info-text small text-muted"></p>
                    </div>
                    <div>
                        <span class="info-label"><i class="fas fa-calendar-alt me-1"></i> Jadwal Praktik</span>
                        <p id="mdl-schedule" class="info-text small fw-bold text-dark"></p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <button type="button" class="btn btn-light rounded-pill w-100 fw-bold border py-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                    <div class="col-6">
                        <a href="" id="mdl-booking-link" class="btn btn-jhc rounded-pill w-100 fw-bold py-3">Buat Janji</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modalDetail = document.getElementById('modalDetail');
    modalDetail.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const doctorId = button.getAttribute('data-id');
        
        document.getElementById('mdl-name').innerText = button.getAttribute('data-name');
        document.getElementById('mdl-desc').innerText = button.getAttribute('data-desc') || 'Biodata profesional belum tersedia.';
        document.getElementById('mdl-schedule').innerText = button.getAttribute('data-schedule') || 'Jadwal belum diatur.';
        document.getElementById('mdl-img').src = button.getAttribute('data-img');
        
        document.getElementById('mdl-booking-link').href = 'booking.php?doctor_id=' + doctorId;
    });
</script>
</body>
</html>