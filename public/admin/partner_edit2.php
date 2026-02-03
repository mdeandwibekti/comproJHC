<?php
require_once "../../config.php";
require_once 'layout/header.php';

$name = $url = $logo_path = "";
$name_err = "";
$page_title = "Add New Partner";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Partner";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }
    $url = trim($_POST["url"]);
    $logo_path = $_POST['current_logo'];

    // Handle logo upload
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["logo"]["name"]);
        $upload_dir = "../assets/img/partners/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $upload_path = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_path)) {
            // Delete old logo if it exists
            if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                unlink("../" . $logo_path);
            }
            $logo_path = "assets/img/partners/" . $new_filename;
        }
    }

    if (empty($name_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO partners2 (name, url, logo_path) VALUES (?, ?, ?)";
        } else {
            $sql = "UPDATE partners2 SET name = ?, url = ?, logo_path = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sss", $name, $url, $logo_path);
            } else {
                $stmt->bind_param("sssi", $name, $url, $logo_path, $id);
            }
            
            if ($stmt->execute()) {
                header("location: partners2.php?saved=true");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

if (!empty($id)) {
    $sql = "SELECT name, url, logo_path FROM partners2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->store_result();
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
    <form action="partner_edit2.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="form-group">
            <label>Partner Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Website URL</label>
            <input type="text" name="url" class="form-control" value="<?php echo $url; ?>">
        </div>
        <div class="form-group mt-3">
            <label>Logo</label><br>
            <?php if($logo_path): ?><img src="../<?php echo $logo_path; ?>" width="150" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_logo" value="<?php echo $logo_path; ?>">
            <input type="file" name="logo" class="form-control-file">
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="partners2.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>
