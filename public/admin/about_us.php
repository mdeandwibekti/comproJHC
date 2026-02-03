<?php
require_once "../../config.php";
require_once 'layout/header.php';

// Define the sections
$sections = [
    'visi-misi' => 'Visi-Misi',
    'sejarah' => 'Sejarah',
    'salam-direktur' => 'Salam Direktur',
    'budaya-kerja' => 'Budaya Kerja'
];
$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_key = trim($_POST["section_key"]);

    if (empty(trim($_POST["title"]))) {
        $errors[$section_key]['title'] = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }

    if (empty(trim($_POST["content"]))) {
        $errors[$section_key]['content'] = "Please enter content.";
    } else {
        $content = trim($_POST["content"]);
    }

    $image_path = trim($_POST["current_image"]);
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $errors[$section_key]['image'] = "Error: Please select a valid file format.";
        }

        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $errors[$section_key]['image'] = "Error: File size is larger than the allowed limit.";
        }

        if (empty($errors[$section_key]['image'])) {
            $new_filename = uniqid() . "." . $ext;
            $upload_path = "../assets/img/gallery/" . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                $image_path = "assets/img/gallery/" . $new_filename;
            } else {
                $errors[$section_key]['image'] = "Error: There was a problem uploading your file. Please try again.";
            }
        }
    }

    if (empty($errors[$section_key])) {
        $sql = "INSERT INTO about_us_sections (section_key, title, content, image_path) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), image_path = VALUES(image_path)";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssss", $section_key, $title, $content, $image_path);

            if ($stmt->execute()) {
                $success_message = "Content for '" . htmlspecialchars($sections[$section_key]) . "' updated successfully.";
            } else {
                // Add more detailed error reporting
                $errors[$section_key]['general'] = "Something went wrong. Please try again later. Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Add more detailed error reporting for prepare failure
            $errors[$section_key]['general'] = "Failed to prepare statement. Database error: " . $mysqli->error;
        }
    }
}

// Fetch all section data
$all_sections_data = [];
$sql_select = "SELECT section_key, title, content, image_path FROM about_us_sections";
$result = $mysqli->query($sql_select);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $all_sections_data[$row['section_key']] = $row;
    }
}

?>

<div class="container-fluid">
    <h3>Edit About Us Page</h3>
    <hr>
    <?php
    if (!empty($success_message)) {
        echo "<div class='alert alert-success'>{$success_message}</div>";
    }
    ?>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="aboutUsTabs" role="tablist">
        <?php $first = true; foreach ($sections as $key => $name): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php if($first) echo 'active'; ?>" id="<?php echo $key; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $key; ?>" type="button" role="tab" aria-controls="<?php echo $key; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>"><?php echo $name; ?></button>
        </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="aboutUsTabsContent">
        <?php $first = true; foreach ($sections as $key => $name): 
            $section_data = $all_sections_data[$key] ?? ['title' => '', 'content' => '', 'image_path' => ''];
            $section_errors = $errors[$key] ?? [];
        ?>
        <div class="tab-pane fade <?php if($first) echo 'show active'; ?> p-3" id="<?php echo $key; ?>" role="tabpanel" aria-labelledby="<?php echo $key; ?>-tab">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($section_errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $section_errors['general']; ?></div>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=<?php echo $key; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="section_key" value="<?php echo $key; ?>">
                        
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control <?php echo (!empty($section_errors['title'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($section_data['title']); ?>">
                            <span class="invalid-feedback"><?php echo $section_errors['title'] ?? ''; ?></span>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label>Content</label>
                            <textarea name="content" class="form-control <?php echo (!empty($section_errors['content'])) ? 'is-invalid' : ''; ?>" rows="5"><?php echo htmlspecialchars($section_data['content']); ?></textarea>
                            <span class="invalid-feedback"><?php echo $section_errors['content'] ?? ''; ?></span>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label>Current Image</label><br>
                            <?php if (!empty($section_data['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($section_data['image_path']); ?>" width="200" class="img-thumbnail mb-2">
                            <?php else: ?>
                                <p>No image uploaded.</p>
                            <?php endif; ?>
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($section_data['image_path']); ?>">
                        </div>

                        <div class="form-group mt-3">
                            <label>Upload New Image (optional)</label>
                            <input type="file" name="image" class="form-control-file <?php echo (!empty($section_errors['image'])) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $section_errors['image'] ?? ''; ?></span>
                        </div>

                        <div class="form-group mt-4">
                            <input type="submit" class="btn btn-primary" value="Save Changes">
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php $first = false; endforeach; ?>
    </div>
</div>

<script>
// Persist active tab on reload after form submission
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    const successMessage = urlParams.get('success');

    if (activeTab) {
        let tabEl = document.querySelector('#' + activeTab + '-tab');
        if(tabEl) {
            let tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }
    
    // If there is a success message, redirect to clean the URL
    if (window.location.search.includes('success=1') || window.location.search.includes('tab=')) {
        // Use replaceState to avoid adding to history
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

<?php 
$mysqli->close();
require_once 'layout/footer.php'; 
?>
