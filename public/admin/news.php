<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    // Tabel 'news'
    $sql = "DELETE FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Redirect ke news.php
            header("location: news.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch data dari tabel 'news'
$sql = "SELECT * FROM news ORDER BY post_date DESC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage News</h3>
        <a href="news_edit.php" class="btn btn-primary">Add New Article</a>
    </div>
    <hr>
    <?php
    if (isset($_GET['deleted'])) {
        echo "<div class='alert alert-success'>Article deleted successfully.</div>";
    }
    if (isset($_GET['saved'])) {
        echo "<div class='alert alert-success'>Article saved successfully.</div>";
    }
    ?>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    // Menampilkan gambar
                    $img = !empty($row['image_path']) ? $row['image_path'] : 'assets/img/news/default.jpg';
                    echo "<td><img src='../" . htmlspecialchars($img) . "' width='100' class='img-thumbnail'></td>";
                    
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>" . date('Y-m-d', strtotime($row['post_date'])) . "</td>";
                    echo "<td>";
                    // Link Edit ke news_edit.php
                    echo "<a href='news_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a> ";
                    // Link Delete ke news.php
                    echo '<a href="news.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No news articles found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require_once 'layout/footer.php'; ?>