<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php untuk mencegah error header) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $sql = "DELETE FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Pengalihan sukses menggunakan PHP header (Aman karena belum ada output HTML)
            header("location: careers.php?deleted=true");
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

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
    .main-wrapper {
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

    /* Tombol Utama Gradasi JHC */
    .btn-jhc-main { 
        background: var(--jhc-gradient) !important; 
        color: white !important; 
        border-radius: 12px !important; 
        padding: 10px 24px !important; 
        font-weight: 700; 
        text-decoration: none; 
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
    }
    .btn-jhc-main:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4); 
        opacity: 0.95;
    }

    /* Tabel Styling sesuai referensi visual */
    .table thead th { 
        background-color: #f8f9fa; 
        color: #6c757d; 
        font-weight: 700; 
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
    
    .status-badge { padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .status-open { background-color: #e6f4ea; color: #1e7e34; }
    .status-closed { background-color: #fceaea; color: #c53030; }

    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Karir</h3>
                <p class="text-muted small mb-0">Kelola lowongan pekerjaan dan peluang karir di RS JHC Tasikmalaya.</p>
            </div>
            <a href="career_edit.php" class="btn btn-jhc-main"><i class="fas fa-plus me-2"></i> Tambah Lowongan</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4" role="alert">
                <i class="fas fa-trash-alt me-2"></i> Lowongan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> Data berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="ps-4">Judul Pekerjaan</th>
                        <th>Lokasi</th>
                        <th class="text-center">Status</th>
                        <th>Tanggal Posting</th>
                        <th class="text-center">Aksi</th>
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
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="career_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="careers.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?');" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
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

<?php require_once 'layout/footer.php'; ?>