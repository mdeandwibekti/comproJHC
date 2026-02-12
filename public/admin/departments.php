<?php
require_once "../../config.php";

// --- LOGIKA PENGHAPUSAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    // ... ambil input teks lainnya ...

    // Ambil path lama jika sedang edit
    $image_path = $_POST['old_image'] ?? '';

    // LOGIKA UPLOAD GAMBAR UTAMA
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $upload_dir = "../assets/img/departments/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_name = "dept_" . time() . "_" . uniqid() . "." . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
            // Hapus file lama jika ada
            if (!empty($image_path) && file_exists("../" . $image_path)) unlink("../" . $image_path);
            $image_path = "assets/img/departments/" . $new_name;
        }
    }

    // Eksekusi Update/Insert (Sesuaikan query Anda)
    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE departments SET name=?, category=?, image_path=?, ... WHERE id=?");
        // ... bind_param ...
    } else {
        $stmt = $mysqli->prepare("INSERT INTO departments (name, category, image_path, ...) VALUES (?, ?, ?, ...)");
        // ... bind_param ...
    }
}

// Ambil data lengkap
$sql = "SELECT id, name, category, icon_path, image_path, display_order, description, special_skills, additional_info 
        FROM departments 
        ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red-dark: #8a3033;
        --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
    }

    .admin-wrapper {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 35px;
        margin-top: 20px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .page-header-jhc {
        border-left: 5px solid var(--jhc-red-dark);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Table Customization */
    .table-responsive { border-radius: 15px; border: 1px solid #f0f0f0; }
    .table thead th { 
        background-color: #fcfcfc; 
        color: #888; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 1px;
        padding: 15px;
        border-bottom: 2px solid #f4f4f4;
    }

    .table tbody td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f8f8f8; }

    /* UI Components */
    .icon-box {
        width: 48px; height: 48px;
        background: #fff5f5;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid #ffeaea;
    }
    .icon-box img { width: 28px; height: 28px; object-fit: contain; }

    .badge-jhc {
        padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 0.7rem;
    }
    .badge-layanan { background: #fff0f0; color: #d63031; }
    .badge-poli { background: #eef5ff; color: #0984e3; }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .btn-jhc-add { 
        background: var(--jhc-gradient); color: white; border-radius: 12px; 
        padding: 12px 25px; font-weight: 700; border: none;
        box-shadow: 0 4px 15px rgba(138, 48, 51, 0.2);
    }
</style>

<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Departments & Services</h4>
            <a href="department_edit.php" class="btn btn-primary" style="background: #8a3033; border: none; border-radius: 10px;">
                <i class="fas fa-plus me-2"></i> Tambah Data
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Order</th>
                        <th>Media</th>
                        <th>Departemen</th>
                        <th>Kategori</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $row['display_order']; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light p-2 rounded" style="width: 50px; height: 50px;">
                                    <img src="../<?= $row['icon_path'] ?: 'assets/img/icons/default.png'; ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <?php if($row['image_path']): ?>
                                    <img src="../<?= $row['image_path']; ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= $row['name']; ?></div>
                            <div class="text-muted small"><?= substr(strip_tags($row['description']), 0, 50); ?>...</div>
                        </td>
                        <td>
                            <span class="badge <?= $row['category'] == 'Layanan' ? 'bg-danger' : 'bg-primary'; ?> rounded-pill">
                                <?= $row['category']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="department_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-light rounded-circle text-primary"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?= $row['id']; ?>" class="btn btn-sm btn-light rounded-circle text-danger" onclick="return confirm('Hapus data?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>