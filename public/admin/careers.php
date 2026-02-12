<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS DATA (DELETE) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Hapus data berdasarkan ID
    $sql = "DELETE FROM careers WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Redirect agar URL bersih
            header("location: careers.php?msg=deleted");
            exit();
        } else {
            $error_msg = "Gagal menghapus data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- 2. AMBIL DATA (READ) ---
// Mengambil semua kolom yang diperlukan, diurutkan dari yang terbaru
$sql = "SELECT id, job_title, location, status, post_date, deadline FROM careers ORDER BY post_date DESC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Wrapper Utama */
    .main-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    /* Header Halaman */
    .page-header-jhc {
        border-left: 4px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Tombol Utama */
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
        color: white !important;
    }

    /* Tabel Styling */
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

    /* Badge Deadline */
    .deadline-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    .deadline-safe { background-color: #f0f7ff; color: #0056b3; }
    .deadline-urgent { background-color: #fff4e6; color: #d97706; }
    .deadline-passed { background-color: #f4f4f5; color: #71717a; text-decoration: line-through; }
        
    /* Badge Status */
    .status-badge { padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .status-open { background-color: #e6f4ea; color: #1e7e34; }
    .status-closed { background-color: #fceaea; color: #c53030; }

    /* Tombol Aksi Kecil */
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

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4" role="alert">
                <i class="fas fa-trash-alt me-2"></i> Lowongan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
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
                        <th>Batas Pendaftaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['job_title']); ?></div>
                                    <small class="text-muted">Diposting: <?= date('d/m/Y', strtotime($row['post_date'])); ?></small>
                                </td>
                                <td><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?= htmlspecialchars($row['location'] ?? 'Tasikmalaya'); ?></td>
                                <td class="text-center">
                                    <?php 
                                    $statusClass = (strtolower($row['status']) == 'open') ? 'status-open' : 'status-closed';
                                    ?>
                                    <span class="status-badge <?= $statusClass; ?>"><?= strtoupper($row['status']); ?></span>
                                </td>
                                
                                <td>
                                    <?php 
                                    if ($row['deadline']) {
                                        $deadline_ts = strtotime($row['deadline']);
                                        $today_ts = strtotime(date('Y-m-d'));
                                        $diff = ($deadline_ts - $today_ts) / (60 * 60 * 24); // Hitung selisih hari

                                        if ($diff < 0) {
                                            $d_class = "deadline-passed";
                                            $d_text = "Berakhir";
                                        } elseif ($diff <= 7) {
                                            $d_class = "deadline-urgent";
                                            $d_text = ceil($diff) . " Hari Lagi";
                                        } else {
                                            $d_class = "deadline-safe";
                                            $d_text = date('d M Y', $deadline_ts);
                                        }
                                        echo '<span class="deadline-badge ' . $d_class . '"><i class="far fa-clock me-1"></i>' . $d_text . '</span>';
                                    } else {
                                        echo '<span class="text-muted small">-</span>';
                                    }
                                    ?>
                                </td>

                                <td class="text-center pe-4">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="career_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="careers.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?');" title="Hapus"><i class="fas fa-trash-alt"></i></a>
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