<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php untuk mencegah error header) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $sql = "DELETE FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Redirect aman menggunakan PHP header karena belum ada output HTML
            header("location: doctors.php?deleted=true");
            exit();
        } else {
            $error_msg = "Gagal menghapus data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch doctors dengan join ke tabel departments
$sql = "SELECT d.id, d.name, d.specialty, d.photo_path, d.is_featured, d.description, dep.name as department_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY d.name ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
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
        font-size: 0.9rem;
    }

    .doctor-avatar {
        width: 48px; height: 48px; object-fit: cover;
        border-radius: 50%; border: 2px solid #fff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .badge-featured { background-color: #e6f4ea; color: #1e7e34; border-radius: 50px; padding: 6px 16px; font-weight: 600; }
    .btn-action-jhc { border-radius: 8px; font-weight: 600; padding: 6px 12px; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Manajemen Dokter</h3>
                <p class="text-muted small mb-0">Kelola profil dokter, spesialisasi, dan jadwal praktik RS JHC.</p>
            </div>
            <a href="doctor_edit.php" class="btn btn-jhc-main"><i class="fas fa-plus me-2"></i> Tambah Dokter</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm border-start border-warning border-4 mb-4">
                <i class="fas fa-trash-alt me-2"></i> Data dokter berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm border-start border-success border-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Data berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 80px;">Foto</th>
                <th>Nama Dokter</th>
                <th>Spesialisasi</th>
                <th style="width: 250px;">Tentang Dokter</th> <th>Departemen</th>
                <th class="text-center">Unggulan</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center">
                            <?php $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'assets/img/gallery/default-avatar.png'; ?>
                            <img src="../<?= htmlspecialchars($photo); ?>" class="doctor-avatar" onerror="this.src='../assets/img/gallery/default-avatar.png';">
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['specialty']); ?></td>
                        
                        <td>
                            <div class="text-muted small text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                <?= !empty($row['description']) ? htmlspecialchars($row['description']) : '<i class="text-light">Tidak ada deskripsi.</i>'; ?>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small fw-bold">
                                <?= htmlspecialchars($row['department_name'] ?? '-'); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($row['is_featured']): ?>
                                <span class="badge-featured small"><i class="fas fa-check-circle me-1"></i> Ya</span>
                            <?php else: ?>
                                <span class="text-muted small">Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="doctor_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary btn-action-jhc" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="doctors.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-action-jhc" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus dokter ini?');" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>