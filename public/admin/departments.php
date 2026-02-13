<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS DATA (TETAP SAMA) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $query_check = "SELECT icon_path, image_path FROM departments WHERE id = ?";
    if ($stmt_check = $mysqli->prepare($query_check)) {
        $stmt_check->bind_param("i", $id_to_delete);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $data_img = $result_check->fetch_assoc();
        $stmt_check->close();
    }

    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            if (!empty($data_img['icon_path']) && file_exists("../" . $data_img['icon_path'])) {
                @unlink("../" . $data_img['icon_path']); 
            }
            if (!empty($data_img['image_path']) && file_exists("../" . $data_img['image_path'])) {
                @unlink("../" . $data_img['image_path']);
            }
            header("Location: departments.php?msg=deleted");
            exit();
        }
        $stmt->close();
    }
}

// --- 2. AMBIL DATA & PISAHKAN KATEGORI ---
$sql = "SELECT id, name, category, icon_path, display_order FROM departments ORDER BY display_order ASC";
$result = $mysqli->query($sql);

$poliklinik = [];
$layanan = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        if ($row['category'] == 'Poliklinik') {
            $poliklinik[] = $row;
        } else {
            $layanan[] = $row;
        }
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033; 
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); 
        --med-green: #2d9d78;
    }
    
    /* Wrapper lebih rapat */
    .main-wrapper { 
        background: #ffffff; 
        border-radius: 16px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        padding: 25px; 
        border: 1px solid rgba(0,0,0,0.03); 
    }

    /* Ukuran Judul diperkecil */
    .section-title-box {
        padding: 6px 15px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .title-poli { background: #e9f7f3; color: var(--med-green); border-left: 4px solid var(--med-green); }
    .title-layanan { background: #fdf2f2; color: var(--jhc-red); border-left: 4px solid var(--jhc-red); }

    /* Tabel Compact */
    .table-container { 
        border-radius: 12px; 
        overflow: hidden; 
        border: 1px solid #eee; 
        margin-bottom: 30px;
    }

    .table thead th { 
        font-size: 0.7rem; 
        padding: 10px 15px; 
        letter-spacing: 0.5px;
    }
    
    .table-poli thead th { background-color: var(--med-green); color: #fff; }
    .table-layanan thead th { background: var(--jhc-gradient); color: #fff; }

    .table tbody td { 
        padding: 8px 15px; 
        font-size: 0.85rem; 
        border-bottom: 1px solid #f8f9fa; 
    }

    /* Ukuran Ikon diperkecil */
    .icon-preview { 
        width: 35px; height: 35px; 
        border-radius: 8px; 
        background: #f8f9fa;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid #eee;
    }
    .icon-preview img { width: 22px; height: 22px; object-fit: contain; }

    /* Tombol Aksi Mini */
    .action-btn {
        width: 30px; height: 30px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: 0.2s;
    }
    .btn-edit { background: #f0f7ff; color: #007bff; }
    .btn-del { background: #fff5f5; color: #dc3545; }
    .btn-edit:hover { background: #007bff; color: #fff; }
    .btn-del:hover { background: #dc3545; color: #fff; }

    .order-badge {
        background: #f1f3f5;
        color: #495057;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid py-3">
    
    <nav aria-label="breadcrumb" class="mb-3 ms-1">
        <ol class="breadcrumb bg-transparent p-0 m-0" style="font-size: 0.75rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Departemen</li>
        </ol>
    </nav>

    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0" style="font-size: 1.25rem;">Departemen</h4>
                <p class="text-muted small mb-0">Kelola Poli & Layanan JHC.</p>
            </div>
            <a href="department_edit.php" class="btn btn-sm btn-jhc-add px-3" style="font-size: 0.8rem; border-radius: 10px;">
                <i class="fas fa-plus-circle me-1"></i> Tambah Baru
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success py-2 px-3 border-0 shadow-sm mb-4 small">
                <i class="fas fa-check-circle me-1"></i> Data diperbarui!
            </div>
        <?php endif; ?>

        <div class="section-title-box title-poli">
            <i class="fas fa-stethoscope me-2"></i> Poliklinik (<?= count($poliklinik) ?>)
        </div>
        
        <div class="table-container shadow-sm">
            <table class="table table-poli align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">#</th>
                        <th width="80">IKON</th>
                        <th>NAMA UNIT</th>
                        <th class="text-center" width="120">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($poliklinik)): foreach($poliklinik as $row): ?>
                        <tr>
                            <td class="text-center"><span class="order-badge"><?= $row['display_order'] ?></span></td>
                            <td>
                                <div class="icon-preview">
                                    <img src="../<?= !empty($row['icon_path']) ? $row['icon_path'] : 'assets/img/gallery/default-icon.png' ?>" alt="">
                                </div>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="text-center">
                                <a href="department_edit.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="departments.php?delete=<?= $row['id'] ?>" class="action-btn btn-del" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center py-3 text-muted small">Belum ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title-box title-layanan">
            <i class="fas fa-star me-2"></i> Layanan Unggulan (<?= count($layanan) ?>)
        </div>
        
        <div class="table-container shadow-sm">
            <table class="table table-layanan align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">#</th>
                        <th width="80">IKON</th>
                        <th>NAMA LAYANAN</th>
                        <th class="text-center" width="120">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($layanan)): foreach($layanan as $row): ?>
                        <tr>
                            <td class="text-center"><span class="order-badge"><?= $row['display_order'] ?></span></td>
                            <td>
                                <div class="icon-preview">
                                    <img src="../<?= !empty($row['icon_path']) ? $row['icon_path'] : 'assets/img/gallery/default-icon.png' ?>" alt="">
                                </div>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="text-center">
                                <a href="department_edit.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="departments.php?delete=<?= $row['id'] ?>" class="action-btn btn-del" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center py-3 text-muted small">Belum ada data.</td></tr>
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