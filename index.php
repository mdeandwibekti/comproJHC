<?php 
$page_title = "Rs JHC Tasikmalaya | Home";
require_once "public/layout/public_header.php"; 
echo "<!-- DEBUG: PHP Execution Reached Here -->";

$banners_data = [];
$banner_sql = "SELECT image_path, title, description FROM banners2 ORDER BY display_order ASC";
$banner_result = $mysqli->query($banner_sql);
if ($banner_result && $banner_result->num_rows > 0) {
    while($row = $banner_result->fetch_assoc()) {
        $banners_data[] = $row;
    }
}

$mcu_packages_data = [];
$mcu_sql = "SELECT image_path, title, description, price FROM mcu_packages2 ORDER BY display_order ASC";
$mcu_result = $mysqli->query($mcu_sql);
if ($mcu_result && $mcu_result->num_rows > 0) {
    while($row = $mcu_result->fetch_assoc()) {
        $mcu_packages_data[] = $row;
    }
}

$partners_data = [];
$partners_sql = "SELECT name, logo_path, url FROM partners2 ORDER BY name ASC";
$partners_result = $mysqli->query($partners_sql);
if ($partners_result && $partners_result->num_rows > 0) {
    while($row = $partners_result->fetch_assoc()) {
        $partners_data[] = $row;
    }
}
?>
      <section class="py-xxl-10 pb-0" id="home">
        <div class="bg-holder bg-size" style="background-image:url(public/<?php echo htmlspecialchars($settings['hero_background_path'] ?? 'assets/img/gallery/hero-bg.png'); ?>);background-position:top center;background-size:cover;">
        </div>
        <!--/.bg-holder-->
        <div class="container">
          <div class="row min-vh-xl-100 min-vh-xxl-25 align-items-center">
            <div class="col-md-6 text-md-start text-center py-6">
              <h1 class="fw-light font-base fs-6 fs-xxl-7" id="banner-title"></h1>
              <p class="fs-1 mb-5" id="banner-description"></p>
              <a class="btn btn-lg btn-primary rounded-pill" href="<?php echo BASE_URL; ?>#appointment" role="button">Make an Appointment</a>
            </div>
            <div class="col-md-6 order-0 order-md-1 text-end">
              <div id="banner-slider" style="width: 100%; height: 400px; overflow: hidden; position: relative;">
                <?php if (!empty($banners_data)): ?>
                  <?php foreach ($banners_data as $index => $banner): ?>
                    <img class="banner-image" src="public/<?php echo htmlspecialchars($banner['image_path']); ?>" data-title="<?php echo htmlspecialchars($banner['title']); ?>" data-description="<?php echo htmlspecialchars($banner['description']); ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: <?php echo ($index === 0) ? '1' : '0'; ?>; z-index: <?php echo ($index === 0) ? '1' : '0'; ?>;" alt="<?php echo htmlspecialchars($banner['title']); ?>" />
                  <?php endforeach; ?>
                <?php else: ?>
                  <img src="public/assets/img/gallery/hero.png" style="width: 100%; height: 100%; object-fit: cover;" alt="Default Banner" />
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bannerImages = document.querySelectorAll('.banner-image');
            const bannerTitle = document.getElementById('banner-title');
            const bannerDescription = document.getElementById('banner-description');
            let currentIndex = 0;

            function updateBannerContent() {
                bannerImages.forEach((img, index) => {
                    if (index === currentIndex) {
                        img.style.opacity = '1';
                        img.style.zIndex = '1'; // Bring to front
                        bannerTitle.textContent = img.dataset.title;
                        bannerDescription.textContent = img.dataset.description;
                    } else {
                        img.style.opacity = '0';
                        img.style.zIndex = '0'; // Send to back
                    }
                });
            }

            function nextBanner() {
                currentIndex = (currentIndex + 1) % bannerImages.length;
                updateBannerContent();
            }

            if (bannerImages.length > 0) {
                // Initialize with the first banner's content
                bannerTitle.textContent = bannerImages[0].dataset.title;
                bannerDescription.textContent = bannerImages[0].dataset.description;
                bannerImages[0].style.opacity = '1';
                bannerImages[0].style.zIndex = '1';

                setInterval(nextBanner, 3000); // Change every 3 seconds
            } else {
                bannerTitle.textContent = "Welcome to JHC";
                bannerDescription.textContent = "We're determined for your better life. You can get the care you need 24/7 – be it online or in person. You will be treated by caring specialist doctors.";
            }
        });
      </script>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="py-5" id="departments">

        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image:url(public/<?php echo htmlspecialchars($settings['bg_departments_path'] ?? 'assets/img/gallery/bg-departments.png'); ?>);background-position:top center;background-size:contain;">
              </div>
              <!--/.bg-holder-->

              <h1 class="text-center">POLIKLINIK</h1>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->




      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="py-0">

        <div class="container">
          <div class="row py-5 align-items-center justify-content-center justify-content-lg-evenly">
            <?php 
              $dept_sql = "SELECT id, name, icon_path, icon_hover_path FROM departments2 ORDER BY display_order ASC";
              $dept_result = $mysqli->query($dept_sql);
              if ($dept_result->num_rows > 0) {
                while($dept = $dept_result->fetch_assoc()) {
            ?>
            <div class="col-auto col-md-4 col-lg-auto text-xl-start">
              <div class="d-flex flex-column align-items-center">
                <div class="icon-box text-center icon-hover-effect"><a class="text-decoration-none department-link" href="#!" data-department-id="<?php echo $dept['id']; ?>" data-department-name="<?php echo $dept['name']; ?>"><img class="mb-3 deparment-icon" src="public/<?php echo $dept['icon_path']; ?>" style="width: 50px; height: 50px; object-fit: contain; display: block; margin: 0 auto;" alt="..." />
                    <img class="mb-3 deparment-icon-hover" src="public/<?php echo $dept['icon_hover_path']; ?>" style="width: 50px; height: 50px; object-fit: contain; display: none; margin: 0 auto;" alt="..." />
                    <p class="fs-1 fs-xxl-2 text-center"><?php echo $dept['name']; ?></p>
                  </a></div>
              </div>
            </div>
            <?php 
                }
              }
            ?>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <section class="bg-secondary">
        <div class="bg-holder" style="background-image:url(public/assets/img/gallery/bg-eye-care.png);background-position:center;background-size:contain;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <?php if (!empty($mcu_packages_data)): ?>
            <div id="mcuCarousel" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-indicators">
                <?php foreach ($mcu_packages_data as $index => $package): ?>
                  <button type="button" data-bs-target="#mcuCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo ($index === 0) ? 'active' : ''; ?>" aria-current="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
              </div>
              <div class="carousel-inner">
                <?php foreach ($mcu_packages_data as $index => $package): ?>
                  <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                    <div class="row align-items-center">
                      <div class="col-md-5 col-xxl-6 position-relative">
                        <img class="img-fluid" src="public/<?php echo htmlspecialchars($package['image_path']); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>" style="width: 100%; height: 300px; object-fit: cover;" />
                        <div class="price-sticker" style="position: absolute; top: 10px; right: 10px;">
                            Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                        </div>
                      </div>
                      <div class="col-md-7 col-xxl-6 text-center text-md-start">
                        <h2 class="fw-bold text-light mb-4 mt-4 mt-lg-0"><?php echo htmlspecialchars($package['title']); ?></h2>
                        <p class="text-light"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                        <div class="py-3">
                          <?php
                            $whatsapp_number = '6287760615300';
                            $whatsapp_message = urlencode("Saya ingin order pemeriksaan MCU dengan paket " . $package['title'] . " dengan harga Rp " . number_format($package['price'], 0, ',', '.') . ".");
                            $whatsapp_link = "https://api.whatsapp.com/send?phone={$whatsapp_number}&text={$whatsapp_message}";
                          ?>
                          <a class="btn btn-lg btn-light rounded-pill" href="<?php echo $whatsapp_link; ?>" target="_blank" role="button">Booking MCU</a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#mcuCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#mcuCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          <?php else: ?>
            <div class="row align-items-center">
              <div class="col-12 text-center">
                <h2 class="fw-bold text-light mb-4 mt-4 mt-lg-0">No MCU Packages Available</h2>
                <p class="text-light">Please check back later for our exciting health checkup packages.</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="pb-0" id="about">

        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/about-us.png);background-position:top center;background-size:contain;">
              </div>
              <!--/.bg-holder-->
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->

      <?php
        $about_us_sections = [];
        $sql_about = "SELECT section_key, title, content, image_path FROM about_us_sections2";
        $result_about = $mysqli->query($sql_about);
        if ($result_about && $result_about->num_rows > 0) {
            while($row = $result_about->fetch_assoc()) {
                $about_us_sections[$row['section_key']] = $row;
            }
        }
      ?>
      <section class="py-5">
        <div class="bg-holder bg-size" style="background-image:url(public/<?php echo htmlspecialchars($settings['bg_about_path'] ?? 'assets/img/gallery/about-bg.png'); ?>);background-position:top center;background-size:contain;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-md-3">
                    <h1 class="text-center text-md-start">TENTANG KAMI</h1>
                </div>
                <div class="col-md-9">
                    <ul class="nav nav-pills justify-content-center justify-content-md-start" id="about-us-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="visi-misi-tab" data-bs-toggle="pill" data-bs-target="#visi-misi" type="button" role="tab" aria-controls="visi-misi" aria-selected="true">Visi-Misi</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sejarah-tab" data-bs-toggle="pill" data-bs-target="#sejarah" type="button" role="tab" aria-controls="sejarah" aria-selected="false">Sejarah</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salam-direktur-tab" data-bs-toggle="pill" data-bs-target="#salam-direktur" type="button" role="tab" aria-controls="salam-direktur" aria-selected="false">Salam Direktur</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="budaya-kerja-tab" data-bs-toggle="pill" data-bs-target="#budaya-kerja" type="button" role="tab" aria-controls="budaya-kerja" aria-selected="false">Budaya Kerja</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="about-us-tabs-content">
                <?php $first = true; foreach ($about_us_sections as $key => $section): ?>
                <div class="tab-pane fade <?php if($first) echo 'show active'; ?>" id="<?php echo $key; ?>" role="tabpanel" aria-labelledby="<?php echo $key; ?>-tab">
                    <div class="row align-items-center">
                        <div class="col-md-6 order-lg-1 mb-5 mb-lg-0">
                            <img class="fit-cover rounded-circle w-100" src="public/<?php echo $section['image_path']; ?>" alt="..." />
                        </div>
                        <div class="col-md-6 text-center text-md-start">
                            <h2 class="fw-bold mb-4"><?php echo $section['title']; ?></h2>
                            <p><?php echo nl2br($section['content']); ?></p>
                        </div>
                    </div>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
      </section>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="pb-0" id="our-doctors">

        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url(public/<?php echo htmlspecialchars($settings['bg_doctors_path'] ?? 'assets/img/gallery/doctors-us.png'); ?>);background-position:center;background-size:cover;">
              </div>
              <!--/.bg-holder-->

              <h1 class="text-center position-relative">DOKTER KAMI</h1>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <section class="py-5">
        <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/doctors-bg.png);background-position:top center;background-size:contain;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <div class="row flex-center">
            <div class="col-xl-10 px-0">
              <div class="carousel slide" id="carouselExampleDark" data-bs-ride="carousel"><a class="carousel-control-prev carousel-icon z-index-2" href="#carouselExampleDark" role="button" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></a><a class="carousel-control-next carousel-icon z-index-2" href="#carouselExampleDark" role="button" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></a>
                <div class="carousel-inner">
                  <?php
                    $featured_sql = "SELECT id, name, specialty, photo_path FROM doctors2 WHERE is_featured = 1";
                    $featured_result = $mysqli->query($featured_sql);
                    if ($featured_result && $featured_result->num_rows > 0) {
                        $featured_doctors = $featured_result->fetch_all(MYSQLI_ASSOC);
                        $doctor_chunks = array_chunk($featured_doctors, 3);
                        $is_first_item = true;
                        foreach ($doctor_chunks as $chunk) {
                  ?>
                  <div class="carousel-item <?php if ($is_first_item) { echo 'active'; $is_first_item = false; } ?>" data-bs-interval="10000">
                    <div class="row h-100 m-lg-7 mx-3 mt-6 mx-md-4 my-md-7">
                      <?php foreach ($chunk as $doctor) { ?>
                      <div class="col-md-4 mb-8 mb-md-0">
                        <div class="card card-span h-100 shadow">
                          <div class="card-body d-flex flex-column flex-center py-5">
                            <img src="public/<?php echo htmlspecialchars($doctor['photo_path'] ? $doctor['photo_path'] : 'assets/img/gallery/jane.png'); ?>" width="128" alt="..." />
                            <h5 class="mt-3"><?php echo htmlspecialchars($doctor['name']); ?></h5>
                            <p class="mb-0 fs-xxl-1"><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                            <div class="text-center mt-4">
                              <a class="btn btn-outline-secondary rounded-pill" href="public/doctor_details.php?id=<?php echo $doctor['id']; ?>">View Profile</a>
                            </div>
                          </div>
                        </div>
                      </div>
                      <?php } ?>
                    </div>
                  </div>
                  <?php 
                        }
                    } else {
                  ?>
                    <div class="carousel-item active"><p class="text-center">No featured doctors found.</p></div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section id="facilities">
        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/bg-departments.png);background-position:top center;background-size:contain;">
              </div>
              <h1 class="text-center">FASILITAS</h1>
            </div>
          </div>
          <div class="row">
            <?php
              $facilities_sql = "SELECT * FROM facilities2 ORDER BY display_order ASC";
              $facilities_result = $mysqli->query($facilities_sql);
              if ($facilities_result && $facilities_result->num_rows > 0) {
                while($facility = $facilities_result->fetch_assoc()) {
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 shadow card-span rounded-3">
                <img class="card-img-top rounded-top-3" src="public/<?php echo htmlspecialchars($facility['image_path']); ?>" alt="<?php echo htmlspecialchars($facility['name']); ?>" />
                <div class="card-body">
                  <h5 class="font-base fs-lg-0 fs-xl-1 my-3"><?php echo htmlspecialchars($facility['name']); ?></h5>
                  <p><?php echo nl2br(htmlspecialchars($facility['description'])); ?></p>
                </div>
              </div>
            </div>
            <?php 
                }
              }
            ?>
          </div>
        </div>
      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section id="careers" class="bg-light">
        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <h1 class="text-center">KARIR</h1>
            </div>
          </div>
          <div class="row">
            <div class="accordion" id="careersAccordion">
            <?php
              $careers_sql = "SELECT * FROM careers2 WHERE status = 'open' AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY post_date DESC";
              $careers_result = $mysqli->query($careers_sql);
              if ($careers_result && $careers_result->num_rows > 0) {
                $is_first_career = true;
                while($career = $careers_result->fetch_assoc()) {
            ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $career['id']; ?>">
                  <button class="accordion-button <?php if(!$is_first_career) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $career['id']; ?>" aria-expanded="<?php echo $is_first_career ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $career['id']; ?>">
                    <?php echo htmlspecialchars($career['job_title']); ?> - <small class="text-muted ms-2"><?php echo htmlspecialchars($career['location']); ?>
                    <?php if (!empty($career['end_date'])): ?>
                        <br>Tanggal Beakhir : <?php echo date('d M Y', strtotime($career['end_date'])); ?>
                    <?php endif; ?>
                    </small>
                  </button>
                </h2>
                <div id="collapse<?php echo $career['id']; ?>" class="accordion-collapse collapse <?php if($is_first_career) echo 'show'; ?>" aria-labelledby="heading<?php echo $career['id']; ?>" data-bs-parent="#careersAccordion">
                  <div class="accordion-body">
                    <?php echo nl2br(htmlspecialchars($career['description'])); ?>
                    <div class="mt-3">
                        <a href="public/apply.php?job_id=<?php echo $career['id']; ?>" class="btn btn-primary">Apply Now</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php 
                  $is_first_career = false;
                }
              } else {
                echo "<p class='text-center'>No open positions at the moment.</p>";
              }
            ?>
            </div>
          </div>
        </div>
      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->

      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section id="virtual-room">
        <div class="container">
          <?php
            $vr_sql = "SELECT title, content, image_path_360 FROM page_virtual_room2 WHERE id = 1";
            $vr_result = $mysqli->query($vr_sql);
            $vr_content = $vr_result->fetch_assoc();
          ?>
          <div class="row text-center">
            <div class="col-12 py-3">
              <h1 class="text-center"><?php echo htmlspecialchars($vr_content['title']); ?></h1>
              <p><?php echo htmlspecialchars($vr_content['content']); ?></p>
            </div>
            <div class="col-12">
              <?php if (!empty($vr_content['image_path_360'])) : ?>
                <div id="panorama" style="width: 100%; height: 500px;"></div>
                <script>
                  document.addEventListener('DOMContentLoaded', function() {
                    pannellum.viewer('panorama', {
                      "type": "equirectangular",
                      "panorama": "<?php echo BASE_URL . 'public/' . htmlspecialchars($vr_content['image_path_360']); ?>",
                      "autoLoad": true
                    });
                  });
                </script>
              <?php else: ?>
                <div class="alert alert-info">No 360-degree image available for virtual tour. Please upload one from the admin panel.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->

      </section>
      <!-- DEBUG NEWS SECTION START -->
      <section class="py-5" id="news">

        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image:url(public/<?php echo htmlspecialchars($settings['bg_news_path'] ?? 'assets/img/gallery/blog-post.png'); ?>);background-position:top center;background-size:contain;">
              </div>
              <!--/.bg-holder-->

              <h1 class="text-center">BERITA</h1>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <section>
        <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/dot-bg.png);background-position:top left;background-size:auto;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <div class="row">
            <?php
              $news_sql = "SELECT * FROM news2 ORDER BY post_date DESC LIMIT 4";
              $news_result = $mysqli->query($news_sql);
              if ($news_result && $news_result->num_rows > 0) {
                while($article = $news_result->fetch_assoc()) {
            ?>
            <div class="col-sm-6 col-lg-4 mb-4">
              <div class="card h-100 shadow card-span rounded-3"><img class="card-img-top rounded-top-3" src="public/<?php echo htmlspecialchars($article['image_path']); ?>" style="height: 300px; object-fit: cover;" alt="<?php echo htmlspecialchars($article['title']); ?>" />
                <div class="card-body"><span class="fs--1 text-primary me-3">Health</span>
                  <svg class="bi bi-calendar2 me-2" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"> </path>
                  </svg><span class="fs--1 text-900">Nov 21, 2021</span><span class="fs--1"></span>
                  <h5 class="font-base fs-lg-0 fs-xl-1 my-3"><?php echo htmlspecialchars($article['title']); ?></h5><a class="stretched-link" href="public/news_article.php?id=<?php echo $article['id']; ?>">read full article</a>
                </div>
              </div>
            </div>
            <?php 
                }
              } else {
                echo "<p class='text-center'>No news articles found.</p>";
              }
            ?>
          </div>
        </div>
      </section>
      <!-- DEBUG NEWS SECTION END -->


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="py-5 position-relative" id="partners">
        <?php if (!empty($settings['bg_partners_path'])):
            $bg_url = 'public/' . htmlspecialchars($settings['bg_partners_path']);
        ?>
        <div class="bg-holder bg-size" style="background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('<?php echo $bg_url; ?>'); background-position: center; background-size: cover;">
        </div>
        <!--/.bg-holder-->
        <?php endif; ?>
        <div class="container">
            <div class="row">
                <div class="col-12 py-3">
                    <h1 class="text-center">MITRA KERJA SAMA</h1>
                </div>
            </div>

            <div class="row mt-4">
                <?php

                if (!empty($partners_data)) {
                    foreach($partners_data as $partner) {
                        echo '<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">';
                        echo '    <div class="card h-100 text-center shadow-sm">';
                        if (!empty($partner['url'])) {
                            echo '        <a href="' . htmlspecialchars($partner['url']) . '" target="_blank">';
                        }
                        echo '            <img src="public/' . htmlspecialchars($partner['logo_path']) . '" class="card-img-top p-3" alt="' . htmlspecialchars($partner['name']) . '" style="object-fit: contain; height: 100px;">';
                        if (!empty($partner['url'])) {
                            echo '        </a>';
                        }
                        echo '        <div class="card-footer">';
                        echo '            <h6 class="card-title fs--1">' . htmlspecialchars($partner['name']) . '</h6>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center">Tidak ada mitra untuk ditampilkan.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->
      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <!-- DEBUG APPOINTMENT SECTION START -->
      <section class="py-5">

        <div class="container">
          <div class="row">
            <div class="col-12 py-3">
              <div class="bg-holder bg-size" style="background-image:url(public/assets/img/gallery/people.png);background-position:top center;background-size:contain;">
              </div>
              <!--/.bg-holder-->

              <h1 class="text-center">KONTAK</h1>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <section class="py-8" id="appointment">
        <div class="container">
          <div class="row">
            <div class="bg-holder bg-size" style="background-image:url(public/<?php echo htmlspecialchars($settings['bg_contact_path'] ?? 'assets/img/gallery/dot-bg.png'); ?>);background-position:bottom right;background-size:auto;">
            </div>
            <!--/.bg-holder-->

            <div class="col-lg-6 z-index-2 mb-5"><img class="w-100" src="public/assets/img/gallery/appointment.png" alt="..." /></div>
            <div class="col-lg-6 z-index-2">
              <div id="appointment-message"></div>
              <form class="row g-3" id="appointment-form">
                <div class="col-md-6">
                  <label class="visually-hidden" for="inputName">Name</label>
                  <input class="form-control form-livedoc-control" id="inputName" name="name" type="text" placeholder="Name" required />
                </div>
                <div class="col-md-6">
                  <label class="visually-hidden" for="inputPhone">Phone</label>
                  <input class="form-control form-livedoc-control" id="inputPhone" name="phone" type="text" placeholder="Phone" />
                </div>
                <div class="col-md-6">
                  <label class="form-label visually-hidden" for="inputCategory">Category</label>
                  <select class="form-select" id="inputCategory" name="category">
                    <option selected="selected">Category</option>
                    <option> Pelayanan</option>
                    <option> Jadwal</option>
                    <option> Fasilitas</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="visually-hidden" for="inputEmail">Email</label>
                  <input class="form-control form-livedoc-control" id="inputEmail" name="email" type="email" placeholder="Email" required />
                </div>
                <div class="col-md-12">
                  <label class="form-label visually-hidden" for="validationTextarea">Message</label>
                  <textarea class="form-control form-livedoc-control" id="validationTextarea" name="message" placeholder="Message" style="height: 250px;" required></textarea>
                </div>
                <div class="col-12">
                  <div class="d-grid">
                    <button class="btn btn-primary rounded-pill" type="submit">Send Message</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
      <!-- DEBUG APPOINTMENT SECTION END -->

      <section class="py-0 bg-secondary">
        <div class="bg-holder opacity-25" style="background-image:url(public/assets/img/gallery/dot-bg.png);background-position:top left;margin-top:-3.125rem;background-size:auto;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <?php
            // Settings are already fetched in public_header.php
            // $settings = [];
            // $settings_result = $mysqli->query("SELECT * FROM settings WHERE setting_key LIKE 'contact_%'");
            // while($setting = $settings_result->fetch_assoc()){
            //     $settings[$setting['setting_key']] = $setting['setting_value'];
            // }
          ?>
          <div class="row py-8">
            <div class="col-12 col-sm-12 col-lg-6 mb-4 order-0 order-sm-0"><a class="text-decoration-none" href="#"><img src="/comprojhc/public/<?php echo htmlspecialchars($settings['footer_logo_path'] ?? 'assets/img/gallery/footer-logo.png'); ?>" height="51" alt="" /></a>
              <p class="text-light my-4"><?php echo nl2br(htmlspecialchars($settings['contact_tagline'] ?? '')); ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-2 order-sm-1">
              <h5 class="lh-lg fw-bold mb-4 text-light font-sans-serif">Poliklinik</h5>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="footer-link" href="#!">Jantung</a></li>
                <li class="lh-lg"><a class="footer-link" href="#!">Neurologi</a></li>
                <li class="lh-lg"><a class="footer-link" href="#!">Penyakit Dalam</a></li>
              </ul>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-3 order-sm-2">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif"> Customer Care</h5>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="footer-link" href="#!">About Us</a></li>
                <li class="lh-lg"><a class="footer-link" href="#!">Contact US</a></li>
                <li class="lh-lg"><a class="footer-link" href="#!">Get Update</a></li>
              </ul>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 mb-3 order-3 order-sm-2">
              <h5 class="lh-lg fw-bold text-light mb-4 font-sans-serif"> Our Location</h5>
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.3026479277464!2d108.22658777513445!3d-7.319860891719987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f575256b155e3%3A0xf50e5319fe82a294!2sRS%20Jantung%20Tasikmalaya!5e0!3m2!1sid!2sid!4v1759453736032!5m2!1sid!2sid" width="200" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          </div>
        </div>


        <!-- ============================================-->
        <!-- <section> begin ============================-->
        <section class="py-0 bg-primary">

          <div class="container">
            <div class="row justify-content-md-between justify-content-evenly py-4">
              <div class="col-12 col-sm-8 col-md-6 col-lg-auto text-center text-md-start">
                <p class="fs--1 my-2 fw-bold text-200">&copy; NuTech, 2025</p>
              </div>
              <div class="col-12 col-sm-8 col-md-6">
               
              </div>
            </div>
          </div>
          <!-- end of .container-->

        </section>
        <!-- <section> close ============================-->
        <!-- ============================================-->


      </section>
<?php require_once "public/layout/public_footer.php"; ?>