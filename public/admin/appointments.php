<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Handle status update
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = $_GET['id'];
    $status = $_GET['action'] == 'read' ? 'read' : 'contacted';
    $mysqli->query("UPDATE appointments SET status = '{$status}' WHERE id = {$id}");
    header("location: appointments.php");
    exit();
}

$sql = "SELECT * FROM appointments ORDER BY submission_date DESC";
$result = $mysqli->query($sql);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center"><h3>View Appointments</h3></div><hr>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark"><tr><th>Received</th><th>Name</th><th>Email / Phone</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $style = $row['status'] == 'new' ? 'font-weight: bold;' : '';
                    echo "<tr style='{$style}'>";
                    echo "<td>" . date('Y-m-d H:i', strtotime($row['submission_date'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "<br>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($row['message'])) . "</td>";
                    echo "<td><span class='badge bg-info text-dark'>" . htmlspecialchars($row['status']) . "</span></td>";
                    echo "<td>";
                    if($row['status'] == 'new') echo "<a href='appointments.php?action=read&id={$row['id']}' class='btn btn-sm btn-secondary'>Mark as Read</a> ";
                    if($row['status'] != 'contacted') echo "<a href='appointments.php?action=contacted&id={$row['id']}' class='btn btn-sm btn-success'>Mark as Contacted</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No appointments found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require_once 'layout/footer.php'; ?>