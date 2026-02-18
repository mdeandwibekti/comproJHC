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
        
        /* --- HERO SECTION --- */
        .hero-header { 
            background: var(--jhc-gradient); 
            color: white; 
            padding: 60px 0 80px; 
            border-radius: 0 0 40px 40px;
            margin-bottom: -40px; /* Agar kartu overlap sedikit */
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

        /* --- DOCTOR CARD --- */
        .doctor-card { 
            border: none; 
            border-radius: 20px; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            background: white; 
            padding: 25px 20px;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow-soft);
        }
        .doctor-card:hover { 
            transform: translateY(-10px); 
            box-shadow: var(--shadow-hover); 
        }

        .img-wrapper { 
            width: 110px; 
            height: 110px; 
            margin: 0 auto 15px; 
            border-radius: 50%; 
            padding: 4px; 
            background: #fff; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
            border: 1px solid #eee;
        }
        .img-wrapper img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 50%; 
        }

        .btn-detail { 
            border: 1px solid #e0e0e0; 
            color: #555; 
            border-radius: 50px; 
            font-weight: 600; 
            font-size: 0.85rem;
            transition: 0.3s;
        }
        .btn-detail:hover { 
            background: var(--jhc-red); 
            color: white; 
            border-color: var(--jhc-red); 
        }

        /* --- MODAL MODERN STYLE --- */
        .modal-content { 
            border-radius: 25px; 
            border: none; 
            overflow: visible; /* Penting agar foto bisa keluar */
        }
        
        .modal-header-custom {
            background: var(--jhc-gradient);
            height: 100px;
            border-radius: 25px 25px 0 0;
            position: relative;
        }

        .modal-profile-img {
            width: 140px; height: 140px;
            object-fit: cover; 
            border-radius: 50%;
            border: 5px solid #fff;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -70px; /* Membuat gambar setengah di header, setengah di body */
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            background: #fff;
        }

        .modal-body { 
            padding-top: 80px; /* Memberi ruang untuk foto */
            padding-bottom: 30px;
            text-align: center;
        }

        .info-box {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            text-align: left;
            border-left: 4px solid var(--jhc-red);
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 5px;
            display: block;
        }

        .btn-booking {
            background: var(--jhc-gradient);
            color: white;
            border-radius: 50px;
            font-weight: 700;
            padding: 12px;
            box-shadow: 0 5px 15px rgba(138, 48, 51, 0.3);
            transition: 0.3s;
        }
        .btn-booking:hover { transform: translateY(-2px); color: white; }

    </style>
</head>
<body>

<header class="hero-header text-center">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($dept_name); ?></h2>
        <p class="opacity-75 small">Pilih dokter spesialis Anda dan buat janji temu sekarang.</p>
    </div>
</header>

<main class="container py-5">
    <div class="row g-3 g-md-4 justify-content-center">
        <?php if (!empty($doctors_data)): ?>
            <?php foreach($doctors_data as $doc): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card doctor-card h-100">
                        <div class="img-wrapper">
                            <img src="public/<?= htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/default-doctor.png'); ?>" 
                                 alt="<?= htmlspecialchars($doc['name']); ?>"
                                 onerror="this.src='assets/img/default-doctor.png';">
                        </div>
                        
                        <div class="text-center flex-grow-1 d-flex flex-column">
                            <h6 class="fw-bold mb-1 text-dark text-truncate px-1" title="<?= htmlspecialchars($doc['name']); ?>">
                                <?= htmlspecialchars($doc['name']); ?>
                            </h6>
                            <p class="text-danger small fw-bold text-uppercase mb-3" style="font-size: 0.65rem;">
                                <?= htmlspecialchars($doc['specialty']); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <button class="btn btn-detail w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetail" 
                                        data-id="<?= $doc['id']; ?>"
                                        data-name="<?= htmlspecialchars($doc['name']); ?>"
                                        data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                                        data-desc="<?= htmlspecialchars($doc['description']); ?>"
                                        data-schedule="<?= htmlspecialchars($doc['schedule']); ?>">
                                    Lihat Profil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white p-5 rounded-4 shadow-sm d-inline-block">
                    <i class="fas fa-user-md fa-3x text-muted mb-3 opacity-50"></i>
                    <h6 class="text-muted fw-bold">Belum ada data dokter.</h6>
                    <p class="small text-muted mb-0">Silakan hubungi customer service kami.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="mdl-img" src="" class="modal-profile-img" onerror="this.src='assets/img/default-doctor.png';">
            </div>

            <div class="modal-body">
                <h4 id="mdl-name" class="fw-bold text-dark mb-1"></h4>
                <span class="badge bg-light text-danger border border-danger-subtle rounded-pill px-3">Dokter Spesialis</span>
                
                <div class="info-box">
                    <div class="mb-3">
                        <span class="info-label"><i class="fas fa-user me-1"></i> Profil Singkat</span>
                        <p id="mdl-desc" class="small text-muted mb-0" style="line-height: 1.6;">-</p>
                    </div>
                    <div>
                        <span class="info-label"><i class="far fa-calendar-alt me-1"></i> Jadwal Praktik</span>
                        <p id="mdl-schedule" class="small fw-bold text-dark mb-0">-</p>
                    </div>
                </div>

                <div class="row mt-4 g-2">
                    <div class="col-4">
                        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-bold" data-bs-dismiss="modal">Tutup</button>
                    </div>
                    <div class="col-8">
                        <a href="" id="mdl-booking-link" class="btn btn-booking w-100">
                            <i class="fas fa-calendar-check me-2"></i> Buat Janji
                        </a>
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
        // Tombol yang memicu modal
        const button = event.relatedTarget;
        
        // Ambil data dari atribut data-*
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const img = button.getAttribute('data-img');
        const desc = button.getAttribute('data-desc');
        const schedule = button.getAttribute('data-schedule');
        
        // Isi data ke dalam modal
        document.getElementById('mdl-name').innerText = name;
        document.getElementById('mdl-desc').innerText = desc || 'Informasi profil belum tersedia.';
        document.getElementById('mdl-schedule').innerText = schedule || 'Hubungi RS untuk jadwal.';
        
        // Handle gambar error
        const imgEl = document.getElementById('mdl-img');
        imgEl.src = img;
        imgEl.onerror = function() { this.src = 'assets/img/default-doctor.png'; };
        
        // Set link booking
        document.getElementById('mdl-booking-link').href = 'booking.php?doctor_id=' + id;
    });
</script>

</body>
</html>