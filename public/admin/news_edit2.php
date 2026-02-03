<?php
require_once "../../config.php";
require_once 'layout/header.php';

$title = $content = $image_path = $category = "";
$page_title = "Add New Article";
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Article";
    $sql = "SELECT * FROM news2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $title = $row['title'];
                $content = $row['content'];
                $image_path = $row['image_path'];
                $category = $row['category'];
            }
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);
    $image_path = $_POST['current_image'];

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $new_filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
        $upload_dir = "../assets/img/news/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            $image_path = "assets/img/news/" . $new_filename;
        }
    }

    if (empty($id)) {
        $sql = "INSERT INTO news2 (title, content, image_path, category) VALUES (?, ?, ?, ?)";
    } else {
        $sql = "UPDATE news2 SET title = ?, content = ?, image_path = ?, category = ? WHERE id = ?";
    }

    if ($stmt = $mysqli->prepare($sql)) {
        if (empty($id)) {
            $stmt->bind_param("ssss", $title, $content, $image_path, $category);
        } else {
            $stmt->bind_param("ssssi", $title, $content, $image_path, $category, $id);
        }
        $stmt->execute();
        header("location: news2.php");
        exit();
    }
}
?>
<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3><hr>
    <form action="news_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?php echo $title; ?>"></div>
        <div class="form-group mt-3"><label>Category</label><input type="text" name="category" class="form-control" value="<?php echo $category; ?>"></div>
        <div class="form-group mt-3"><label>Content</label><textarea name="content" class="form-control" rows="10"><?php echo $content; ?></textarea></div>
        <div class="form-group mt-3">
            <label>Image</label><br>
            <?php if($image_path): ?><img src="../<?php echo $image_path; ?>" width="200" class="img-thumbnail mb-2"><br><?php endif; ?>
            <input type="hidden" name="current_image" value="<?php echo $image_path; ?>">
            <input type="file" name="image" class="form-control-file">
        </div>
        <div class="form-group mt-4"><input type="submit" class="btn btn-primary" value="Save"><a href="news2.php" class="btn btn-secondary">Cancel</a></div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>