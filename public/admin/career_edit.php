<?php
require_once "../../config.php";
require_once 'layout/header.php';

$job_title = $description = $location = $end_date = "";
$status = 'open';
$page_title = "Add New Job Opening";
$id = null;
$job_title_err = $description_err = $location_err = $end_date_err = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $page_title = "Edit Job Opening";
    
    $sql = "SELECT job_title, description, location, status, end_date FROM careers WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $job_title = $row['job_title'];
                $description = $row['description'];
                $location = $row['location'];
                $status = $row['status'];
                $end_date = $row['end_date'];
            } else {
                header("location: careers.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);

    if (empty(trim($_POST["job_title"]))) {
        $job_title_err = "Please enter a job title.";
    } else {
        $job_title = trim($_POST["job_title"]);
    }

    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }

    if (empty(trim($_POST["location"]))) {
        $location_err = "Please enter a location.";
    } else {
        $location = trim($_POST["location"]);
    }

    $status = trim($_POST["status"]);
    $end_date = trim($_POST["end_date"]);

    if (!empty($end_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
        $end_date_err = "Please enter a valid date format (YYYY-MM-DD).";
    } elseif (empty($end_date)) {
        $end_date = NULL; 
    }

    if (empty($job_title_err) && empty($description_err) && empty($location_err) && empty($end_date_err)) {
        
        if (empty($id)) {
            $sql = "INSERT INTO careers (job_title, description, location, status, end_date) VALUES (?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE careers SET job_title = ?, description = ?, location = ?, status = ?, end_date = ? WHERE id = ?";
        }

        if ($stmt = $mysqli->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sssss", $job_title, $description, $location, $status, $end_date);
            } else {
                $stmt->bind_param("sssssi", $job_title, $description, $location, $status, $end_date, $id);
            }
            
            if ($stmt->execute()) {
                header("location: careers.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later. Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Oops! Something went wrong. Please try again later. Failed to prepare statement: " . $mysqli->error;
        }
    }
}
?>

<div class="container-fluid">
    <h3><?php echo $page_title; ?></h3><hr>
    <form action="career_edit.php<?php echo $id ? '?id='.$id : '' ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="form-group">
            <label>Job Title</label>
            <input type="text" name="job_title" class="form-control <?php echo (!empty($job_title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($job_title); ?>">
            <span class="invalid-feedback"><?php echo $job_title_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($location); ?>">
            <span class="invalid-feedback"><?php echo $location_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="open" <?php echo $status=='open'?'selected':''; ?>>Open</option>
                <option value="closed" <?php echo $status=='closed'?'selected':''; ?>>Closed</option>
            </select>
        </div>
        <div class="form-group mt-3">
            <label>Application End Date (Optional)</label>
            <input type="date" name="end_date" class="form-control <?php echo (!empty($end_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($end_date); ?>">
            <span class="invalid-feedback"><?php echo $end_date_err; ?></span>
        </div>
        <div class="form-group mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="10"><?php echo htmlspecialchars($description); ?></textarea>
            <span class="invalid-feedback"><?php echo $description_err; ?></span>
        </div>
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Save">
            <a href="careers.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php require_once 'layout/footer.php'; ?>