<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php untuk mencegah error header) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Ambil path gambar untuk dihapus dari server sebelum data di DB dihapus
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
        $stmt->close();
    }
}

// Fetch Data Berita
$sql = "SELECT * FROM news ORDER BY post_date DESC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Wrapper Neumorphism */
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
        transition: 0.3s; 
        text-decoration: none;
    }
    .btn-jhc-main:hover { transform: translateY(-2px); opacity: 0.95; color: white; }

    /* Tabel Styling */
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
    }

    .news-thumb {
        width: 100px; height: 60px; object-fit: cover;
        border-radius: 10px; border: 1px solid #eee;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .badge-category {
        background-color: #fceaea; 
        color: #c53030; 
        padding: 6px 14px; 
        border-radius: 50px; 
        font-weight: 600; 
        font-size: 0.75rem;
    }

    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Berita & Artikel</h3>
                <p class="text-muted small mb-0">Kelola publikasi artikel kesehatan, tips medis, dan pengumuman RS JHC.</p>
            </div>
            <a href="news_edit.php" class="btn btn-jhc-main">
                <i class="fas fa-plus me-2"></i> Tambah Artikel Baru
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Artikel berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Artikel berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 150px;">Sampul</th>
                        <th>Judul Artikel</th>
                        <th>Kategori</th>
                        <th>Tanggal Posting</th>
                        <th class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
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
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']); ?></div>
                                </td>
                                <td>
                                    <span class="badge-category">
                                        <?= htmlspecialchars($row['category']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?= date('d M Y', strtotime($row['post_date'])); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="news_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="news.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada artikel yang diterbitkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>