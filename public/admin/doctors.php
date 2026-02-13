<?php
require_once "../../config.php";

// --- 1. LOGIKA PENGHAPUSAN (TETAP SAMA) ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    $stmt_img = $mysqli->prepare("SELECT photo_path FROM doctors WHERE id = ?");
    $stmt_img->bind_param("i", $id_to_delete);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result()->fetch_assoc();

    $sql = "DELETE FROM doctors WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            if (!empty($res_img['photo_path']) && file_exists("../" . $res_img['photo_path'])) {
                @unlink("../" . $res_img['photo_path']);
            }
            header("location: doctors.php?deleted=true");
            exit();
        }
        $stmt->close();
    }
}

// --- 2. FETCH DATA DOKTER ---
$sql = "SELECT d.*, dep.name as department_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY dep.name ASC, d.name ASC";
$result = $mysqli->query($sql);

// Hitung statistik singkat
$total_dokter = $result->num_rows;
$dokter_unggulan = 0;

$dokter_list = [];
if ($result && $total_dokter > 0) {
    while($row = $result->fetch_assoc()) {
        $dokter_list[] = $row;
        if($row['is_featured']) $dokter_unggulan++;
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
    }
    
    .main-wrapper { 
        background: #ffffff; 
        border-radius: 24px; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.05); 
        padding: 35px; 
        border: 1px solid rgba(0,0,0,0.05); 
    }

    /* Stats Card */
    .stat-card {
        background: #fcfcfc;
        border-radius: 16px;
        padding: 15px 20px;
        border: 1px solid #f1f1f1;
        transition: 0.3s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

    /* Table Improvements */
    .table-container { border-radius: 16px; overflow: hidden; border: 1px solid #f1f1f1; }
    .table thead th { 
        background-color: #fcfcfc; 
        color: #6c757d; 
        font-weight: 800; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        padding: 18px; 
    }
    
    .doctor-avatar { 
        width: 50px; height: 50px; 
        object-fit: cover; 
        border-radius: 12px; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.08); 
    }

    .badge-dept { 
        background-color: #f0f4f8; 
        color: #4a5568; 
        border-radius: 8px; 
        padding: 6px 12px; 
        font-weight: 700; 
        font-size: 0.7rem; 
    }

    .btn-jhc-main { 
        background: var(--jhc-gradient); 
        color: white !important; 
        border-radius: 12px; 
        padding: 12px 28px; 
        font-weight: 700; 
        border: none; 
        box-shadow: 0 8px 20px rgba(138, 48, 51, 0.3); 
        transition: 0.3s; 
    }
    .btn-jhc-main:hover { transform: scale(1.02); box-shadow: 0 10px 25px rgba(138, 48, 51, 0.4); }

    .action-btn {
        width: 36px; height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: 0.2s;
    }
</style>

<div class="container-fluid py-4">
    
    <nav aria-label="breadcrumb" class="mb-4 ms-2">
        <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 0.8rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Manajemen Dokter</li>
        </ol>
    </nav>

    <div class="main-wrapper">
        <div class="row align-items-center mb-5">
            <div class="col-md-7">
                <h3 class="fw-bold text-dark mb-1">Database Dokter</h3>
                <p class="text-muted mb-0">Kelola tenaga medis dan jadwal praktik Rumah Sakit JHC.</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="doctor_edit.php" class="btn btn-jhc-main">
                    <i class="fas fa-user-plus me-2"></i>Tambah Dokter
                </a>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.6rem;">Total Dokter</small>
                    <h4 class="fw-bold mb-0 text-dark"><?= $total_dokter; ?> <span class="small fw-normal text-muted">Orang</span></h4>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning border-0 shadow-sm border-start border-warning border-4 mb-4 small">
                <i class="fas fa-trash-alt me-2"></i> Data dokter telah dihapus secara permanen.
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Profil</th>
                        <th>Detail Dokter</th>
                        <th>Penempatan Poli</th>
                        <th>Informasi Praktik</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dokter_list)): ?>
                        <?php foreach($dokter_list as $row): ?>
                            <tr>
                                <td class="text-center">
                                    <?php $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'assets/img/gallery/default-avatar.png'; ?>
                                    <img src="../<?= htmlspecialchars($photo); ?>" class="doctor-avatar" onerror="this.src='../assets/img/gallery/default-avatar.png';">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars($row['specialty']); ?></div>
                                </td>
                                <td>
                                    <span class="badge-dept text-uppercase">
                                        <i class="fas fa-hospital me-1 text-primary"></i>
                                        <?= htmlspecialchars($row['department_name'] ?? 'Umum'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted" style="max-width: 200px; line-height: 1.2;">
                                        <?php if (!empty($row['schedule'])): ?>
                                            <i class="far fa-calendar-alt me-1 text-danger"></i> <?= htmlspecialchars($row['schedule']); ?>
                                        <?php else: ?>
                                            <span class="fst-italic opacity-50">Jadwal belum diisi</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['is_featured']): ?>
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold" style="font-size: 0.65rem;">
                                            <i class="fas fa-star me-1"></i>UNGGULAN
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="doctor_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary action-btn" title="Edit Data">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="doctors.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger action-btn" 
                                           onclick="return confirm('Hapus profil dr. <?= addslashes($row['name']); ?>?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-user-md fa-3x mb-3 opacity-10"></i>
                                <p class="text-muted">Belum ada tenaga medis yang terdaftar.</p>
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