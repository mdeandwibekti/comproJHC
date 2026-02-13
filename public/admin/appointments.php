<?php
require_once "../../config.php";

// --- 1. LOGIKA PEMROSESAN STATUS ---
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $status = $_GET['action'] == 'read' ? 'read' : 'contacted';
    
    $stmt = $mysqli->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if($stmt->execute()){
        header("location: appointments.php?status_updated=true");
        exit();
    }
    $stmt->close();
}

// --- 2. AMBIL DATA & HITUNG STATISTIK ---
$sql = "SELECT * FROM appointments ORDER BY submission_date DESC";
$result = $mysqli->query($sql);

$total_all = $result->num_rows;
$total_new = 0;
$appointments = [];

if ($result) {
    while($row = $result->fetch_assoc()){
        $appointments[] = $row;
        if($row['status'] == 'new') $total_new++;
    }
}

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033;
        --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%);
        --bg-light: #f8f9fa;
    }

    .main-wrapper {
        background: #ffffff; border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 30px; border: 1px solid rgba(0,0,0,0.05);
    }

    /* Stats Box Minimalis */
    .stat-box {
        background: var(--bg-light); border-radius: 12px;
        padding: 12px 18px; border-left: 4px solid #ddd;
        transition: 0.3s;
    }
    .stat-box:hover { background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

    /* Table Styling Compact */
    .table-container { border-radius: 12px; overflow: hidden; border: 1px solid #f1f1f1; }
    .table thead th { 
        background-color: #fcfcfc; color: #6c757d; 
        font-weight: 800; font-size: 0.7rem; 
        text-transform: uppercase; letter-spacing: 0.5px;
        padding: 12px 15px; border-bottom: 2px solid #f1f1f1;
    }
    
    .table tbody td { padding: 12px 15px; font-size: 0.85rem; }
    
    .row-unread { background-color: rgba(138, 48, 51, 0.02); }
    .row-unread td { font-weight: 600; }

    /* Badge Status */
    .status-badge {
        padding: 4px 10px; border-radius: 6px;
        font-size: 0.65rem; font-weight: 800;
        text-transform: uppercase;
    }
    .badge-new { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
    .badge-read { background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; }
    .badge-contacted { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    .whatsapp-link { color: #25d366; text-decoration: none; font-weight: 700; }
    .btn-action-mini {
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; font-size: 0.75rem; transition: 0.2s;
    }
</style>

<div class="container-fluid py-3">
    
    <nav aria-label="breadcrumb" class="mb-3 ms-1">
        <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 0.75rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Janji Temu</li>
        </ol>
    </nav>

    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0">Janji Temu</h4>
                <p class="text-muted small mb-0">Data pesan masuk dari website publik.</p>
            </div>
            <?php if(isset($_GET['status_updated'])): ?>
                <div class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
                    <i class="fas fa-check me-1"></i> Terupdate
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="stat-box" style="border-left-color: var(--jhc-red);">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.55rem;">Pesan Baru</small>
                    <h5 class="fw-bold mb-0"><?= $total_new; ?></h5>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-box">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.55rem;">Total Data</small>
                    <h5 class="fw-bold mb-0"><?= $total_all; ?></h5>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="120">Waktu</th>
                            <th width="180">Nama Pasien</th>
                            <th width="200">Kontak & Email</th>
                            <th>Pesan</th>
                            <th class="text-center" width="100">Status</th>
                            <th class="text-end" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)): ?>
                            <?php foreach($appointments as $row): 
                                $is_new = ($row['status'] == 'new');
                                $badge_class = 'badge-' . $row['status'];
                                
                                // Format WA Link
                                $wa_number = preg_replace('/[^0-9]/', '', $row['phone']);
                                if(substr($wa_number, 0, 1) == '0') $wa_number = '62' . substr($wa_number, 1);
                                $wa_link = "https://wa.me/{$wa_number}";
                            ?>
                                <tr class="<?= $is_new ? 'row-unread' : ''; ?>">
                                    <td>
                                        <div class="small text-muted"><?= date('d/m/y', strtotime($row['submission_date'])); ?></div>
                                        <div class="fw-bold"><?= date('H:i', strtotime($row['submission_date'])); ?></div>
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <div>
                                            <a href="<?= $wa_link ?>" target="_blank" class="whatsapp-link small">
                                                <i class="fab fa-whatsapp me-1"></i><?= htmlspecialchars($row['phone']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <i class="far fa-envelope me-1"></i><?= htmlspecialchars($row['email']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['message']); ?>">
                                            <?= htmlspecialchars($row['message']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge <?= $badge_class; ?>">
                                            <?= $row['status'] == 'new' ? 'Baru' : ($row['status'] == 'read' ? 'Dibaca' : 'Selesai'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <?php if($row['status'] == 'new'): ?>
                                                <a href="appointments.php?action=read&id=<?= $row['id']; ?>" 
                                                   class="btn-action-mini btn-outline-primary" title="Baca">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if($row['status'] != 'contacted'): ?>
                                                <a href="appointments.php?action=contacted&id=<?= $row['id']; ?>" 
                                                   class="btn-action-mini btn-success text-white" title="Selesaikan">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <i class="fas fa-check-double text-success small"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted small">Inbox kosong.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>