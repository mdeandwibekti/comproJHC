<?php
require_once "../../config.php";

// --- LOGIKA PENGHAPUSAN ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Ambil path ikon untuk dihapus dari storage
    $stmt_get = $mysqli->prepare("SELECT icon_path FROM departments WHERE id = ?");
    $stmt_get->bind_param("i", $id_to_delete);
    $stmt_get->execute();
    $res_icon = $stmt_get->get_result()->fetch_assoc();

    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Hapus file fisik jika bukan default
            if (!empty($res_icon['icon_path']) && file_exists("../" . $res_icon['icon_path'])) {
                unlink("../" . $res_icon['icon_path']);
            }
            header("location: departments.php?status=deleted");
            exit();
        }
    }
}

// Ambil data lengkap
$sql = "SELECT id, name, category, icon_path, display_order, description, special_skills, additional_info 
        FROM departments 
        ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 35px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Table Customization */
    .table-responsive { border-radius: 15px; border: 1px solid #f0f0f0; }
    .table thead th { 
        background-color: #fcfcfc; 
        color: #888; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 1px;
        padding: 15px;
        border-bottom: 2px solid #f4f4f4;
    }

    .table tbody td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f8f8f8; }

    /* UI Components */
    .icon-box {
        width: 48px; height: 48px;
        background: #fff5f5;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid #ffeaea;
    }
    .icon-box img { width: 28px; height: 28px; object-fit: contain; }

    .badge-jhc {
        padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 0.7rem;
    }
    .badge-layanan { background: #fff0f0; color: #d63031; }
    .badge-poli { background: #eef5ff; color: #0984e3; }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .btn-jhc-add { 
        background: var(--jhc-gradient); color: white; border-radius: 12px; 
        padding: 12px 25px; font-weight: 700; border: none;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.2);
    }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center page-header-jhc">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Departments & Services</h4>
                <p class="text-muted small mb-0">Kelola informasi publik, layanan unggulan, dan profil poliklinik.</p>
            </div>
            <a href="department_edit.php" class="btn btn-jhc-add mt-3 mt-md-0">
                <i class="fas fa-plus-circle me-2"></i> Tambah Data Baru
            </a>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 mb-4 alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> 
                Data berhasil <?= $_GET['status'] == 'deleted' ? 'dihapus' : 'diperbarui'; ?>!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive shadow-sm">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">Urutan</th>
                        <th style="width: 80px;">Ikon</th>
                        <th>Departemen & Deskripsi</th>
                        <th>Info Publik</th>
                        <th style="width: 150px;">Kategori</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $row['display_order']; ?></td>
                                <td>
                                    <div class="icon-box">
                                        <img src="../<?= !empty($row['icon_path']) ? htmlspecialchars($row['icon_path']) : 'assets/img/icons/default.png'; ?>" alt="icon">
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-muted text-truncate-2">
                                        <?= !empty($row['description']) ? htmlspecialchars(strip_tags($row['description'])) : '<em class="small text-danger">Deskripsi belum diisi...</em>'; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <?php if(!empty($row['special_skills'])): ?>
                                            <span class="text-success small"><i class="fas fa-check-circle me-1"></i> Keahlian Khusus</span>
                                        <?php endif; ?>
                                        <?php if(!empty($row['additional_info'])): ?>
                                            <span class="text-primary small"><i class="fas fa-info-circle me-1"></i> Info Tambahan</span>
                                        <?php endif; ?>
                                        <?php if(empty($row['special_skills']) && empty($row['additional_info'])): ?>
                                            <span class="text-muted small italic">Data kosong</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-jhc <?= ($row['category'] == 'Layanan') ? 'badge-layanan' : 'badge-poli'; ?>">
                                        <i class="fas <?= ($row['category'] == 'Layanan') ? 'fa-star' : 'fa-stethoscope'; ?> me-2"></i>
                                        <?= strtoupper($row['category']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary border-0 shadow-sm" style="border-radius: 8px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="departments.php?delete=<?= $row['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger border-0 shadow-sm" 
                                           style="border-radius: 8px;"
                                           onclick="return confirm('Apakah Anda yakin? Data deskripsi dan keahlian juga akan terhapus.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <p class="text-muted mb-0">Belum ada data departemen.</p>
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