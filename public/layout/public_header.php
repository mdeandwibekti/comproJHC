<?php
require_once __DIR__ . '/../../config.php';

// Fetch all settings
$settings = [];
$settings_result = $mysqli->query("SELECT setting_key, setting_value FROM settings2");
while($setting = $settings_result->fetch_assoc()){
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title><?php echo $page_title ?? 'JHC | Landing, Responsive &amp; Business Template'; ?></title>

    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo rtrim(BASE_URL, '/'); ?>/public/<?php echo htmlspecialchars($settings['favicon_path'] ?? 'assets/img/favicons/favicon.ico'); ?>">
    <meta name="msapplication-TileImage" content="<?php echo rtrim(BASE_URL, '/'); ?>/public/<?php echo htmlspecialchars($settings['favicon_path'] ?? 'assets/img/favicons/mstile-150x150.png'); ?>">
    <meta name="theme-color" content="#ffffff">

    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link href="/comprojhc/public/assets/css/theme.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>

    <style>
      /* ── General ───────────────────────────────────── */
      .banner-image {
        transition: opacity 1s ease-in-out;
        position: absolute;
        top: 0;
        left: 0;
      }
      .icon-hover-effect .deparment-icon-hover { display: none; }
      .icon-hover-effect:hover .deparment-icon  { display: none; }
      .icon-hover-effect:hover .deparment-icon-hover { display: block; }

      .price-sticker {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #800000;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      }

      #panorama {
        width: 100%;
        height: 500px;
      }

      /* ── Navbar ─────────────────────────────────────── */
      @media (min-width: 1200px) {

        body {
          padding-top: 100px;
        }

        .public-navbar-container {
          position: fixed;
          width: 100%;
          top: 0;
          left: 0;
          z-index: 1030;
        }

        .main-navbar {
          background: linear-gradient(90deg, #b63d3f, #d8584a);
          margin: 10px auto 0 auto;
          padding: 8px 22px;
          width: 90%;
          border-radius: 50px;
          display: flex;
          align-items: center;
          justify-content: space-between;
          box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        /* ── Logo – matching Heartology reference size ── */
        .main-navbar .navbar-brand {
          display: flex;
          align-items: center;
          flex-shrink: 0;
          margin-right: 20px;
        }

        .main-navbar .navbar-brand img {
          height: 58px;       /* Matches Heartology logo height in reference */
          width: auto;
          max-width: 200px;
          object-fit: contain;
          filter: brightness(0) invert(1); /* White logo on gradient bg */
          display: block;
        }

        /* ── Nav Links ─────────────────────────────────── */
        .main-navbar .nav-links {
          display: flex;
          gap: 22px;
          margin: 0 auto;
        }

        .main-navbar .nav-links a {
          color: rgba(255,255,255,0.92);
          text-decoration: none;
          font-size: 14.5px;
          font-weight: 500;
          letter-spacing: 0.01em;
          white-space: nowrap;
          transition: color 0.2s;
        }

        .main-navbar .nav-links a:hover {
          color: #ffffff;
        }

        /* ── Search Box ────────────────────────────────── */
        .search-box {
          background: white;
          display: flex;
          align-items: center;
          padding: 6px 14px;
          border-radius: 20px;
          gap: 6px;
          flex-shrink: 0;
        }

        .search-box input {
          border: none;
          outline: none;
          background: transparent;
          font-size: 13px;
          width: 120px;
        }

        .search-box i {
          cursor: pointer;
          font-size: 13px;
          color: #999;
        }

        /* ── Hide Bootstrap defaults not needed on desktop */
        .main-navbar .navbar-toggler,
        .main-navbar .btn-danger {
          display: none;
        }

        .main-navbar .navbar-collapse {
          display: contents;
        }
      }

      /* ── Mobile adjustments ─────────────────────────── */
      @media (max-width: 1199px) {
        .main-navbar .navbar-brand img {
          height: 46px;
          width: auto;
        }
      }
    </style>
  </head>

  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">

      <div class="public-navbar-container">
        <nav class="navbar navbar-expand-xl main-navbar">

          <!-- Logo -->
          <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img
              src="/comprojhc/public/<?php echo htmlspecialchars($settings['header_logo_path'] ?? 'assets/img/gallery/logo.png'); ?>"
              alt="Logo"
            />
          </a>

          <!-- Mobile toggle -->
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Collapsible content -->
          <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- Nav links -->
            <div class="nav-links">
              <a href="<?php echo BASE_URL; ?>#departments">Poliklinik</a>
              <a href="<?php echo BASE_URL; ?>#about">Tentang Kami</a>
              <a href="<?php echo BASE_URL; ?>#our-doctors">Dokter</a>
              <a href="<?php echo BASE_URL; ?>#facilities">Fasilitas</a>
              <a href="<?php echo BASE_URL; ?>#mcu_packages_data">Paket MCU</a>
              <a href="<?php echo BASE_URL; ?>#careers">Karir</a>
              <a href="<?php echo BASE_URL; ?>#virtual-room">Virtual Room</a>
              <a href="<?php echo BASE_URL; ?>#news">Berita</a>
              <a href="<?php echo BASE_URL; ?>#partners">Mitra</a>
              <a href="<?php echo BASE_URL; ?>#appointment">Kontak</a>
            </div>

            <!-- Search box -->
            <div class="search-box">
              <input type="text" placeholder="Search..." />
              <i class="fas fa-search"></i>
            </div>

          </div>

          <!-- Emergency button -->
          <?php
            $whatsapp_link = 'https://wa.me/6285175000374';
            echo '<a class="btn btn-sm btn-danger rounded-pill order-1 order-lg-0 ms-lg-4" href="' . htmlspecialchars($whatsapp_link) . '" target="_blank">Telepon Darurat</a>';
          ?>

        </nav>
      </div>