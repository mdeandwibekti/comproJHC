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
    <title><?php echo $page_title ?? 'JHC | Landing, Responsive &amp; Business Templatee'; ?></title>


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
    <!-- <link href="/comprojhc/public/assets/vendors/pannellum/pannellum.min.css" rel="stylesheet" /> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <style>
      .banner-image {
        transition: opacity 1s ease-in-out;
        position: absolute; /* Ensure images stack correctly for fade */
        top: 0;
        left: 0;
      }
      .icon-hover-effect .deparment-icon-hover {
        display: none;
      }

      .icon-hover-effect:hover .deparment-icon {
        display: none;
      }

      .icon-hover-effect:hover .deparment-icon-hover {
        display: block;
      }
      .price-sticker {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #800000; /* Primary color, adjust as needed */
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      }
      /* Style for Pannellum viewer */
      #panorama {
        width: 100%;
        height: 500px; /* Adjust height as needed */
      }

    /* Custom Navbar Style */
    @media (min-width: 1200px) {
        body { padding-top: 100px; } /* Add padding to body to prevent content from hiding behind the new navbar position */
        .public-navbar-container {
            position: fixed; /* Changed from absolute to fixed */
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1030; /* Ensure it's above other content */
        }
        .main-navbar {
            background: linear-gradient(90deg,#b63d3f,#d8584a);
            margin: 10px auto 0 auto;
            padding: 6px 18px;
            width: 90%;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .main-navbar .navbar-brand img {
            filter: brightness(0) invert(1); /* Make logo white */
        }
        .main-navbar .nav-links {
            display: flex;
            gap: 25px; /* Slightly reduced gap */
            color: white;
            font-weight: 500;
            margin: 0 auto; /* Center the links */
        }
        .main-navbar .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }
        .search-box {
            background: white;
            display: flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            gap: 5px;
        }
        .search-box input {
            border: none;
            outline: none;
            background: transparent;
        }
        .search-box i {
            cursor: pointer;
            font-size: 14px;
            color: #999;
        }
        /* Hide bootstrap elements that are not part of the new design on desktop */
        .main-navbar .navbar-toggler, .main-navbar .btn-danger { display: none; }
        .main-navbar .navbar-collapse { display: contents; }
    }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/> <!-- Font Awesome for search icon -->
  </head>


  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <div class="public-navbar-container">
        <nav class="navbar navbar-expand-xl main-navbar">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><img src="/comprojhc/public/<?php echo htmlspecialchars($settings['header_logo_path'] ?? 'assets/img/gallery/logo.png'); ?>" width="118" alt="logo" /></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"> </span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
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
                <div class="search-box">
                    <input type="text" placeholder="Search..." />
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <?php
            $whatsapp_link = 'https://wa.me/6285175000374';
            echo '<a class="btn btn-sm btn-danger rounded-pill order-1 order-lg-0 ms-lg-4" href="' . htmlspecialchars($whatsapp_link) . '" target="_blank">Telepon Darurat</a>';
            ?>
        </nav>
      </div>
