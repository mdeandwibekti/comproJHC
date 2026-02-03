<?php
require_once "../../config.php";
require_once 'layout/header.php';

$title = $description = $image_path = "";
$price = 0.00;
$display_order = 0;
$title_err = $price_err = $image_err = "";
$page_title = "Add New MCU Package";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit MCU Package";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    $description = trim($_POST["description"]);
    $display_order = $_POST['display_order'];
    $image_path = $_POST['current_image'];

    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter a price.";
    } else {
        $price = filter_var(trim($_POST["price"]), FILTER_VALIDATE_FLOAT);
        if ($price === false) {
            $price_err = "Please enter a valid price.";
        }
    }

    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
        $upload_dir = "../assets/img/mcu_packages/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $upload_path = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
            $image_path = "assets/img/mcu_packages/" . $new_filename;
        } else {
            $image_err = "Error uploading image.";
        }
    } else if (empty($image_path) && empty($id)) { // Image is required for new packages
        $image_err = "Please upload an image.";
    }

    if (empty($title_err) && empty($price_err) && empty($image_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO mcu_packages2 (image_path, title, description, price, display_order) VALUES (?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE mcu_packages2 SET image_path = ?, title = ?, description = ?, price = ?, display_order = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("ssdii", $image_path, $title, $description, $price, $display_order);
            } else {
                $stmt->bind_param("sssdii", $image_path, $title, $description, $price, $display_order, $id);
            }
            
            if ($stmt->execute()) {
                header("location: mcu_packages2.php?saved=true");
                exit();
            } else {
                echo "Something went wrong. Please try again later." . $mysqli->error;
            }
            $stmt->close();
        }
    }
}

if (!empty($id)) {
    $sql = "SELECT image_path, title, description, price, display_order FROM mcu_packages2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($image_path, $title, $description, $price, $display_order);
            $stmt->fetch();
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <form action="mcu_package_edit2.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
            <span class="invalid-feedback"><?php echo $title_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="form-group mt-3">
            <label>Price</label>
            <input type="text" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($price); ?>">
            <span class="invalid-feedback"><?php echo $price_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Display Order</label>
            <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
        </div>
        <div class="form-group mt-3">
            <label>Image</label><br>
            <?php if($image_path): ?><img src="../<?php echo $image_path; ?>" width="200" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">
            <input type="file" name="image" class="form-control-file <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $image_err; ?></span>
        </div>

        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="mcu_packages2.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>