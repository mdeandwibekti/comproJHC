<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN POST (Harus SEBELUM require layout/header.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $video_url = trim($_POST["video_url"]);
    $current_img = $_POST["current_image_360"];
    
    // Logika Upload File
    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $upload_dir = "../assets/img/virtual_tour/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["image_360"]["name"], PATHINFO_EXTENSION));
        $new_name = uniqid('vr_') . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $upload_dir . $new_name)) {
            // Hapus file lama jika ada penggantian untuk efisiensi storage
            if (!empty($current_img) && file_exists("../" . $current_img)) {
                unlink("../" . $current_img);
            }
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

// Ambil data terbaru
$data = $mysqli->query("SELECT * FROM page_virtual_room WHERE id=1")->fetch_assoc();

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Wrapper Neumorphism sesuai standar admin JHC */
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

    /* Tombol Utama Gradasi JHC */
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
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .preview-container { 
        background: #fdfdfd; 
        border: 2px dashed #ddd; 
        border-radius: 15px; 
        padding: 20px; 
    }

    .instruction-box { 
        background-color: #fef8f8; 
        border-left: 4px solid var(--jhc-red-dark); 
        padding: 15px; 
        border-radius: 8px; 
    }
    
    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; text-transform: uppercase; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Virtual Room Settings</h3>
                <p class="text-muted small mb-0">Kelola konten tur virtual dan video pengenalan fasilitas Rumah Sakit JHC.</p>
            </div>
            <button type="submit" form="vrForm" class="btn btn-jhc-save">
                <i class="fas fa-save me-2"></i> Simpan Perubahan
            </button>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Konten Virtual Room berhasil diperbarui!
            </div>
        <?php endif; ?>

        <form id="vrForm" action="virtual_room.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_image_360" value="<?= $data['image_path_360']; ?>">
            
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="mb-4">
                        <label class="form-label">Judul Halaman</label>
                        <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($data['title']); ?>" placeholder="Contoh: Jelajahi Fasilitas JHC secara Virtual" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-danger"><i class="fab fa-youtube me-1"></i> YouTube Embed Link</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-link text-muted"></i></span>
                            <input type="text" name="video_url" class="form-control form-control-lg border-start-0" value="<?= htmlspecialchars($data['video_url']); ?>" placeholder="https://www.youtube.com/embed/XYZ123">
                        </div>
                        
                        <div class="instruction-box mt-3 shadow-sm">
                            <p class="mb-2 small fw-bold text-dark"><i class="fas fa-lightbulb me-1 text-warning"></i> Panduan Link Video:</p>
                            <ul class="mb-0 small text-muted ps-3">
                                <li>Pastikan link mengandung kata <strong>/embed/</strong>.</li>
                                  <li>Contoh: <code>https://www.youtube.com/embed/XXXXX</code></li></code>.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Deskripsi / Kata Pengantar</label>
                        <textarea name="content" class="form-control" rows="10" placeholder="Tuliskan penjelasan singkat mengenai fasilitas yang ditampilkan..."><?= htmlspecialchars($data['content']); ?></textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card bg-light border-0 rounded-4 p-4">
                        <label class="form-label mb-3">Preview Media Aktif</label>
                        <div class="preview-container text-center mb-4">
                            <?php if(!empty($data['video_url'])): ?>
                                <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden mb-3">
                                    <iframe src="<?= $data['video_url']; ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fas fa-play-circle me-1"></i> Video YouTube</span>
                            <?php else: ?>
                                <img src="../<?= $data['image_path_360']; ?>" class="img-fluid rounded shadow-sm mb-3" style="max-height: 200px;">
                                <br><span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="fas fa-image me-1"></i> Foto Statis Aktif</span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small">Ganti Gambar Statis (Fallback)</label>
                            <input type="file" name="image_360" class="form-control shadow-sm">
                            <div class="form-text x-small mt-2 text-muted">Gambar ini muncul jika URL video kosong. Rekomendasi: Lanskap (1920x1080px).</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>