<?php 
$page_title = "Jadwal Dokter | RS JHC Tasikmalaya";

// Sesuaikan path config jika perlu
if (file_exists("config.php")) {
    require_once "config.php";
} elseif (file_exists("../config.php")) {
    require_once "../config.php";
}

// Fetch Doctors with Department
$doctors_schedule = [];
$sql = "SELECT d.*, dep.name as dept_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.id 
        ORDER BY d.name ASC";
$result = $mysqli->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $doctors_schedule[] = $row;
    }
}

// Load Header (jika ada file layout header terpisah, gunakan itu. Jika tidak, copas struktur HTML head dari index.php)
// Di sini saya asumsikan pakai struktur standar
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <link href="public/assets/css/theme.css" rel="stylesheet" />
    <link href="public/vendors/fontawesome/all.min.js" rel="stylesheet" />
</head>
<body>
    
    <nav class="navbar navbar-light bg-light shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
            </a>
            <span class="navbar-text fw-bold">RS JHC Tasikmalaya</span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold text-primary">JADWAL PRAKTIK DOKTER</h2>
                <p class="text-muted">Informasi jadwal dokter spesialis RS JHC Tasikmalaya</p>
            </div>
        </div>

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="py-3 ps-4">Dokter</th>
                                <th class="py-3">Poliklinik/Spesialis</th>
                                <th class="py-3">Jadwal Praktik</th>
                                <th class="py-3 pe-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($doctors_schedule)): ?>
                                <?php foreach($doctors_schedule as $doc): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $img = !empty($doc['photo_path']) ? 'public/' . $doc['photo_path'] : 'public/assets/img/gallery/jane.png';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($img); ?>" class="rounded-circle me-3 border" width="50" height="50" style="object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($doc['name']); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary">
                                            <?php echo htmlspecialchars($doc['specialty']); ?>
                                        </span>
                                        <?php if($doc['dept_name']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($doc['dept_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($doc['schedule'])) {
                                            echo nl2br(htmlspecialchars($doc['schedule']));
                                        } else {
                                            echo "<span class='text-muted fst-italic'>Jadwal belum tersedia</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?php
                                            $wa_msg = urlencode("Halo, saya ingin mendaftar ke " . $doc['name'] . " untuk jadwal...");
                                        ?>
                                        <a href="https://api.whatsapp.com/send?phone=6287760615300&text=<?php echo $wa_msg; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill text-white">
                                            <i class="fab fa-whatsapp me-1"></i> Daftar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">Belum ada data dokter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="public/vendors/bootstrap/bootstrap.min.js"></script>
</body>
</html>