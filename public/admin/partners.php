<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);

    // 1. Ambil path logo untuk dihapus dari server fisik
    $sql_select = "SELECT logo_path FROM partners WHERE id = ?";
    if ($stmt_select = $mysqli->prepare($sql_select)) {
        $stmt_select->bind_param("i", $id_to_delete);
        $stmt_select->execute();
        $stmt_select->bind_result($logo_path);
        if ($stmt_select->fetch()) {
            if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                unlink("../" . $logo_path);
            }
        }
        $stmt_select->close();
    }

    // 2. Hapus data dari database
    $sql_delete = "DELETE FROM partners WHERE id = ?";
    if ($stmt_delete = $mysqli->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $id_to_delete);
        if ($stmt_delete->execute()) {
            header("location: partners.php?deleted=true");
            exit();
        }
        $stmt_delete->close();
    }
}

// Fetch Data Partner
$sql = "SELECT id, name, logo_path, url FROM partners ORDER BY name ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    /* Card Wrapper bergaya Neumorphism */
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
        display: inline-flex;
        align-items: center;
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
        font-size: 0.9rem;
    }

    .partner-logo-thumb {
        width: 100px; height: 50px; object-fit: contain;
        background: #fdfdfd; border-radius: 8px; border: 1px solid #eee;
        padding: 5px;
    }

    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 0.85rem; }
    .url-text { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; color: #0d6efd; }
</style>

<div class="container-fluid py-4">
    <div class="admin-wrapper">
        <div class="d-flex justify-content-between align-items-center manage-header">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Rekanan & Partner</h3>
                <p class="text-muted small mb-0">Kelola logo instansi, asuransi, dan perusahaan yang bekerja sama dengan RS JHC.</p>
            </div>
            <a href="partner_edit.php" class="btn btn-jhc-main">
                <i class="fas fa-plus me-2"></i> Tambah Partner Baru
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Partner berhasil dihapus dari sistem.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Data partner berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 150px;">Logo</th>
                        <th>Nama Instansi</th>
                        <th>Tautan Website</th>
                        <th class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center">
                                    <img src="../<?= htmlspecialchars($row['logo_path']); ?>" class="partner-logo-thumb" alt="Logo">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                </td>
                                <td>
                                    <?php if(!empty($row['url'])): ?>
                                        <a href="<?= htmlspecialchars($row['url']); ?>" target="_blank" class="url-text small">
                                            <i class="fas fa-external-link-alt me-1"></i> <?= htmlspecialchars($row['url']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Tidak ada tautan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="partner_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="partners.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus partner ini?');">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted italic">
                                <i class="fas fa-handshake fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada partner rekanan yang terdaftar.
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