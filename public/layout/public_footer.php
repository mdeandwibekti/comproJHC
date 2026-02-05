    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->




    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->
    <script src="public/vendors/@popperjs/popper.min.js"></script>
    <script src="public/vendors/bootstrap/bootstrap.min.js"></script>
    <script src="public/vendors/is/is.min.js"></script>
    <script src="https://scripts.sirv.com/sirvjs/v3/sirv.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="public/vendors/fontawesome/all.min.js"></script>
    <script src="public/assets/js/theme.js"></script>
    <!-- <script src="public/assets/vendors/pannellum/pannellum.min.js"></script> -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Modal -->
    <div class="modal fade" id="doctorsModal" tabindex="-1" aria-labelledby="doctorsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="doctorsModalLabel">Doctors</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="doctorsModalBody">
            <!-- Doctors will be loaded here -->
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var doctorsModal = new bootstrap.Modal(document.getElementById('doctorsModal'));
        var modalTitle = document.getElementById('doctorsModalLabel');
        var modalBody = document.getElementById('doctorsModalBody');

        document.querySelectorAll('.department-link').forEach(function (link) {
          link.addEventListener('click', function (event) {
            event.preventDefault();
            var deptId = this.dataset.departmentId;
            var deptName = this.dataset.departmentName;

            modalTitle.textContent = 'Doctors in ' + deptName;
            modalBody.innerHTML = '<p>Loading...</p>';
            doctorsModal.show();

            fetch('public/api/get_doctors.php?department_id=' + deptId)
              .then(response => response.json())
              .then(data => {
                var html = '<div class="row">'
                if (data.length > 0) {
                  data.forEach(function (doctor) {
                    var photo = doctor.photo_path ? 'public/' + doctor.photo_path : 'public/assets/img/gallery/jane.png';
                    html += '<div class="col-md-4 mb-4">';
                    html += '  <div class="card h-100 text-center">';
                    html += '    <img src="' + photo + '" class="card-img-top" alt="' + doctor.name + '" style="height: 200px; object-fit: cover;">';
                    html += '    <div class="card-body">';
                    html += '      <h5 class="card-title">' + doctor.name + '</h5>';
                    html += '      <p class="card-text">' + doctor.specialty + '</p>';
                    html += '    </div>';
                    html += '  </div>';
                    html += '</div>';
                  });
                } else {
                  html += '<div class="col-12"><p>No doctors found for this department.</p></div>';
                }
                html += '</div>';
                modalBody.innerHTML = html;
              })
              .catch(error => {
                modalBody.innerHTML = '<p>Error loading doctors. Please try again later.</p>';
                console.error('Error:', error);
              });
          });
        });

        // Handle Appointment Form Submission
        var appointmentForm = document.getElementById('appointment-form');
        appointmentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(appointmentForm);
            var messageDiv = document.getElementById('appointment-message');

            messageDiv.innerHTML = '<div class="alert alert-info">Sending...</div>';

            fetch('public/api/submit_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    appointmentForm.reset();
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                console.error('Error:', error);
            });
        });
      });
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&amp;family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100&amp;display=swap" rel="stylesheet">

    <?php if (isset($settings['popup_status']) && $settings['popup_status'] == 'active' && !empty($settings['popup_title'])) : ?>
    <!-- Promotional Popup Modal -->
    <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="promoModalLabel"><?php echo htmlspecialchars($settings['popup_title']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <?php if (isset($settings['popup_image_path']) && !empty($settings['popup_image_path'])) : ?>
              <img src="public/<?php echo htmlspecialchars($settings['popup_image_path']); ?>" class="img-fluid mb-3" alt="Promotion">
            <?php endif; ?>
            <?php echo nl2br(htmlspecialchars($settings['popup_content'])); ?>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        if (!sessionStorage.getItem('promoPopupShownInSession')) {
          var promoModal = new bootstrap.Modal(document.getElementById('promoModal'));
          promoModal.show();
          sessionStorage.setItem('promoPopupShownInSession', 'true');
        }
      });
    </script>
    <?php endif; ?>
    <style>
      @keyframes pulse {
        0% {
          transform: scale(1);
        }
        50% {
          transform: scale(1.1);
        }
        100% {
          transform: scale(1);
        }
      }
      .whatsapp-button {
        animation: pulse 2s infinite;
      }
    </style>
    <a href="https://wa.me/6287760615300" target="_blank" class="whatsapp-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/1200px-WhatsApp.svg.png" alt="WhatsApp" width="50">
    </a>
  </body>

</html>