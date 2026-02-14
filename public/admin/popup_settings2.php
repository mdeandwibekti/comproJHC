<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    // Ambil info file sebelum dihapus dari database
    $stmt = $mysqli->prepare("SELECT image_path FROM popups WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // Hapus fisik file (Path di DB adalah 'assets/img/popups/...' jadi butuh '../' untuk akses dari admin)
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            @unlink("../" . $row['image_path']);
        }
    }
    
    $mysqli->query("DELETE FROM popups WHERE id = $del_id");
    header("location: popup_settings2.php?msg=deleted");
    exit();
}

// --- 2. LOGIKA SIMPAN (TAMBAH/EDIT) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'inactive';
    $image_path = $_POST['current_image'] ?? '';

    // Folder upload fisik dari sisi admin: ../assets/img/popups/
    $upload_dir = "../assets/img/popups/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // Handle Upload Gambar Baru
    if (isset($_FILES["popup_image"]) && $_FILES["popup_image"]["error"] == 0) {
        $file_name = $_FILES["popup_image"]["name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $new_filename = 'popup_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus file lama jika sedang melakukan EDIT dan ada file baru
                if (!empty($_POST['current_image']) && file_exists("../" . $_POST['current_image'])) {
                    @unlink("../" . $_POST['current_image']);
                }
                // Path yang disimpan di DB tanpa ../ agar frontend mudah memanggil 'public/' . $path
                $image_path = 'assets/img/popups/' . $new_filename;
            }
        }
    }

    if ($id > 0) {
        // Mode UPDATE
        $stmt = $mysqli->prepare("UPDATE popups SET title=?, content=?, image_path=?, status=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $content, $image_path, $status, $id);
    } else {
        // Mode INSERT
        $stmt = $mysqli->prepare("INSERT INTO popups (title, content, image_path, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $image_path, $status);
    }
    
    if($stmt->execute()){
        header("location: popup_settings2.php?msg=saved");
    } else {
        echo "Error: " . $stmt->error;
    }
    exit();
}

// Ambil data semua popup untuk ditampilkan di tabel
$popups = $mysqli->query("SELECT * FROM popups ORDER BY created_at DESC");

require_once 'layout/header.php';
?>

<style>
    :root { --jhc-red: #8a3033; --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #fff; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 25px; border: 1px solid #eee; }
    .table-sm-custom { font-size: 0.85rem; }
    .img-preview-mini { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; background: #f8f9fa; }
    .badge-status { font-size: 0.7rem; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; font-weight: 800; }
    .badge-active { background: #e9f7f3; color: #2d9d78; border: 1px solid #bbf7d0; }
    .badge-inactive { background: #fdf2f2; color: var(--jhc-red); border: 1px solid #feb2b2; }
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; }
</style>

<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3 ms-1">
        <ol class="breadcrumb bg-transparent p-0 m-0" style="font-size: 0.75rem;">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold" style="color: var(--jhc-red);">Promotional Popups</li>
        </ol>
    </nav>

    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark" style="font-size: 1.25rem;">Popup Management</h4>
                <p class="text-muted small mb-0">Kelola banner promo yang tampil secara otomatis di beranda.</p>
            </div>
            <button type="button" class="btn btn-sm btn-danger px-4 rounded-pill fw-bold shadow-sm" 
                    data-bs-toggle="modal" data-bs-target="#popupModal" onclick="resetForm()" 
                    style="background: var(--jhc-red); border: none;">
                <i class="fas fa-plus me-1"></i> Tambah Baru
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success py-2 px-3 mb-4 border-0 shadow-sm d-flex align-items-center" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="fas fa-check-circle me-2"></i> 
                <?= $_GET['msg'] == 'saved' ? 'Data berhasil disimpan dan diperbarui.' : 'Data popup berhasil dihapus.'; ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm-custom">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center">Preview</th>
                        <th>Informasi Promo</th>
                        <th class="text-center" width="120">Status</th>
                        <th class="text-end" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($popups && $popups->num_rows > 0): ?>
                        <?php while($p = $popups->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center">
                                <?php if(!empty($p['image_path'])): ?>
                                    <img src="../<?= htmlspecialchars($p['image_path']) ?>" class="img-preview-mini shadow-sm">
                                <?php else: ?>
                                    <div class="img-preview-mini d-flex align-items-center justify-content-center bg-light">
                                        <i class="fas fa-image text-muted opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['title']) ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 350px;">
                                    <?= htmlspecialchars(strip_tags($p['content'])) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-status <?= $p['status'] == 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $p['status'] == 'active' ? 'Aktif' : 'Mati' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn-action btn btn-sm btn-outline-primary border me-1" 
                                        onclick='editPopup(<?= json_encode($p) ?>)' title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <a href="?delete_id=<?= $p['id'] ?>" 
                                   class="btn-action btn btn-sm btn-outline-danger border" 
                                   onclick="return confirm('Hapus popup ini secara permanen?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted small">Belum ada data popup promo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="popupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="" method="post" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold m-0"><i class="fas fa-edit me-2 text-danger"></i>Form Editor Popup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="form_id">
                <input type="hidden" name="current_image" id="form_current_image">
                
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Judul Promo</label>
                            <input type="text" name="title" id="form_title" class="form-control" placeholder="Contoh: Promo Ramadhan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Deskripsi Pesan</label>
                            <textarea name="content" id="form_content" class="form-control" rows="5" placeholder="Tuliskan detail promo di sini..."></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase d-block">Status Tampilan</label>
                            <select name="status" id="form_status" class="form-select">
                                <option value="active" class="text-success fw-bold">Aktif (Muncul di Beranda)</option>
                                <option value="inactive" class="text-muted" selected>Mati (Simpan sebagai Draft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted text-uppercase">Gambar Banner</label>
                        <div id="previewContainer" class="bg-light rounded-3 text-center p-2 border mb-2 d-flex align-items-center justify-content-center" style="min-height: 200px; overflow: hidden;">
                            <i class="fas fa-image fa-3x opacity-25"></i>
                        </div>
                        <input type="file" name="popup_image" id="imageInput" class="form-control form-control-sm shadow-sm" accept="image/*">
                        <div class="x-small text-muted mt-2 fst-italic"><i class="fas fa-info-circle me-1"></i>Rekomendasi: 800x800px (JPG/PNG).</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" style="background: var(--jhc-red); border: none;">
                    <i class="fas fa-save me-1"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function resetForm() {
        document.getElementById('form_id').value = '';
        document.getElementById('form_title').value = '';
        document.getElementById('form_content').value = '';
        document.getElementById('form_status').value = 'inactive';
        document.getElementById('form_current_image').value = '';
        document.getElementById('previewContainer').innerHTML = '<i class="fas fa-image fa-3x opacity-25"></i>';
    }

    function editPopup(data) {
        document.getElementById('form_id').value = data.id;
        document.getElementById('form_title').value = data.title;
        document.getElementById('form_content').value = data.content;
        document.getElementById('form_status').value = data.status;
        document.getElementById('form_current_image').value = data.image_path;
        
        if(data.image_path) {
            document.getElementById('previewContainer').innerHTML = `<img src="../${data.image_path}" class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
        } else {
            document.getElementById('previewContainer').innerHTML = '<i class="fas fa-image fa-3x opacity-25"></i>';
        }
        
        new bootstrap.Modal(document.getElementById('popupModal')).show();
    }

    document.getElementById('imageInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const url = URL.createObjectURL(file);
            document.getElementById('previewContainer').innerHTML = `<img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>