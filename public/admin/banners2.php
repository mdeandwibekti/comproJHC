<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Wajib SEBELUM require header.php) ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // 1. Ambil path gambar untuk dihapus secara fisik dari server
    $stmt = $mysqli->prepare("SELECT image_path FROM banners WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner_to_delete = $result->fetch_assoc();
    $stmt->close();

    if ($banner_to_delete && !empty($banner_to_delete['image_path'])) {
        $file_path = '../' . $banner_to_delete['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // 2. Hapus data dari database
    $stmt = $mysqli->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("location: banners2.php?deleted=true");
        exit();
    }
    $stmt->close();
}

// Ambil data banner terbaru
$banners = [];
$sql = "SELECT id, image_path, title, description, display_order FROM banners ORDER BY display_order ASC";
$result = $mysqli->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $banners[] = $row;
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism sesuai referensi image_bf1502.png */
    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 40px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .manage-header {
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
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: all 0.3s ease;
    }
    .btn-jhc-main:hover { transform: translateY(-2px); opacity: 0.95; }

    /* Tabel Styling sesuai image_bf1502.png */
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

    .img-banner-thumb {
        width: 120px;
        height: 70px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #eee;
    }

    .btn-action-edit { background-color: #e3f2fd; color: #1976d2; border-radius: 8px; font-weight: 600; padding: 6px 12px; }
    .btn-action-delete { background-color: #ffebee; color: #c62828; border-radius: 8px; font-weight: 600; padding: 6px 12px; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1">Manage Hero Banners</h3>
                <p class="text-muted small mb-0">Kelola gambar slider utama dan pesan promosi di halaman beranda.</p>
            </div>
            <a href="banner_edit.php" class="btn btn-jhc-main">
                <i class="fas fa-plus me-2"></i> Add New Banner
            </a>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Banner berhasil diperbarui!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash me-2"></i> Banner telah dihapus.
            </div>
        <?php endif; ?>

        <div class="table-responsive mt-2">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Order</th>
                        <th style="width: 150px;">Preview</th>
                        <th>Banner Info</th>
                        <th class="text-center" style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary"><?php echo $banner['display_order']; ?></td>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($banner['image_path']); ?>" class="img-banner-thumb shadow-sm">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($banner['title']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($banner['description'], 0, 90)); ?>...</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="banner_edit.php?id=<?php echo $banner['id']; ?>" class="btn btn-sm btn-action-edit text-decoration-none">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="banners2.php?delete_id=<?php echo $banner['id']; ?>" 
                                           class="btn btn-sm btn-action-delete text-decoration-none" 
                                           onclick="return confirm('Hapus banner ini selamanya?');">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-images fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada banner yang ditambahkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>