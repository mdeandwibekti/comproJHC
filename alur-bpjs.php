<?php 
require_once 'config.php'; 
// Ambil data dari database
$query = $mysqli->query("SELECT bpjs_content FROM page_virtual_room WHERE id=1");
$data = $query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alur Pendaftaran Pasien BPJS - RS JHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --jhc-red: #8a3033; 
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        
        .header-bpjs {
            background: var(--jhc-gradient);
            color: white;
            padding: 60px 0;
            border-radius: 0 0 50px 50px;
        }

        /* Timeline Style */
        .timeline-steps {
            position: relative;
            padding: 20px 0;
        }
        .timeline-step {
            position: relative;
            margin-bottom: 30px;
            padding-left: 80px;
        }
        .timeline-step::before {
            content: "";
            position: absolute;
            left: 39px;
            top: 40px;
            bottom: -40px;
            width: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .timeline-step:last-child::before { display: none; }
        
        .step-number {
            position: absolute;
            left: 20px;
            top: 0;
            width: 40px;
            height: 40px;
            background: var(--jhc-red);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(138,48,51,0.3);
        }

        .step-content {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .requirement-card {
            border-left: 4px solid var(--jhc-red);
            background: #fff0f1;
        }
    </style>
</head>
<body>

<header class="header-bpjs text-center mb-5">
    
    <div class="container">
        <a href="index.php" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
        <h1 class="fw-bold">Alur Pendaftaran BPJS</h1>
        <p class="opacity-75">Panduan langkah demi langkah bagi pasien Jaminan Kesehatan Nasional (JKN).</p>
    </div>
</header>

<main class="container mb-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 requirement-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-file-invoice me-2"></i>Persyaratan Dokumen</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Kartu Peserta BPJS/KIS</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> KTP Asli / Kartu Keluarga</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Surat Rujukan Faskes 1</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Surat Kontrol (Pasien Rawat Jalan)</li>
                </ul>
            </div>
            
            <div class="mt-4 p-3 bg-white rounded-4 shadow-sm text-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b4/BPJS_Kesehatan_logo.svg" alt="BPJS Logo" style="width: 150px; opacity: 0.7;">
            </div>
        </div>

        <div class="col-lg-8">
            
            <div class="timeline-steps">
                
                <div class="timeline-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h6 class="fw-bold text-dark">Fasilitas Kesehatan Tingkat Pertama (FKTP)</h6>
                        <p class="small text-muted mb-0">Pasien mendatangi Puskesmas/Klinik/Dokter Keluarga sesuai tempat terdaftar untuk mendapatkan pemeriksaan awal dan surat rujukan.</p>
                    </div>
                </div>

                <div class="timeline-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h6 class="fw-bold text-dark">Ambil Nomor Antrean RS</h6>
                        <p class="small text-muted mb-0">Pasien mengambil nomor antrean di mesin anjungan mandiri atau melakukan pendaftaran online melalui aplikasi Mobile JKN.</p>
                    </div>
                </div>

                <div class="timeline-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h6 class="fw-bold text-dark">Verifikasi Loket BPJS</h6>
                        <p class="small text-muted mb-0">Pasien menuju loket pendaftaran untuk verifikasi berkas (Rujukan, KTP, Kartu BPJS) dan mendapatkan Surat Eligibilitas Peserta (SEP).</p>
                    </div>
                </div>

                <div class="timeline-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h6 class="fw-bold text-dark">Pemeriksaan Poliklinik</h6>
                        <p class="small text-muted mb-0">Pasien menunggu di depan ruang poliklinik spesialis untuk dilakukan pemeriksaan oleh Dokter Spesialis terkait.</p>
                    </div>
                </div>

                <div class="timeline-step">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h6 class="fw-bold text-dark">Farmasi & Kasir</h6>
                        <p class="small text-muted mb-0">Setelah pemeriksaan, pasien mengambil resep obat di Farmasi. Terakhir, validasi administrasi di Kasir (tanpa biaya jika sesuai prosedur).</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<footer class="text-center py-4 text-muted small">
    &copy; 2026 RS Jantung Jakarta - Memberikan Layanan Jantung Terintegrasi
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>