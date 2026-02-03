<?php
require_once "../../config.php";
require_once 'layout/header.php';

$sql = "SELECT * FROM careers ORDER BY post_date DESC";
$result = $mysqli->query($sql);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Manage Careers</h3>
        <a href="career_edit.php" class="btn btn-primary">Add New Job Opening</a>
    </div>
    <hr>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Title</th>
                <th>Location</th>
                <th>Status</th>
                <th>Posted On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                    
                    $loc = isset($row['location']) ? $row['location'] : '-';
                    echo "<td>" . htmlspecialchars($loc) . "</td>";
                    
                    $stat = isset($row['status']) ? $row['status'] : 'Active';
                    echo "<td>" . htmlspecialchars($stat) . "</td>";
                    
                    echo "<td>" . date('Y-m-d', strtotime($row['post_date'])) . "</td>";
                    
                    echo "<td><a href='career_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>Edit</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No job openings found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require_once 'layout/footer.php'; ?>