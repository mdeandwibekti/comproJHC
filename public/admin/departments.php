<?php
require_once "../../config.php";
require_once 'layout/header.php';

if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='departments.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data termasuk category
$sql = "SELECT id, name, category, icon_path, display_order FROM departments ORDER BY category DESC, display_order ASC";
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
    }
    
    .table tbody tr:hover { background-color: #fff5f5; }
    
    .badge-category { padding: 0.5em 1em; border-radius: 50px; font-weight: 500; font-size: 0.8rem; }
    .badge-layanan { background-color: #ffebee; color: var(--primary-red); border: 1px solid #ffcdd2; }
    .badge-poli { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

    .btn-add {
        background-color: var(--primary-red); color: white; border-radius: 50px;
        padding: 0.5rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.3s;
    }
    .btn-add:hover { background-color: #b71c1c; color: white; box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3); }

    .icon-preview {
        width: 40px; height: 40px; object-fit: contain;
        background: #f1f1f1; border-radius: 8px; padding: 5px;
    }
    
    .action-btn {
        width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; text-decoration: none; transition: 0.2s;
    }
    .btn-action {
        padding: 6px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    /* Tombol Edit: Biru Muda agar kontras tapi tidak 'bahaya' */
    .btn-edit-action {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    .btn-edit-action:hover {
        background-color: #1976d2;
        color: white;
        box-shadow: 0 3px 8px rgba(25, 118, 210, 0.2);
    }

    /* Tombol Hapus: Merah Muda agar jelas 'bahaya' */
    .btn-del-action {
        background-color: #ffebee;
        color: #c62828;
    }
    .btn-del-action:hover {
        background-color: #c62828;
        color: white;
        box-shadow: 0 3px 8px rgba(198, 40, 40, 0.2);
    }

    .btn-action i { margin-right: 6px; font-size: 0.8rem; }
</style>

<div class="container-fluid py-4">
    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-clinic-medical me-2 text-danger"></i> Departments & Services</h3>
            <p class="text-muted mb-0 small">Manage hospital departments, polyclinics, and featured services.</p>
        </div>
        <a href="department_edit.php" class="btn-add">
            <i class="fas fa-plus me-2"></i> Add New
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Item deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Item saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 8%;">Order</th>
                            <th style="width: 10%;">Icon</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-center" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?php echo htmlspecialchars($row['display_order']); ?></td>
                                    
                                    <td class="text-center">
                                        <?php if (!empty($row['icon_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($row['icon_path']); ?>" class="icon-preview" alt="Icon">
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>

                                    <td>
                                        <?php if($row['category'] == 'Layanan'): ?>
                                            <span class="badge badge-category badge-layanan"><i class="fas fa-star me-1"></i> Layanan</span>
                                        <?php else: ?>
                                            <span class="badge badge-category badge-poli"><i class="fas fa-stethoscope me-1"></i> Poliklinik</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="department_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit-action">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="departments.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                                    No departments or services found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>