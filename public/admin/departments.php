<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Menangani Penghapusan Data
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

// Query yang sudah diperbaiki (Hanya mengambil kolom yang benar-benar ada di database)
// Sesuai file .sql: id, name, category, description, icon_path, icon_hover_path, display_order
$sql = "SELECT id, name, category, icon_path, display_order, description FROM departments ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    .page-header { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--jhc-red-dark); margin-bottom: 2rem; }
    .btn-add { background: var(--jhc-gradient); color: white; border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 600; text-decoration: none; border: none; transition: 0.3s; }
    .btn-add:hover { opacity: 0.9; color: white; box-shadow: 0 4px 10px rgba(138, 48, 51, 0.3); }
    .badge-layanan { background-color: #ffebee; color: #8a3033; border: 1px solid #ffcdd2; padding: 0.5em 1em; border-radius: 50px; }
    .badge-poli { background-color: #e3f2fd; color: #002855; border: 1px solid #bbdefb; padding: 0.5em 1em; border-radius: 50px; }
    .icon-preview { width: 40px; height: 40px; object-fit: contain; background: #f1f1f1; border-radius: 8px; padding: 5px; }
</style>

<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-clinic-medical me-2" style="color:#8a3033;"></i> Departments & Services</h3>
            <p class="text-muted mb-0 small">Kelola data poliklinik dan layanan unggulan RS JHC.</p>
        </div>
        <a href="department_edit.php" class="btn-add"><i class="fas fa-plus me-2"></i> Add New</a>
    </div>

    <?php if (isset($_GET['deleted']) || isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4">
            <i class="fas fa-check-circle me-2"></i> Berhasil memperbarui data!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="text-center" style="width: 8%;">Order</th>
                            <th style="width: 10%;">Icon</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $row['display_order']; ?></td>
                                    <td class="text-center">
                                        <img src="../<?= !empty($row['icon_path']) ? htmlspecialchars($row['icon_path']) : 'assets/img/icons/heart.png'; ?>" class="icon-preview">
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <span class="<?= ($row['category'] == 'Layanan') ? 'badge-layanan' : 'badge-poli'; ?>">
                                            <?= $row['category']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                            <a href="departments.php?delete=<?= $row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus data ini?');" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data departemen.</td></tr>
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