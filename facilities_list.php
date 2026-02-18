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
    <style>
        .hero-category { background: #8a3033; color: white; padding: 60px 0; border-radius: 0 0 40px 40px; }
        .item-card { border: none; border-radius: 20px; overflow: hidden; transition: 0.3s; background: white; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .item-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .item-img { height: 250px; width: 100%; object-fit: cover; }
    </style>
</head>
<body class="bg-light">

<header class="hero-category text-center">
    <div class="container">
        <a href="index.php#facilities" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
            <i class="fas fa-chevron-left"></i> Kembali
        </a>
        <h1 class="fw-bold display-5"><?= htmlspecialchars($category); ?></h1>
    </div>
</header>

<main class="container py-5">
    <div class="row g-4">
        <?php if (!empty($facilities_list)): ?>
            <?php foreach($facilities_list as $f): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card item-card">
                        <?php if(!empty($f['image_path'])): ?>
                            <img src="public/<?= htmlspecialchars($f['image_path']); ?>" class="item-img" alt="Gambar Fasilitas">
                        <?php endif; ?>
                        <div class="card-body p-4">
                            <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($f['name']); ?></h4>
                            <p class="text-muted small"><?= nl2br(htmlspecialchars($f['description'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">Belum ada detail fasilitas untuk kategori ini.</h4>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>