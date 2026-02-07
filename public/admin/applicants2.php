<?php
require_once '../../config.php';
require_once 'layout/header.php';

// Pastikan koneksi database ada
if (!$mysqli) { die("Koneksi database hilang."); }

// Ambil data pelamar dengan JOIN ke tabel careers agar nama posisi muncul
$sql = "SELECT a.*, c.job_title 
        FROM applicants a 
        LEFT JOIN careers c ON a.job_id = c.id 
        ORDER BY a.applied_at DESC";
$result = $mysqli->query($sql);
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-red-light: #bd3030;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

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

    /* Status Badge Styling */
    .badge-status { padding: 6px 14px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
    .status-pending { background-color: #fff4e5; color: #b7791f; }
    .status-accepted { background-color: #e6f4ea; color: #1e7e34; }
    .status-rejected { background-color: #fceaea; color: #c53030; }

    .btn-download-cv {
        color: var(--jhc-red-dark);
        border: 1px solid var(--jhc-red-dark);
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: 0.3s;
        padding: 5px 15px;
        text-decoration: none;
    }
    .btn-download-cv:hover {
        background: var(--jhc-gradient);
        color: white !important;
        border-color: transparent;
    }

    .btn-action-jhc { border-radius: 50px; font-weight: 700; font-size: 0.8rem; padding: 5px 15px; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="page-header-jhc">
            <h3 class="fw-bold mb-1 text-dark">Daftar Pelamar Pekerjaan</h3>
            <p class="text-muted small mb-0">Tinjau CV, ubah status rekrutmen, atau hapus data pelamar.</p>
        </div>

        <div id="status-update-message"></div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Pelamar</th>
                        <th>Posisi</th>
                        <th>Kontak</th>
                        <th class="text-center">Berkas</th>
                        <th class="text-center">Status</th>
                        <th>Tanggal Masuk</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($row['education']); ?></div>
                                </td>
                                <td class="fw-semibold text-primary">
                                    <?= htmlspecialchars($row['job_title'] ?? 'Posisi Terhapus'); ?>
                                </td>
                                <td>
                                    <div class="small"><i class="fas fa-phone-alt me-1 text-muted"></i> <?= htmlspecialchars($row['phone']); ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['email']); ?></div>
                                </td>
                                
                                <td class="text-center">
                                    <?php $file_path = "../../" . $row['cv_path']; ?>
                                    <a href="<?= $file_path; ?>" target="_blank" class="btn btn-sm btn-download-cv">
                                        <i class="fas fa-file-pdf me-1"></i> Lihat CV
                                    </a>
                                </td>

                                <td class="text-center">
                                    <?php 
                                        $status = $row['status'];
                                        $cls = 'status-pending';
                                        if($status == 'Diterima') $cls = 'status-accepted';
                                        if($status == 'Ditolak') $cls = 'status-rejected';
                                    ?>
                                    <span class="badge-status <?= $cls; ?>"><?= htmlspecialchars($status); ?></span>
                                </td>
                                
                                <td class="text-muted small"><?= date('d/m/y H:i', strtotime($row['applied_at'])); ?></td>
                                
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <button class="btn btn-sm btn-success btn-action-jhc update-status-btn" 
                                                    data-id="<?= $row['id']; ?>" data-status="Diterima" title="Terima Pelamar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-action-jhc update-status-btn" 
                                                    data-id="<?= $row['id']; ?>" data-status="Ditolak" title="Tolak Pelamar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-outline-secondary btn-action-jhc delete-applicant-btn" 
                                                data-id="<?= $row['id']; ?>" title="Hapus Permanen">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada pelamar yang masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // LOGIKA UPDATE STATUS (TERIMA/TOLAK)
    const statusButtons = document.querySelectorAll('.update-status-btn');
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const applicantId = this.dataset.id;
            const newStatus = this.dataset.status;
            const messageDiv = document.getElementById('status-update-message');
            
            if (confirm(`Ubah status pelamar menjadi "${newStatus}"?`)) {
                const formData = new FormData();
                formData.append('applicant_id', applicantId);
                formData.append('status', newStatus);

                fetch('../api/update_applicant_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = `<div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i> ${data.message}</div>`;
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(err => alert('Terjadi kesalahan koneksi.'));
            }
        });
    });

    // LOGIKA HAPUS PELAMAR
    const deleteButtons = document.querySelectorAll('.delete-applicant-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const applicantId = this.dataset.id;
            const messageDiv = document.getElementById('status-update-message');
            
            if (confirm('Hapus data pelamar ini secara permanen? File CV juga akan dihapus dari server.')) {
                const formData = new FormData();
                formData.append('applicant_id', applicantId);

                fetch('../api/delete_applicant.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = `<div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-trash me-2"></i> ${data.message}</div>`;
                        this.closest('tr').style.opacity = '0.3'; // Efek visual terhapus
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Gagal menghapus: ' + data.message);
                    }
                })
                .catch(err => alert('Terjadi kesalahan koneksi server.'));
            }
        });
    });
});
</script>

<?php require_once 'layout/footer.php'; ?>