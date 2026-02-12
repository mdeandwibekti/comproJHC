<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
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
                // Jika ID tidak ditemukan, arahkan kembali
                header("location: doctors.php");
                exit();
            }
        }
        $stmt->close();
    }
}

// 3. Proses Simpan Data (INSERT atau UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id            = $_POST['id'];
    $name          = trim($_POST["name"]);
    $specialty     = trim($_POST["specialty"]);
    $schedule      = trim($_POST["schedule"]);
    $description   = trim($_POST["description"]);
    $department_id = (int)$_POST["department_id"];
    $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
    $photo_path    = $_POST['current_photo'];

    // Handle Upload Foto Baru
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('doc_') . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus foto lama jika ada dan bukan foto default
                if (!empty($_POST['current_photo']) && file_exists("../" . $_POST['current_photo'])) {
                    unlink("../" . $_POST['current_photo']);
                }
                $photo_path = "assets/img/gallery/" . $new_filename;
            }
        }
    }

    // Eksekusi Database
    if (empty($id)) {
        // Mode Tambah Baru
        $sql = "INSERT INTO doctors (name, specialty, schedule, department_id, is_featured, photo_path, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    } else {
        // Mode Update
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
$page_title_text = empty($id) ? "Tambah Dokter Baru" : "Edit Profil Dokter";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .main-wrapper {
        background: #ffffff; border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px; margin-bottom: 30px;
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
        padding: 20px; text-align: center; min-height: 200px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .img-preview-box img { max-height: 250px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Kelola informasi dokter RS JHC untuk kategori poliklinik yang sesuai.</p>
            </div>
            <a href="doctors.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Batal & Kembali</a>
        </div>

        <?php if(isset($db_err)): ?>
            <div class="alert alert-danger border-0 shadow-sm"><?php echo $db_err; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($name); ?>" placeholder="Contoh: dr. Asep Sopandia A S, Sp.JP(K)" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Spesialisasi Profesional</label>
                            <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($specialty); ?>" placeholder="Contoh: Spesialis Jantung Dewasa" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Hubungkan ke Poliklinik <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">-- Pilih Poliklinik --</option>
                                <?php
                                $depts = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
                                while($d = $depts->fetch_assoc()):
                                    $selected = ($d['id'] == $department_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $d['id']; ?>" <?= $selected; ?>><?= htmlspecialchars($d['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted small">Jadwal Praktik Singkat</label>
                            <textarea name="schedule" class="form-control" rows="2" placeholder="Contoh: Senin, Rabu, Jumat (08:00 - 12:00)"><?php echo htmlspecialchars($schedule); ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted small">Biografi & Pengalaman Dokter</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Tuliskan riwayat pendidikan atau keahlian dokter..."><?php echo htmlspecialchars($description); ?></textarea>
                            <small class="text-muted">Informasi ini akan muncul saat pasien mengklik tombol "Lihat Profil" di halaman depan.</small>
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="form-check form-switch p-0 d-flex align-items-center">
                                <label class="form-check-label me-4 fw-bold text-dark" for="featuredCheck">Tampilkan sebagai Dokter Unggulan?</label>
                                <input class="form-check-input ms-0 shadow-none" type="checkbox" role="switch" name="is_featured" value="1" id="featuredCheck" <?php echo ($is_featured == 1) ? 'checked' : ''; ?> style="width: 2.8em; height: 1.4em; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label text-muted small">Foto Profil Dokter</label>
                    <div class="img-preview-box mb-3 shadow-sm">
                        <?php if(!empty($photo_path) && file_exists("../" . $photo_path)): ?>
                            <img src="../<?php echo htmlspecialchars($photo_path); ?>" alt="Doctor Photo" id="previewImg" class="img-fluid">
                        <?php else: ?>
                            <div id="placeholder" class="py-5 text-muted text-center">
                                <i class="fas fa-user-md fa-5x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada foto terpilih</span>
                            </div>
                            <img id="previewImg" class="img-fluid d-none">
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo_path); ?>">
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Unggah/Ganti Foto (JPG, PNG, WEBP):</label>
                        <input type="file" name="photo" id="photoInput" class="form-control form-control-sm shadow-sm" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview gambar instan sebelum upload
    document.getElementById('photoInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('previewImg');
            const placeholder = document.getElementById('placeholder');
            
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>