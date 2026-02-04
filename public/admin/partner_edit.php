<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Inisialisasi Variabel
$name = $url = $logo_path = "";
$name_err = $logo_err = "";
$page_title = "Add New Partner";
$id = 0;

// Cek Mode Edit
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Partner";
}

// --- PROSES SUBMIT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    // 1. Validasi Nama
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    $url = trim($_POST["url"]);
    $logo_path = $_POST['current_logo'] ?? '';

    // 2. Handle Upload Logo
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES["logo"]["name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '-partner.' . $ext;
            $upload_dir = "../assets/img/partners/";
            
            // Buat folder jika belum ada
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_path)) {
                // Hapus logo lama jika ada & berhasil upload baru
                if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                    unlink("../" . $logo_path);
                }
                $logo_path = "assets/img/partners/" . $new_filename;
            } else {
                $logo_err = "Gagal mengupload gambar.";
            }
        } else {
            $logo_err = "Format file tidak valid (Hanya JPG, PNG, GIF).";
        }
    }

    // 3. Simpan ke Database
    if (empty($name_err) && empty($logo_err)) {
        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO partners (name, url, logo_path) VALUES (?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sss", $name, $url, $logo_path);
            }
        } else {
            // UPDATE
            $sql = "UPDATE partners SET name = ?, url = ?, logo_path = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sssi", $name, $url, $logo_path, $id);
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                // Redirect ke partners.php (bukan partners2.php)
                header("location: partners.php?saved=true");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error SQL: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error Prepare: " . $mysqli->error . "</div>";
        }
    }
}

// --- AMBIL DATA UNTUK EDIT ---
if ($_SERVER["REQUEST_METHOD"] != "POST" && !empty($id)) {
    $sql = "SELECT name, url, logo_path FROM partners WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $url, $logo_path);
            $stmt->fetch();
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="form-group">
            <label>Partner Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        
        <div class="form-group mt-3">
            <label>Website URL</label>
            <input type="text" name="url" class="form-control" value="<?php echo htmlspecialchars($url); ?>" placeholder="https://example.com">
        </div>
        
        <div class="form-group mt-3">
            <label>Logo</label><br>
            <?php if(!empty($logo_path)): ?>
                <div class="mb-2 p-2 border rounded d-inline-block bg-light">
                    <img src="../<?php echo htmlspecialchars($logo_path); ?>" style="height: 80px; object-fit: contain;">
                </div>
                <br>
            <?php endif; ?>
            
            <input type="hidden" name="current_logo" value="<?php echo htmlspecialchars($logo_path); ?>">
            <input type="file" name="logo" class="form-control-file <?php echo (!empty($logo_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback d-block"><?php echo $logo_err; ?></span>
            <small class="text-muted">Recommended size: 200x100 pixels (PNG/JPG).</small>
        </div>

        <div class="form-group mt-4 pt-3 border-top">
            <input type="submit" class="btn btn-primary" value="Save Partner">
            <a href="partners.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>