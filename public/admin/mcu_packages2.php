<?php
require_once "../../config.php";
require_once 'layout/header.php';

$page_title = "Manage MCU Packages";

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Get image path to delete file from server
    $stmt = $mysqli->prepare("FROM mcu_packages2 WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package_to_delete = $result->fetch_assoc();
    $stmt->close();

    if ($package_to_delete && file_exists('../' . $package_to_delete['image_path'])) {
        unlink('../' . $package_to_delete['image_path']);
    }

    $stmt = $mysqli->prepare("DELETE FROM mcu_packages2 WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("location: mcu_packages2.php?deleted=true");
        exit();
    } else {
        echo "Error deleting record: " . $mysqli->error;
    }
    $stmt->close();
}

// Fetch all MCU packages
$packages = [];
$sql = "SELECT id, image_path, title, price, display_order FROM mcu_packages2 ORDER BY display_order ASC";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
}

?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <a href="mcu_package_edit2.php" class="btn btn-primary mb-3">Add New MCU Package</a>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">MCU Package saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">MCU Package deleted successfully!</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($packages)): ?>
                    <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?php echo $package['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($package['image_path']); ?>" width="100"></td>
                            <td><?php echo htmlspecialchars($package['title']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($package['price'], 2)); ?></td>
                            <td><?php echo $package['display_order']; ?></td>
                            <td>
                                <a href="mcu_package_edit2.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="mcu_packages2.php?delete_id=<?php echo $package['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this MCU package?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No MCU packages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>