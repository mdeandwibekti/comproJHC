<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once __DIR__ . '/../../../config.php';

// Fetch all settings
$settings = [];
$settings_result = $mysqli->query("SELECT setting_key, setting_value FROM settings2");
while($setting = $settings_result->fetch_assoc()){
    $settings[$setting['setting_key']] = $setting['setting_value']; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo rtrim(BASE_URL, '/'); ?>/public/<?php echo htmlspecialchars($settings['favicon_path'] ?? 'assets/img/favicons/favicon.ico'); ?>">
    <link href="../assets/css/theme.css" rel="stylesheet" />
    <style>
        body { padding-top: 70px; }
        .wrapper{ width: 80%; padding: 20px; margin: 20px auto; }
        .card-link { text-decoration: none; color: inherit; }
        .card-link .card:hover { transform: translateY(-5px); box-shadow: 0 4px 20px rgba(0,0,0,.1); transition: all .3s ease; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 bg-light border-bottom">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><img src="../assets/img/gallery/logo.png" width="118" alt="logo" /></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarSupportedContent" aria-controls="adminNavbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"> </span></button>
        <div class="collapse navbar-collapse" id="adminNavbarSupportedContent">
            <ul class="navbar-nav ms-auto pt-2 pt-lg-0 font-base">
                <li class="nav-item px-2"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="applicants2.php">Applicants</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="banners2.php">Manage Banners</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="mcu_packages.php">Manage MCU Packages</a></li>
                <li class="nav-item px-2"><a class="nav-link" href="partners.php">Manage Partners</a></li>
                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Settings
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="background_settings2.php">Background Settings</a></li>
                        <li><a class="dropdown-item" href="logo_settings.php">Logo Settings</a></li>
                        <li><a class="dropdown-item" href="popup_settings2.php">Popup Settings</a></li>
                    </ul>
                </li>
            </ul>
            <a class="btn btn-sm btn-outline-danger rounded-pill order-1 order-lg-0 ms-lg-4" href="logout.php">Sign Out</a>
        </div>
    </div>
</nav>

<main class="main" id="top">
    <div class="wrapper">
