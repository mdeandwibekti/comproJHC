<?php 
$page_title = "Rs JHC Tasikmalaya | Home";
$no_igd = "(0265) 3172112";
$no_rs_wa = "6285175000375";

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

// --- 1. AMBIL DATA DARI DATABASE ---
$about_sections = [];
// Pastikan tabel dan kolom sesuai database Anda
$sql_about = "SELECT * FROM about_us_sections"; 
$result_about = $mysqli->query($sql_about);

if ($result_about) {
    while ($row = $result_about->fetch_assoc()) {
        // Bersihkan key (huruf kecil, tanpa spasi) untuk ID HTML yang valid
        $clean_key = strtolower(trim($row['section_key']));
        $about_sections[$clean_key] = $row;
    }
}

// Konfigurasi Tab (Urutan Tetap)
// Key array ini HARUS cocok dengan 'section_key' di database
$tabs_config = [
    'visi-misi'      => ['label' => 'Visi & Misi',    'icon' => 'fa-bullseye'],
    'sejarah'        => ['label' => 'Sejarah',        'icon' => 'fa-history'],
    'salam-direktur' => ['label' => 'Salam Direktur', 'icon' => 'fa-user-tie'],
    'budaya-kerja'   => ['label' => 'Budaya Kerja',   'icon' => 'fa-hand-holding-heart']
];

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

// Virtual Room (About Us)
$vr_data = null;
$res = $mysqli->query("SELECT * FROM page_virtual_room WHERE id = 1");
if ($res && $res->num_rows > 0) $vr_data = $res->fetch_assoc();


