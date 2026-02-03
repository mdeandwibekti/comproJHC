<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $sql = "DELETE FROM doctors2 WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            header("location: doctors2.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record.";
        }
        $stmt->close();
    }
}

// Fetch doctors with department name
$sql = "SELECT d.id, d.name, d.specialty, d.photo_path, d.is_featured, dep.name as department_name FROM doctors2 d LEFT JOIN departments2 dep ON d.department_id = dep.id ORDER BY d.name ASC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Doctors</h3>
        <a href="doctor_edit2.php" class="btn btn-primary">Add New Doctor</a>
    </div>
    <hr>
    <?php
    if (isset($_GET['deleted'])) {
        echo "<div class='alert alert-success'>Doctor deleted successfully.</div>";
    }
    if (isset($_GET['saved'])) {
        echo "<div class='alert alert-success'>Doctor saved successfully.</div>";
    }
    ?>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Specialty</th>
                <th>Department</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='../" . htmlspecialchars($row['photo_path'] ? $row['photo_path'] : 'assets/img/gallery/jane.png') . "' width='50' class='img-thumbnail'></td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['specialty']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['department_name']) . "</td>";
                    echo "<td>" . ($row['is_featured'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>";
                    echo "<a href="doctor_edit2.php?id=' . $row['id'] . '" class='btn btn-sm btn-info'>Edit</a> ";
                    echo '<a href="doctors2.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No doctors found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>
