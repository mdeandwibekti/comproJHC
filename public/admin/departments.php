<?php
require_once "../../config.php";
require_once 'layout/header.php';

if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='departments.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data termasuk kolom baru: education dan expertise
$sql = "SELECT id, name, category, icon_path, display_order, description, education, expertise FROM departments ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);
?>

<style>
    :root { --primary-red: #D32F2F; --light-bg: #f8f9fa; }
    .page-header { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red); margin-bottom: 2rem; }
    .main-card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .table thead th { background-color: #f8f9fa; color: #444; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
    .badge-category { padding: 0.5em 1em; border-radius: 50px; font-weight: 500; font-size: 0.8rem; }
    .badge-layanan { background-color: #ffebee; color: var(--primary-red); border: 1px solid #ffcdd2; }
    .badge-poli { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
    .btn-add { background-color: var(--primary-red); color: white; border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.3s; }
    .btn-add:hover { background-color: #b71c1c; color: white; box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3); }
    .icon-preview { width: 40px; height: 40px; object-fit: contain; background: #f1f1f1; border-radius: 8px; padding: 5px; }
    .btn-action { padding: 6px 14px; font-size: 0.85rem; font-weight: 600; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s; border: 1px solid transparent; }
    
    /* Tombol Biru untuk Detail */
    .btn-view-action { background-color: #f0f4f8; color: #444; border: 1px solid #d1d9e0; margin-right: 5px; }
    .btn-view-action:hover { background-color: #e2e8f0; color: #000; }
    
    .btn-edit-action { background-color: #e3f2fd; color: #1976d2; margin-right: 5px; }
    .btn-edit-action:hover { background-color: #1976d2; color: white; }
    
    .btn-del-action { background-color: #ffebee; color: #c62828; }
    .btn-del-action:hover { background-color: #c62828; color: white; }

    /* Modal Styling */
    .modal-content { border: none; border-radius: 15px; }
    .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #eee; border-radius: 15px 15px 0 0; }
    .info-label { font-weight: 700; color: var(--primary-red); display: block; margin-bottom: 5px; font-size: 0.9rem; text-transform: uppercase; }
    .info-content { color: #555; margin-bottom: 1.5rem; line-height: 1.6; }
</style>

<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-clinic-medical me-2 text-danger"></i> Departments & Services</h3>
            <p class="text-muted mb-0 small">Kelola data poliklinik dan layanan unggulan rumah sakit.</p>
        </div>
        <a href="department_edit.php" class="btn-add"><i class="fas fa-plus me-2"></i> Add New</a>
    </div>

    <?php if (isset($_GET['deleted']) || isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
            <i class="fas fa-check-circle me-2"></i> Berhasil memperbarui data!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 8%;">Order</th>
                            <th style="width: 10%;">Icon</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-center" style="width: 25%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $row['display_order']; ?></td>
                                    <td class="text-center">
                                        <img src="../<?= !empty($row['icon_path']) ? htmlspecialchars($row['icon_path']) : 'assets/img/default-icon.png'; ?>" class="icon-preview">
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <span class="badge badge-category <?= ($row['category'] == 'Layanan') ? 'badge-layanan' : 'badge-poli'; ?>">
                                            <i class="fas <?= ($row['category'] == 'Layanan') ? 'fa-star' : 'fa-stethoscope'; ?> me-1"></i> <?= $row['category']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button type="button" class="btn-action btn-view-action btn-detail" 
                                                data-name="<?= htmlspecialchars($row['name']); ?>"
                                                data-desc="<?= htmlspecialchars($row['description']); ?>"
                                                data-expertise="<?= htmlspecialchars($row['expertise']); ?>"
                                                data-education="<?= htmlspecialchars($row['education']); ?>">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>

                                            <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn-action btn-edit-action">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="departments.php?delete=<?= $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Hapus data ini?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailDept" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-info-circle text-danger me-2"></i> Detail <span id="view-name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <span class="info-label"><i class="fas fa-align-left me-2"></i> Deskripsi Layanan</span>
                        <p id="view-desc" class="info-content"></p>

                        <span class="info-label"><i class="fas fa-star me-2"></i> Keahlian Khusus / Fasilitas</span>
                        <div id="view-expertise" class="info-content p-3 bg-light rounded"></div>

                        <span class="info-label mt-3"><i class="fas fa-graduation-cap me-2"></i> Pendidikan / Informasi Tambahan</span>
                        <p id="view-education" class="info-content"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailButtons = document.querySelectorAll('.btn-detail');
    const modal = new bootstrap.Modal(document.getElementById('modalDetailDept'));

    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Ambil data dari atribut tombol
            const name = this.getAttribute('data-name');
            const desc = this.getAttribute('data-desc') || 'Tidak ada deskripsi.';
            const expertise = this.getAttribute('data-expertise') || 'Informasi keahlian belum diisi.';
            const education = this.getAttribute('data-education') || 'Informasi pendidikan belum diisi.';

            // Masukkan data ke dalam modal
            document.getElementById('view-name').innerText = name;
            document.getElementById('view-desc').innerText = desc;
            document.getElementById('view-expertise').innerText = expertise;
            document.getElementById('view-education').innerText = education;

            // Tampilkan modal
            modal.show();
        });
    });
});
</script>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>