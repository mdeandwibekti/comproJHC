<?php require_once 'layout/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        /* Warna Gradasi JHC Sesuai Gambar Referensi */
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f4f7f6;
    }

    body {
        background-color: var(--admin-bg) !important;
    }

    /* Welcome Section bergaya Neumorphism */
    .welcome-banner {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 6px;
        background: var(--jhc-gradient);
    }

    .welcome-banner h1 {
        font-weight: 300;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .welcome-banner b {
        color: var(--jhc-red-dark);
        font-weight: 800;
    }

    /* Dashboard Grid Cards */
    .dashboard-card {
        border: none;
        border-radius: 18px;
        background: #ffffff;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        height: 100%;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .dashboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(138, 48, 51, 0.1);
        border-color: rgba(138, 48, 51, 0.2);
    }

    .card-body {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    /* Icon Box dengan Gradasi Baru */
    .icon-box {
        width: 70px;
        height: 70px;
        background: #f8f9fa;
        color: var(--jhc-red-dark);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.5rem auto;
        transition: all 0.4s ease;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
    }

    .dashboard-card:hover .icon-box {
        background: var(--jhc-gradient);
        color: white;
        transform: rotateY(10deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.3);
    }

    .card-title {
        font-weight: 700;
        color: #34495e;
        margin-bottom: 0.75rem;
        font-size: 1.1rem;
    }

    .card-text {
        font-size: 0.85rem;
        color: #7f8c8d;
        line-height: 1.6;
    }

    .section-divider {
        font-weight: 800;
        color: #95a5a6;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.8rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
    }

    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #eee;
        margin-left: 15px;
    }

    .card-link {
        text-decoration: none !important;
        display: block;
        height: 100%;
    }
</style>

<div class="container py-5">
    
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div>
            <h1>Halo, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
            <p class="text-muted mb-0">Selamat datang kembali di Panel Administrasi RS JHC Tasikmalaya.</p>
        </div>
        <div class="d-none d-md-block opacity-25">
            <i class="fas fa-heartbeat fa-4x text-danger"></i>
        </div>
    </div>
    
    <div class="section-divider">Manajemen Konten Utama</div>
    
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="about_us.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-hospital-user"></i></div>
                        <h5 class="card-title">Tentang Kami</h5>
                        <p class="card-text">Kelola visi, misi, sejarah, dan profil utama rumah sakit secara dinamis.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="departments.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-clinic-medical"></i></div>
                        <h5 class="card-title">Layanan & Poli</h5>
                        <p class="card-text">Atur spesialisasi medis, poliklinik, dan unit layanan unggulan JHC.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="doctors.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-user-md"></i></div>
                        <h5 class="card-title">Tim Dokter</h5>
                        <p class="card-text">Update profil dokter spesialis, jadwal praktik, dan keahlian medis.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="section-divider mt-5">Interaksi & Publikasi</div>

        <div class="col-md-6 col-lg-4">
            <a href="appointments.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box" style="color: #f39c12;"><i class="fas fa-calendar-check"></i></div>
                        <h5 class="card-title">Janji Temu</h5>
                        <p class="card-text">Pantau dan kelola permintaan konsultasi dari calon pasien secara real-time.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="news.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-newspaper"></i></div>
                        <h5 class="card-title">Berita & Artikel</h5>
                        <p class="card-text">Publikasikan edukasi kesehatan, berita RS, dan pengumuman terbaru.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="careers.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-briefcase"></i></div>
                        <h5 class="card-title">Karir & Rekrutmen</h5>
                        <p class="card-text">Buka lowongan pekerjaan baru dan kelola data pelamar yang masuk.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="section-divider mt-5">Pengaturan Sistem</div>

        <div class="col-md-6 col-lg-4">
            <a href="virtual_room.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-vr-cardboard"></i></div>
                        <h5 class="card-title">Virtual Room</h5>
                        <p class="card-text">Edit konten tur virtual dan tampilan video 360 derajat fasilitas RS.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="contact_settings.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-address-book"></i></div>
                        <h5 class="card-title">Info Kontak</h5>
                        <p class="card-text">Update alamat, nomor darurat IGD, dan email resmi operasional.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="logo_settings.php" class="card-link">
                <div class="dashboard-card">
                    <div class="card-body">
                        <div class="icon-box"><i class="fas fa-image"></i></div>
                        <h5 class="card-title">Logo & Branding</h5>
                        <p class="card-text">Ganti logo utama (Header/Footer) dan favicon website rumah sakit.</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<?php require_once 'layout/footer.php'; ?>