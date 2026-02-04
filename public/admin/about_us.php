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

// Icon mapping for tabs
$section_icons = [
    'visi-misi' => 'fa-bullseye',
    'sejarah' => 'fa-history',
    'salam-direktur' => 'fa-user-tie',
    'budaya-kerja' => 'fa-hand-holding-heart'
];

$errors = [];
$success_message = "";

// --- PHP LOGIC (TIDAK BERUBAH) ---
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
                $errors[$section_key]['general'] = "Something went wrong. Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
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

<style>
    :root {
        --primary-red: #D32F2F;
        --light-bg: #f8f9fa;
    }

    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 5px solid var(--primary-red);
        margin-bottom: 2rem;
    }

    .main-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* Tab Styling */
    .card-header-tabs {
        background-color: #fff;
        border-bottom: 1px solid #eee;
        padding: 0 1rem;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary-red);
        background: rgba(211, 47, 47, 0.05);
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-red);
        border-bottom: 3px solid var(--primary-red);
        background: transparent;
        font-weight: 600;
    }

    /* Form Styling */
    .form-control:focus {
        border-color: var(--primary-red);
        box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.15);
    }

    .form-label {
        font-weight: 600;
        color: #444;
        margin-bottom: 0.5rem;
    }

    .img-preview-box {
        padding: 10px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        display: inline-block;
        background: #f9f9f9;
        text-align: center;
    }

    .img-preview-box img {
        max-height: 150px;
        border-radius: 5px;
    }

    /* Buttons */
    .btn-save {
        background-color: var(--primary-red);
        border-color: var(--primary-red);
        color: white;
        padding: 0.6rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-save:hover {
        background-color: #b71c1c;
        border-color: #b71c1c;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
    }

    .btn-cancel {
        background-color: #f1f3f5;
        color: #495057;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 50px;
        font-weight: 600;
    }
    
    .btn-cancel:hover {
        background-color: #e9ecef;
    }
</style>

<div class="container-fluid py-4">
    
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <h3 class="mb-1 text-dark fw-bold"><i class="fas fa-hospital-user me-2 text-danger"></i> Manage About Us</h3>
            <p class="text-muted mb-0 small">Edit company profile, history, and vision information.</p>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-header card-header-tabs pt-3">
            <ul class="nav nav-tabs card-header-tabs" id="aboutUsTabs" role="tablist">
                <?php $first = true; foreach ($sections as $key => $name): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php if($first) echo 'active'; ?>" id="<?php echo $key; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $key; ?>" type="button" role="tab" aria-controls="<?php echo $key; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                        <i class="fas <?php echo $section_icons[$key] ?? 'fa-file-alt'; ?> me-2"></i> <?php echo $name; ?>
                    </button>
                </li>
                <?php $first = false; endforeach; ?>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="aboutUsTabsContent">
                <?php $first = true; foreach ($sections as $key => $name): 
                    $section_data = $all_sections_data[$key] ?? ['title' => '', 'content' => '', 'image_path' => ''];
                    $section_errors = $errors[$key] ?? [];
                ?>
                <div class="tab-pane fade <?php if($first) echo 'show active'; ?>" id="<?php echo $key; ?>" role="tabpanel" aria-labelledby="<?php echo $key; ?>-tab">
                    
                    <?php if (!empty($section_errors['general'])): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $section_errors['general']; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=<?php echo $key; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="section_key" value="<?php echo $key; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="title" class="form-control form-control-lg <?php echo (!empty($section_errors['title'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($section_data['title']); ?>" placeholder="Enter title e.g., <?php echo $name; ?>">
                                    <div class="invalid-feedback"><?php echo $section_errors['title'] ?? ''; ?></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Content / Description</label>
                                    <textarea name="content" class="form-control <?php echo (!empty($section_errors['content'])) ? 'is-invalid' : ''; ?>" rows="8" placeholder="Write the content here..."><?php echo htmlspecialchars($section_data['content']); ?></textarea>
                                    <div class="invalid-feedback"><?php echo $section_errors['content'] ?? ''; ?></div>
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> You can use basic HTML tags for formatting if needed.</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Section Image</label>
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="img-preview-box mb-3">
                                                <?php if (!empty($section_data['image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($section_data['image_path']); ?>" class="img-fluid" alt="Preview">
                                                <?php else: ?>
                                                    <div class="py-4 text-muted">
                                                        <i class="fas fa-image fa-3x mb-2"></i><br>
                                                        No image uploaded
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($section_data['image_path']); ?>">
                                            
                                            <label for="file-<?php echo $key; ?>" class="form-label small text-start w-100 fw-bold text-muted">Upload New Image</label>
                                            <input type="file" id="file-<?php echo $key; ?>" name="image" class="form-control <?php echo (!empty($section_errors['image'])) ? 'is-invalid' : ''; ?>">
                                            <div class="invalid-feedback text-start"><?php echo $section_errors['image'] ?? ''; ?></div>
                                            <div class="form-text text-start small mt-2">Allowed: JPG, JPEG, PNG. Max: 5MB.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Persist active tab on reload after form submission
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        let tabEl = document.querySelector('#' + activeTab + '-tab');
        if(tabEl) {
            let tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }
    
    // Clean URL
    if (window.location.search.includes('success=1') || window.location.search.includes('tab=')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

<?php 
if(isset($mysqli)) $mysqli->close();
require_once 'layout/footer.php'; 
?>