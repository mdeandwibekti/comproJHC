<?php
require_once "../../config.php";

// --- 1. LOGIKA HAPUS DATA ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    $query_check = "SELECT icon_path, image_path FROM departments WHERE id = ?";
    if ($stmt_check = $mysqli->prepare($query_check)) {
        $stmt_check->bind_param("i", $id_to_delete);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $data_img = $result_check->fetch_assoc();
        $stmt_check->close();
    }

    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Gunakan path yang sama dengan tampilan agar file benar-benar terhapus
            if (!empty($data_img['icon_path']) && file_exists("../../public/" . $data_img['icon_path'])) {
                @unlink("../../public/" . $data_img['icon_path']); 
            }
            header("Location: departments.php?msg=deleted");
            exit();
        }
        $stmt->close();
    }
}

// --- 2. AMBIL DATA ---
$sql = "SELECT * FROM departments ORDER BY category DESC, id ASC";
$result = $mysqli->query($sql);

$poliklinik = [];
$layanan = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        if ($row['category'] == 'Poliklinik') {
            $poliklinik[] = $row;
        } else {
            $layanan[] = $row;
        }
    }
}

// DEFINISIKAN PATH GAMBAR UNTUK ADMIN
// Jika posisi file ini ada di admin/departments.php, dan gambar ada di public/assets/...
// Maka kita butuh ../../public/ untuk mengaksesnya dari folder admin
$base_url_img = "../../public/"; 

require_once 'layout/header.php';
?>

<style>
    :root { 
        --jhc-red: #8a3033; 
        --med-green: #2d9d78;
    }
    .main-wrapper { 
        background: #fff; 
        border-radius: 15px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        padding: 25px; 
    }
    
    .table-container { 
        border-radius: 10px; 
        overflow: hidden; 
        border: 1px solid #eee; 
        margin-bottom: 30px; 
    }

    .bg-poli { background-color: var(--med-green) !important; color: #fff; }
    .bg-layanan { background: linear-gradient(90deg, #8a3033, #bd3030) !important; color: #fff; }

    .img-preview-box { 
        width: 45px; height: 45px; 
        border-radius: 8px; 
        background: #f8f9fa;
        display: flex; align-items: center; justify-content: center; 
        border: 1px solid #eee; 
        overflow: hidden;
    }
    .img-preview-box img { 
        width: 100%; height: 100%; 
        object-fit: contain; /* Contain agar logo tidak terpotong */
        padding: 2px;
    }

    .desc-column {
        font-size: 0.8rem;
        color: #666;
        max-width: 300px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .action-btn { 
        width: 32px; height: 32px; 
        border-radius: 8px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        transition: 0.2s; 
    }
    .btn-edit { background: #eef5ff; color: #007bff; }
    .btn-del { background: #fff1f1; color: #dc3545; }
    .btn-edit:hover { background: #007bff; color: #fff; }
    .btn-del:hover { background: #dc3545; color: #fff; }
</style>

<div class="container-fluid py-4">
    <div class="main-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Database Departemen</h4>
                <p class="text-muted small mb-0">Mengelola Poliklinik & Layanan (Ikon & Foto Utama).</p>
            </div>
            <a href="department_edit.php" class="btn btn-danger btn-sm px-3 shadow-sm" style="background: var(--jhc-red); border-radius: 8px;">
                <i class="fas fa-plus me-1"></i> Tambah Baru
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success py-2 px-3 border-0 shadow-sm mb-4 small">
                <i class="fas fa-check-circle me-1"></i> Data berhasil diperbarui!
            </div>
        <?php endif; ?>

        <h6 class="fw-bold mb-3" style="color: var(--med-green);"><i class="fas fa-stethoscope me-2"></i> Kategori: Poliklinik</h6>
        <div class="table-container shadow-sm mb-5">
            <table class="table align-middle mb-0">
                <thead class="bg-poli">
                    <tr>
                        <th class="text-center" width="50">ID</th>
                        <th width="80">Ikon</th>
                        <th>Nama Unit</th>
                        <th>Deskripsi</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($poliklinik as $row): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $row['id'] ?></td>
                        <td>
                            <?php 
                            // GABUNGKAN BASE PATH DENGAN DATA DATABASE
                            $full_path = $base_url_img . $row['icon_path']; 
                            if(!empty($row['icon_path']) && file_exists($full_path)): ?>
                                <div class="img-preview-box">
                                    <img src="<?= $full_path ?>?v=<?= time() ?>" alt="icon">
                                </div>
                            <?php else: ?>
                                <div class="img-preview-box text-muted" style="font-size: 8px; background: #eee;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                        <td><div class="desc-column"><?= htmlspecialchars($row['description']) ?></div></td>
                        <td class="text-center">
                            <a href="department_edit.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="departments.php?delete=<?= $row['id'] ?>" class="action-btn btn-del" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h6 class="fw-bold mb-3" style="color: var(--jhc-red);"><i class="fas fa-heartbeat me-2"></i> Kategori: Layanan Unggulan</h6>
        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0">
                <thead class="bg-layanan">
                    <tr>
                        <th class="text-center" width="50">ID</th>
                        <th width="120">Ikon</th>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($layanan as $row): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $row['id'] ?></td>
                        <td>
                            <div class="img-preview-box">
                                <?php 
                                $full_path_layanan = $base_url_img . $row['icon_path'];
                                if(!empty($row['icon_path']) && file_exists($full_path_layanan)): ?>
                                    <img src="<?= $full_path_layanan ?>?v=<?= time() ?>" alt="icon">
                                <?php else: ?>
                                    <i class="fas fa-image text-muted"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                        <td><div class="desc-column"><?= htmlspecialchars($row['description']) ?></div></td>
                        <td class="text-center">
                            <a href="department_edit.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="departments.php?delete=<?= $row['id'] ?>" class="action-btn btn-del" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$mysqli->close(); 
require_once 'layout/footer.php'; 
?>