<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN DATA ---
$name = $description = $image_path = "";
$display_order = 0;
// Inisialisasi category kosong untuk memastikan item ini dianggap sebagai 'Kategori Induk'
$category = ""; 
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// 1. Jika Mode Edit, Ambil Data Fasilitas
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    // Query hanya mengambil field yang diperlukan (tanpa category karena ini adalah Parent)
    $sql = "SELECT name, description, display_order, image_path FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $name = $row['name'];
                $description = $row['description'];
                $image_path = $row['image_path'];
                $display_order = $row['display_order'];
            }
        }
        $stmt->close();
    }
}

// 2. Proses Simpan Data (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST["display_order"];
    $image_path = $_POST['current_image'];
    // Category dikosongkan agar item ini muncul di daftar utama admin & frontend
    $category = ""; 

    // Handle Upload Gambar Baru
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '-facility.' . $file_ext;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                unlink("../" . $_POST['current_image']);
            }
            $image_path = "assets/img/gallery/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO facilities (name, category, description, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE facilities SET name = ?, category = ?, description = ?, display_order = ?, image_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("sssis", $name, $category, $description, $display_order, $image_path);
        } else {
            $stmt->bind_param("sssisi", $name, $category, $description, $display_order, $image_path, $id);
        }
        
        if ($stmt->execute()) {
            header("location: facilities.php?saved=true");
            exit();
        } else {
            $db_error = "Terjadi kesalahan database: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'layout/header.php';
$page_title_text = empty($id) ? "Tambah Kategori Fasilitas" : "Edit Kategori Fasilitas";
?>

<style>
    :root { --jhc-red-dark: #8a3033; --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05); }
    .page-header-jhc { border-left: 5px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .form-label { font-weight: 700; color: #444; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }
    .btn-jhc-save { background: var(--jhc-gradient); color: white !important; border-radius: 12px; padding: 12px 30px; font-weight: 700; border: none; transition: 0.3s; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); }
    .img-preview-box { background: #fdfdfd; border: 2px dashed #ddd; border-radius: 15px; padding: 20px; text-align: center; }
    .img-preview-box img { max-height: 220px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $page_title_text; ?></h3>
                <p class="text-muted small mb-0">Input ini hanya untuk <b>Kategori Utama</b> (kartu fasilitas yang muncul di beranda).</p>
            </div>
            <a href="facilities.php" class="btn btn-light rounded-pill px-4 btn-sm fw-bold border">Kembali</a>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row g-5">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Nama Kategori Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($name); ?>" placeholder="Contoh: IGD 24 Jam, Kamar Perawatan..." required>
                        <small class="text-muted">Nama ini akan menjadi judul pada kartu fasilitas di halaman utama.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Berikan penjelasan singkat mengenai kategori ini..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Urutan Tampilan</label>
                        <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Gambar Sampul Kategori</label>
                    <div class="img-preview-box mb-3">
                        <?php if(!empty($image_path)): ?>
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" class="img-fluid">
                        <?php else: ?>
                            <div class="py-5 text-muted">
                                <i class="fas fa-image fa-4x opacity-25"></i>
                                <p class="small mt-2">Pratinjau Gambar Utama</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                    <input type="file" name="image" class="form-control shadow-sm">
                    <small class="text-muted mt-2 d-block italic">Disarankan ukuran 800x600px atau rasio 4:3.</small>
                </div>
            </div>

            <div class="mt-5 border-top pt-4 text-end">
                <button type="submit" class="btn btn-jhc-save shadow">
                    <i class="fas fa-save me-2"></i> Simpan Kategori Utama
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>