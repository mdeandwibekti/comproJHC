<?php
require_once "../../config.php";

// --- LOGIKA PENGHAPUSAN ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Opsional: Ambil path foto untuk dihapus dari server sebelum record di DB dihapus
    $stmt_img = $mysqli->prepare("SELECT photo_path FROM doctors WHERE id = ?");
    $stmt_img->bind_param("i", $id_to_delete);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result()->fetch_assoc();

    $sql = "DELETE FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Hapus file fisik jika bukan default
            if (!empty($res_img['photo_path']) && file_exists("../" . $res_img['photo_path'])) {
                @unlink("../" . $res_img['photo_path']);
            }
            header("location: doctors.php?deleted=true");
            exit();
        }
        $stmt->close();
    }
}

// --- FETCH DATA DOKTER (DENGAN RELASI DEPARTEMEN) ---
// Perbaikan: Menggunakan LEFT JOIN agar d.department_id berelasi dengan dep.id
$sql = "SELECT d.*, dep.name as department_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY dep.name ASC, d.name ASC"; // Diurutkan berdasarkan Poli dulu baru Nama
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    /* ... (Style CSS Anda) ... */
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    .main-wrapper { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05); }
    .page-header-jhc { border-left: 4px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .btn-jhc-main { background: var(--jhc-gradient) !important; color: white !important; border-radius: 12px !important; padding: 10px 24px !important; font-weight: 700; border: none !important; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); transition: 0.3s; }
    .doctor-avatar { width: 48px; height: 48px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
    .badge-dept { background-color: #f8f9fa; color: #333; border: 1px solid #dee2e6; border-radius: 50px; padding: 5px 12px; font-weight: 600; font-size: 0.75rem; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manage Dokter</h3>
                <p class="text-muted small mb-0">Kelola profil dokter dan hubungkan dengan Poliklinik yang sesuai.</p>
            </div>
            <a href="doctor_edit.php" class="btn btn-jhc-main"><i class="fas fa-plus me-2"></i> Tambah Dokter</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Data dokter berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">Foto</th>
                        <th>Dokter & Spesialisasi</th>
                        <th>Poliklinik / Departemen</th>
                        <th style="width: 200px;">Jadwal Praktik</th>
                        <th class="text-center">Unggulan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center">
                                    <?php $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'assets/img/gallery/default-avatar.png'; ?>
                                    <img src="../<?= htmlspecialchars($photo); ?>" class="doctor-avatar" onerror="this.src='../assets/img/gallery/default-avatar.png';">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-danger small fw-bold"><?= htmlspecialchars($row['specialty']); ?></div>
                                </td>
                                <td>
                                    <span class="badge-dept">
                                        <i class="fas fa-hospital-user me-1 text-primary"></i>
                                        <?= htmlspecialchars($row['department_name'] ?? 'Belum Diatur'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <?php if (!empty($row['schedule'])): ?>
                                            <i class="fas fa-clock me-1 text-warning"></i> <?= htmlspecialchars($row['schedule']); ?>
                                        <?php else: ?>
                                            <span class="fst-italic text-light">Belum diatur</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['is_featured']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
                                            <i class="fas fa-star me-1"></i>Ya
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="doctor_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="doctors.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Hapus data dokter ini?');" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data dokter.</td>
                        </tr>
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