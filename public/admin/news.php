<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            echo "<script>window.location.href='news.php?deleted=true';</script>";
            exit();
        } else {
            $error_msg = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch Data
$sql = "SELECT * FROM news ORDER BY post_date DESC";
$result = $mysqli->query($sql);
?>

<style>
    :root { --primary-red: #D32F2F; --light-bg: #f8f9fa; }
    
    .page-header {
        background: white; padding: 1.5rem; border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }
    
    .main-card {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa; border-bottom: 2px solid #eee;
        color: #444; font-weight: 600; text-transform: uppercase; font-size: 0.85rem;
        vertical-align: middle;
    }
    
    .table tbody tr:hover { background-color: #fff5f5; transition: 0.2s; }
    
    .btn-add {
        background-color: var(--primary-red); color: white; border-radius: 50px;
        padding: 0.6rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.3s;
        box-shadow: 0 4px 6px rgba(211, 47, 47, 0.2);
    }
    .btn-add:hover { background-color: #b71c1c; color: white; transform: translateY(-2px); }

    .news-thumb {
        width: 80px; height: 50px; object-fit: cover;
        border-radius: 6px; border: 1px solid #eee;
    }

    .badge-category {
        background-color: #e3f2fd; color: #1565c0; 
        border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 50px; font-weight: 500; font-size: 0.8rem;
    }

    /* Tombol Aksi */
    .btn-action {
        padding: 6px 14px; font-size: 0.85rem; font-weight: 600; border-radius: 6px;
        text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;
    }
    .btn-edit-action { background-color: #e3f2fd; color: #1976d2; }
    .btn-edit-action:hover { background-color: #1976d2; color: white; box-shadow: 0 3px 8px rgba(25, 118, 210, 0.2); }
    
    .btn-del-action { background-color: #ffebee; color: #c62828; }
    .btn-del-action:hover { background-color: #c62828; color: white; box-shadow: 0 3px 8px rgba(198, 40, 40, 0.2); }
    
    .btn-action i { margin-right: 6px; font-size: 0.8rem; }
</style>

<div class="container-fluid py-4">
    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-newspaper me-2 text-danger"></i> Manage News</h3>
            <p class="text-muted mb-0 small">Publish articles, health tips, and hospital announcements.</p>
        </div>
        <a href="news_edit.php" class="btn-add">
            <i class="fas fa-plus me-2"></i> Add New Article
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> Article deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Image</th>
                            <th style="width: 35%;">Title</th>
                            <th style="width: 15%;">Category</th>
                            <th style="width: 15%;">Date</th>
                            <th class="text-center" style="width: 25%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php 
                                        $img = !empty($row['image_path']) ? "../" . htmlspecialchars($row['image_path']) : "";
                                        if($img): ?>
                                            <img src="<?php echo $img; ?>" class="news-thumb" alt="News">
                                        <?php else: ?>
                                            <div class="news-thumb d-flex align-items-center justify-content-center bg-light text-muted mx-auto">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </td>
                                    
                                    <td>
                                        <span class="badge-category">
                                            <?php echo htmlspecialchars($row['category']); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('d M Y', strtotime($row['post_date'])); ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="news_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit-action">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="news.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-del-action" onclick="return confirm('Are you sure you want to delete this article?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i><br>
                                    No news articles found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>