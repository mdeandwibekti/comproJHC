<?php
require_once "../../config.php";

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $stmt_img = $mysqli->prepare("SELECT image_path FROM news WHERE id = ?");
    $stmt_img->bind_param("i", $id_to_delete);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result();
    if($row_img = $res_img->fetch_assoc()) {
        if (!empty($row_img['image_path']) && file_exists("../" . $row_img['image_path'])) {
            unlink("../" . $row_img['image_path']);
        }
    }
    $stmt_img->close();

    $sql = "DELETE FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: news.php?deleted=true");
            exit();
        }
    }
}

// Fetch Data Berita
$sql = "SELECT * FROM news ORDER BY post_date DESC";
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
    .breadcrumb-jhc a { text-decoration: none; color: #64748b; transition: 0.3s; font-weight: 500; }
    .breadcrumb-jhc a:hover { color: var(--jhc-red-dark); }
    .breadcrumb-jhc .current { color: var(--jhc-red-dark); font-weight: 700; }

    .admin-wrapper {
        background: #ffffff; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); 
        padding: 40px; margin-top: 10px; border: 1px solid #f1f5f9;
    }

    .manage-header { border-left: 6px solid var(--jhc-red-dark); padding-left: 24px; margin-bottom: 35px; }

    .btn-jhc-main { 
        background: var(--jhc-gradient) !important; color: white !important; 
        border-radius: 14px !important; padding: 12px 28px !important; 
        font-weight: 700; border: none !important;
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.2); transition: 0.3s; text-decoration: none;
    }
    .btn-jhc-main:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(138, 48, 51, 0.3); color: white; }

    /* Table Styling */
    .table thead th { 
        background-color: #fcfdfe; color: #94a3b8; text-transform: uppercase; 
        font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; 
        border-bottom: 2px solid #f1f5f9; padding: 20px 15px;
    }

    .table tbody td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #475569; }

    .news-thumb {
        width: 110px; height: 70px; object-fit: cover;
        border-radius: 12px; border: 2px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .badge-category {
        background-color: #fff1f2; color: var(--jhc-red-dark); 
        padding: 6px 14px; border-radius: 50px; 
        font-weight: 800; font-size: 0.65rem; text-transform: uppercase;
        border: 1px solid rgba(138, 48, 51, 0.1);
    }

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
        <span class="current">Berita & Artikel</span>
    </div>

    <div class="admin-wrapper">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center manage-header">
            <div>
                <h2 class="fw-extrabold mb-1 text-dark" style="font-weight: 800; letter-spacing: -1px;">Manajemen Artikel</h2>
                <p class="text-muted small mb-0">Kelola publikasi edukasi kesehatan dan pengumuman resmi RS JHC.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="news_edit.php" class="btn btn-jhc-main">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Artikel Baru
                </a>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <div class="d-flex align-items-center"><i class="fas fa-trash-alt me-2"></i> Artikel telah dihapus dari sistem.</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <div class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i> Artikel berhasil diterbitkan/diperbarui!</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center">Sampul</th>
                        <th>Judul Artikel</th>
                        <th class="text-center">Kategori</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="align-middle">
                                <td class="text-center">
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" class="news-thumb">
                                    <?php else: ?>
                                        <div class="news-thumb d-flex align-items-center justify-content-center bg-light text-muted opacity-50 mx-auto">
                                            <i class="fas fa-newspaper fa-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem; line-height: 1.4; max-width: 400px;"><?= htmlspecialchars($row['title']); ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge-category"><?= htmlspecialchars($row['category']); ?></span>
                                </td>
                                <td class="text-center text-muted small">
                                    <div class="fw-bold"><i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($row['post_date'])); ?></div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="news_edit.php?id=<?= $row['id']; ?>" class="btn-action-jhc btn-edit" title="Edit Artikel">
                                            <i class="fas fa-pen-nib"></i>
                                        </a>
                                        <a href="news.php?delete=<?= $row['id']; ?>" class="btn-action-jhc btn-delete" 
                                           onclick="return confirm('Hapus artikel ini secara permanen?');" title="Hapus Artikel">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted italic">Belum ada artikel yang diterbitkan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>