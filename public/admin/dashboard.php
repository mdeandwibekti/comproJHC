<?php require_once 'layout/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary-red: #D32F2F; /* Merah Rumah Sakit yang Profesional */
        --light-red: #ffebee;
        --dark-text: #333;
        --grey-bg: #f8f9fa;
    }

    body {
        background-color: var(--grey-bg);
    }

    /* Welcome Section */
    .welcome-banner {
        background: white;
        border-left: 5px solid var(--primary-red);
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .welcome-banner h1 {
        font-weight: 300;
        color: var(--dark-text);
        margin-bottom: 0;
    }

    .welcome-banner b {
        color: var(--primary-red);
        font-weight: 700;
    }

    /* Dashboard Cards */
    .dashboard-card {
        border: none;
        border-radius: 10px;
        background: white;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    /* Red accent line on the left */
    .dashboard-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: var(--primary-red);
        transition: width 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(211, 47, 47, 0.15);
    }

    /* Efek hover full block merah (opsional, agar lebih 'bold') */
    .dashboard-card:hover::before {
        width: 100%;
        opacity: 0.05; /* Memberi tint merah tipis pada background saat hover */
    }

    .card-body {
        padding: 1.5rem;
        position: relative;
        z-index: 2;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        background-color: var(--light-red);
        color: var(--primary-red);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover .icon-box {
        background-color: var(--primary-red);
        color: white;
        transform: rotateY(180deg);
    }

    .card-title {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .card-text {
        font-size: 0.9rem;
        color: #6c757d;
        line-height: 1.5;
    }

    .card-link {
        text-decoration: none;
        color: inherit;
    }
    
    .card-link:hover {
        text-decoration: none;
    }
</style>

<div class="container py-5">
    
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div>
            <h1>Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
            <p class="text-muted mb-0 mt-2">Welcome to your Hospital Administration Panel.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-heartbeat fa-3x" style="color: var(--light-red);"></i>
        </div>
    </div>
    
    <h4 class="mb-4 text-secondary font-weight-bold border-bottom pb-2">Manage Content</h4>
    
    <div class="row g-4"> <div class="col-md-6 col-lg-4 mb-4">
            <a href="about_us.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <h5 class="card-title">About Us Page</h5>
                        <p class="card-text">Edit the vision, mission, history, and main content of the hospital profile.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="departments.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-clinic-medical"></i>
                        </div>
                        <h5 class="card-title">Departments</h5>
                        <p class="card-text">Manage medical specialties, polyclinics, and service units.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="doctors.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h5 class="card-title">Doctors</h5>
                        <p class="card-text">Update doctor profiles, schedules, and specialty assignments.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="facilities.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-procedures"></i>
                        </div>
                        <h5 class="card-title">Facilities</h5>
                        <p class="card-text">Manage list of rooms, medical equipment, and patient amenities.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="careers.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h5 class="card-title">Careers</h5>
                        <p class="card-text">Post job openings and manage recruitment information.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="virtual_room.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-vr-cardboard"></i>
                        </div>
                        <h5 class="card-title">Virtual Room</h5>
                        <p class="card-text">Edit the virtual tour content and 360-degree views.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="appointments.php" class="card-link">
                <div class="dashboard-card" style="border-left-color: #ff9800;"> <div class="card-body">
                        <div class="icon-box" style="color: #ff9800; background-color: #fff3e0;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="card-title">Appointments</h5>
                        <p class="card-text">View and manage patient appointment requests.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="news.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h5 class="card-title">News & Articles</h5>
                        <p class="card-text">Publish health articles, hospital news, and announcements.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="news_settings.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h5 class="card-title">News Settings</h5>
                        <p class="card-text">Configure layout and header images for the news section.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="contact_settings.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <h5 class="card-title">Contact Info</h5>
                        <p class="card-text">Update hospital address, emergency numbers, and email.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <a href="logo_settings.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-image"></i>
                        </div>
                        <h5 class="card-title">Logo Settings</h5>
                        <p class="card-text">Update the main hospital branding logos (Header/Footer).</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<?php require_once 'layout/footer.php'; ?>