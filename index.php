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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title><?php echo $page_title; ?></title>

    <?php 
    $favicon = !empty($settings['favicon_path']) ? $settings['favicon_path'] : 'assets/img/favicons/favicon.ico';
    ?>
    <link rel="shortcut icon" type="image/x-icon" href="public/<?php echo htmlspecialchars($favicon); ?>">
    <link href="public/assets/css/theme.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />

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

        /* ==================== NAVBAR - IMPROVED ==================== */
        .navbar {
            padding: 0.75rem 0;
            min-height: 80px;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            box-shadow: var(--shadow-sm);
            transition: var(--transition-smooth);
        }

        .navbar.navbar-scrolled {
            padding: 0.5rem 0;
            min-height: 70px;
            box-shadow: var(--shadow-md);
            border-bottom: 3px solid var(--jhc-red);
        }

        .navbar .container {
            max-width: 1320px;
            padding: 0 1.25rem;
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
            transition: var(--transition-smooth);
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
        }

        .navbar.navbar-scrolled .navbar-brand img {
            height: 55px;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
            filter: drop-shadow(0 4px 12px rgba(200, 16, 46, 0.25));
        }

        .nav-link {
            color: var(--jhc-navy) !important;
            font-weight: 600;
            font-size: 0.9rem;
            position: relative;
            margin: 0 0.5rem;
            padding: 0.625rem 0.875rem !important;
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

        /* ==================== UNIFIED BUTTON STYLES ==================== */
        .btn-janji,
        .btn-detail-layanan,
        .btn-detail-dokter,
        .btn-mcu-reservasi,
        .btn-janji-modal {
            background: var(--jhc-gradient);
            color: white !important;
            border-radius: 50px;
            padding: 0.625rem 1.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            border: 2px solid transparent;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.2);
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-janji:hover,
        .btn-detail-layanan:hover,
        .btn-detail-dokter:hover,
        .btn-mcu-reservasi:hover,
        .btn-janji-modal:hover {
            background: white;
            color: var(--jhc-red) !important;
            border-color: var(--jhc-red);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 16, 46, 0.3);
        }

        .btn-detail-layanan,
        .btn-detail-dokter {
            padding: 0.5rem 1.25rem;
            font-size: 0.8rem;
        }

        /* ==================== FLOATING BUTTONS - IMPROVED ==================== */
        .floating-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 1030;
        }

        .btn-igd-float, .btn-wa-float {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
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
            transform: scale(1.12) rotate(8deg);
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
            transform: scale(1.12);
            box-shadow: 0 8px 35px rgba(37, 211, 102, 0.4);
        }

        /* ==================== HERO SECTION - FIXED ==================== */
        .hero-section {
            width: 100%;
            height: 100vh;
            min-height: 600px;
            max-height: 900px;
            margin: 0;
            padding: 0;
            overflow: hidden;
            position: relative;
            background-color: #000;
        }

        #heroCarousel, 
        .carousel-inner, 
        .carousel-item {
            height: 100%;
            width: 100%;
        }

        .bg-holder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            transition: transform 8s ease-in-out;
        }

        .carousel-item.active .bg-holder {
            animation: zoomIn 15s ease-in-out;
        }

        @keyframes zoomIn {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(0, 20, 40, 0.85) 0%, rgba(138, 48, 51, 0.45) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white !important;
            padding: 0.625rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .carousel-item h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            color: white !important;
            text-shadow: 2px 4px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .carousel-item p {
            font-size: clamp(1rem, 2vw, 1.125rem);
            max-width: 650px;
            color: rgba(255, 255, 255, 0.95) !important;
            text-shadow: 1px 2px 5px rgba(0, 0, 0, 0.5);
            line-height: 1.7;
        }

        .btn-hero {
            display: inline-flex;
            align-items: center;
            padding: 0.875rem 2rem;
            font-size: 0.95rem;
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
            color: #8a3033 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* ==================== SECTIONS - IMPROVED ==================== */
        section {
            padding: 5rem 0;
        }

        .section-subtitle {
            color: var(--jhc-red);
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
        }

        .section-title {
            position: relative;
            display: inline-block;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 800;
            color: var(--jhc-navy);
            margin-bottom: 1.25rem;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--jhc-gradient);
            margin: 1rem auto 0;
            border-radius: 10px;
        }

        /* ==================== CARDS - IMPROVED ==================== */
        .card {
            border: none;
            transition: var(--transition-smooth);
        }

        .hover-lift {
            transition: var(--transition-smooth);
        }

        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .service-card {
            border-radius: 20px;
            background: white;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            height: 100%;
            border: 2px solid transparent;
        }

        .service-card:hover {
            border-color: rgba(200, 16, 46, 0.2);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            margin: 0 auto 1.25rem;
            transition: var(--transition-smooth);
        }

        .service-card:hover .icon-wrapper {
            background: linear-gradient(135deg, #fff5f5, #ffe5e5);
            transform: scale(1.1) rotate(5deg);
        }

        .icon-wrapper img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        /* ==================== DOCTORS SECTION - IMPROVED ==================== */
        :root {
          --jhc-red-dark: #8a3033;
          --jhc-red-light: #bd3030;
          --jhc-gradient: linear-gradient(145deg, #8a3033 0%, #bd3030 100%);
        }

        .doctor-card {
            border-radius: 25px;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .doctor-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(138, 48, 51, 0.12);
        }

        .doctor-img-container {
            position: relative;
            padding-top: 10px;
        }

        /* Lingkaran dekorasi di belakang foto */
        .img-bg-decoration {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 140px;
            background: var(--jhc-gradient);
            border-radius: 50%;
            opacity: 0.1;
            z-index: 0;
        }

        .doctor-img-wrapper {
            position: relative;
            z-index: 1;
            width: 130px;
            height: 130px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #ffffff;
        }

        .doctor-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .doctor-card:hover .doctor-img-wrapper img {
            transform: scale(1.15);
        }

        .doctor-name {
            font-size: 1.1rem;
            line-height: 1.3;
            color: #2d3436 !important;
        }

        .doctor-specialty {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--jhc-red-light);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-detail-dokter {
          /* Gradasi merah tua ke merah terang */
          background: linear-gradient(145deg, #8a3033 0%, #bd3030 100%);
          color: #ffffff;
          border: 2px solid transparent; /* Border transparan agar layout tidak geser saat hover */
          font-weight: 600;
          font-size: 0.85rem;
          padding: 10px 0;
          width: 100%;
          display: block;
          border-radius: 50px; /* Membuat tombol lonjong/rounded pill */
          transition: all 0.4s ease;
          cursor: pointer;
          box-shadow: 0 4px 15px rgba(138, 48, 51, 0.2);
        }

        /* Kondisi saat tombol disentuh (Hover) atau ditekan (Active) */
        .btn-detail-dokter:hover, 
        .btn-detail-dokter:active {
            background: #ffffff !important; /* Background menjadi putih */
            color: #8a3033 !important;      /* Tulisan menjadi merah tua */
            border: 2px solid #8a3033;     /* Menambahkan garis pinggir merah */
            transform: translateY(-2px);    /* Efek sedikit terangkat */
            box-shadow: 0 6px 20px rgba(138, 48, 51, 0.3);
        }

        .doctor-card:hover .btn-detail-dokter {
            background: var(--jhc-gradient);
            color: #ffffff;
            border-color: transparent;
        }

        /* Responsif Mobile */
        @media (max-width: 576px) {
            .doctor-img-wrapper {
                width: 100px;
                height: 100px;
            }
            .img-bg-decoration {
                width: 110px;
                height: 110px;
            }
            .doctor-name {
                font-size: 0.95rem;
            }
        }

        /* ==================== MODALS - IMPROVED ==================== */
        .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: var(--jhc-gradient);
            color: white !important;
            border: none;
            padding: 1.25rem 1.875rem;
        }

        .modal-title {
            color: white !important;
        }

        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 1;
        }

        .modal-body {
            padding: 1.875rem;
        }

        /* ==================== MCU CAROUSEL - IMPROVED ==================== */
        /* Styling Khusus agar Rapi & Simple */
        .mcu-card { border-radius: 20px; transition: 0.3s; overflow: hidden; }
        .mcu-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08)!important; }
        
        .mcu-img-wrapper { position: relative; height: 200px; }
        .mcu-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        
        .mcu-price-tag { 
          position: absolute; bottom: 15px; left: 15px; 
          background: rgba(255,255,255,0.95); backdrop-filter: blur(5px);
          color: #0066cc; font-weight: 800; padding: 5px 15px; border-radius: 10px; font-size: 0.9rem;
        }

        .mcu-card:hover .mcu-img-wrapper img {
          transform: scale(1.1);
        }

        .mcu-price-badge {
          position: absolute;
          bottom: 15px;
          right: 15px;
          background: #ffffff;
          color: #0066cc;
          padding: 6px 16px;
          border-radius: 8px;
          font-weight: 800;
          font-size: 0.95rem;
          box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .mcu-desc-container {
          border-left: 2px solid #eef2f7;
          padding-left: 15px;
        }

        .mcu-btn {
          background-color: #0066cc;
          border: none;
          transition: background 0.3s;
        }

        .mcu-btn:hover {
          background-color: #004d99;
        }

        /* Memastikan teks deskripsi tidak merusak tinggi card */
        .card-text {
          display: -webkit-box;
          -webkit-line-clamp: 4;
          -webkit-box-orient: vertical;
          overflow: hidden;
          line-height: 1.6;
        }
      

        /* ==================== PARTNERS - IMPROVED ==================== */
        .partner-logo {
            filter: none !important;
            -webkit-filter: none !important;
            opacity: 1 !important;
            transition: transform 0.3s ease;
            max-height: 70px;
            width: auto;
            object-fit: contain;
        }

        .partner-logo:hover {
            transform: scale(1.08);
        }

        /* ==================== NEWS - IMPROVED ==================== */
        .news-date-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--jhc-gradient);
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            z-index: 10;
            box-shadow: var(--shadow-md);
        }

        .news-card-link {
            color: var(--jhc-blue);
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition-smooth);
            display: inline-flex;
            align-items: center;
        }

        .news-card-link:hover {
            color: var(--jhc-red);
            transform: translateX(5px);
        }

        /* ==================== FOOTER - IMPROVED ==================== */
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
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e9ecef;
            transition: var(--transition-smooth);
        }

        .form-control-modern:focus {
            background: white;
            box-shadow: 0 0 0 4px rgba(27, 113, 161, 0.1);
            border-color: var(--jhc-blue);
        }

        /* ==================== CAROUSEL CONTROLS ==================== */
        .carousel-control-prev,
        .carousel-control-next {
            width: 45px;
            height: 45px;
            background: rgba(200, 16, 46, 0.75);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
            transition: var(--transition-smooth);
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            background: rgba(200, 16, 46, 0.95);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-indicators {
            bottom: 1.5rem;
        }

        .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.6);
            border: 2px solid white;
            margin: 0 4px;
        }

        .carousel-indicators button.active {
            background-color: var(--jhc-red);
        }

        /* ==================== RESPONSIVE - IMPROVED ==================== */
        @media (max-width: 1199px) {
            section {
                padding: 4rem 0;
            }
        }

        @media (max-width: 991px) {
            .navbar {
                padding: 0.625rem 0;
                min-height: 70px;
            }

            .navbar-brand img {
                height: 55px;
            }

            .navbar.navbar-scrolled .navbar-brand img {
                height: 50px;
            }

            .nav-link {
                padding: 0.75rem 1rem !important;
                margin: 0;
            }

            .btn-janji {
                margin-top: 0.5rem;
                width: 100%;
                text-align: center;
            }

            .floating-actions {
                bottom: 15px;
                right: 15px;
            }

            .btn-igd-float, .btn-wa-float {
                width: 52px;
                height: 52px;
                font-size: 22px;
            }

            .hero-section {
                height: 80vh;
                min-height: 500px;
            }

            section {
                padding: 3.5rem 0;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 767px) {
            .navbar-brand img {
                height: 50px;
            }

            .hero-section {
                height: 75vh;
                min-height: 450px;
            }

            .carousel-item h1 {
                font-size: 1.75rem;
            }

            .carousel-item p {
                font-size: 0.95rem;
            }

            .btn-hero {
                padding: 0.75rem 1.5rem;
                font-size: 0.875rem;
            }

            .partner-logo {
                max-height: 55px;
            }

            .floating-actions {
                bottom: 12px;
                right: 12px;
                gap: 10px;
            }

            .btn-igd-float, .btn-wa-float {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }

            .icon-wrapper {
                width: 70px;
                height: 70px;
            }

            .icon-wrapper img {
                width: 42px;
                height: 42px;
            }

            .doctor-img-wrapper {
                width: 120px;
                height: 120px;
            }

            section {
                padding: 3rem 0;
            }
        }

        @media (max-width: 575px) {
            .btn-janji {
                padding: 0.5rem 1.25rem;
                font-size: 0.8rem;
            }

            .service-card {
                margin-bottom: 1rem;
            }

            .section-title {
                font-size: 1.625rem;
            }

            .hero-badge {
                font-size: 0.7rem;
                padding: 0.5rem 1rem;
            }

            .modal-body {
                padding: 1.25rem;
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

        /* ==================== LOADING SPINNER ==================== */
        .spinner-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner-overlay.active {
            display: flex;
        }

        /* ==================== PopUp ==================== */
        /* Container Utama Modal */
        #promoPopup .modal-content {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            background-color: #fff;
        }

        /* Penataan Gambar agar Proporsional */
        .popup-image-container {
            width: 100%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative; /* Penting untuk z-index tombol close */
        }

        #promoPopup img {
            width: 100%;
            height: auto;
            max-height: 450px; /* Sedikit lebih tinggi agar proposional di layar besar */
            object-fit: cover; 
            display: block; /* Menghilangkan gap putih di bawah gambar */
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Efek Hover Gambar */
        #promoPopup:hover img {
            transform: scale(1.05); /* Sedikit lebih besar untuk efek dramatis */
        }

        /* Tipografi */
        #promoPopup h4 {
            color: #2d3436;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
        }

        #promoPopup p {
            color: #636e72;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Tombol Tutup (Close Button) */
        #promoPopup .btn-close {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1055; /* Pastikan di atas gambar */
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        #promoPopup .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg) scale(1.1);
            background-color: #fff;
        }

        /* Tombol Aksi di Bawah */
        #promoPopup .btn-primary {
            background: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            border: none;
            padding: 12px 45px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px; /* Konsisten dengan gaya tombol janji temu */
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 6px 20px rgba(138, 48, 51, 0.25);
        }

        #promoPopup .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(138, 48, 51, 0.35);
            filter: brightness(1.1);
        }

        /* Animasi Masuk Popup */
        .modal.fade .modal-dialog {
            transform: scale(0.9) translateY(20px);
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); /* Efek membal/bounce */
        }

        .modal.show .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* Responsif Mobile */
        @media (max-width: 576px) {
            #promoPopup .modal-dialog {
                margin: 15px;
            }
            
            #promoPopup img {
                max-height: 280px; 
            }

            #promoPopup .btn-primary {
                width: 100%; /* Tombol full width di HP agar mudah ditekan */
            }
        }
    </style>
  </head>
  <body>
    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
      <div class="spinner-border text-danger" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <main class="main" id="top">
      
      <!-- ==================== NAVBAR ==================== -->
     <nav class="navbar navbar-expand-lg navbar-light fixed-top" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <?php $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
            <img src="public/<?php echo htmlspecialchars($header_logo); ?>" alt="JHC Logo" height="50" />
          </a>
        
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item"><a class="nav-link" href="index.php#about_us">Tentang Kami</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#departments">Layanan</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#facilities">Fasilitas</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#mcu_packages_data">Paket MCU</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#virtual_room">Virtual Room</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#doctors">Dokter Kami</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#news">Berita</a></li>
            </ul>

            <div class="nav-actions ms-lg-3">
              <a class="btn btn-janji" href="career.php">
                <i class="fas fa-briefcase me-2"></i>Apply Job
              </a>
            </div>
          </div>
        </div>
      </nav>

      <!-- ==================== FLOATING BUTTONS ==================== -->
      <div class="floating-actions">
        <a href="tel:<?php echo $no_igd; ?>" class="btn-igd-float pulse-igd" title="Darurat IGD: <?php echo $no_igd; ?>" aria-label="Call IGD">
          <i class="fas fa-ambulance"></i>
        </a>

        <a href="https://wa.me/<?php echo $no_rs_wa; ?>" target="_blank" class="btn-wa-float" title="WhatsApp RS" aria-label="WhatsApp">
          <i class="fab fa-whatsapp"></i>
        </a>
      </div>

      <!-- ==================== HERO SECTION ==================== -->
      <section class="hero-section p-0" id="home">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            
            <div class="carousel-inner">
                <?php if (!empty($banners_data)): ?>
                    <?php foreach ($banners_data as $index => $banner): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                            <div class="bg-holder" style="background-image:url(public/<?= htmlspecialchars($banner['image_path']); ?>);"></div>
                            <div class="banner-overlay"></div>
                            
                            <div class="container hero-content">
                                <div class="row align-items-center min-vh-100">
                                    <div class="col-lg-8 text-center text-lg-start text-white">
                                        <h1 class="display-3 fw-bold"><?= htmlspecialchars($banner['title']); ?></h1>
                                        <p class="lead"><?= htmlspecialchars($banner['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="z-index: 5;">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="z-index: 5;">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>


    <!-- ==================== ABOUT US ==================== -->
      <section class="py-5" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);" id="about_us">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle">
                <i class="fas fa-hospital me-2"></i>TENTANG KAMI
              </p>
              <h2 class="section-title">Mengenal Lebih Dekat RS JHC</h2>
              <p class="text-muted mt-3">Dedikasi kami untuk pelayanan kesehatan terbaik bagi Anda dan keluarga.</p>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-3">
              <div class="nav flex-column nav-pills shadow-sm rounded-4 bg-white overflow-hidden sticky-top" 
                   id="v-pills-tab" role="tablist" style="top: 100px;">
                <?php 
                $no = 0;
                foreach ($tabs_config as $key => $info): 
                  $active = ($no === 0) ? 'active' : '';
                ?>
                  <button class="nav-link <?php echo $active; ?> text-start py-3 py-md-4 px-3 px-md-4 fw-bold border-bottom" 
                          id="v-pills-<?php echo $key; ?>-tab" 
                          data-bs-toggle="pill" 
                          data-bs-target="#v-pills-<?php echo $key; ?>" 
                          type="button"
                          role="tab"
                          aria-controls="v-pills-<?php echo $key; ?>"
                          aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
                    <i class="fas <?php echo $info['icon']; ?> me-2 me-md-3 text-danger"></i> 
                    <span class="d-none d-sm-inline"><?php echo $info['label']; ?></span>
                  </button>
                <?php $no++; endforeach; ?>
              </div>
            </div>

            <div class="col-lg-9">
              <div class="tab-content p-4 p-md-5 bg-white rounded-4 shadow-sm" style="min-height: 450px;">
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
                       id="v-pills-<?php echo $key; ?>"
                       role="tabpanel"
                       aria-labelledby="v-pills-<?php echo $key; ?>-tab">
                    
                    <?php if ($key === 'salam-direktur'): ?>
                      <div class="text-center">
                        <img src="<?php echo htmlspecialchars($img_display); ?>" 
                             class="rounded-circle shadow-lg mb-4 border border-4 border-white" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/150';"
                             alt="Direktur">
                        <h3 class="text-danger fw-bold mb-3"><?php echo htmlspecialchars($judul); ?></h3>
                        <div class="text-muted fst-italic px-lg-4" style="line-height: 1.8;">
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
                               style="object-fit: cover; height: 280px;"
                               onerror="this.src='https://via.placeholder.com/800x400';"
                               alt="<?php echo htmlspecialchars($judul); ?>">
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
                <?php $no++; endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== SERVICES SECTION ==================== -->
      <section class="py-5 bg-white" id="departments">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle">
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
                      <div class="card service-card h-100 hover-lift text-center p-3 p-md-4">
                          <div class="card-body p-2">
                              <div class="icon-wrapper">
                                  <?php if(!empty($item['icon_path'])): ?>
                                      <img src="public/<?= htmlspecialchars($item['icon_path']); ?>" alt="icon" />
                                  <?php else: ?>
                                      <i class="fas <?= $is_layanan ? 'fa-star text-warning' : 'fa-heartbeat text-primary' ?> fa-2x"></i>
                                  <?php endif; ?>
                              </div>
                              <h5 class="card-title fw-bold text-dark mb-3" style="font-size: clamp(0.875rem, 2vw, 1rem);">
                                  <?= htmlspecialchars($item['name']); ?>
                              </h5>
                              <a href="javascript:void(0)" 
                                 class="btn btn-detail-layanan btn-sm rounded-pill px-3 py-2 shadow-sm btn-buka-detail" 
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
              <div style="width: 80px; height: 4px; background: var(--jhc-gradient); margin: 1rem auto; border-radius: 3px;"></div>
            </div>
            <div class="row gx-3 gx-md-4">
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
              <div style="width: 80px; height: 4px; background: var(--jhc-gradient); margin: 1rem auto; border-radius: 3px;"></div>
            </div>
            <div class="row gx-3 gx-md-4">
              <?php render_cards($poliklinik_data, false); ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Modal Layanan -->
      <div class="modal fade" id="modalLayanan" tabindex="-1" aria-labelledby="modalLayananLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fw-bold" id="modalLayananLabel">Detail Layanan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="text-center mb-4">
                <div class="icon-wrapper mx-auto">
                  <img id="m-icon" src="" alt="Icon">
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
                  <div class="p-3 p-md-4 bg-light rounded-3 h-100 border-start border-warning border-4">
                    <label class="fw-bold text-dark small text-uppercase mb-2 d-block">
                      <i class="fas fa-star me-2 text-warning"></i>Keahlian Khusus
                    </label>
                    <div id="m-expertise" class="text-muted small" style="white-space: pre-line;"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-3 p-md-4 rounded-3 bg-light h-100 border-start border-primary border-4">
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
              <a href="booking.php" class="btn btn-janji-modal rounded-pill px-4 shadow-sm">
                <i class="fas fa-calendar-check me-2"></i>Buat Janji Temu
              </a>
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
              <p class="section-subtitle">
                <i class="fas fa-building me-2"></i>FASILITAS
              </p>
              <h2 class="section-title">Fasilitas Unggulan</h2>
              <p class="text-muted mt-3">Fasilitas modern dan lengkap untuk kenyamanan Anda.</p>
            </div>
          </div>

          <div class="row g-4">
            <?php foreach($facilities_data as $fac): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden" style="border-radius: 20px;">
                <div class="position-relative" style="height: 240px; overflow: hidden;">
                  <img src="public/<?php echo htmlspecialchars($fac['image_path']); ?>" 
                       class="w-100 h-100" style="object-fit: cover; transition: transform 0.6s;" 
                       alt="<?php echo htmlspecialchars($fac['name']); ?>">
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 p-md-4" 
                       style="background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);">
                    <h5 class="text-white mb-0 fw-bold" style="font-size: clamp(0.95rem, 2vw, 1.125rem);">
                      <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($fac['name']); ?>
                    </h5>
                  </div>
                </div>
                <div class="card-body p-3 p-md-4">
                  <p class="card-text text-muted" style="line-height: 1.7; font-size: clamp(0.875rem, 1.5vw, 0.95rem);">
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


      <?php if (!empty($mcu_packages_data)): ?>
      <section class="py-5 bg-light" id="mcu_packages_data">
        <div class="container">
          <div class="row mb-5 align-items-end">
            <div class="col-lg-7">
              <p class="text-primary fw-bold text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.85rem;">
                <i class="fas fa-file-medical me-2"></i>Layanan Check Up
              </p>
              <h2 class="fw-bold text-dark mb-0">Pilih Paket Kesehatan Anda</h2>
            </div>
          </div>
          
          <div class="row g-4">
            <?php foreach ($mcu_packages_data as $index => $package): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mcu-card">
                  
                  <div class="mcu-img-wrapper">
                    <img src="public/<?php echo htmlspecialchars($package['image_path']); ?>" class="card-img-top" alt="MCU">
                    <div class="mcu-price-tag">
                      Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                    </div>
                  </div>

                  <div class="card-body p-4 d-flex flex-column">
                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($package['title']); ?></h5>
                    
                    <p class="text-muted small mb-4 line-clamp-3">
                      <?php echo htmlspecialchars(substr($package['description'], 0, 120)) . '...'; ?>
                    </p>

                    <div class="mt-auto">
                      <div class="row g-2">
                        <div class="col-6">
                          <button type="button" class="btn btn-outline-primary w-100 rounded-pill fw-bold btn-sm py-2" 
                                  data-bs-toggle="modal" data-bs-target="#mcuModal<?php echo $index; ?>">
                            Lihat Detail
                          </button>
                        </div>
                        <div class="col-6">
                          <?php
                            $wa_link = "https://api.whatsapp.com/send?phone=6287760615300&text=" . urlencode("Halo JHC, saya ingin reservasi: " . $package['title']);
                          ?>
                          <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-mcu-reservasi w-100 rounded-pill fw-bold btn-sm py-2">
                            Reservasi
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="mcuModal<?php echo $index; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 p-lg-5">
                      <div class="row g-4">
                        <div class="col-md-5">
                          <img src="public/<?php echo htmlspecialchars($package['image_path']); ?>" class="img-fluid rounded-4 shadow-sm" alt="Detail MCU">
                        </div>
                        <div class="col-md-7">
                          <span class="badge bg-primary-soft text-primary mb-2 px-3">Rincian Paket</span>
                          <h3 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($package['title']); ?></h3>
                          <h4 class="text-primary fw-bold mb-4">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></h4>
                          
                          <div class="detail-content text-muted mb-4" style="line-height: 1.8;">
                            <h6 class="text-dark fw-bold"><i class="fas fa-list-check me-2"></i>Item Pemeriksaan:</h6>
                            <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                          </div>

                          <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="fab fa-whatsapp me-2"></i> Hubungi Kami Sekarang
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

      <!-- ==================== VIRTUAL ROOM ==================== -->
      <?php if ($vr_data): ?>
      <section class="py-5 bg-white" id="virtual_room">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-5">
            
            <div class="col-lg-6 order-2 order-lg-1">
              <div class="position-relative">
                <div class="bg-primary position-absolute rounded-4" 
                    style="width: 100%; height: 100%; top: 20px; left: -20px; z-index: -1; opacity: 0.12;"></div>
                
                <?php 
                // 1. PRIORITAS: VIDEO YOUTUBE
                if(!empty($vr_data['video_url'])): 
                  $embed_url = $vr_data['video_url'];
                  $sep = (strpos($embed_url, '?') !== false) ? '&' : '?';
                  $autoplay_url = $embed_url . $sep . "autoplay=1&mute=1&loop=1&playlist=" . basename(parse_url($embed_url, PHP_URL_PATH));
                ?>
                  <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden">
                    <iframe src="<?php echo htmlspecialchars($autoplay_url); ?>" 
                            allow="autoplay; encrypted-media" allowfullscreen></iframe>
                  </div>

                <?php 
                // 2. KEDUA: VIDEO LOKAL (MP4)
                elseif(!empty($vr_data['video_path'])): ?>
                  <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden bg-black">
                    <video class="w-100 h-100" autoplay muted loop controls style="object-fit: cover;">
                      <source src="<?php echo htmlspecialchars($vr_data['video_path']); ?>" type="video/mp4">
                      Browser Anda tidak mendukung tag video.
                    </video>
                  </div>

                <?php 
                // 3. TERAKHIR: GAMBAR 360 / FALLBACK
                else: ?>
                  <img src="<?php echo htmlspecialchars($vr_data['image_path_360']); ?>" 
                      class="img-fluid rounded-4 shadow-lg w-100" alt="Virtual Room">
                <?php endif; ?>
              </div>
            </div>

            <div class="col-lg-6 order-1 order-lg-2">
              <p class="section-subtitle">
                <i class="fas fa-building me-2"></i>VIRTUAL ROOM
              </p>
              <h2 class="section-title mb-4"><?php echo htmlspecialchars($vr_data['title']); ?></h2>
              <p class="text-secondary lead text-justify mb-4" style="line-height: 1.8;">
                <?php echo nl2br(htmlspecialchars($vr_data['content'])); ?>
              </p>
              
              <div class="row g-3 g-md-4">
                <div class="col-sm-6">
                  <div class="d-flex align-items-center p-3 p-md-4 bg-light rounded-4">
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
                  <div class="d-flex align-items-center p-3 p-md-4 bg-light rounded-4">
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

      <!-- ==================== DOCTORS SECTION ==================== -->
      <section class="py-5 bg-white" id="doctors">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle">
                <i class="fas fa-user-md me-2"></i>TIM TERBAIK
              </p>
              <h2 class="section-title">Tim Dokter Spesialis</h2>
              <p class="text-muted mt-3">Ditangani oleh tenaga medis profesional dengan dedikasi tinggi dan pengalaman luas.</p>
            </div>
          </div>
          
          <div class="row g-4 justify-content-center">
            <?php foreach($doctors_data as $doc): ?>
            <div class="col-6 col-md-4 col-lg-3">
              <div class="card doctor-card h-100 border-0">
                <div class="card-body p-4">
                  <div class="doctor-img-container">
                    <div class="img-bg-decoration"></div>
                    <div class="doctor-img-wrapper shadow-sm">
                      <img src="public/<?php echo htmlspecialchars(!empty($doc['photo_path']) ? $doc['photo_path'] : 'assets/img/gallery/jane.png'); ?>" 
                          alt="<?php echo htmlspecialchars($doc['name']); ?>">
                    </div>
                  </div>
                  
                  <div class="doctor-info-wrapper mt-3">
                    <h5 class="doctor-name fw-bold text-dark mb-1">
                      <?php echo htmlspecialchars($doc['name']); ?>
                    </h5>
                    <p class="doctor-specialty mb-3">
                      <?php echo htmlspecialchars($doc['specialty']); ?>
                    </p>
                    
                    <button type="button" 
                            class="btn btn-detail-dokter" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetailDokter"
                            data-name="<?= htmlspecialchars($doc['name']); ?>"
                            data-specialty="<?= htmlspecialchars($doc['specialty']); ?>"
                            data-desc="<?= htmlspecialchars($doc['description']); ?>"
                            data-img="public/<?= htmlspecialchars($doc['photo_path']); ?>"
                            data-schedule="<?= htmlspecialchars($doc['schedule'] ?? ''); ?>">
                        Lihat Profil
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Modal Detail Dokter -->
      <div class="modal fade" id="modalDetailDokter" tabindex="-1" aria-labelledby="modalDetailDokterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light">
              <h5 class="modal-title fw-bold text-dark" id="modalDetailDokterLabel">Profil Dokter</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
              <img id="mdl-img" src="" class="rounded-circle mb-4 border border-4 border-white shadow-sm" 
                  style="width: 140px; height: 140px; object-fit: cover; margin-top: -70px; background: white;" alt="Doctor">
              
              <h4 id="mdl-name" class="fw-bold text-dark mb-1"></h4>
              <p id="mdl-specialty" class="text-primary small fw-bold text-uppercase mb-4" style="letter-spacing: 1px;"></p>
              
              <div class="text-start bg-light p-3 p-md-4 rounded-4 mb-3">
                <h6 class="fw-bold small text-muted text-uppercase mb-2">
                  <i class="fas fa-info-circle me-2"></i>Tentang Dokter:
                </h6>
                <p id="mdl-desc" class="small text-secondary mb-0" style="line-height: 1.7;"></p>
              </div>

              <div class="text-start bg-info-subtle p-3 p-md-4 rounded-4 border border-info-subtle">
                <h6 class="fw-bold small text-info text-uppercase mb-3">
                  <i class="fas fa-calendar-alt me-2"></i>Jadwal Praktik:
                </h6>
                <div id="mdl-schedule" class="d-flex flex-wrap gap-2"></div>
              </div>
              
              <div class="d-grid mt-4">
                <a id="mdl-book-link" href="booking.php" class="btn btn-janji-modal rounded-pill py-3 fw-bold shadow-sm">
                  <i class="fas fa-calendar-check me-2"></i>Buat Janji Temu
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      
      <!-- ==================== NEWS SECTION ==================== -->
      <section class="py-5 bg-white" id="news">
        <div class="container">
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
              <p class="section-subtitle">
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
              <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden" style="border-radius: 20px;">
                <div class="position-relative" style="height: 240px; overflow: hidden;">
                  <img src="public/<?php echo htmlspecialchars($article['image_path']); ?>" 
                       class="w-100 h-100" style="object-fit: cover;" 
                       alt="<?php echo htmlspecialchars($article['title']); ?>">
                  <div class="news-date-badge">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo date('d M Y', strtotime($article['post_date'])); ?>
                  </div>
                </div>
                <div class="card-body p-3 p-md-4">
                  <span class="badge bg-light text-primary mb-3 px-3 py-2 rounded-pill">
                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($article['category']); ?>
                  </span>
                  <h5 class="card-title fw-bold text-dark lh-base mb-3" style="font-size: clamp(0.95rem, 2vw, 1.05rem);">
                    <?php echo htmlspecialchars($article['title']); ?>
                  </h5>
                  <p class="card-text text-muted small" style="line-height: 1.7; font-size: clamp(0.8rem, 1.5vw, 0.875rem);">
                    <?php echo substr(strip_tags($article['content']), 0, 100); ?>...
                  </p>
                  <a href="javascript:void(0)" 
                     class="news-card-link small mt-3 btn-read-more"
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
      <div class="modal fade" id="modalArticle" tabindex="-1" aria-labelledby="modalArticleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title fw-bold" id="modalArticleLabel">Detail Artikel</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <img id="article-img" src="" class="w-100" style="max-height: 320px; object-fit: cover;" alt="Article">
              <div class="p-4">
                <div class="d-flex gap-3 mb-3 flex-wrap">
                  <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-tag me-1"></i><span id="article-category"></span>
                  </span>
                  <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-calendar-alt me-1"></i><span id="article-date"></span>
                  </span>
                </div>
                <h3 id="article-title" class="fw-bold text-dark mb-4"></h3>
                <div id="article-content" class="text-secondary" style="line-height: 1.8; text-align: justify;"></div>
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
              <p class="section-subtitle">
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
                     title="<?php echo htmlspecialchars($partner['name']); ?>"
                     rel="noopener noreferrer">
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

      <?php 
  // 1. Ambil semua data settings terkait popup
      $popup_res = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'popup_%'");
      $p_data = [];
      while($row = $popup_res->fetch_assoc()) {
          $p_data[$row['setting_key']] = $row['setting_value'];
      }

      // 2. Cek apakah status popup aktif
      if (($p_data['popup_status'] ?? '') === 'active'): 
          // Ambil path gambar (misal: assets/img/popups/file.jpg)
          $raw_path = $p_data['popup_image_path'] ?? '';
          
          // Karena index.php di luar, kita arahkan ke folder public/
          $final_image_url = "public/" . $raw_path;
      ?>

      <div class="modal fade" id="promoPopup" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            
            <button type="button" class="btn-close position-absolute end-0 top-0 m-3 shadow-none" 
                    data-bs-dismiss="modal" style="z-index: 100; background-color: white; border-radius: 50%; padding: 10px;"></button>

            <div class="modal-body p-0">
              <?php if (!empty($raw_path)): ?>
                <div class="popup-image-container">
                  <img src="<?php echo $final_image_url; ?>" 
                      class="w-100 d-block" 
                      onerror="this.src='public/assets/img/gallery/JHC_Logo.png'; this.style.padding='40px';"
                      alt="Promo Image">
                </div>
              <?php endif; ?>
              
              <div class="p-4 text-center">
                <?php if (!empty($p_data['popup_title'])): ?>
                  <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($p_data['popup_title']); ?></h4>
                <?php endif; ?>
                
                <?php if (!empty($p_data['popup_content'])): ?>
                  <p class="text-muted mb-0 small" style="line-height: 1.6;">
                      <?php echo nl2br(htmlspecialchars($p_data['popup_content'])); ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="modal-footer border-0 p-3 justify-content-center">
              <button type="button" class="btn btn-primary px-5 rounded-pill fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>

<footer class="py-0 bg-primary position-relative overflow-hidden">
    <div class="bg-holder opacity-10" 
         style="background-image:url(public/assets/img/gallery/dot-bg.png);
                background-position:top left; margin-top:-3rem; background-size:auto;"></div>
    
    <div class="container position-relative">
        <div class="row py-6 g-5">
            <div class="col-12 col-md-6 col-lg-4">
                <a class="text-decoration-none d-inline-block mb-4" href="index.php">
                    <?php 
                    $footer_logo = !empty($settings['footer_logo_path']) ? $settings['footer_logo_path'] : 'assets/img/gallery/JhC_Logo.png';
                    ?>
                    <img src="public/<?php echo htmlspecialchars($footer_logo); ?>" height="65" alt="JHC Logo" class="footer-logo-main" />
                </a>
                <p class="text-light opacity-75 mb-4 small" style="line-height: 1.8;">
                    Memberikan pelayanan kesehatan jantung terpadu dengan standar kualitas tinggi dan tenaga medis profesional untuk masyarakat Tasikmalaya dan sekitarnya.
                </p>
                <div class="contact-info">
                    <div class="d-flex align-items-center text-light mb-3">
                        <div class="icon-circle me-3"><i class="fas fa-ambulance"></i></div>
                        <div><small class="d-block opacity-50">Gawat Darurat (IGD)</small><strong>(0265) 3172112</strong></div>
                    </div>
                    <div class="d-flex align-items-center text-light mb-3">
                        <div class="icon-circle me-3"><i class="fab fa-whatsapp"></i></div>
                        <div><small class="d-block opacity-50">WhatsApp RS</small><strong>+62 851-7500-0375</strong></div>
                    </div>
                    <div class="d-flex align-items-center text-light mb-4">
                        <div class="icon-circle me-3"><i class="fas fa-envelope"></i></div>
                        <div><small class="d-block opacity-50">Email Resmi</small><strong>jhc.tasik@gmail.com</strong></div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="https://facebook.com/rsjantungjakarta" class="social-btn" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com/rsjantungtasikmalaya" class="social-btn" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://youtube.com/@RSJantungJakartaJHC" class="social-btn" target="_blank"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-6 col-lg-2">
                <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Informasi</h5>
                <ul class="list-unstyled footer-nav">
                    <li><a href="index.php#about_us" class="footer-link">Tentang Kami</a></li>
                    <li><a href="index.php#facilities" class="footer-link">Fasilitas</a></li>
                    <li><a href="index.php#doctors" class="footer-link">Tim Dokter</a></li>
                    <li><a href="index.php#news" class="footer-link">Berita & Artikel</a></li>
                    <li><a href="career.php" class="footer-link">Karir / Lowongan</a></li>
                </ul>
            </div>
            
            <div class="col-6 col-lg-2">
                <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Layanan</h5>
                <ul class="list-unstyled footer-nav">
                    <?php foreach(array_slice($layanan_data, 0, 5) as $d): ?>
                        <li><a href="index.php#departments" class="footer-link"><?php echo htmlspecialchars($d['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-12 col-lg-4">
                <h5 class="fw-bold text-white mb-4 border-start border-3 border-danger ps-3">Lokasi Kami</h5>
                <div class="map-container shadow-lg rounded-4 overflow-hidden position-relative">
                    <iframe 
                        src="https://maps.google.com/maps?q=RS+Jantung+Tasikmalaya+JHC&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                        width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"
                        title="Lokasi RS JHC Tasikmalaya">
                    </iframe>
                    <div class="p-3 bg-white bg-opacity-10 backdrop-blur">
                        <small class="text-white"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Jl. Raya Tasikmalaya, Jawa Barat, Indonesia</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container position-relative">
        <div class="row py-4 border-top border-white border-opacity-10">
            <div class="col-12 text-center">
                <p class="mb-0 text-light opacity-50 small fw-bold">
                    &copy; 2026 RS Jantung Heart Center Tasikmalaya. All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Custom Footer Styling */
    .footer-nav li { margin-bottom: 12px; }
    
    .footer-link {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .footer-link:hover {
        color: #fff;
        transform: translateX(5px);
    }

    .icon-circle {
        width: 35px; height: 35px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.9rem;
    }

    .social-btn {
        width: 40px; height: 40px;
        background: rgba(255,255,255,0.1);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: 0.3s;
    }

    .social-btn:hover {
        background: #bd3030; /* JHC Red */
        color: white;
        transform: translateY(-3px);
    }

    .map-container {
        border: 1px solid rgba(255,255,255,0.1);
    }

    .footer-logo-main {
        filter: brightness(0) invert(1); /* Pastikan logo putih jika background gelap */
    }
  </style>
    </main>
    <!-- ==================== SCRIPTS ==================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Carousel Bootstrap
        const myCarousel = document.getElementById('heroCarousel');
        
        // Jika Anda ingin kontrol manual lewat JS (opsional)
        const carousel = new bootstrap.Carousel(myCarousel, {
            interval: 5000, // Kecepatan ganti slide (5 detik)
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
            const scheduleRaw = this.getAttribute('data-schedule') || "";

            document.getElementById('mdl-name').innerText = name;
            document.getElementById('mdl-specialty').innerText = specialty;
            document.getElementById('mdl-desc').innerText = desc;
            document.getElementById('mdl-img').src = img;

            // Render Jadwal
            const scheduleContainer = document.getElementById('mdl-schedule');
            if (scheduleContainer) {
              scheduleContainer.innerHTML = '';
              if (scheduleRaw.trim() !== "") {
                const schedules = scheduleRaw.split(',');
                schedules.forEach(item => {
                  const span = document.createElement('span');
                  span.className = 'badge bg-white text-info border border-info-subtle rounded-pill px-3 py-2 small fw-bold mb-1 me-1';
                  span.innerHTML = `<i class="far fa-clock me-2"></i>${item.trim()}`;
                  scheduleContainer.appendChild(span);
                });
              } else {
                scheduleContainer.innerHTML = '<small class="text-muted italic">Jadwal belum tersedia</small>';
              }
            }

            // PERBAIKAN: Update link agar tetap di web yang sama (booking.php)
            const bookBtn = document.getElementById('mdl-book-link');
            if(bookBtn) {
              // Mengirimkan parameter dokter dan spesialis ke halaman booking internal
              bookBtn.href = `booking.php?dokter=${encodeURIComponent(name)}&spesialis=${encodeURIComponent(specialty)}`;
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
                const navbarHeight = navbar.offsetHeight;
                const offsetTop = target.offsetTop - navbarHeight - 20;
                window.scrollTo({
                  top: offsetTop,
                  behavior: 'smooth'
                });
              }
            }
          });
        });

        // Lazy loading for images
        if ('IntersectionObserver' in window) {
          const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                  img.src = img.dataset.src;
                  img.removeAttribute('data-src');
                  observer.unobserve(img);
                }
              }
            });
          });

          document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
          });
        }
      });

      // Page load performance
      window.addEventListener('load', function() {
        document.body.classList.add('loaded');
      });

      document.addEventListener('DOMContentLoaded', function() {
        var myModalEl = document.getElementById('promoPopup');
        if (myModalEl) {
          var myModal = new bootstrap.Modal(myModalEl);
          setTimeout(function() {
            myModal.show();
          }, 1000); // Popup muncul setelah 1 detik
        }
      });
      <?php endif; ?>
    </script>
  </body>
</html>