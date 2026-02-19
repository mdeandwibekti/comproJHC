<?php
require_once "../../config.php";

// --- LOGIKA HAPUS DATA (DELETE) TETAP ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $sql = "DELETE FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: careers.php?msg=deleted");
            exit();
        }
        $stmt->close();
    }
}

// --- LOGIKA AMBIL DATA (READ) TETAP ---
$sql = "SELECT id, job_title, location, status, post_date, deadline FROM careers ORDER BY post_date DESC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body { background-color: var(--admin-bg) !important; font-family: 'Inter', sans-serif; }

    /* Breadcrumb Styling */
    .breadcrumb-jhc { font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; font-weight: 500; transition: 0.3s; }
    .breadcrumb-jhc a:hover { color: var(--jhc-red-dark); }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .main-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 40px; border: 1px solid #f1f5f9;
    }

    .page-header-jhc { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 40px; }

    .btn-jhc-main { 
        background: var(--jhc-gradient) !important; color: white !important; 
        border-radius: 14px !important; padding: 12px 28px !important; 
        font-weight: 700; border: none !important;
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); transition: 0.3s; text-decoration: none;
    }
    .btn-jhc-main:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); color: white !important; }

    /* Table Styling Modern */
    .table thead th { 
        background-color: #fcfdfe; color: #94a3b8; text-transform: uppercase; 
        font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; 
        border-bottom: 2px solid #f1f5f9; padding: 20px 15px;
    }

    .table tbody td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #475569; }

    .status-badge { padding: 6px 16px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    .status-open { background-color: #ecfdf5; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.1); }
    .status-closed { background-color: #fef2f2; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); }

    .deadline-badge { padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; }
    .deadline-safe { background-color: #eff6ff; color: #3b82f6; }
    .deadline-urgent { background-color: #fffbeb; color: #d97706; }
    .deadline-passed { background-color: #f4f4f5; color: #94a3b8; text-decoration: line-through; }

    .btn-action-jhc { 
        border-radius: 10px; width: 38px; height: 38px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.2s; border: 1px solid #e2e8f0; background: #fff; text-decoration: none;
    }
    .btn-edit { color: #3b82f6; }
    .btn-edit:hover { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .btn-delete { color: #ef4444; }
    .btn-delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
</style>

<div class="container-fluid py-4">
    <div class="breadcrumb-jhc px-2">
        <a href="dashboard.php">Dashboard</a> 
        <span class="text-muted opacity-50">/</span> 
        <span class="current">Manajemen Karir</span>
    </div>

    <div class="main-wrapper">
        <div class="page-header-jhc d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;">Daftar Lowongan</h2>
                <p class="text-muted small mb-0">Kelola peluang karir profesional di RS JHC Tasikmalaya.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="career_edit.php" class="btn btn-jhc-main">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Lowongan
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-5 mb-4 p-3">
                <div class="d-flex align-items-center"><i class="fas fa-trash-alt me-3 fa-lg"></i> Lowongan pekerjaan telah dihapus.</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="ps-4">Posisi Pekerjaan</th>
                        <th>Penempatan</th>
                        <th class="text-center">Status</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark" style="font-size: 1rem;"><?= htmlspecialchars($row['job_title']); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><i class="far fa-calendar-check me-1"></i> Post: <?= date('d M Y', strtotime($row['post_date'])); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt me-2 text-danger opacity-50"></i>
                                        <span class="fw-500"><?= htmlspecialchars($row['location'] ?? 'Tasikmalaya'); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php $statusClass = (strtolower($row['status']) == 'open') ? 'status-open' : 'status-closed'; ?>
                                    <span class="status-badge <?= $statusClass; ?>"><?= $row['status']; ?></span>
                                </td>
                                <td>
                                    <?php 
                                    if ($row['deadline']) {
                                        $deadline_ts = strtotime($row['deadline']);
                                        $diff = ($deadline_ts - strtotime(date('Y-m-d'))) / 86400;
                                        if ($diff < 0) { $d_class = "deadline-passed"; $d_text = "Closed"; }
                                        elseif ($diff <= 7) { $d_class = "deadline-urgent"; $d_text = ceil($diff) . " Hari Lagi"; }
                                        else { $d_class = "deadline-safe"; $d_text = date('d M Y', $deadline_ts); }
                                        echo '<span class="deadline-badge ' . $d_class . '"><i class="far fa-clock me-2"></i>' . $d_text . '</span>';
                                    } else { echo '<span class="text-muted small">Tanpa Batas</span>'; }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="career_edit.php?id=<?= $row['id']; ?>" class="btn-action-jhc btn-edit" title="Edit"><i class="fas fa-pen-nib"></i></a>
                                        <a href="careers.php?delete=<?= $row['id']; ?>" class="btn-action-jhc btn-delete" onclick="return confirm('Hapus lowongan ini secara permanen?');" title="Hapus"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted italic">Belum ada lowongan pekerjaan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>