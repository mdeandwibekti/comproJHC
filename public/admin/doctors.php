<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='doctors.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch doctors with department name
$sql = "SELECT d.id, d.name, d.specialty, d.photo_path, d.is_featured, dep.name as department_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY d.name ASC";
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
    
    /* Tombol Add New */
    .btn-add {
        background-color: var(--primary-red); color: white; border-radius: 50px;
        padding: 0.6rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(211, 47, 47, 0.2);
    }
    .btn-add:hover { background-color: #b71c1c; color: white; transform: translateY(-2px); }

    /* Avatar Foto Dokter */
    .doctor-avatar {
        width: 50px; height: 50px; object-fit: cover;
        border-radius: 50%; border: 2px solid #eee;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Badge Status */
    .badge-featured { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 5px 10px; border-radius: 20px; font-weight: 500; }
    .badge-standard { background-color: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; padding: 5px 10px; border-radius: 20px; font-weight: 500; }

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
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-user-md me-2 text-danger"></i> Manage Doctors</h3>
            <p class="text-muted mb-0 small">Manage doctor profiles, specialties, and schedules.</p>
        </div>
        <a href="doctor_edit.php" class="btn-add">
            <i class="fas fa-plus me-2"></i> Add New Doctor
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Doctor deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Doctor data saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Photo</th>
                            <th style="width: 20%;">Name</th>
                            <th style="width: 20%;">Specialty</th>
                            <th style="width: 20%;">Department</th>
                            <th class="text-center" style="width: 10%;">Featured</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php 
                                        $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'assets/img/gallery/default-avatar.png'; 
                                        ?>
                                        <img src="../<?php echo htmlspecialchars($photo); ?>" class="doctor-avatar" alt="Doc" onerror="this.src='../assets/img/gallery/default-avatar.png';">
                                    </td>
                                    
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($row['specialty']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($row['department_name'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($row['is_featured']): ?>
                                            <span class="badge-featured"><i class="fas fa-check me-1"></i> Yes</span>
                                        <?php else: ?>
                                            <span class="badge-standard">No</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="doctor_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit-action">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="doctors.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Are you sure you want to delete this doctor?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-md fa-3x mb-3 opacity-25"></i><br>
                                    No doctors found.
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