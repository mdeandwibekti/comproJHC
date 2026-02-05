<?php
require_once "../../config.php";

// --- LOGIKA HAPUS (DELETE) ---
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete']; 

    // 1. Hapus gambar fisik
    $sql_select = "SELECT logo_path FROM partners WHERE id = ?";
    if ($stmt_select = $mysqli->prepare($sql_select)) {
        $stmt_select->bind_param("i", $id_to_delete);
        $stmt_select->execute();
        $stmt_select->bind_result($logo_path);
        
        if ($stmt_select->fetch()) {
            if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                unlink("../" . $logo_path);
            }
        }
        $stmt_select->close();
    }

    // 2. Hapus data database
    $sql_delete = "DELETE FROM partners WHERE id = ?";
    if ($stmt_delete = $mysqli->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $id_to_delete);
        
        if ($stmt_delete->execute()) {
            header("location: partners.php?deleted=true");
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    }
}

// --- PANGGIL HEADER ---
require_once 'layout/header.php';

// Fetch Data
$sql = "SELECT id, name, logo_path, url FROM partners ORDER BY name ASC";
$result = $mysqli->query($sql);
?>

<style>
    :root { --primary-red: #D32F2F; --light-bg: #f8f9fa; }
    
    .page-header {
        background: white; padding: 1.2rem; border-radius: 10px; /* Padding diperkecil */
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }
    
    .main-card {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden;
    }

    /* Ukuran Font Header Tabel */
    .table thead th {
        background-color: #343a40; border-bottom: 2px solid #eee;
        color: #fff; font-weight: 600; text-transform: uppercase; 
        font-size: 0.8rem; /* Diperkecil dari 0.85rem */
        vertical-align: middle;
    }
    
    /* Ukuran Font Isi Tabel (Global) */
    .table tbody td {
        font-size: 0.9rem; /* Ukuran font diperkecil */
        vertical-align: middle;
    }
    
    .table tbody tr:hover { background-color: #fff5f5; transition: 0.2s; }
    
    .btn-add {
        background-color: var(--primary-red); color: white; border-radius: 50px;
        padding: 0.5rem 1.2rem; font-weight: 600; text-decoration: none; transition: 0.3s;
        font-size: 0.9rem; /* Tombol diperkecil */
        box-shadow: 0 4px 6px rgba(211, 47, 47, 0.2);
    }
    .btn-add:hover { background-color: #b71c1c; color: white; transform: translateY(-2px); }

    .partner-logo-thumb {
        height: 40px; width: auto; max-width: 100px; /* Gambar thumb diperkecil */
        object-fit: contain; padding: 3px;
        background: #fff; border: 1px solid #eee; border-radius: 6px;
    }

    .btn-action {
        padding: 4px 8px; font-size: 0.8rem; /* Tombol aksi diperkecil */
        font-weight: 600; border-radius: 6px;
        text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;
    }
    .btn-edit-action { background-color: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
    .btn-edit-action:hover { background-color: #1976d2; color: white; }
    
    .btn-del-action { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .btn-del-action:hover { background-color: #c62828; color: white; }
    
    .link-url { color: #555; text-decoration: none; transition: 0.2s; font-size: 0.85rem; }
    .link-url:hover { color: var(--primary-red); text-decoration: underline; }
</style>

<div class="container-fluid py-4">
    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="fas fa-handshake me-2 text-danger"></i> Manage Partners</h4>
            <p class="text-muted mb-0 small">Manage insurance companies and corporate partners.</p>
        </div>
        <a href="partner_edit.php" class="btn-add">
            <i class="fas fa-plus me-2"></i> Add New
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small" role="alert">
            <i class="fas fa-check-circle me-2"></i> Partner deleted successfully.
            <button type="button" class="btn-close small" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small" role="alert">
            <i class="fas fa-check-circle me-2"></i> Partner saved successfully.
            <button type="button" class="btn-close small" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close small" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Logo</th>
                            <th style="width: 35%;">Partner Name</th>
                            <th style="width: 35%;">Website URL</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center bg-light">
                                        <?php 
                                        $imgSrc = !empty($row['logo_path']) ? "../" . htmlspecialchars($row['logo_path']) : "";
                                        if($imgSrc && file_exists("../" . $row['logo_path'])): ?>
                                            <img src="<?php echo $imgSrc; ?>" class="partner-logo-thumb" alt="Logo">
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                    
                                    <td>
                                        <?php if(!empty($row['url'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="link-url">
                                                <i class="fas fa-external-link-alt me-1 small"></i> <?php echo htmlspecialchars($row['url']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="partner_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit-action" title="Edit">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="partners.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Are you sure you want to delete this partner?');" title="Delete">
                                                <i class="fas fa-trash-alt"></i> Del
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted small">
                                    <i class="fas fa-handshake fa-2x mb-2 opacity-25"></i><br>
                                    No partners found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>