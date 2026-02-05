<?php
require_once "../../config.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $video_url = trim($_POST["video_url"]);
    $current_img = $_POST["current_image_360"];
    
    // Logika Upload File
    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $upload_dir = "../assets/img/virtual_tour/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["image_360"]["name"], PATHINFO_EXTENSION);
        $new_name = uniqid() . "_vr." . $file_ext;
        
        if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $upload_dir . $new_name)) {
            // Hapus file lama jika ada penggantian
            if (!empty($current_img) && file_exists("../" . $current_img)) unlink("../" . $current_img);
            $current_img = "assets/img/virtual_tour/" . $new_name;
        }
    }

    // Eksekusi Update ke Database
    $sql = "UPDATE page_virtual_room SET title=?, content=?, video_url=?, image_path_360=? WHERE id=1";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssss", $title, $content, $video_url, $current_img);
        if ($stmt->execute()) {
            header("location: virtual_room.php?status=updated");
            exit();
        }
        $stmt->close();
    }
}

// Ambil data untuk ditampilkan di form
$data = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1")->fetch_assoc();

require_once 'layout/header.php';
?>

<style>
    :root { --jhc-grad: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); }
    .btn-jhc { background: var(--jhc-grad); color: white !important; border: none; border-radius: 50px; padding: 10px 30px; font-weight: 700; transition: 0.3s; }
    .btn-jhc:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(138, 48, 51, 0.4); }
    .preview-box { border: 2px dashed #ddd; border-radius: 10px; padding: 15px; background: #f9f9f9; }
    .instruction-box { background-color: #fff5f5; border-left: 4px solid #bd3030; padding: 15px; border-radius: 4px; margin-top: 10px; }
    .instruction-box code { background: #eee; padding: 2px 5px; border-radius: 3px; color: #bd3030; font-size: 0.9em; word-break: break-all; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Manage Virtual Room</h3>
        <button type="submit" form="mainForm" class="btn btn-jhc"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
    </div>

    <?php if(isset($_GET['status'])) echo '<div class="alert alert-success shadow-sm border-0 border-start border-success border-4">Data Virtual Room berhasil diperbarui!</div>'; ?>

    <form id="mainForm" action="virtual_room.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="current_image_360" value="<?= $data['image_path_360']; ?>">
        
        <div class="row g-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted">Judul Utama</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted">YouTube Embed Link</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fab fa-youtube text-danger"></i></span>
                            <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($data['video_url']); ?>" placeholder="https://www.youtube.com/embed/...">
                        </div>
                        
                        <div class="instruction-box mt-3">
                            <p class="mb-2 small fw-bold text-dark"><i class="fas fa-info-circle me-1"></i> Penting:</p>
                            <ul class="mb-0 small text-muted ps-3">
                                <li>Wajib menggunakan format <code>/embed/</code> agar video dapat diputar otomatis di halaman utama.</li>
                                <li>Contoh format benar:<br><code>https://www.youtube.com/embed/jEEGbQE1sns</code></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted">Deskripsi Konten</label>
                        <textarea name="content" class="form-control" rows="8"><?= htmlspecialchars($data['content']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <label class="form-label fw-bold text-muted mb-3">Preview Media Saat Ini</label>
                    <div class="preview-box text-center mb-4">
                        <?php if(!empty($data['video_url'])): ?>
                            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm mb-2">
                                <iframe src="<?= $data['video_url']; ?>" frameborder="0"></iframe>
                            </div>
                            <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="fas fa-video me-1"></i> Video Aktif</span>
                        <?php else: ?>
                            <img src="../<?= $data['image_path_360']; ?>" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px;">
                            <br><span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="fas fa-image me-1"></i> Gambar Aktif (Fallback)</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted">Ganti Gambar Fallback (Jika Video Kosong)</label>
                        <input type="file" name="image_360" class="form-control">
                        <p class="form-text text-muted small mt-2">Format: JPG, PNG, JPEG. Rekomendasi rasio 16:9.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'layout/footer.php'; ?>