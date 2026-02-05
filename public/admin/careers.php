<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Logika Menghapus Data Karir
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='careers.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Gagal menghapus data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data karir dari database
$sql = "SELECT id, job_title, location, status, post_date FROM careers ORDER BY post_date DESC";
$result = $mysqli->query($sql);
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .page-header { 
        background: white; 
        padding: 1.5rem; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        border-left: 5px solid var(--jhc-red-dark); 
        margin-bottom: 2rem; 
    }

    .btn-jhc-add { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 50px; 
        padding: 0.5rem 1.5rem; 
        font-weight: 600; 
        text-decoration: none; 
        border: none;
        transition: 0.3s; 
    }
    .btn-jhc-add:hover { 
        opacity: 0.9; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(138, 48, 51, 0.3); 
    }

    .main-card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .table thead th { background-color: #f8f9fa; color: #555; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
    
    .status-badge { padding: 0.4em 1em; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .status-open { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .status-closed { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-briefcase me-2" style="color: var(--jhc-red-dark);"></i> Career Management</h3>
            <p class="text-muted mb-0 small">Kelola lowongan pekerjaan dan peluang karir di RS JHC.</p>
        </div>
        <a href="career_edit.php" class="btn-jhc-add"><i class="fas fa-plus me-2"></i> Add New Job</a>
    </div>

    <?php if (isset($_GET['deleted']) || isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4">
            <i class="fas fa-check-circle me-2"></i> Operasi berhasil dilakukan!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead>
                        <tr>
                            <th class="ps-4">Job Title</th>
                            <th>Location</th>
                            <th class="text-center">Status</th>
                            <th>Posted Date</th>
                            <th class="text-center" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['job_title']); ?></div>
                                    </td>
                                    <td><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?= htmlspecialchars($row['location'] ?? 'Tasikmalaya'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $statusClass = (strtolower($row['status']) == 'open') ? 'status-open' : 'status-closed';
                                        $statusLabel = strtoupper($row['status'] ?? 'OPEN');
                                        ?>
                                        <span class="status-badge <?= $statusClass; ?>"><?= $statusLabel; ?></span>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($row['post_date'])); ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group">
                                            <a href="career_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="careers.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?');" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted italic">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                    Belum ada lowongan pekerjaan yang terdaftar.
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