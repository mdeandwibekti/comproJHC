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
$tabs_config = [
    'visi-misi'      => ['label' => 'Visi & Misi', 'icon' => 'fa-bullseye'],
    'sejarah'        => ['label' => 'Sejarah',     'icon' => 'fa-history'],
    'salam-direktur' => ['label' => 'Salam Direktur','icon' => 'fa-user-tie'],
    'budaya-kerja'   => ['label' => 'Budaya Kerja', 'icon' => 'fa-hand-holding-heart']
];

// 2. Ambil data dari tabel 'about_us_sections'
$about_sections = []; // Kita gunakan nama ini agar sinkron dengan kode HTML sebelumnya
$res = $mysqli->query("SELECT * FROM about_us_sections");
if ($res) {
    while($row = $res->fetch_assoc()) { 
        // Menggunakan section_key sebagai indeks (visi-misi, sejarah, dll)
        $about_sections[$row['section_key']] = $row; 
    }
}

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

$sql = "SELECT id, name, category, icon_path, icon_hover_path, description, 
               special_skills, additional_info, btn_text, btn_link 
        FROM departments 
        ORDER BY display_order ASC";

$dept_result = $mysqli->query($sql);

if ($dept_result) { 
    while($row = $dept_result->fetch_assoc()) { 
        $row['description']    = $row['description'] ?? '';
        $row['special_skills'] = $row['special_skills'] ?? '';
        $row['additional_info']= $row['additional_info'] ?? '';
        
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
$partner_result = $mysqli->query("SELECT name, logo_path, url FROM partners ORDER BY name ASC");
if ($partner_result) {
    while($row = $partner_result->fetch_assoc()) {
        $partners_data[] = $row;
    }
}

$facilities_data = [];
$fac_result = $mysqli->query("SELECT * FROM facilities ORDER BY display_order ASC");
if ($fac_result) { while($row = $fac_result->fetch_assoc()) { $facilities_data[] = $row; } }

// Popup settings
$popup_res = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'popup_%'");
$p_data = [];
if ($popup_res) {
    while($row = $popup_res->fetch_assoc()) {
        $p_data[$row['setting_key']] = $row['setting_value'];
    }
}
$show_popup = (($p_data['popup_status'] ?? '') === 'active');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <?php 
  $favicon = !empty($settings['favicon_path']) ? $settings['favicon_path'] : 'assets/img/favicons/favicon.ico';
  ?>
  <link rel="shortcut icon" type="image/x-icon" href="public/<?php echo htmlspecialchars($favicon); ?>">
  <link href="public/assets/css/theme.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    /* ============================================================
       ROOT & GLOBAL
    ============================================================ */
    :root {
      --red-dark:  #8a3033;
      --red:       #C8102E;
      --red-light: #bd3030;
      --navy:      #002855;
      --blue:      #1B71A1;
      --grad:      linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
      --ease:      cubic-bezier(.4,0,.2,1);
      --shadow-sm: 0 2px 8px rgba(0,0,0,.07);
      --shadow-md: 0 4px 18px rgba(0,0,0,.10);
      --shadow-lg: 0 10px 35px rgba(0,0,0,.12);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
      background: #fff;
    }

    img { max-width: 100%; display: block; }

    a { text-decoration: none; }

    section { padding: 5rem 0; }

    /* ============================================================
       NAVBAR
    ============================================================ */
    .navbar {
      padding: .75rem 0;
      background: rgba(255,255,255,.98) !important;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      box-shadow: var(--shadow-sm);
      transition: all .35s var(--ease);
    }

    .navbar.scrolled {
      padding: .5rem 0;
      box-shadow: 0 2px 0 var(--red), var(--shadow-md);
    }

    .navbar .container { max-width: 1320px; }

    .navbar-brand img {
      height: 60px;
      width: auto;
      object-fit: contain;
      transition: all .35s var(--ease);
    }

    .navbar.scrolled .navbar-brand img { height: 50px; }

    /* Nav links */
    .navbar-nav .nav-link {
      color: var(--navy) !important;
      font-weight: 600;
      font-size: .875rem;
      padding: .6rem .85rem !important;
      position: relative;
      transition: color .25s var(--ease);
    }

    .navbar-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 2px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 2.5px;
      background: var(--grad);
      border-radius: 3px;
      transition: width .3s var(--ease);
    }

    .navbar-nav .nav-link:hover { color: var(--red) !important; }
    .navbar-nav .nav-link:hover::after { width: 75%; }

    /* Navbar toggler */
    .navbar-toggler {
      border: 2px solid var(--red);
      border-radius: 8px;
      padding: .35rem .55rem;
    }

    .navbar-toggler:focus { box-shadow: 0 0 0 3px rgba(200,16,46,.2); }

    /* Apply Job button */
    .btn-nav-cta {
      background: var(--grad);
      color: #fff !important;
      border-radius: 50px;
      padding: .55rem 1.5rem;
      font-size: .85rem;
      font-weight: 700;
      border: 2px solid transparent;
      transition: all .3s var(--ease);
      box-shadow: 0 4px 14px rgba(200,16,46,.22);
      white-space: nowrap;
    }

    .btn-nav-cta:hover {
      background: #fff;
      color: var(--red) !important;
      border-color: var(--red);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(200,16,46,.28);
    }

    /* Mobile nav */
    @media (max-width: 991px) {
      .navbar-collapse {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        margin-top: .75rem;
        padding: 1rem;
        border-top: 3px solid var(--red);
      }

      .navbar-nav .nav-link {
        padding: .75rem 1rem !important;
        border-radius: 10px;
      }

      .navbar-nav .nav-link:hover { background: #fff5f5; }

      .btn-nav-cta {
        display: block;
        text-align: center;
        margin-top: .5rem;
      }
    }

    /* ============================================================
       FLOATING BUTTONS
    ============================================================ */
    .float-wrap {
      position: fixed;
      bottom: 22px;
      right: 22px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      z-index: 1030;
    }

    .float-btn {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      border: 3px solid #fff;
      box-shadow: var(--shadow-lg);
      transition: all .3s var(--ease);
      cursor: pointer;
    }

    .float-igd { background: var(--grad); color: #fff !important; }
    .float-wa  { background: #25D366;      color: #fff !important; }

    .float-igd:hover { transform: scale(1.12) rotate(8deg); box-shadow: 0 8px 30px rgba(200,16,46,.45); }
    .float-wa:hover  { transform: scale(1.12);               box-shadow: 0 8px 30px rgba(37,211,102,.45); }

    @keyframes pulse-igd {
      0%,100% { box-shadow: 0 0 0 0 rgba(200,16,46,.65), var(--shadow-lg); }
      60%      { box-shadow: 0 0 0 16px rgba(200,16,46,0),  var(--shadow-lg); }
    }
    .float-igd { animation: pulse-igd 2.2s infinite; }

    @media (max-width: 575px) {
      .float-btn { width: 48px; height: 48px; font-size: 19px; }
    }

    /* ============================================================
       HERO SECTION
    ============================================================ */
    .hero-section {
      position: relative;
      width: 100%;
      height: 100vh;
      min-height: 560px;
      max-height: 920px;
      overflow: hidden;
      background: #000;
    }

    #heroCarousel, .carousel-inner, .carousel-item { height: 100%; }

    .bg-holder {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .carousel-item.active .bg-holder {
      animation: hero-zoom 14s ease-in-out forwards;
    }

    @keyframes hero-zoom {
      from { transform: scale(1); }
      to   { transform: scale(1.08); }
    }

    .banner-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(100deg, rgba(0,16,36,.88) 0%, rgba(138,48,51,.42) 65%, transparent 100%);
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      height: 100%;
    }

    .carousel-item h1 {
      font-size: clamp(1.9rem, 5vw, 3.4rem);
      font-weight: 800;
      color: #fff !important;
      text-shadow: 0 3px 14px rgba(0,0,0,.45);
      line-height: 1.2;
    }

    .carousel-item p.lead {
      font-size: clamp(.95rem, 2vw, 1.1rem);
      color: rgba(255,255,255,.92) !important;
      text-shadow: 0 2px 6px rgba(0,0,0,.4);
      max-width: 600px;
      line-height: 1.75;
    }

    /* Carousel controls */
    .carousel-control-prev,
    .carousel-control-next {
      width: 44px;
      height: 44px;
      top: 50%;
      transform: translateY(-50%);
      border-radius: 50%;
      background: rgba(200,16,46,.7);
      opacity: .85;
      transition: all .3s var(--ease);
    }

    .carousel-control-prev { left: 18px; }
    .carousel-control-next { right: 18px; }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
      background: rgba(200,16,46,.95);
      transform: translateY(-50%) scale(1.08);
    }

    .carousel-indicators {
      bottom: 1.5rem;
      gap: 6px;
    }

    .carousel-indicators button {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: rgba(255,255,255,.55);
      border: 2px solid rgba(255,255,255,.8);
      transition: all .3s;
      padding: 0;
    }

    .carousel-indicators button.active {
      background: var(--red);
      width: 24px;
      border-radius: 5px;
    }

    @media (max-width: 767px) {
      .hero-section { height: 75vh; min-height: 450px; }
    }

    /* ============================================================
       SECTION HEADINGS
    ============================================================ */
    .section-label {
      display: inline-block;
      color: var(--red);
      font-weight: 700;
      font-size: .78rem;
      letter-spacing: 1.8px;
      text-transform: uppercase;
      margin-bottom: .6rem;
    }

    .section-title {
      font-size: clamp(1.7rem, 4vw, 2.4rem);
      font-weight: 800;
      color: var(--navy);
      margin-bottom: .5rem;
    }

    .section-divider {
      width: 52px;
      height: 4px;
      background: var(--grad);
      border-radius: 10px;
      margin: .9rem auto 0;
    }

    /* ============================================================
       BUTTONS
    ============================================================ */
    .btn-jhc {
      background: var(--grad);
      color: #fff !important;
      border: 2px solid transparent;
      border-radius: 50px;
      padding: .55rem 1.5rem;
      font-size: .85rem;
      font-weight: 700;
      transition: all .3s var(--ease);
      box-shadow: 0 4px 14px rgba(200,16,46,.22);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .btn-jhc:hover {
      background: #fff;
      color: var(--red) !important;
      border-color: var(--red);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(200,16,46,.28);
    }

    .btn-jhc-sm {
      padding: .45rem 1.1rem;
      font-size: .8rem;
    }

    .btn-jhc-outline {
      background: transparent;
      color: var(--red) !important;
      border: 2px solid var(--red);
      border-radius: 50px;
      padding: .5rem 1.3rem;
      font-size: .82rem;
      font-weight: 700;
      transition: all .3s var(--ease);
      display: inline-flex;
      align-items: center;
      cursor: pointer;
    }

    .btn-jhc-outline:hover {
      background: var(--grad);
      color: #fff !important;
      border-color: transparent;
      transform: translateY(-2px);
    }

    /* ============================================================
       CARDS — HOVER LIFT
    ============================================================ */
    .hover-lift {
      transition: transform .35s var(--ease), box-shadow .35s var(--ease);
    }

    .hover-lift:hover {
      transform: translateY(-7px);
      box-shadow: var(--shadow-lg) !important;
    }

    /* ============================================================
       SERVICE CARDS
    ============================================================ */
    .service-card {
      border-radius: 18px;
      border: 2px solid transparent;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      height: 100%;
      transition: all .35s var(--ease);
    }

    .service-card:hover {
      border-color: rgba(200,16,46,.18);
      box-shadow: var(--shadow-md);
    }

    .icon-wrap {
      width: 76px;
      height: 76px;
      background: linear-gradient(135deg,#f8f9fa,#e9ecef);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.1rem;
      transition: all .35s var(--ease);
    }

    .service-card:hover .icon-wrap {
      background: linear-gradient(135deg,#fff5f5,#ffe0e0);
      transform: scale(1.1) rotate(5deg);
    }

    .icon-wrap img { width: 44px; height: 44px; object-fit: contain; }

    /* ============================================================
       DOCTOR CARDS
    ============================================================ */
    .doctor-card {
      border-radius: 22px;
      background: #fff;
      box-shadow: 0 8px 28px rgba(0,0,0,.06);
      transition: all .4s var(--ease);
      text-align: center;
      overflow: hidden;
    }

    .doctor-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 18px 40px rgba(138,48,51,.12);
    }

    .doctor-img-ring {
      width: 128px;
      height: 128px;
      border-radius: 50%;
      overflow: hidden;
      border: 5px solid #fff;
      box-shadow: 0 4px 16px rgba(0,0,0,.12);
      margin: 0 auto;
      transition: transform .4s var(--ease);
    }

    .doctor-card:hover .doctor-img-ring { transform: scale(1.06); }

    .doctor-img-ring img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* ============================================================
       ABOUT TABS
    ============================================================ */
    .about-image-wrapper {
    position: relative;
    height: 450px;
    background: #f8f9fa;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    #main-about-image {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Menjaga proporsi gambar */
        transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
    }

    .custom-tab-btn {
        position: relative;
        padding: 10px 20px;
        color: #6c757d !important;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none !important;
    }

    .custom-tab-btn.active {
        color: #8a3033 !important;
    }

    .custom-tab-btn::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: #8a3033;
        transition: 0.3s;
        transform: translateX(-50%);
    }

    .custom-tab-btn.active::after {
        width: 80%;
    }

    #aboutTabContent {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border-left: 6px solid #8a3033;
        min-height: 350px;
        padding: 2rem;
    }

    @keyframes slideFromRight {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .slide-from-right.active {
        animation: slideFromRight 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    }

    /* ============================================================
       MCU CARDS
    ============================================================ */
    .mcu-card {
      border-radius: 18px;
      overflow: hidden;
      transition: all .32s var(--ease);
    }

    .mcu-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg) !important; }

    .mcu-img-wrap { position: relative; height: 195px; overflow: hidden; }

    .mcu-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .5s var(--ease);
    }

    .mcu-card:hover .mcu-img-wrap img { transform: scale(1.07); }

    .mcu-price-tag {
      position: absolute;
      bottom: 12px;
      left: 12px;
      background: rgba(255,255,255,.95);
      backdrop-filter: blur(6px);
      color: #0066cc;
      font-weight: 800;
      padding: 5px 14px;
      border-radius: 10px;
      font-size: .88rem;
    }

    /* ============================================================
       FACILITIES
    ============================================================ */
    .fac-card {
      border-radius: 18px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    .fac-img { height: 235px; overflow: hidden; }

    .fac-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .55s var(--ease);
    }

    .fac-card:hover .fac-img img { transform: scale(1.08); }

    /* ============================================================
       NEWS CARDS
    ============================================================ */
    .news-date-badge {
      position: absolute;
      top: 1rem;
      left: 1rem;
      background: var(--grad);
      color: #fff !important;
      padding: .4rem .9rem;
      border-radius: 50px;
      font-size: .7rem;
      font-weight: 700;
      z-index: 5;
      box-shadow: var(--shadow-md);
    }

    .news-card { border-radius: 18px; overflow: hidden; box-shadow: var(--shadow-sm); }
    .news-img   { height: 235px; overflow: hidden; }

    .news-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .5s var(--ease);
    }

    .news-card:hover .news-img img { transform: scale(1.07); }

    .news-read-more {
      color: var(--blue);
      font-weight: 700;
      font-size: .85rem;
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      transition: all .25s var(--ease);
    }

    .news-read-more:hover { color: var(--red); transform: translateX(4px); }

    /* ============================================================
       PARTNERS
    ============================================================ */
    .partner-logo {
      max-height: 65px;
      width: auto;
      object-fit: contain;
      transition: transform .3s var(--ease), filter .3s;
      filter: grayscale(0%);
    }

    .partner-logo:hover { transform: scale(1.08); filter: grayscale(0%); }

    /* ============================================================
       MODALS
    ============================================================ */
    .modal-content {
      border-radius: 20px;
      border: none;
      overflow: hidden;
      box-shadow: var(--shadow-lg);
    }

    .modal-header {
      background: var(--grad);
      color: #fff !important;
      border: none;
      padding: 1.1rem 1.75rem;
    }

    .modal-header .modal-title { color: #fff !important; font-weight: 700; }

    .modal-header .btn-close {
      filter: brightness(0) invert(1);
      opacity: 1;
    }

    /* ============================================================
       FORM CONTROLS
    ============================================================ */
    .form-control {
      border-radius: 12px;
      padding: .75rem 1.1rem;
      border: 2px solid #e5e9f0;
      font-size: .9rem;
      transition: all .25s var(--ease);
    }

    .form-control:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 4px rgba(27,113,161,.1);
      background: #fff;
    }

    /* ============================================================
       FOOTER
    ============================================================ */
    footer { background: var(--grad); color: #fff; }

    .footer-link {
      color: rgba(255,255,255,.72);
      font-size: .88rem;
      display: inline-block;
      transition: all .25s var(--ease);
    }

    .footer-link:hover { color: #fff; transform: translateX(5px); }

    .footer-icon-circle {
      width: 34px;
      height: 34px;
      background: rgba(255,255,255,.12);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .85rem;
      flex-shrink: 0;
    }

    .social-btn {
      width: 40px;
      height: 40px;
      background: rgba(255,255,255,.12);
      color: #fff;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .28s var(--ease);
    }

    .social-btn:hover {
      background: rgba(255,255,255,.28);
      color: #fff;
      transform: translateY(-3px);
    }

    /* ============================================================
       PROMO POPUP
    ============================================================ */
    #promoPopup .modal-content {
      border-radius: 22px;
      overflow: hidden;
    }

    #promoPopup .modal-body { padding: 0; }

    .popup-img-wrap {
      overflow: hidden;
      max-height: 420px;
    }

    .popup-img-wrap img {
      width: 100%;
      height: auto;
      object-fit: cover;
      transition: transform .6s var(--ease);
    }

    #promoPopup:hover .popup-img-wrap img { transform: scale(1.04); }

    #promoPopup .btn-close {
      position: absolute;
      top: 12px;
      right: 12px;
      z-index: 60;
      background: rgba(255,255,255,.9);
      border-radius: 50%;
      padding: 9px;
      box-shadow: var(--shadow-md);
      opacity: .85;
      transition: all .25s;
    }

    #promoPopup .btn-close:hover { opacity: 1; transform: rotate(90deg) scale(1.1); }

    .modal.fade .modal-dialog {
      transform: scale(.92) translateY(18px);
      transition: transform .45s cubic-bezier(.34,1.56,.64,1);
    }

    .modal.show .modal-dialog { transform: scale(1) translateY(0); }

    /* ============================================================
       LOADING SPINNER
    ============================================================ */
    .spinner-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(255,255,255,.9);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .spinner-overlay.active { display: flex; }

    /* ============================================================
       UTILITIES
    ============================================================ */
    .text-jhc-red  { color: var(--red) !important; }
    .text-jhc-navy { color: var(--navy) !important; }
    .bg-jhc        { background: var(--grad); }
    .rounded-xl    { border-radius: 18px; }
    .rounded-2xl   { border-radius: 22px; }

    @media (max-width: 575px) {
      section { padding: 3rem 0; }
      .section-title { font-size: 1.55rem; }
    }
  </style>
