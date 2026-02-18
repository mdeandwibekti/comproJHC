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
    $stmt = $mysqli->prepare("SELECT * FROM facilities WHERE category = ? ORDER BY display_order ASC");
    $stmt->bind_param("s", $view_category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query("SELECT * FROM facilities WHERE category IS NULL OR category = '' ORDER BY display_order ASC");
}

require_once 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --admin-bg: #f8fafb;
    }

    body {
        background-color: var(--admin-bg) !important;
        font-family: 'Inter', sans-serif;
    }

    /* Wrapper Utama Dashboard */
    .main-wrapper { 
        background: #ffffff; 
        border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 40px; 
        margin-top: 20px; 
        border: 1px solid #f1f5f9; 
    }

    /* Header Section */
    .page-header-jhc { 
        border-left: 6px solid var(--jhc-red-dark); 
        padding-left: 24px; 
        margin-bottom: 40px; 
    }

    /* Tombol Kategori Utama: PUTIH, Tulisan MERAH */
    .btn-white-red {
        background: #ffffff !important;
        color: var(--jhc-red-dark) !important;
        border: 2px solid var(--jhc-red-dark) !important;
        border-radius: 14px !important;
        font-weight: 800;
        padding: 10px 24px !important;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .btn-white-red:hover {
        background: var(--jhc-red-dark) !important;
        color: #ffffff !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2);
    }

    /* Tombol Tambah Sub-Item: MERAH, Tulisan PUTIH */
    .btn-red-white {
        background: var(--jhc-gradient) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 14px !important;
        font-weight: 800;
        padding: 12px 28px !important;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.25);
    }

    .btn-red-white:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(138, 48, 51, 0.35);
        filter: brightness(1.1);
        color: #ffffff !important;
    }

    .btn-back-jhc {
        background: #f8fafb;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-weight: 700;
        padding: 10px 20px;
        transition: 0.3s;
        text-decoration: none;
    }
    
    .btn-back-jhc:hover {
        background: #fff;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    /* Table Styling */
    .table thead th { 
        background-color: #fcfdfe; 
        color: #94a3b8; 
        text-transform: uppercase; 
        font-size: 0.7rem; 
        font-weight: 800;
        letter-spacing: 1.5px; 
        border-bottom: 2px solid #f1f5f9;
        padding: 20px 15px; 
    }

    .table tbody td { 
        padding: 20px 15px; 
        vertical-align: middle; 
        border-bottom: 1px solid #f1f5f9; 
        color: #475569;
        font-size: 0.9rem;
    }

    .facility-img { 
        width: 90px; height: 60px; object-fit: cover; 
        border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 2px solid #fff;
    }

    /* Action Buttons */
    .btn-action-jhc { 
        border-radius: 10px; width: 38px; height: 38px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.2s; border: 1px solid #e2e8f0; background: #fff; text-decoration: none;
    }

    .btn-action-edit { color: #3b82f6; }
    .btn-action-edit:hover { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    
    .btn-action-delete { color: #ef4444; }
    .btn-action-delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }

    .btn-action-view { color: #10b981; }
    .btn-action-view:hover { background: #ecfdf5; border-color: #10b981; color: #10b981; }

    .badge-info-sub { 
        background: #fff5f5; color: var(--jhc-red-dark); 
        font-size: 0.65rem; padding: 4px 12px; border-radius: 50px; 
        font-weight: 800; border: 1px solid rgba(138, 48, 51, 0.1);
        text-transform: uppercase; display: inline-block; margin-top: 5px;
    }

    .breadcrumb-jhc {
    font-size: 0.9rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    }
    .breadcrumb-jhc a {
        text-decoration: none;
        color: #6c757d; /* Warna abu-abu Dashboard */
        transition: 0.3s;
    }
    .breadcrumb-jhc a:hover {
        color: var(--jhc-red-dark);
    }
    .breadcrumb-jhc .separator {
        color: #dee2e6;
    }
    .breadcrumb-jhc .current {
        color: var(--jhc-red-dark); /* Warna merah JHC */
        font-weight: 700;
    }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="breadcrumb-jhc">
            <a href="dashboard.php">Dashboard</a> 
            <span class="separator">/</span> 
            <span class="current">Manajemen Fasilitas</span>
        </div>
        <div class="page-header-jhc d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="letter-spacing: -1px; font-weight: 800;">
                    <?= $view_category ? "Detail Sub: " . htmlspecialchars($view_category) : "Manajemen Fasilitas" ?>
                </h2>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= $view_category ? "Menampilkan semua item di dalam kategori ini." : "Kelola kategori fasilitas utama rumah sakit secara dinamis." ?>
                </p>
            </div>
            
            <div class="d-flex gap-3 mt-3 mt-md-0 align-items-center">
                <?php if ($view_category): ?>
                    <a href="facilities.php" class="btn-back-jhc">
                        <i class="fas fa-chevron-left me-2"></i> Kembali
                    </a>
                <?php endif; ?>

                <a href="facility_edit.php" class="btn-white-red">
                    <i class="fas fa-layer-group me-2"></i> Kategori Utama
                </a>

                <a href="facility_item_edit.php" class="btn-red-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Sub-Item
                </a>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-trash-alt me-2"></i> 
                    <span>Data fasilitas telah berhasil dihapus dari sistem.</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">URUTAN</th>
                        <th class="text-center" style="width: 130px;">PREVIEW</th>
                        <th>IDENTITAS FASILITAS</th>
                        <th>DESKRIPSI SINGKAT</th>
                        <th class="text-center" style="width: 180px;">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php $is_sub = (!empty($row['category'])); ?>
                            <tr class="align-middle">
                                <td class="text-center fw-bold text-muted"><?= $row['display_order']; ?></td>
                                <td class="text-center">
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" class="facility-img">
                                    <?php else: ?>
                                        <div class="facility-img d-flex align-items-center justify-content-center bg-light text-muted opacity-50">
                                            <i class="fas fa-image fa-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bolder text-dark mb-1" style="font-weight: 700;"><?= htmlspecialchars($row['name']); ?></div>
                                    <?php if($is_sub): ?>
                                        <span class="badge-info-sub">Parent: <?= htmlspecialchars($row['category']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-muted lh-sm" style="font-size: 0.85rem; max-width: 400px; text-align: justify;">
                                        <?= strlen(strip_tags($row['description'])) > 100 ? substr(strip_tags($row['description']), 0, 100) . '...' : strip_tags($row['description']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <?php if (!$is_sub): ?>
                                            <a href="facilities.php?view_sub=<?= urlencode($row['name']); ?>" class="btn-action-jhc btn-action-view" title="Lihat Sub-Item">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= $is_sub ? 'facility_item_edit.php' : 'facility_edit.php'; ?>?id=<?= $row['id']; ?>" class="btn-action-jhc btn-action-edit" title="Edit">
                                            <i class="fas fa-pen-nib"></i>
                                        </a>
                                        <a href="facilities.php?delete=<?= $row['id']; ?>" class="btn-action-jhc btn-action-delete" onclick="return confirm('Hapus fasilitas ini secara permanen?');" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted fst-italic">Belum ada data fasilitas tersimpan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>