<?php 
// 1. Inisialisasi Judul Halaman
$page_title = "Jadwal Praktik Dokter | RS JHC Tasikmalaya";

// 2. Logika Koneksi Database yang Lebih Kuat
$config_path = file_exists("config.php") ? "config.php" : (file_exists("../config.php") ? "../config.php" : "../../config.php");
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Error: File konfigurasi tidak ditemukan.");
}

// 3. Fetch Data Dokter dengan Nama Departemen
$doctors_schedule = [];
$sql = "SELECT d.name, d.specialty, d.schedule, d.photo_path, dep.name as dept_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY d.name ASC";
$result = $mysqli->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $doctors_schedule[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <link href="public/assets/css/theme.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
            --bg-soft: #f4f7f6;
        }

        body { background-color: var(--bg-soft); font-family: 'Inter', sans-serif; }

        /* Navbar Styling */
        .navbar-jhc { background: white; border-bottom: 3px solid var(--jhc-red-dark); }
        .btn-back { color: var(--jhc-red-dark); font-weight: 700; transition: 0.3s; }
        .btn-back:hover { color: var(--jhc-red-light); transform: translateX(-5px); }

        /* Header Section */
        .page-header { padding: 60px 0; background: white; margin-bottom: -50px; }
        .title-line { width: 80px; height: 4px; background: var(--jhc-gradient); margin: 15px auto; border-radius: 10px; }

        /* Card Wrapper bergaya Neumorphism */
        .schedule-wrapper {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            padding: 20px;
            border: 1px solid rgba(0,0,0,0.02);
            overflow: hidden;
        }

        /* Table Styling */
        .table thead th {
            background-color: #f8f9fa;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 20px;
            border: none;
        }

        .table tbody td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f1f1; }
        .doc-name { color: var(--jhc-red-dark); font-weight: 700; margin-bottom: 2px; }
        .specialty-badge { 
            background: #fff5f5; color: var(--jhc-red-dark); 
            border-radius: 50px; padding: 4px 15px; font-size: 0.75rem; font-weight: 600;
        }

        /* Tombol Daftar Gradasi */
        .btn-register {
            background: var(--jhc-gradient);
            color: white !important;
            border-radius: 50px;
            padding: 8px 25px;
            font-weight: 700;
            font-size: 0.85rem;
            border: none;
            box-shadow: 0 4px 12px rgba(138, 48, 51, 0.2);
            transition: 0.3s;
        }
        .btn-register:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(138, 48, 51, 0.3); }
        
        .doctor-img { border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-jhc sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand btn-back" href="index.php">
                <i class="fas fa-chevron-left me-2"></i> KEMBALI
            </a>
            <img src="public/assets/img/logo.png" height="40" alt="Logo JHC" onerror="this.style.display='none'">
        </div>
    </nav>

    <header class="page-header text-center">
        <div class="container">
            <h1 class="fw-bold text-dark">JADWAL PRAKTIK DOKTER</h1>
            <div class="title-line"></div>
            <p class="text-muted">Pusat Pelayanan Jantung Terpadu & Spesialisasi Medis RS JHC Tasikmalaya</p>
        </div>
    </header>

    <div class="container pb-5">
        <div class="schedule-wrapper">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Identitas Dokter</th>
                            <th>Layanan / Poli</th>
                            <th>Hari & Jam Praktik</th>
                            <th class="text-center">Registrasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($doctors_schedule)): ?>
                            <?php foreach($doctors_schedule as $doc): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $img = !empty($doc['photo_path']) ? 'public/' . $doc['photo_path'] : 'public/assets/img/gallery/default-doc.png';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" class="rounded-circle me-3 doctor-img" width="60" height="60" style="object-fit: cover;" onerror="this.src='public/assets/img/gallery/default-doc.png'">
                                        <div>
                                            <div class="doc-name"><?php echo htmlspecialchars($doc['name']); ?></div>
                                            <small class="text-muted"><i class="fas fa-id-card me-1"></i> STR Terverifikasi</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="specialty-badge"><?php echo htmlspecialchars($doc['specialty']); ?></span>
                                    <?php if($doc['dept_name']): ?>
                                        <div class="mt-1 small text-secondary fw-bold"><i class="fas fa-hospital-alt me-1"></i> <?php echo htmlspecialchars($doc['dept_name']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-dark">
                                    <div class="d-flex align-items-start">
                                        <i class="far fa-clock mt-1 me-2 text-danger"></i>
                                        <div>
                                            <?php 
                                            if (!empty($doc['schedule'])) {
                                                echo nl2br(htmlspecialchars($doc['schedule']));
                                            } else {
                                                echo "<span class='text-muted fst-italic'>Konfirmasi via WhatsApp</span>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $wa_msg = urlencode("Halo RS JHC Tasikmalaya, saya ingin mendaftar untuk pemeriksaan dengan " . $doc['name'] . " pada jadwal terdekat. Mohon info ketersediaan kuotanya.");
                                    ?>
                                    <a href="https://api.whatsapp.com/send?phone=6287760615300&text=<?php echo $wa_msg; ?>" target="_blank" class="btn btn-register">
                                        <i class="fab fa-whatsapp me-1"></i> DAFTAR
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-user-clock fa-3x mb-3 text-muted opacity-25"></i>
                                    <p class="text-muted">Mohon maaf, data jadwal belum tersedia saat ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <div class="alert alert-info border-0 shadow-sm d-inline-block px-4 py-2 rounded-pill">
                <i class="fas fa-info-circle me-2"></i> Jadwal dapat berubah sewaktu-waktu sesuai kebijakan rumah sakit.
            </div>
        </div>
    </div>

    <script src="public/vendors/bootstrap/bootstrap.min.js"></script>
</body>
</html>