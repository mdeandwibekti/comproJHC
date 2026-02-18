<?php
require_once 'config.php';

// Menangkap parameter kategori dari URL
$category = isset($_GET['category']) ? $_GET['category'] : '';
$facilities_list = [];

if (!empty($category)) {
    // Query untuk mengambil data berdasarkan kolom 'category'
    $stmt = $mysqli->prepare("SELECT * FROM facilities WHERE category = ? ORDER BY display_order ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $facilities_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail <?= htmlspecialchars($category); ?> - RS JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --jhc-red: #8a3033; }
        .hero-category { background: var(--jhc-red); color: white; padding: 60px 0; border-radius: 0 0 40px 40px; }
        .item-card { border: none; border-radius: 20px; overflow: hidden; transition: 0.3s; background: white; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); cursor: pointer; }
        .item-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .item-img { height: 250px; width: 100%; object-fit: cover; }
        
        /* Styling Modal */
        .modal-content { border-radius: 25px; border: none; overflow: hidden; }
        .modal-header { background: var(--jhc-red); color: white; border: none; }
        .btn-close-white { filter: brightness(0) invert(1); }
        .modal-body img { border-radius: 15px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">

<header class="hero-category text-center">
    <div class="container">
        <a href="index.php#facilities" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
            <i class="fas fa-chevron-left"></i> Kembali ke Beranda
        </a>
        <h1 class="fw-bold display-5"><?= htmlspecialchars($category); ?></h1>
        <p class="opacity-75">Klik pada salah satu fasilitas untuk melihat informasi lengkap</p>
    </div>
</header>

<main class="container py-5">
    <div class="row g-4">
        <?php if (!empty($facilities_list)): ?>
            <?php foreach($facilities_list as $idx => $f): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card item-card" 
                         data-bs-toggle="modal" 
                         data-bs-target="#facilityModal"
                         data-name="<?= htmlspecialchars($f['name']); ?>"
                         data-desc="<?= nl2br(htmlspecialchars($f['description'])); ?>"
                         data-img="public/<?= htmlspecialchars($f['image_path']); ?>">
                        
                        <?php if(!empty($f['image_path'])): ?>
                            <img src="public/<?= htmlspecialchars($f['image_path']); ?>" class="item-img" alt="Gambar Fasilitas">
                        <?php endif; ?>
                        
                        <div class="card-body p-4 text-center">
                            <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($f['name']); ?></h4>
                            <p class="text-muted small mb-0">Klik untuk detail <i class="fas fa-arrow-right ms-1"></i></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                <h4 class="text-muted">Belum ada detail fasilitas untuk kategori ini.</h4>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="facilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Nama Fasilitas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <img src="" id="modalImg" class="img-fluid w-100" alt="Detail Gambar">
                    </div>
                    <div class="col-md-6">
                        <h5 class="fw-bold text-dark mb-3">Deskripsi & Fasilitas</h5>
                        <div id="modalDesc" class="text-muted" style="line-height: 1.7; font-size: 0.95rem;">
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Script untuk mengisi data modal secara dinamis
    const facilityModal = document.getElementById('facilityModal');
    facilityModal.addEventListener('show.bs.modal', function (event) {
        // Elemen yang diklik
        const card = event.relatedTarget;
        
        // Ambil data dari atribut data-*
        const name = card.getAttribute('data-name');
        const desc = card.getAttribute('data-desc');
        const img = card.getAttribute('data-img');

        // Update konten modal
        document.getElementById('modalTitle').textContent = name;
        document.getElementById('modalDesc').innerHTML = desc;
        document.getElementById('modalImg').src = img;
    });
</script>

</body>
</html>