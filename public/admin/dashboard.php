<?php require_once 'layout/header.php'; ?>

<div class="container">
    <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to your admin panel.</h1>
    
    <h3>Manage Content</h3>
    <div class="row">
        <div class="col-md-4">
            <a href="about_us.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">About Us Page</h5>
                        <p class="card-text">Edit the title, content, and image for the About Us section.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="departments.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Departments</h5>
                        <p class="card-text">Manage the list of medical departments/services.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="doctors.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Doctors</h5>
                        <p class="card-text">Manage the list of doctors and their departments.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="facilities.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Facilities</h5>
                        <p class="card-text">Manage the list of hospital facilities.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="careers.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Careers</h5>
                        <p class="card-text">Manage job openings.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="virtual_room.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Virtual Room</h5>
                        <p class="card-text">Edit the virtual room page content.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="contact_settings.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Contact Info</h5>
                        <p class="card-text">Edit footer address, phone, email, etc.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="appointments.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Appointments</h5>
                        <p class="card-text">View messages from visitors.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="news.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">News</h5>
                        <p class="card-text">Manage news articles and blog posts.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="news_settings.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">News Section Settings</h5>
                        <p class="card-text">Edit title and background image of the news section.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="logo_settings.php" class="card-link">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Logo Settings</h5>
                        <p class="card-text">Manage header and footer logos.</p>
                    </div>
                </div>
            </a>
        </div>
        <!-- More modules will be added here -->
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>