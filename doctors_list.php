<?php
require_once 'config.php';

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
$doctors_data = [];
$dept_name = "Tim Dokter Spesialis";
$is_layanan = false;

if ($dept_id > 0) {
    // Ambil data departemen termasuk kategorinya
    $stmt_dept = $mysqli->prepare("SELECT name, category FROM departments WHERE id = ?");
    $stmt_dept->bind_param("i", $dept_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result()->fetch_assoc();
    
    if($res_dept) {
        if ($res_dept['category'] == 'Layanan') {
            $dept_name = $res_dept['name'];
            $is_layanan = true;
        } else {
            $dept_name = "" . $res_dept['name'];
        }
    }

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --jhc-red: #8a3033; 
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --shadow-deep: 0 20px 40px rgba(0,0,0,0.08);
            --shadow-hover: 0 25px 50px rgba(138, 48, 51, 0.18);
        }
        
        body { 
            background-color: #f4f7f9; 
            font-family: 'Inter', sans-serif; 
            color: #334155; 
        }
        
        /* Header Ramping & Elegan */
        .hero-header { 
            background: var(--jhc-gradient); 
            color: white; 
            padding: 40px 0 60px; 
            border-radius: 0 0 35px 35px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(138, 48, 51, 0.2);
        }

        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border-radius: 12px;
            padding: 8px 18px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-back:hover { background: white; color: var(--jhc-red); }

        /* Card yang Lebih Terlihat & Menonjol */
        .doctor-card { 
            border: 1px solid rgba(0,0,0,0.03); 
            border-radius: 24px; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            background: white; 
            padding: 30px 20px;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow-deep);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .doctor-card:hover { 
            transform: translateY(-12px); 
            box-shadow: var(--shadow-hover);
            border-color: rgba(138, 48, 51, 0.15);
        }

        /* Foto Profil Bulat Sempurna */
        .img-wrapper { 
            width: 130px; 
            height: 130px; 
            margin: 0 auto 20px; 
            border-radius: 50%; 
            padding: 5px; 
            background: #fff; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }
        .img-wrapper img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 50%; 
            transition: 0.5s;
        }
        .doctor-card:hover img { transform: scale(1.1); }

        .doc-name {
            font-weight: 800;
            font-size: 1.1rem;
            color: #0f172a;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .doc-spec {
            background: #fff5f5;
            color: var(--jhc-red);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 15px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-detail { 
            background: #f8fafc;
            border: 1.5px solid #e2e8f0; 
            color: #64748b; 
            border-radius: 15px; 
            font-weight: 700; 
            font-size: 0.85rem;
            padding: 12px;
            transition: 0.3s;
            margin-top: auto;
        }
        .btn-detail:hover { 
            background: var(--jhc-red); 
            color: white; 
            border-color: var(--jhc-red);
            box-shadow: 0 8px 15px rgba(138, 48, 51, 0.2);
        }

        /* Modal Styling Premium */
        .modal-content { border-radius: 30px; border: none; overflow: hidden; }
        .modal-header-custom { background: var(--jhc-gradient); height: 110px; position: relative; }
        .modal-profile-img {
            width: 150px; height: 150px; object-fit: cover; 
            border-radius: 50%; border: 6px solid #fff;
            position: absolute; left: 50%; transform: translateX(-50%);
            bottom: -75px; box-shadow: 0 15px 30px rgba(0,0,0,0.15); background: #fff;
        }
        .modal-body { padding: 95px 30px 40px; text-align: center; }

        .info-box {
            background-color: #f8fafc; border-radius: 20px;
            padding: 25px; margin: 25px 0; text-align: left; border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.2px; color: #94a3b8; margin-bottom: 8px; display: flex; align-items: center;
        }
        .info-label i { color: var(--jhc-red); margin-right: 8px; }

        .btn-booking {
            /* Gradasi Hijau WhatsApp yang lebih premium */
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: white !important;
            border-radius: 20px; /* Sedikit lebih bulat */
            font-weight: 800; /* Lebih tebal */
            padding: 18px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            /* Bayangan lebih menyebar */
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.4);
            /* Animasi denyut halus agar menarik perhatian mata */
            animation: pulse-green 2s infinite;
        }

        .btn-booking:hover {
            transform: translateY(-5px) scale(1.02); /* Naik lebih tinggi saat hover */
            box-shadow: 0 15px 35px rgba(37, 211, 102, 0.5);
            filter: brightness(1.1);
        }

        .btn-booking i {
            font-size: 1.4rem;
            margin-right: 10px;
        }

        /* Keyframes untuk efek denyut (Pulse) */
        @keyframes pulse-green {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }

        /* Efek cahaya kilat (Shine) yang lewat sesekali */
        .btn-booking::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: none;
            animation: shine 4s infinite;
        }

        @keyframes shine {
            0% { left: -60%; }
            20% { left: 120%; }
            100% { left: 120%; }
        }

        /* Gaya baru untuk tombol Tutup Jendela */
        .btn-close-modal {
            background: #ffffff;
            color: #64748b !important; /* Warna abu-abu slate yang elegan */
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-weight: 700;
            padding: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-close-modal:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .btn-close-modal i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

    </style>
</head>
<body>

<header class="hero-header">
    <div class="container">
        <div class="d-flex justify-content-start mb-3">
            <a href="index.php" class="btn-back">
                <i class="fas fa-chevron-left me-2"></i>Kembali
            </a>
        </div>
        <div class="text-center">
            <h1 style="font-weight: 800; letter-spacing: -1px;"><?= htmlspecialchars($dept_name); ?></h1>
            <p class="mb-0 opacity-80 small">Daftar pakar kesehatan dan jadwal praktik mitra Anda.</p>
        </div>
    </div>
</header>

<main class="container" style="margin-top: 40px; padding-bottom: 80px;">
    <div class="row g-4 justify-content-center">
        <?php if (!empty($doctors_data)): ?>
            <?php foreach($doctors_data as $doc): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card doctor-card">
                        <div class="img-wrapper text-center">
                            <img src="public/<?= htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/default-doctor.png'); ?>" 
                                 alt="<?= htmlspecialchars($doc['name']); ?>"
                                 onerror="this.src='assets/img/default-doctor.png';">
                        </div>
                        
                        <div class="text-center flex-grow-1 d-flex flex-column">
                            <h6 class="doc-name"><?= htmlspecialchars($doc['name']); ?></h6>
                            <div>
                                <span class="doc-spec"><?= htmlspecialchars($doc['specialty']); ?></span>
                            </div>
                            
                            <button class="btn btn-detail w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalDetail" 
                                    data-id="<?= $doc['id']; ?>"
                                    data-name="<?= htmlspecialchars($doc['name']); ?>"
                                    data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                                    data-desc="<?= htmlspecialchars($doc['description']); ?>"
                                    data-schedule="<?= htmlspecialchars($doc['schedule']); ?>"
                                    data-category="<?= htmlspecialchars($res_dept['category'] ?? ''); ?>">
                                Lihat Profil Dokter
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white p-5 rounded-5 shadow-sm d-inline-block">
                    <i class="fas fa-user-md fa-4x text-muted opacity-20 mb-3"></i>
                    <h5 class="fw-bold text-dark">Data Dokter Belum Tersedia</h5>
                    <p class="text-muted small">Informasi sedang diperbarui oleh tim admin kami.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="mdl-img" src="" class="modal-profile-img" onerror="this.src='assets/img/default-doctor.png';">
            </div>

            <div class="modal-body">
                <h3 id="mdl-name" class="fw-800 text-dark mb-1" style="font-weight: 800;"></h3>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-4 py-2 fw-bold">Dokter Spesialis</span>
                
                <div class="info-box">
                    <div class="mb-4">
                        <span class="info-label"><i class="fas fa-user-circle"></i> Profil Singkat</span>
                        <p id="mdl-desc" class="small text-muted mb-0" style="line-height: 1.6;">-</p>
                    </div>
                    <div>
                        <span class="info-label"><i class="far fa-calendar-alt"></i> Jadwal Praktik</span>
                        <p id="mdl-schedule" class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">-</p>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <a href="" id="mdl-booking-link" target="_blank" class="btn btn-booking">
                        <i class="fab fa-whatsapp me-2"></i>Buat Janji via WhatsApp
                    </a>
                    <button type="button" class="btn btn-close-modal" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle"></i>Tutup Jendela
                    </button>                
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
        
        const name = button.getAttribute('data-name');
        const img = button.getAttribute('data-img');
        const desc = button.getAttribute('data-desc');
        const schedule = button.getAttribute('data-schedule');
        const category = button.getAttribute('data-category');
        
        const headerTitle = document.querySelector('.hero-header h1').innerText;
        const labelType = (category === 'Layanan') ? 'Layanan' : 'Layanan/Poli';
        
        document.getElementById('mdl-name').innerText = name;
        document.getElementById('mdl-desc').innerText = desc || 'Informasi profil belum tersedia.';
        document.getElementById('mdl-schedule').innerText = schedule || 'Hubungi RS untuk jadwal.';
        
        const imgEl = document.getElementById('mdl-img');
        imgEl.src = img;
        imgEl.onerror = function() { this.src = 'assets/img/default-doctor.png'; };
        
        const phoneNumber = "6285175000375";
        const message = `Halo RS JHC, saya ingin membuat janji temu.%0A%0A` +
                        `*Data Pendaftaran:*%0A` +
                        `- Nama Pasien: %0A` +
                        `- No. Telepon: %0A` +
                        `- ${labelType}: ${headerTitle}%0A` +
                        `- Dokter: ${name}%0A` +
                        `- Jadwal/Jam: ${schedule}%0A%0A` +
                        `Mohon konfirmasi pendaftaran saya. Terima kasih.`;
        
        const waLink = `https://api.whatsapp.com/send/?phone=${phoneNumber}&text=${message}`;
        document.getElementById('mdl-booking-link').href = waLink;
    });
</script>

</body>
</html>