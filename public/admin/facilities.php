<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php untuk mencegah error header) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Ambil path gambar untuk dihapus dari server sebelum data di DB dihapus
    $stmt_img = $mysqli->prepare("SELECT image_path FROM facilities WHERE id = ?");
    $stmt_img->bind_param("i", $id_to_delete);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result();
    if($row_img = $res_img->fetch_assoc()) {
        if (!empty($row_img['image_path']) && file_exists("../" . $row_img['image_path'])) {
            unlink("../" . $row_img['image_path']);
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

// Fetch Data dari database
$sql = "SELECT * FROM facilities ORDER BY display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Wrapper Neumorphism sesuai referensi image_bf1502.png */
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
        border: none !important;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        transition: 0.3s; 
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
    }

    .facility-img {
        width: 100px; height: 65px; object-fit: cover;
        border-radius: 10px; border: 1px solid #eee;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Fasilitas</h3>
                <p class="text-muted small mb-0">Kelola informasi ruangan, alat medis, dan area publik RS JHC.</p>
            </div>
            <a href="facility_edit.php" class="btn btn-jhc-main">
                <i class="fas fa-plus me-2"></i> Tambah Fasilitas Baru
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Fasilitas berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Data fasilitas berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Urutan</th>
                        <th class="text-center" style="width: 150px;">Foto</th>
                        <th>Nama Fasilitas</th>
                        <th>Deskripsi Singkat</th>
                        <th class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= htmlspecialchars($row['display_order']); ?></td>
                                <td class="text-center">
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" class="facility-img">
                                    <?php else: ?>
                                        <div class="facility-img d-flex align-items-center justify-content-center bg-light text-muted opacity-50">
                                            <i class="fas fa-image fa-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></td>
                                <td class="text-muted small">
                                    <?php 
                                    $desc = htmlspecialchars($row['description']);
                                    echo strlen($desc) > 90 ? substr($desc, 0, 90) . '...' : $desc; 
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="facility_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="facilities.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus fasilitas ini?');">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-building fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada data fasilitas yang terdaftar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>