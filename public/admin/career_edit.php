<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN POST ---
$job_title = $description = $location = $deadline = ""; // Ubah end_date jadi deadline
$status = 'open';
$job_title_err = $description_err = $location_err = ""; 
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi Judul, Deskripsi, Lokasi (Tetap sama)
    if (empty(trim($_POST["job_title"]))) { $job_title_err = "Silakan masukkan judul."; } else { $job_title = trim($_POST["job_title"]); }
    if (empty(trim($_POST["description"]))) { $description_err = "Silakan masukkan deskripsi."; } else { $description = trim($_POST["description"]); }
    if (empty(trim($_POST["location"]))) { $location_err = "Silakan masukkan lokasi."; } else { $location = trim($_POST["location"]); }

    $status = trim($_POST["status"]);
    // Ambil nilai deadline dari input 'deadline'
    $deadline = !empty($_POST["deadline"]) ? trim($_POST["deadline"]) : NULL;

    if (empty($job_title_err) && empty($description_err) && empty($location_err)) {
        if (empty($id)) {
            // Gunakan kolom 'deadline' sesuai database
            $sql = "INSERT INTO careers (job_title, description, location, status, deadline) VALUES (?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE careers SET job_title = ?, description = ?, location = ?, status = ?, deadline = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssss", $job_title, $description, $location, $status, $deadline);
            } else {
                $stmt->bind_param("sssssi", $job_title, $description, $location, $status, $deadline, $id);
            }
            
            if ($stmt->execute()) {
                header("location: careers.php?saved=true");
                exit();
            } else {
                $db_err = "Gagal menyimpan: " . $stmt->error;
            }
        }
    }
} elseif (isset($_GET['id'])) {
    // Mode EDIT: Ambil data deadline
    $sql = "SELECT job_title, description, location, status, deadline FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $job_title = $row['job_title'];
                $description = $row['description'];
                $location = $row['location'];
                $status = $row['status'];
                $deadline = $row['deadline']; // Pastikan ini 'deadline'
            }
        }
    }
}

// --- TAMPILAN DIMULAI DISINI ---
require_once 'layout/header.php';
$page_title_form = empty($id) ? "Tambah Lowongan Baru" : "Edit Lowongan Pekerjaan";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    
    /* Card Wrapper bergaya Neumorphism */
    .main-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc { 
        border-left: 5px solid var(--jhc-red-dark); 
        padding-left: 20px;
        margin-bottom: 30px; 
    }

    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.9rem; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 30px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4); }
    
    .form-control:focus, .form-select:focus { 
        border-color: var(--jhc-red-dark); 
        box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); 
    }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1 text-dark fw-bold"><?php echo $page_title_form; ?></h3>
                <p class="text-muted mb-0 small">Lengkapi detail pekerjaan untuk dipublikasikan di halaman karir RS JHC.</p>
            </div>
            <a href="careers.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold">Kembali</a>
        </div>

        <?php if(isset($db_err)): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $db_err; ?></div>
        <?php endif; ?>

        <form action="career_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label text-muted text-uppercase small">Judul Pekerjaan <span class="text-danger">*</span></label>
                    <input type="text" name="job_title" class="form-control form-control-lg <?php echo (!empty($job_title_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($job_title); ?>" placeholder="Contoh: Perawat IGD, Dokter Spesialis...">
                    <div class="invalid-feedback"><?php echo $job_title_err; ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted text-uppercase small">Status Lowongan</label>
                    <select name="status" class="form-select form-select-lg">
                        <option value="open" <?php echo ($status == 'open') ? 'selected' : ''; ?>>BUKA (Aktif)</option>
                        <option value="closed" <?php echo ($status == 'closed') ? 'selected' : ''; ?>>TUTUP (Draft)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted text-uppercase small">Lokasi Penempatan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                        <input type="text" name="location" class="form-control form-control-lg border-start-0 <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($location); ?>" placeholder="Contoh: Tasikmalaya, Jawa Barat">
                        <div class="invalid-feedback"><?php echo $location_err; ?></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted text-uppercase small">Batas Akhir Pendaftaran (Deadline)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" name="deadline" class="form-control form-control-lg border-start-0" 
                            value="<?php echo htmlspecialchars($deadline); ?>">
                    </div>
                    <div class="form-text small text-info"><i class="fas fa-info-circle me-1"></i> Biarkan kosong jika tidak ada batas waktu.</div>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted text-uppercase small">Deskripsi & Kualifikasi Pekerjaan <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" 
                              rows="10" placeholder="Tuliskan detail pekerjaan, syarat pendidikan, pengalaman, dan kualifikasi lainnya..."><?php echo htmlspecialchars($description); ?></textarea>
                    <div class="invalid-feedback"><?php echo $description_err; ?></div>
                    <div class="form-text mt-2 italic text-muted">
                        <i class="fas fa-info-circle me-1"></i> Gunakan baris baru untuk memisahkan poin-poin kualifikasi agar tampilan rapi di website.
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-save me-2"></i> Simpan Lowongan Karir
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>