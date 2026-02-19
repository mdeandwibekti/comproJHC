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

$tabs_config = [
    'visi-misi'      => ['label' => 'Visi & Misi', 'icon' => 'fa-bullseye'],
    'sejarah'        => ['label' => 'Sejarah',     'icon' => 'fa-history'],
    'salam-direktur' => ['label' => 'Salam Direktur','icon' => 'fa-user-tie'],
    'budaya-kerja'   => ['label' => 'Budaya Kerja', 'icon' => 'fa-hand-holding-heart']
];

$about_sections = [];
$res = $mysqli->query("SELECT * FROM about_us_sections");
if ($res) {
    while($row = $res->fetch_assoc()) { 
        $about_sections[$row['section_key']] = $row; 
    }
}

$settings = [];
$res_settings = $mysqli->query("SELECT * FROM settings2 WHERE setting_key LIKE 'contact_%' OR setting_key LIKE 'social_%'");
if ($res_settings) {
    while($row = $res_settings->fetch_assoc()){
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
$fac_query = "SELECT * FROM facilities WHERE category IS NULL OR category = '' ORDER BY display_order ASC";

$fac_result = $mysqli->query($fac_query);
if ($fac_result) { 
    while($row = $fac_result->fetch_assoc()) { 
        $facilities_data[] = $row; 
    } 
}

$popup_query = $mysqli->query("SELECT * FROM popups WHERE status = 'active' ORDER BY created_at DESC");

$active_popups = [];
if ($popup_query) {
    while($row = $popup_query->fetch_assoc()) {
        $active_popups[] = $row;
    }
}

$show_popup = (count($active_popups) > 0);
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    /* ============================================================
       TOKENS & RESET
    ============================================================ */
    :root {
      --crimson:      #C8102E;
      --crimson-deep: #8C0D20;
      --crimson-soft: #F4D0D5;
      --navy:         #0A1628;
      --navy-mid:     #1E3A5F;
      --slate:        #4A5568;
      --mist:         #F7F8FC;
      --white:        #FFFFFF;
      --gold:         #D4A942;

      --grad-primary: linear-gradient(135deg, #C8102E 0%, #8C0D20 100%);
      --grad-subtle:  linear-gradient(135deg, #FFF0F2 0%, #FFE4E8 100%);
      --grad-dark:    linear-gradient(160deg, #0A1628 0%, #1E3A5F 100%);
      --grad-mesh:    radial-gradient(ellipse at 20% 50%, rgba(200,16,46,0.12) 0%, transparent 50%),
                      radial-gradient(ellipse at 80% 20%, rgba(26,58,95,0.1) 0%, transparent 50%);

      --shadow-xs:    0 1px 3px rgba(10,22,40,.06), 0 1px 2px rgba(10,22,40,.04);
      --shadow-sm:    0 4px 12px rgba(10,22,40,.07), 0 2px 6px rgba(10,22,40,.04);
      --shadow-md:    0 8px 28px rgba(10,22,40,.10), 0 4px 10px rgba(10,22,40,.06);
      --shadow-lg:    0 20px 60px rgba(10,22,40,.13), 0 8px 20px rgba(10,22,40,.08);
      --shadow-xl:    0 32px 80px rgba(10,22,40,.18), 0 12px 28px rgba(10,22,40,.10);
      --shadow-red:   0 8px 28px rgba(200,16,46,.30);

      --radius-sm:  8px;
      --radius-md:  14px;
      --radius-lg:  20px;
      --radius-xl:  28px;
      --radius-2xl: 40px;

      --ease-out:   cubic-bezier(0.16, 1, 0.3, 1);
      --ease-in:    cubic-bezier(0.4, 0, 1, 1);
      --ease-both:  cubic-bezier(0.4, 0, 0.2, 1);

      --font-display: 'Outfit', sans-serif;
      --font-serif:   'Lora', serif;

      --nav-height: 76px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; font-size: 16px; }
    body {
      font-family: var(--font-display);
      color: var(--navy);
      background: var(--white);
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }
    img { max-width: 100%; display: block; height: auto; }
    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; cursor: pointer; }
    section { padding: 6rem 0; }

    /* ============================================================
       SCROLLBAR
    ============================================================ */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: var(--mist); }
    ::-webkit-scrollbar-thumb { background: var(--crimson); border-radius: 10px; }

    /* ============================================================
       NAVBAR
    ============================================================ */
    .site-nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 1040;
      height: var(--nav-height);
      background: rgba(255,255,255,0.97);
      backdrop-filter: blur(18px) saturate(160%);
      -webkit-backdrop-filter: blur(18px) saturate(160%);
      border-bottom: 1px solid rgba(0,0,0,0.07);
      transition: all 0.38s var(--ease-out);
    }

    .site-nav.scrolled {
      height: 64px;
      background: #fff;
      border-bottom: 1px solid rgba(200,16,46,0.15);
      box-shadow: 0 2px 16px rgba(10,22,40,0.08);
    }

    .nav-inner {
      max-width: 1340px;
      width: 100%;
      height: 100%;
      margin: 0 auto;
      padding: 0 2.5rem;
      display: grid;
      grid-template-columns: auto 1fr auto;
      grid-template-areas: "brand links cta";
      align-items: center;
      gap: 1.5rem;
    }

    .nav-brand   { grid-area: brand; }
    .nav-links   { grid-area: links; }
    .nav-cta-wrap { grid-area: cta; }
    .nav-hamburger { display: none; grid-area: cta; }

    .nav-brand {
      display: flex;
      align-items: center;
    }
    .nav-brand img {
      height: 56px;
      width: auto;
      object-fit: contain;
      transition: height 0.3s var(--ease-out);
    }
    .site-nav.scrolled .nav-brand img { height: 46px; }

    .nav-links {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.1rem;
      list-style: none;
    }

    .nav-links a {
      display: block;
      padding: 0.52rem 0.9rem;
      font-size: 0.875rem;
      font-weight: 700;
      color: #1a1a2e;
      border-radius: var(--radius-sm);
      transition: color 0.22s var(--ease-both);
      white-space: nowrap;
      position: relative;
      letter-spacing: 0.005em;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: 4px; left: 50%;
      transform: translateX(-50%);
      width: 0; height: 2.5px;
      background: var(--crimson);
      border-radius: 2px;
      transition: width 0.28s var(--ease-out);
    }
    .nav-links a:hover { color: var(--crimson); }
    .nav-links a:hover::after { width: 65%; }

    .nav-cta-wrap {
      display: flex;
      align-items: center;
      justify-content: flex-end;
    }

    .nav-cta {
      display: inline-flex;
      align-items: center;
      gap: 0.55rem;
      padding: 0.72rem 1.6rem;
      background: var(--crimson);
      color: #fff !important;
      font-size: 0.9rem;
      font-weight: 800;
      border-radius: 50px;
      border: none;
      box-shadow: 0 6px 22px rgba(200,16,46,.35);
      transition: all 0.28s var(--ease-out);
      white-space: nowrap;
      letter-spacing: 0.01em;
      cursor: pointer;
      text-decoration: none;
    }
    .nav-cta i { font-size: 0.88rem; }
    .nav-cta:hover {
      background: var(--crimson-deep);
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(200,16,46,.45);
      color: #fff !important;
    }

    .nav-hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      padding: 8px;
      background: none;
      border: 2px solid var(--crimson);
      border-radius: var(--radius-sm);
      cursor: pointer;
    }
    .nav-hamburger span {
      display: block;
      width: 22px; height: 2px;
      background: var(--crimson);
      border-radius: 2px;
      transition: all 0.3s var(--ease-out);
    }

    @media (max-width: 1080px) {
      .nav-inner {
        grid-template-columns: auto 1fr;
        grid-template-areas: "brand ham";
        padding: 0 1.25rem;
        gap: 0;
      }
      .nav-links    { display: none !important; grid-area: unset; }
      .nav-cta-wrap { display: none !important; grid-area: unset; }
      .nav-hamburger {
        display: flex;
        grid-area: ham;
        justify-self: end;
      }
      .nav-links.mobile-open {
        display: flex !important;
        position: absolute;
        top: calc(var(--nav-height) + 6px);
        left: 1rem; right: 1rem;
        background: var(--white);
        border-top: 3px solid var(--crimson);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        box-shadow: var(--shadow-xl);
        padding: 0.65rem;
        flex-direction: column;
        gap: 0.1rem;
        justify-content: flex-start;
        z-index: 200;
      }
      .nav-cta-wrap.mobile-open {
        display: flex !important;
        position: absolute;
        left: 1rem; right: 1rem;
        background: var(--white);
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        border-top: 1px solid rgba(0,0,0,0.07);
        padding: 0.65rem;
        z-index: 200;
        justify-content: stretch;
        box-shadow: 0 20px 40px rgba(10,22,40,.15);
      }
      .nav-links.mobile-open a {
        padding: 0.78rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.93rem;
        font-weight: 700;
        color: var(--navy);
      }
      .nav-links.mobile-open a:hover { background: var(--crimson-soft); color: var(--crimson); }
      .nav-links.mobile-open a::after { display: none !important; }
      .nav-cta-wrap.mobile-open .nav-cta { width: 100%; justify-content: center; }
    }

    @media (max-width: 575px) {
      .nav-inner { padding: 0 1rem; }
    }

    /* ============================================================
       FLOATING BUTTONS
    ============================================================ */
    .float-dock {
      position: fixed;
      bottom: 28px; right: 24px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      z-index: 1030;
    }

    .float-pill {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      padding: 0.7rem 1.1rem 0.7rem 0.85rem;
      border-radius: 50px;
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      border: 2.5px solid rgba(255,255,255,0.8);
      box-shadow: var(--shadow-lg);
      transition: all 0.3s var(--ease-out);
      white-space: nowrap;
      overflow: hidden;
      max-width: 54px;
    }

    .float-pill .fp-icon {
      width: 32px; height: 32px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
    }

    .float-pill .fp-text { 
      opacity: 0; 
      transition: opacity 0.25s var(--ease-both);
      pointer-events: none;
    }

    .float-pill:hover {
      max-width: 200px;
      padding-right: 1.3rem;
    }
    .float-pill:hover .fp-text { opacity: 1; }

    .float-igd { background: var(--grad-primary); color: #fff; }
    .float-igd .fp-icon { background: rgba(255,255,255,0.2); }
    .float-wa { background: #25D366; color: #fff; }
    .float-wa .fp-icon { background: rgba(255,255,255,0.2); }

    @keyframes float-pulse {
      0%, 100% { box-shadow: var(--shadow-lg), 0 0 0 0 rgba(200,16,46,0.5); }
      60%       { box-shadow: var(--shadow-lg), 0 0 0 14px rgba(200,16,46,0); }
    }
    .float-igd { animation: float-pulse 2.4s infinite; }

    @media (max-width: 575px) {
      .float-pill { padding: 0 !important; width: 50px; height: 50px; max-width: 50px !important; justify-content: center; border-radius: 50%; }
      .float-pill .fp-text { display: none; }
    }

    /* ============================================================
       HERO SECTION
    ============================================================ */
    .hero-wrap {
      position: relative;
      width: 100%;
      height: 100vh;
      min-height: 580px;
      max-height: 960px;
      overflow: hidden;
    }

    #heroCarousel,
    .hero-carousel-inner,
    .hero-item { height: 100%; }

    .hero-item { position: relative; overflow: hidden; }

    .hero-bg {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center top;
      background-repeat: no-repeat;
      transform-origin: center;
    }

    .hero-item.active .hero-bg {
      animation: hero-ken 15s ease-in-out forwards;
    }
    @keyframes hero-ken {
      from { transform: scale(1.0); }
      to   { transform: scale(1.08); }
    }

    .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        115deg,
        rgba(8,14,28,0.90) 0%,
        rgba(10,22,40,0.70) 40%,
        rgba(140,13,32,0.20) 75%,
        transparent 100%
      );
      z-index: 1;
    }

    .hero-body {
      position: relative;
      z-index: 2;
      height: 100%;
      display: flex;
      align-items: center;
      padding: 0 2rem;
      max-width: 1340px;
      margin: 0 auto;
    }

    .hero-text {
      max-width: 680px;
      padding-top: var(--nav-height);
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.45rem 1rem;
      background: rgba(200,16,46,0.25);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(200,16,46,0.4);
      border-radius: 50px;
      color: #FFB3BE;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 1.5rem;
    }

    .hero-title {
      font-size: clamp(2.1rem, 5.5vw, 3.8rem);
      font-weight: 900;
      color: #fff;
      line-height: 1.12;
      letter-spacing: -0.02em;
      margin-bottom: 1.25rem;
      text-shadow: 0 2px 20px rgba(0,0,0,0.3);
    }
    .hero-title em {
      font-style: normal;
      color: #FF8A9B;
      font-family: var(--font-serif);
    }

    .hero-desc {
      font-size: clamp(0.95rem, 2vw, 1.1rem);
      color: rgba(255,255,255,0.82);
      line-height: 1.8;
      margin-bottom: 2.25rem;
      max-width: 520px;
      font-weight: 400;
    }

    .hero-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
    }

    .carousel-ctrl {
      position: absolute;
      bottom: 2.5rem;
      right: 2rem;
      z-index: 10;
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .carousel-ctrl button {
      width: 46px; height: 46px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.4);
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(8px);
      color: #fff;
      font-size: 0.85rem;
      display: flex; align-items: center; justify-content: center;
      transition: all 0.25s var(--ease-out);
      cursor: pointer;
    }

    .carousel-ctrl button:hover {
      background: var(--crimson);
      border-color: var(--crimson);
    }

    .carousel-dots {
      position: absolute;
      bottom: 2.5rem;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 8px;
      z-index: 10;
    }

    .carousel-dots button {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,0.4);
      border: none;
      padding: 0;
      transition: all 0.3s var(--ease-out);
      cursor: pointer;
    }
    .carousel-dots button.active {
      width: 26px;
      border-radius: 4px;
      background: var(--crimson);
    }

    @media (max-width: 767px) {
      .hero-wrap { height: 80vh; min-height: 480px; }
      .hero-body { padding: 0 1.25rem; }
    }
    @media (max-width: 480px) {
      .carousel-ctrl { display: none; }
    }

    /* ============================================================
       SECTION HEADERS
    ============================================================ */
    .sec-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--crimson);
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-bottom: 0.75rem;
    }
    .sec-eyebrow::before,
    .sec-eyebrow::after {
      content: '';
      display: block;
      width: 20px; height: 2px;
      background: var(--crimson);
      border-radius: 2px;
    }

    .sec-title {
      font-size: clamp(1.8rem, 4vw, 2.6rem);
      font-weight: 800;
      color: var(--navy);
      letter-spacing: -0.025em;
      line-height: 1.2;
    }

    .sec-title em {
      font-style: normal;
      font-family: var(--font-serif);
      color: var(--crimson-deep);
    }

    .sec-subtitle {
      color: var(--slate);
      font-size: 0.975rem;
      line-height: 1.75;
      max-width: 520px;
      margin-top: 0.75rem;
    }

    .sec-header-center { text-align: center; }
    .sec-header-center .sec-eyebrow { justify-content: center; }
    .sec-header-center .sec-subtitle { margin: 0.75rem auto 0; }

    /* ============================================================
       BUTTONS
    ============================================================ */
    .btn-primary-jhc {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.75rem;
      background: var(--grad-primary);
      color: #fff;
      font-size: 0.875rem;
      font-weight: 700;
      border-radius: 50px;
      border: 2px solid transparent;
      box-shadow: var(--shadow-red);
      transition: all 0.3s var(--ease-out);
      letter-spacing: 0.01em;
      cursor: pointer;
    }
    .btn-primary-jhc:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 36px rgba(200,16,46,.38);
      color: #fff;
    }

    .btn-ghost-jhc {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.72rem 1.75rem;
      background: transparent;
      color: #fff;
      font-size: 0.875rem;
      font-weight: 700;
      border-radius: 50px;
      border: 2px solid rgba(255,255,255,0.5);
      transition: all 0.3s var(--ease-out);
      letter-spacing: 0.01em;
      cursor: pointer;
    }
    .btn-ghost-jhc:hover {
      background: rgba(255,255,255,0.15);
      border-color: rgba(255,255,255,0.8);
      transform: translateY(-2px);
      color: #fff;
    }

    .btn-outline-jhc {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.65rem 1.4rem;
      background: transparent;
      color: var(--crimson);
      font-size: 0.82rem;
      font-weight: 700;
      border-radius: 50px;
      border: 2px solid var(--crimson);
      transition: all 0.3s var(--ease-out);
      letter-spacing: 0.01em;
      cursor: pointer;
    }
    .btn-outline-jhc:hover {
      background: var(--grad-primary);
      color: #fff;
      border-color: transparent;
      transform: translateY(-2px);
      box-shadow: var(--shadow-red);
    }

    .btn-sm-jhc {
      padding: 0.5rem 1.15rem;
      font-size: 0.78rem;
    }

    /* ============================================================
       HOVER LIFT
    ============================================================ */
    .lift {
      transition: transform 0.35s var(--ease-out), box-shadow 0.35s var(--ease-out);
      will-change: transform;
    }
    .lift:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg) !important;
    }

    /* ============================================================
       ABOUT SECTION — IMPROVED LAYOUT
       Sesuai sketsa: [1][2][3] tabs di atas
       Kolom kiri: Gambar | Kolom kanan: Kata-kata
    ============================================================ */
    .about-section { 
      background: var(--mist);
      padding: 5rem 0;
    }

    /* Tab buttons row — di atas, centered */
    .about-tabs-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 2rem;
    }

    .about-tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.52rem 1.2rem;
      border-radius: 50px;
      border: 2px solid #E2E8F0;
      background: var(--white);
      color: var(--slate);
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      transition: all 0.25s var(--ease-out);
      cursor: pointer;
      white-space: nowrap;
      box-shadow: var(--shadow-xs);
    }
    .about-tab-btn i { font-size: 0.72rem; }
    .about-tab-btn .tab-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px; height: 20px;
      background: rgba(10,22,40,0.08);
      border-radius: 50%;
      font-size: 0.65rem;
      font-weight: 900;
      flex-shrink: 0;
    }
    .about-tab-btn:hover {
      border-color: var(--crimson);
      color: var(--crimson);
      background: var(--crimson-soft);
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }
    .about-tab-btn.active {
      background: var(--grad-primary);
      color: #fff;
      border-color: transparent;
      box-shadow: 0 6px 18px rgba(200,16,46,.30);
    }
    .about-tab-btn.active .tab-num {
      background: rgba(255,255,255,0.25);
    }

    /* Two-column body grid */
    .about-body-grid {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 1.75rem;
      align-items: stretch;
    }

    @media (max-width: 1199px) {
      .about-body-grid {
        grid-template-columns: 260px 1fr;
        gap: 1.5rem;
      }
    }
    @media (max-width: 991px) {
      .about-body-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
      }
    }

    /* Image column */
    .about-img-col {
      position: relative;
      border-radius: var(--radius-xl);
      overflow: hidden;
      height: 400px;
      box-shadow: var(--shadow-xl);
      flex-shrink: 0;
    }
    @media (max-width: 991px) {
      .about-img-col {
        height: 280px;
      }
    }
    @media (max-width: 575px) {
      .about-img-col {
        height: 220px;
      }
    }

    .about-img-col img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.32s ease, transform 0.45s var(--ease-out);
    }

    /* Decorative accents on image */
    .about-img-col::before {
      content: '';
      position: absolute;
      bottom: 0; right: 0;
      width: 60px; height: 60px;
      background: var(--crimson);
      clip-path: polygon(100% 0, 100% 100%, 0 100%);
      z-index: 5;
      opacity: 0.88;
    }
    .about-img-col::after {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 4px; height: 60%;
      background: var(--grad-primary);
      border-radius: 0 4px 4px 0;
      z-index: 5;
    }

    /* Image badge overlay */
    .about-img-badge {
      position: absolute;
      bottom: 1rem; left: 1rem;
      background: rgba(10,22,40,0.75);
      backdrop-filter: blur(10px);
      color: #fff;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.4rem 0.85rem;
      border-radius: 50px;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      z-index: 6;
      border: 1px solid rgba(255,255,255,0.15);
    }
    .about-img-badge i { color: #FF8A9B; font-size: 0.65rem; }

    /* Content panel (right column) */
    .about-content-panel {
      background: var(--white);
      border-radius: var(--radius-xl);
      border-left: 5px solid var(--crimson);
      box-shadow: var(--shadow-md);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      min-height: 340px;
    }
    @media (max-width: 991px) {
      .about-content-panel {
        min-height: 280px;
        border-left: none;
        border-top: 5px solid var(--crimson);
      }
    }

    .about-pane-wrap {
      flex: 1;
      overflow-y: auto;
      padding: 2rem 2.25rem;
    }
    @media (max-width: 575px) {
      .about-pane-wrap { padding: 1.5rem 1.25rem; }
    }

    .about-pane-wrap::-webkit-scrollbar { width: 3px; }
    .about-pane-wrap::-webkit-scrollbar-track { background: #f1f3f8; }
    .about-pane-wrap::-webkit-scrollbar-thumb { background: var(--crimson); border-radius: 10px; }

    .about-tab-pane { display: none; animation: fadeSlide 0.32s var(--ease-out); }
    .about-tab-pane.active { display: block; }

    @keyframes fadeSlide {
      from { opacity: 0; transform: translateX(14px); }
      to   { opacity: 1; transform: translateX(0); }
    }

    .about-tab-pane h3 {
      font-size: 1.1rem;
      font-weight: 800;
      color: var(--crimson-deep);
      margin-bottom: 1rem;
      padding-bottom: 0.7rem;
      border-bottom: 1.5px solid var(--crimson-soft);
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }
    .about-tab-pane h3 i {
      color: var(--crimson);
      font-size: 0.9rem;
    }

    .about-tab-pane .tab-text {
      font-size: 0.88rem;
      line-height: 1.9;
      color: var(--slate);
      text-align: justify;
    }

    /* ============================================================
       SERVICE CARDS
    ============================================================ */
    .services-section { background: var(--white); }

    .dept-divider {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2.5rem;
    }
    .dept-divider-line { flex: 1; height: 1px; background: #E2E8F0; }
    .dept-divider-label {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1.25rem;
      background: linear-gradient(135deg, #f7f8fc, #fff);
      border: 1.5px solid #E2E8F0;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--slate);
    }

    /* Layanan grid */
    .layanan-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
    }
    @media (max-width: 1100px) { .layanan-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 767px)  { .layanan-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }
    @media (max-width: 420px)  { .layanan-grid { grid-template-columns: 1fr; } }

    .lu-card {
      background: var(--white);
      border: 1.5px solid #E8ECF4;
      border-radius: 18px;
      padding: 2rem 1.25rem 1.6rem;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0;
      cursor: pointer;
      height: 100%;
      transition: transform 0.32s var(--ease-out),
                  box-shadow 0.32s var(--ease-out),
                  border-color 0.32s var(--ease-out);
      box-shadow: 0 2px 12px rgba(10,22,40,.05);
    }
    .lu-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 40px rgba(10,22,40,.12);
      border-color: rgba(200,16,46,.18);
    }

    .lu-icon {
      width: 80px; height: 80px;
      border-radius: 50%;
      background: #F1F3F8;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 1.1rem;
      transition: background 0.3s, transform 0.3s;
      flex-shrink: 0;
    }
    .lu-card:hover .lu-icon { background: var(--crimson-soft); transform: scale(1.07); }
    .lu-icon img { width: 44px; height: 44px; object-fit: contain; display: block; }
    .lu-icon .lu-fallback-icon { font-size: 1.6rem; color: var(--gold); }

    .lu-name {
      font-size: 0.9rem;
      font-weight: 700;
      color: var(--navy);
      line-height: 1.4;
      margin-bottom: 1.1rem;
      flex: 1;
    }

    .lu-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      padding: 0.55rem 1.3rem;
      background: var(--crimson-deep);
      color: #fff;
      font-size: 0.78rem;
      font-weight: 800;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      transition: all 0.25s var(--ease-out);
      letter-spacing: 0.01em;
      white-space: nowrap;
    }
    .lu-btn i { font-size: 0.72rem; }
    .lu-btn:hover {
      background: var(--crimson);
      box-shadow: 0 6px 20px rgba(200,16,46,.35);
      transform: translateY(-2px);
      color: #fff;
    }

    .service-card {
      background: var(--white);
      border: 1.5px solid #E8ECF4;
      border-radius: 18px;
      padding: 1.75rem 1.25rem 1.5rem;
      text-align: center;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: all 0.32s var(--ease-out);
      box-shadow: 0 2px 12px rgba(10,22,40,.05);
    }
    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 38px rgba(10,22,40,.11);
      border-color: rgba(200,16,46,.18);
    }

    .svc-card-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      flex: 1;
    }

    .svc-icon-wrap {
      width: 72px; height: 72px;
      background: #F1F3F8;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1rem;
      transition: background 0.3s, transform 0.3s;
    }
    .service-card:hover .svc-icon-wrap { background: var(--crimson-soft); transform: scale(1.07); }
    .svc-icon-wrap img { width: 40px; height: 40px; object-fit: contain; }
    .svc-icon-wrap i { font-size: 1.4rem; }

    .svc-name {
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 1rem;
      line-height: 1.4;
      flex: 1;
    }

    .btn-svc {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
      padding: 0.52rem 1.1rem;
      background: var(--crimson-deep);
      color: #fff;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 800;
      border: none;
      transition: all 0.25s var(--ease-out);
      cursor: pointer;
    }
    .btn-svc:hover {
      background: var(--crimson);
      box-shadow: 0 5px 16px rgba(200,16,46,.32);
      transform: translateY(-2px);
      color: #fff;
    }

    /* ============================================================
       FACILITIES
    ============================================================ */
    .facilities-section { background: linear-gradient(160deg, var(--mist) 0%, #EEF5FB 100%); }

    .fac-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
    @media (max-width: 991px) { .fac-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .fac-grid { grid-template-columns: 1fr; gap: 1rem; } }

    .fac-card {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      height: 340px;
      cursor: pointer;
      box-shadow: var(--shadow-sm);
      transition: transform 0.38s var(--ease-out), box-shadow 0.38s var(--ease-out), height 0.45s var(--ease-out);
    }
    .fac-card:hover { transform: translateY(-6px); box-shadow: 0 24px 56px rgba(10,22,40,.18); }
    .fac-card.expanded { height: auto; min-height: 340px; }

    .fac-bg {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-color: #1a2a4a;
      transition: transform 0.55s var(--ease-out);
    }
    .fac-card:hover .fac-bg { transform: scale(1.04); }
    .fac-card.expanded .fac-bg { transform: scale(1); }

    .fac-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(5,10,22,0.10) 0%, rgba(5,10,22,0.45) 40%, rgba(5,10,22,0.90) 100%);
      transition: background 0.4s;
    }
    .fac-card.expanded .fac-overlay { background: rgba(5,10,22,0.82); }

    .fac-body {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 1.75rem 1.6rem;
      z-index: 2;
    }

    .fac-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.32rem 0.85rem;
      background: rgba(200,16,46,0.82);
      backdrop-filter: blur(6px);
      border-radius: 50px;
      color: #fff;
      font-size: 0.65rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 0.6rem;
      width: fit-content;
    }

    .fac-title {
      font-size: 1.25rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.3;
      margin-bottom: 0.6rem;
      text-shadow: 0 1px 8px rgba(0,0,0,.4);
    }

    .fac-desc-wrap {
      overflow: hidden;
      transition: max-height 0.45s var(--ease-out);
      max-height: 0;
    }
    .fac-card.expanded .fac-desc-wrap { max-height: 400px; }

    .fac-desc {
      font-size: 0.83rem;
      color: rgba(255,255,255,0.88);
      line-height: 1.7;
      text-shadow: 0 1px 4px rgba(0,0,0,.3);
      padding-bottom: 0.75rem;
    }

    .fac-toggle {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0;
      background: none;
      border: none;
      color: rgba(255,255,255,0.75);
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
      transition: color 0.22s;
      letter-spacing: 0.02em;
      margin-top: 0.25rem;
    }
    .fac-toggle:hover { color: #fff; }
    .fac-toggle i { font-size: 0.65rem; transition: transform 0.3s var(--ease-out); }
    .fac-card.expanded .fac-toggle i.fa-chevron-down { transform: rotate(180deg); }

    .fac-number {
      position: absolute;
      top: 1.25rem; right: 1.4rem;
      font-size: 3.5rem;
      font-weight: 900;
      color: rgba(255,255,255,0.06);
      line-height: 1;
      pointer-events: none;
      z-index: 2;
      user-select: none;
    }

    /* ============================================================
       MCU PACKAGES
    ============================================================ */
    .mcu-section { background: var(--white); }

    .mcu-card {
      border-radius: var(--radius-xl);
      overflow: hidden;
      border: 1.5px solid #EDF0F5;
      transition: all 0.38s var(--ease-out);
      height: 100%;
      background: var(--white);
    }
    .mcu-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-xl); border-color: rgba(200,16,46,0.15); }

    .mcu-img-wrap {
      position: relative;
      height: 220px;
      overflow: hidden;
    }
    .mcu-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform 0.55s var(--ease-out);
    }
    .mcu-card:hover .mcu-img-wrap img { transform: scale(1.07); }

    .mcu-price-badge {
      position: absolute;
      bottom: 1rem; left: 1rem;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(8px);
      color: var(--navy-mid);
      font-weight: 800;
      font-size: 0.9rem;
      padding: 0.35rem 1rem;
      border-radius: var(--radius-sm);
      box-shadow: var(--shadow-sm);
    }

    .mcu-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .mcu-title { font-size: 1rem; font-weight: 800; color: var(--navy); line-height: 1.3; }
    .mcu-desc {
      font-size: 0.82rem;
      color: var(--slate);
      line-height: 1.65;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .mcu-actions { display: flex; gap: 0.75rem; margin-top: 0.25rem; }

    /* ============================================================
       VIRTUAL ROOM
    ============================================================ */
    .vr-section { background: var(--mist); }

    .vr-video-wrap {
      border-radius: var(--radius-xl);
      overflow: hidden;
      box-shadow: var(--shadow-xl);
      border: 3px solid var(--white);
    }

    .vr-feature-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.25rem;
      background: var(--white);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-xs);
      border: 1.5px solid #EDF0F5;
    }
    .vr-feature-icon {
      width: 48px; height: 48px;
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }
    .vr-feature-icon.red-bg { background: rgba(200,16,46,0.08); color: var(--crimson); }
    .vr-feature-icon.blue-bg { background: rgba(30,58,95,0.08); color: var(--navy-mid); }
    .vr-feature-card h6 { font-size: 0.88rem; font-weight: 700; margin-bottom: 0.15rem; color: var(--navy); }
    .vr-feature-card small { font-size: 0.75rem; color: var(--slate); }

    /* ============================================================
       NEWS CARDS
    ============================================================ */
    .news-section { background: var(--white); }

    .news-card {
      border-radius: var(--radius-xl);
      overflow: hidden;
      background: var(--white);
      border: 1.5px solid #EDF0F5;
      height: 100%;
      transition: all 0.38s var(--ease-out);
    }
    .news-card:hover { transform: translateY(-7px); box-shadow: var(--shadow-xl); border-color: rgba(200,16,46,0.1); }

    .news-img-wrap {
      height: 220px;
      overflow: hidden;
      position: relative;
    }
    .news-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform 0.55s var(--ease-out);
    }
    .news-card:hover .news-img-wrap img { transform: scale(1.06); }

    .news-date-tag {
      position: absolute;
      top: 1rem; left: 1rem;
      background: var(--grad-primary);
      color: #fff;
      font-size: 0.7rem;
      font-weight: 800;
      letter-spacing: 0.04em;
      padding: 0.35rem 0.9rem;
      border-radius: 50px;
      box-shadow: var(--shadow-md);
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }

    .news-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; }

    .news-category {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      background: rgba(200,16,46,0.07);
      color: var(--crimson);
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.04em;
      padding: 0.3rem 0.85rem;
      border-radius: 50px;
    }

    .news-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--navy);
      line-height: 1.45;
    }

    .news-excerpt {
      font-size: 0.82rem;
      color: var(--slate);
      line-height: 1.7;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .news-read-link {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      color: var(--crimson);
      font-size: 0.82rem;
      font-weight: 700;
      transition: all 0.25s var(--ease-out);
    }
    .news-read-link:hover { gap: 0.65rem; }
    .news-read-link i { font-size: 0.7rem; }

    /* ============================================================
       PARTNERS
    ============================================================ */
    .partners-section { background: var(--mist); }

    .partner-item {
      display: flex; align-items: center; justify-content: center;
      padding: 1.25rem;
      background: var(--white);
      border: 1.5px solid #EDF0F5;
      border-radius: var(--radius-md);
      transition: all 0.3s var(--ease-out);
      min-height: 88px;
    }
    .partner-item:hover {
      box-shadow: var(--shadow-md);
      border-color: rgba(200,16,46,0.15);
      transform: translateY(-3px);
    }
    .partner-logo {
      max-height: 52px;
      width: auto;
      object-fit: contain;
      filter: grayscale(20%) opacity(0.85);
      transition: all 0.3s var(--ease-out);
    }
    .partner-item:hover .partner-logo {
      filter: grayscale(0%) opacity(1);
      transform: scale(1.05);
    }

    /* ============================================================
       MODALS
    ============================================================ */
    .modal-content {
      border: none;
      border-radius: var(--radius-xl) !important;
      overflow: hidden;
      box-shadow: var(--shadow-xl);
    }

    .jhc-modal-header {
      background: var(--grad-dark);
      padding: 1.25rem 1.75rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .jhc-modal-header .modal-title {
      color: #fff;
      font-weight: 700;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }
    .jhc-modal-header .modal-title i { color: #FF8A9B; }
    .jhc-modal-header .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .jhc-modal-header .btn-close:hover { opacity: 1; }

    /* ============================================================
       FORM CONTROLS
    ============================================================ */
    .jhc-input {
      width: 100%;
      padding: 0.8rem 1.1rem;
      border: 1.5px solid #E2E8F0;
      border-radius: var(--radius-md);
      font-family: var(--font-display);
      font-size: 0.9rem;
      color: var(--navy);
      background: var(--white);
      transition: all 0.25s var(--ease-out);
      outline: none;
    }
    .jhc-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px rgba(200,16,46,0.1); }

    /* ============================================================
       FOOTER
    ============================================================ */
    .site-footer {
      background: var(--grad-dark);
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .site-footer::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 300px; height: 300px;
      background: radial-gradient(circle, rgba(200,16,46,0.12) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .site-footer::after {
      content: '';
      position: absolute;
      bottom: 0; right: 0;
      width: 250px; height: 250px;
      background: radial-gradient(circle, rgba(27,113,161,0.1) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .footer-inner { position: relative; z-index: 1; }
    .footer-main { padding: 5rem 0 3.5rem; }

    .footer-brand-logo {
      height: 56px;
      width: auto;
      filter: brightness(0) invert(1);
      margin-bottom: 1.25rem;
    }

    .footer-tagline {
      font-size: 0.875rem;
      color: rgba(255,255,255,0.62);
      line-height: 1.85;
      max-width: 300px;
      margin-bottom: 1.75rem;
    }

    .footer-contact-item {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin-bottom: 0.9rem;
    }
    .fci-icon {
      width: 36px; height: 36px;
      background: rgba(255,255,255,0.1);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.85rem;
      flex-shrink: 0;
      color: #FF8A9B;
    }
    .fci-label { font-size: 0.7rem; color: rgba(255,255,255,0.45); margin-bottom: 0.1rem; }
    .fci-value { font-size: 0.875rem; font-weight: 700; }

    .social-cluster { display: flex; gap: 0.6rem; margin-top: 1.5rem; }
    .social-btn {
      width: 40px; height: 40px;
      background: rgba(255,255,255,0.1);
      color: #fff;
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem;
      transition: all 0.28s var(--ease-out);
      border: 1px solid rgba(255,255,255,0.08);
    }
    .social-btn:hover { background: var(--crimson); border-color: var(--crimson); transform: translateY(-3px); color: #fff; }

    .footer-heading {
      font-size: 0.82rem;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.5);
      margin-bottom: 1.25rem;
      padding-left: 0.75rem;
      border-left: 2.5px solid var(--crimson);
    }

    .footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.6rem; }
    .footer-links a {
      font-size: 0.875rem;
      color: rgba(255,255,255,0.65);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.25s var(--ease-out);
    }
    .footer-links a::before {
      content: '';
      display: block;
      width: 4px; height: 4px;
      border-radius: 50%;
      background: var(--crimson);
      opacity: 0.6;
      transition: all 0.25s;
    }
    .footer-links a:hover { color: #fff; padding-left: 0.35rem; }
    .footer-links a:hover::before { opacity: 1; }

    .footer-map-wrap {
      border-radius: var(--radius-lg);
      overflow: hidden;
      border: 2px solid rgba(255,255,255,0.1);
    }
    .footer-map-wrap iframe { height: 220px; }
    .footer-map-label {
      padding: 0.65rem 1rem;
      background: rgba(255,255,255,0.07);
      font-size: 0.78rem;
      color: rgba(255,255,255,0.7);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .footer-map-label i { color: var(--crimson); }

    .footer-bottom {
      padding: 1.25rem 0;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    .footer-bottom p { font-size: 0.8rem; color: rgba(255,255,255,0.38); margin: 0; }

    .footer-contact-item a:hover .fci-value {
      color: #bd3030 !important;
      transition: 0.3s;
    }

    /* ============================================================
       PROMO POPUP
    ============================================================ */
    #promoPopupCarousel .modal-content { 
      border-radius: 24px !important; 
      overflow: hidden; 
      border: none;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
    }

    .btn-close-custom {
      position: absolute;
      right: 16px; top: 16px;
      z-index: 1100;
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(8px);
      border: none;
      width: 34px; height: 34px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      color: #8a3033;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      cursor: pointer;
    }
    .btn-close-custom:hover { transform: rotate(90deg) scale(1.1); background: #8a3033; color: #fff; }

    .popup-img-container {
      background: #f8f9fa;
      height: 400px;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; position: relative;
    }
    .popup-img-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }

    .content-area {
      padding: 2.5rem 2rem;
      background: #fff;
      text-align: center;
    }

    .popup-title { font-size: 1.35rem; font-weight: 800; color: #1a1a1a; margin-bottom: 0.75rem; letter-spacing: -0.5px; }
    .popup-text { font-size: 0.95rem; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; }

    .custom-nav {
      width: 42px; height: 42px;
      background: rgba(255, 255, 255, 0.9);
      color: #8a3033;
      border-radius: 50%;
      top: 40%;
      opacity: 0;
      margin: 0 15px;
      font-size: 1rem;
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.4s ease;
      display: flex; align-items: center; justify-content: center;
      z-index: 100;
    }
    .modal-content:hover .custom-nav { opacity: 1; }
    .custom-nav:hover { background: #8a3033; color: #fff; transform: scale(1.1); }

    .custom-indicators { bottom: 0 !important; transform: translateY(-175px); margin-bottom: 0; }
    .custom-indicators button {
      width: 8px !important; height: 8px !important;
      border-radius: 50% !important;
      background-color: #becad9 !important;
      border: none !important;
      margin: 0 4px !important;
      transition: all 0.3s ease;
    }
    .custom-indicators button.active {
      background-color: #8a3033 !important;
      width: 24px !important;
      border-radius: 10px !important;
    }

    .btn-danger.rounded-pill {
      padding: 12px 45px;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 1px;
      background: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
      border: none;
      box-shadow: 0 8px 20px -5px rgba(138, 48, 51, 0.4);
      transition: 0.3s;
    }
    .btn-danger.rounded-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px -5px rgba(138, 48, 51, 0.5);
      opacity: 0.95;
    }

    .modal.fade .modal-dialog {
      transform: scale(0.8) translateY(40px);
      transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal.show .modal-dialog { transform: scale(1) translateY(0); }

    /* ============================================================
       SPINNER
    ============================================================ */
    .spinner-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(255,255,255,0.9);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    .spinner-overlay.active { display: flex; }

    /* ============================================================
       UTILITIES
    ============================================================ */
    .text-crimson { color: var(--crimson) !important; }
    .text-navy    { color: var(--navy) !important; }
    .bg-mist      { background: var(--mist) !important; }
    .gap-container { max-width: 1340px; margin: 0 auto; padding: 0 2rem; }

    @media (max-width: 575px) {
      section { padding: 4rem 0; }
      .gap-container { padding: 0 1.1rem; }
    }
    @media (max-width: 768px) {
      .gap-container { padding: 0 1.25rem; }
    }
  </style>
</head>
<body>

<!-- Spinner -->
<div class="spinner-overlay" id="loadingSpinner">
  <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;">
    <div class="spinner-border text-danger" style="width:2.5rem;height:2.5rem;" role="status">
      <span class="visually-hidden">Loading…</span>
    </div>
    <p style="font-size:0.8rem;color:var(--slate);font-weight:600;letter-spacing:0.05em;">MEMUAT…</p>
  </div>
</div>

<main id="top">

  <!-- ============================================================
       NAVBAR
  ============================================================ -->
  <nav class="site-nav" id="mainNavbar" role="navigation" aria-label="Navigasi Utama">
    <div class="nav-inner">
      <a class="nav-brand" href="index.php" aria-label="RS JHC Tasikmalaya - Home">
        <?php $header_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; ?>
        <img src="public/<?= htmlspecialchars($header_logo); ?>" alt="JHC Logo">
      </a>

      <ul class="nav-links" id="navMenu" role="list">
        <li><a href="index.php#about_us">Tentang Kami</a></li>
        <li><a href="index.php#departments">Layanan</a></li>
        <li><a href="index.php#facilities">Fasilitas</a></li>
        <li><a href="index.php#mcu_packages_data">Paket MCU</a></li>
        <li><a href="index.php#virtual_room">Virtual Room</a></li>
        <li><a href="index.php#news">Berita</a></li>
      </ul>

      <div class="nav-cta-wrap" id="navCta">
        <a class="nav-cta" href="career.php">
          <i class="fas fa-briefcase"></i>
          Apply Job
        </a>
      </div>

      <button class="nav-hamburger" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- ============================================================
       FLOATING BUTTONS
  ============================================================ -->
  <div class="float-dock" role="complementary" aria-label="Kontak Cepat">
    <a href="tel:<?= $no_igd; ?>" class="float-pill float-igd" title="Darurat IGD" aria-label="Telepon IGD">
      <div class="fp-icon"><i class="fas fa-ambulance"></i></div>
      <span class="fp-text">IGD <?= $no_igd; ?></span>
    </a>
    <a href="https://wa.me/<?= $no_rs_wa; ?>" target="_blank" rel="noopener" class="float-pill float-wa" title="WhatsApp" aria-label="WhatsApp RS">
      <div class="fp-icon"><i class="fab fa-whatsapp"></i></div>
      <span class="fp-text">WhatsApp RS</span>
    </a>
  </div>

  <!-- ============================================================
       HERO / BANNER
  ============================================================ -->
  <section class="hero-wrap p-0" id="home" aria-label="Banner Utama">
    <div id="heroCarousel" class="carousel slide carousel-fade h-100"
         data-bs-ride="carousel" data-bs-interval="6000" aria-roledescription="carousel">
      <div class="carousel-inner hero-carousel-inner">
        <?php if (!empty($banners_data)): ?>
          <?php foreach ($banners_data as $idx => $banner): ?>
          <div class="carousel-item hero-item <?= $idx === 0 ? 'active' : ''; ?>"
               aria-roledescription="slide"
               aria-label="Slide <?= $idx + 1; ?> dari <?= count($banners_data); ?>">
            <div class="hero-bg"
                 style="background-image: url('public/<?= htmlspecialchars($banner['image_path']); ?>');"
                 role="img"
                 aria-label="<?= htmlspecialchars($banner['title']); ?>">
            </div>
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-body">
              <div class="hero-text">
                <div class="hero-badge" aria-hidden="true">
                  <i class="fas fa-heart"></i>
                  RS Jantung Tasikmalaya
                </div>
                <h1 class="hero-title"><?= htmlspecialchars($banner['title']); ?></h1>
                <p class="hero-desc"><?= htmlspecialchars($banner['description']); ?></p>
                <div class="hero-actions">
                  <a class="btn-primary-jhc" href="index.php#departments">
                    <i class="fas fa-stethoscope"></i>
                    Lihat Layanan
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="carousel-ctrl" aria-label="Navigasi carousel">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Berikutnya">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <div class="carousel-dots">
        <?php foreach ($banners_data as $idx => $b): ?>
          <button type="button"
                  data-bs-target="#heroCarousel"
                  data-bs-slide-to="<?= $idx; ?>"
                  class="<?= $idx === 0 ? 'active' : ''; ?>"
                  aria-current="<?= $idx === 0 ? 'true' : 'false'; ?>"
                  aria-label="Slide <?= $idx + 1; ?>">
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       ABOUT US 
  ============================================================ -->
  <section id="about_us" class="about-section">
    <div class="container" style="max-width:1280px;">

      <!-- Header Section -->
      <div class="sec-header-center mb-4">
        <div class="sec-eyebrow" aria-hidden="true">Mengenal Kami</div>
        <h2 class="sec-title"> <em>RS Jantung</em> Tasikmalaya</h2>
      </div>

      <!-- Tab Buttons [1][2][3] — centered, di atas dua kolom -->
      <div class="about-tabs-row" role="tablist" aria-label="Informasi RS Jantung Tasikmalaya">
        <?php $no = 0; foreach ($tabs_config as $key => $info): ?>
          <button class="about-tab-btn <?= $no === 0 ? 'active' : ''; ?>"
                  role="tab"
                  aria-selected="<?= $no === 0 ? 'true' : 'false'; ?>"
                  aria-controls="atab-<?= $key; ?>"
                  data-img="<?= $about_sections[$key]['image_path'] ?? ''; ?>"
                  onclick="switchAboutTab(this, 'atab-<?= $key; ?>')">
            <span class="tab-num"><?= $no + 1; ?></span>
            <i class="fas <?= $info['icon']; ?>"></i>
            <?= $info['label']; ?>
          </button>
        <?php $no++; endforeach; ?>
      </div>

      <!-- Body: Gambar kiri | Kata-kata kanan -->
      <div class="about-body-grid">

        <!-- Kolom Kiri: Gambar -->
        <div class="about-img-col">
          <?php 
            $first_key   = array_key_first($tabs_config);
            $db_path     = $about_sections[$first_key]['image_path'] ?? '';
            $display_img = !empty($db_path) ? 'public/' . $db_path : 'public/assets/img/default-about.jpg';
          ?>
          <img src="<?= $display_img; ?>"
               id="main-about-image"
               alt="Tentang RS Jantung Tasikmalaya">
          <div class="about-img-badge">
            <i class="fas fa-hospital-alt"></i>
            RS Jantung Tasikmalaya
          </div>
        </div>

        <!-- Kolom Kanan: Konten / Kata-kata -->
        <div class="about-content-panel">
          <div class="about-pane-wrap">
            <?php $no = 0; foreach ($tabs_config as $key => $info): ?>
              <div class="about-tab-pane <?= $no === 0 ? 'active' : ''; ?>"
                   id="atab-<?= $key; ?>"
                   role="tabpanel"
                   aria-labelledby="btn-<?= $key; ?>">
                <h3>
                  <i class="fas <?= $info['icon']; ?>"></i>
                  <?= htmlspecialchars($about_sections[$key]['title'] ?? $info['label']); ?>
                </h3>
                <p class="tab-text">
                  <?= (isset($about_sections[$key]['content']) && $about_sections[$key]['content'] !== '')
                    ? nl2br(htmlspecialchars((string)$about_sections[$key]['content']))
                    : 'Konten belum tersedia.'; ?>
                </p>
              </div>
            <?php $no++; endforeach; ?>
          </div>
        </div>

      </div><!-- /about-body-grid -->

    </div>
  </section>

  <!-- ============================================================
       SERVICES / DEPARTMENTS
  ============================================================ -->
  <section id="departments" class="services-section">
    <div class="container" style="max-width:1280px;">

      <div class="sec-header-center mb-5">
        <div class="sec-eyebrow" aria-hidden="true">Layanan Unggulan</div>
        <h2 class="sec-title">Pelayanan <em>Kami</em></h2>
        <p class="sec-subtitle">Layanan unggulan dan poliklinik spesialis untuk mendukung kesehatan Anda.</p>
      </div>

      <?php 
      function render_poliklinik_cards($data_list) {
        foreach ($data_list as $item): ?>
          <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="service-card">
              <div class="svc-card-inner">
                <div class="svc-icon-wrap">
                  <?php if (!empty($item['icon_path'])): ?>
                    <img src="<?= htmlspecialchars($item['icon_path']); ?>" alt="">
                  <?php else: ?>
                    <i class="fas fa-heartbeat text-primary"></i>
                  <?php endif; ?>
                </div>
                <h3 class="svc-name"><?= htmlspecialchars($item['name']); ?></h3>
                <a href="doctors_list.php?dept_id=<?= $item['id']; ?>" class="btn-svc">
                  <i class="fas fa-user-md"></i> Lihat Dokter
                </a>
              </div>
            </div>
          </div>
        <?php endforeach;
      }
      ?>

      <!-- Layanan Unggulan -->
      <?php if (!empty($layanan_data)): ?>
        <div class="dept-divider mt-2">
          <div class="dept-divider-line"></div>
          <div class="dept-divider-label"><i class="fas fa-stethoscope"></i> Layanan</div>
          <div class="dept-divider-line"></div>
        </div>

        <div class="layanan-grid mb-5">
          <?php foreach ($layanan_data as $item): ?>
            <div class="lu-card btn-open-layanan"
                role="button"
                tabindex="0"
                data-id="<?= $item['id']; ?>" 
                data-name="<?= htmlspecialchars($item['name']); ?>"
                data-desc="<?= htmlspecialchars($item['description']); ?>"
                data-expertise="<?= htmlspecialchars($item['special_skills']); ?>"
                data-info="<?= htmlspecialchars($item['additional_info']); ?>"
                data-image="<?= htmlspecialchars($item['icon_path'] ?? ''); ?>"
                data-btn-text="<?= htmlspecialchars($item['btn_text'] ?? 'Hubungi Kami'); ?>"
                data-btn-link="<?= htmlspecialchars($item['btn_link'] ?? '#'); ?>"
                data-bs-toggle="modal" data-bs-target="#modalLayanan">

              <div class="lu-icon">
                <?php if (!empty($item['icon_path'])): ?>
                  <img src="<?= htmlspecialchars($item['icon_path']); ?>"
                      alt="<?= htmlspecialchars($item['name']); ?>"
                      onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                  <span class="lu-fallback-icon" style="display:none;">
                    <i class="fas fa-star"></i>
                  </span>
                <?php else: ?>
                  <i class="fas fa-star lu-fallback-icon"></i>
                <?php endif; ?>
              </div>

              <h3 class="lu-name"><?= htmlspecialchars($item['name']); ?></h3>

              <div class="d-flex flex-column gap-2 mt-auto w-100">
                <button class="lu-btn w-100" aria-label="Detail <?= htmlspecialchars($item['name']); ?>">
                  <i class="fas fa-info-circle"></i> Detail
                </button>
                
                <a href="doctors_list.php?dept_id=<?= $item['id']; ?>" 
                  class="btn btn-sm btn-outline-danger rounded-pill fw-bold py-2" 
                  onclick="event.stopPropagation();" 
                  style="font-size: 0.75rem;">
                  <i class="fas fa-user-md me-1"></i> Lihat Dokter
                </a>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Poliklinik Spesialis -->
      <?php if (!empty($poliklinik_data)): ?>
        <div class="dept-divider mt-2">
          <div class="dept-divider-line"></div>
          <div class="dept-divider-label"><i class="fas fa-stethoscope"></i> Poliklinik Spesialis</div>
          <div class="dept-divider-line"></div>
        </div>
        <div class="row gx-3 gx-md-4">
          <?php render_poliklinik_cards($poliklinik_data); ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Modal: Detail Layanan -->
  <div class="modal fade" id="modalLayanan" tabindex="-1" aria-labelledby="modalLayananLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
        <div class="jhc-modal-header" style="background: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); padding: 1.2rem; color: white;">
          <h5 class="modal-title fw-bold" id="modalLayananLabel">
            <i class="fas fa-stethoscope me-2"></i>Detail Unit Layanan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        
        <div class="modal-body p-4">
          <div class="row g-4 align-items-center mb-4">
            <div class="col-md-3 text-center">
                <div class="p-3 bg-light rounded-circle d-inline-block shadow-sm" style="width: 100px; height: 100px;">
                    <img id="m-image" src="" alt="Icon" class="img-fluid" style="height: 100%; object-fit: contain;">
                </div>
            </div>
            <div class="col-md-9">
                <h3 id="m-name" class="fw-bold mb-1" style="color:#8a3033;"></h3>
                <div id="m-desc" class="text-secondary small" style="line-height: 1.6;"></div>
            </div>
          </div>
          <hr class="opacity-5">
        </div>

        <div class="modal-footer border-0 p-4 pt-0">
          <button type="button" class="btn btn-light rounded-pill px-4 fw-bold w-100" data-bs-dismiss="modal">
            Tutup Informasi
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       FACILITIES
  ============================================================ -->
  <?php if (!empty($facilities_data)): ?>
    <section id="facilities" class="facilities-section">
      <div class="container" style="max-width:1280px;">

        <div class="sec-header-center mb-5">
          <div class="sec-eyebrow" aria-hidden="true">Layanan & Fasilitas</div>
          <h2 class="sec-title">Fasilitas <em>RS Jantung</em> Tasikmalaya</h2>
          <p class="sec-subtitle">Fasilitas modern dan lengkap untuk kenyamanan dan kesembuhan Anda.</p>
        </div>

        <div class="fac-grid">
          <?php foreach ($facilities_data as $idx => $fac):
              $bg_style = !empty($fac['image_path'])
                  ? "background-image: url('public/" . htmlspecialchars($fac['image_path']) . "');"
                  : "background: linear-gradient(135deg, #0f1f3d 0%, #1E3A5F 100%);";
              
              $cat_param = urlencode($fac['name']); 
              $target_link = "facilities_list.php?category=" . $cat_param;
          ?>
          
          <a href="<?= $target_link; ?>" class="fac-card text-decoration-none" id="fac-<?= $idx; ?>">
              <div class="fac-bg" style="<?= $bg_style ?>"></div>
              <div class="fac-overlay"></div>
              <span class="fac-number" aria-hidden="true"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT); ?></span>
              
              <div class="fac-body">
                  <div class="fac-badge">
                      <i class="fas fa-hospital"></i> Unit Layanan
                  </div>
                  <h3 class="fac-title text-white"><?= htmlspecialchars($fac['name']); ?></h3>

                  <div class="fac-toggle mt-2 text-white">
                      <span class="fac-toggle-text">Lihat Detail Fasilitas</span>
                      <i class="fas fa-arrow-right ms-2"></i>
                  </div>
              </div>
          </a>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- ============================================================
       MCU PACKAGES
  ============================================================ -->
  <?php if (!empty($mcu_packages_data)): ?>
  <section id="mcu_packages_data" class="mcu-section">
    <div class="container" style="max-width:1280px;">

      <div class="sec-header-center mb-5">
        <div class="sec-eyebrow" aria-hidden="true">Layanan Check Up</div>
        <h2 class="sec-title">Pilih Paket <em>Kesehatan</em> Anda</h2>
        <p class="sec-subtitle">Deteksi dini adalah investasi terbaik untuk kesehatan Anda dan keluarga.</p>
      </div>

      <div class="row g-4">
        <?php foreach ($mcu_packages_data as $idx => $pkg): ?>
          <?php
            $wa_text = urlencode("Halo JHC, saya ingin reservasi paket: " . $pkg['title']);
            $wa_link = "https://api.whatsapp.com/send?phone=6287760615300&text={$wa_text}";
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="mcu-card">
              <div class="mcu-img-wrap">
                <img src="public/<?= htmlspecialchars($pkg['image_path']); ?>"
                     alt="<?= htmlspecialchars($pkg['title']); ?>">
                <div class="mcu-price-badge">
                  Rp <?= number_format($pkg['price'], 0, ',', '.'); ?>
                </div>
              </div>
              <div class="mcu-body">
                <h3 class="mcu-title"><?= htmlspecialchars($pkg['title']); ?></h3>
                <p class="mcu-desc"><?= htmlspecialchars(substr($pkg['description'], 0, 130)); ?>…</p>
                <div class="mcu-actions">
                  <button type="button"
                          class="btn-outline-jhc btn-sm-jhc"
                          style="flex:1;justify-content:center;"
                          data-bs-toggle="modal"
                          data-bs-target="#mcuModal<?= $idx; ?>">
                    <i class="fas fa-list"></i> Detail
                  </button>
                  <a href="<?= $wa_link; ?>" target="_blank" rel="noopener"
                     class="btn-primary-jhc btn-sm-jhc"
                     style="flex:1;justify-content:center;">
                    <i class="fab fa-whatsapp"></i> Reservasi
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- MCU Detail Modal -->
          <div class="modal fade" id="mcuModal<?= $idx; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="jhc-modal-header">
                  <h5 class="modal-title">
                    <i class="fas fa-file-medical"></i>Detail Paket MCU
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 p-lg-5">
                  <div class="row g-4">
                    <div class="col-md-5">
                      <img src="public/<?= htmlspecialchars($pkg['image_path']); ?>"
                           class="img-fluid rounded-3 shadow-sm w-100" alt="<?= htmlspecialchars($pkg['title']); ?>">
                    </div>
                    <div class="col-md-7">
                      <span class="sec-eyebrow" style="font-size:0.68rem;margin-bottom:0.5rem;">Rincian Paket</span>
                      <h3 class="fw-bold mb-1" style="color:var(--navy);font-size:1.25rem;"><?= htmlspecialchars($pkg['title']); ?></h3>
                      <p class="fw-bold mb-4" style="color:var(--crimson);font-size:1.4rem;">
                        Rp <?= number_format($pkg['price'], 0, ',', '.'); ?>
                      </p>
                      <div class="text-muted mb-4" style="font-size:0.88rem;line-height:1.8;">
                        <?= nl2br(htmlspecialchars($pkg['description'])); ?>
                      </div>
                      <a href="<?= $wa_link; ?>" target="_blank" rel="noopener"
                         class="btn-primary-jhc w-100 justify-content-center"
                         style="padding:.9rem;">
                        <i class="fab fa-whatsapp"></i> Hubungi Kami Sekarang
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
  <section id="virtual_room" class="vr-section">
    <div class="container" style="max-width:1280px;">
      <div class="row align-items-center g-4 g-lg-5">

        <div class="col-lg-6 order-2 order-lg-1">
          <div class="vr-video-wrap">
            <?php if (!empty($vr_data['video_url'])): 
              $embed_url = $vr_data['video_url'];
              $sep = strpos($embed_url,'?') !== false ? '&' : '?';
              $auto_url = $embed_url . $sep . 'autoplay=1&mute=1&loop=1&playlist=' . basename(parse_url($embed_url, PHP_URL_PATH));
            ?>
              <div class="ratio ratio-16x9">
                <iframe src="<?= htmlspecialchars($auto_url); ?>"
                        allow="autoplay;encrypted-media" allowfullscreen
                        title="Virtual Room RS Jantung Tasikmalaya"></iframe>
              </div>
            <?php elseif (!empty($vr_data['video_path'])): ?>
              <div class="ratio ratio-16x9 bg-black">
                <video class="w-100 h-100" autoplay muted loop controls style="object-fit:cover;">
                  <source src="<?= htmlspecialchars($vr_data['video_path']); ?>" type="video/mp4">
                </video>
              </div>
            <?php else: ?>
              <img src="<?= htmlspecialchars($vr_data['image_path_360']); ?>"
                   class="w-100" alt="Virtual Room">
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-6 order-1 order-lg-2">
          <div class="sec-eyebrow" aria-hidden="true">Virtual Room</div>
          <h2 class="sec-title mb-4"><?= htmlspecialchars($vr_data['title']); ?></h2>
          <p class="sec-subtitle mb-5" style="max-width:100%;">
            <?= nl2br(htmlspecialchars($vr_data['content'])); ?>
          </p>

          <div class="row g-3">
            <div class="col-sm-6">
              <div class="vr-feature-card">
                <div class="vr-feature-icon blue-bg"><i class="fas fa-user-md"></i></div>
                <div>
                  <h6>Dokter Ahli</h6>
                  <small>Berpengalaman & Berdedikasi</small>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="vr-feature-card">
                <div class="vr-feature-icon red-bg"><i class="fas fa-clock"></i></div>
                <div>
                  <h6>Layanan 24 Jam</h6>
                  <small>Siap melayani kapan saja</small>
                </div>
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
  <section id="news" class="news-section">
    <div class="container" style="max-width:1280px;">

      <div class="sec-header-center mb-5">
        <div class="sec-eyebrow" aria-hidden="true">Berita Terkini</div>
        <h2 class="sec-title">
          <?= htmlspecialchars($settings['news_section_title'] ?? 'Berita &'); ?>
          <em><?= strpos(($settings['news_section_title'] ?? ''), ' ') !== false ? '' : 'Artikel'; ?></em>
        </h2>
        <p class="sec-subtitle">Informasi terbaru seputar kesehatan jantung dan layanan RS Jantung Tasikmalaya.</p>
      </div>

      <div class="row g-4">
        <?php foreach ($news_data as $article): ?>
        <div class="col-md-6 col-lg-4">
          <article class="news-card">
            <div class="news-img-wrap">
              <img src="public/<?= htmlspecialchars($article['image_path']); ?>"
                   alt="<?= htmlspecialchars($article['title']); ?>">
              <div class="news-date-tag">
                <i class="fas fa-calendar-alt"></i>
                <?= date('d M Y', strtotime($article['post_date'])); ?>
              </div>
            </div>
            <div class="news-body">
              <div>
                <span class="news-category">
                  <i class="fas fa-tag"></i><?= htmlspecialchars($article['category']); ?>
                </span>
              </div>
              <h3 class="news-title"><?= htmlspecialchars($article['title']); ?></h3>
              <p class="news-excerpt"><?= substr(strip_tags($article['content']), 0, 110); ?>…</p>
              <button class="news-read-link btn-read-more"
                      style="background:none;border:none;padding:0;font-family:inherit;"
                      data-bs-toggle="modal" data-bs-target="#modalArticle"
                      data-title="<?= htmlspecialchars($article['title']); ?>"
                      data-category="<?= htmlspecialchars($article['category']); ?>"
                      data-date="<?= date('d M Y', strtotime($article['post_date'])); ?>"
                      data-image="public/<?= htmlspecialchars($article['image_path']); ?>"
                      data-content="<?= htmlspecialchars($article['content']); ?>">
                Baca Selengkapnya <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- Modal: Detail Artikel -->
  <div class="modal fade" id="modalArticle" tabindex="-1" aria-labelledby="modalArticleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="jhc-modal-header">
          <h5 class="modal-title" id="modalArticleLabel">
            <i class="fas fa-newspaper"></i>Artikel
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-0">
          <img id="article-img" src="" class="w-100"
               style="max-height:280px;object-fit:cover;display:block;" alt="">
          <div class="p-4 p-lg-5">
            <div class="d-flex gap-2 mb-3 flex-wrap">
              <span class="news-category">
                <i class="fas fa-tag"></i><span id="article-category"></span>
              </span>
              <span class="news-category" style="background:rgba(30,58,95,0.07);color:var(--navy-mid);">
                <i class="fas fa-calendar-alt"></i><span id="article-date"></span>
              </span>
            </div>
            <h3 id="article-title" class="fw-bold mb-4" style="color:var(--navy);font-size:1.3rem;"></h3>
            <div id="article-content" class="text-secondary" style="font-size:0.9rem;line-height:1.9;text-align:justify;"></div>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4">
          <button type="button" class="btn-outline-jhc" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       PARTNERS
  ============================================================ -->
  <section id="partners" style="background:linear-gradient(135deg,#f8f9fa 0%,#fff 100%); padding: 5rem 0;">
    <div class="container" style="max-width:1280px;">
      <div class="sec-header-center mb-5">
        <div class="sec-eyebrow" aria-hidden="true">
          <i class="fas fa-handshake"></i>
          Mitra Kami
        </div>
        <h2 class="sec-title">Mitra Asuransi & <em>Perusahaan</em></h2>
        <p class="sec-subtitle">Bekerja sama dengan berbagai mitra terpercaya untuk pelayanan terbaik.</p>
      </div>

      <div class="row justify-content-center align-items-center g-4">
        <?php if (!empty($partners_data)): ?>
          <?php foreach($partners_data as $partner): ?>
          <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center">
            <a href="<?= htmlspecialchars(!empty($partner['url']) ? $partner['url'] : '#'); ?>"
               target="<?= !empty($partner['url']) ? '_blank' : '_self'; ?>"
               rel="noopener noreferrer"
               class="partner-item d-block"
               data-bs-toggle="tooltip" title="<?= htmlspecialchars($partner['name']); ?>">
              <img src="public/<?= htmlspecialchars($partner['logo_path']); ?>"
                   class="partner-logo"
                   alt="<?= htmlspecialchars($partner['name']); ?>"
                   onerror="this.src='public/assets/img/gallery/default-partner.png';">
            </a>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-info-circle fa-3x mb-3 d-block opacity-25"></i>
            <p>Belum ada mitra yang ditampilkan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       PROMO POPUP
  ============================================================ -->
  <?php if ($show_popup): ?>
    <div class="modal fade popup-modern" id="promoPopupCarousel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>

                <div class="modal-body p-0">
                    <div id="carouselPromo" class="carousel slide" data-bs-ride="carousel">
                        
                        <?php if (count($active_popups) > 1): ?>
                        <div class="carousel-indicators" style="bottom: 20px;">
                            <?php foreach ($active_popups as $index => $popup): ?>
                            <button type="button" data-bs-target="#carouselPromo" data-bs-slide-to="<?= $index; ?>" class="<?= $index === 0 ? 'active' : ''; ?>" aria-current="<?= $index === 0 ? 'true' : 'false'; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="carousel-inner">
                            <?php foreach ($active_popups as $index => $popup): 
                                $img_url = 'public/' . htmlspecialchars($popup['image_path']);
                            ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                <?php if (!empty($popup['image_path'])): ?>
                                    <div class="popup-img-container">
                                        <img src="<?= $img_url; ?>" class="d-block w-100" alt="<?= htmlspecialchars($popup['title']); ?>"
                                            onerror="this.src='public/assets/img/gallery/logo.png'; this.style.padding='50px';">
                                    </div>
                                <?php endif; ?>

                                <div class="p-4 p-lg-5 text-center bg-white">
                                    <?php if (!empty($popup['title'])): ?>
                                        <h4 class="fw-bold text-dark mb-3"><?= htmlspecialchars($popup['title']); ?></h4>
                                    <?php endif; ?>

                                    <?php if (!empty($popup['content'])): ?>
                                        <div class="text-muted mb-4" style="line-height: 1.6; font-size: 0.9rem;">
                                            <?= nl2br(htmlspecialchars($popup['content'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <button type="button" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" 
                                            data-bs-dismiss="modal" style="background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); border: none;">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($active_popups) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPromo" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselPromo" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <?php endif; ?>

  <!-- ============================================================
       FOOTER
  ============================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="footer-inner">
      <div class="container" style="max-width:1280px;">
        <div class="footer-main">
          <div class="row g-5">

            <div class="col-12 col-lg-4">
              <?php 
                $footer_logo = !empty($settings['footer_logo_path']) ? $settings['footer_logo_path'] : 'assets/img/gallery/JHC_Logo.png'; 
              ?>
              <a href="index.php" aria-label="Beranda">
                <img src="public/<?= htmlspecialchars($footer_logo); ?>"
                     class="footer-brand-logo" alt="RS JHC Tasikmalaya">
              </a>
              <p class="footer-tagline">
                <?= htmlspecialchars($settings['contact_tagline'] ?? 'Memberikan pelayanan kesehatan jantung terpadu dengan standar kualitas tinggi.'); ?>
              </p>

              <div class="footer-contact-item">
                <div class="fci-icon"><i class="fas fa-ambulance"></i></div>
                <div>
                  <p class="fci-label">Gawat Darurat (IGD)</p>
                  <?php 
                    $igd_raw = $settings['contact_igd'] ?? '(0265) 3172112';
                    $igd_dial = preg_replace('/[^0-9]/', '', $igd_raw);
                  ?>
                  <a href="tel:<?= $igd_dial; ?>" class="text-decoration-none">
                    <p class="fci-value"><?= htmlspecialchars($igd_raw); ?></p>
                  </a>
                </div>
              </div>

              <div class="footer-contact-item">
                <div class="fci-icon"><i class="fab fa-whatsapp"></i></div>
                <div>
                  <p class="fci-label">WhatsApp RS</p>
                  <?php 
                    $wa_raw = $settings['contact_whatsapp'] ?? '+62 851-7500-0375';
                    $wa_link = preg_replace('/[^0-9]/', '', $wa_raw);
                    if (substr($wa_link, 0, 1) === '0') {
                        $wa_link = '62' . substr($wa_link, 1);
                    }
                  ?>
                  <a href="https://wa.me/<?= $wa_link; ?>" target="_blank" class="text-decoration-none">
                    <p class="fci-value"><?= htmlspecialchars($wa_raw); ?></p>
                  </a>
                </div>
              </div>

              <div class="footer-contact-item">
                <div class="fci-icon"><i class="fas fa-envelope"></i></div>
                <div>
                  <p class="fci-label">Email Resmi</p>
                  <?php $email_raw = $settings['contact_email'] ?? 'jhc.tasik@gmail.com'; ?>
                  <a href="mailto:<?= htmlspecialchars($email_raw); ?>" class="text-decoration-none">
                    <p class="fci-value"><?= htmlspecialchars($email_raw); ?></p>
                  </a>
                </div>
              </div>

              <div class="social-cluster">
                <?php if(!empty($settings['social_facebook'])): ?>
                  <a href="<?= htmlspecialchars($settings['social_facebook']); ?>" class="social-btn" target="_blank" rel="noopener">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                <?php endif; ?>
                <?php if(!empty($settings['social_instagram'])): ?>
                  <a href="<?= htmlspecialchars($settings['social_instagram']); ?>" class="social-btn" target="_blank" rel="noopener">
                    <i class="fab fa-instagram"></i>
                  </a>
                <?php endif; ?>
                <?php if(!empty($settings['social_youtube'])): ?>
                  <a href="<?= htmlspecialchars($settings['social_youtube']); ?>" class="social-btn" target="_blank" rel="noopener">
                    <i class="fab fa-youtube"></i>
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-6 col-lg-2">
              <h5 class="footer-heading">Informasi</h5>
              <ul class="footer-links">
                <li><a href="index.php#about_us">Tentang Kami</a></li>
                <li><a href="index.php#facilities">Fasilitas</a></li>
                <li><a href="index.php#doctors">Tim Dokter</a></li>
                <li><a href="index.php#news">Berita & Artikel</a></li>
                <li><a href="career.php">Karir / Lowongan</a></li>
              </ul>
            </div>

            <div class="col-6 col-lg-2">
              <h5 class="footer-heading">Layanan</h5>
              <ul class="footer-links">
                <?php foreach (array_slice($layanan_data, 0, 5) as $d): ?>
                  <li>
                    <a href="index.php#departments">
                      <?= htmlspecialchars($d['name']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="col-12 col-lg-4">
              <h5 class="footer-heading">Lokasi Kami</h5>
              <div class="footer-map-wrap">
                <iframe
                  src="<?= $settings['contact_map_url'] ?? ''; ?>"
                  width="100%" height="220" style="border:0;display:block;" allowfullscreen="" loading="lazy"
                  title="Lokasi RS Jantung Tasikmalaya"></iframe>
                <div class="footer-map-label">
                  <i class="fas fa-map-marker-alt"></i>
                  <?= htmlspecialchars($settings['contact_address'] ?? 'Alamat belum diatur.'); ?>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="container" style="max-width:1280px;">
        <div class="footer-bottom text-center">
          <p>© <?= date('Y'); ?> RS Jantung Heart Center Tasikmalaya. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </footer>

</main>

<!-- ============================================================
     SCRIPTS
============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
  'use strict';

  /* ── Navbar scroll ── */
  const navbar = document.getElementById('mainNavbar');
  const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 60);
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ── Hamburger menu ── */
  const toggler = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  const navCta  = document.getElementById('navCta');

  function closeMenu() {
    navMenu.classList.remove('mobile-open');
    navCta.classList.remove('mobile-open');
    toggler.setAttribute('aria-expanded', 'false');
    const spans = toggler.querySelectorAll('span');
    spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
    navCta.style.top = '';
  }

  toggler.addEventListener('click', function() {
    const isOpen = navMenu.classList.toggle('mobile-open');
    navCta.classList.toggle('mobile-open', isOpen);
    this.setAttribute('aria-expanded', isOpen.toString());

    const spans = this.querySelectorAll('span');
    if (isOpen) {
      spans[0].style.transform = 'translateY(7px) rotate(45deg)';
      spans[1].style.opacity   = '0';
      spans[2].style.transform = 'translateY(-7px) rotate(-45deg)';

      requestAnimationFrame(() => {
        const menuH = navMenu.offsetHeight;
        const navH  = navbar.offsetHeight;
        navCta.style.top = (navH + 6 + menuH) + 'px';
        navCta.style.borderTop = '1px solid rgba(0,0,0,0.07)';
      });
    } else {
      closeMenu();
    }
  });

  navMenu.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', function(e) {
    if (!navbar.contains(e.target)) closeMenu();
  });

  /* ── Smooth Scroll ── */
  document.querySelectorAll('a[href^="index.php#"], a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      const hashIndex = href.indexOf('#');
      if (hashIndex === -1) return;
      const hash = href.slice(hashIndex);
      if (hash === '#' || hash === '#!') return;
      const target = document.querySelector(hash);
      if (!target) return;
      e.preventDefault();
      const offset = navbar.offsetHeight + 16;
      window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
    });
  });

  /* ── About Tab Switcher ── */
  window.switchAboutTab = function(btn, targetId) {
    /* Deactivate all */
    document.querySelectorAll('.about-tab-btn').forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.about-tab-pane').forEach(p => p.classList.remove('active'));

    /* Activate chosen */
    btn.classList.add('active');
    btn.setAttribute('aria-selected', 'true');
    const pane = document.getElementById(targetId);
    if (pane) pane.classList.add('active');

    /* Swap image with smooth transition */
    const imgPath = btn.getAttribute('data-img');
    const mainImg = document.getElementById('main-about-image');
    if (imgPath && imgPath.trim()) {
      mainImg.style.opacity = '0';
      mainImg.style.transform = 'scale(1.04)';
      mainImg.style.transition = 'opacity 0.28s ease, transform 0.4s var(--ease-out)';
      setTimeout(() => {
        mainImg.src = 'public/' + imgPath;
        mainImg.onload = () => {
          mainImg.style.opacity = '1';
          mainImg.style.transform = 'scale(1)';
        };
        mainImg.onerror = () => {
          mainImg.style.opacity = '1';
          mainImg.style.transform = 'scale(1)';
        };
      }, 240);
    }
  };

  /* ── Modal: Detail Layanan ── */
  document.addEventListener('DOMContentLoaded', function() {
    const modalLayanan = document.getElementById('modalLayanan');
    
    modalLayanan.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const deptId = button.getAttribute('data-id');
        
        document.getElementById('m-name').textContent = button.getAttribute('data-name');
        document.getElementById('m-desc').innerHTML = button.getAttribute('data-desc');
        document.getElementById('m-image').src = button.getAttribute('data-image');

        const doctorContainer = document.getElementById('m-doctor-list');
        if (doctorContainer) {
          doctorContainer.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-danger" role="status"></div><span class="ms-2">Mencari dokter...</span></div>';

          fetch('get_doctors_api.php?dept_id=' + deptId)
              .then(response => response.json())
              .then(data => {
                  doctorContainer.innerHTML = '';
                  
                  if (data.length > 0) {
                      data.forEach(doc => {
                          doctorContainer.innerHTML += `
                              <div class="col-md-6">
                                  <div class="d-flex align-items-center p-2 border rounded-3 shadow-sm bg-white">
                                      <img src="public/${doc.photo_path || 'assets/img/default-doctor.png'}" 
                                           class="rounded-circle me-3" 
                                           style="width: 50px; height: 50px; object-fit: cover;">
                                      <div>
                                          <div class="fw-bold small text-dark">${doc.name}</div>
                                          <div class="text-danger" style="font-size: 0.65rem; font-weight: 700;">${doc.specialty}</div>
                                      </div>
                                  </div>
                              </div>
                          `;
                      });
                  } else {
                      doctorContainer.innerHTML = '<div class="col-12 text-center py-3 text-muted small">Jadwal dokter belum tersedia untuk unit ini.</div>';
                  }
              })
              .catch(error => {
                  doctorContainer.innerHTML = '<div class="col-12 text-center py-3 text-danger small">Gagal memuat data dokter.</div>';
              });
        }
    });
  });
  
  /* ── Fasilitas: expand / collapse ── */
  window.toggleFacDesc = function(idx) {
    const card       = document.getElementById('fac-' + idx);
    const toggleBtn  = card ? card.querySelector('.fac-toggle') : null;
    const toggleText = card ? card.querySelector('.fac-toggle-text') : null;

    if (!card) return;
    const isExpanded = card.classList.toggle('expanded');

    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', isExpanded.toString());
    if (toggleText) toggleText.textContent = isExpanded ? 'Sembunyikan' : 'Baca Selengkapnya';

    if (isExpanded) {
      card.style.transform = 'none';
    }
  };

  document.querySelectorAll('.fac-toggle').forEach(btn => {
    btn.addEventListener('click', e => e.stopPropagation());
  });

  /* ── Modal: Artikel ── */
  document.querySelectorAll('.btn-read-more').forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('article-title').textContent    = this.dataset.title    || '';
      document.getElementById('article-category').textContent = this.dataset.category || '';
      document.getElementById('article-date').textContent     = this.dataset.date     || '';
      document.getElementById('article-img').src              = this.dataset.image    || '';
      document.getElementById('article-content').innerHTML    =
        (this.dataset.content || '').replace(/\n/g, '<br>');
    });
  });

  /* ── Tooltips ── */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  /* ── Promo Popup ── */
  document.addEventListener("DOMContentLoaded", function() {
    var promoElement = document.getElementById('promoPopupCarousel');
    if (promoElement) {
        var myModal = new bootstrap.Modal(promoElement);
        setTimeout(function() {
            myModal.show();
        }, 1000);
    }
  });

  /* ── Animate on scroll ── */
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity   = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.07, rootMargin: '0px 0px -32px 0px' });

  document.querySelectorAll('.lu-card, .service-card, .fac-card, .mcu-card, .news-card').forEach(el => {
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(22px)';
    el.style.transition = 'opacity 0.52s cubic-bezier(0.16,1,0.3,1), transform 0.52s cubic-bezier(0.16,1,0.3,1)';
    observer.observe(el);
  });

})();
</script>
</body>
</html>