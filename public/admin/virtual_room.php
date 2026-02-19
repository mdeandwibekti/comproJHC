<?php
require_once "../../config.php";

$success_msg = "";
$error_msg = "";

// --- LOGIKA PEMROSESAN POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? '');
    $content = trim($_POST["content"] ?? '');
    $video_url = trim($_POST["video_url"] ?? ''); 
    $bpjs_link = trim($_POST["bpjs_link"] ?? 'alur-bpjs.php');
    $bpjs_content = $_POST["bpjs_content"] ?? '';
    $current_img = $_POST["current_image_360"] ?? '';
    $current_video = $_POST["current_video_path"] ?? '';

    // Logika upload video lokal
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

    // Logika upload image 360
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
        // UPDATE SQL: Menyimpan semua data termasuk konten BPJS
        $sql = "UPDATE page_virtual_room SET title=?, content=?, video_url=?, image_path_360=?, video_path=?, bpjs_link=?, bpjs_content=? WHERE id=1";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssssss", $title, $content, $video_url, $current_img, $current_video, $bpjs_link, $bpjs_content);
            if ($stmt->execute()) {
                header("location: virtual_room.php?status=updated");
                exit();
            } else { $error_msg = "Gagal memperbarui database."; }
            $stmt->close();
        }
    }
}

$query = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1");
$data = $query->fetch_assoc() ?: [
    'title'=>'','content'=>'','video_url'=>'','image_path_360'=>'','video_path'=>'','bpjs_link'=>'alur-bpjs.php','bpjs_content'=>''
];

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }
    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }
    .admin-wrapper { background: #ffffff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); padding: 45px; border: 1px solid #f1f5f9; }
    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 35px; }
    .card-settings { background: #fcfdfe; border: 1px solid #edf2f7; border-radius: 18px; padding: 25px; margin-bottom: 25px; transition: 0.3s; }
    .card-settings:hover { border-color: #cbd5e1; background: #fff; }
    .form-label { font-weight: 700; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: block; }
    .form-control { border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; background-color: #fcfdfe; }
    .btn-jhc-save { background: var(--jhc-gradient) !important; color: white !important; border-radius: 14px; padding: 14px 40px; font-weight: 800; border: none !important; box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); }
    .status-pill { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <form id="vrForm" action="virtual_room.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_image_360" value="<?= $data['image_path_360']; ?>">
            <input type="hidden" name="current_video_path" value="<?= $data['video_path']; ?>">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
                <div>
                    <h2 style="font-weight: 800; letter-spacing: -1px;">Pengaturan Tur Virtual & BPJS</h2>
                    <p class="text-muted small mb-0">Kelola konten imersif serta panduan pendaftaran pasien.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="submit" class="btn btn-jhc-save" id="btnSubmit">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                <div id="successAlert" class="alert alert-success border-0 shadow-sm border-start border-success border-5 mb-4 p-3">
                    <div class="d-flex align-items-center"><i class="fas fa-check-circle fa-lg me-3"></i> <strong>Berhasil!</strong> Data telah diperbarui.</div>
                </div>
            <?php endif; ?>

            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Utama Halaman</label>
                        <input type="text" name="title" class="form-control fw-bold" value="<?= htmlspecialchars($data['title']); ?>" required>
                    </div>

                    <div class="card card-settings border-primary border-start border-4">
                        <div class="status-pill" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-route me-1"></i> Alur BPJS</div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Link Navigasi</label>
                            <input type="text" name="bpjs_link" class="form-control" value="<?= htmlspecialchars($data['bpjs_link']); ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-primary">Isi Panduan Alur (Teks)</label>
                            <textarea name="bpjs_content" class="form-control" rows="8"><?= htmlspecialchars($data['bpjs_content']); ?></textarea>
                        </div>
                    </div>

                    <div class="card card-settings">
                        <div class="status-pill" style="background:#fff1f2; color:#e11d48;"><i class="fab fa-youtube me-1"></i> Video YouTube</div>
                        <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($data['video_url']); ?>" placeholder="https://www.youtube.com/embed/...">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Narasi Deskripsi Fasilitas</label>
                        <textarea name="content" class="form-control" rows="5"><?= htmlspecialchars($data['content']); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card card-settings">
                        <label class="form-label">Update Media Lokal</label>
                        <small class="d-block mb-2 text-muted">Video (.mp4):</small>
                        <input type="file" name="video_file" class="form-control mb-3" accept="video/mp4">
                        <small class="d-block mb-2 text-muted">Gambar 360°:</small>
                        <input type="file" name="image_360" class="form-control" accept="image/*">
                    </div>

                    <label class="form-label">Preview Media</label>
                    <div class="preview-box">
                        <?php if(!empty($data['video_url'])): ?>
                            <div class="ratio ratio-16x9"><iframe src="<?= $data['video_url']; ?>" allowfullscreen></iframe></div>
                        <?php elseif(!empty($data['video_path'])): ?>
                            <video controls class="w-100"><source src="../../<?= $data['video_path']; ?>" type="video/mp4"></video>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        if ($("#successAlert").length) {
            setTimeout(function() { $("#successAlert").fadeOut("slow"); }, 3000);
        }
    });
</script>

<?php require_once 'layout/footer.php'; ?>