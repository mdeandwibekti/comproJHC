<?php
require_once "../../config.php";
require_once 'layout/header.php';

$title = $content = $image_path_360 = "";
$title_err = $content_err = $image_path_360_err = "";

// Ambil data saat ini (agar form tidak kosong saat pertama dibuka)
$sql = "SELECT title, content, image_path_360 FROM page_virtual_room WHERE id = 1";
$result = $mysqli->query($sql);
if($result && $result->num_rows == 1){
    $row = $result->fetch_assoc();
    $title = $row['title'];
    $content = $row['content'];
    $image_path_360 = $row['image_path_360'];
} else {
    // Jika data id=1 belum ada, buat default dummy (opsional, tapi lebih baik insert via SQL dulu)
    $title = "Virtual Room Default";
    $content = "Content goes here...";
}

// Proses Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validasi Title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Validasi Content
    if (empty(trim($_POST["content"]))) {
        $content_err = "Please enter content.";
    } else {
        $content = trim($_POST["content"]);
    }

    // Ambil path gambar lama
    $image_path_360 = trim($_POST["current_image_360"]);

    // Handle Upload Gambar Baru
    if (isset($_FILES["image_360"]) && $_FILES["image_360"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["image_360"]["name"];
        $filetype = $_FILES["image_360"]["type"];
        $filesize = $_FILES["image_360"]["size"];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Cek ekstensi file
        if (!array_key_exists($ext, $allowed)) {
            $image_path_360_err = "Error: Please select a valid image format (JPG, JPEG, PNG).";
        }

        // Cek ukuran file (Max 10MB)
        $maxsize = 10 * 1024 * 1024; 
        if ($filesize > $maxsize) {
            $image_path_360_err = "Error: File size is larger than the allowed limit (10MB).";
        }

        if (empty($image_path_360_err)) {
            // Rename file agar unik
            $new_filename = uniqid() . "_360." . $ext;
            $upload_dir = "../assets/img/virtual_tour/";
            
            // Buat folder jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES["image_360"]["tmp_name"], $upload_path)) {
                // Path untuk database
                $image_path_360 = "assets/img/virtual_tour/" . $new_filename;
            } else {
                $image_path_360_err = "Error: There was a problem uploading your file. Please try again.";
            }
        }
    }

    // Update Database jika tidak ada error
    if (empty($title_err) && empty($content_err) && empty($image_path_360_err)) {
        // Gunakan tabel 'page_virtual_room' (bukan page_virtual_room2)
        $sql = "UPDATE page_virtual_room SET title = ?, content = ?, image_path_360 = ? WHERE id = 1";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sss", $title, $content, $image_path_360);
            
            if ($stmt->execute()) {
                // Redirect ke file yang benar (virtual_room.php)
                header("location: virtual_room.php?saved=true");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later. Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Oops! Something went wrong. Please try again later. Failed to prepare statement: " . $mysqli->error;
        }
    }
}
?>

<div class="container-fluid">
    <h3>Edit Virtual Room Page</h3><hr>
    <?php if(isset($_GET['saved'])) echo "<div class='alert alert-success'>Content saved successfully.</div>"; ?>
    
    <form action="virtual_room.php" method="post" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
            <span class="invalid-feedback"><?php echo $title_err; ?></span>
        </div>
        
        <div class="form-group mt-3">
            <label>Content</label>
            <textarea name="content" class="form-control <?php echo (!empty($content_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo htmlspecialchars($content); ?></textarea>
            <span class="invalid-feedback"><?php echo $content_err; ?></span>
        </div>
        
        <div class="form-group mt-3">
            <label>Current 360 Image</label><br>
            <?php if (!empty($image_path_360)): ?>
                <img src="../<?php echo htmlspecialchars($image_path_360); ?>" width="200" class="img-thumbnail mb-2"><br>
            <?php else: ?>
                <p class="text-muted">No 360 image uploaded.</p>
            <?php endif; ?>
            <input type="hidden" name="current_image_360" value="<?php echo htmlspecialchars($image_path_360); ?>">
        </div>
        
        <div class="form-group mt-3">
            <label>Upload New 360 Image (JPG, JPEG, PNG - Max 10MB)</label>
            <input type="file" name="image_360" class="form-control-file <?php echo (!empty($image_path_360_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $image_path_360_err; ?></span>
        </div>
        
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'layout/footer.php'; ?>