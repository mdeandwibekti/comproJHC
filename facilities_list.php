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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?= htmlspecialchars($category); ?> - RS JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --jhc-red: #8a3033; 
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
            --shadow-hover: 0 15px 35px rgba(138, 48, 51, 0.15);
        }
        
        body { 
            background-color: #f8f9fa; 
            font-family: 'Inter', sans-serif; 
            color: #444; 
        }

        /* --- HERO HEADER (Sama dengan doctors.php) --- */
        .hero-header { 
            background: var(--jhc-gradient); 
            color: white; 
            padding: 60px 0 80px; 
            border-radius: 0 0 40px 40px;
            margin-bottom: -40px;
            position: relative;
            z-index: 1;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .btn-back:hover { background: rgba(255,255,255,0.3); color: white; }

        /* --- CARD STYLE --- */
        .facility-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .facility-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .card-img-wrapper {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .facility-card:hover .card-img-wrapper img {
            transform: scale(1.1);
        }

        .card-body {
            padding: 25px 20px;
            text-align: center;
        }

        .btn-view-detail {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--jhc-red);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
        }
        
        /* --- MODAL STYLE --- */
        .modal-content {
            border-radius: 25px;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: var(--jhc-gradient);
            color: white;
            border: none;
            padding: 20px 30px;
        }

        .modal-body img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<header class="hero-header text-center">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="index.php#facilities" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <h2 class="fw-bold mb-1">Fasilitas <?= htmlspecialchars($category); ?></h2>
        <p class="opacity-75 small">Layanan penunjang medis modern dan lengkap.</p>
    </div>
</header>

<main class="container py-5">
    <div class="row g-4">
        <?php if (!empty($facilities_list)): ?>
            <?php foreach($facilities_list as $f): ?>
                <div class="col-6 col-md-6 col-lg-4">
                    <div class="facility-card h-100" 
                         data-bs-toggle="modal" 
                         data-bs-target="#facilityModal"
                         data-name="<?= htmlspecialchars($f['name']); ?>"
                         data-desc="<?= nl2br(htmlspecialchars($f['description'])); ?>"
                         data-img="public/<?= htmlspecialchars($f['image_path']); ?>">
                        
                        <div class="card-img-wrapper">
                            <?php if(!empty($f['image_path'])): ?>
                                <img src="public/<?= htmlspecialchars($f['image_path']); ?>" alt="<?= htmlspecialchars($f['name']); ?>" onerror="this.src='assets/img/gallery/gallery-1.jpg';">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="fw-bold text-dark mb-2" style="font-size: clamp(1rem, 2vw, 1.1rem);">
                                <?= htmlspecialchars($f['name']); ?>
                            </h5>
                            <span class="btn-view-detail">
                                Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white p-5 rounded-4 shadow-sm d-inline-block">
                    <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
                    <h6 class="text-muted fw-bold">Belum ada fasilitas untuk kategori ini.</h6>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="facilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Detail Fasilitas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-md-6">
                        <img src="" id="modalImg" class="img-fluid" alt="Detail Fasilitas" onerror="this.src='assets/img/gallery/gallery-1.jpg';">
                    </div>
                    <div class="col-md-6">
                        <h4 class="fw-bold text-dark mb-3" id="modalName"></h4>
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-danger">
                            <p id="modalDesc" class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 pe-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const facilityModal = document.getElementById('facilityModal');
    facilityModal.addEventListener('show.bs.modal', function (event) {
        const card = event.relatedTarget;
        
        // Ambil Data
        const name = card.getAttribute('data-name');
        const desc = card.getAttribute('data-desc');
        const img = card.getAttribute('data-img');

        // Isi Modal
        document.getElementById('modalTitle').textContent = name; // Judul Modal = Nama Fasilitas
        document.getElementById('modalName').textContent = name;
        document.getElementById('modalDesc').innerHTML = desc || 'Deskripsi belum tersedia.';
        
        const imgEl = document.getElementById('modalImg');
        imgEl.src = img;
    });
</script>

</body>
</html>