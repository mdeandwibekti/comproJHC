<?php 
$page_title = "Rs JHC Tasikmalaya | Home";

if (file_exists("config.php")) {
    require_once "config.php";
} else {
    // Fallback jika config ada di folder parent
    if (file_exists("../config.php")) require_once "../config.php";
}

$appointment_status = "";
$appointment_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_appointment'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']); 
    $category = trim($_POST['category'] ?? '-');

    $final_message = "[Kategori: $category] " . $message;

    if (!empty($name) && !empty($phone)) {
        $stmt = $mysqli->prepare("INSERT INTO appointments (name, phone, email, message, status) VALUES (?, ?, ?, ?, 'new')");
        $stmt->bind_param("ssss", $name, $phone, $email, $final_message);
        
        if ($stmt->execute()) {
            $appointment_status = "success";
            $appointment_message = "Permintaan janji temu berhasil dikirim! Tim kami akan segera menghubungi Anda.";
        } else {
            $appointment_status = "danger";
            $appointment_message = "Gagal mengirim data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $appointment_status = "warning";
        $appointment_message = "Nama dan Nomor Telepon wajib diisi.";
    }
}

// --- FETCH DATA ---

$settings = [];
$set_result = $mysqli->query("SELECT * FROM settings2");
if ($set_result) { 
    while($row = $set_result->fetch_assoc()) { 
        $settings[$row['setting_key']] = $row['setting_value']; 
    } 
}

$banners_data = [];
$banner_result = $mysqli->query("SELECT image_path, title, description FROM banners ORDER BY display_order ASC");
if ($banner_result) { while($row = $banner_result->fetch_assoc()) { $banners_data[] = $row; } }

$layanan_data = [];
$poliklinik_data = [];

$dept_result = $mysqli->query("SELECT id, name, category, icon_path, icon_hover_path FROM departments ORDER BY display_order ASC");

if ($dept_result) { 
    while($row = $dept_result->fetch_assoc()) { 
        if ($row['category'] == 'Layanan') {
            $layanan_data[] = $row;
        } else {
            $poliklinik_data[] = $row;
        }
    } 
}
$doctors_data = [];
$doc_result = $mysqli->query("SELECT * FROM doctors WHERE is_featured = 1 ORDER BY id ASC");
if ($doc_result) { while($row = $doc_result->fetch_assoc()) { $doctors_data[] = $row; } }

$news_data = [];
$news_result = $mysqli->query("SELECT * FROM news ORDER BY post_date DESC LIMIT 3");
if ($news_result) { while($row = $news_result->fetch_assoc()) { $news_data[] = $row; } }

$careers_data = [];
$career_result = $mysqli->query("SELECT * FROM careers WHERE status = 'open' ORDER BY post_date DESC");
if ($career_result) { while($row = $career_result->fetch_assoc()) { $careers_data[] = $row; } }

$vr_data = null;
$vr_result = $mysqli->query("SELECT title, content, image_path_360 FROM page_virtual_room WHERE id = 1");
if ($vr_result && $vr_result->num_rows > 0) { $vr_data = $vr_result->fetch_assoc(); }

$mcu_packages_data = [];
$mcu_result = $mysqli->query("SELECT * FROM mcu_packages ORDER BY display_order ASC"); 
if ($mcu_result) { while($row = $mcu_result->fetch_assoc()) { $mcu_packages_data[] = $row; } }

$partners_data = [];
$partners_result = $mysqli->query("SELECT * FROM partners ORDER BY name ASC");
if ($partners_result) { while($row = $partners_result->fetch_assoc()) { $partners_data[] = $row; } }

$facilities_data = [];
$fac_result = $mysqli->query("SELECT * FROM facilities ORDER BY display_order ASC");
if ($fac_result) { while($row = $fac_result->fetch_assoc()) { $facilities_data[] = $row; } }

?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>

    <?php 
    $favicon = !empty($settings['favicon_path']) ? $settings['favicon_path'] : 'assets/img/favicons/favicon.ico';
    ?>
    <link rel="shortcut icon" type="image/x-icon" href="public/<?php echo htmlspecialchars($favicon); ?>">
    <link href="public/assets/css/theme.css" rel="stylesheet" />
    <link href="public/vendors/fontawesome/all.min.js" rel="stylesheet" />

