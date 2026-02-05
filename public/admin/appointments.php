<?php
require_once "../../config.php";

// --- PERBAIKAN: Logika Update ditaruh DI ATAS header.php ---
// Handle status update
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = (int)$_GET['id']; // Casting ke int untuk keamanan sederhana
    $action = $_GET['action'];
    
    // Tentukan status
    $status = ($action == 'read') ? 'read' : 'contacted';
    
    // Gunakan Prepared Statement agar lebih aman
    if ($stmt = $mysqli->prepare("UPDATE appointments SET status = ? WHERE id = ?")) {
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect bisa berjalan karena belum ada HTML yang dicetak
    header("location: appointments.php");
    exit();
}

// --- Baru panggil header setelah logika selesai ---
require_once 'layout/header.php';

// Fetch data
$sql = "SELECT * FROM appointments ORDER BY submission_date DESC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h3>View Appointments</h3>
    </div>
    <hr>
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0 align-middle">
                    <thead class="thead-dark text-white" style="background-color: #343a40;">
                        <tr>
                            <th>Received</th>
                            <th>Name</th>
                            <th>Email / Phone</th>
                            <th style="width: 30%;">Message</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Style bold untuk pesan baru
                                $fw = $row['status'] == 'new' ? 'fw-bold' : 'fw-normal';
                                $bg_class = $row['status'] == 'new' ? 'table-warning' : '';
                                
                                echo "<tr class='{$bg_class}'>";
                                echo "<td class='{$fw}'>" . date('d M Y H:i', strtotime($row['submission_date'])) . "</td>";
                                echo "<td class='{$fw}'>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "<br><small class='text-muted'>" . htmlspecialchars($row['phone']) . "</small></td>";
                                echo "<td class='small'>" . nl2br(htmlspecialchars($row['message'])) . "</td>";
                                
                                // Badge Status
                                $badge_color = 'bg-secondary';
                                if($row['status'] == 'new') $badge_color = 'bg-danger';
                                if($row['status'] == 'read') $badge_color = 'bg-info text-dark';
                                if($row['status'] == 'contacted') $badge_color = 'bg-success';
                                
                                echo "<td><span class='badge {$badge_color}'>" . strtoupper(htmlspecialchars($row['status'])) . "</span></td>";
                                
                                echo "<td>";
                                // Tombol Aksi
                                if($row['status'] == 'new') {
                                    echo "<a href='appointments.php?action=read&id={$row['id']}' class='btn btn-sm btn-outline-primary me-1' title='Mark as Read'><i class='fas fa-envelope-open'></i> Read</a> ";
                                }
                                
                                if($row['status'] != 'contacted') {
                                    echo "<a href='appointments.php?action=contacted&id={$row['id']}' class='btn btn-sm btn-outline-success' title='Mark as Contacted'><i class='fas fa-check'></i> Done</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'><i class='fas fa-inbox fa-3x mb-3'></i><br>No appointments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>