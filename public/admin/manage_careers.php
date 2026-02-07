<?php
require_once '../../config.php'; // Pastikan path ke config.php benar
require_once 'layout/header.php';

// 1. Logika Hapus Data
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM careers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Lowongan berhasil dihapus!'); window.location.href='manage_careers.php';</script>";
    }
}

// 2. Ambil data dari database careers
$sql = "SELECT * FROM careers ORDER BY id DESC";
$result = $mysqli->query($sql);
?>

<style>
    .main-wrapper {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .status-badge { border-radius: 50px; padding: 5px 15px; font-size: 0.75rem; font-weight: 600; }
    .bg-open { background-color: #e6f4ea; color: #1e7e34; }
    .bg-closed { background-color: #f1f1f1; color: #6c757d; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Manajemen Lowongan Pekerjaan</h4>
                <p class="text-muted small">Kelola daftar posisi yang tampil di halaman karir pengunjung.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i> Tambah Lowongan
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Posisi Pekerjaan</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id']; ?></td>
                            <td><span class="fw-bold"><?= htmlspecialchars($row['job_title']); ?></span></td>
                            <td><small class="text-muted"><?= substr(htmlspecialchars($row['description']), 0, 100); ?>...</small></td>
                            <td class="text-center">
                                <span class="status-badge <?= ($row['status'] == 'Open') ? 'bg-open' : 'bg-closed'; ?>">
                                    <?= $row['status']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="edit_career.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <a href="manage_careers.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus lowongan ini?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data lowongan di database.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="process_manage_career.php" method="POST" class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Posisi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Posisi</label>
                    <input type="text" name="job_title" class="form-control" required placeholder="Contoh: Perawat Anak">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Deskripsi Pekerjaan</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="Open">Open (Tampil di Web)</option>
                        <option value="Closed">Closed (Sembunyikan)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="save_career" class="btn btn-primary w-100 rounded-pill">Simpan Lowongan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>