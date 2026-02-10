<?php
// Cek lokasi file config
if (file_exists("config.php")) {
    require_once "config.php";
} elseif (file_exists("../config.php")) {
    require_once "../config.php";
}

$success_msg = "";
$error_msg = "";

// --- LOGIKA MENANGKAP DATA DARI MODAL ---
$nama_dokter_url = isset($_GET['dokter']) ? $_GET['dokter'] : '';
$spesialis_url = isset($_GET['spesialis']) ? $_GET['spesialis'] : '';

// Jika ada data dokter, buat pesan default
$default_message = "";
if (!empty($nama_dokter_url)) {
    $default_message = "Halo, saya ingin membuat janji temu dengan " . htmlspecialchars($nama_dokter_url) . " (" . htmlspecialchars($spesialis_url) . ").";
}

// --- PROSES FORM SAAT SUBMIT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    $category = isset($_POST['category']) ? $_POST['category'] : 'Umum';
    
    $final_message = "[Kategori: $category] " . $message;

    if (empty($name) || empty($phone) || empty($message)) {
        $error_msg = "Nama, Nomor Telepon, dan Pesan wajib diisi.";
    } else {
        if (isset($mysqli)) {
            $sql = "INSERT INTO appointments (name, email, phone, message, status, submission_date) VALUES (?, ?, ?, ?, 'new', NOW())";
            
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssss", $name, $email, $phone, $final_message);
                
                if ($stmt->execute()) {
                    $success_msg = "Janji temu berhasil dibuat! Tim kami akan segera menghubungi Anda melalui WhatsApp atau Telepon.";
                    $default_message = ""; 
                } else {
                    $error_msg = "Terjadi kesalahan sistem: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_msg = "Database error: " . $mysqli->error;
            }
        } else {
            $success_msg = "Simulasi: Janji temu berhasil dibuat (Database belum terkoneksi).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Janji Temu - RS JHC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --rs-red: #a12a2a;
            --rs-red-light: #bd3030;
            --rs-bg: #f8f9fa;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--rs-bg);
        }

        /* --- NAVBAR STYLE (Sama Persis dengan Index.php) --- */
        .navbar {
            padding: 0.75rem 0;
            min-height: 80px;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 3px solid var(--rs-red);
            transition: var(--transition-smooth);
        }

        .navbar .container {
            max-width: 1320px;
            padding: 0 1.25rem;
        }

        /* Logo Besar & Animasi */
        .navbar-brand img {
            height: 65px; /* Ukuran Besar sesuai Index */
            width: auto;
            transition: var(--transition-smooth);
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
        }

        /* Efek Bergerak saat Disentuh (Hover) */
        .navbar-brand:hover img {
            transform: scale(1.05);
            filter: drop-shadow(0 4px 12px rgba(200, 16, 46, 0.25));
        }

        /* --- BUTTON STYLE --- */
        .btn-janji {
            background: var(--jhc-gradient) !important;
            color: white !important;
            border-radius: 50px;
            padding: 10px 25px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 2px solid transparent !important;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-janji:hover, 
        .btn-janji:active,
        .btn-janji:focus {
            background: white !important;
            color: var(--rs-red) !important;
            border-color: var(--rs-red) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 16, 46, 0.3);
        }

        .btn-janji:hover i {
            color: var(--rs-red) !important;
        }

        /* Tombol Form Full Width */
        .btn-submit-form {
            width: 100%;
            padding: 12px 30px;
        }

        /* --- CARD STYLE --- */
        .booking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .booking-header {
            background: var(--jhc-gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .contact-info i {
            color: var(--rs-red);
            width: 30px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--rs-red-light);
            box-shadow: 0 0 0 0.25rem rgba(189, 48, 48, 0.1);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="index.php">
                <img src="public/assets/img/gallery/JHC_Logo.png" alt="JHC Logo" onerror="this.src='assets/img/gallery/JHC_Logo.png';">
            </a>
            
            <a href="index.php" class="btn btn-janji">
                <i class="fas fa-home me-2"></i> Kembali ke Home
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 130px; margin-bottom: 50px;">
        <div class="row justify-content-center">
            
            <div class="col-lg-4 mb-4 d-none d-lg-block">
                <div class="h-100 p-4 bg-white rounded-3 shadow-sm border-start border-5 border-danger">
                    <h4 class="fw-bold mb-4 text-dark">Informasi Layanan</h4>
                    <p class="text-muted small">Pendaftaran online memudahkan Anda mendapatkan nomor antrean lebih awal tanpa harus menunggu lama di rumah sakit.</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Verifikasi data cepat</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Konfirmasi via WhatsApp</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Berlaku untuk Umum & Asuransi</li>
                    </ul>
                    <hr>
                    <div class="contact-info mt-4">
                        <p class="mb-2"><i class="fas fa-phone-alt"></i> (0265) 123-4567</p>
                        <p class="mb-2"><i class="fas fa-clock"></i> Layanan 24 Jam</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card booking-card">
                    <div class="booking-header">
                        <h3 class="mb-1 fw-bold">Formulir Janji Temu</h3>
                        <p class="mb-0 opacity-75">Lengkapi form di bawah untuk membuat reservasi.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <?php if ($success_msg): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="booking.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small text-muted">Nama Lengkap Pasien</label>
                                    <input type="text" name="name" class="form-control" placeholder="Nama sesuai KTP" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Nomor WhatsApp</label>
                                    <input type="number" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Email (Opsional)</label>
                                    <input type="email" name="email" class="form-control" placeholder="alamat@email.com">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold small text-muted">Poliklinik Tujuan</label>
                                    <select name="category" class="form-select" required>
                                        <option value="" disabled <?php echo empty($nama_dokter_url) ? 'selected' : ''; ?>>Pilih Poliklinik</option>
                                        <option value="Poli Umum">Poli Umum</option>
                                        <option value="Poli Jantung">Poli Jantung</option>
                                        <option value="Poli Gigi">Poli Gigi</option>
                                        <option value="Poli Kandungan">Poli Kandungan</option>
                                        <option value="Poli Anak">Poli Anak</option>
                                        <option value="Poli Penyakit Dalam">Poli Penyakit Dalam</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold small text-muted">Detail Keluhan / Pesan</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="Tuliskan keluhan singkat Anda..." required><?php echo $default_message; ?></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-janji btn-submit-form shadow-lg">
                                        <i class="fas fa-paper-plane me-2"></i> Konfirmasi Janji Temu
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>