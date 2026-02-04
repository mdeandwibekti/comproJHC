<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi Variabel Default
$title = $description = $image_path = "";
$price = 0;
$display_order = 0;
$title_err = $price_err = $image_err = "";
$page_title = "Add New MCU Package";
$id = 0;

// Cek apakah mode Edit
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit MCU Package";
}

// --- PROSES SUBMIT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    // 1. Validasi Input
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }

    $description = trim($_POST["description"]);
    $display_order = (int)$_POST['display_order']; 
    $image_path = $_POST['current_image'] ?? '';

    // Bersihkan format harga (hapus Rp, titik, koma) agar jadi angka murni
    $clean_price = str_replace(['Rp', '.', ','], '', $_POST["price"]);
    if (is_numeric($clean_price)) {
        $price = $clean_price;
    } else {
        $price_err = "Invalid price format. Use numbers only.";
    }

    // 2. Handle Upload Gambar
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES["image"]["name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid() . '-mcu.' . $file_ext;
            $upload_dir = "../assets/img/mcu_packages/";
            
            // Buat folder otomatis jika belum ada
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    die("Gagal membuat folder upload: $upload_dir");
                }
            }

            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                $image_path = "assets/img/mcu_packages/" . $new_filename;
            } else {
                $image_err = "Gagal memindahkan file gambar.";
            }
        } else {
            $image_err = "Tipe file tidak valid. Hanya JPG, PNG, GIF diperbolehkan.";
        }
    } 
    // Jika tambah baru tapi tidak upload gambar
    else if (empty($id) && empty($image_path)) {
        $image_err = "Wajib upload gambar untuk paket baru.";
    }

    // 3. Eksekusi Query
    if (empty($title_err) && empty($price_err) && empty($image_err)) {
        
        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO mcu_packages (title, description, price, display_order, image_path) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                // Tipe data: s=string, d=double, i=int
                $stmt->bind_param("ssdis", $title, $description, $price, $display_order, $image_path);
            } else {
                die("Error Prepare INSERT: " . $mysqli->error);
            }
        } else {
            // UPDATE
            $sql = "UPDATE mcu_packages SET title=?, description=?, price=?, display_order=?, image_path=? WHERE id=?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ssdisi", $title, $description, $price, $display_order, $image_path, $id);
            } else {
                die("Error Prepare UPDATE: " . $mysqli->error);
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                // Redirect Sukses
                header("location: mcu_packages.php?saved=true");
                exit();
            } else {
                // TAMPILKAN ERROR JIKA GAGAL MENYIMPAN
                die("Error Execute SQL: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// --- FETCH DATA (EDIT MODE) ---
if ($_SERVER["REQUEST_METHOD"] != "POST" && !empty($id)) {
    $sql = "SELECT title, description, price, display_order, image_path FROM mcu_packages WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($title, $description, $price, $display_order, $image_path);
            $stmt->fetch();
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    
    <?php if($title_err || $price_err || $image_err): ?>
        <div class="alert alert-danger">
            <ul>
                <?php if($title_err) echo "<li>$title_err</li>"; ?>
                <?php if($price_err) echo "<li>$price_err</li>"; ?>
                <?php if($image_err) echo "<li>$image_err</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Nama Paket <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                <div class="form-group mt-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="price" class="form-control" value="<?php echo htmlspecialchars(number_format((float)$price, 0, ',', '.')); ?>" required>
                    <small class="text-muted">Contoh: 500.000</small>
                </div>
                <div class="form-group mt-3">
                    <label>Urutan Tampil</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
                </div>
                <div class="form-group mt-3">
                    <label>Gambar</label><br>
                    <?php if(!empty($image_path)): ?>
                        <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($image_path); ?>" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    <?php endif; ?>
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
                    <input type="file" name="image" class="form-control-file">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
            </div>
        </div>
        <div class="mt-4 border-top pt-3">
            <button type="submit" class="btn btn-primary">Simpan Paket</button>
            <a href="mcu_packages.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>


<?php
$mysqli->close();
require_once 'layout/footer.php';
?>