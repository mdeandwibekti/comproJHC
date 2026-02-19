<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN TETAP (Form Submission & Edit Mode) ---
$job_title = $description = $location = $deadline = "";
$status = 'open';
$job_title_err = $description_err = $location_err = ""; 
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['id']) && !empty($_POST['id'])){ $id = intval($_POST['id']); }
    if (empty(trim($_POST["job_title"]))) { $job_title_err = "Judul pekerjaan wajib diisi."; } else { $job_title = trim($_POST["job_title"]); }
    if (empty(trim($_POST["description"]))) { $description_err = "Deskripsi pekerjaan wajib diisi."; } else { $description = trim($_POST["description"]); }
    if (empty(trim($_POST["location"]))) { $location_err = "Lokasi penempatan wajib diisi."; } else { $location = trim($_POST["location"]); }
    $status = trim($_POST["status"]);
    $deadline = !empty($_POST["deadline"]) ? trim($_POST["deadline"]) : NULL;

    if (empty($job_title_err) && empty($description_err) && empty($location_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO careers (job_title, description, location, status, deadline, post_date) VALUES (?, ?, ?, ?, ?, NOW())";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sssss", $job_title, $description, $location, $status, $deadline);
                if ($stmt->execute()) { header("location: careers.php?msg=saved"); exit(); }
                $stmt->close();
            }
        } else {
            $sql = "UPDATE careers SET job_title = ?, description = ?, location = ?, status = ?, deadline = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sssssi", $job_title, $description, $location, $status, $deadline, $id);
                if ($stmt->execute()) { header("location: careers.php?msg=saved"); exit(); }
                $stmt->close();
            }
        }
    }
} elseif (!empty($id)) {
    $sql = "SELECT job_title, description, location, status, deadline FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $job_title = $row['job_title']; $description = $row['description'];
                $location = $row['location']; $status = $row['status']; $deadline = $row['deadline'];
            }
        }
    }
}

require_once 'layout/header.php';
$page_title_form = empty($id) ? "Tambah Lowongan Baru" : "Edit Lowongan Pekerjaan";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }
    
    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .main-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 45px; border: 1px solid #f1f5f9;
    }

    .page-header-jhc { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }

    .form-label { font-weight: 700; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; }
    
    .form-control, .form-select { 
        border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; 
        transition: 0.3s; background-color: #fcfdfe; font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus { 
        border-color: var(--jhc-red-dark); box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1); 
        background-color: #fff; 
    }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 14px; padding: 14px 40px; font-weight: 800; border: none; 
        transition: 0.3s; box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2);
    }
    .btn-jhc-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <a href="careers.php">Manajemen Karir</a>
        <span class="text-muted opacity-50">/</span> 
        <span class="current"><?= empty($id) ? 'Tambah' : 'Edit'; ?> Lowongan</span>
    </div>

    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;"><?php echo $page_title_form; ?></h2>
                <p class="text-muted small mb-0">Publikasikan posisi karir strategis untuk RS JHC Tasikmalaya.</p>
            </div>
            <a href="careers.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold shadow-sm">
                <i class="fas fa-chevron-left me-2"></i> Kembali
            </a>
        </div>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) . ($id ? '?id='.$id : '') ?>" method="post">
            <?php if(!empty($id)): ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label">Posisi / Judul Pekerjaan <span class="text-danger">*</span></label>
                    <input type="text" name="job_title" class="form-control form-control-lg fw-bold <?= (!empty($job_title_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?= htmlspecialchars($job_title); ?>" placeholder="E.g. Perawat Rawat Inap">
                    <div class="invalid-feedback"><?= $job_title_err; ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status Rekrutmen</label>
                    <select name="status" class="form-select form-select-lg fw-bold">
                        <option value="open" <?= ($status == 'open') ? 'selected' : ''; ?>>BUKA (Aktif)</option>
                        <option value="closed" <?= ($status == 'closed') ? 'selected' : ''; ?>>TUTUP (Draft)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lokasi Penempatan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-2 border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-map-marker-alt text-danger opacity-50"></i></span>
                        <input type="text" name="location" class="form-control border-start-0 <?= (!empty($location_err)) ? 'is-invalid' : ''; ?>" 
                               style="border-radius: 0 12px 12px 0;" value="<?= htmlspecialchars($location); ?>" placeholder="Tasikmalaya, Jawa Barat">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Batas Pendaftaran (Deadline)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-2 border-end-0" style="border-radius: 12px 0 0 12px;"><i class="far fa-calendar-alt text-primary opacity-50"></i></span>
                        <input type="date" name="deadline" class="form-control border-start-0" style="border-radius: 0 12px 12px 0;" value="<?= htmlspecialchars($deadline); ?>">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi & Kualifikasi Lengkap <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control <?= (!empty($description_err)) ? 'is-invalid' : ''; ?>" 
                              rows="12" style="line-height: 1.6;" placeholder="Jabarkan syarat pendidikan, pengalaman, dan tugas utama..."><?= htmlspecialchars($description); ?></textarea>
                    <div class="alert alert-light border mt-3 small text-muted">
                        <i class="fas fa-info-circle me-2 text-primary"></i> Gunakan poin-poin (Enter) agar informasi mudah dibaca oleh calon pelamar di halaman depan.
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-5 text-center text-lg-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-paper-plane me-2"></i> Publikasikan Lowongan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>