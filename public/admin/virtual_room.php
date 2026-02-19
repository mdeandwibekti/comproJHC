<?php
require_once "../../config.php";

$success_msg = "";
$error_msg = "";

// --- LOGIKA PEMROSESAN POST (Tetap Sama) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? '');
    $content = trim($_POST["content"] ?? '');
    $video_url = trim($_POST["video_url"] ?? ''); 
    $current_img = $_POST["current_image_360"] ?? '';
    $current_video = $_POST["current_video_path"] ?? '';

    if (isset($_FILES["video_file"]) && $_FILES["video_file"]["error"] == 0) {
        $vid_dir = "../../assets/videos/virtual_tour/"; 
        if (!is_dir($vid_dir)) mkdir($vid_dir, 0777, true);
        $vid_ext = strtolower(pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION));
        if (in_array($vid_ext, ['mp4', 'webm'])) {
            if ($_FILES["video_file"]["size"] < 60 * 1024 * 1024) {
                $new_vid_name = "VR_VID_" . uniqid() . "." . $vid_ext;
                if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $vid_dir . $new_vid_name)) {
                    if (!empty($current_video) && file_exists("../../" . $current_video)) unlink("../../" . $current_video);
                    $current_video = "assets/videos/virtual_tour/" . $new_vid_name;
                    $video_url = ""; 
                }
            } else { $error_msg = "Ukuran video terlalu besar (Maks 60MB)."; }
        } else { $error_msg = "Format video harus MP4 atau WebM."; }
    }

    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $img_dir = "../../assets/img/virtual_tour/"; 
        if (!is_dir($img_dir)) mkdir($img_dir, 0777, true);
        $img_ext = strtolower(pathinfo($_FILES["image_360"]["name"], PATHINFO_EXTENSION));
        if (in_array($img_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $new_img_name = "VR_IMG_" . uniqid() . "." . $img_ext;
            if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $img_dir . $new_img_name)) {
                if (!empty($current_img) && file_exists("../../" . $current_img)) unlink("../../" . $current_img);
                $current_img = "assets/img/virtual_tour/" . $new_img_name;
            }
        }
    }

    if (empty($error_msg)) {
        $sql = "UPDATE page_virtual_room SET title=?, content=?, video_url=?, image_path_360=?, video_path=? WHERE id=1";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssss", $title, $content, $video_url, $current_img, $current_video);
            if ($stmt->execute()) {
                header("location: virtual_room.php?status=updated");
                exit();
            } else { $error_msg = "Gagal memperbarui database."; }
            $stmt->close();
        }
    }
}

