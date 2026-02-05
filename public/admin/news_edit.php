<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
$title = $content = $image_path = $category = "";
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

// 1. Ambil data jika dalam mode Edit
if ($id && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT title, content, image_path, category FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $title = $row['title'];
                $content = $row['content'];
                $image_path = $row['image_path'];
                $category = $row['category'];
            }
        }
        $stmt->close();
    }
}

// 2. Proses Simpan Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);
    $image_path = $_POST['current_image'];

    // Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/news/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid('news_') . '.' . $ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            // Hapus file lama jika ada penggantian
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
        if (empty($id)) {
            $stmt->bind_param("ssss", $title, $content, $image_path, $category);
        } else {
            $stmt->bind_param("ssssi", $title, $content, $image_path, $category, $id);
        }
        
        if ($stmt->execute()) {
            header("location: news.php?saved=true");
            exit();
        } else {
            $db_error = "Terjadi kesalahan database: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Artikel Baru" : "Edit Artikel Berita";
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
    .main-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }

    .btn-jhc-save { 
        background: var(--jhc-gradient); color: white !important; 
        border-radius: 12px; padding: 12px 35px; font-weight: 700; 
        border: none; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
    }
    .btn-jhc-save:hover { transform: translateY(-2px); opacity: 0.95; }

    .img-preview-box {
        background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px;
        padding: 20px; text-align: center; transition: 0.3s;
    }
    .img-preview-box img { max-height: 220px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .form-control:focus { border-color: var(--jhc-red-dark); box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Publikasikan edukasi kesehatan, berita rumah sakit, dan pengumuman terbaru JHC.</p>
            </div>
            <a href="news.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">
            
            <div class="row g-5">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label class="form-label text-muted small">Judul Artikel <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($title); ?>" placeholder="Masukkan headline berita..." required>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Kategori</label>
                            <input type="text" name="category" class="form-control form-control-lg" value="<?php echo htmlspecialchars($category); ?>" placeholder="Tips, Event, dll" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted small">Isi Konten Berita</label>
                            <textarea name="content" class="form-control" rows="15" placeholder="Tuliskan isi artikel secara lengkap di sini..."><?php echo htmlspecialchars($content); ?></textarea>
                            <div class="form-text mt-2 italic"><i class="fas fa-info-circle me-1"></i> Tips: Gunakan paragraf yang jelas agar pembaca nyaman mengikuti informasi kesehatan.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small">Gambar Utama (Cover)</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="News Image" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-newspaper fa-5x opacity-25 mb-3"></i><br>
                                <span class="small">Belum ada gambar sampul</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Ganti Gambar Sampul:</label>
                        <input type="file" name="image" class="form-control form-control-sm shadow-sm">
                        <div class="form-text x-small mt-2">Rekomendasi: Lanskap (16:9) untuk tampilan terbaik di website.</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Artikel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>