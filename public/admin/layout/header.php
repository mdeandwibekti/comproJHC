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
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f4f7f6;
        }

        body { 
        background-color: var(--admin-bg) !important; 
        font-family: 'Inter', sans-serif;
        }
        
        /* Navbar dengan Gradasi Linear 90 derajat */
        .navbar-admin {
            background: linear-gradient(90deg, #8a3033 0%, #bd3030 100%) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none !important;
            padding: 0.75rem 0;
        }

        .navbar-admin .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.625rem 0.875rem !important;
        }

        .navbar-admin .nav-link:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }

        /* Text Admin Panel */
        .navbar-admin .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .navbar-admin .navbar-brand:hover {
            transform: scale(1.05);
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Dropdown Menu */
        .navbar-admin .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-radius: 12px;
            margin-top: 0.5rem;
            padding: 0.5rem;
        }

        .navbar-admin .dropdown-item {
            border-radius: 8px;
            padding: 0.625rem 1rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .navbar-admin .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(138, 48, 51, 0.1) 0%, rgba(189, 48, 48, 0.1) 100%);
            color: var(--jhc-red-dark);
            transform: translateX(5px);
        }

        .wrapper { 
           background: #ffffff;
           border-radius: 20px;
           box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
           padding: 40px;
           margin-top: 30px;
           border: 1px solid rgba(0,0,0,0.05);
        }

        .manage-header {
        border-left: 4px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
        }

        .btn-jhc-main {
        background: var(--jhc-gradient) !important;
        border: none !important;
        color: white !important;
        border-radius: 12px !important;
        padding: 10px 24px !important;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: all 0.3s ease;
        }

        .btn-jhc-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.15);
            color: white !important;
            font-weight: 700;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-logout:hover {
            background: white;
            color: var(--jhc-red-dark) !important;
            border-color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        /* Navbar Container */
        .navbar-admin .container {
            max-width: 1320px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-admin .navbar-brand {
                font-size: 1.25rem;
            }

            .navbar-admin .nav-link {
                padding: 0.75rem 1rem !important;
            }

            .btn-logout {
                margin-top: 1rem;
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            .navbar-admin .navbar-brand {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-admin">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-shield-halved me-2"></i>Admin Panel
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item px-2">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-chart-line me-2"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item px-2">
                    <a class="nav-link" href="applicants2.php">
                        <i class="fas fa-users me-2"></i>Pelamar
                    </a>
                </li>
                
                <li class="nav-item px-2">
                    <a class="nav-link" href="banners.php">
                        <i class="fas fa-image me-2"></i>Banner
                    </a>
                </li>
                
                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" href="#" id="dropMCU" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-hospital-user me-2"></i>Layanan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropMCU">
                        <li><a class="dropdown-item" href="mcu_packages.php"><i class="fas fa-file-medical me-2"></i>Paket MCU</a></li>
                        <li><a class="dropdown-item" href="partners.php"><i class="fas fa-handshake me-2"></i>Mitra Perusahaan</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" href="#" id="dropSettings" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-2"></i>Pengaturan
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropSettings">
                        <li><a class="dropdown-item" href="background_settings2.php"><i class="fas fa-panorama me-2"></i>Background Hero</a></li>
                        <li><a class="dropdown-item" href="logo_settings.php"><i class="fas fa-icons me-2"></i>Logo & Favicon</a></li>
                        <li><a class="dropdown-item" href="popup_settings2.php"><i class="fas fa-bullhorn me-2"></i>Popup Promo</a></li>
                    </ul>
                </li>
            </ul>
            
            <a class="btn btn-sm btn-logout rounded-pill px-4 ms-lg-3" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Keluar
            </a>
        </div>
    </div>
</nav>

<!-- Spacer untuk fixed navbar -->
<div style="height: 80px;"></div>

<main class="main" id="top">
    <div class="wrapper">