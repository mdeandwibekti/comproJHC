<?php
require_once '../../config.php';
require_once 'layout/header.php';

$sql = "SELECT a.*, c.job_title FROM applicants a JOIN careers c ON a.job_id = c.id ORDER BY a.applied_at DESC";
$result = $mysqli->query($sql);
?>

<div class="container-fluid">
    <h3>Job Applicants</h3>
    <hr>
    <div id="status-update-message"></div>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Name</th>
                <th>Job Title</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Education</th>
                <th>CV</th>
                <th>Status</th>
                <th>Applied At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['education']) . "</td>";
                    echo "<td><a href='../" . htmlspecialchars($row['cv_path']) . "' target='_blank' class='btn btn-sm btn-outline-primary'>Download CV</a></td>";
                    
                    $status_color = 'bg-warning text-dark';
                    if ($row['status'] == 'Diterima') {
                        $status_color = 'bg-success';
                    } elseif ($row['status'] == 'Ditolak') {
                        $status_color = 'bg-danger';
                    }
                    echo "<td><span class='badge " . $status_color . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                    
                    echo "<td>" . date('Y-m-d H:i', strtotime($row['applied_at'])) . "</td>";
                    
                    echo "<td>";
                    if ($row['status'] == 'Pending') {
                        echo "<button class='btn btn-sm btn-success update-status-btn mx-1' data-id='" . $row['id'] . "' data-status='Diterima'>Terima</button>";
                        echo "<button class='btn btn-sm btn-danger update-status-btn mx-1' data-id='" . $row['id'] . "' data-status='Ditolak'>Tolak</button>";
                    } else {
                        echo "-";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10' class='text-center'>No applicants found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.update-status-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const applicantId = this.dataset.id;
            const newStatus = this.dataset.status;
            
            if (confirm(`Are you sure you want to change the status to "${newStatus}"?`)) {
                const formData = new FormData();
                formData.append('applicant_id', applicantId);
                formData.append('status', newStatus);

                fetch('../api/update_applicant_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById('status-update-message');
                    if (data.success) {
                        messageDiv.className = 'alert alert-success';
                        messageDiv.textContent = data.message;
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        messageDiv.className = 'alert alert-danger';
                        messageDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const messageDiv = document.getElementById('status-update-message');
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'An error occurred while connecting to server.';
                });
            }
        });
    });
});
</script>

<?php require_once 'layout/footer.php'; ?>