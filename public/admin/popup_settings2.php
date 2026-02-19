<?php
require_once "../../config.php";

/**
 * --- 1. LOGIKA HAPUS (DELETE CRUD) ---
 * Menghapus data dari database dan file fisik agar hosting tidak penuh
 */
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    $stmt = $mysqli->prepare("SELECT image_path FROM popups WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // Hapus fisik file dari folder public
        if (!empty($row['image_path']) && file_exists("../public/" . $row['image_path'])) {
            @unlink("../public/" . $row['image_path']);
        }
    }
    
    $mysqli->query("DELETE FROM popups WHERE id = $del_id");
    header("location: popup_settings2.php?msg=deleted");
    exit();
}

/**
 * --- 2. LOGIKA SIMPAN (INSERT / UPDATE) ---
 * Integrasi kolom wa_link sesuai struktur database terbaru
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id         = intval($_POST['id'] ?? 0);
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $status     = $_POST['status'] ?? 'inactive';
    $wa_link    = trim($_POST['wa_link'] ?? ''); 
    $image_path = $_POST['current_image'] ?? '';

    // Folder target fisik: ../public/assets/img/popups/
    $upload_dir = "../public/assets/img/popups/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // Proses Upload Gambar Baru
    if (isset($_FILES["popup_image"]) && $_FILES["popup_image"]["error"] == 0) {
        $ext = strtolower(pathinfo($_FILES["popup_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $new_filename = 'popup_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES["popup_image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Hapus gambar lama jika ganti file
                if (!empty($_POST['current_image']) && file_exists("../public/" . $_POST['current_image'])) {
                    @unlink("../public/" . $_POST['current_image']);
                }
                // Simpan path relatif terhadap folder public
                $image_path = 'public/assets/img/popups/' . $new_filename;
            }
        }
    }

    if ($id > 0) {
        // UPDATE record yang sudah ada
        $stmt = $mysqli->prepare("UPDATE popups SET title=?, content=?, image_path=?, status=?, wa_link=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $content, $image_path, $status, $wa_link, $id);
    } else {
        // INSERT record baru
        $stmt = $mysqli->prepare("INSERT INTO popups (title, content, image_path, status, wa_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $content, $image_path, $status, $wa_link);
    }
    
    $stmt->execute();
    header("location: popup_settings2.php?msg=saved");
    exit();
}

$popups = $mysqli->query("SELECT * FROM popups ORDER BY created_at DESC");
require_once 'layout/header.php';
?>

<style>
    :root { --jhc-red: #8a3033; --jhc-gradient: linear-gradient(135deg, #8a3033 0%, #bd3030 100%); }
    .main-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px; }
    .img-preview-mini { width: 60px; height: 60px; object-fit: cover; border-radius: 12px; border: 1px solid #eee; }
    .badge-status { font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; font-weight: 800; }
    .bg-aktif { background: #e8f5e9; color: #2e7d32; }
    .bg-mati { background: #ffebee; color: #c62828; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0 text-dark">Manajemen Popup Promo</h4>
            <button class="btn btn-danger px-4 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#popupModal" onclick="resetForm()">
                <i class="fas fa-plus me-2"></i> Tambah Baru
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> Operasi berhasil dilakukan!
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Preview</th>
                        <th>Informasi Promo & WhatsApp</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $popups->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../public/<?= htmlspecialchars($p['image_path']) ?>?t=<?= time() ?>" 
                                 class="img-preview-mini shadow-sm" 
                                 onerror="this.src='../public/assets/img/gallery/logo.png'">
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['title']) ?></div>
                            <div class="small text-success fw-bold"><i class="fab fa-whatsapp me-1"></i> <?= $p['wa_link'] ?: 'Tanpa Link WA'; ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge-status <?= $p['status'] == 'active' ? 'bg-aktif' : 'bg-mati' ?>">
                                <?= strtoupper($p['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary border-0" onclick='editPopup(<?= json_encode($p) ?>)'><i class="fas fa-edit"></i></button>
                            <a href="?delete_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus popup ini?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="popupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="" method="post" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="fw-bold m-0">Editor Popup Promo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="form_id">
                <input type="hidden" name="current_image" id="form_current_image">
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">JUDUL PROMO</label>
                            <input type="text" name="title" id="form_title" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-success">LINK WHATSAPP (URL WA.ME)</label>
                            <input type="text" name="wa_link" id="form_wa_link" class="form-control rounded-3" placeholder="https://wa.me/62812345678">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">ISI PESAN</label>
                            <textarea name="content" id="form_content" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">STATUS TAMPILAN</label>
                            <select name="status" id="form_status" class="form-select rounded-3">
                                <option value="active">Aktif</option>
                                <option value="inactive">Mati (Draft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">BANNER PROMO</label>
                        <div id="previewContainer" class="bg-light rounded-4 text-center border p-2 mb-2 d-flex align-items-center justify-content-center" style="min-height: 200px; overflow: hidden;">
                            <i class="fas fa-image fa-3x opacity-25"></i>
                        </div>
                        <input type="file" name="popup_image" id="imageInput" class="form-control form-control-sm shadow-sm" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-danger w-100 rounded-pill py-3 fw-bold shadow-sm" style="background: var(--jhc-red); border: none;">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>

<script>
    function resetForm() {
        document.getElementById('form_id').value = '';
        document.getElementById('form_title').value = '';
        document.getElementById('form_content').value = '';
        document.getElementById('form_wa_link').value = '';
        document.getElementById('form_status').value = 'active';
        document.getElementById('form_current_image').value = '';
        document.getElementById('previewContainer').innerHTML = '<i class="fas fa-image fa-3x opacity-25"></i>';
    }

    function editPopup(data) {
        document.getElementById('form_id').value = data.id;
        document.getElementById('form_title').value = data.title;
        document.getElementById('form_content').value = data.content;
        document.getElementById('form_wa_link').value = data.wa_link || '';
        document.getElementById('form_status').value = data.status;
        document.getElementById('form_current_image').value = data.image_path;
        
        if(data.image_path) {
            document.getElementById('previewContainer').innerHTML = `<img src="../public/${data.image_path}" class="img-fluid rounded-3 shadow-sm">`;
        }
        new bootstrap.Modal(document.getElementById('popupModal')).show();
    }

    document.getElementById('imageInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('previewContainer').innerHTML = `<img src="${URL.createObjectURL(file)}" class="img-fluid rounded-3 shadow-sm">`;
        }
    };
</script>

<?php require_once 'layout/footer.php'; ?>