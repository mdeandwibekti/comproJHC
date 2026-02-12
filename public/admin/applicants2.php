<?php
require_once '../../config.php';
require_once 'layout/header.php';

if (!$mysqli) { die("Koneksi database hilang."); }

$sql = "SELECT a.*, c.job_title 
        FROM applicants a 
        LEFT JOIN careers c ON a.job_id = c.id 
        ORDER BY a.applied_at DESC";
$result = $mysqli->query($sql);
?>

<style>
    :root { --jhc-red-dark: #8a3033; --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; margin-top: 20px; }
    .page-header-jhc { border-left: 4px solid var(--jhc-red-dark); padding-left: 20px; margin-bottom: 30px; }
    .badge-status { padding: 6px 14px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    .status-pending { background-color: #fff4e5; color: #b7791f; }
    .status-accepted { background-color: #e6f4ea; color: #1e7e34; }
    .status-rejected { background-color: #fceaea; color: #c53030; }
    .btn-download-cv { color: var(--jhc-red-dark); border: 1px solid var(--jhc-red-dark); border-radius: 50px; font-size: 0.8rem; padding: 5px 15px; transition: 0.3s; }
    .btn-download-cv:hover { background: var(--jhc-gradient); color: white !important; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc">
            <h3 class="fw-bold mb-1">Daftar Pelamar Pekerjaan</h3>
            <p class="text-muted small mb-0">Lokasi Berkas: <code>public/uploads/cv/</code></p>
        </div>

        <div id="status-update-message"></div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Pelamar</th>
                        <th>Posisi</th>
                        <th>Kontak</th>
                        <th class="text-center">Berkas</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($row['education']); ?></div>
                                </td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($row['job_title'] ?? 'Posisi Terhapus'); ?></td>
                                <td>
                                    <div class="small"><i class="fas fa-phone-alt me-1"></i> <?= htmlspecialchars($row['phone']); ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td class="text-center">
                                    <a href="../<?= htmlspecialchars($row['cv_path']); ?>" target="_blank" class="btn btn-sm btn-download-cv">
                                        <i class="fas fa-file-pdf me-1"></i> Lihat CV
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $status = $row['status'];
                                        $cls = ($status == 'Diterima') ? 'status-accepted' : (($status == 'Ditolak') ? 'status-rejected' : 'status-pending');
                                    ?>
                                    <span class="badge-status <?= $cls; ?>"><?= htmlspecialchars($status); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <button class="btn btn-sm btn-success rounded-circle update-status-btn" data-id="<?= $row['id']; ?>" data-status="Diterima"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-sm btn-danger rounded-circle update-status-btn" data-id="<?= $row['id']; ?>" data-status="Ditolak"><i class="fas fa-times"></i></button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle delete-applicant-btn" data-id="<?= $row['id']; ?>"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada pelamar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Logic AJAX untuk Update & Delete tetap sama seperti sebelumnya
document.addEventListener('DOMContentLoaded', function() {
    // Update Status
    document.querySelectorAll('.update-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const status = this.dataset.status;
            if(confirm(`Ubah status ke ${status}?`)) {
                const fd = new FormData();
                fd.append('applicant_id', id);
                fd.append('status', status);
                fetch('../api/update_applicant_status.php', { method: 'POST', body: fd })
                .then(res => res.json()).then(data => { if(data.success) location.reload(); });
            }
        });
    });

    // Delete
    document.querySelectorAll('.delete-applicant-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if(confirm('Hapus pelamar & file CV secara permanen?')) {
                const fd = new FormData();
                fd.append('applicant_id', id);
                fetch('../api/delete_applicant.php', { method: 'POST', body: fd })
                .then(res => res.json()).then(data => { if(data.success) location.reload(); });
            }
        });
    });
});
</script>

<?php require_once 'layout/footer.php'; ?>