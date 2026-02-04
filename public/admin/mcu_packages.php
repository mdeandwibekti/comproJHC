<?php
require_once "../../config.php";
require_once 'layout/header.php';

$page_title = "Manage MCU Packages";

// --- HANDLE DELETE ACTION ---
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // 1. Ambil path gambar lama sebelum menghapus data
    $sql_select = "SELECT image_path FROM mcu_packages WHERE id = ?";
    
    // Pengecekan Error Prepare
    if ($stmt = $mysqli->prepare($sql_select)) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $package_to_delete = $result->fetch_assoc();
        }
        $stmt->close(); // Tutup statement select

        // Hapus file fisik jika ada
        if ($package_to_delete && !empty($package_to_delete['image_path'])) {
            $file_path = '../' . $package_to_delete['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    // 2. Hapus data dari database
    $sql_delete = "DELETE FROM mcu_packages WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql_delete)) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            // Redirect javascript agar URL bersih
            echo "<script>window.location.href='mcu_packages.php?deleted=true';</script>";
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error deleting record: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error Preparing Delete: " . $mysqli->error . "</div>";
    }
}

// --- FETCH DATA ---
$packages = [];
$sql = "SELECT id, image_path, title, price, display_order FROM mcu_packages ORDER BY display_order ASC";
$result = $mysqli->query($sql);

if (!$result) {
    // Tampilkan error jika tabel mcu_packages tidak ditemukan
    echo "<div class='alert alert-danger'>Error Query: " . $mysqli->error . "<br>Pastikan tabel <b>mcu_packages</b> sudah dibuat.</div>";
} else {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3>
    <hr>
    <a href="mcu_package_edit.php" class="btn btn-primary mb-3">Add New MCU Package</a>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">MCU Package saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">MCU Package deleted successfully!</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 150px;">Image</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th style="width: 80px;">Order</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($packages)): ?>
                    <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?php echo $package['id']; ?></td>
                            <td class="text-center">
                                <?php if (!empty($package['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($package['image_path']); ?>" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                <?php else: ?>
                                    <span class="text-muted small">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($package['title']); ?></td>
                            <td>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                            <td><?php echo $package['display_order']; ?></td>
                            <td>
                                <a href="mcu_package_edit.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="mcu_packages.php?delete_id=<?php echo $package['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this MCU package?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No MCU packages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>