</head>
<body>

<!-- ── Loading Spinner ───────────────────────────────────────── -->
<div class="spinner-overlay" id="loadingSpinner">
  <div class="spinner-border text-danger" style="width:3rem;height:3rem;" role="status">
    <span class="visually-hidden">Loading…</span>
  </div>
</div>

<main id="top">

  <!-- ============================================================
       NAVBAR
  ============================================================ -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNavbar">
    <div class="container">

      <a class="navbar-brand" href="index.php">
        <?php $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
        <img src="public/<?php echo htmlspecialchars($header_logo); ?>" alt="JHC Logo">
      </a>

      <button class="navbar-toggler border-0" type="button"
              data-bs-toggle="collapse" data-bs-target="#navMain"
              aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="index.php#about_us">Tentang Kami</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#departments">Layanan</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#facilities">Fasilitas</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#mcu_packages_data">Paket MCU</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#virtual_room">Virtual Room</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#doctors">Dokter Kami</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#news">Berita</a></li>
        </ul>

        <div class="ms-lg-3 mt-2 mt-lg-0">
          <a class="btn-nav-cta" href="career.php">
            <i class="fas fa-briefcase me-2"></i>Apply Job
          </a>
        </div>
      </div>

    </div>
  </nav>

  <!-- ============================================================
       FLOATING BUTTONS
  ============================================================ -->
  <div class="float-wrap">
    <a href="tel:<?php echo $no_igd; ?>" class="float-btn float-igd"
       title="Darurat IGD: <?php echo $no_igd; ?>" aria-label="Telepon IGD">
      <i class="fas fa-ambulance"></i>
    </a>
    <a href="https://wa.me/<?php echo $no_rs_wa; ?>" target="_blank" rel="noopener"
       class="float-btn float-wa" title="WhatsApp RS" aria-label="WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>
  </div>

  <!-- ============================================================
       HERO / BANNER
  ============================================================ -->
  <section class="hero-section p-0" id="home">
    <div id="heroCarousel" class="carousel slide carousel-fade h-100"
         data-bs-ride="carousel" data-bs-interval="5000">

      <div class="carousel-inner h-100">
        <?php if (!empty($banners_data)): ?>
          <?php foreach ($banners_data as $idx => $banner): ?>
          <div class="carousel-item h-100 <?= $idx === 0 ? 'active' : ''; ?>">
            <div class="bg-holder"
                 style="background-image:url(public/<?= htmlspecialchars($banner['image_path']); ?>);"></div>
            <div class="banner-overlay"></div>

            <div class="container hero-content d-flex align-items-center">
              <div class="col-lg-8 text-center text-lg-start text-white pt-5">
                <h1 class="mb-3"><?= htmlspecialchars($banner['title']); ?></h1>
                <p class="lead mb-0"><?= htmlspecialchars($banner['description']); ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <button class="carousel-control-prev" type="button"
              data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button"
              data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>

      <div class="carousel-indicators">
        <?php foreach ($banners_data as $idx => $b): ?>
          <button type="button" data-bs-target="#heroCarousel"
                  data-bs-slide-to="<?= $idx; ?>"
                  <?= $idx === 0 ? 'class="active" aria-current="true"' : ''; ?>
                  aria-label="Slide <?= $idx+1; ?>"></button>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

    <!-- ============================================================
        ABOUT US
    ============================================================ -->
    <section id="about_us" class="py-5 overflow-hidden">
      <div class="container">
        <div class="row g-5 align-items-center">
          
          <div class="col-lg-4">
            <div class="about-image-wrapper">
              <?php 
                $first_key = array_key_first($tabs_config);
                
                // Ambil path yang tersimpan di database (contoh: assets/img/gallery/nama_file.jpg)
                $db_path = $about_sections[$first_key]['image_path'] ?? '';
                
                // Gabungkan dengan prefix 'public/' agar mengarah ke folder yang benar
                // Jika kolom image_path di DB sudah menyertakan 'assets/img/gallery/', tinggal tambah 'public/'
                if (!empty($db_path)) {
                    $display_img = 'public/' . $db_path;
                } else {
                    // Gambar default jika data di database kosong
                    $display_img = 'public/assets/img/default-about.jpg'; 
                }
              ?>
              <img src="<?= $display_img; ?>" 
                  id="main-about-image" 
                  alt="Tentang JHC" 
                  style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          </div>

          <div class="col-lg-8">
            <ul class="nav nav-tabs border-0 mb-4 flex-nowrap overflow-auto pb-2" id="aboutTab" role="tablist">
              <?php $no = 0; foreach ($tabs_config as $key => $info): $active = ($no === 0) ? 'active' : ''; ?>
                <li class="nav-item">
                  <button class="nav-link <?= $active; ?> custom-tab-btn" 
                          data-bs-toggle="tab" 
                          data-bs-target="#content-<?= $key; ?>" 
                          type="button" role="tab"
                          /* Atribut data-img mengambil path murni dari kolom image_path di database */
                          data-img="<?= $about_sections[$key]['image_path'] ?? ''; ?>">
                    <i class="fas <?= $info['icon']; ?> me-2"></i><?= $info['label']; ?>
                  </button>
                </li>
              <?php $no++; endforeach; ?>
            </ul>

            <div class="tab-content" id="aboutTabContent">
              <?php $no = 0; foreach ($tabs_config as $key => $info): $show = ($no === 0) ? 'show active' : ''; ?>
                <div class="tab-pane fade <?= $show; ?> slide-from-right" id="content-<?= $key; ?>" role="tabpanel">
                  <h3 class="text-danger fw-bold mb-3"><?= htmlspecialchars($about_sections[$key]['title'] ?? $info['label']); ?></h3>
                  <div class="text-secondary lh-lg fs-5" style="text-align: justify;">
                    <?= (isset($about_sections[$key]['content']) && $about_sections[$key]['content'] !== '') 
                        ? nl2br(htmlspecialchars((string)$about_sections[$key]['content'])) 
                        : 'Konten belum tersedia.'; ?>
                  </div>
                </div>
              <?php $no++; endforeach; ?>
            </div>
          </div>

        </div>
      </div>
    </section>
  <!-- ============================================================
       SERVICES / DEPARTMENTS
  ============================================================ -->
  <section id="departments" class="bg-white">
    <div class="container">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <span class="section-label"><i class="fas fa-star me-2"></i>Layanan Terbaik</span>
          <h2 class="section-title">Pelayanan Kami</h2>
          <div class="section-divider mx-auto"></div>
          <p class="text-muted mt-4">Layanan unggulan dan poliklinik spesialis untuk kesehatan Anda.</p>
        </div>
      </div>

      <?php 
      function render_dept_cards($data_list, $is_layanan = true) {
        foreach($data_list as $item): ?>
          <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card service-card hover-lift text-center p-3 p-md-4">
              <div class="card-body p-1">
                <div class="icon-wrap">
                  <?php if(!empty($item['icon_path'])): ?>
                    <img src="<?= htmlspecialchars($item['icon_path']); ?>" alt="ikon">
                  <?php else: ?>
                    <i class="fas <?= $is_layanan ? 'fa-star text-warning' : 'fa-heartbeat text-primary'; ?> fa-2x"></i>
                  <?php endif; ?>
                </div>
                <h5 class="fw-bold text-dark mb-3" style="font-size:clamp(.875rem,2vw,1rem);line-height:1.3;">
                  <?= htmlspecialchars($item['name']); ?>
                </h5>
                <?php if (!$is_layanan): ?>
                  <a href="doctors_list.php?dept_id=<?= $item['id']; ?>"
                     class="btn-jhc btn-jhc-sm">
                    <i class="fas fa-user-md me-1"></i> Lihat Dokter
                  </a>
                <?php else: ?>
                  <button class="btn-jhc btn-jhc-sm btn-open-layanan"
                          data-name="<?= htmlspecialchars($item['name']); ?>"
                          data-desc="<?= htmlspecialchars($item['description']); ?>"
                          data-expertise="<?= htmlspecialchars($item['special_skills']); ?>"
                          data-info="<?= htmlspecialchars($item['additional_info']); ?>"
                          data-image="<?= htmlspecialchars($item['icon_path'] ?? ''); ?>"
                          data-btn-text="<?= htmlspecialchars($item['btn_text'] ?? 'Hubungi Kami'); ?>"
                          data-btn-link="<?= htmlspecialchars($item['btn_link'] ?? '#'); ?>"
                          data-bs-toggle="modal" data-bs-target="#modalLayanan">
                    <i class="fas fa-info-circle me-1"></i> Detail
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach;
      }
      ?>

      <!-- Layanan Unggulan -->
      <?php if (!empty($layanan_data)): ?>
      <div class="mb-5">
        <div class="text-center mb-4">
          <h4 class="fw-bold text-jhc-navy text-uppercase" style="font-size:1rem;letter-spacing:1px;">
            <i class="fas fa-award me-2 text-warning"></i>Layanan Unggulan
          </h4>
          <div style="width:60px;height:3px;background:var(--grad);margin:.75rem auto 0;border-radius:3px;"></div>
        </div>
        <div class="row gx-3 gx-md-4">
          <?php render_dept_cards($layanan_data, true); ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Poliklinik Spesialis -->
      <?php if (!empty($poliklinik_data)): ?>
      <div class="mt-4 pt-3">
        <div class="text-center mb-4">
          <h4 class="fw-bold text-secondary text-uppercase" style="font-size:1rem;letter-spacing:1px;">
            <i class="fas fa-stethoscope me-2"></i>Poliklinik Spesialis
          </h4>
          <div style="width:60px;height:3px;background:var(--grad);margin:.75rem auto 0;border-radius:3px;"></div>
        </div>
        <div class="row gx-3 gx-md-4">
          <?php render_dept_cards($poliklinik_data, false); ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal: Detail Layanan -->
  <div class="modal fade" id="modalLayanan" tabindex="-1" aria-labelledby="modalLayananLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLayananLabel">
            <i class="fas fa-info-circle me-2"></i>Detail Layanan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="text-center mb-4">
            <img id="m-image" src="" alt="Gambar Layanan"
                 class="img-fluid rounded-3 shadow-sm mb-3"
                 style="max-height:220px;width:100%;object-fit:cover;display:none;">
            <h3 id="m-name" class="fw-bold text-dark"></h3>
          </div>
          <div class="row g-4">
            <div class="col-12">
              <p class="text-muted fw-semibold small text-uppercase mb-1">
                <i class="fas fa-align-left me-1 text-danger"></i>Tentang Layanan
              </p>
              <p id="m-desc" class="text-secondary mb-0"></p>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 h-100 border-start border-4 border-warning">
                <p class="fw-bold small text-uppercase mb-2">
                  <i class="fas fa-star me-1 text-warning"></i>Keahlian Khusus
                </p>
                <div id="m-expertise" class="text-muted small" style="white-space:pre-line;"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 h-100 border-start border-4 border-primary">
                <p class="fw-bold small text-uppercase mb-2 text-primary">
                  <i class="fas fa-clock me-1"></i>Informasi Layanan
                </p>
                <div id="m-info" class="text-muted small" style="white-space:pre-line;"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
          <a id="m-btn" href="#" class="btn-jhc">
            <span id="m-btn-text">Hubungi Kami</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       FACILITIES
  ============================================================ -->
  <?php if (!empty($facilities_data)): ?>
  <section id="facilities" style="background:linear-gradient(135deg,#F0F9FF 0%,#E3F2FD 100%);">
    <div class="container">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <span class="section-label"><i class="fas fa-building me-2"></i>Fasilitas</span>
          <h2 class="section-title">Fasilitas Unggulan</h2>
          <div class="section-divider mx-auto"></div>
          <p class="text-muted mt-4">Fasilitas modern dan lengkap untuk kenyamanan Anda.</p>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach($facilities_data as $fac): ?>
        <div class="col-md-6 col-lg-4">
          <div class="fac-card hover-lift bg-white h-100">
            <div class="fac-img position-relative">
              <img src="public/<?= htmlspecialchars($fac['image_path']); ?>"
                   alt="<?= htmlspecialchars($fac['name']); ?>">
              <div class="position-absolute bottom-0 start-0 w-100 p-3"
                   style="background:linear-gradient(to top,rgba(0,0,0,.8),transparent);">
                <h5 class="text-white mb-0 fw-bold" style="font-size:clamp(.9rem,2vw,1.05rem);">
                  <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($fac['name']); ?>
                </h5>
              </div>
            </div>
            <div class="card-body p-4">
              <p class="text-muted mb-0" style="line-height:1.75;font-size:.9rem;">
                <?= nl2br(htmlspecialchars($fac['description'])); ?>
              </p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================
       MCU PACKAGES
  ============================================================ -->
  <?php if (!empty($mcu_packages_data)): ?>
  <section id="mcu_packages_data" class="bg-light">
    <div class="container">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <span class="section-label"><i class="fas fa-file-medical me-2"></i>Layanan Check Up</span>
          <h2 class="section-title">Pilih Paket Kesehatan Anda</h2>
          <div class="section-divider mx-auto"></div>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach ($mcu_packages_data as $idx => $pkg): ?>
          <?php
            $wa_text = urlencode("Halo JHC, saya ingin reservasi: " . $pkg['title']);
            $wa_link = "https://api.whatsapp.com/send?phone=6287760615300&text={$wa_text}";
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="card mcu-card border-0 shadow-sm h-100">
              <div class="mcu-img-wrap">
                <img src="public/<?= htmlspecialchars($pkg['image_path']); ?>" alt="MCU">
                <div class="mcu-price-tag">
                  Rp <?= number_format($pkg['price'], 0, ',', '.'); ?>
                </div>
              </div>
              <div class="card-body p-4 d-flex flex-column">
                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($pkg['title']); ?></h5>
                <p class="text-muted small mb-4" style="line-height:1.65;-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                  <?= htmlspecialchars(substr($pkg['description'], 0, 120)); ?>…
                </p>
                <div class="mt-auto row g-2">
                  <div class="col-6">
                    <button type="button"
                            class="btn-jhc-outline w-100 justify-content-center"
                            style="font-size:.8rem;padding:.45rem .5rem;"
                            data-bs-toggle="modal" data-bs-target="#mcuModal<?= $idx; ?>">
                      Lihat Detail
                    </button>
                  </div>
                  <div class="col-6">
                    <a href="<?= $wa_link; ?>" target="_blank" rel="noopener"
                       class="btn-jhc btn-jhc-sm w-100 justify-content-center">
                      Reservasi
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- MCU Detail Modal -->
          <div class="modal fade" id="mcuModal<?= $idx; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
                <div class="modal-header">
                  <h5 class="modal-title">Detail Paket MCU</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-lg-5">
                  <div class="row g-4">
                    <div class="col-md-5">
                      <img src="public/<?= htmlspecialchars($pkg['image_path']); ?>"
                           class="img-fluid rounded-3 shadow-sm" alt="Detail MCU">
                    </div>
                    <div class="col-md-7">
                      <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-2 rounded-pill">
                        <i class="fas fa-list-check me-1"></i>Rincian Paket
                      </span>
                      <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($pkg['title']); ?></h3>
                      <h4 class="text-primary fw-bold mb-4">
                        Rp <?= number_format($pkg['price'], 0, ',', '.'); ?>
                      </h4>
                      <div class="text-muted mb-4" style="line-height:1.8;font-size:.9rem;">
                        <?= nl2br(htmlspecialchars($pkg['description'])); ?>
                      </div>
                      <a href="<?= $wa_link; ?>" target="_blank" rel="noopener"
                         class="btn btn-success w-100 py-3 rounded-3 fw-bold">
                        <i class="fab fa-whatsapp me-2"></i>Hubungi Kami Sekarang
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================
       VIRTUAL ROOM
  ============================================================ -->
  <?php if ($vr_data): ?>
  <section id="virtual_room" class="bg-white">
    <div class="container">
      <div class="row align-items-center g-4 g-lg-5">

        <div class="col-lg-6 order-2 order-lg-1">
          <div class="position-relative">
            <?php if (!empty($vr_data['video_url'])): 
              $embed_url = $vr_data['video_url'];
              $sep = strpos($embed_url,'?') !== false ? '&' : '?';
              $auto_url = $embed_url.$sep.'autoplay=1&mute=1&loop=1&playlist='.basename(parse_url($embed_url,PHP_URL_PATH));
            ?>
              <div class="ratio ratio-16x9 shadow-lg rounded-3 overflow-hidden">
                <iframe src="<?= htmlspecialchars($auto_url); ?>"
                        allow="autoplay;encrypted-media" allowfullscreen title="Virtual Room"></iframe>
              </div>
            <?php elseif (!empty($vr_data['video_path'])): ?>
              <div class="ratio ratio-16x9 shadow-lg rounded-3 overflow-hidden bg-black">
                <video class="w-100 h-100" autoplay muted loop controls style="object-fit:cover;">
                  <source src="<?= htmlspecialchars($vr_data['video_path']); ?>" type="video/mp4">
                </video>
              </div>
            <?php else: ?>
              <img src="<?= htmlspecialchars($vr_data['image_path_360']); ?>"
                   class="img-fluid rounded-3 shadow-lg w-100" alt="Virtual Room">
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-6 order-1 order-lg-2">
          <span class="section-label"><i class="fas fa-vr-cardboard me-2"></i>Virtual Room</span>
          <h2 class="section-title mb-4"><?= htmlspecialchars($vr_data['title']); ?></h2>
          <p class="text-secondary mb-4" style="line-height:1.85;text-align:justify;">
            <?= nl2br(htmlspecialchars($vr_data['content'])); ?>
          </p>

          <div class="row g-3">
            <div class="col-sm-6">
              <div class="d-flex align-items-center p-3 bg-light rounded-3 gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                     style="width:44px;height:44px;flex-shrink:0;">
                  <i class="fas fa-user-md"></i>
                </div>
                <div><h6 class="fw-bold mb-0">Dokter Ahli</h6><small class="text-muted">Berpengalaman</small></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-center p-3 bg-light rounded-3 gap-3">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                     style="width:44px;height:44px;flex-shrink:0;">
                  <i class="fas fa-clock"></i>
                </div>
                <div><h6 class="fw-bold mb-0">Layanan 24 Jam</h6><small class="text-muted">Selalu Siap</small></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- ============================================================
       NEWS
  ============================================================ -->
  <section id="news" class="bg-white">
    <div class="container">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <span class="section-label"><i class="fas fa-newspaper me-2"></i>Berita Terkini</span>
          <h2 class="section-title">
            <?= htmlspecialchars($settings['news_section_title'] ?? 'Berita & Artikel'); ?>
          </h2>
          <div class="section-divider mx-auto"></div>
          <p class="text-muted mt-4">Informasi terbaru seputar kesehatan dan layanan kami.</p>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach($news_data as $article): ?>
        <div class="col-md-6 col-lg-4">
          <div class="news-card hover-lift bg-white h-100">
            <div class="news-img position-relative">
              <img src="public/<?= htmlspecialchars($article['image_path']); ?>"
                   alt="<?= htmlspecialchars($article['title']); ?>">
              <span class="news-date-badge">
                <i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($article['post_date'])); ?>
              </span>
            </div>
            <div class="card-body p-4">
              <span class="badge bg-light text-primary mb-3 px-3 py-2 rounded-pill" style="font-size:.75rem;">
                <i class="fas fa-tag me-1"></i><?= htmlspecialchars($article['category']); ?>
              </span>
              <h5 class="fw-bold text-dark lh-base mb-3" style="font-size:clamp(.9rem,2vw,1rem);">
                <?= htmlspecialchars($article['title']); ?>
              </h5>
              <p class="text-muted small mb-3" style="line-height:1.7;">
                <?= substr(strip_tags($article['content']), 0, 100); ?>…
              </p>
              <a href="javascript:void(0)"
                 class="news-read-more btn-read-more"
                 data-bs-toggle="modal" data-bs-target="#modalArticle"
                 data-title="<?= htmlspecialchars($article['title']); ?>"
                 data-category="<?= htmlspecialchars($article['category']); ?>"
                 data-date="<?= date('d M Y', strtotime($article['post_date'])); ?>"
                 data-image="public/<?= htmlspecialchars($article['image_path']); ?>"
                 data-content="<?= htmlspecialchars($article['content']); ?>">
                Baca Selengkapnya <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Modal: Detail Artikel -->
  <div class="modal fade" id="modalArticle" tabindex="-1" aria-labelledby="modalArticleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalArticleLabel">
            <i class="fas fa-newspaper me-2"></i>Detail Artikel
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <img id="article-img" src="" class="w-100" style="max-height:300px;object-fit:cover;" alt="">
          <div class="p-4">
            <div class="d-flex gap-2 mb-3 flex-wrap">
              <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                <i class="fas fa-tag me-1"></i><span id="article-category"></span>
              </span>
              <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                <i class="fas fa-calendar-alt me-1"></i><span id="article-date"></span>
              </span>
            </div>
            <h3 id="article-title" class="fw-bold text-dark mb-4"></h3>
            <div id="article-content" class="text-secondary" style="line-height:1.85;text-align:justify;"></div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       PARTNERS
  ============================================================ -->
  <section id="partners" style="background:linear-gradient(135deg,#f8f9fa 0%,#fff 100%);">
    <div class="container">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <span class="section-label"><i class="fas fa-handshake me-2"></i>Mitra Kami</span>
          <h2 class="section-title">Mitra Asuransi & Perusahaan</h2>
          <div class="section-divider mx-auto"></div>
          <p class="text-muted mt-4">Bekerja sama dengan berbagai mitra terpercaya untuk pelayanan terbaik.</p>
        </div>
      </div>

      <div class="row justify-content-center align-items-center g-4">
        <?php if (!empty($partners_data)): ?>
          <?php foreach($partners_data as $partner): ?>
          <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center">
            <a href="<?= htmlspecialchars(!empty($partner['url']) ? $partner['url'] : '#'); ?>"
               target="<?= !empty($partner['url']) ? '_blank' : '_self'; ?>"
               rel="noopener noreferrer"
               class="d-block p-3"
               data-bs-toggle="tooltip" title="<?= htmlspecialchars($partner['name']); ?>">
              <img src="public/<?= htmlspecialchars($partner['logo_path']); ?>"
                   class="img-fluid partner-logo"
                   alt="<?= htmlspecialchars($partner['name']); ?>"
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

  <!-- ============================================================
       PROMO POPUP
  ============================================================ -->
  <?php if ($show_popup): 
    $raw_path      = $p_data['popup_image_path'] ?? '';
    $final_img_url = 'public/' . $raw_path;
  ?>
  <div class="modal fade" id="promoPopup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius:22px;">
        <button type="button" class="btn-close"
                data-bs-dismiss="modal"
                style="position:absolute;top:12px;right:12px;z-index:60;background:rgba(255,255,255,.9);border-radius:50%;padding:9px;box-shadow:0 4px 12px rgba(0,0,0,.15);">
        </button>
        <div class="modal-body p-0">
          <?php if (!empty($raw_path)): ?>
          <div class="popup-img-wrap">
            <img src="<?= $final_img_url; ?>" class="w-100"
                 onerror="this.src='public/assets/img/gallery/JHC_Logo.png';this.style.padding='40px';"
                 alt="Promo">
          </div>
          <?php endif; ?>
          <div class="p-4 text-center">
            <?php if (!empty($p_data['popup_title'])): ?>
              <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($p_data['popup_title']); ?></h4>
            <?php endif; ?>
            <?php if (!empty($p_data['popup_content'])): ?>
              <p class="text-muted small mb-0" style="line-height:1.65;">
                <?= nl2br(htmlspecialchars($p_data['popup_content'])); ?>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer border-0 p-3 justify-content-center">
          <button type="button" class="btn-jhc px-5" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ============================================================
       FOOTER
  ============================================================ -->
  <footer class="py-0">
    <div class="container">
      <div class="row py-5 g-5">

        <!-- Brand & Contact -->
        <div class="col-12 col-md-6 col-lg-4">
          <?php $footer_logo = !empty($settings['footer_logo_path']) ? $settings['footer_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
          <a href="index.php" class="d-inline-block mb-4">
            <img src="public/<?= htmlspecialchars($footer_logo); ?>" height="60" alt="JHC Logo"
                 style="filter:brightness(0) invert(1);">
          </a>
          <p class="text-white opacity-75 small mb-4" style="line-height:1.85;">
            Memberikan pelayanan kesehatan jantung terpadu dengan standar kualitas tinggi dan tenaga medis profesional untuk masyarakat Tasikmalaya dan sekitarnya.
          </p>

          <div class="d-flex flex-column gap-3 mb-4">
            <div class="d-flex align-items-center gap-3 text-white">
              <div class="footer-icon-circle"><i class="fas fa-ambulance"></i></div>
              <div>
                <small class="d-block opacity-50">Gawat Darurat (IGD)</small>
                <strong>(0265) 3172112</strong>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 text-white">
              <div class="footer-icon-circle"><i class="fab fa-whatsapp"></i></div>
              <div>
                <small class="d-block opacity-50">WhatsApp RS</small>
                <strong>+62 851-7500-0375</strong>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 text-white">
              <div class="footer-icon-circle"><i class="fas fa-envelope"></i></div>
              <div>
                <small class="d-block opacity-50">Email Resmi</small>
                <strong>jhc.tasik@gmail.com</strong>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <a href="https://facebook.com/rsjantungjakarta" class="social-btn" target="_blank" rel="noopener" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://instagram.com/rsjantungtasikmalaya" class="social-btn" target="_blank" rel="noopener" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://youtube.com/@RSJantungJakartaJHC" class="social-btn" target="_blank" rel="noopener" aria-label="YouTube">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>

        <!-- Informasi -->
        <div class="col-6 col-lg-2">
          <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Informasi</h5>
          <ul class="list-unstyled d-flex flex-column gap-2">
            <li><a href="index.php#about_us"   class="footer-link">Tentang Kami</a></li>
            <li><a href="index.php#facilities" class="footer-link">Fasilitas</a></li>
            <li><a href="index.php#doctors"    class="footer-link">Tim Dokter</a></li>
            <li><a href="index.php#news"       class="footer-link">Berita & Artikel</a></li>
            <li><a href="career.php"           class="footer-link">Karir / Lowongan</a></li>
          </ul>
        </div>

        <!-- Layanan -->
        <div class="col-6 col-lg-2">
          <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Layanan</h5>
          <ul class="list-unstyled d-flex flex-column gap-2">
            <?php foreach(array_slice($layanan_data, 0, 5) as $d): ?>
              <li>
                <a href="index.php#departments" class="footer-link">
                  <?= htmlspecialchars($d['name']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Map -->
        <div class="col-12 col-lg-4">
          <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Lokasi Kami</h5>
          <div class="rounded-3 overflow-hidden shadow-lg border border-white border-opacity-10">
            <iframe
              src="https://maps.google.com/maps?q=RS+Jantung+Tasikmalaya+JHC&t=&z=15&ie=UTF8&iwloc=&output=embed"
              width="100%" height="195" style="border:0;" allowfullscreen="" loading="lazy"
              title="Lokasi RS JHC Tasikmalaya"></iframe>
            <div class="p-3 bg-white bg-opacity-10">
              <small class="text-white">
                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                Jl. Raya Tasikmalaya, Jawa Barat, Indonesia
              </small>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="border-top border-white border-opacity-10">
      <div class="container">
        <div class="py-4 text-center">
          <p class="mb-0 text-white opacity-50 small fw-semibold">
            &copy; 2026 RS Jantung Heart Center Tasikmalaya. All Rights Reserved.
          </p>
        </div>
      </div>
    </div>
  </footer>

</main><!-- /#top -->

<!-- ============================================================
     SCRIPTS
============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. NAVBAR SCROLL EFFECT ── */
  const navbar = document.getElementById('mainNavbar');
  window.addEventListener('scroll', function () {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  }, { passive: true });

  /* ── 2. SMOOTH SCROLL ── */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#' || href === '#!') return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const offset = navbar.offsetHeight + 20;
      window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.custom-tab-btn');
    const mainImg = document.getElementById('main-about-image');

    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (event) {
            const newImgPath = event.target.getAttribute('data-img');
            
            // Debugging: Cek path yang terpanggil di F12 Console browser
            console.log("Memuat path gambar: " + newImgPath);

            if(newImgPath && newImgPath.trim() !== "") {
                // Beri efek transisi memudar
                mainImg.style.opacity = '0';
                
                setTimeout(() => {
                    // Update SRC gambar
                    mainImg.src = newImgPath;
                    
                    // Pastikan gambar muncul kembali setelah sumbernya berubah
                    mainImg.onload = function() {
                        mainImg.style.opacity = '1';
                    };
                }, 300);
            }
        });
    });
  });

  /* ── 3. MODAL: DETAIL LAYANAN ── */
  document.querySelectorAll('.btn-open-layanan').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const name      = this.dataset.name     || '';
      const desc      = this.dataset.desc     || '-';
      const expertise = this.dataset.expertise|| '-';
      const info      = this.dataset.info     || 'Informasi belum tersedia.';
      const imagePath = this.dataset.image    || '';
      const bText     = this.dataset.btnText  || 'Hubungi Kami';
      const bLink     = this.dataset.btnLink  || '#';

      document.getElementById('m-name').innerText      = name;
      document.getElementById('m-desc').innerText      = desc;
      document.getElementById('m-expertise').innerText = expertise;
      document.getElementById('m-info').innerText      = info;

      /* Gambar layanan */
      const imgEl = document.getElementById('m-image');
      if (imagePath && imagePath !== '' && imagePath !== 'public/' && imagePath !== 'public') {
        imgEl.src           = imagePath;
        imgEl.style.display = 'block';
      } else {
        imgEl.style.display = 'none';
      }

      /* Tombol aksi */
      const actionBtn  = document.getElementById('m-btn');
      const actionText = document.getElementById('m-btn-text');

      actionBtn.href       = bLink;
      actionText.innerText = bText;

      const isExternal = bLink.includes('http') || bLink.includes('wa.me');
      if (isExternal) {
        actionBtn.setAttribute('target', '_blank');
        actionBtn.setAttribute('rel', 'noopener noreferrer');
      } else {
        actionBtn.removeAttribute('target');
        actionBtn.removeAttribute('rel');
      }
    });
  });

  /* ── 4. MODAL: DETAIL DOKTER ── */
  document.querySelectorAll('.btn-open-dokter').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const name        = this.dataset.name     || '';
      const specialty   = this.dataset.specialty|| '';
      const desc        = this.dataset.desc     || 'Tidak ada deskripsi tersedia.';
      const img         = this.dataset.img      || '';
      const scheduleRaw = this.dataset.schedule || '';

      document.getElementById('mdl-name').innerText      = name;
      document.getElementById('mdl-specialty').innerText = specialty;
      document.getElementById('mdl-desc').innerText      = desc;
      document.getElementById('mdl-img').src             = img;

      /* Jadwal */
      const scheduleEl = document.getElementById('mdl-schedule');
      scheduleEl.innerHTML = '';
      if (scheduleRaw.trim() !== '') {
        scheduleRaw.split(',').forEach(function (item) {
          const span = document.createElement('span');
          span.className = 'badge bg-white text-info border border-info-subtle rounded-pill px-3 py-2 small fw-bold';
          span.innerHTML = '<i class="far fa-clock me-1"></i>' + item.trim();
          scheduleEl.appendChild(span);
        });
      } else {
        scheduleEl.innerHTML = '<small class="text-muted fst-italic">Jadwal belum tersedia</small>';
      }

      /* Link booking */
      const bookBtn = document.getElementById('mdl-book-link');
      if (bookBtn) {
        bookBtn.href = 'booking.php?dokter=' + encodeURIComponent(name) +
                       '&spesialis=' + encodeURIComponent(specialty);
      }
    });
  });

  /* ── 5. MODAL: ARTIKEL ── */
  document.querySelectorAll('.btn-read-more').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('article-title').innerText    = this.dataset.title    || '';
      document.getElementById('article-category').innerText = this.dataset.category || '';
      document.getElementById('article-date').innerText     = this.dataset.date     || '';
      document.getElementById('article-img').src            = this.dataset.image    || '';
      document.getElementById('article-content').innerHTML  =
        (this.dataset.content || '').replace(/\n/g, '<br>');
    });
  });

  /* ── 6. TOOLTIPS ── */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

  /* ── 7. PROMO POPUP (1 detik setelah load) ── */
  <?php if ($show_popup): ?>
  const promoEl = document.getElementById('promoPopup');
  if (promoEl) {
    const promoModal = new bootstrap.Modal(promoEl);
    setTimeout(function () { promoModal.show(); }, 1000);
  }
  <?php endif; ?>

});
</script>
</body>
</html>
