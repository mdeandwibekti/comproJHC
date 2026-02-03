<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    
    // Query DELETE ke tabel 'departments'
    $sql = "DELETE FROM departments WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Redirect kembali ke halaman departments.php setelah hapus
            header("location: departments.php?deleted=true");
            exit();
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch Data: Mengambil kolom yang sesuai dengan gambar database Anda
// (id, name, icon_path, display_order)
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
    // Menampilkan notifikasi sukses
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
                <th style="width: 10%;">Order</th>
                <th>Name</th>
                <th style="width: 15%;">Icon</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    
                    // Kolom Display Order
                    echo "<td class='text-center'>" . htmlspecialchars($row['display_order']) . "</td>";
                    
                    // Kolom Name
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    
                    // Kolom Icon (Sesuai dengan data 'assets/img/icons/...' di database)
                    echo "<td>";
                    if (!empty($row['icon_path'])) {
                        // Menggunakan '../' karena asumsi file ini ada di dalam folder admin/
                        echo "<img src='../" . htmlspecialchars($row['icon_path']) . "' width='40' alt='Icon'>";
                    } else {
                        echo "<span class='text-muted'>No Icon</span>";
                    }
                    echo "</td>";
                    
                    // Kolom Actions
                    echo "<td>";
                    echo "<a href='department_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a> ";
                    echo "<a href='departments.php?delete=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this department?');\">Delete</a>";
                    echo "</td>";
                    
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