<style>
    :root {
        --jhc-red: #C8102E;
        --jhc-navy: #002855;
        --jhc-light: #F8F9FA;
    }

    /* Navbar */
    .navbar {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        min-height: 70px; 
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    /* Efek saat Navbar di-scroll (Jika menggunakan JS bawaan theme Anda) */
    .navbar.navbar-scrolled {
        padding: 10px 0;
        border-bottom: 3px solid var(--jhc-red);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

   .navbar .container {
        max-width: 1200px;
        padding-left: 10px; /* Memberikan jarak dari pinggir kiri */
        padding-right: 1px;
    }

  /* Mengatur ukuran Logo JHC */
    .navbar-brand img {
        height: 80px; 
        width: auto;  
        transition: transform 0.3s ease;
        object-fit: contain;
    }

    .navbar-brand:hover img {
        transform: scale(1.05); 
    }

    /* Menu Animasi */
    .nav-link {
        color: var(--jhc-navy) !important;
        font-weight: 700;
        position: relative;
        margin: 0 5px;
    }
    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: var(--jhc-red);
        transition: width 0.3s ease;
    }
    .nav-link:hover::after {
        width: 100%;
    }

    .navbar-nav .nav-link {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }

    .navbar-brand img {
        transition: all 0.3s ease-in-out;
        filter: drop-shadow(0px 0px 0px rgba(200, 16, 46, 0));
    }

    .navbar-brand:hover img {
        transform: scale(1.1) rotate(-2deg); /* Logo membesar sedikit dan miring */
        filter: drop-shadow(0px 4px 8px rgba(200, 16, 46, 0.3)); /* Memberikan glow merah halus */
    }

    /* Navbar yang berubah warna saat di-scroll */
    .navbar.navbar-scrolled {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 25px rgba(0,0,0,0.1);
    }

    /* Tombol Janji Temu Interaktif */
    .btn-janji {
        background: var(--jhc-red);
        color: white !important;
        border-radius: 50px;
        padding: 8px 20px;
        font-size: 0.9rem;
        font-weight: 700;
        border: 2px solid var(--jhc-red);
        transition: all 0.3s ease;
    } 
    .btn-janji:hover {
        background: transparent;
        color: var(--jhc-red) !important;
        box-shadow: 0 5px 15px rgba(200, 16, 46, 0.3);
    }
    
    .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-10px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }

    .banner-overlay {
    /* Ganti 0.9 dan 0.4 menjadi angka lebih kecil */
    background: linear-gradient(to right, rgba(27, 113, 161, 0.5), rgba(45, 59, 72, 0.2));
    position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;
    }
    .hero-content { position: relative; z-index: 2; }

    .doctor-card img { transition: transform 0.5s ease; }
    .doctor-card:hover img { transform: scale(1.05); }

    .partner-logo { filter: grayscale(100%); opacity: 0.7; transition: all 0.3s; }
    .partner-logo:hover { filter: grayscale(0%); opacity: 1; }

    .section-title {
        position: relative; display: inline-block; margin-bottom: 3rem; font-weight: 800; color: var(--secondary-color);
    }
    .section-title::after {
        content: ''; display: block; width: 60px; height: 4px; background: var(--primary-color); margin: 10px auto 0; border-radius: 2px;
    }

    .news-date-badge {
        position: absolute; top: 15px; left: 15px; background: var(--primary-color); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; z-index: 10;
    }
    
    .form-control-modern {
        border-radius: 10px; padding: 15px; border: 1px solid #e0e0e0; background: #f8f9fa;
    }
    .form-control-modern:focus {
        background: #fff; box-shadow: 0 0 0 4px rgba(27, 113, 161, 0.1); border-color: var(--primary-color);
    }
</style>
  </head>
  <body>
    <main class="main" id="top">
      
      <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block" data-navbar-on-scroll="data-navbar-on-scroll">
    <div class="container">
        <a class="navbar-brand" href="index.php">
    <?php 
    // Sesuaikan nama file dengan yang ada di folder public/assets/img/gallery/
    $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png';
    ?>
    <img src="public/<?php echo htmlspecialchars($header_logo); ?>" width="130" alt="JHC Logo" />
