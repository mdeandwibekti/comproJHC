<?php
require_once '../../config.php';
require_once 'layout/header.php';

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Jika form disubmit (Proses Update)
if (isset($_POST['update_career'])) {
    $title = $_POST['job_title'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $stmt = $mysqli->prepare("UPDATE careers SET job_title = ?, description = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $desc, $status, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data lowongan berhasil diperbarui!'); window.location.href='manage_careers.php';</script>";
    } else {
        $error = "Gagal memperbarui data: " . $mysqli->error;
    }
}

// 3. Ambil data lama untuk ditampilkan di form
$stmt_get = $mysqli->prepare("SELECT * FROM careers WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$data = $result->fetch_assoc();

// Jika ID tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='manage_careers.php';</script>";
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-edit me-2 text-primary"></i> Edit Lowongan Pekerjaan</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Posisi Pekerjaan</label>
                            <input type="text" name="job_title" class="form-control" 
                                   value="<?= htmlspecialchars($data['job_title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deskripsi & Kualifikasi</label>
                            <textarea name="description" class="form-control" rows="8" required><?= htmlspecialchars($data['description']); ?></textarea>
                            <div class="form-text">Gunakan kalimat yang jelas untuk menarik pelamar.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Status Lowongan</label>
                            <select name="status" class="form-select">
                                <option value="Open" <?= $data['status'] == 'Open' ? 'selected' : ''; ?>>Open (Tampil di Website)</option>
                                <option value="Closed" <?= $data['status'] == 'Closed' ? 'selected' : ''; ?>>Closed (Sembunyikan)</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update_career" class="btn btn-primary px-4 rounded-pill">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="manage_careers.php" class="btn btn-light px-4 rounded-pill">Batal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>