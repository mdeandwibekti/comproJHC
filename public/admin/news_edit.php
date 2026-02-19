<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN ---
$title = $content = $image_path = $category = "";
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

if ($id && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT title, content, image_path, category FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $title = $row['title']; $content = $row['content'];
                $image_path = $row['image_path']; $category = $row['category'];
            }
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);
    $image_path = $_POST['current_image'];

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/news/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid('news_') . '.' . $ext;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                unlink("../" . $_POST['current_image']);
            }
            $image_path = "assets/img/news/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO news (title, content, image_path, category) VALUES (?, ?, ?, ?)";
    } else {
        $sql = "UPDATE news SET title = ?, content = ?, image_path = ?, category = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) { $stmt->bind_param("ssss", $title, $content, $image_path, $category); }
        else { $stmt->bind_param("ssssi", $title, $content, $image_path, $category, $id); }
        if ($stmt->execute()) { header("location: news.php?saved=true"); exit(); }
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Artikel Baru" : "Edit Artikel Berita";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .main-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 45px; margin-top: 10px; border: 1px solid #f1f5f9;
    }

    .page-header-jhc { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }

    .form-label { font-weight: 700; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; }
    .form-control { border: 2px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; transition: 0.3s; background-color: #fcfdfe; }
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 4px rgba(138, 48, 51, 0.1); background-color: #fff; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 14px; padding: 14px 40px; font-weight: 800; border: none; 
        transition: 0.3s; box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2);
    }
    .btn-jhc-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); }

    .img-preview-box {
        background: #fcfdfe; border: 2px dashed #e2e8f0; border-radius: 20px;
        padding: 30px; text-align: center; transition: 0.3s;
    }
    .img-preview-box:hover { border-color: var(--jhc-red-dark); background: #fff; }
    .img-preview-box img { max-height: 250px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 3px solid #fff; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <a href="news.php">Berita & Artikel</a>
        <span class="text-muted opacity-50">/</span> 
        <span class="current"><?= empty($id) ? 'Tambah' : 'Edit'; ?> Artikel</span>
    </div>

    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;"><?php echo $page_title_text; ?></h2>
                <p class="text-muted small mb-0">Publikasikan informasi kesehatan yang bermanfaat bagi masyarakat.</p>
            </div>
            <a href="news.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">
            
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="row g-4">
                        <div class="col-md-9">
                            <label class="form-label">Headline / Judul Artikel <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg fw-bold" value="<?php echo htmlspecialchars($title); ?>" placeholder="Masukkan judul utama..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control form-control-lg" value="<?php echo htmlspecialchars($category); ?>" placeholder="E.g. Tips Medis" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Isi Konten Artikel</label>
                            <textarea name="content" class="form-control" rows="18" style="font-size: 0.95rem; line-height: 1.6;" placeholder="Tuliskan isi artikel secara mendalam..."><?php echo htmlspecialchars($content); ?></textarea>
                            <div class="alert alert-light border mt-3 small text-muted">
                                <i class="fas fa-info-circle me-1"></i> Gunakan paragraf yang jelas. Artikel yang rapi meningkatkan kepercayaan pembaca.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <label class="form-label">Gambar Sampul (Thumbnail)</label>
                    <div class="img-preview-box mb-4">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted opacity-25">
                                <i class="fas fa-cloud-upload-alt fa-5x mb-3"></i><br>
                                <span class="small fw-bold">Belum ada sampul</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-3 rounded-4 border bg-light">
                        <label class="small text-muted mb-2 d-block fw-bold">Pilih File Baru:</label>
                        <input type="file" name="image" class="form-control form-control-sm">
                        <div class="form-text x-small mt-2">Gunakan rasio 16:9 (Lanskap) untuk hasil visual terbaik di website.</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-5 text-center text-lg-end">
                <button type="submit" class="btn btn-jhc-save">
                    <i class="fas fa-paper-plane me-2"></i> Simpan & Terbitkan Artikel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>