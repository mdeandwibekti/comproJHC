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
        // Query Insert
        $sql = "INSERT INTO appointments (name, email, phone, message, status, submission_date) VALUES (?, ?, ?, ?, 'new', NOW())";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssss", $name, $email, $phone, $final_message);
            
            if ($stmt->execute()) {
                $success_msg = "Janji temu berhasil dibuat! Tim kami akan segera menghubungi Anda melalui WhatsApp atau Telepon.";
                // Kosongkan pesan setelah sukses
                $default_message = ""; 
            } else {
                $error_msg = "Terjadi kesalahan sistem: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: " . $mysqli->error;
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
            --rs-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--rs-bg);
        }

        .navbar-rs {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 3px solid var(--rs-red);
        }

        .booking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .booking-header {
            background-color: var(--rs-red);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .btn-submit {
            background-color: var(--rs-red);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }
        
        .contact-info i {
            color: var(--rs-red);
            width: 30px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-rs navbar-light fixed-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="index.php">
                <i class="fas fa-heartbeat text-danger me-2"></i> RS JHC Tasikmalaya
            </a>
            <a href="index.php" class="btn btn-outline-danger rounded-pill btn-sm">Kembali ke Home</a>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px; margin-bottom: 50px;">
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
                                    <button type="submit" class="btn btn-primary btn-submit shadow-lg">
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