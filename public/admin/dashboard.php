<?php require_once 'layout/header.php'; ?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        /* Gradasi merah khas JHC */
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        
        /* Warna Aksen Baru untuk kesan Colorful namun Profesional */
        --accent-blue: #3b82f6;
        --accent-amber: #f59e0b;
        --accent-emerald: #10b981;
        --accent-purple: #8b5cf6;
    }

    body {
        background-color: var(--admin-bg) !important;
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
    }

    /* Welcome Banner dengan Efek Pola Geometris Merah Putih */
    .welcome-banner {
        background: #ffffff;
        border-radius: 24px;
        padding: 3.5rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 3.5rem;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(138, 48, 51, 0.08);
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: var(--jhc-gradient);
        border-radius: 50%;
        opacity: 0.05;
    }

    .welcome-banner h1 {
        font-weight: 400;
        letter-spacing: -0.02em;
        font-size: 2.4rem;
    }

    .welcome-banner b {
        color: var(--jhc-red-dark);
        font-weight: 800;
    }

    /* Section Dividers dengan warna Merah */
    .section-divider {
        font-weight: 700;
        color: var(--jhc-red-dark);
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-size: 0.8rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
    }

    .section-divider::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(to right, rgba(138, 48, 51, 0.2), transparent);
        margin-left: 20px;
    }

    /* Dashboard Cards dengan Hover Berwarna */
    .dashboard-card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: var(--card-shadow);
        height: 100%;
        border-bottom: 4px solid transparent;
    }

    .dashboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.08);
    }

    /* Berbagai Warna Border Bawah Saat Hover */
    .card-red:hover { border-color: var(--jhc-red-dark); }
    .card-blue:hover { border-color: var(--accent-blue); }
    .card-amber:hover { border-color: var(--accent-amber); }
    .card-emerald:hover { border-color: var(--accent-emerald); }
    .card-purple:hover { border-color: var(--accent-purple); }

    .card-body { padding: 2.5rem 2rem; }

    /* Icon Box dengan variasi warna lembut */
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    /* Warna Icon Box Default */
    .ib-red { background: #fff1f2; color: var(--jhc-red-dark); }
    .ib-blue { background: #eff6ff; color: var(--accent-blue); }
    .ib-amber { background: #fffbeb; color: var(--accent-amber); }
    .ib-emerald { background: #ecfdf5; color: var(--accent-emerald); }
    .ib-purple { background: #f5f3ff; color: var(--accent-purple); }

    /* Hover States untuk Icons */
    .dashboard-card:hover .ib-red { background: var(--jhc-gradient); color: white; }
    .dashboard-card:hover .ib-blue { background: var(--accent-blue); color: white; }
    .dashboard-card:hover .ib-amber { background: var(--accent-amber); color: white; }
    .dashboard-card:hover .ib-emerald { background: var(--accent-emerald); color: white; }
    .dashboard-card:hover .ib-purple { background: var(--accent-purple); color: white; }

    .card-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.6rem;
    }

    .card-text {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .card-link { text-decoration: none !important; }

    /* Animasi Masuk */
    .row > div { animation: fadeInUp 0.6s ease backwards; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container py-5">
    
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div>
            <h1>Halo, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
            <p class="mb-0">Akses cepat manajemen konten RS JHC Tasikmalaya.</p>
        </div>
        <div class="d-none d-lg-block">
            <div class="p-4 rounded-4" style="background: rgba(138, 48, 51, 0.03); border: 1px solid rgba(138, 48, 51, 0.1);">
                <i class="fas fa-notes-medical fa-3x text-danger"></i>
            </div>
        </div>
    </div>
    
    <div class="section-divider">Manajemen Konten Utama</div>
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <a href="about_us.php" class="card-link">
                <div class="dashboard-card card-red">
                    <div class="card-body">
                        <div class="icon-box ib-red"><i class="fas fa-hospital-user"></i></div>
                        <h5 class="card-title">Tentang Kami</h5>
                        <p class="card-text">Kelola visi, misi, dan profil utama rumah sakit.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="departments.php" class="card-link">
                <div class="dashboard-card card-blue">
                    <div class="card-body">
                        <div class="icon-box ib-blue"><i class="fas fa-clinic-medical"></i></div>
                        <h5 class="card-title">Layanan & Poli</h5>
                        <p class="card-text">Spesialisasi medis dan unit layanan unggulan.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="doctors.php" class="card-link">
                <div class="dashboard-card card-emerald">
                    <div class="card-body">
                        <div class="icon-box ib-emerald"><i class="fas fa-user-md"></i></div>
                        <h5 class="card-title">Tim Dokter</h5>
                        <p class="card-text">Profil dokter spesialis dan jadwal praktik.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="section-divider">Interaksi & Publikasi</div>
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <a href="appointments.php" class="card-link">
                <div class="dashboard-card card-amber">
                    <div class="card-body">
                        <div class="icon-box ib-amber"><i class="fas fa-calendar-check"></i></div>
                        <h5 class="card-title">Janji Temu</h5>
                        <p class="card-text">Kelola permintaan konsultasi pasien real-time.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="facilities.php" class="card-link">
                <div class="dashboard-card card-purple">
                    <div class="card-body">
                        <div class="icon-box ib-purple"><i class="fas fa-building"></i></div>
                        <h5 class="card-title">Fasilitas</h5>
                        <p class="card-text">Informasi sarana medis dan penunjang.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="news.php" class="card-link">
                <div class="dashboard-card card-emerald">
                    <div class="card-body">
                        <div class="icon-box ib-emerald"><i class="fas fa-newspaper"></i></div>
                        <h5 class="card-title">Berita & Artikel</h5>
                        <p class="card-text">Edukasi kesehatan dan pengumuman terbaru.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="careers.php" class="card-link">
                <div class="dashboard-card card-blue">
                    <div class="card-body">
                        <div class="icon-box ib-blue"><i class="fas fa-briefcase"></i></div>
                        <h5 class="card-title">Karir & Rekrutmen</h5>
                        <p class="card-text">Lowongan kerja dan database pelamar.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="section-divider">Pengaturan Sistem</div>
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="virtual_room.php" class="card-link">
                <div class="dashboard-card card-purple">
                    <div class="card-body">
                        <div class="icon-box ib-purple"><i class="fas fa-vr-cardboard"></i></div>
                        <h5 class="card-title">Virtual Room</h5>
                        <p class="card-text">Edit tur virtual dan video 360 derajat.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="contact_settings.php" class="card-link">
                <div class="dashboard-card card-red">
                    <div class="card-body">
                        <div class="icon-box ib-red"><i class="fas fa-address-book"></i></div>
                        <h5 class="card-title">Info Kontak</h5>
                        <p class="card-text">Alamat, nomor IGD, dan email operasional.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="logo_settings.php" class="card-link">
                <div class="dashboard-card card-amber">
                    <div class="card-body">
                        <div class="icon-box ib-amber"><i class="fas fa-image"></i></div>
                        <h5 class="card-title">Logo & Branding</h5>
                        <p class="card-text">Ganti logo utama dan favicon website.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>