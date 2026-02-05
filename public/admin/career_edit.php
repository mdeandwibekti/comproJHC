<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN POST (Harus SEBELUM require layout/header.php) ---
$job_title = $description = $location = $end_date = "";
$status = 'open';
$job_title_err = $description_err = $location_err = $end_date_err = "";
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi Judul Pekerjaan
    if (empty(trim($_POST["job_title"]))) {
        $job_title_err = "Silakan masukkan judul pekerjaan.";
    } else {
        $job_title = trim($_POST["job_title"]);
    }

    // Validasi Deskripsi
    if (empty(trim($_POST["description"]))) {
        $description_err = "Silakan masukkan deskripsi pekerjaan.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validasi Lokasi
    if (empty(trim($_POST["location"]))) {
        $location_err = "Silakan masukkan lokasi.";
    } else {
        $location = trim($_POST["location"]);
    }

    $status = trim($_POST["status"]);
    $end_date = !empty($_POST["end_date"]) ? trim($_POST["end_date"]) : NULL;

    // Cek jika tidak ada error sebelum simpan ke database
    if (empty($job_title_err) && empty($description_err) && empty($location_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO careers (job_title, description, location, status, end_date) VALUES (?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE careers SET job_title = ?, description = ?, location = ?, status = ?, end_date = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssss", $job_title, $description, $location, $status, $end_date);
            } else {
                $stmt->bind_param("sssssi", $job_title, $description, $location, $status, $end_date, $id);
            }
            
            if ($stmt->execute()) {
                header("location: careers.php?saved=true");
                exit();
            } else {
                $db_err = "Terjadi kesalahan sistem: " . $stmt->error;
            }
            $stmt->close();
        }
    }
} elseif (isset($_GET['id'])) {
    // Ambil data untuk mode EDIT
    $sql = "SELECT job_title, description, location, status, end_date FROM careers WHERE id = ?";
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
                $end_date = $row['end_date'];
            }
        }
        $stmt->close();
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
    .page-header { 
        background: white; padding: 1.5rem; border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--jhc-red-dark); 
        margin-bottom: 2rem; 
    }
    .main-card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.9rem; }
    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 50px; padding: 0.6rem 2.5rem; font-weight: 600; 
        border: none; transition: 0.3s; 
    }
    .btn-jhc-save:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(138, 48, 51, 0.3); }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-briefcase me-2" style="color: var(--jhc-red-dark);"></i> <?php echo $page_title_form; ?></h3>
            <p class="text-muted mb-0 small">Lengkapi detail pekerjaan untuk dipublikasikan di halaman karir.</p>
        </div>
        <a href="careers.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold">Kembali</a>
    </div>

    <div class="card main-card">
        <div class="card-body p-4 p-md-5">
            <?php if(isset($db_err)): ?>
                <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $db_err; ?></div>
            <?php endif; ?>

            <form action="career_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label">Judul Pekerjaan <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" class="form-control form-control-lg <?php echo (!empty($job_title_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($job_title); ?>" placeholder="Contoh: Perawat IGD, Dokter Spesialis...">
                        <div class="invalid-feedback"><?php echo $job_title_err; ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status Lowongan</label>
                        <select name="status" class="form-select form-select-lg">
                            <option value="open" <?php echo ($status == 'open') ? 'selected' : ''; ?>>BUKA (Aktif)</option>
                            <option value="closed" <?php echo ($status == 'closed') ? 'selected' : ''; ?>>TUTUP (Draft)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lokasi Penempatan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                            <input type="text" name="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($location); ?>" placeholder="Contoh: Tasikmalaya, Jawa Barat">
                        </div>
                        <div class="text-danger small mt-1"><?php echo $location_err; ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Batas Akhir Pendaftaran (Opsional)</label>
                        <input type="date" name="end_date" class="form-control <?php echo (!empty($end_date_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($end_date); ?>">
                        <div class="invalid-feedback"><?php echo $end_date_err; ?></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi & Kualifikasi Pekerjaan <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" 
                                  rows="12" placeholder="Tuliskan detail pekerjaan, syarat pendidikan, pengalaman, dan kualifikasi lainnya..."><?php echo htmlspecialchars($description); ?></textarea>
                        <div class="invalid-feedback"><?php echo $description_err; ?></div>
                        <div class="form-text mt-2 italic">Gunakan baris baru untuk memisahkan poin-poin kualifikasi agar rapi di halaman depan.</div>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 text-end">
                    <button type="submit" class="btn btn-jhc-save shadow">
                        <i class="fas fa-save me-2"></i> Simpan Lowongan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>