</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse border-top border-lg-0 mt-4 mt-lg-0" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto pt-2 pt-lg-0 font-base">
                <li class="nav-item px-2"><a class="nav-link" href="#about">Tentang Kami</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#departments">Layanan</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#doctors">Dokter</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#news">Berita</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#appointment">Kontak</a></li>
            </ul>
            <a class="btn btn-janji order-1 order-lg-0 ms-lg-4" href="#appointment">Janji Temu</a>
        </div>
    </div>
</nav>

      <section class="py-0 position-relative" id="home">
        <div class="bg-holder" style="
            background-image:url(public/<?php echo htmlspecialchars($settings['hero_background_path'] ?? 'assets/img/gallery/JHC2.jpg'); ?>);
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            min-height: 600px;
            width: 100%;">       
            </div>
        <div class="banner-overlay"></div>
        
        <div class="container h-100">
          <div class="row min-vh-100 align-items-center hero-content">
            <div class="col-md-7 text-md-start text-center py-6">
              <span class="badge bg-light text-primary mb-3 px-3 py-2 rounded-pill shadow-sm fw-bold">Selamat Datang di JHC Tasikmalaya</span>
              <h1 class="fw-bold text-white display-4 mb-4" id="banner-title"></h1>
              <p class="fs-1 mb-5 text-white lead" id="banner-description"></p>
              <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                  <a class="btn btn-lg btn-light text-primary fw-bold rounded-pill px-5 shadow-lg" href="#appointment" role="button">Buat Janji Temu</a>
                  <a class="btn btn-lg btn-outline-light rounded-pill px-5" href="#doctors" role="button">Lihat Dokter</a>
              </div>
            </div>
            
            <div class="col-md-5 d-none d-md-block">
                <div id="banner-slider" style="display:none;">
                <?php if (!empty($banners_data)): ?>
                  <?php foreach ($banners_data as $index => $banner): ?>
                    <div class="banner-item" 
                         data-title="<?php echo htmlspecialchars($banner['title']); ?>" 
                         data-description="<?php echo htmlspecialchars($banner['description']); ?>">
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                    <div class="banner-item" 
                         data-title="Pelayanan Kesehatan Terbaik" 
                         data-description="Kami bertekad memberikan pelayanan medis profesional dan sepenuh hati untuk Anda dan keluarga.">
                    </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bannerItems = document.querySelectorAll('.banner-item');
            const bannerTitle = document.getElementById('banner-title');
            const bannerDescription = document.getElementById('banner-description');
            let currentIndex = 0;

            function updateBanner() {
                if(bannerItems.length === 0) return;
                bannerTitle.style.opacity = 0;
                bannerDescription.style.opacity = 0;
                setTimeout(() => {
                    bannerTitle.textContent = bannerItems[currentIndex].dataset.title;
                    bannerDescription.textContent = bannerItems[currentIndex].dataset.description;
                    bannerTitle.style.opacity = 1;
                    bannerDescription.style.opacity = 1;
                    bannerTitle.style.transition = "opacity 0.5s ease-in-out";
                    bannerDescription.style.transition = "opacity 0.5s ease-in-out";
                }, 500);
            }

            if(bannerItems.length > 0) {
                updateBanner();
                setInterval(() => {
                    currentIndex = (currentIndex + 1) % bannerItems.length;
                    updateBanner();
                }, 6000);
            }
        });
      </script>

      <section class="py-5 bg-white" id="departments">
        <div class="container">
            
            <div class="row justify-content-center mb-5">
                <div class="col-12 text-center">
                    <h2 class="section-title">PELAYANAN KAMI</h2>
                    <p class="text-muted">Kami menyediakan berbagai layanan unggulan dan poliklinik spesialis.</p>
                </div>
            </div>

            <?php if (!empty($layanan_data)): ?>
            <div class="mb-5">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <h4 class="fw-bold text-primary"><i class="fas fa-star me-2"></i>LAYANAN UNGGULAN</h4>
                        <hr style="width: 50px; margin: 10px auto; border-top: 3px solid var(--accent-color); opacity: 1;">
                    </div>
                </div>

                <div class="row gx-4 gy-4 justify-content-center">
                    <?php foreach($layanan_data as $layanan): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                        <div class="card h-100 border-0 shadow hover-lift text-center p-4" style="background: #f8faff;">
                            <div class="card-body d-flex flex-column align-items-center">
                                <div class="mb-4 d-flex justify-content-center align-items-center bg-white rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                                    <?php if(!empty($layanan['icon_path'])): ?>
                                        <img src="public/<?php echo htmlspecialchars($layanan['icon_path']); ?>" style="width: 45px; height: 45px; object-fit: contain;" alt="..." />
                                    <?php else: ?>
                                        <i class="fas fa-star fa-2x text-warning"></i>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($layanan['name']); ?></h5>
                                <div class="mt-auto">
                                    <a href="#!" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">Detail Layanan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($poliklinik_data)): ?>
        <div class="mt-5">
            <div class="row mb-4 pt-4">
                <div class="col-12 text-center">
                    <h4 class="fw-bold text-secondary"><i class="fas fa-stethoscope me-2"></i>POLIKLINIK SPESIALIS</h4>
                    <hr style="width: 50px; margin: 10px auto; border-top: 3px solid var(--secondary-color); opacity: 1;">
                </div>
            </div>

            <div class="row gx-4 gy-4 justify-content-center">
                <?php foreach($poliklinik_data as $poli): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <div class="card h-100 border-0 shadow-sm hover-lift text-center p-4">
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="mb-4 d-flex justify-content-center align-items-center bg-light rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                                <?php if(!empty($poli['icon_path'])): ?>
                                    <img src="public/<?php echo htmlspecialchars($poli['icon_path']); ?>" style="width: 45px; height: 45px; object-fit: contain;" alt="..." />
                                <?php else: ?>
                                    <i class="fas fa-heartbeat fa-2x text-primary"></i>
                                <?php endif; ?>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($poli['name']); ?></h5>
                            <div class="mt-auto">
                                <a href="#!" class="btn btn-outline-primary btn-sm rounded-pill px-4">Jadwal Dokter</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

            </div>
        </section>

      <?php if (!empty($mcu_packages_data)): ?>
      <section class="py-5" style="background-color: #f1f7fc;">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 text-center">
                    <h2 class="section-title">PAKET MEDICAL CHECK UP</h2>
                    <p class="text-muted">Pencegahan lebih baik daripada pengobatan.</p>
                </div>
            </div>
            
            <div id="mcuCarousel" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-inner">
                <?php foreach ($mcu_packages_data as $index => $package): ?>
                  <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                    <div class="card border-0 shadow-lg overflow-hidden mx-auto" style="max-width: 900px;">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <img src="public/<?php echo htmlspecialchars($package['image_path']); ?>" class="img-fluid h-100 w-100" style="object-fit: cover; min-height: 300px;" alt="<?php echo htmlspecialchars($package['title']); ?>">
                            </div>
                            <div class="col-md-7 d-flex align-items-center bg-white">
                                <div class="card-body p-5 text-center text-md-start">
                                    <span class="badge bg-warning text-dark mb-2">Populer</span>
                                    <h3 class="card-title fw-bold text-primary mb-3"><?php echo htmlspecialchars($package['title']); ?></h3>
                                    <p class="card-text text-muted mb-4"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                                    <h4 class="text-dark fw-bold mb-4">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></h4>
                                    
                                    <?php
                                    $whatsapp_number = '6287760615300';
                                    $whatsapp_message = urlencode("Halo JHC, saya ingin booking paket MCU: " . $package['title']);
                                    $whatsapp_link = "https://api.whatsapp.com/send?phone={$whatsapp_number}&text={$whatsapp_message}";
                                    ?>
                                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-primary rounded-pill px-4 py-2">
                                        <i class="fab fa-whatsapp me-2"></i> Booking Via WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#mcuCarousel" data-bs-slide="prev" style="width: 5%;">
                <span class="carousel-control-prev-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#mcuCarousel" data-bs-slide="next" style="width: 5%;">
                <span class="carousel-control-next-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
              </button>
            </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($vr_data): ?>
      <section class="py-5" id="about">
        <div class="container">
            <div class="row align-items-center gx-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="position-relative">
                        <div class="bg-primary position-absolute rounded-3" style="width: 100%; height: 100%; top: 15px; left: -15px; z-index: -1;"></div>
                        <?php if(!empty($vr_data['image_path_360'])): ?>
                            <img src="public/<?php echo htmlspecialchars($vr_data['image_path_360']); ?>" class="img-fluid rounded-3 shadow w-100" alt="Virtual Room">
                        <?php else: ?>
                            <img src="public/assets/img/gallery/health-care.png" class="img-fluid rounded-3 shadow w-100" alt="About">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-primary fw-bold text-uppercase">Tentang Kami</h5>
                    <h2 class="fw-bold mb-4 display-6"><?php echo htmlspecialchars($vr_data['title']); ?></h2>
                    <p class="text-secondary lead text-justify mb-4"><?php echo nl2br(htmlspecialchars($vr_data['content'])); ?></p>
                    <div class="d-flex gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-3 text-primary me-3"><i class="fas fa-user-md fa-lg"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Dokter Ahli</h6>
                                <small class="text-muted">Berpengalaman</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-3 text-primary me-3"><i class="fas fa-clock fa-lg"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Layanan 24 Jam</h6>
                                <small class="text-muted">Selalu Siap</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="py-5 bg-white" id="doctors">
         <div class="container">
             <div class="row justify-content-center mb-5">
                 <div class="col-md-8 text-center">
                     <h2 class="section-title">TIM DOKTER KAMI</h2>
                     <p class="text-muted">Ditangani langsung oleh dokter spesialis yang berpengalaman di bidangnya.</p>
                 </div>
             </div>
             
             <div class="row g-4 justify-content-center">
                 <?php foreach($doctors_data as $doc): ?>
                 <div class="col-sm-6 col-lg-3">
                     <div class="card h-100 border-0 shadow-sm hover-lift doctor-card text-center">
                         <div class="card-body p-4">
                             <div class="mx-auto mb-4 position-relative" style="width: 140px; height: 140px;">
                                 <img src="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>" class="w-100 h-100 rounded-circle border border-4 border-light shadow-sm" style="object-fit: cover;" alt="Doctor">
                             </div>
                             <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($doc['name']); ?></h5>
                             <p class="text-primary small fw-bold text-uppercase mb-3"><?php echo htmlspecialchars($doc['specialty']); ?></p>
                             <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-4">Lihat Profil</a>
                         </div>
                     </div>
                 </div>
                 <?php endforeach; ?>
             </div>
         </div>
      </section>

      <?php if (!empty($facilities_data)): ?>
      <section class="py-5" id="facilities" style="background-color: #F8FDFF;">
         <div class="container">
             <div class="row justify-content-center mb-5">
                 <div class="col-md-8 text-center">
                     <h2 class="section-title">FASILITAS UNGGULAN</h2>
                 </div>
             </div>
             <div class="row g-4">
                 <?php foreach($facilities_data as $fac): ?>
                 <div class="col-md-4">
                     <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden rounded-3">
                         <div class="position-relative" style="height: 220px;">
                             <img src="public/<?php echo htmlspecialchars($fac['image_path']); ?>" class="w-100 h-100" style="object-fit: cover;" alt="Fasilitas">
                             <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                                <h5 class="text-white mb-0 fw-bold"><?php echo htmlspecialchars($fac['name']); ?></h5>
                             </div>
                         </div>
                         <div class="card-body">
                             <p class="card-text text-muted small"><?php echo nl2br(htmlspecialchars($fac['description'])); ?></p>
                         </div>
                     </div>
                 </div>
                 <?php endforeach; ?>
             </div>
         </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($careers_data)): ?>
      <section class="py-5 bg-white" id="careers">
         <div class="container">
             <div class="row justify-content-center mb-5">
                 <div class="col-md-8 text-center">
                     <h2 class="section-title">BERGABUNG BERSAMA KAMI</h2>
                     <p class="text-muted">Karir dan peluang kerja terbaru di JHC Tasikmalaya.</p>
                 </div>
             </div>
             
             <div class="row justify-content-center">
                 <div class="col-lg-10">
                     <?php foreach($careers_data as $job): ?>
                     <div class="card mb-3 border-0 shadow-sm hover-lift">
                         <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                             <div class="mb-3 mb-md-0">
                                 <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                 <div class="text-muted small">
                                     <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?>
                                     <?php if (!empty($job['end_date'])): ?>
                                        <span class="mx-2">•</span> <span class="text-danger">Deadline: <?php echo date('d M Y', strtotime($job['end_date'])); ?></span>
                                     <?php endif; ?>
                                 </div>
                             </div>
                             <button class="btn btn-primary rounded-pill px-4" type="button" data-bs-toggle="collapse" data-bs-target="#jobDesc<?php echo $job['id']; ?>">Detail</button>
                         </div>
                         <div class="collapse" id="jobDesc<?php echo $job['id']; ?>">
                             <div class="card-footer bg-light border-0 p-4">
                                 <h6>Deskripsi Pekerjaan:</h6>
                                 <p class="small text-secondary mb-3"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                 <a href="#" class="btn btn-sm btn-outline-primary">Kirim Lamaran</a>
                             </div>
                         </div>
                     </div>
                     <?php endforeach; ?>
                 </div>
             </div>
         </div>
      </section>
      <?php endif; ?>

      <section class="py-5" id="news" style="background-color: #f9f9f9;">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <h2 class="section-title"><?php echo htmlspecialchars($settings['news_section_title'] ?? 'BERITA & ARTIKEL'); ?></h2>
            </div>
          </div>
          <div class="row g-4">
            <?php foreach($news_data as $article): ?>
            <div class="col-md-4">
              <div class="card h-100 border-0 shadow-sm hover-lift rounded-3 overflow-hidden">
                <div class="position-relative">
                    <img src="public/<?php echo htmlspecialchars($article['image_path']); ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?php echo htmlspecialchars($article['title']); ?>" />
                    <div class="news-date-badge shadow-sm">
                        <?php echo date('d M Y', strtotime($article['post_date'])); ?>
                    </div>
                </div>
                <div class="card-body p-4">
                  <span class="badge bg-light text-primary mb-2"><?php echo htmlspecialchars($article['category']); ?></span>
                  <h5 class="card-title fw-bold text-dark mt-2 mb-3 lh-sm">
                      <a href="#!" class="text-decoration-none text-dark"><?php echo htmlspecialchars($article['title']); ?></a>
                  </h5>
                  <p class="card-text text-muted small"><?php echo substr(strip_tags($article['content']), 0, 90); ?>...</p>
                </div>
                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <a href="#!" class="text-primary fw-bold text-decoration-none small">Baca Selengkapnya <i class="fas fa-chevron-right ms-1"></i></a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="py-5 bg-white">
         <div class="container">
             <div class="row justify-content-center mb-4">
                 <div class="col-12 text-center"><h4 class="fw-bold text-secondary">MITRA ASURANSI & PERUSAHAAN</h4></div>
             </div>
             <div class="row justify-content-center align-items-center g-4">
                 <?php foreach($partners_data as $partner): ?>
                 <div class="col-4 col-md-2 text-center">
                     <a href="<?php echo htmlspecialchars($partner['url'] ?? '#'); ?>" target="_blank" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($partner['name']); ?>">
                         <img src="public/<?php echo htmlspecialchars($partner['logo_path']); ?>" class="img-fluid partner-logo" style="max-height: 60px; object-fit: contain;" alt="Partner">
                     </a>
                 </div>
                 <?php endforeach; ?>
             </div>
         </div>
      </section>

      <section class="py-5" id="appointment" style="background: linear-gradient(135deg, #1B71A1 0%, #2D3B48 100%);">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-5 mb-5 mb-lg-0 text-white">
                <h2 class="fw-bold display-6 mb-3">Butuh Bantuan Medis?</h2>
                <p class="lead mb-4 opacity-75">Isi formulir di samping untuk membuat janji temu atau konsultasi dengan tim medis kami.</p>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white text-primary rounded-circle p-3 me-3"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <h6 class="mb-0 text-white">Hubungi Kami</h6>
                        <p class="mb-0 fw-bold fs-5">0265 123 4567</p>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-white text-primary rounded-circle p-3 me-3"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h6 class="mb-0 text-white">Email</h6>
                        <p class="mb-0 fw-bold">info@jhc-tasikmalaya.com</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
              <div class="card border-0 shadow-lg rounded-3">
                  <div class="card-body p-5">
                      <h4 class="fw-bold text-primary mb-4">Formulir Janji Temu</h4>
                      
                      <?php if ($appointment_status): ?>
                        <div class="alert alert-<?php echo $appointment_status; ?> alert-dismissible fade show" role="alert">
                          <?php echo $appointment_message; ?>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                      <?php endif; ?>

                      <form class="row g-3" id="appointment-form" method="POST" action="index.php#appointment">
                        <div class="col-md-6">
                          <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                          <input class="form-control form-control-modern" name="name" type="text" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-bold text-muted">No. WhatsApp</label>
                          <input class="form-control form-control-modern" name="phone" type="text" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-bold text-muted">Email (Opsional)</label>
                          <input class="form-control form-control-modern" name="email" type="email" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-bold text-muted">Kategori</label>
                          <select class="form-select form-control-modern" name="category">
                            <option value="Jadwal Dokter">Jadwal Dokter</option>
                            <option value="Layanan Medis">Layanan Medis</option>
                            <option value="Keluhan">Keluhan / Saran</option>
                            <option value="Lainnya">Lainnya</option>
                          </select>
                        </div>
                        <div class="col-12">
                          <label class="form-label small fw-bold text-muted">Pesan / Keluhan</label>
                          <textarea class="form-control form-control-modern" name="message" rows="4" required></textarea>
                        </div>
                        <div class="col-12 mt-4">
                          <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm" type="submit" name="submit_appointment">Kirim Pesan Sekarang <i class="fas fa-paper-plane ms-2"></i></button>
                        </div>
                      </form>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <footer class="py-0 bg-primary">
        <div class="bg-holder opacity-25" style="background-image:url(public/assets/img/gallery/dot-bg.png);background-position:top left;margin-top:-3.125rem;background-size:auto;"></div>
        
        <div class="container">
          <div class="row py-7 py-lg-8">
            <div class="col-12 col-sm-6 col-lg-3 mb-4 order-0 order-sm-0">
                <a class="text-decoration-none" href="#">
                    <?php 
                    $footer_logo = !empty($settings['footer_logo_path']) ? $settings['footer_logo_path'] : 'assets/img/gallery/footer-logo.png';
                    ?>
                    <img src="public/<?php echo htmlspecialchars($footer_logo); ?>" height="51" alt="" />
                </a>
                <p class="text-light mt-4 mb-0">EU: +49 9999 0000</p>
                <p class="text-light mb-0">US: +00 4444 0000</p>
                <p class="text-light mt-4 mb-0">info@jhc.com</p>
                <div class="d-flex mt-5">
                    <a class="text-decoration-none me-3" href="#!"><img src="public/assets/img/icons/facebook.svg" alt="" /></a>
                    <a class="text-decoration-none me-3" href="#!"><img src="public/assets/img/icons/twitter.svg" alt="" /></a>
                    <a class="text-decoration-none me-3" href="#!"><img src="public/assets/img/icons/instagram.svg" alt="" /></a>
                </div>
            </div>
            
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-2 order-sm-1">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif">Departments</h5>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <?php foreach(array_slice($dept_data, 0, 5) as $d): ?>
                    <li class="lh-lg"><a class="text-200" href="#departments"><?php echo htmlspecialchars($d['name']); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-3 order-sm-2">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif">Useful Links</h5>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="text-200" href="#about">About Us</a></li>
                <li class="lh-lg"><a class="text-200" href="#news">Blog</a></li>
                <li class="lh-lg"><a class="text-200" href="#appointment">Contact</a></li>
                <li class="lh-lg"><a class="text-200" href="#appointment">Appointment</a></li>
              </ul>
            </div>
            
           <div class="col-6 col-sm-4 col-lg-3 mb-3 order-3 order-sm-2">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif">Our Location</h5>
              <div class="ratio ratio-1x1" style="max-height: 200px; max-width: 250px;">
                <iframe 
                  src="https://maps.google.com/maps?q=RS+Jantung+Tasikmalaya+JHC&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                  style="border:0;" 
                  allowfullscreen="" 
                  loading="lazy" 
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
            </div>
          </div>
        </div>

        <div class="container">
          <div class="row justify-content-md-between justify-content-evenly py-4">
            <div class="col-12 col-sm-8 col-md-6 col-lg-auto text-center text-md-start">
              <p class="fs--1 my-2 fw-bold text-200">All rights Reserved © JHC, 2026</p>
            </div>
          </div>
        </div>
      </section>
<?php 
if (file_exists("public/layout/public_footer.php")) {
    require_once "public/layout/public_footer.php"; 
} else {
    echo '<footer class="py-4 bg-light text-center"><p class="mb-0 text-muted">&copy; '.date('Y').' Rs JHC Tasikmalaya. All rights reserved.</p></footer><script src="public/vendors/bootstrap/bootstrap.min.js"></script></body></html>';
}
?>