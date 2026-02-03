<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    // FIX: Tabel 'departments'
    $sql = "DELETE FROM departments WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // FIX: Redirect ke departments.php
            header("location: departments.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record.";
        }
        $stmt->close();
    }
}

// FIX: Tabel 'departments'
$sql = "SELECT id, name, icon_path, display_order FROM departments ORDER BY display_order ASC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Departments</h3>
        <a href="department_edit.php" class="btn btn-primary">Add New Department</a>
    </div>
    <hr>
    <?php
    if (isset($_GET['deleted'])) {
        echo "<div class='alert alert-success'>Department deleted successfully.</div>";
    }
    if (isset($_GET['saved'])) {
        echo "<div class='alert alert-success'>Department saved successfully.</div>";
    }
    ?>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Icon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['display_order']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    
                    // Cek gambar icon
                    $iconSrc = !empty($row['icon_path']) ? "../" . htmlspecialchars($row['icon_path']) : "";
                    if($iconSrc) {
                        echo "<td><img src='$iconSrc' width='40'></td>";
                    } else {
                        echo "<td>No Icon</td>";
                    }
                    
                    echo '<td>';
                    // FIX: Link Edit ke department_edit.php
                    echo '<a href="department_edit.php?id=' . $row['id'] . '" class="btn btn-sm btn-info">Edit</a> ';
                    // FIX: Link Delete ke departments.php
                    echo '<a href="departments.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                    echo '</td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No departments found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>