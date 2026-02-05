<?php
// 1. Inisialisasi Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cek Keamanan Login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// 3. Import Konfigurasi Database
require_once __DIR__ . '/../../../config.php';

// 4. Ambil Pengaturan Global dari Database
$settings = [];
$settings_result = $mysqli->query("SELECT setting_key, setting_value FROM settings2");
if ($settings_result) {
    while($setting = $settings_result->fetch_assoc()){
        $settings[$setting['setting_key']] = $setting['setting_value']; 
    }
}

// Tentukan Logo (Gunakan setting dari DB atau default)
$admin_logo = !empty($settings['header_logo_path']) ? $settings['header_logo_path'] : 'assets/img/gallery/68dce989dbee2-jhc3-removebg-preview.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($settings['site_title']) ? htmlspecialchars($settings['site_title']) : 'Admin Panel JHC'; ?></title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../public/<?php echo htmlspecialchars($settings['favicon_path'] ?? 'assets/img/favicons/favicon.ico'); ?>">
    
    <link href="../assets/css/theme.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
        }

        body { padding-top: 90px; background-color: #f8f9fa; }
        
        .navbar-admin {
            background: var(--jhc-gradient) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            border: none !important;
        }

        .navbar-admin .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600;
            transition: 0.3s;
        }

        .navbar-admin .nav-link:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }

        .navbar-admin .navbar-brand img {
            filter: brightness(0) invert(1); /* Memutihkan logo agar kontras */
            height: 50px;
            width: auto;
        }

        .wrapper { 
            width: 95%; 
            max-width: 1200px;
            padding: 30px; 
            margin: 20px auto; 
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .btn-logout {
            background: #fff;
            color: var(--jhc-red-dark) !important;
            font-weight: 700;
            border: none;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #f1f1f1;
            transform: scale(1.05);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top py-2 navbar-admin">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../public/<?php echo htmlspecialchars($admin_logo); ?>" alt="Logo JHC">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto pt-2 pt-lg-0">
                <li class="nav-item px-2"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-1"></i> Dashboard</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="applicants2.php"><i class="fas fa-users me-1"></i> Pelamar</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="banners2.php"><i class="fas fa-image me-1"></i> Banner</a></li>
                
                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" href="#" id="dropMCU" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-hospital-user me-1"></i> Layanan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropMCU">
                        <li><a class="dropdown-item" href="mcu_packages.php">Paket MCU</a></li>
                        <li><a class="dropdown-item" href="partners.php">Mitra Perusahaan</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" href="#" id="dropSettings" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropSettings">
                        <li><a class="dropdown-item" href="background_settings2.php">Background Hero</a></li>
                        <li><a class="dropdown-item" href="logo_settings.php">Logo & Favicon</a></li>
                        <li><a class="dropdown-item" href="popup_settings2.php">Popup Promo</a></li>
                    </ul>
                </li>
            </ul>
            
            <a class="btn btn-sm btn-logout rounded-pill px-4 ms-lg-3" href="logout.php">
                <i class="fas fa-sign-out-alt me-1"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<main class="main" id="top">
    <div class="wrapper">