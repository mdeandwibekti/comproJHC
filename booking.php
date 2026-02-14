<?php
// 1. Cek lokasi file config untuk koneksi database
if (file_exists("config.php")) {
    require_once "config.php";
} elseif (file_exists("../config.php")) {
    require_once "../config.php";
}

$success_msg = "";
$error_msg = "";

// --- 2. LOGIKA MENANGKAP DATA DOKTER DARI URL ---
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$selected_doctor = null;

if ($doctor_id > 0 && isset($mysqli)) {
    // Ambil detail dokter untuk ditampilkan sebagai ringkasan di form
    $stmt_doc = $mysqli->prepare("SELECT name, specialty FROM doctors WHERE id = ?");
    $stmt_doc->bind_param("i", $doctor_id);
    $stmt_doc->execute();
    $selected_doctor = $stmt_doc->get_result()->fetch_assoc();
}

// Buat pesan default jika ada dokter yang dipilih
$default_message = "";
if ($selected_doctor) {
    $default_message = "Halo, saya ingin membuat janji temu dengan " . htmlspecialchars($selected_doctor['name']) . " (" . htmlspecialchars($selected_doctor['specialty']) . ").";
}

// --- 3. PROSES FORM SAAT SUBMIT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $message  = trim($_POST['message']);
    $category = isset($_POST['category']) ? $_POST['category'] : 'Umum';
    
    // Gabungkan info poliklinik ke dalam pesan untuk database
    $final_message = "[Poli: $category] " . $message;

    if (empty($name) || empty($phone) || empty($message)) {
        $error_msg = "Nama, Nomor Telepon, dan Pesan wajib diisi.";
    } else {
        if (isset($mysqli)) {
            $sql = "INSERT INTO appointments (name, email, phone, message, status, submission_date) VALUES (?, ?, ?, ?, 'new', NOW())";
            
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssss", $name, $email, $phone, $final_message);
                
                if ($stmt->execute()) {
                    $success_msg = "Janji temu berhasil dibuat! Tim kami akan segera menghubungi Anda melalui WhatsApp untuk konfirmasi jadwal.";
                    $default_message = ""; // Kosongkan pesan setelah sukses
                } else {
                    $error_msg = "Gagal menyimpan data: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $success_msg = "Mode Simulasi: Pendaftaran berhasil (Database tidak terdeteksi).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Janji Temu - RS JHC Tasikmalaya</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --rs-red: #8a3033;
            --rs-red-light: #bd3030;
            --rs-bg: #f8f9fa;
            --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        }
        
        body { font-family: 'Poppins', sans-serif; background-color: var(--rs-bg); }

        /* Navbar Style */
        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 3px solid var(--rs-red);
        }
        .navbar-brand img { height: 60px; }

        /* Card & UI */
        .booking-card { border: none; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); overflow: hidden; }
        .booking-header { background: var(--jhc-gradient); color: white; padding: 40px 20px; text-align: center; }
        
        .doctor-info-box {
            background: #eef2f7; border-radius: 20px; padding: 20px;
            display: flex; align-items: center; border-left: 5px solid var(--rs-red);
        }

        .btn-janji {
            background: var(--jhc-gradient); color: white !important;
            border-radius: 50px; padding: 12px 30px; font-weight: 600;
            border: none; transition: 0.3s; width: 100%;
        }
        .btn-janji:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(138, 48, 51, 0.3); }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #555; text-transform: uppercase; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand" href="index.php">
                <img src="public/assets/img/gallery/JHC_Logo.png" alt="JHC Logo" onerror="this.src='assets/img/gallery/JHC_Logo.png';">
            </a>
            <a href="index.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                <i class="fas fa-home me-1"></i> Beranda
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 130px; margin-bottom: 80px;">
        <div class="row justify-content-center">
            
            <div class="col-lg-4 mb-4 d-none d-lg-block">
                <div class="h-100 p-4 bg-white rounded-4 shadow-sm">
                    <h5 class="fw-bold mb-4"><i class="fas fa-info-circle text-danger me-2"></i>Layanan Mandiri</h5>
                    <p class="small text-muted">Daftar secara online untuk efisiensi waktu. Pasien umum maupun asuransi dapat menggunakan formulir ini.</p>
                    <hr>
                    <ul class="list-unstyled small">
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Konfirmasi cepat via WhatsApp</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Bebas antre pendaftaran fisik</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Riwayat medis terjamin aman</li>
                    </ul>
                    <div class="mt-5 p-3 rounded-4 bg-light">
                        <p class="mb-0 small fw-bold text-dark">Butuh bantuan cepat?</p>
                        <a href="https://wa.me/6285175000375" class="text-decoration-none small text-danger fw-bold">Chat WhatsApp Admin &raquo;</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card booking-card">
                    <div class="booking-header">
                        <h3 class="fw-bold m-0">Buat Janji Temu</h3>
                        <p class="small opacity-75 mb-0 mt-2">Lengkapi data Anda untuk pendaftaran poliklinik.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <?php if ($success_msg): ?>
                            <div class="alert alert-success border-0 rounded-4 p-3 mb-4">
                                <i class="fas fa-check-circle me-2"></i> <?= $success_msg; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger border-0 rounded-4 p-3 mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_msg; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($selected_doctor): ?>
                            <div class="doctor-info-box mb-4 shadow-sm">
                                <i class="fas fa-user-md fa-2x text-danger me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Pilihan Dokter:</small>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($selected_doctor['name']); ?></span>
                                    <span class="badge bg-danger ms-2" style="font-size: 0.6rem;"><?= htmlspecialchars($selected_doctor['specialty']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="booking.php" method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap Pasien</label>
                                    <input type="text" name="name" class="form-control" placeholder="Input nama sesuai KTP" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nomor WhatsApp</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email (Opsional)</label>
                                    <input type="email" name="email" class="form-control" placeholder="alamat@email.com">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Pilih Poliklinik</label>
                                    <select name="category" class="form-select" required>
                                        <option value="" disabled <?= !$selected_doctor ? 'selected' : ''; ?>>-- Pilih Unit Layanan --</option>
                                        <option value="Jantung" <?= ($selected_doctor && strpos(strtolower($selected_doctor['specialty']), 'jantung') !== false) ? 'selected' : ''; ?>>Poliklinik Jantung</option>
                                        <option value="Penyakit Dalam" <?= ($selected_doctor && strpos(strtolower($selected_doctor['specialty']), 'dalam') !== false) ? 'selected' : ''; ?>>Penyakit Dalam</option>
                                        <option value="Saraf" <?= ($selected_doctor && strpos(strtolower($selected_doctor['specialty']), 'saraf') !== false) ? 'selected' : ''; ?>>Poliklinik Saraf</option>
                                        <option value="Umum">Dokter Umum / MCU</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Keluhan atau Pesan Tambahan</label>
                                    <textarea name="message" class="form-control" rows="5" required placeholder="Jelaskan keluhan Anda atau konfirmasi waktu kunjungan..."><?= $default_message; ?></textarea>
                                </div>

                                <div class="col-12 mt-4 text-center">
                                    <button type="submit" class="btn btn-janji shadow-lg">
                                        <i class="fas fa-check-circle me-2"></i> Kirim Permintaan Janji Temu
                                    </button>
                                    <p class="x-small text-muted mt-3">Dengan mengirim form ini, Anda menyetujui syarat & ketentuan layanan kami.</p>
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