<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // 1. Ambil path gambar lama sebelum menghapus data
    $sql_select = "SELECT image_path FROM mcu_packages WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql_select)) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $package_to_delete = $result->fetch_assoc();
        $stmt->close();

        // Hapus file fisik jika ada
        if ($package_to_delete && !empty($package_to_delete['image_path'])) {
            $file_path = '../' . $package_to_delete['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    // 2. Hapus data dari database
    $sql_delete = "DELETE FROM mcu_packages WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql_delete)) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            header("location: mcu_packages.php?deleted=true");
            exit();
        }
        $stmt->close();
    }
}

// --- FETCH DATA ---
$packages = [];
$sql = "SELECT id, image_path, title, price, display_order FROM mcu_packages ORDER BY display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
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

    .mcu-img-thumb {
        width: 100px; height: 65px; object-fit: cover;
        border-radius: 10px; border: 1px solid #eee;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 0.85rem; }
    
    .price-tag { color: var(--jhc-red-dark); font-weight: 700; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Paket MCU</h3>
                <p class="text-muted small mb-0">Kelola daftar paket Medical Check Up dan harga layanan RS JHC.</p>
            </div>
            <a href="mcu_package_edit.php" class="btn btn-jhc-main">
                <i class="fas fa-plus me-2"></i> Tambah Paket Baru
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Paket MCU berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Data paket berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Order</th>
                        <th class="text-center" style="width: 150px;">Preview</th>
                        <th>Nama Paket</th>
                        <th>Estimasi Harga</th>
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
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" class="mcu-img-thumb">
                                    <?php else: ?>
                                        <div class="mcu-img-thumb d-flex align-items-center justify-content-center bg-light text-muted opacity-50">
                                            <i class="fas fa-file-medical fa-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['title']); ?></td>
                                <td class="price-tag">
                                    Rp <?= number_format($row['price'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="mcu_package_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="mcu_packages.php?delete_id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus paket MCU ini?');">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">
                                <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada paket MCU yang terdaftar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>