$query = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1");
$data = $query->fetch_assoc() ?: ['title'=>'','content'=>'','video_url'=>'','image_path_360'=>'','video_path'=>''];

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    /* Breadcrumb */
    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; transition: 0.3s; }
    .breadcrumb-jhc a:hover { color: var(--jhc-red-dark); }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .admin-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 45px; border: 1px solid #f1f5f9;
    }

    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 35px; }

    .card-settings {
        background: #fcfdfe; border: 1px solid #edf2f7;
        border-radius: 18px; transition: 0.3s;
    }
    .card-settings:hover { border-color: #cbd5e1; background: #fff; }

    .form-label { font-weight: 700; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: block; }
    .form-control { border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; transition: 0.3s; background-color: #fcfdfe; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1); background-color: #fff; }

    .btn-jhc-save { 
        background: var(--jhc-gradient) !important; color: white !important; 
        border-radius: 14px; padding: 14px 40px; font-weight: 800; border: none !important;
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); transition: 0.3s; 
    }
    .btn-jhc-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); }

    .preview-box {
        background: #1a202c; border-radius: 20px; overflow: hidden;
        border: 4px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .status-pill {
        display: inline-flex; align-items: center; padding: 4px 12px;
        border-radius: 50px; font-size: 0.7rem; font-weight: 800;
        text-transform: uppercase; margin-bottom: 10px;
    }
    .pill-active { background: #ecfdf5; color: #10b981; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <span class="current">Tur Virtual & 360°</span>
    </div>

    <div class="admin-wrapper">
        <form id="vrForm" action="virtual_room.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_image_360" value="<?= $data['image_path_360']; ?>">
            <input type="hidden" name="current_video_path" value="<?= $data['video_path']; ?>">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
                <div>
                    <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;">Pengaturan Tur Virtual</h2>
                    <p class="text-muted small mb-0">Kelola konten visual imersif untuk fasilitas Rumah Sakit.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="submit" class="btn btn-jhc-save" id="btnSubmit">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Publikasikan Perubahan
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <div class="alert alert-success border-0 shadow-sm border-start border-success border-5 mb-4 p-3">
                    <div class="d-flex align-items-center"><i class="fas fa-check-circle fa-lg me-3"></i> Virtual Room telah berhasil diperbarui!</div>
                </div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="alert alert-danger border-0 shadow-sm border-start border-danger border-5 mb-4 p-3">
                    <div class="d-flex align-items-center"><i class="fas fa-exclamation-triangle fa-lg me-3"></i> <?= $error_msg; ?></div>
                </div>
            <?php endif; ?>

            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Utama Halaman</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold" value="<?= htmlspecialchars($data['title']); ?>" required>
                    </div>

                    <div class="card card-settings p-4 mb-4">
                        <div class="status-pill pill-active"><i class="fas fa-link me-1"></i> Opsi A: Integrasi Video</div>
                        <label class="form-label text-danger" style="opacity: 0.7;">YouTube Embed Link</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fab fa-youtube text-danger"></i></span>
                            <input type="text" name="video_url" class="form-control border-start-0" value="<?= htmlspecialchars($data['video_url']); ?>" placeholder="https://www.youtube.com/embed/XXXXX">
                        </div>
                        <small class="text-muted mt-2 d-block fst-italic">*Jika diisi, sistem akan memprioritaskan link YouTube ini.</small>
                    </div>

                    <div class="card card-settings p-4 mb-4">
                        <div class="status-pill pill-active" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-file-video me-1"></i> Opsi B: Server Video Lokal</div>
                        <label class="form-label text-primary" style="opacity: 0.7;">Upload Video MP4/WebM</label>
                        <input type="file" name="video_file" class="form-control" accept="video/mp4, video/webm">
                        <div id="uploadProgress" class="progress mt-3 d-none" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Maks 60MB. Disarankan format <b>.mp4</b> dengan resolusi 1080p.</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Narasi Deskripsi Fasilitas</label>
                        <textarea name="content" class="form-control" rows="8" style="line-height: 1.6;" placeholder="Berikan deskripsi mendalam mengenai pengalaman tur virtual ini..."><?= htmlspecialchars($data['content']); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label">Preview Konten Aktif</label>
                    <div class="preview-box mb-4">
                        <?php if(!empty($data['video_url'])): ?>
                            <div class="ratio ratio-16x9"><iframe src="<?= $data['video_url']; ?>" allowfullscreen></iframe></div>
                        <?php elseif(!empty($data['video_path'])): ?>
                            <video controls class="w-100 d-block"><source src="../../<?= $data['video_path']; ?>" type="video/mp4"></video>
                        <?php elseif(!empty($data['image_path_360'])): ?>
                            <img src="../../<?= $data['image_path_360']; ?>" class="img-fluid d-block">
                        <?php else: ?>
                            <div class="py-5 text-white opacity-25 text-center">
                                <i class="fas fa-photo-video fa-4x mb-3"></i><br>
                                <span class="small fw-bold">Belum ada media aktif</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('vrForm').onsubmit = function() {
        const btn = document.getElementById('btnSubmit');
        const progress = document.getElementById('uploadProgress');
        const videoInput = document.querySelector('input[name="video_file"]');
        
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
        btn.classList.add('disabled');
        
        if(videoInput.files.length > 0) {
            progress.classList.remove('d-none');
            let width = 0;
            const bar = progress.querySelector('.progress-bar');
            const interval = setInterval(() => {
                if (width >= 90) clearInterval(interval);
                width += 5;
                bar.style.width = width + '%';
            }, 600);
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>