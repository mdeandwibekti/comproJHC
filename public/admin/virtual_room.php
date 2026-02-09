<?php
require_once "../../config.php";

$success_msg = "";
$error_msg = "";

// --- LOGIKA PEMROSESAN POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? '');
    $content = trim($_POST["content"] ?? '');
    $video_url = trim($_POST["video_url"] ?? '');
    $current_img = $_POST["current_image_360"] ?? '';
    
    // Logika Upload File
    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $upload_dir = "../../assets/img/virtual_tour/"; 
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_info = pathinfo($_FILES["image_360"]["name"]);
        $file_ext = strtolower($file_info['extension']);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_name = uniqid('vr_') . "." . $file_ext;
            $target_file = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $target_file)) {
                // Hapus file lama jika ada
                if (!empty($current_img) && file_exists("../../" . $current_img)) {
                    unlink("../../" . $current_img);
                }
                $current_img = "assets/img/virtual_tour/" . $new_name;
            } else {
                $error_msg = "Gagal memindahkan file.";
            }
        } else {
            $error_msg = "Format file tidak didukung (Gunakan JPG/PNG/WebP).";
        }
    }

    if (empty($error_msg)) {
        $sql = "UPDATE page_virtual_room SET title=?, content=?, video_url=?, image_path_360=? WHERE id=1";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssss", $title, $content, $video_url, $current_img);
            if ($stmt->execute()) {
                header("location: virtual_room.php?status=updated");
                exit();
            } else {
                $error_msg = "Database error: " . $mysqli->error;
            }
            $stmt->close();
        }
    }
}

// --- AMBIL DATA DENGAN PREVENT NULL ---
$query = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1");
$data = $query->fetch_assoc();

// Jika data tidak ditemukan di database, inisialisasi array kosong agar tidak error
if (!$data) {
    $data = [
        'title' => '',
        'content' => '',
        'video_url' => '',
        'image_path_360' => ''
    ];
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .manage-header {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; 
        color: white !important; 
        border-radius: 12px !important; 
        padding: 12px 35px !important; 
        font-weight: 700; 
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
    }

    .preview-container { 
        background: #fdfdfd; 
        border: 2px dashed #ddd; 
        border-radius: 15px; 
        padding: 15px; 
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .instruction-box { 
        background-color: #fef8f8; 
        border-left: 4px solid var(--jhc-red-dark); 
        padding: 15px; 
        border-radius: 8px; 
    }
    
    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; text-transform: uppercase; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <form id="vrForm" action="virtual_room.php" method="post" enctype="multipart/form-data">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">Virtual Room Settings</h3>
                    <p class="text-muted small mb-0">Kelola konten tur virtual RS JHC.</p>
                </div>
                <button type="submit" class="btn btn-jhc-save mt-3 mt-md-0" id="btnSubmit">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                    <i class="fas fa-check-circle me-2"></i> Berhasil diperbarui!
                </div>
            <?php endif; ?>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger border-start border-danger border-4 mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_msg; ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="current_image_360" value="<?= htmlspecialchars($data['image_path_360'] ?? ''); ?>">
            
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Halaman</label>
                        <input type="text" name="title" class="form-control form-control-lg" 
                               value="<?= htmlspecialchars($data['title'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-danger"><i class="fab fa-youtube me-1"></i> YouTube Embed Link</label>
                        <input type="text" name="video_url" class="form-control" 
                               value="<?= htmlspecialchars($data['video_url'] ?? ''); ?>" 
                               placeholder="https://www.youtube.com/embed/XXXXX">
                        
<<<<<<< HEAD
                        <div class="instruction-box mt-3">
                            <p class="mb-1 small fw-bold">Penting:</p>
                            <small class="text-muted">Link harus mengandung <b>/embed/</b> agar video muncul.</small>
=======
                        <div class="instruction-box mt-3 shadow-sm">
                            <p class="mb-2 small fw-bold text-dark"><i class="fas fa-lightbulb me-1 text-warning"></i> Panduan Link Video:</p>
                            <ul class="mb-0 small text-muted ps-3">
                                <li>Pastikan link mengandung kata <strong>/embed/</strong>.</li>
                                  <li>Contoh: <code>https://www.youtube.com/embed/XXXXX</code></li></code>.</li>
                            </ul>
>>>>>>> ab9426a9713eff8e47ef0d104eb64cbc11274bcd
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="content" class="form-control" rows="8"><?= htmlspecialchars($data['content'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card bg-light border-0 rounded-4 p-4 shadow-sm">
                        <label class="form-label mb-3">Preview Aktif</label>
                        <div class="preview-container text-center mb-4">
                            <?php if(!empty($data['video_url'])): ?>
                                <div class="ratio ratio-16x9 rounded overflow-hidden mb-2">
                                    <iframe src="<?= htmlspecialchars($data['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                                <span class="badge bg-danger rounded-pill">Video Mode</span>
                            <?php elseif(!empty($data['image_path_360'])): ?>
                                <img src="../../<?= htmlspecialchars($data['image_path_360']); ?>" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px;">
                                <span class="badge bg-secondary rounded-pill">Image Mode</span>
                            <?php else: ?>
                                <p class="text-muted small">Belum ada media.</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small">Ganti Gambar (Fallback)</label>
                            <input type="file" name="image_360" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('vrForm').onsubmit = function() {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        btn.classList.add('disabled');
    };
</script>

<?php require_once 'layout/footer.php'; ?>