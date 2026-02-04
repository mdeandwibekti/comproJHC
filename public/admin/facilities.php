<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete Logic
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='facilities.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch Data
$sql = "SELECT * FROM facilities ORDER BY display_order ASC";
$result = $mysqli->query($sql);
?>

<style>
    :root { --primary-red: #D32F2F; --light-bg: #f8f9fa; }
    
    .page-header {
        background: white; padding: 1.5rem; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }
    
    .main-card {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa; border-bottom: 2px solid #eee;
        color: #444; font-weight: 600; text-transform: uppercase; font-size: 0.85rem;
        vertical-align: middle;
    }
    
    .table tbody tr:hover { background-color: #fff5f5; transition: 0.2s; }
    
    .btn-add {
        background-color: var(--primary-red); color: white; border-radius: 50px;
        padding: 0.6rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(211, 47, 47, 0.2);
    }
    .btn-add:hover { background-color: #b71c1c; color: white; transform: translateY(-2px); }

    .facility-img {
        width: 80px; height: 60px; object-fit: cover;
        border-radius: 6px; border: 1px solid #eee;
    }

    /* Tombol Aksi */
    .btn-action {
        padding: 6px 14px; font-size: 0.85rem; font-weight: 600; border-radius: 6px;
        text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;
    }
    .btn-edit-action { background-color: #e3f2fd; color: #1976d2; }
    .btn-edit-action:hover { background-color: #1976d2; color: white; box-shadow: 0 3px 8px rgba(25, 118, 210, 0.2); }
    
    .btn-del-action { background-color: #ffebee; color: #c62828; }
    .btn-del-action:hover { background-color: #c62828; color: white; box-shadow: 0 3px 8px rgba(198, 40, 40, 0.2); }
    
    .btn-action i { margin-right: 6px; font-size: 0.8rem; }
</style>

<div class="container-fluid py-4">
    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-building me-2 text-danger"></i> Manage Facilities</h3>
            <p class="text-muted mb-0 small">Manage hospital rooms, equipment, and public areas.</p>
        </div>
        <a href="facility_edit.php" class="btn-add">
            <i class="fas fa-plus me-2"></i> Add New Facility
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Facility deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Order</th>
                            <th class="text-center" style="width: 15%;">Image</th>
                            <th style="width: 25%;">Name</th>
                            <th style="width: 30%;">Description</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?php echo htmlspecialchars($row['display_order']); ?></td>
                                    
                                    <td class="text-center">
                                        <?php 
                                        $imgSrc = !empty($row['image_path']) ? "../" . htmlspecialchars($row['image_path']) : "";
                                        if($imgSrc): ?>
                                            <img src="<?php echo $imgSrc; ?>" class="facility-img" alt="Facility">
                                        <?php else: ?>
                                            <div class="facility-img d-flex align-items-center justify-content-center bg-light text-muted">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                    
                                    <td class="text-muted small">
                                        <?php 
                                        $desc = htmlspecialchars($row['description']);
                                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc; 
                                        ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="facility_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit-action">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="facilities.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Are you sure you want to delete this facility?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-building fa-3x mb-3 opacity-25"></i><br>
                                    No facilities found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>