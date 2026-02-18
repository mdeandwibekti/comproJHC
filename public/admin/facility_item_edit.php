<?php
require_once "../../config.php";

// --- 1. AMBIL NAMA-NAMA DARI FASILITAS UTAMA UNTUK DROPDOWN ---
// Fasilitas Utama adalah yang kolom 'category'-nya kosong/NULL
$existing_categories = [];
$cat_query = "SELECT name FROM facilities WHERE category IS NULL OR category = '' ORDER BY name ASC";
$cat_result = $mysqli->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $existing_categories[] = $row['name'];
    }
}

// --- 2. LOGIKA PEMROSESAN DATA ---
$name = $category = $description = $image_path = "";
$display_order = 0;
$id = isset($_POST['id']) ? trim($_POST['id']) : (isset($_GET['id']) ? trim($_GET['id']) : null);

// Ambil data untuk mode EDIT
if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT name, category, description, display_order, image_path FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                $name = $row['name'];
                $category = $row['category'];
                $description = $row['description'];
                $image_path = $row['image_path'];
                $display_order = $row['display_order'];
            }
        }
        $stmt->close();
    }
}

// Proses SIMPAN (Insert/Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $category = $_POST["category"]; // Nama Kategori Utama (Misal: Kamar Perawatan)
    $description = trim($_POST["description"]);
    $display_order = (int)$_POST["display_order"];
    $image_path = $_POST['current_image'];

    // Handle Upload Foto
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "../assets/img/gallery/";
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid('sub_fac_') . '.' . $file_ext;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                @unlink("../" . $_POST['current_image']);
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
        }
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { --jhc-red: #8a3033; --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border: 1px solid #eee; }
    .form-label { font-weight: 700; color: #444; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 7px; display: block;}
    .category-note { font-size: 0.75rem; color: #888; font-style: italic; margin-top: 5px; display: block;}
    .btn-save { background: var(--jhc-gradient); color: white; border: none; padding: 12px 35px; border-radius: 12px; font-weight: 700; transition: 0.3s; }
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(138, 48, 51, 0.3); }
    .img-preview { max-height: 250px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container py-4">
    <div class="main-wrapper">
        <div class="mb-4 border-start border-4 border-danger ps-3">
            <h3 class="fw-bold mb-0"><?= empty($id) ? 'Input Isi Fasilitas' : 'Edit Isi Fasilitas'; ?></h3>
            <p class="text-muted small">Hubungkan item fasilitas spesifik ke Kategori Utama.</p>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="current_image" value="<?= $image_path; ?>">

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="mb-4">
                        <label class="form-label">Hubungkan ke Kategori Utama</label>
                        <select name="category" class="form-select form-control-lg" required>
                            <option value="" disabled <?= empty($category) ? 'selected' : ''; ?>>-- Pilih Kategori Utama --</option>
                            <?php if (!empty($existing_categories)): ?>
                                <?php foreach ($existing_categories as $cat_name): ?>
                                    <option value="<?= htmlspecialchars($cat_name); ?>" <?= ($category == $cat_name) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($cat_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada Kategori Utama. Buat dulu di menu utama.</option>
                            <?php endif; ?>
                        </select>
                        <span class="category-note">*Daftar ini berisi nama fasilitas utama (Parent) yang Anda input di halaman Manajemen Fasilitas.</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nama Item (Misal: Kamar VIP, R. Lab, Ambulans)</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kamar VVIP" value="<?= htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Fasilitas</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan detail fasilitas ini..."><?= htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="display_order" class="form-control" value="<?= $display_order; ?>">
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Foto Item Fasilitas</label>
                    <div class="border rounded-4 p-3 text-center bg-light mb-3">
                        <?php if($image_path): ?>
                            <img src="../<?= $image_path; ?>" class="img-fluid img-preview">
                        <?php else: ?>
                            <div class="py-5 text-muted"><i class="fas fa-image fa-4x opacity-25"></i><br>Belum ada foto</div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>

            <div class="text-end border-top pt-4 mt-4">
                <a href="facilities.php" class="btn btn-light rounded-pill px-4 me-2">Kembali</a>
                <button type="submit" class="btn btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>