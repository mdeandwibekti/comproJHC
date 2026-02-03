<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM news2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: news2.php?deleted=true");
            exit();
        }

        else {
            echo "Error deleting record.";
        }
        $stmt->close();
    }
}

$sql = "SELECT * FROM news2 ORDER BY post_date DESC";
$result = $mysqli->query($sql);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage News</h3>
        <a href="news_edit2.php" class="btn btn-primary">Add New Article</a>
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
        <thead class="thead-dark"><tr><th>Image</th><th>Title</th><th>Category</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='../" . htmlspecialchars($row['image_path']) . "' width='100' class='img-thumbnail'></td>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>" . date('Y-m-d', strtotime($row['post_date'])) . "</td>";
                    echo "<td>";
                    echo "<a href='news_edit2.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a> ";
                    echo '<a href="news2.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No news articles found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require_once 'layout/footer.php'; ?>
