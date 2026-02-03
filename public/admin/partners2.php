<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];

    // First, get the path to the logo to delete the file
    $sql_select = "SELECT logo_path FROM partners2 WHERE id = ?";
    if ($stmt_select = $mysqli->prepare($sql_select)) {
        $stmt_select->bind_param("i", $id_to_delete);
        $stmt_select->execute();
        $stmt_select->bind_result($logo_path);
        if ($stmt_select->fetch()) {
            // Delete the file if it exists
            if (!empty($logo_path) && file_exists("../" . $logo_path)) {
                unlink("../" . $logo_path);
            }
        }
        $stmt_select->close();
    }

    // Then, delete the record from the database
    $sql_delete = "DELETE FROM partners2 WHERE id = ?";
    if ($stmt_delete = $mysqli->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $id_to_delete);
        if ($stmt_delete->execute()) {
            header("location: partners2.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record.";
        }
        $stmt_delete->close();
    }
}

// Fetch all partners
$sql = "SELECT id, name, logo_path, url FROM partners2 ORDER BY name ASC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Partners</h3>
        <a href="partner_edit2.php" class="btn btn-primary">Add New Partner</a>
    </div>
    <hr>
    <?php
    if (isset($_GET['deleted'])) {
        echo "<div class='alert alert-success'>Partner deleted successfully.</div>";
    }
    if (isset($_GET['saved'])) {
        echo "<div class='alert alert-success'>Partner saved successfully.</div>";
    }
    ?>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Logo</th>
                <th>Name</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='../" . htmlspecialchars($row['logo_path']) . "' width='100' class='img-thumbnail'></td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['url']) . "</td>";
                    echo "<td>";
                    echo "<a href='partner_edit2.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a> ";
                    echo '<a href="partners2.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this partner?\');">Delete</a>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No partners found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'layout/footer.php';
?>
