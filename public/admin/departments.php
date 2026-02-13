<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS DATA (DELETE) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);

    // A. Ambil nama file gambar lama dulu (untuk dihapus dari folder)
    $query_check = "SELECT icon_path, image_path FROM departments WHERE id = ?";
    if ($stmt_check = $mysqli->prepare($query_check)) {
        $stmt_check->bind_param("i", $id_to_delete);
        if ($stmt_check->execute()) {
            $result_check = $stmt_check->get_result();
            $data_img = $result_check->fetch_assoc();
        }
        $stmt_check->close();
    }

    // B. Hapus Data dari Database
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // C. Hapus Fisik File jika ada di folder
            if (!empty($data_img['icon_path']) && file_exists("../" . $data_img['icon_path'])) {
                @unlink("../" . $data_img['icon_path']); 
            }
            if (!empty($data_img['image_path']) && file_exists("../" . $data_img['image_path'])) {
                @unlink("../" . $data_img['image_path']);
            }

            // Redirect Sukses
            header("Location: departments.php?msg=deleted");
            exit();
        } else {
            // Tampilkan error jika query gagal
            echo "Error Database: " . $stmt->error;
            exit;
        }
        $stmt->close();
    }
}

// --- 2. AMBIL DATA LENGKAP (READ) ---
// Perhatikan nama kolom disesuaikan dengan database Anda
$sql = "SELECT id, name, category, icon_path, image_path, display_order, description, btn_text, btn_link 
        FROM departments 
        ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { --jhc-red-dark: #8a3033; --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; margin-top: 20px; border: 1px solid rgba(0,0,0,0.05); }
    .page-header-jhc { border-left: 4px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .btn-jhc-main { background: var(--jhc-gradient) !important; color: white !important; border-radius: 12px; padding: 10px 24px; font-weight: 700; border: none; box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3); transition: 0.3s; text-decoration: none; }
    .btn-jhc-main:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4); opacity: 0.95; color: white !important; }
    .table thead th { background-color: #f8f9fa; color: #6c757d; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; padding: 15px; border: none; }
    .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f1f1; font-size: 0.9rem; }
    .badge-jhc { padding: 6px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    .badge-layanan { background-color: #fceaea; color: #c53030; }
    .badge-poli { background-color: #e6f4ea; color: #1e7e34; }
    .icon-preview { width: 45px; height: 45px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; overflow: hidden; }
    .icon-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 10px; font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Departemen</h3>
                <p class="text-muted small mb-0">Kelola daftar layanan unggulan, poliklinik, dan penunjang medis.</p>
            </div>
            <a href="department_edit.php" class="btn btn-jhc-main"><i class="fas fa-plus me-2"></i> Tambah Baru</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4" role="alert">
                <i class="fas fa-trash-alt me-2"></i> Data departemen berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'saved'): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> Data berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Urutan</th>
                        <th style="width: 100px;">Ikon</th>
                        <th>Nama Departemen</th>
                        <th>Kategori</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted">#<?= $row['display_order']; ?></td>
                                
                                <td>
                                    <div class="icon-preview">
                                        <?php 
                                        // Cek path gambar, sesuaikan path relatifnya
                                        $img_src = !empty($row['icon_path']) ? "../" . $row['icon_path'] : "";
                                        if(!empty($img_src) && file_exists($row['icon_path'])): // Cek file exists relatif terhadap root script jika perlu, atau abaikan cek file exists untuk tampilan admin sederhana
                                        ?>
                                            <img src="../<?= htmlspecialchars($row['icon_path']); ?>" alt="icon">
                                        <?php elseif(!empty($row['icon_path'])): ?>
                                             <img src="../<?= htmlspecialchars($row['icon_path']); ?>" alt="icon">
                                        <?php else: ?>
                                            <i class="fas fa-hospital text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars(strip_tags($row['description'])); ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <?php 
                                    $catClass = 'badge-poli'; // Default
                                    if ($row['category'] == 'Layanan') $catClass = 'badge-layanan';
                                    if ($row['category'] == 'Penunjang') $catClass = 'badge-penunjang'; // Jika ada kategori ini
                                    ?>
                                    <span class="badge-jhc <?= $catClass; ?>"><?= htmlspecialchars($row['category']); ?></span>
                                </td>
                                
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn btn-outline-primary btn-action-jhc" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="departments.php?delete=<?= $row['id']; ?>" class="btn btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($row['name']); ?>? Tindakan ini tidak dapat dibatalkan.');" title="Hapus">
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
                                Belum ada data departemen yang ditambahkan.
                            </td>
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