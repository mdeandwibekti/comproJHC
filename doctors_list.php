<?php
require_once 'config.php';

// 1. Tangkap ID Departemen dari URL (contoh: doctors_list.php?dept_id=5)
// 1. Tangkap ID dari URL (Contoh: doctors_list.php?dept_id=1)
$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
$doctors_data = [];

if ($dept_id > 0) {
    // 2. Query hanya dokter yang memiliki department_id yang sesuai
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
    <title>Tim Dokter - JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --jhc-red: #8a3033; --jhc-white: #ffffff; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-header { background: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); color: white; padding: 60px 0; border-radius: 0 0 40px 40px; }
        .doctor-card { border: none; border-radius: 20px; transition: 0.3s; overflow: hidden; background: white; padding: 25px; }
        .doctor-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .img-wrapper { width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; padding: 5px; background: #fceeee; }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid #fff; }
        .btn-jhc { background: #8a3033; color: white; border-radius: 50px; font-weight: 600; transition: 0.3s; }
        .btn-jhc:hover { background: #bd3030; color: white; transform: scale(1.05); }
    </style>
</head>
<body>

<header class="hero-header text-center shadow-sm">
    <div class="container">
        <a href="index.php" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
            <i class="fas fa-chevron-left"></i> Kembali ke Beranda
        </a>
        <h1 class="fw-bold">Poliklinik <?= htmlspecialchars($dept_id); ?></h1>
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
                        <button class="btn btn-jhc btn-sm w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalDetail" 
                            data-name="<?= htmlspecialchars($doc['name']); ?>"
                            data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                            data-desc="<?= htmlspecialchars($doc['description']); ?>"
                            data-schedule="<?= htmlspecialchars($doc['schedule']); ?>">
                            Lihat Profil
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-user-md fa-4 text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">Belum ada dokter yang terdaftar di poliklinik ini.</h5>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 25px;">
            <div class="modal-body p-4 text-center">
                <img id="mdl-img" src="" class="rounded-circle border border-5 border-white shadow-sm mb-3" style="width: 140px; height: 140px; object-fit: cover; margin-top: -80px; background: white;">
                <h4 id="mdl-name" class="fw-bold"></h4>
                <hr>
                <div class="text-start">
                    <h6 class="fw-bold"><i class="fas fa-user-md me-2 text-danger"></i>Tentang:</h6>
                    <p id="mdl-desc" class="small text-muted"></p>
                    <h6 class="fw-bold"><i class="fas fa-calendar-alt me-2 text-danger"></i>Jadwal Praktik:</h6>
                    <p id="mdl-schedule" class="small text-dark fw-bold"></p>
                </div>
                <button type="button" class="btn btn-secondary rounded-pill w-100 mt-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modalDetail = document.getElementById('modalDetail');
    modalDetail.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('mdl-name').innerText = button.getAttribute('data-name');
        document.getElementById('mdl-desc').innerText = button.getAttribute('data-desc') || 'Biodata tidak tersedia.';
        document.getElementById('mdl-schedule').innerText = button.getAttribute('data-schedule') || 'Jadwal belum diatur.';
        document.getElementById('mdl-img').src = button.getAttribute('data-img');
    });
</script>
</body>
</html>