<?php
require_once "layout/public_header.php"; // Corrected path

$article = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM news WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $article = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// If article not found, redirect or show error
if (!$article) {
    header("location: index.php"); // Redirect to homepage or a 404 page
    exit();
}

$page_title = htmlspecialchars($article['title']); // $article is now defined here

?>

      <section class="py-8">
        <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/dot-bg.png);background-position:top left;background-size:auto;">
        </div>
        <!--/.bg-holder-->
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                    <p class="text-muted">Category: <?php echo htmlspecialchars($article['category']); ?> | Posted on: <?php echo date('M d, Y', strtotime($article['post_date'])); ?></p>
                    <?php if ($article['image_path']): ?>
                        <div class="mb-4" style="width: 100%; height: 400px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($article['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        </div>
                    <?php endif; ?>
                    <p><?php echo nl2br($article['content']); ?></p>
                    <a href="<?php echo BASE_URL; ?>#news" class="btn btn-primary mt-4">Back to News</a>
                </div>
            </div>
        </div>
      </section>
    </main> <!-- Add this closing main tag -->

<?php 
$mysqli->close();
require_once "layout/public_footer.php"; // Corrected path
?>