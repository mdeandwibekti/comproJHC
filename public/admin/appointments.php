<?php
require_once "../../config.php";

// --- LOGIKA PEMROSESAN (Harus SEBELUM require layout/header.php untuk mencegah error header) ---
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

// Ambil data janji temu (Pastikan nama kolom sesuai dengan database Anda)
$sql = "SELECT * FROM appointments ORDER BY submission_date DESC";
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

    /* Tabel Styling sesuai referensi */
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

    /* Row Highlight untuk Pesan Baru */
    .row-new { background-color: rgba(138, 48, 51, 0.03); font-weight: 600; }

    /* Badge Status */
    .badge-status { padding: 6px 14px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    .status-new { background-color: #fceaea; color: #c53030; }
    .status-read { background-color: #e3f2fd; color: #1976d2; }
    .status-contacted { background-color: #e6f4ea; color: #1e7e34; }

    .btn-action-jhc { 
        border-radius: 50px; 
        font-weight: 700; 
        font-size: 0.75rem; 
        padding: 5px 15px; 
        transition: 0.3s;
    }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Janji Temu Pasien</h3>
                <p class="text-muted small mb-0">Kelola permintaan konsultasi dan janji temu yang masuk melalui website.</p>
            </div>
            <?php if(isset($_GET['status_updated'])): ?>
                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check me-1"></i> Status Diperbarui</span>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Waktu Masuk</th>
                        <th>Nama Pasien</th>
                        <th>Kontak / Email</th>
                        <th>Isi Pesan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $is_new = ($row['status'] == 'new');
                            $row_class = $is_new ? 'row-new' : '';
                            
                            // Tentukan Badge Status
                            $status_label = htmlspecialchars($row['status']);
                            $badge_class = 'status-' . $row['status'];
                        ?>
                            <tr class="<?= $row_class; ?>">
                                <td class="text-muted small">
                                    <?= date('d M Y', strtotime($row['submission_date'])); ?><br>
                                    <span class="fw-bold"><?= date('H:i', strtotime($row['submission_date'])); ?> WIB</span>
                                </td>
                                <td>
                                    <div class="text-dark fw-bold"><?= htmlspecialchars($row['name']); ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="fab fa-whatsapp text-success me-1"></i> <?= htmlspecialchars($row['phone']); ?></div>
                                    <div class="x-small text-muted"><?= htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td>
                                    <div class="text-muted small" style="max-width: 300px;"><?= nl2br(htmlspecialchars($row['message'])); ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge-status <?= $badge_class; ?> text-uppercase"><?= $status_label; ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <?php if($row['status'] == 'new'): ?>
                                            <a href="appointments.php?action=read&id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary btn-action-jhc" title="Tandai Sudah Dibaca">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($row['status'] != 'contacted'): ?>
                                            <a href="appointments.php?action=contacted&id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-success btn-action-jhc" title="Tandai Sudah Dihubungi">
                                                <i class="fas fa-check"></i> Hubungi
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fas fa-user-check me-1"></i> Selesai</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 d-block opacity-25"></i>
                                Belum ada data janji temu yang masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>