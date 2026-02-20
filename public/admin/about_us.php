<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Tetap Sama) ---
$sections = [
    'visi-misi' => 'Visi-Misi',
    'sejarah' => 'Sejarah',
    'salam-direktur' => 'Salam Direktur'
];

$section_icons = [
    'visi-misi' => 'fa-bullseye',
    'sejarah' => 'fa-history',
    'salam-direktur' => 'fa-user-tie'
];

$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_key = trim($_POST["section_key"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $image_path = trim($_POST["current_image"]);

    if (empty($title)) $errors[$section_key]['title'] = "Judul wajib diisi.";
    if (empty($content)) $errors[$section_key]['content'] = "Konten wajib diisi.";

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
                if (!empty($_POST["current_image"]) && file_exists("../" . $_POST["current_image"])) {
                    unlink("../" . $_POST["current_image"]);
                }
                $image_path = "assets/img/gallery/" . $new_filename;
            }
        }
    }

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
while($row = $res->fetch_assoc()) { 
    $all_sections_data[$row['section_key']] = $row; 
}

// Tentukan tab aktif untuk logika Sidebar
$active_tab = $_GET['tab'] ?? 'visi-misi';

// Ambil waktu update dari database untuk tab yang sedang dibuka
$display_last_update = isset($all_sections_data[$active_tab]['updated_at']) 
    ? date('d M Y, H:i', strtotime($all_sections_data[$active_tab]['updated_at'])) 
    : 'Belum ada data';

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f0f2f5;
    }

    body { background-color: var(--admin-bg); font-family: 'Inter', sans-serif; }

    .page-header { background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05); }
    .header-icon-box { width: 64px; height: 64px; background: var(--jhc-gradient); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 10px 20px rgba(138, 48, 51, 0.2); }
    .main-card { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04); background: #fff; overflow: hidden; }
    .nav-tabs-container { background: #fdfdfd; border-right: 1px solid #f1f3f5; }
    .nav-tabs-jhc { border-bottom: none; padding: 1.5rem; flex-direction: column; }
    .nav-tabs-jhc .nav-link { border: none !important; color: #636e72; padding: 1rem 1.5rem; font-weight: 600; transition: 0.3s; border-radius: 12px; margin-bottom: 0.5rem; display: flex; align-items: center; }
    .nav-tabs-jhc .nav-link.active { color: white; background: var(--jhc-gradient); box-shadow: 0 8px 15px rgba(138, 48, 51, 0.2); }
    .form-control { border: 1.5px solid #e9ecef; border-radius: 12px; padding: 0.75rem 1rem; background: #f8f9fa; }
    .img-preview-card { border: 2px dashed #cbd5e0; border-radius: 20px; padding: 20px; background: #f8fafc; }
    .preview-img-tag { width: 100%; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .btn-jhc-save { background: var(--jhc-gradient); color: white !important; border-radius: 14px; padding: 0.8rem 2.5rem; font-weight: 700; border: none; transition: 0.3s; }
</style>

<div class="container py-4">
    <div class="page-header d-flex align-items-center">
        <div class="header-icon-box me-4 d-none d-md-flex"><i class="fas fa-building fa-lg"></i></div>
        <div>
            <h2 class="mb-1 fw-800">About Us Content</h2>
            <p class="text-muted mb-0">Kelola narasi visi, misi, dan sejarah RS JHC secara real-time.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center">
            <i class="fas fa-check-circle me-3 fa-lg"></i>
            <div><strong>Pembaruan Berhasil!</strong> Data pada tab <b><?php echo $sections[$active_tab]; ?></b> telah diperbarui.</div>
        </div>
    <?php endif; ?>

    <div class="main-card">
        <div class="row g-0">
            <div class="col-lg-3 nav-tabs-container">
                <ul class="nav nav-tabs nav-tabs-jhc" id="aboutTab" role="tablist">
                    <?php foreach ($sections as $key => $label): ?>
                        <li class="nav-item">
                            <button class="nav-link w-100 <?php echo ($active_tab == $key) ? 'active' : ''; ?>" 
                                    onclick="window.location.href='about_us.php?tab=<?php echo $key; ?>'"
                                    id="<?php echo $key; ?>-tab" data-bs-toggle="tab" data-bs-target="#tab-<?php echo $key; ?>" type="button">
                                <i class="fas <?php echo $section_icons[$key]; ?> me-3"></i> 
                                <span><?php echo $label; ?></span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="p-4 d-none d-lg-block">
                    <div class="card bg-light border-0 rounded-4 p-3 shadow-sm">
                        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Update Terakhir (<?php echo $sections[$active_tab]; ?>):</small>
                        <span class="fw-bold small text-dark"><i class="far fa-clock me-1 text-danger"></i> <?php echo $display_last_update; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card-body p-4 p-md-5">
                    <div class="tab-content" id="aboutTabContent">
                        <?php foreach ($sections as $key => $label): 
                            $data = $all_sections_data[$key] ?? ['title' => '', 'content' => '', 'image_path' => ''];
                            $s_errors = $errors[$key] ?? [];
                        ?>
                        <div class="tab-pane fade <?php echo ($active_tab == $key) ? 'show active' : ''; ?>" id="tab-<?php echo $key; ?>">
                            <form action="about_us.php?tab=<?php echo $key; ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="section_key" value="<?php echo $key; ?>">
                                
                                <div class="mb-4">
                                    <label class="form-label">Judul Utama Halaman</label>
                                    <input type="text" name="title" class="form-control form-control-lg <?php echo isset($s_errors['title']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($data['title']); ?>">
                                    <div class="invalid-feedback"><?php echo $s_errors['title'] ?? ''; ?></div>
                                </div>
                                
                                <div class="mb-5">
                                    <label class="form-label">Deskripsi / Narasi Lengkap</label>
                                    <textarea name="content" class="form-control <?php echo isset($s_errors['content']) ? 'is-invalid' : ''; ?>" 
                                              rows="10"><?php echo htmlspecialchars($data['content']); ?></textarea>
                                    <div class="invalid-feedback"><?php echo $s_errors['content'] ?? ''; ?></div>
                                </div>

                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div class="img-preview-card text-center">
                                            <?php if (!empty($data['image_path'])): ?>
                                                <img src="../<?php echo htmlspecialchars($data['image_path']); ?>" class="preview-img-tag mb-3">
                                            <?php else: ?>
                                                <div class="py-5 opacity-50"><i class="fas fa-image fa-3x"></i></div>
                                            <?php endif; ?>
                                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($data['image_path']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="p-3 bg-light rounded-4 border">
                                            <label class="form-label">Ganti Gambar</label>
                                            <input type="file" name="image" class="form-control mb-2 <?php echo isset($s_errors['image']) ? 'is-invalid' : ''; ?>">
                                            <small class="text-muted">Maks: 5MB.</small>
                                            <div class="invalid-feedback d-block"><?php echo $s_errors['image'] ?? ''; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-top">
                                    <button type="submit" class="btn btn-jhc-save float-end">
                                        <i class="fas fa-check-circle me-2"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>