// --- FETCH PARTNERS DATA ---
$partners_data = [];
// Mengambil kolom id, name, logo_path, url sesuai struktur tabel di backend
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
    <link href="public/vendors/fontawesome/all.min.js" rel="stylesheet" />

    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
            --jhc-navy: #002855;
        }

        /* Navbar */
        .navbar {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
            min-height: 75px; 
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .navbar.navbar-scrolled {
            padding: 10px 0;
            border-bottom: 3px solid var(--jhc-red);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .navbar .container {
            max-width: 1200px;
            padding-left: 10px;
            padding-right: 1px;
        }

        .navbar-brand img {
            height: 80px; 
            width: auto;  
            transition: transform 0.3s ease;
            object-fit: contain;
        }

        .navbar-brand:hover img {
            transform: scale(1.08);
            filter: drop-shadow(0px 4px 8px rgba(200, 16, 46, 0.2));
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

        .nav-link:hover {
        color: var(--jhc-red-light) !important;
        }

        .nav-link:hover::after {
            background-color: var(--jhc-red-dark);
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
            transform: scale(1.1) rotate(-2deg); 
            filter: drop-shadow(0px 4px 8px rgba(200, 16, 46, 0.3)); 
        }

        .navbar.navbar-scrolled {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.1);
        }

        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        /* Memastikan tombol berada di depan */
        .btn-buka-detail {
            position: relative;
            z-index: 2;
        }

        .btn-janji {
            background: var(--jhc-gradient);
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
        position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;
        }
        .hero-content { position: relative; z-index: 2; }

        .partner-logo { filter: grayscale(100%); opacity: 0.7; transition: all 0.3s; }
        .partner-logo:hover { filter: grayscale(0%); opacity: 1; }

        .section-title {
            position: relative; display: inline-block; margin-bottom: 3rem; font-weight: 800; color: var(--secondary-color);
        }
        .section-title::after {
            content: ''; display: block; width: 60px; height: 4px; background: var(--primary-color); margin: 10px auto 0; border-radius: 2px;
            background: var(--jhc-gradient);
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

        .btn-igd {
            background-color: #C8102E; /* Merah JHC */
            color: white !important;
            border-radius: 50px;
            padding: 8px 18px !important;
            font-weight: 700;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2px solid #C8102E;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-igd:hover {
            background-color: white;
            color: #C8102E !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Animasi sederhana untuk icon IGD */
        .pulse-icon {
            animation: pulse-red 2s infinite;
            border-radius: 50%;
        }

        .pulse-emergency {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); }
            70% { transform: scale(1.1); }
            100% { transform: scale(0.95); }
        }

        /* Kontainer Tombol Melayang di Kanan Bawah */
        .floating-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column; /* Menyusun ke atas */
            gap: 15px;
            z-index: 1030;
        }

        /* Styling Tombol IGD Melayang */
        .btn-igd-float {
            width: 60px;
            height: 60px;
            background: var(--jhc-gradient) !important;
            color: white !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.4);
            transition: all 0.3s ease;
            text-decoration: none;
            border: 2px solid white;
        }

        .btn-igd-float:hover {
            transform: scale(1.1) rotate(10deg);
            background-color: #002855; /* Navy saat hover */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* Efek Denyut untuk IGD */
        .pulse-igd {
            animation: pulse-red-infinite 2s infinite;
        }

        @keyframes pulse-red-infinite {
            0% { box-shadow: 0 0 0 0 rgba(200, 16, 46, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(200, 16, 46, 0); }
            100% { box-shadow: 0 0 0 0 rgba(200, 16, 46, 0); }
        }

        /* Penyesuaian Tombol WA agar sejajar */
        .btn-wa-float {
            width: 60px;
            height: 60px;
            background-color: #25D366;
            color: white !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            text-decoration: none;
            border: 2px solid white;
        }
        
        .btn-wa-float:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
        }

        
        /* Membuat logo grayscale (hitam putih) lalu berwarna saat dihover */
        .partner-logo {
            filter: grayscale(100%);
            opacity: 0.6;
            transition: all 0.4s ease;
            max-height: 70px; /* Membatasi tinggi agar seragam */
            width: auto;
            object-fit: contain;
        }

        .partner-logo:hover {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.1); /* Efek zoom sedikit */
        }

        .partner-logo {
        /* Mematikan grayscale agar langsung berwarna */
        filter: grayscale(0%) !important; 
        opacity: 1 !important;
        
        /* Mengatur ukuran agar lebih besar */
        max-height: 95px; /* Sebelumnya biasanya 60px, sekarang diperbesar */
        width: auto;      /* Lebar menyesuaikan proporsi */
        object-fit: contain;
        
        /* Efek transisi halus saat disentuh */
        transition: transform 0.3s ease;
        }

        /* Efek Zoom sedikit saat mouse diarahkan (opsional) */
        .partner-logo:hover {
            transform: scale(1.1); 
        }
        
        /* Penyesuaian untuk layar HP agar tidak terlalu besar */
        @media (max-width: 576px) {
            .partner-logo {
                max-height: 70px;
            }
        }
        /* Warna Identitas JHC */
        
        /* Animasi Kartu di Index */
        #career-cta .card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        #career-cta .card:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
        }

        .btn-career-animate:hover {
            background-color: #155a82;
            padding-left: 3rem !important; /* Efek bergeser sedikit */
            letter-spacing: 1px;
        }

        /* Style Khusus Form di apply.php agar seragam */
        .form-control-modern {
            border: 2px solid #f1f3f5;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control-modern:focus {
            border-color: var(--jhc-blue);
            box-shadow: 0 0 0 0.25rem rgba(27, 113, 161, 0.1);
            background-color: #fff;
        }

        /* Pastikan tombol berada di lapisan atas dan kursor berubah */
        .btn-primary {
            position: relative;
            z-index: 10;
            cursor: pointer !important;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(27, 113, 161, 0.3) !important;
        }

        /* Animasi klik */
        .btn-primary:active {
            transform: translateY(0);
        }

        /* Style Tombol Apply Job di Navbar */
        .btn-janji {
            background-color: #1B71A1;
            color: white !important;
            transition: all 0.3s ease;
            border: 2px solid #1B71A1;
        }

        .btn-janji:hover {
            background-color: white;
            color: #1B71A1 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(27, 113, 161, 0.2);
        }

        /* Transisi halus saat pindah halaman */
        body {
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* 1. Efek Muncul Saat Hover pada Card */
        .doctor-card {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(0,0,0,0.05) !important;
        }

        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }

        /* 2. Animasi Gambar Dokter */
        .doctor-card img {
            transition: transform 0.5s ease;
        }

        .doctor-card:hover img {
            transform: scale(1.08); /* Gambar sedikit membesar saat hover */
        }

        /* 3. Animasi Modal (Smooth Fade & Slide) */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translateY(20px); /* Mulai sedikit dari bawah */
        }

        .modal.show .modal-dialog {
            transform: translateY(0);
        }

        /* 4. Efek Glassmorphism pada Modal Body */
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
        }

        /* 5. Animasi Loading Gambar Modal */
        #mdl-img {
            opacity: 0;
            animation: fadeInCircle 0.6s forwards;
            animation-delay: 0.2s;
        }

        @keyframes fadeInCircle {
            from { opacity: 0; transform: scale(0.8) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* 6. Animasi Teks Detail di Dalam Modal */
        #mdl-name, #mdl-specialty, #mdl-desc {
            opacity: 0;
            transform: translateY(10px);
            animation: slideUpText 0.5s forwards;
        }

        #mdl-name { animation-delay: 0.3s; }
        #mdl-specialty { animation-delay: 0.4s; }
        #mdl-desc { animation-delay: 0.5s; }

        @keyframes slideUpText {
            to { opacity: 1; transform: translateY(0); }
        }

        /* 7. Gaya Tombol Agar Menarik */
        .btn-detail-dokter {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-detail-dokter::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
            z-index: -1;
        }

        .btn-detail-dokter:hover::after {
            width: 200%;
            height: 200%;
        }
    </style>
  </head>
  <body>
    <main class="main" id="top">
      
     <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <?php $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
            <img src="public/<?php echo htmlspecialchars($header_logo); ?>" width="130" alt="JHC Logo" />
          </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
          </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto pt-2 pt-lg-0 font-base">
                <li class="nav-item px-2"><a class="nav-link" href="#departments">Layanan</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#virtual_room">Virtual Room</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#about_us">Tentang Kami</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#doctors">Dokter Kami</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#facilities">Fasilitas</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="#news">Berita</a></li>
            </ul>

            <div class="nav-actions ms-lg-4">
                <a class="btn btn-janji px-4 rounded-pill fw-bold" href="career.php">Apply Job</a>
            </div>
          </div>
        </div>
      </nav>

      <div class="floating-actions">
          <a href="tel:02653172112" class="btn-igd-float pulse-igd" title="Darurat IGD: <?php echo $no_igd; ?>">
              <i class="fas fa-ambulance"></i>
          </a>

          <a href="https://wa.me/<?php echo $no_rs_wa; ?>" target="_blank" class="btn-wa-float" title="WhatsApp RS: 085175000375">
              <i class="fab fa-whatsapp"></i>
          </a>
      </div>

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
                         data-title="Pusat Pelayanan Jantung Terpadu dengan Teknologi Mutakhir." 
                         data-description="Dengan teknologi mutakhir dan tim spesialis jantung terbaik di Indonesia, kami membantu Anda pulih lebih cepat, hidup lebih lama, dan kembali beraktivitas tanpa rasa takut. Kami mendengarkan, memahami, dan memberi perawatan khusus untuk Anda, karena setiap jantung punya cerita unik yang layak diselamatkan.">
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

        document.addEventListener('DOMContentLoaded', function() {
            const detailButtons = document.querySelectorAll('.btn-detail-dokter');
            
            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 1. Ambil data dari atribut tombol detail
                    const name = this.getAttribute('data-name');
                    const specialty = this.getAttribute('data-specialty');
                    const desc = this.getAttribute('data-desc') || "Tidak ada deskripsi tersedia.";
                    const img = this.getAttribute('data-img');
                    
                    // 2. Masukkan ke dalam elemen modal (Teks & Gambar)
                    document.getElementById('mdl-name').innerText = name;
                    document.getElementById('mdl-specialty').innerText = specialty;
                    document.getElementById('mdl-desc').innerText = desc;
                    document.getElementById('mdl-img').src = img;

                    // 3. Update Link Booking secara dinamis
                    // encodeURIComponent digunakan agar karakter spesial (spasi, titik) aman di URL
                    const bookingUrl = `booking.php?dokter=${encodeURIComponent(name)}&spesialis=${encodeURIComponent(specialty)}`;
                    
                    const bookBtn = document.getElementById('mdl-book-link');
                    if(bookBtn) {
                        bookBtn.href = bookingUrl;
                    }
                });
            });
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
                                        <a href="javascript:void(0)" 
                                        class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm btn-buka-detail" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalLayanan" 
                                        data-name="<?php echo htmlspecialchars($layanan['name']); ?>" 
                                        data-desc="<?php echo htmlspecialchars($layanan['description'] ?? ''); ?>" 
                                        data-expertise="<?php echo htmlspecialchars($layanan['expertise'] ?? ''); ?>" 
                                        data-education="<?php echo htmlspecialchars($layanan['education'] ?? ''); ?>" 
                                        data-icon="public/<?php echo htmlspecialchars($layanan['icon_path']); ?>">
                                        <i class="fas fa-calendar-check me-1"></i> Detail Layanan
                                        </a>
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
                                            <a href="javascript:void(0)" 
                                            class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm btn-buka-detail" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalLayanan" 
                                            data-name="<?php echo htmlspecialchars($poli['name']); ?>" 
                                            data-desc="<?php echo htmlspecialchars($poli['description'] ?? ''); ?>" 
                                            data-expertise="<?php echo htmlspecialchars($poli['expertise'] ?? ''); ?>" 
                                            data-education="<?php echo htmlspecialchars($poli['education'] ?? ''); ?>" 
                                            data-icon="public/<?php echo htmlspecialchars($poli['icon_path'] ?? ''); ?>">
                                            <i class="fas fa-calendar-check me-1"></i> Detail Layanan
                                            </a>
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

        <div class="modal fade" id="modalLayanan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 p-4 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px;">
                                <img id="m-icon" src="" style="width: 60px; height: 60px; object-fit: contain;">
                            </div>
                            <h3 id="m-name" class="fw-bold text-dark"></h3>
                            <hr style="width: 50px; margin: 15px auto; border-top: 3px solid #0d6efd; opacity: 1;">
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="fw-bold text-primary small text-uppercase mb-2 d-block"><i class="fas fa-info-circle me-2"></i>Tentang Layanan</label>
                                <p id="m-desc" class="text-muted"></p>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <label class="fw-bold text-dark small text-uppercase mb-2 d-block"><i class="fas fa-star me-2 text-warning"></i>Keahlian Khusus</label>
                                    <div id="m-expertise" class="text-muted" style="white-space: pre-line;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <label class="fw-bold text-dark small text-uppercase mb-2 d-block"><i class="fas fa-graduation-cap me-2 text-danger"></i>Informasi Tambahan</label>
                                    <div id="m-education" class="text-muted" style="white-space: pre-line;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <a href="booking.php" class="btn btn-primary rounded-pill px-4">Buat Janji Temu</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Delegasi event klik untuk semua tombol detail
                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btn-buka-detail');
                    
                    if (btn) {
                        // Berhenti jika tombol tidak memiliki target modal
                        const modalId = btn.getAttribute('data-bs-target');
                        const modalElement = document.querySelector(modalId);
                        
                        if (modalElement) {
                            // 1. Ambil data dari atribut tombol
                            const name = btn.getAttribute('data-name');
                            const desc = btn.getAttribute('data-desc') || 'Deskripsi tidak tersedia.';
                            const expertise = btn.getAttribute('data-expertise') || 'Informasi belum tersedia.';
                            const education = btn.getAttribute('data-education') || 'Informasi belum tersedia.';
                            const icon = btn.getAttribute('data-icon');

                            // 2. Isi konten Modal
                            document.getElementById('m-name').innerText = name;
                            document.getElementById('m-desc').innerText = desc;
                            document.getElementById('m-expertise').innerText = expertise;
                            document.getElementById('m-education').innerText = education;
                            document.getElementById('m-icon').src = icon;

                            // 3. Jalankan Modal secara manual
                            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                            modal.show();
                        }
                    }
                });
            });
        </script>

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
      <section class="py-5" id="virtual_room">
        <div class="container">
            <div class="row align-items-center gx-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="position-relative">
            <div class="bg-primary position-absolute rounded-3" style="width: 100%; height: 100%; top: 15px; left: -15px; z-index: -1;"></div>
                <?php if(!empty($vr_data['video_url'])): 
                        // Menambahkan parameter autoplay=1 dan mute=1 agar video langsung berputar
                        $embed_url = $vr_data['video_url'];
                        $sep = (strpos($embed_url, '?') !== false) ? '&' : '?';
                        $autoplay_url = $embed_url . $sep . "autoplay=1&mute=1&loop=1&playlist=" . basename(parse_url($embed_url, PHP_URL_PATH));
                    ?>
                        <div class="ratio ratio-16x9 shadow-lg rounded-3 overflow-hidden">
                            <iframe src="<?php echo htmlspecialchars($autoplay_url); ?>" 
                                    allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <img src="public/<?php echo htmlspecialchars($vr_data['image_path_360']); ?>" class="img-fluid rounded-3 shadow w-100" alt="Virtual Room">
                    <?php endif; ?>
                </div>
            </div>
                <div class="col-lg-6">
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
        
     <section class="py-5 bg-white" id="about_us">
        <div class="container">
            
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 text-center">
                    <h5 class="text-primary fw-bold text-uppercase">TENTANG KAMI</h5>
                    <h2 class="section-title fw-bold">Mengenal Lebih Dekat RS JHC</h2>
                    <hr style="width: 60px; border-top: 3px solid #D32F2F; margin: 15px auto; opacity: 1;">
                    <p class="text-muted">Dedikasi kami untuk pelayanan kesehatan terbaik bagi Anda dan keluarga.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="nav flex-column nav-pills shadow-sm rounded bg-light overflow-hidden" 
                        id="v-pills-tab" 
                        role="tablist" 
                        aria-orientation="vertical">
                        
                        <?php 
                        $no = 0;
                        foreach ($tabs_config as $key => $info): 
                            $active = ($no === 0) ? 'active' : '';
                            $selected = ($no === 0) ? 'true' : 'false';
                        ?>
                            <button class="nav-link <?php echo $active; ?> text-start py-3 fw-bold border-bottom" 
                                    id="v-pills-<?php echo $key; ?>-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-<?php echo $key; ?>" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="v-pills-<?php echo $key; ?>" 
                                    aria-selected="<?php echo $selected; ?>">
                                <i class="fas <?php echo $info['icon']; ?> me-2 text-danger"></i> <?php echo $info['label']; ?>
                            </button>
                        <?php 
                            $no++; 
                        endforeach; 
                        ?>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="tab-content p-4 border rounded shadow-sm bg-white" id="v-pills-tabContent" style="min-height: 400px;">
                        <?php 
                        $no = 0;
                        foreach ($tabs_config as $key => $info): 
                            // Class 'show active' hanya untuk tab pertama
                            $show_active = ($no === 0) ? 'show active' : '';
                            
                            // Cek Data
                            $row = isset($about_sections[$key]) ? $about_sections[$key] : null;
                            
                            // Default Data jika database kosong
                            $judul = ($row && !empty($row['title'])) ? $row['title'] : $info['label'];
                            $isi   = ($row && !empty($row['content'])) ? nl2br($row['content']) : "Konten belum tersedia.";
                            
                            // Cek Gambar (pastikan path public benar)
                            $img_src = ($row && !empty($row['image_path'])) ? 'public/' . $row['image_path'] : '';
                            
                            // Placeholder jika gambar kosong/rusak
                            $img_display = !empty($img_src) ? $img_src : "https://via.placeholder.com/800x400/f8f9fa/dee2e6?text=No+Image";
                        ?>
                            
                            <div class="tab-pane fade <?php echo $show_active; ?>" 
                                id="v-pills-<?php echo $key; ?>" 
                                role="tabpanel" 
                                aria-labelledby="v-pills-<?php echo $key; ?>-tab">
                                
                                <?php if ($key === 'salam-direktur'): ?>
                                    <div class="text-center">
                                        <img src="<?php echo htmlspecialchars($img_display); ?>" 
                                            class="rounded-circle shadow mb-3 border border-3 border-light" 
                                            style="width: 150px; height: 150px; object-fit: cover;"
                                            onerror="this.src='https://via.placeholder.com/150';">
                                            
                                        <h3 class="text-danger fw-bold"><?php echo htmlspecialchars($judul); ?></h3>
                                        <hr class="w-25 mx-auto text-danger mb-4">
                                        
                                        <div class="text-muted fst-italic px-md-5">
                                            <i class="fas fa-quote-left fa-lg text-danger opacity-25 me-2"></i>
                                            <?php echo $isi; ?>
                                            <i class="fas fa-quote-right fa-lg text-danger opacity-25 ms-2"></i>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <div class="row align-items-center">
                                        <div class="col-md-5 mb-3 mb-md-0">
                                            <img src="<?php echo htmlspecialchars($img_display); ?>" 
                                                class="img-fluid rounded shadow w-100" 
                                                style="object-fit: cover; height: 250px;"
                                                onerror="this.src='https://via.placeholder.com/800x400/f8f9fa/dee2e6?text=No+Image';">
                                        </div>
                                        <div class="col-md-7">
                                            <h3 class="text-danger fw-bold mb-3"><?php echo htmlspecialchars($judul); ?></h3>
                                            <div class="text-secondary text-justify" style="line-height: 1.8;">
                                                <?php echo $isi; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php 
                            $no++; 
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>


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
                                <div class="mx-auto mb-4" style="width: 140px; height: 140px;">
                                    <img src="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>" 
                                        class="w-100 h-100 rounded-circle border border-4 border-light shadow-sm" 
                                        style="object-fit: cover;" alt="Doctor">
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($doc['name']); ?></h5>
                                <p class="text-primary small fw-bold text-uppercase mb-3"><?php echo htmlspecialchars($doc['specialty']); ?></p>
                                
                                <button type="button" 
                                        class="btn btn-outline-primary btn-sm rounded-pill px-4 btn-detail-dokter" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetailDokter"
                                        data-name="<?php echo htmlspecialchars($doc['name']); ?>"
                                        data-specialty="<?php echo htmlspecialchars($doc['specialty']); ?>"
                                        data-desc="<?php echo htmlspecialchars($doc['description'] ?? 'Profil profesional dokter di JHC.'); ?>"
                                        data-img="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>">
                                    Lihat Profil
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div class="modal fade" id="modalDetailDokter" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <img id="mdl-img" src="" class="rounded-circle mb-3 border border-4 border-white shadow" 
                            style="width: 120px; height: 120px; object-fit: cover; margin-top: -60px;">
                        
                        <h4 id="mdl-name" class="fw-bold text-dark mb-1"></h4>
                        <p id="mdl-specialty" class="text-primary small fw-bold text-uppercase mb-4"></p>
                        
                        <div class="text-start border-top pt-3">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Tentang Dokter:</h6>
                            <p id="mdl-desc" class="small text-secondary"></p>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <a id="mdl-book-link" href="booking.php" class="btn btn-danger rounded-pill shadow-sm py-2 fw-bold">
                                <i class="fas fa-calendar-check me-2"></i>Buat Janji Temu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

      <section class="py-5 bg-white" id="partners">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-12 text-center">
                    <h4 class="fw-bold text-secondary">MITRA ASURANSI & PERUSAHAAN</h4>
                    <hr style="width: 60px; margin: 15px auto; border-top: 3px solid #C8102E; opacity: 1;">
                </div>
            </div>

            <div class="row justify-content-center align-items-center g-4">
                <?php if (!empty($partners_data)): ?>
                    <?php foreach($partners_data as $partner): ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center">
                            <div class="p-2">
                                <a href="<?php echo htmlspecialchars(!empty($partner['url']) ? $partner['url'] : '#'); ?>" 
                                target="<?php echo !empty($partner['url']) ? '_blank' : '_self'; ?>" 
                                class="partner-link"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top"
                                title="<?php echo htmlspecialchars($partner['name']); ?>">
                                    
                                    <img src="public/<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                                        class="img-fluid partner-logo" 
                                        alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                        onerror="this.src='public/assets/img/gallery/default-partner.png';"> 
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">
                        <p>Belum ada mitra yang ditampilkan.</p>
                    </div>
                <?php endif; ?>
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
                <?php foreach(array_slice($layanan_data, 0, 5) as $d): ?>
                    <li class="lh-lg"><a class="text-200" href="#departments"><?php echo htmlspecialchars($d['name']); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-3 order-sm-2">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif">Useful Links</h5>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="text-200" href="#departments">Layanan</a></li>
                <li class="lh-lg"><a class="text-200" href="#virtual_room">Virtual Room</a></li>
                <li class="lh-lg"><a class="text-200" href="#about_us">Tentang Kami</a></li>
                <li class="lh-lg"><a class="text-200" href="#doctors">Dokter Kami</a></li>
                <li class="lh-lg"><a class="text-200" href="#facilities">Fasilitas</a></li>
                <li class="lh-lg"><a class="text-200" href="#news">Berita</a></li>
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
