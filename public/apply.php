
<?php
require_once '../config.php';

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$job_title = '';

if ($job_id > 0) {
    $stmt = $mysqli->prepare("SELECT job_title FROM careers WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $job = $result->fetch_assoc();
        $job_title = $job['job_title'];
    } else {
        die('Job not found.');
    }
    $stmt->close();
}

$page_title = "Apply for " . htmlspecialchars($job_title);
require_once 'layout/public_header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="text-center">Apply for <?php echo htmlspecialchars($job_title); ?></h1>
                <hr>
                <div id="apply-message"></div>
                <form id="application-form" action="api/submit_application.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">No Hp</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="education" class="form-label">Pendidikan</label>
                        <input type="text" class="form-control" id="education" name="education" required>
                    </div>
                    <div class="mb-3">
                        <label for="cv" class="form-label">Upload CV (PDF, DOC, DOCX)</label>
                        <input class="form-control" type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</section>


<script>
document.getElementById('application-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('apply-message');

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.className = 'alert alert-success';
            messageDiv.textContent = data.message;
            form.reset(); // Clear the form
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'An error occurred while submitting the form.';
        console.error('Error:', error);
    });
});
</script>

<?php require_once 'layout/public_footer.php'; ?>

