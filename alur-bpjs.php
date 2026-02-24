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

        /* Button Kembali — style doctors_list.php */
        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
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
        <div class="text-start mb-3">
            <a href="index.php" class="btn-back">
                <i class="fas fa-chevron-left"></i> Kembali
            </a>
        </div>
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

<!-- Floating WhatsApp Button -->
<style>
    .wa-float {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999;
        display: flex;
        align-items: center;
        gap: 10px;
        background: #25D366;
        color: white;
        text-decoration: none;
        padding: 12px 20px 12px 14px;
        border-radius: 50px;
        box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
        font-size: 14px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        animation: wa-bounce 2.5s ease-in-out infinite;
    }
    .wa-float:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(37, 211, 102, 0.5);
        animation: none;
    }
    .wa-float svg {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }
    @keyframes wa-bounce {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-6px); }
    }
</style>

<a href="https://wa.me/6285175000375?text=Halo%20RS%20Jantung%2C%20saya%20ingin%20bertanya%20mengenai%20alur%20pendaftaran%20pasien%20BPJS.%20Mohon%20informasinya%2C%20terima%20kasih%20%F0%9F%99%8F"
   target="_blank"
   rel="noopener noreferrer"
   class="wa-float">
    <!-- WhatsApp Icon SVG -->
    <svg viewBox="0 0 32 32" fill="white" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 2C8.268 2 2 8.268 2 16c0 2.492.678 4.827 1.86 6.83L2 30l7.38-1.832A13.94 13.94 0 0 0 16 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.5a11.44 11.44 0 0 1-5.84-1.603l-.418-.248-4.38 1.087 1.115-4.27-.272-.44A11.457 11.457 0 0 1 4.5 16C4.5 9.596 9.596 4.5 16 4.5S27.5 9.596 27.5 16 22.404 27.5 16 27.5zm6.29-8.618c-.344-.172-2.036-1.004-2.352-1.118-.316-.115-.546-.172-.776.172-.23.344-.89 1.118-1.09 1.348-.2.23-.4.258-.744.086-.344-.172-1.452-.535-2.766-1.707-1.022-.912-1.712-2.038-1.912-2.382-.2-.344-.021-.53.15-.701.155-.154.344-.402.516-.603.172-.2.23-.344.344-.573.115-.23.058-.431-.029-.603-.086-.172-.776-1.87-1.063-2.56-.28-.672-.564-.581-.776-.592l-.66-.011c-.23 0-.603.086-.919.43-.316.344-1.205 1.177-1.205 2.87s1.234 3.328 1.406 3.558c.172.23 2.428 3.707 5.882 5.198.823.355 1.465.567 1.966.726.826.263 1.578.226 2.172.137.662-.099 2.036-.832 2.323-1.635.287-.803.287-1.492.2-1.635-.086-.143-.316-.23-.66-.402z"/>
    </svg>
    Ada yang ingin ditanyakan?
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>