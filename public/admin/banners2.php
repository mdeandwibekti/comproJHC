<?php
require_once "../../config.php";
require_once 'layout/header.php';

$page_title = "Manage Banners";

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $stmt = $mysqli->prepare("SELECT image_path FROM banners WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner_to_delete = $result->fetch_assoc();
    $stmt->close();

    if ($banner_to_delete && file_exists('../' . $banner_to_delete['image_path'])) {
        unlink('../' . $banner_to_delete['image_path']);
    }

    $stmt = $mysqli->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("location: banners.php?deleted=true");
        exit();
    } else {
        echo "Error deleting record: " . $mysqli->error;
    }
    $stmt->close();
}

$banners = [];
$sql = "SELECT id, image_path, title, description, display_order FROM banners ORDER BY display_order ASC";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $banners[] = $row;
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <a href="banner_edit.php" class="btn btn-primary mb-3">Add New Banner</a>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Banner saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Banner deleted successfully!</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><?php echo $banner['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($banner['image_path']); ?>" width="100"></td>
                            <td><?php echo htmlspecialchars($banner['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($banner['description'], 0, 100)); ?>...</td>
                            <td><?php echo $banner['display_order']; ?></td>
                            <td>
                                <a href="banner_edit.php?id=<?php echo $banner['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="banners.php?delete_id=<?php echo $banner['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this banner?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No banners found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>