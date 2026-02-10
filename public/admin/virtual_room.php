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
    $current_video = $_POST["current_video_path"] ?? '';

    // 1. LOGIKA UPLOAD VIDEO LOKAL
    if (isset($_FILES["video_file"]) && $_FILES["video_file"]["error"] == 0) {
        $vid_dir = "../../assets/videos/virtual_tour/"; 
        if (!is_dir($vid_dir)) mkdir($vid_dir, 0777, true);
        
        $vid_ext = strtolower(pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION));
        $allowed_vid = ['mp4', 'webm'];
        
        if (in_array($vid_ext, $allowed_vid)) {
            // Batasi ukuran (Contoh: 60MB)
            if ($_FILES["video_file"]["size"] < 60 * 1024 * 1024) {
                $new_vid_name = "VR_VID_" . uniqid() . "." . $vid_ext;
                if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $vid_dir . $new_vid_name)) {
                    // Hapus video lama jika ada
                    if (!empty($current_video) && file_exists("../../" . $current_video)) {
                        unlink("../../" . $current_video);
                    }
                    $current_video = "assets/videos/virtual_tour/" . $new_vid_name;
                    $video_url = ""; // Kosongkan URL YouTube jika upload file lokal
                }
            } else { $error_msg = "Ukuran video terlalu besar (Maks 60MB)."; }
        } else { $error_msg = "Format video harus MP4 atau WebM."; }
    }

    // 2. LOGIKA UPLOAD GAMBAR 360
    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $img_dir = "../../assets/img/virtual_tour/"; 
        if (!is_dir($img_dir)) mkdir($img_dir, 0777, true);
        
        $img_ext = strtolower(pathinfo($_FILES["image_360"]["name"], PATHINFO_EXTENSION));
        if (in_array($img_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $new_img_name = "VR_IMG_" . uniqid() . "." . $img_ext;
            if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $img_dir . $new_img_name)) {
                if (!empty($current_img) && file_exists("../../" . $current_img)) {
                    unlink("../../" . $current_img);
                }
                $current_img = "assets/img/virtual_tour/" . $new_img_name;
            }
        }
    }

    // 3. SIMPAN KE DATABASE
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

// AMBIL DATA TERBARU
$query = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1");
$data = $query->fetch_assoc() ?: ['title'=>'','content'=>'','video_url'=>'','image_path_360'=>'','video_path'=>''];

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
            <input type="hidden" name="current_image_360" value="<?= $data['image_path_360']; ?>">
            <input type="hidden" name="current_video_path" value="<?= $data['video_path']; ?>">

            <div class="d-flex justify-content-between align-items-center manage-header">
                <div>
                    <h3 class="fw-bold mb-1">Pengaturan Tur Virtual</h3>
                    <p class="text-muted small mb-0">Kontrol konten visual untuk Virtual Room RS JHC.</p>
                </div>
                <button type="submit" class="btn btn-jhc-save" id="btnSubmit"><i class="fas fa-save me-2"></i> Simpan</button>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> Pengaturan berhasil diperbarui!</div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Halaman</label>
                        <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($data['title']); ?>" required>
                    </div>

                    <div class="card card-settings p-4 mb-4">
                        <label class="form-label text-danger"><i class="fab fa-youtube me-1"></i> Opsi A: Link YouTube</label>
                        <input type="text" name="video_url" class="form-control mb-2" value="<?= htmlspecialchars($data['video_url']); ?>" placeholder="https://www.youtube.com/embed/...">
                        <small class="text-muted">Jika diisi, video lokal akan diabaikan.</small>
                    </div>

                    <div class="card card-settings p-4 mb-4">
                        <label class="form-label text-primary"><i class="fas fa-upload me-1"></i> Opsi B: Upload Video Lokal (MP4)</label>
                        <input type="file" name="video_file" class="form-control" accept="video/mp4">
                        <div id="uploadProgress" class="progress mt-2 d-none" style="height: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Maksimal 60MB. Direkomendasikan resolusi HD 720p.</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Deskripsi Narasi</label>
                        <textarea name="content" class="form-control" rows="6"><?= htmlspecialchars($data['content']); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="form-label">Preview Media Aktif</label>
                    <div class="preview-box mb-4 shadow-sm">
                        <?php if(!empty($data['video_url'])): ?>
                            <div class="ratio ratio-16x9"><iframe src="<?= $data['video_url']; ?>" allowfullscreen></iframe></div>
                        <?php elseif(!empty($data['video_path'])): ?>
                            <video controls class="w-100"><source src="../../<?= $data['video_path']; ?>" type="video/mp4"></video>
                        <?php elseif(!empty($data['image_path_360'])): ?>
                            <img src="../../<?= $data['image_path_360']; ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="text-white opacity-50 text-center"><i class="fas fa-photo-video fa-3x mb-2"></i><br>Belum ada media</div>
                        <?php endif; ?>
                    </div>

                    <div class="card card-settings p-4">
                        <label class="form-label">Gambar 360 / Thumbnail</label>
                        <input type="file" name="image_360" class="form-control" accept="image/*">
                        <small class="text-muted mt-2">Digunakan sebagai latar belakang jika video tidak diputar.</small>
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
        
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengunggah...';
        btn.classList.add('disabled');
        
        // Menampilkan progress bar jika ada file video yang dipilih
        const videoInput = document.querySelector('input[name="video_file"]');
        if(videoInput.files.length > 0) {
            progress.classList.remove('d-none');
            let width = 0;
            const bar = progress.querySelector('.progress-bar');
            const interval = setInterval(() => {
                if (width >= 90) clearInterval(interval);
                width += 10;
                bar.style.width = width + '%';
            }, 500);
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>