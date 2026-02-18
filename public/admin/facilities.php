<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS DATA ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $stmt_img = $mysqli->prepare("SELECT image_path FROM facilities WHERE id = ?");
    $stmt_img->bind_param("i", $id_to_delete);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result();
    if($row_img = $res_img->fetch_assoc()) {
        if (!empty($row_img['image_path']) && file_exists("../" . $row_img['image_path'])) {
            @unlink("../" . $row_img['image_path']);
        }
    }
    $stmt_img->close();

    $sql = "DELETE FROM facilities WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: facilities.php?deleted=true");
            exit();
        }
        $stmt->close();
    }
}

// --- 2. FETCH DATA DENGAN FILTER DETAIL ---
$view_category = isset($_GET['view_sub']) ? $_GET['view_sub'] : null;

if ($view_category) {
    // Jika melihat sub-item, cari yang category-nya sama dengan nama induk
    $stmt = $mysqli->prepare("SELECT * FROM facilities WHERE category = ? ORDER BY display_order ASC");
    $stmt->bind_param("s", $view_category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default: Tampilkan hanya Fasilitas Utama (Category Kosong)
    $result = $mysqli->query("SELECT * FROM facilities WHERE category IS NULL OR category = '' ORDER BY display_order ASC");
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }
    .main-wrapper { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05); }
    .page-header-jhc { border-left: 4px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .btn-jhc-main { background: var(--jhc-gradient) !important; color: white !important; border-radius: 12px !important; padding: 10px 24px !important; font-weight: 700; border: none !important; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); transition: 0.3s; }
    .btn-jhc-main:hover { transform: translateY(-2px); opacity: 0.95; }
    .table thead th { background-color: #f8f9fa; color: #6c757d; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border: none; padding: 15px; }
    .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f1f1; }
    .facility-img { width: 80px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 0.85rem; }
    .badge-info-sub { background: #fff4f4; color: #bd3030; font-size: 0.7rem; padding: 4px 10px; border-radius: 5px; font-weight: 700; border: 1px solid #f5dada; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <?= $view_category ? "Detail Sub: " . htmlspecialchars($view_category) : "Manajemen Fasilitas Utama" ?>
                </h3>
                <p class="text-muted small mb-0">
                    <?= $view_category ? "Menampilkan semua item di dalam kategori ini." : "Kelola kategori fasilitas utama RS JHC." ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($view_category): ?>
                    <a href="facilities.php" class="btn btn-light rounded-pill px-4 fw-bold border shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Utama
                    </a>
                <?php endif; ?>
                <a href="facility_edit.php" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-layer-group me-2"></i> Kategori Utama
                </a>
                <a href="facility_item_edit.php" class="btn btn-jhc-main">
                    <i class="fas fa-plus me-2"></i> Tambah Sub-Item
                </a>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Data berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">Urutan</th>
                        <th class="text-center" style="width: 110px;">Foto</th>
                        <th>Nama Fasilitas</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php $is_sub = (!empty($row['category'])); ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $row['display_order']; ?></td>
                                <td class="text-center">
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" class="facility-img">
                                    <?php else: ?>
                                        <div class="facility-img d-flex align-items-center justify-content-center bg-light text-muted opacity-50">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                    <?php if($is_sub): ?>
                                        <span class="badge-info-sub">SUB DARI: <?= htmlspecialchars($row['category']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= strlen(strip_tags($row['description'])) > 80 ? substr(strip_tags($row['description']), 0, 80) . '...' : strip_tags($row['description']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <?php if (!$is_sub): ?>
                                            <a href="facilities.php?view_sub=<?= urlencode($row['name']); ?>" class="btn btn-sm btn-outline-info btn-action-jhc" title="Lihat Sub-Item">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= $is_sub ? 'facility_item_edit.php' : 'facility_edit.php'; ?>?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="facilities.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" onclick="return confirm('Hapus data ini?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5">Belum ada data fasilitas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>