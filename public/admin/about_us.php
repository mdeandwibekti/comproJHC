<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
$sections = [
    'visi-misi' => 'Visi-Misi',
    'sejarah' => 'Sejarah',
    'salam-direktur' => 'Salam Direktur',
    'budaya-kerja' => 'Budaya Kerja'
];

$section_icons = [
    'visi-misi' => 'fa-bullseye',
    'sejarah' => 'fa-history',
    'salam-direktur' => 'fa-user-tie',
    'budaya-kerja' => 'fa-hand-holding-heart'
];

$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_key = trim($_POST["section_key"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $image_path = trim($_POST["current_image"]);

    // Validasi Dasar
    if (empty($title)) $errors[$section_key]['title'] = "Judul wajib diisi.";
    if (empty($content)) $errors[$section_key]['content'] = "Konten wajib diisi.";

    // Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        
        if (!array_key_exists($ext, $allowed)) {
            $errors[$section_key]['image'] = "Format file tidak valid (Gunakan JPG/PNG).";
        } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $errors[$section_key]['image'] = "Ukuran file maksimal 5MB.";
        } else {
            $new_filename = uniqid() . "_about." . $ext;
            $upload_dir = "../assets/img/gallery/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus file lama jika ada penggantian
                if (!empty($_POST["current_image"]) && file_exists("../" . $_POST["current_image"])) {
                    unlink("../" . $_POST["current_image"]);
                }
                $image_path = "assets/img/gallery/" . $new_filename;
            }
        }
    }

    // Update Database
    if (empty($errors[$section_key])) {
        $sql = "INSERT INTO about_us_sections (section_key, title, content, image_path) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), image_path = VALUES(image_path)";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssss", $section_key, $title, $content, $image_path);
            if ($stmt->execute()) {
                header("location: about_us.php?tab=$section_key&success=1");
                exit();
            }
            $stmt->close();
        }
    }
}

// Ambil semua data bagian
$all_sections_data = [];
$res = $mysqli->query("SELECT * FROM about_us_sections");
while($row = $res->fetch_assoc()) { $all_sections_data[$row['section_key']] = $row; }

// Mulai Output HTML
require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .page-header { 
        background: white; padding: 1.5rem; border-radius: 15px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid var(--jhc-red-dark); 
        margin-bottom: 2rem; 
    }

    .main-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); overflow: hidden; background: #fff; }

    /* Custom Nav Tabs */
    .nav-tabs-jhc { border-bottom: 2px solid #f1f1f1; padding: 0 1.5rem; background: #fafafa; }
    .nav-tabs-jhc .nav-link { 
        border: none; color: #777; padding: 1.2rem 1.5rem; font-weight: 600; 
        transition: 0.3s; position: relative;
    }
    .nav-tabs-jhc .nav-link.active { 
        color: var(--jhc-red-dark); background: transparent; 
    }
    .nav-tabs-jhc .nav-link.active::after {
        content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: var(--jhc-gradient);
    }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 50px; padding: 0.7rem 2.5rem; font-weight: 700; 
        border: none; transition: 0.3s; box-shadow: 0 4px 12px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.9; }

    .img-preview-container {
        border: 2px dashed #ddd; border-radius: 15px; padding: 20px; background: #fdfdfd; transition: 0.3s;
    }
    .img-preview-container img { max-height: 200px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-hospital-user me-2" style="color: var(--jhc-red-dark);"></i> Manage About Us</h3>
            <p class="text-muted mb-0 small">Perbarui informasi profil perusahaan, sejarah, visi & misi secara dinamis.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> Konten berhasil diperbarui!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <ul class="nav nav-tabs nav-tabs-jhc" id="aboutTab" role="tablist">
            <?php $active_tab = $_GET['tab'] ?? 'visi-misi'; ?>
            <?php foreach ($sections as $key => $label): ?>
                <li class="nav-item">
                    <button class="nav-link <?php echo ($active_tab == $key) ? 'active' : ''; ?>" 
                            id="<?php echo $key; ?>-tab" data-bs-toggle="tab" data-bs-target="#tab-<?php echo $key; ?>" type="button">
                        <i class="fas <?php echo $section_icons[$key]; ?> me-2"></i> <?php echo $label; ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="card-body p-4 p-md-5">
            <div class="tab-content" id="aboutTabContent">
                <?php foreach ($sections as $key => $label): 
                    $data = $all_sections_data[$key] ?? ['title' => '', 'content' => '', 'image_path' => ''];
                    $s_errors = $errors[$key] ?? [];
                ?>
                <div class="tab-pane fade <?php echo ($active_tab == $key) ? 'show active' : ''; ?>" id="tab-<?php echo $key; ?>">
                    <form action="about_us.php?tab=<?php echo $key; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="section_key" value="<?php echo $key; ?>">
                        
                        <div class="row g-5">
                            <div class="col-lg-7">
                                <div class="mb-4">
                                    <label class="form-label">Judul Bagian</label>
                                    <input type="text" name="title" class="form-control form-control-lg <?php echo isset($s_errors['title']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($data['title']); ?>" placeholder="Masukkan judul...">
                                    <div class="invalid-feedback"><?php echo $s_errors['title'] ?? ''; ?></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Isi Konten</label>
                                    <textarea name="content" class="form-control <?php echo isset($s_errors['content']) ? 'is-invalid' : ''; ?>" 
                                              rows="10" placeholder="Tuliskan isi informasi di sini..."><?php echo htmlspecialchars($data['content']); ?></textarea>
                                    <div class="invalid-feedback"><?php echo $s_errors['content'] ?? ''; ?></div>
                                    <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Tips: Gunakan paragraf baru agar tampilan di web user tetap rapi.</div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <label class="form-label">Gambar Ilustrasi</label>
                                <div class="img-preview-container text-center mb-3">
                                    <?php if (!empty($data['image_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($data['image_path']); ?>" class="img-fluid mb-3">
                                    <?php else: ?>
                                        <div class="py-5 text-muted">
                                            <i class="fas fa-image fa-4x mb-3 opacity-25"></i><br>Belum ada gambar
                                        </div>
                                    <?php endif; ?>
                                    
                                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($data['image_path']); ?>">
                                    <div class="px-3">
                                        <input type="file" name="image" class="form-control form-control-sm <?php echo isset($s_errors['image']) ? 'is-invalid' : ''; ?>">
                                        <div class="invalid-feedback text-start"><?php echo $s_errors['image'] ?? ''; ?></div>
                                        <div class="form-text text-start x-small mt-2">Format: JPG/PNG. Maks: 5MB.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-end">
                            <button type="submit" class="btn btn-jhc-save shadow">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan <?php echo $label; ?>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>