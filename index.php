<?php 
$page_title = "Rs JHC Tasikmalaya | Home";
$no_igd = "(0265) 3172112";
$no_rs_wa = "6285175000375";

if (file_exists("config.php")) {
    require_once "config.php";
} else {
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
$about_sections = [];
$sql_about = "SELECT * FROM about_us_sections"; 
$result_about = $mysqli->query($sql_about);

if ($result_about) {
    while ($row = $result_about->fetch_assoc()) {
        $clean_key = strtolower(trim($row['section_key']));
        $about_sections[$clean_key] = $row;
    }
}

$tabs_config = [
    'visi-misi'      => ['label' => 'Visi & Misi',    'icon' => 'fa-bullseye'],
    'sejarah'        => ['label' => 'Sejarah',        'icon' => 'fa-history'],
    'salam-direktur' => ['label' => 'Salam Direktur', 'icon' => 'fa-user-tie'],
    'budaya-kerja'   => ['label' => 'Budaya Kerja',   'icon' => 'fa-hand-holding-heart']
];

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

$sql = "SELECT id, name, category, icon_path, icon_hover_path, description, special_skills, additional_info 
        FROM departments 
        ORDER BY display_order ASC";

$dept_result = $mysqli->query($sql);

if ($dept_result) { 
    while($row = $dept_result->fetch_assoc()) { 
        $row['description'] = $row['description'] ?? '';
        $row['special_skills'] = $row['special_skills'] ?? '';
        $row['additional_info'] = $row['additional_info'] ?? '';
        
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

$mcu_packages_data = [];
$mcu_result = $mysqli->query("SELECT * FROM mcu_packages ORDER BY display_order ASC"); 
if ($mcu_result) { while($row = $mcu_result->fetch_assoc()) { $mcu_packages_data[] = $row; } }

$vr_data = null;
$res = $mysqli->query("SELECT * FROM page_virtual_room WHERE id = 1");
if ($res && $res->num_rows > 0) $vr_data = $res->fetch_assoc();

$partners_data = [];
$partner_sql = "SELECT name, logo_path, url FROM partners ORDER BY name ASC";
$partner_result = $mysqli->query($partner_sql);

if ($partner_result) {
    while($row = $partner_result->fetch_assoc()) {
        $partners_data[] = $row;
    }
}

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-red: #C8102E;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --jhc-navy: #002855;
            --jhc-blue: #1B71A1;
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ==================== NAVBAR ==================== */
        .navbar {
            padding: 12px 0;
            min-height: 85px;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            box-shadow: var(--shadow-sm);
            transition: var(--transition-smooth);
        }

        .navbar.navbar-scrolled {
            padding: 8px 0;
            box-shadow: var(--shadow-md);
            border-bottom: 3px solid var(--jhc-red);
        }

        .navbar .container {
            max-width: 1400px;
            padding: 0 20px;
        }

        .navbar-brand img {
            height: 75px;
            width: auto;
            transition: var(--transition-smooth);
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
            filter: drop-shadow(0 4px 12px rgba(200, 16, 46, 0.25));
        }

        .nav-link {
            color: var(--jhc-navy) !important;
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            margin: 0 10px;
            padding: 10px 15px !important;
            transition: var(--transition-smooth);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background: var(--jhc-gradient);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-link:hover {
            color: var(--jhc-red) !important;
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .btn-janji {
            background: var(--jhc-gradient);
            color: white !important;
            border-radius: 50px;
            padding: 10px 28px;
            font-size: 0.9rem;
            font-weight: 700;
            border: 2px solid transparent;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.2);
        }

        .btn-janji:hover {
            background: white;
            color: var(--jhc-red) !important;
            border-color: var(--jhc-red);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 16, 46, 0.3);
        }

        /* ==================== FLOATING BUTTONS ==================== */
        .floating-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            z-index: 1030;
        }

        .btn-igd-float, .btn-wa-float {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: var(--shadow-lg);
            transition: var(--transition-smooth);
            text-decoration: none;
            border: 3px solid white;
            cursor: pointer;
        }

        .btn-igd-float {
            background: var(--jhc-gradient);
            color: white !important;
        }

        .btn-igd-float:hover {
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 8px 35px rgba(200, 16, 46, 0.4);
        }

        .pulse-igd {
            animation: pulse-red-infinite 2s infinite;
        }

        @keyframes pulse-red-infinite {
            0%, 100% { box-shadow: 0 0 0 0 rgba(200, 16, 46, 0.7); }
            50% { box-shadow: 0 0 0 20px rgba(200, 16, 46, 0); }
        }

        .btn-wa-float {
            background-color: #25D366;
            color: white !important;
        }

        .btn-wa-float:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 35px rgba(37, 211, 102, 0.4);
        }

        /* ==================== HERO SECTION ==================== */
        .hero-section {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            position: relative;
            background-color: #000; /* Fallback warna hitam saat gambar loading */
        }

        /* Memastikan Carousel mengikuti tinggi layar */
        #heroCarousel, 
        .carousel-inner, 
        .carousel-item {
            height: 100vh;
            width: 100%;
        }

        .bg-holder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center center; /* Pastikan center agar gambar tidak terpotong aneh */
            background-repeat: no-repeat;
            transition: transform 0.8s ease-in-out; /* Efek zoom halus saat slide */
        }

        /* Overlay diperbaiki agar teks lebih mudah dibaca */
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Menggunakan linear gradient dari kiri ke kanan agar teks di kiri lebih menonjol */
            background: linear-gradient(90deg, rgba(0, 20, 40, 0.8) 0%, rgba(138, 48, 51, 0.4) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            /* Memastikan konten berada di tengah secara vertikal sudah ditangani min-vh-100 di HTML */
        }

        .hero-badge {
            display: inline-flex; /* Gunakan flex agar ikon & teks sejajar sempurna */
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white !important;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Memperbaiki Tipografi Slider */
        .carousel-item h1 {
            font-size: clamp(2rem, 6vw, 4rem); /* Responsif otomatis */
            color: white !important;
            text-shadow: 2px 4px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.1;
        }

        .carousel-item p {
            font-size: clamp(1rem, 2vw, 1.25rem);
            max-width: 700px; /* Batasi lebar agar tidak terlalu panjang ke samping */
            color: rgba(255, 255, 255, 0.9) !important;
            text-shadow: 1px 2px 5px rgba(0, 0, 0, 0.5);
        }

        /* Perbaikan Tombol Hero */
        .btn-hero {
            display: inline-flex;
            align-items: center;
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            color: white !important;
            text-decoration: none;
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            background: white;
            color: #8a3033 !important; /* Gunakan warna merah JHC saat hover */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* ==================== SECTIONS ==================== */
        .section-title {
            position: relative;
            display: inline-block;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            color: #002855; /* Pastikan variabel navy didefinisikan atau gunakan hex */
            margin-bottom: 20px;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px; /* Ukuran lebih proporsional */
            height: 4px;
            background: linear-gradient(90deg, #8a3033, #bd3030);
            margin: 15px auto 0;
            border-radius: 10px;
        }

        /* ==================== CARDS ==================== */
        .card {
            border: none;
            transition: var(--transition-smooth);
        }

        .hover-lift {
            transition: var(--transition-smooth);
        }

        .hover-lift:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow-lg);
        }

        .service-card {
            border-radius: 24px;
            background: white;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            height: 100%;
        }

        .service-card:hover {
            border: 2px solid rgba(138, 48, 51, 0.15);
        }

        .icon-wrapper {
            width: 95px;
            height: 95px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            margin: 0 auto 24px;
            transition: var(--transition-smooth);
        }

        .service-card:hover .icon-wrapper {
            background: linear-gradient(135deg, #fff5f5, #ffe5e5);
            transform: scale(1.12) rotate(8deg);
        }

        /* ==================== DOCTORS SECTION ==================== */
        .doctor-card {
            border-radius: 24px;
            overflow: hidden;
            transition: var(--transition-smooth);
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .doctor-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow-lg);
        }

        .doctor-card img {
            transition: transform 0.6s ease;
        }

        .doctor-card:hover img {
            transform: scale(1.12);
        }

        /* ==================== MODALS ==================== */
        .modal-content {
            border-radius: 24px;
            border: none;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: var(--jhc-gradient);
            color: white !important;
            border: none;
            padding: 20px 30px;
        }

        .modal-title {
            color: white !important;
        }

        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 1;
        }

        .modal-body {
            padding: 30px;
        }

        /* ==================== PARTNERS ==================== */
        .partner-logo {
            filter: none !important;      
            -webkit-filter: none !important; 
            opacity: 1 !important;        
            transition: transform 0.3s ease;
            max-height: 80px;             
            width: auto;
            object-fit: contain;
        }

        .partner-logo:hover {
            transform: scale(1.1);
        }

        /* ==================== NEWS ==================== */
        .news-date-badge {
            position: absolute;
            top: 18px;
            left: 18px;
            background: var(--jhc-gradient);
            color: white !important;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            z-index: 10;
            box-shadow: var(--shadow-md);
        }

        .news-card-link {
            color: var(--jhc-blue);
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .news-card-link:hover {
            color: var(--jhc-red);
            transform: translateX(5px);
        }

        /* ==================== FOOTER ==================== */
        footer {
            background: var(--jhc-gradient);
            color: white;
        }

        footer a {
            color: rgba(255, 255, 255, 0.85);
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        footer a:hover {
            color: white;
            transform: translateX(5px);
        }

        /* ==================== FORM CONTROLS ==================== */
        .form-control-modern {
            border-radius: 14px;
            padding: 16px 20px;
            border: 2px solid #e9ecef;
            transition: var(--transition-smooth);
        }

        .form-control-modern:focus {
            background: white;
            box-shadow: 0 0 0 5px rgba(27, 113, 161, 0.1);
            border-color: var(--jhc-blue);
        }

        /* ==================== CAROUSEL CONTROLS ==================== */
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(200, 16, 46, 0.8);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            background: rgba(200, 16, 46, 1);
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.6);
            border: 2px solid white;
        }

        .carousel-indicators button.active {
            background-color: var(--jhc-red);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 991px) {
            .navbar-brand img {
                height: 60px;
            }

            .nav-link {
                padding: 12px 18px !important;
            }

            .floating-actions {
                bottom: 20px;
                right: 20px;
            }

            .btn-igd-float, .btn-wa-float {
                width: 55px;
                height: 55px;
                font-size: 22px;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 767px) {
            .navbar-brand img {
                height: 55px;
            }

            .hero-section {
                min-height: 75vh;
            }

            .partner-logo {
                max-height: 65px;
            }

            .floating-actions {
                bottom: 15px;
                right: 15px;
            }

            .btn-igd-float, .btn-wa-float {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .icon-wrapper {
                width: 75px;
                height: 75px;
            }
        }

        @media (max-width: 576px) {
            .btn-janji {
                padding: 8px 20px;
                font-size: 0.85rem;
            }

            .col-6 {
                padding-left: 10px;
                padding-right: 10px;
            }

            .section-title {
                font-size: 1.75rem;
            }
        }

        /* ==================== UTILITIES ==================== */
        .text-justify {
            text-align: justify;
        }

        .shadow-soft {
            box-shadow: var(--shadow-sm);
        }

        .bg-gradient-primary {
            background: var(--jhc-gradient);
        }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            animation: fadeInUp 0.8s ease;
        }

        /* ==================== SMOOTH SCROLLING ==================== */
        html {
            scroll-behavior: smooth;
        }
    </style>
  </head>
  <body>
    <main class="main" id="top">
      
      <!-- ==================== NAVBAR ==================== -->
      <nav class="navbar navbar-expand-lg navbar-light fixed-top" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <?php $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
            <img src="public/<?php echo htmlspecialchars($header_logo); ?>" alt="JHC Logo" />
          </a>
        
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="#departments">Layanan</a></li>
              <li class="nav-item"><a class="nav-link" href="#virtual_room">Virtual Room</a></li>
              <li class="nav-item"><a class="nav-link" href="#about_us">Tentang Kami</a></li>
              <li class="nav-item"><a class="nav-link" href="#doctors">Dokter Kami</a></li>
              <li class="nav-item"><a class="nav-link" href="#facilities">Fasilitas</a></li>
              <li class="nav-item"><a class="nav-link" href="#news">Berita</a></li>
            </ul>

            <div class="nav-actions ms-lg-4">
              <a class="btn btn-janji" href="career.php">
                <i class="fas fa-briefcase me-2"></i>Apply Job
              </a>
            </div>
          </div>
        </div>
      </nav>

      <!-- ==================== FLOATING BUTTONS ==================== -->
      <div class="floating-actions">
        <a href="tel:<?php echo $no_igd; ?>" class="btn-igd-float pulse-igd" title="Darurat IGD: <?php echo $no_igd; ?>">
          <i class="fas fa-ambulance"></i>
        </a>

        <a href="https://wa.me/<?php echo $no_rs_wa; ?>" target="_blank" class="btn-wa-float" title="WhatsApp RS">
          <i class="fab fa-whatsapp"></i>
        </a>
      </div>

      <!-- ==================== HERO SECTION ==================== -->
      <section class="hero-section p-0" id="home">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($banners_data as $index => $banner): ?>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index; ?>" class="<?= $index === 0 ? 'active' : ''; ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel-inner">
                <?php if (!empty($banners_data)): ?>
                    <?php foreach ($banners_data as $index => $banner): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                            <div class="bg-holder" style="background-image:url(public/<?= htmlspecialchars($banner['image_path']); ?>);"></div>
                            <div class="banner-overlay"></div>
                            
                            <div class="container hero-content">
                                <div class="row align-items-center min-vh-100">
                                    <div class="col-lg-8 text-center text-lg-start">
                                        <span class="hero-badge animate__animated animate__fadeInDown">
                                            <i class="fas fa-heart-pulse me-2"></i>Selamat Datang di JHC Tasikmalaya
                                        </span>
                                        <h1 class="text-white fw-bold display-3 animate__animated animate__fadeInLeft">
                                            <?= htmlspecialchars($banner['title']); ?>
                                        </h1>
                                        <p class="text-white lead fs-4 animate__animated animate__fadeInLeft animate__delay-1s">
                                            <?= htmlspecialchars($banner['description']); ?>
                                        </p>
                                        <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                                            <a class="btn btn-light btn-lg px-5 py-3 rounded-pill text-primary fw-bold" href="#departments">
                                                <i class="fas fa-hospital me-2"></i>Layanan Kami
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </section>

      <!-- ==================== SERVICES SECTION ==================== -->
      <section class="py-5 bg-white" id="departments">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-star me-2"></i>LAYANAN TERBAIK
              </p>
              <h2 class="section-title">Pelayanan Kami</h2>
              <p class="text-muted mt-3">Kami menyediakan berbagai layanan unggulan dan poliklinik spesialis untuk kesehatan Anda.</p>
            </div>
          </div>

          <?php 
          function render_cards($data_list, $is_layanan = true) {
              foreach($data_list as $item): ?>
                  <div class="col-6 col-md-4 col-lg-3 mb-4">
                      <div class="card service-card h-100 hover-lift text-center p-4">
                          <div class="card-body">
                              <div class="icon-wrapper">
                                  <?php if(!empty($item['icon_path'])): ?>
                                      <img src="public/<?= htmlspecialchars($item['icon_path']); ?>" 
                                           style="width: 52px; height: 52px; object-fit: contain;" alt="icon" />
                                  <?php else: ?>
                                      <i class="fas <?= $is_layanan ? 'fa-star text-warning' : 'fa-heartbeat text-primary' ?> fa-2x"></i>
                                  <?php endif; ?>
                              </div>
                              <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 1rem;">
                                  <?= htmlspecialchars($item['name']); ?>
                              </h5>
                              <a href="javascript:void(0)" 
                                 class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm btn-buka-detail" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#modalLayanan" 
                                 data-name="<?= htmlspecialchars($item['name']); ?>" 
                                 data-desc="<?= htmlspecialchars($item['description']); ?>" 
                                 data-expertise="<?= htmlspecialchars($item['special_skills']); ?>" 
                                 data-info="<?= htmlspecialchars($item['additional_info']); ?>" 
                                 data-icon="public/<?= htmlspecialchars($item['icon_path']); ?>">
                                  <i class="fas fa-info-circle me-1"></i> Detail
                              </a>
                          </div>
                      </div>
                  </div>
              <?php endforeach; 
          } ?>

          <!-- Layanan Unggulan -->
          <?php if (!empty($layanan_data)): ?>
          <div class="mb-5">
            <div class="text-center mb-4">
              <h4 class="fw-bold text-primary text-uppercase">
                <i class="fas fa-award me-2"></i>Layanan Unggulan
              </h4>
              <div style="width: 90px; height: 5px; background: var(--jhc-gradient); margin: 18px auto; border-radius: 3px;"></div>
            </div>
            <div class="row gx-4">
              <?php render_cards($layanan_data, true); ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Poliklinik Spesialis -->
          <?php if (!empty($poliklinik_data)): ?>
          <div class="mt-5 pt-4">
            <div class="text-center mb-4">
              <h4 class="fw-bold text-secondary text-uppercase">
                <i class="fas fa-stethoscope me-2"></i>Poliklinik Spesialis
              </h4>
              <div style="width: 90px; height: 5px; background: var(--jhc-gradient); margin: 18px auto; border-radius: 3px;"></div>
            </div>
            <div class="row gx-4">
              <?php render_cards($poliklinik_data, false); ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Modal Layanan -->
      <div class="modal fade" id="modalLayanan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fw-bold">Detail Layanan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="text-center mb-4">
                <div class="icon-wrapper mx-auto">
                  <img id="m-icon" src="" style="width: 65px; height: 65px; object-fit: contain;" alt="Icon">
                </div>
                <h3 id="m-name" class="fw-bold text-dark mt-3"></h3>
              </div>

              <div class="row g-4">
                <div class="col-12">
                  <label class="fw-bold text-primary small text-uppercase mb-2 d-block">
                    <i class="fas fa-info-circle me-2"></i>Tentang Layanan
                  </label>
                  <p id="m-desc" class="text-muted"></p>
                </div>
                <div class="col-md-6">
                  <div class="p-4 bg-light rounded-3 h-100 border-start border-warning border-4">
                    <label class="fw-bold text-dark small text-uppercase mb-2 d-block">
                      <i class="fas fa-star me-2 text-warning"></i>Keahlian Khusus
                    </label>
                    <div id="m-expertise" class="text-muted small" style="white-space: pre-line;"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-4 rounded-3 bg-light h-100 border-start border-primary border-4">
                    <label class="fw-bold text-primary small text-uppercase mb-2 d-block">
                      <i class="fas fa-clock me-2"></i>Informasi Layanan
                    </label>
                    <div id="m-info" class="text-muted small" style="white-space: pre-line;"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              <a href="booking.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-calendar-check me-2"></i>Buat Janji Temu
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== MCU PACKAGES ==================== -->
      <?php if (!empty($mcu_packages_data)): ?>
      <section class="py-5" style="background: linear-gradient(135deg, #f1f7fc 0%, #e3f2fd 100%);">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-heart-pulse me-2"></i>CEGAH LEBIH BAIK
              </p>
              <h2 class="section-title">Paket Medical Check Up</h2>
              <p class="text-muted mt-3">Pencegahan lebih baik daripada pengobatan. Jaga kesehatan Anda dengan pemeriksaan rutin.</p>
            </div>
          </div>
          
          <div id="mcuCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
              <?php foreach ($mcu_packages_data as $index => $package): ?>
                <button type="button" data-bs-target="#mcuCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo ($index === 0) ? 'active' : ''; ?>"></button>
              <?php endforeach; ?>
            </div>

            <div class="carousel-inner">
              <?php foreach ($mcu_packages_data as $index => $package): ?>
                <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                  <div class="card border-0 shadow-lg mx-auto" style="max-width: 1000px; border-radius: 28px; overflow: hidden;">
                    <div class="row g-0">
                      <div class="col-md-5">
                        <img src="public/<?php echo htmlspecialchars($package['image_path']); ?>" 
                             class="img-fluid h-100 w-100" style="object-fit: cover; min-height: 380px;" 
                             alt="<?php echo htmlspecialchars($package['title']); ?>">
                      </div>
                      <div class="col-md-7 d-flex align-items-center bg-white">
                        <div class="card-body p-5">
                          <span class="badge bg-warning text-dark mb-3 px-4 py-2 rounded-pill">
                            <i class="fas fa-fire me-1"></i>Paket Populer
                          </span>
                          <h3 class="fw-bold text-primary mb-3"><?php echo htmlspecialchars($package['title']); ?></h3>
                          <p class="text-muted mb-4" style="line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                          </p>
                          <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                              <i class="fas fa-tag fa-2x text-success"></i>
                            </div>
                            <div>
                              <small class="text-muted d-block">Harga Paket</small>
                              <h4 class="text-dark fw-bold mb-0">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></h4>
                            </div>
                          </div>
                          
                          <?php
                          $whatsapp_number = '6287760615300';
                          $whatsapp_message = urlencode("Halo JHC, saya ingin booking paket MCU: " . $package['title']);
                          $whatsapp_link = "https://api.whatsapp.com/send?phone={$whatsapp_number}&text={$whatsapp_message}";
                          ?>
                          <a href="<?php echo $whatsapp_link; ?>" target="_blank" 
                             class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow-sm">
                            <i class="fab fa-whatsapp me-2"></i>Booking Via WhatsApp
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#mcuCarousel" data-bs-slide="prev" style="width: 5%;">
              <span class="carousel-control-prev-icon bg-primary rounded-circle p-3"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mcuCarousel" data-bs-slide="next" style="width: 5%;">
              <span class="carousel-control-next-icon bg-primary rounded-circle p-3"></span>
            </button>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <!-- ==================== VIRTUAL ROOM ==================== -->
      <?php if ($vr_data): ?>
      <section class="py-5 bg-white" id="virtual_room">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="position-relative">
                <div class="bg-primary position-absolute rounded-4" 
                     style="width: 100%; height: 100%; top: 25px; left: -25px; z-index: -1; opacity: 0.12;"></div>
                <?php if(!empty($vr_data['video_url'])): 
                  $embed_url = $vr_data['video_url'];
                  $sep = (strpos($embed_url, '?') !== false) ? '&' : '?';
                  $autoplay_url = $embed_url . $sep . "autoplay=1&mute=1&loop=1&playlist=" . basename(parse_url($embed_url, PHP_URL_PATH));
                ?>
                  <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden">
                    <iframe src="<?php echo htmlspecialchars($autoplay_url); ?>" 
                            allow="autoplay; encrypted-media" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <img src="public/<?php echo htmlspecialchars($vr_data['image_path_360']); ?>" 
                       class="img-fluid rounded-4 shadow-lg w-100" alt="Virtual Room">
                <?php endif; ?>
              </div>
            </div>

            <div class="col-lg-6">
              <p class="section-subtitle mb-2">
                <i class="fas fa-building me-2"></i>VIRTUAL ROOM
              </p>
              <h2 class="section-title mb-4"><?php echo htmlspecialchars($vr_data['title']); ?></h2>
              <p class="text-secondary lead text-justify mb-4" style="line-height: 1.9;">
                <?php echo nl2br(htmlspecialchars($vr_data['content'])); ?>
              </p>
              
              <div class="row g-4">
                <div class="col-sm-6">
                  <div class="d-flex align-items-center p-4 bg-light rounded-4">
                    <div class="bg-primary bg-gradient rounded-circle p-3 text-white me-3">
                      <i class="fas fa-user-md fa-lg"></i>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0">Dokter Ahli</h6>
                      <small class="text-muted">Berpengalaman</small>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="d-flex align-items-center p-4 bg-light rounded-4">
                    <div class="bg-danger bg-gradient rounded-circle p-3 text-white me-3">
                      <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0">Layanan 24 Jam</h6>
                      <small class="text-muted">Selalu Siap</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <!-- ==================== ABOUT US ==================== -->
      <section class="py-5" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);" id="about_us">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-hospital me-2"></i>TENTANG KAMI
              </p>
              <h2 class="section-title">Mengenal Lebih Dekat RS JHC</h2>
              <p class="text-muted mt-3">Dedikasi kami untuk pelayanan kesehatan terbaik bagi Anda dan keluarga.</p>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-3">
              <div class="nav flex-column nav-pills shadow-sm rounded-4 bg-white overflow-hidden" 
                   id="v-pills-tab" role="tablist">
                <?php 
                $no = 0;
                foreach ($tabs_config as $key => $info): 
                  $active = ($no === 0) ? 'active' : '';
                ?>
                  <button class="nav-link <?php echo $active; ?> text-start py-4 px-4 fw-bold border-bottom" 
                          id="v-pills-<?php echo $key; ?>-tab" 
                          data-bs-toggle="pill" 
                          data-bs-target="#v-pills-<?php echo $key; ?>" 
                          type="button">
                    <i class="fas <?php echo $info['icon']; ?> me-3 text-danger"></i> 
                    <?php echo $info['label']; ?>
                  </button>
                <?php $no++; endforeach; ?>
              </div>
            </div>

            <div class="col-lg-9">
              <div class="tab-content p-5 bg-white rounded-4 shadow-sm" style="min-height: 480px;">
                <?php 
                $no = 0;
                foreach ($tabs_config as $key => $info): 
                  $show_active = ($no === 0) ? 'show active' : '';
                  $row = isset($about_sections[$key]) ? $about_sections[$key] : null;
                  $judul = ($row && !empty($row['title'])) ? $row['title'] : $info['label'];
                  $isi = ($row && !empty($row['content'])) ? nl2br($row['content']) : "Konten belum tersedia.";
                  $img_src = ($row && !empty($row['image_path'])) ? 'public/' . $row['image_path'] : '';
                  $img_display = !empty($img_src) ? $img_src : "https://via.placeholder.com/800x400/f8f9fa/dee2e6?text=No+Image";
                ?>
                  <div class="tab-pane fade <?php echo $show_active; ?>" 
                       id="v-pills-<?php echo $key; ?>">
                    
                    <?php if ($key === 'salam-direktur'): ?>
                      <div class="text-center">
                        <img src="<?php echo htmlspecialchars($img_display); ?>" 
                             class="rounded-circle shadow-lg mb-4 border border-4 border-white" 
                             style="width: 170px; height: 170px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/170';">
                        <h3 class="text-danger fw-bold mb-3"><?php echo htmlspecialchars($judul); ?></h3>
                        <div class="text-muted fst-italic px-lg-5" style="line-height: 1.9;">
                          <i class="fas fa-quote-left fa-2x text-danger opacity-25 me-2"></i>
                          <?php echo $isi; ?>
                          <i class="fas fa-quote-right fa-2x text-danger opacity-25 ms-2"></i>
                        </div>
                      </div>
                    <?php else: ?>
                      <div class="row align-items-center g-4">
                        <div class="col-md-5">
                          <img src="<?php echo htmlspecialchars($img_display); ?>" 
                               class="img-fluid rounded-4 shadow w-100" 
                               style="object-fit: cover; height: 300px;"
                               onerror="this.src='https://via.placeholder.com/800x400';">
                        </div>
                        <div class="col-md-7">
                          <h3 class="text-danger fw-bold mb-3"><?php echo htmlspecialchars($judul); ?></h3>
                          <div class="text-secondary text-justify" style="line-height: 1.9;">
                            <?php echo $isi; ?>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php $no++; endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== DOCTORS SECTION ==================== -->
      <section class="py-5 bg-white" id="doctors">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-user-doctor me-2"></i>TIM TERBAIK
              </p>
              <h2 class="section-title">Tim Dokter Kami</h2>
              <p class="text-muted mt-3">Ditangani langsung oleh dokter spesialis yang berpengalaman di bidangnya.</p>
            </div>
          </div>
          
          <div class="row g-4">
            <?php foreach($doctors_data as $doc): ?>
            <div class="col-sm-6 col-lg-3">
              <div class="card doctor-card h-100 text-center p-4">
                <div class="card-body">
                  <div class="mx-auto mb-4" style="width: 150px; height: 150px; overflow: hidden; border-radius: 50%;">
                    <img src="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>" 
                         class="w-100 h-100" style="object-fit: cover;" alt="Doctor">
                  </div>
                  <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($doc['name']); ?></h5>
                  <p class="text-primary small fw-bold text-uppercase mb-3">
                    <?php echo htmlspecialchars($doc['specialty']); ?>
                  </p>
                  
                  <button type="button" 
                          class="btn btn-outline-primary btn-sm rounded-pill px-4 btn-detail-dokter" 
                          data-bs-toggle="modal" 
                          data-bs-target="#modalDetailDokter"
                          data-name="<?php echo htmlspecialchars($doc['name']); ?>"
                          data-specialty="<?php echo htmlspecialchars($doc['specialty']); ?>"
                          data-desc="<?php echo htmlspecialchars($doc['description'] ?? 'Profil profesional dokter di JHC.'); ?>"
                          data-img="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>">
                    <i class="fas fa-eye me-1"></i>Lihat Profil
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Modal Detail Dokter -->
      <div class="modal fade" id="modalDetailDokter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fw-bold">Profil Dokter</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
              <img id="mdl-img" src="" class="rounded-circle mb-4 border border-4 border-light shadow-lg" 
                   style="width: 140px; height: 140px; object-fit: cover;">
              
              <h4 id="mdl-name" class="fw-bold text-dark mb-2"></h4>
              <p id="mdl-specialty" class="text-primary small fw-bold text-uppercase mb-4"></p>
              
              <div class="text-start bg-light p-4 rounded-4">
                <h6 class="fw-bold small text-muted text-uppercase mb-2">
                  <i class="fas fa-info-circle me-2"></i>Tentang Dokter:
                </h6>
                <p id="mdl-desc" class="small text-secondary mb-0" style="line-height: 1.7;"></p>
              </div>
              
              <div class="d-grid mt-4">
                <a id="mdl-book-link" href="booking.php" class="btn btn-danger rounded-pill py-3 fw-bold shadow-sm">
                  <i class="fas fa-calendar-check me-2"></i>Buat Janji Temu
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== FACILITIES ==================== -->
      <?php if (!empty($facilities_data)): ?>
      <section class="py-5" style="background: linear-gradient(135deg, #F8FDFF 0%, #E3F2FD 100%);" id="facilities">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-building me-2"></i>FASILITAS
              </p>
              <h2 class="section-title">Fasilitas Unggulan</h2>
              <p class="text-muted mt-3">Fasilitas modern dan lengkap untuk kenyamanan Anda.</p>
            </div>
          </div>

          <div class="row g-4">
            <?php foreach($facilities_data as $fac): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden" style="border-radius: 24px;">
                <div class="position-relative" style="height: 260px; overflow: hidden;">
                  <img src="public/<?php echo htmlspecialchars($fac['image_path']); ?>" 
                       class="w-100 h-100" style="object-fit: cover; transition: transform 0.6s;" alt="Fasilitas">
                  <div class="position-absolute bottom-0 start-0 w-100 p-4" 
                       style="background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);">
                    <h5 class="text-white mb-0 fw-bold">
                      <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($fac['name']); ?>
                    </h5>
                  </div>
                </div>
                <div class="card-body p-4">
                  <p class="card-text text-muted" style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($fac['description'])); ?>
                  </p>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <!-- ==================== NEWS SECTION ==================== -->
      <section class="py-5 bg-white" id="news">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-newspaper me-2"></i>BERITA TERKINI
              </p>
              <h2 class="section-title">
                <?php echo htmlspecialchars($settings['news_section_title'] ?? 'Berita & Artikel'); ?>
              </h2>
              <p class="text-muted mt-3">Informasi terbaru seputar kesehatan dan layanan kami.</p>
            </div>
          </div>

          <div class="row g-4">
            <?php foreach($news_data as $article): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden" style="border-radius: 24px;">
                <div class="position-relative" style="height: 260px; overflow: hidden;">
                  <img src="public/<?php echo htmlspecialchars($article['image_path']); ?>" 
                       class="w-100 h-100" style="object-fit: cover;" 
                       alt="<?php echo htmlspecialchars($article['title']); ?>">
                  <div class="news-date-badge">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo date('d M Y', strtotime($article['post_date'])); ?>
                  </div>
                </div>
                <div class="card-body p-4">
                  <span class="badge bg-light text-primary mb-3 px-3 py-2 rounded-pill">
                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($article['category']); ?>
                  </span>
                  <h5 class="card-title fw-bold text-dark lh-base mb-3">
                    <?php echo htmlspecialchars($article['title']); ?>
                  </h5>
                  <p class="card-text text-muted small" style="line-height: 1.7;">
                    <?php echo substr(strip_tags($article['content']), 0, 110); ?>...
                  </p>
                  <a href="javascript:void(0)" 
                     class="news-card-link small mt-3 d-inline-flex align-items-center btn-read-more"
                     data-bs-toggle="modal" 
                     data-bs-target="#modalArticle"
                     data-title="<?php echo htmlspecialchars($article['title']); ?>"
                     data-category="<?php echo htmlspecialchars($article['category']); ?>"
                     data-date="<?php echo date('d M Y', strtotime($article['post_date'])); ?>"
                     data-image="public/<?php echo htmlspecialchars($article['image_path']); ?>"
                     data-content="<?php echo htmlspecialchars($article['content']); ?>">
                    Baca Selengkapnya <i class="fas fa-arrow-right ms-2"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Modal Article Detail -->
      <div class="modal fade" id="modalArticle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fw-bold">Detail Artikel</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
              <img id="article-img" src="" class="w-100" style="max-height: 350px; object-fit: cover;" alt="Article">
              <div class="p-4">
                <div class="d-flex gap-3 mb-3">
                  <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-tag me-1"></i><span id="article-category"></span>
                  </span>
                  <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-calendar-alt me-1"></i><span id="article-date"></span>
                  </span>
                </div>
                <h3 id="article-title" class="fw-bold text-dark mb-4"></h3>
                <div id="article-content" class="text-secondary" style="line-height: 1.9; text-align: justify;"></div>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== PARTNERS ==================== -->
      <section class="py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);" id="partners">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle mb-2">
                <i class="fas fa-handshake me-2"></i>MITRA KAMI
              </p>
              <h2 class="section-title">Mitra Asuransi & Perusahaan</h2>
              <p class="text-muted mt-3">Bekerja sama dengan berbagai mitra terpercaya untuk pelayanan terbaik.</p>
            </div>
          </div>

          <div class="row justify-content-center align-items-center g-4">
            <?php if (!empty($partners_data)): ?>
              <?php foreach($partners_data as $partner): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center">
                  <a href="<?php echo htmlspecialchars(!empty($partner['url']) ? $partner['url'] : '#'); ?>" 
                     target="<?php echo !empty($partner['url']) ? '_blank' : '_self'; ?>" 
                     class="d-block p-3"
                     data-bs-toggle="tooltip" 
                     title="<?php echo htmlspecialchars($partner['name']); ?>">
                    <img src="public/<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                         class="img-fluid partner-logo" 
                         alt="<?php echo htmlspecialchars($partner['name']); ?>"
                         onerror="this.src='public/assets/img/gallery/default-partner.png';">
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <p>Belum ada mitra yang ditampilkan.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- ==================== FOOTER ==================== -->
      <footer class="py-0 bg-primary position-relative">
        <div class="bg-holder opacity-25" 
             style="background-image:url(public/assets/img/gallery/dot-bg.png);
                    background-position:top left;margin-top:-3.125rem;background-size:auto;"></div>
        
        <div class="container position-relative">
          <div class="row py-7 py-lg-8 g-4">
            <div class="col-12 col-sm-6 col-lg-3 mb-4">
              <a class="text-decoration-none d-block mb-4" href="#">
                <?php 
                $footer_logo = !empty($settings['footer_logo_path']) ? $settings['footer_logo_path'] : 'assets/img/gallery/footer-logo.png';
                ?>
                <img src="public/<?php echo htmlspecialchars($footer_logo); ?>" height="65" alt="JHC Logo" />
              </a>
              <div class="mb-4">
                <p class="text-light mb-2">
                  <i class="fas fa-phone me-2"></i>EU: +49 9999 0000
                </p>
                <p class="text-light mb-2">
                  <i class="fas fa-phone me-2"></i>US: +00 4444 0000
                </p>
                <p class="text-light mb-0">
                  <i class="fas fa-envelope me-2"></i>info@jhc.com
                </p>
              </div>
              <div class="d-flex gap-3">
                <a class="text-decoration-none d-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" 
                   href="#!" style="width: 42px; height: 42px;">
                  <i class="fab fa-facebook-f text-white"></i>
                </a>
                <a class="text-decoration-none d-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" 
                   href="#!" style="width: 42px; height: 42px;">
                  <i class="fab fa-twitter text-white"></i>
                </a>
                <a class="text-decoration-none d-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" 
                   href="#!" style="width: 42px; height: 42px;">
                  <i class="fab fa-instagram text-white"></i>
                </a>
              </div>
            </div>
            
            <div class="col-6 col-sm-6 col-lg-2 mb-3">
              <h5 class="lh-lg fw-bold text-light mb-4">Departments</h5>
              <ul class="list-unstyled">
                <?php foreach(array_slice($layanan_data, 0, 5) as $d): ?>
                  <li class="lh-lg">
                    <a class="text-200 text-decoration-none" href="#departments">
                      <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>
                      <?php echo htmlspecialchars($d['name']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            
            <div class="col-6 col-sm-6 col-lg-2 mb-3">
              <h5 class="lh-lg fw-bold text-light mb-4">Useful Links</h5>
              <ul class="list-unstyled">
                <li class="lh-lg">
                  <a class="text-200 text-decoration-none" href="#departments">
                    <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>Layanan
                  </a>
                </li>
                <li class="lh-lg">
                  <a class="text-200 text-decoration-none" href="#virtual_room">
                    <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>Virtual Room
                  </a>
                </li>
                <li class="lh-lg">
                  <a class="text-200 text-decoration-none" href="#about_us">
                    <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>Tentang Kami
                  </a>
                </li>
                <li class="lh-lg">
                  <a class="text-200 text-decoration-none" href="#doctors">
                    <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>Dokter Kami
                  </a>
                </li>
                <li class="lh-lg">
                  <a class="text-200 text-decoration-none" href="#facilities">
                    <i class="fas fa-chevron-right me-2" style="font-size: 0.7rem;"></i>Fasilitas
                  </a>
                </li>
              </ul>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-3">
              <h5 class="lh-lg fw-bold text-light mb-4">Our Location</h5>
              <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-lg" style="max-height: 280px;">
                <iframe 
                  src="https://maps.google.com/maps?q=RS+Jantung+Tasikmalaya+JHC&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                  style="border:0;" 
                  allowfullscreen="" 
                  loading="lazy">
                </iframe>
              </div>
            </div>
          </div>
        </div>

        <div class="container position-relative">
          <div class="row justify-content-center py-4 border-top border-white border-opacity-25">
            <div class="col-12 text-center">
              <p class="mb-0 text-200 fw-bold">
                <i class="fas fa-copyright me-2"></i>All rights Reserved © JHC Tasikmalaya, 2026
              </p>
            </div>
          </div>
        </div>
      </footer>

    </main>

    <!-- ==================== SCRIPTS ==================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>   
     <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Carousel Bootstrap
        const myCarousel = document.getElementById('heroCarousel');
        
        // Jika Anda ingin kontrol manual lewat JS (opsional)
        const carousel = new bootstrap.Carousel(myCarousel, {
            interval: 10000, // Kecepatan ganti slide (10 detik)
            ride: 'carousel',
            pause: false // Slide tetap jalan meskipun mouse di atas banner
        });

        // EVENT: Setiap kali slide akan berpindah
        myCarousel.addEventListener('slide.bs.carousel', function (e) {
            // Ambil elemen teks di slide yang akan muncul
            const nextSlide = e.relatedTarget;
            const animatedElements = nextSlide.querySelectorAll('.animate__animated');

            // Reset animasi agar bisa terulang kembali
            animatedElements.forEach(el => {
                el.style.visibility = 'hidden';
                const animationClass = Array.from(el.classList).find(cl => cl.startsWith('animate__fade'));
                el.classList.remove(animationClass);
                
                // Trigger reflow untuk restart animasi
                void el.offsetWidth; 
                
                el.style.visibility = 'visible';
                el.classList.add(animationClass);
            });
        });
    

        // Detail Layanan Modal
        const detailButtons = document.querySelectorAll('.btn-buka-detail');
        
        detailButtons.forEach(button => {
          button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const desc = this.getAttribute('data-desc');
            const expertise = this.getAttribute('data-expertise');
            const info = this.getAttribute('data-info');
            const icon = this.getAttribute('data-icon');

            document.getElementById('m-name').innerText = name;
            document.getElementById('m-desc').innerText = (desc && desc.trim() !== '') ? desc : 'Deskripsi belum tersedia.';
            
            const iconImg = document.getElementById('m-icon');
            if(icon && icon.trim() !== 'public/') {
              iconImg.src = icon;
              iconImg.style.display = 'block';
            } else {
              iconImg.src = 'assets/img/default-icon.png';
            }

            const expertiseBox = document.getElementById('m-expertise');
            expertiseBox.innerText = (expertise && expertise.trim() !== '') ? expertise : 'Informasi keahlian belum tersedia.';

            const infoBox = document.getElementById('m-info');
            infoBox.innerText = (info && info.trim() !== '') ? info : 'Jadwal atau informasi tambahan belum tersedia.';
          });
        });

        // Detail Dokter Modal
        const doctorButtons = document.querySelectorAll('.btn-detail-dokter');
        
        doctorButtons.forEach(button => {
          button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const specialty = this.getAttribute('data-specialty');
            const desc = this.getAttribute('data-desc') || "Tidak ada deskripsi tersedia.";
            const img = this.getAttribute('data-img');
            
            document.getElementById('mdl-name').innerText = name;
            document.getElementById('mdl-specialty').innerText = specialty;
            document.getElementById('mdl-desc').innerText = desc;
            document.getElementById('mdl-img').src = img;

            const bookingUrl = `booking.php?dokter=${encodeURIComponent(name)}&spesialis=${encodeURIComponent(specialty)}`;
            const bookBtn = document.getElementById('mdl-book-link');
            if(bookBtn) {
              bookBtn.href = bookingUrl;
            }
          });
        });

        // Article Detail Modal
        const readMoreButtons = document.querySelectorAll('.btn-read-more');
        
        readMoreButtons.forEach(button => {
          button.addEventListener('click', function() {
            const title = this.getAttribute('data-title');
            const category = this.getAttribute('data-category');
            const date = this.getAttribute('data-date');
            const image = this.getAttribute('data-image');
            const content = this.getAttribute('data-content');
            
            document.getElementById('article-title').innerText = title;
            document.getElementById('article-category').innerText = category;
            document.getElementById('article-date').innerText = date;
            document.getElementById('article-img').src = image;
            document.getElementById('article-content').innerHTML = content.replace(/\n/g, '<br>');
          });
        });

        // Initialize Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
          anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
              e.preventDefault();
              const target = document.querySelector(href);
              if (target) {
                const offsetTop = target.offsetTop - 90;
                window.scrollTo({
                  top: offsetTop,
                  behavior: 'smooth'
                });
              }
            }
          });
        });
      });
    </script>
  </body>
</html>