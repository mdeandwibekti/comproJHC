<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
$name = $specialty = $schedule = $photo_path = "";
$is_featured = 0;
$department_id = 0;
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// 1. Ambil Departemen untuk Dropdown
$departments = [];
$dept_res = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
if($dept_res) { while($row = $dept_res->fetch_assoc()) { $departments[] = $row; } }

// 2. Jika Mode Edit, Ambil Data Dokter
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, specialty, schedule, photo_path, is_featured, department_id FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $specialty, $schedule, $photo_path, $is_featured, $department_id);
            $stmt->fetch();
        }
        $stmt->close();
    }
}

// 3. Proses Simpan Data (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $specialty = trim($_POST["specialty"]);
    $schedule = trim($_POST["schedule"]);
    $department_id = (int)$_POST["department_id"];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $photo_path = $_POST['current_photo'];

    // Handle Upload Foto Baru
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '-doc.' . $file_ext;
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_dir . $new_filename)) {
            // Hapus foto lama jika ada penggantian
            if (!empty($_POST['current_photo']) && file_exists("../" . $_POST['current_photo'])) {
                unlink("../" . $_POST['current_photo']);
            }
            $photo_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO doctors (name, specialty, schedule, department_id, is_featured, photo_path) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE doctors SET name = ?, specialty = ?, schedule = ?, department_id = ?, is_featured = ?, photo_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("sssiis", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path);
        } else {
            $stmt->bind_param("sssiisi", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path, $id);
        }
        
        if ($stmt->execute()) {
            header("location: doctors.php?saved=true");
            exit();
        } else {
            $db_err = "Terjadi kesalahan database: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Mulai Tampilan
require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Dokter Baru" : "Edit Profil Dokter";
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

    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 30px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 220px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Kelola informasi profil, spesialisasi, dan jadwal praktik dokter JHC.</p>
            </div>
            <a href="doctors.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="doctor_edit.php<?php echo $id ? '?id='.$id : ''; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($name); ?>" placeholder="Contoh: dr. Asep Sopandia A S, Sp.JP(K)" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Spesialisasi</label>
                            <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($specialty); ?>" placeholder="Contoh: Spesialis Jantung Dewasa" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Departemen / Poli</label>
                            <select name="department_id" class="form-select">
                                <option value="0">Pilih Departemen</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted small">Jadwal Praktik</label>
                            <textarea name="schedule" class="form-control" rows="4" placeholder="Contoh: Senin - Jumat: 09.00 - 14.00"><?php echo htmlspecialchars($schedule); ?></textarea>
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="form-check form-switch p-0 d-flex align-items-center">
                                <label class="form-check-label me-4 fw-bold text-dark" for="featuredCheck">Tampilkan di Beranda?</label>
                                <input class="form-check-input ms-0 shadow-none" type="checkbox" role="switch" name="is_featured" value="1" id="featuredCheck" <?php echo ($is_featured == 1) ? 'checked' : ''; ?> style="width: 2.8em; height: 1.4em;">
                            </div>
                            <small class="text-muted italic mt-2 d-block">Jika aktif, dokter akan muncul di bagian "Dokter Kami" di halaman depan.</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label text-muted small">Foto Profil Dokter</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($photo_path)): ?>
                            <img src="../<?php echo htmlspecialchars($photo_path); ?>" alt="Doctor Photo" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-user-circle fa-5x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada foto diunggah</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo_path); ?>">
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2">Pilih File Baru (Rekomendasi: 500x500px):</label>
                        <input type="file" name="photo" class="form-control form-control-sm shadow-sm">
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan Dokter
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>