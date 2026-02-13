<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (TETAP SAMA) ---
$name = $specialty = $schedule = $description = $photo_path = "";
$is_featured = 0;
$department_id = 0;
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// 1. Ambil Daftar Departemen untuk Dropdown
$departments = [];
$dept_res = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
if($dept_res) { 
    while($row = $dept_res->fetch_assoc()) { 
        $departments[] = $row; 
    } 
}

// 2. Jika Mode Edit, Ambil Data Dokter dari Database
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, specialty, schedule, photo_path, is_featured, department_id, description FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $specialty, $schedule, $photo_path, $is_featured, $department_id, $description);
            if (!$stmt->fetch()) {
                header("location: doctors.php");
                exit();
            }
        }
        $stmt->close();
    }
}

// 3. Proses Simpan Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id            = $_POST['id'];
    $name          = trim($_POST["name"]);
    $specialty     = trim($_POST["specialty"]);
    $schedule      = trim($_POST["schedule"]);
    $description   = trim($_POST["description"]);
    $department_id = (int)$_POST["department_id"];
    $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
    $photo_path    = $_POST['current_photo'];

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('doc_') . '.' . $file_ext;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_dir . $new_filename)) {
                if (!empty($_POST['current_photo']) && file_exists("../" . $_POST['current_photo'])) {
                    @unlink("../" . $_POST['current_photo']);
                }
                $photo_path = "assets/img/gallery/" . $new_filename;
            }
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO doctors (name, specialty, schedule, department_id, is_featured, photo_path, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE doctors SET name = ?, specialty = ?, schedule = ?, department_id = ?, is_featured = ?, photo_path = ?, description = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("sssiiss", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path, $description);
        } else {
            $stmt->bind_param("sssiissi", $name, $specialty, $schedule, $department_id, $is_featured, $photo_path, $description, $id);
        }
        
        if ($stmt->execute()) {
            header("location: doctors.php?saved=true");
            exit();
        } else {
            $db_err = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
    }

    .form-container {
        background: #ffffff; border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        overflow: hidden; border: 1px solid rgba(0,0,0,0.05);
    }

    .form-header {
        background: #fcfcfc; padding: 2.5rem; border-bottom: 1px solid #f1f1f1;
    }

    .form-body { padding: 3rem; }

    .section-title {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px;
        color: var(--jhc-red); font-weight: 800; display: flex; align-items: center;
        margin-bottom: 1.5rem;
    }
    .section-title::after { content: ""; flex: 1; height: 1px; background: #eee; margin-left: 15px; }

    .form-label { font-weight: 700; color: #444; font-size: 0.9rem; margin-bottom: 0.6rem; }
    
    .form-control, .form-select {
        border-radius: 12px; border: 2px solid #f1f3f5; padding: 0.8rem 1rem;
        transition: 0.3s; background: #fcfcfc;
    }
    .form-control:focus {
        border-color: var(--jhc-red); background: #fff; box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1);
    }

    /* Image Preview Modern */
    .upload-card {
        border: 2px dashed #cbd5e0; border-radius: 20px; padding: 20px;
        background: #f8fafc; transition: 0.3s; position: relative;
    }
    .upload-card:hover { border-color: var(--jhc-red); background: #fff; }

    #previewImg {
        width: 100%; height: 350px; object-fit: cover; border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .btn-save-jhc {
        background: var(--jhc-gradient); color: white !important;
        border-radius: 14px; padding: 1rem 3rem; font-weight: 700;
        border: none; transition: 0.3s; box-shadow: 0 10px 25px rgba(138, 48, 51, 0.3);
    }
    .btn-save-jhc:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(138, 48, 51, 0.4); }

    .featured-box {
        background: #fff5f5; border-radius: 12px; padding: 1rem 1.5rem;
        border: 1px solid #ffebeb;
    }
</style>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4 ms-2">
        <ol class="breadcrumb" style="font-size: 0.85rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="doctors.php" class="text-muted text-decoration-none">Dokter</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Form Profil</li>
        </ol>
    </nav>

    <div class="form-container">
        <div class="form-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold m-0 text-dark"><?= empty($id) ? 'Tambah' : 'Edit'; ?> Profil Dokter</h3>
                <p class="text-muted small mb-0 mt-1">Pastikan informasi gelar dan jadwal praktik sudah sesuai dengan data HRD.</p>
            </div>
            <a href="doctors.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>

        <form action="" method="post" enctype="multipart/form-data" class="form-body">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="current_photo" value="<?= htmlspecialchars($photo_path); ?>">

            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="section-title">Informasi Utama</div>
                    
                    <div class="mb-4">
                        <label class="form-label">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name); ?>" placeholder="Contoh: dr. Nama Dokter, Sp.JP" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Spesialisasi</label>
                            <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($specialty); ?>" placeholder="Contoh: Spesialis Jantung" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Poliklinik</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Pilih Poliklinik</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?= $dept['id']; ?>" <?= ($dept['id'] == $department_id) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Detail & Jadwal</div>

                    <div class="mb-4">
                        <label class="form-label">Jadwal Praktik</label>
                        <textarea name="schedule" class="form-control" rows="2" placeholder="Contoh: Senin - Jumat (08:00 - 14:00)"><?= htmlspecialchars($schedule); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Biografi Singkat / Keahlian</label>
                        <textarea name="description" class="form-control" rows="6" placeholder="Tuliskan pengalaman atau riwayat pendidikan..."><?= htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="featured-box d-flex align-items-center justify-content-between">
                        <div>
                            <label class="fw-bold text-dark d-block mb-0">Status Dokter Unggulan</label>
                            <small class="text-muted">Muncul di halaman depan sebagai rekomendasi utama.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_featured" value="1" <?= ($is_featured == 1) ? 'checked' : ''; ?> style="width: 2.5em; height: 1.25em;">
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="section-title">Foto Profil</div>
                    
                    <div class="upload-card text-center mb-4">
                        <div id="imageArea">
                            <?php if(!empty($photo_path) && file_exists("../" . $photo_path)): ?>
                                <img src="../<?= htmlspecialchars($photo_path); ?>" id="previewImg" alt="Profil">
                            <?php else: ?>
                                <div id="placeholder" class="py-5">
                                    <i class="fas fa-user-md fa-6x text-muted opacity-25 mb-3"></i><br>
                                    <span class="text-muted small">Belum ada foto dokter</span>
                                </div>
                                <img id="previewImg" class="d-none">
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 px-3">
                            <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted d-block mt-2">Rasio 3:4 disarankan. Maks 2MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 pt-4 border-top">
                <button type="submit" class="btn btn-save-jhc">
                    <i class="fas fa-check-circle me-2"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('photoInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('previewImg');
            const placeholder = document.getElementById('placeholder');
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            if (placeholder) placeholder.style.display = 'none';
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>