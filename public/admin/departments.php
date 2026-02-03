<?php
require_once "../../config.php";
require_once 'layout/header.php';

if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: departments.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data termasuk category
$sql = "SELECT id, name, category, icon_path, display_order FROM departments ORDER BY category DESC, display_order ASC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Departments & Services</h3>
        <a href="department_edit.php" class="btn btn-primary">Add New Item</a>
    </div>
    <hr>
    
    <?php
    if (isset($_GET['deleted'])) {
        echo "<div class='alert alert-success'>Item deleted successfully.</div>";
    }
    if (isset($_GET['saved'])) {
        echo "<div class='alert alert-success'>Item saved successfully.</div>";
    }
    ?>

    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th style="width: 10%;">Order</th>
                <th>Category</th> <th>Name</th>
                <th style="width: 15%;">Icon</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td class='text-center'>" . htmlspecialchars($row['display_order']) . "</td>";
                    
                    // Menampilkan Kategori dengan Badge warna berbeda
                    $badge_color = ($row['category'] == 'Layanan') ? 'badge-info' : 'badge-success';
                    echo "<td><span class='badge $badge_color'>" . htmlspecialchars($row['category']) . "</span></td>";
                    
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    
                    echo "<td>";
                    if (!empty($row['icon_path'])) {
                        echo "<img src='../" . htmlspecialchars($row['icon_path']) . "' width='40' alt='Icon'>";
                    } else {
                        echo "<span class='text-muted'>No Icon</span>";
                    }
                    echo "</td>";
                    
                    echo "<td>";
                    echo "<a href='department_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a> ";
                    echo "<a href='departments.php?delete=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No items found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>