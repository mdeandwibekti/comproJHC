<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Redirect aman menggunakan PHP header karena belum ada output HTML
            header("location: departments.php?deleted=true");
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data departemen (Hanya kolom yang ada di database)
$sql = "SELECT id, name, category, icon_path, display_order, description FROM departments ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 4px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Tombol Utama dengan Gradasi */
    .btn-jhc-add { 
        background: var(--jhc-gradient) !important; 
        color: white !important; 
        border-radius: 12px !important; 
        padding: 10px 24px !important; 
        font-weight: 700; 
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
    }
    .btn-jhc-add:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4); 
        opacity: 0.95;
    }

    /* Tabel Styling sesuai referensi visual */
    .table thead th { 
        background-color: #f8f9fa; 
        color: #6c757d; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px; 
        border: none;
        padding: 15px;
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1;
        font-size: 0.9rem;
    }

    .badge-layanan { background-color: #fceaea; color: #c53030; padding: 6px 14px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    .badge-poli { background-color: #e3f2fd; color: #1976d2; padding: 6px 14px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    
    .icon-preview { width: 45px; height: 45px; object-fit: contain; background: #f8f9fa; border-radius: 10px; padding: 8px; border: 1px solid #eee; }
    
    .btn-action { border-radius: 8px; font-weight: 600; padding: 6px 12px; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Departments & Services</h3>
                <p class="text-muted small mb-0">Kelola data poliklinik dan layanan unggulan RS JHC Tasikmalaya.</p>
            </div>
            <a href="department_edit.php" class="btn btn-jhc-add"><i class="fas fa-plus me-2"></i> Add New</a>
        </div>

        <?php if (isset($_GET['deleted']) || isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Data berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Order</th>
                        <th style="width: 100px;">Icon</th>
                        <th>Department Name</th>
                        <th>Category</th>
                        <th class="text-center" style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $row['display_order']; ?></td>
                                <td>
                                    <img src="../<?= !empty($row['icon_path']) ? htmlspecialchars($row['icon_path']) : 'assets/img/icons/heart.png'; ?>" class="icon-preview shadow-sm">
                                </td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <span class="<?= ($row['category'] == 'Layanan') ? 'badge-layanan' : 'badge-poli'; ?>">
                                        <i class="fas <?= ($row['category'] == 'Layanan') ? 'fa-star' : 'fa-stethoscope'; ?> me-1 small"></i> <?= $row['category']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="departments.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted italic">Belum ada data departemen yang terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>