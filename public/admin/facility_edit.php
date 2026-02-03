<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi variabel
$name = $description = $image_path = "";
$display_order = 0;
$page_title = "Add New Facility";
$id = null;

// Cek apakah ini mode Edit (ada ID di URL)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Facility";
    
    // Perbaikan: Nama tabel jadi 'facilities'
    $sql = "SELECT * FROM facilities WHERE id = ?";
    
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

// Proses Simpan Data (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $display_order = trim($_POST["display_order"]);
    $image_path = $_POST['current_image']; // Pakai gambar lama jika tidak ada upload baru

    // Proses Upload Gambar Baru
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
        $upload_dir = "../assets/img/gallery/"; // Folder penyimpanan gambar
        
        // Buat folder jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            $image_path = "assets/img/gallery/" . $new_filename;
        }
    }

    // Perbaikan: Query ke tabel 'facilities'
    if (empty($id)) {
        // Mode INSERT (Tambah Baru)
        $sql = "INSERT INTO facilities (name, description, display_order, image_path) VALUES (?, ?, ?, ?)";
    } else {
        // Mode UPDATE (Edit Data)
        $sql = "UPDATE facilities SET name = ?, description = ?, display_order = ?, image_path = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("ssis", $name, $description, $display_order, $image_path);
        } else {
            $stmt->bind_param("ssisi", $name, $description, $display_order, $image_path, $id);
        }
        
        if ($stmt->execute()) {
            // Perbaikan: Redirect ke 'facilities.php' (file list yang benar)
            header("location: facilities.php");
            exit();
        } else {
            echo "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <form action="facility_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        
        <div class="form-group mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>
        
        <div class="form-group mt-3">
            <label>Display Order</label>
            <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars($display_order); ?>">
        </div>
        
        <div class="form-group mt-3">
            <label>Image</label><br>
            <?php if(!empty($image_path)): ?>
                <img src="../<?php echo htmlspecialchars($image_path); ?>" width="200" class="img-thumbnail mb-2"><br>
            <?php endif; ?>
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image_path); ?>">
            <input type="file" name="image" class="form-control-file">
            <small class="text-muted d-block">Biarkan kosong jika tidak ingin mengubah gambar.</small>
        </div>
        
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="facilities.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'layout/footer.php'; ?>