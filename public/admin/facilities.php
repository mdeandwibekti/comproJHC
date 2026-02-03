<?php
require_once "../../config.php";
require_once 'layout/header.php';

// PERBAIKAN 1: Nama tabel sudah benar 'facilities' (bukan facilities2)
$sql = "SELECT * FROM facilities ORDER BY display_order ASC";
$result = $mysqli->query($sql);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Facilities</h3>
        <a href="facility_edit.php" class="btn btn-primary">Add New Facility</a>
    </div>
    <hr>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Order</th>
                <th>Image</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['display_order']) . "</td>";
                    
                    // Cek apakah ada gambar, jika tidak pakai placeholder text
                    $imgSrc = !empty($row['image_path']) ? "../" . htmlspecialchars($row['image_path']) : "";
                    if($imgSrc) {
                        echo "<td><img src='$imgSrc' width='100' class='img-thumbnail'></td>";
                    } else {
                        echo "<td>No Image</td>";
                    }

                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    
                    // PERBAIKAN 3: Link tombol Edit mengarah ke 'facility_edit.php'
                    echo "<td><a href='facility_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No facilities found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require_once 'layout/footer.php'; ?>