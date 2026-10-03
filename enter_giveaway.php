<?php
   require_once 'config.php';
   
   if (session_status() == PHP_SESSION_NONE) {
       session_start();
   }
   
   function get_all_active_giveaways($pdo) {
       try {
           $sql = "SELECT id, title, description, image_url, value, prize_name_short FROM giveaways ORDER BY id ASC";
           $stmt = $pdo->query($sql);
           return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
       } catch (PDOException $e) {
          
           return [];
       }
   }
   
   $giveaways = get_all_active_giveaways($pdo);
   // -----------------------------------------------------------
   
   
   function get_site_setting_value($pdo, $key, $default = '') {
       try {
           $sql = "SELECT setting_value FROM site_config WHERE setting_key = :key";
           $stmt = $pdo->prepare($sql);
           $stmt->bindParam(":key", $key, PDO::PARAM_STR);
           $stmt->execute();
           $value = $stmt->fetchColumn();
           
          
           return $value !== false ? $value : $default;
           
       } catch (PDOException $e) {
           
           return $default;
       }
   }
   

   
   
   require_once  'get_setting.php';
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Celebrity Booking'); ?> | Fan Competitions</title>
	  <!-- Favicon -->
      <?php if ($use_favicon): ?>
      <link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $favicon_path; ?>">
      <?php else: ?>
      <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
      <?php endif; ?>
      <link href="assets/css/style.css" rel="stylesheet">
      <link href="assets/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
      <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
      <script src="assets/js/sweetalert2.all.min.js"></script>
     <style>
 .prize-card {transition: transform 0.3s ease, box-shadow 0.3s ease;border: none;box-shadow: 0 0 40px 5px rgb(0 0 0 / 5%) !important;}.prize-card:hover {transform: translateY(-5px);box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);}.prize-card .card-img-top {height: 250px;object-fit: cover;}.modal-header-custom {background-color: #4f46e5;color: white;border-bottom: none;}.text-crimson {color: #6d0000 !important;}.prize-btn{padding: 6px 20px;border-radius: 50px;background: #fff;border: 2px #7d0000 solid;color: #7b0000 !important;font-weight: 700;}.prize-btn:hover{background: linear-gradient(135deg, var(--bs-crimson-light), var(--bs-crimson-dark));color:#fff !important;box-shadow:none !Important;border: 2px #970000 solid !important;}.btn-skew {transform: skewX(-15deg);padding: 10px 30px !important;border-radius: 5px !important;font-size: 13px;font-weight: 800 !important;transition: transform 0.3s ease, box-shadow 0.3s ease;font-family: 'Work Sans', sans-serif !important;display: inline-block;border: none;}.btn-skew-text {transform: skewX(15deg);display: inline-block;transition: transform 0.3s ease;}.btn-skew:hover {transform: skewX(15deg) scale(1.05);box-shadow: 0 5px 15px rgba(0,0,0,0.2);}.btn-skew:hover .btn-skew-text {transform: skewX(-15deg);}.form-step {display: none;}.form-step.active {display: block;}.was-validated .form-control:invalid ~ .invalid-feedback, .form-control.is-invalid ~ .invalid-feedback, .was-validated .form-control:invalid ~ .valid-feedback, .form-control.is-invalid ~ .valid-feedback {display: none !important;}.form-control.is-invalid {background-image: none !important;border-color: var(--bs-danger) !important;padding-right: var(--bs-form-control-padding-x) !important;}.file-upload-box {border: 2px dashed #adb5bd;border-radius: 0.375rem;padding: 2rem;text-align: center;cursor: pointer;transition: border-color 0.2s ease, background-color 0.2s ease;background-color: #f8f9fa;}.file-upload-box:hover {border-color: #4f46e5;background-color: #f0f3ff;}.file-upload-box.is-invalid {border-color: var(--bs-danger) !important;box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);}.file-upload-box input[type="file"] {opacity: 0;position: absolute;width: 100%;height: 100%;top: 0;left: 0;cursor: pointer;}.file-upload-info {display: block;color: #6c757d;margin-top: 0.5rem;}.file-upload-label {color: #4f46e5;font-weight: bold;}.file-upload-text-muted {font-size: 0.875em;}
</style>
   </head>
   <body>
      <?php
         if (isset($_SESSION['message'])):
             $msg = $_SESSION['message'];
             $type = $msg['type']; // 'success' or 'error'
         
             
             $text_json = json_encode($msg['text']);
         
            
             unset($_SESSION['message']);
         
             
             $confirm_button_color = ($type === 'success') ? '#4f46e5' : '#dc3545';
             $title = ($type === 'success') ? 'Entry Confirmed! 🥳' : 'Submission Failed 😥';
             $icon = ($type === 'success') ? 'success' : 'error';
             ?>
      <script>
         document.addEventListener('DOMContentLoaded', function() {
             
             let messageText = <?= $text_json ?>;
         
             Swal.fire({
                 title: '<?= $title ?>',
                 html: messageText, 
                 icon: '<?= $icon ?>',
                 confirmButtonText: 'Got it!',
                 confirmButtonColor: '<?= $confirm_button_color ?>'
             });
         });
      </script>
      <?php endif; ?>
      <?php include "nav.php" ?>
      <header class="hero-gradient-bg text-white text-center shadow-lg">
         <div class="container">
            <h1 class="fw-bolder mb-2" style="font-family: 'Playfair Display', serif;">WIN A CELEBRITY EXPERIENCE</h1>
            <p class="lead mb-0 fs-5 opacity-90">Choose the ultimate luxury experience you want to win.</p>
         </div>
      </header>
       <section id="giveaways" class="py-5">
          <div class="container">
            <h2 class="text-center fw-bolder mb-5 text-dark">Active Fan Competitions</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($giveaways as $prize): ?>
                <div class="col">
                  <div class="card prize-card h-100 shadow">
                      <img src="<?= str_replace('../', '', $prize['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($prize['title']) ?>">
                      <div class="card-body d-flex flex-column">
                          <h5 class="card-title fw-bold text-crimson mb-2">
                          <?= htmlspecialchars($prize['title']) ?></h4>
                          <p class="card-text text-dark-text flex-grow-1"><?= htmlspecialchars($prize['description']) ?></p>
                          <div class="mt-4">
                              <button type="button" style="" 
                                  class="btn btn-md btn-select-prize prize-btn btn-gradient text-white"
                                  data-bs-toggle="modal"
                                  data-bs-target="#entryModal"
                                  data-prize-id="<?= $prize['id'] ?>"
                                  data-prize-name="<?= htmlspecialchars($prize['prize_name_short']) ?>">
                              <i class="bi bi-check2-circle me-1"></i> Select Prize
                              </button>
                          </div>
                      </div>
                  </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                  <div class="p-4 rounded-3 text-white shadow-lg d-flex align-items-start" 
                      style="background-color:#0c0c0c; border: 4px solid rgba(255, 255, 255, 0.5);">
                      <i class="bi bi-star-fill fs-2 me-3 flex-shrink-0 text-warning"></i>
                      <div>
                          <h4 class="fw-bold mb-1 text-uppercase" style="letter-spacing: 1px;">Crucial Rule: One Selection Per Draw!</h4>
                          <p class="lead mb-0 fs-6">
                              Please remember, your entry is limited to **ONE prize** per official draw period. 
                              <span class="fw-semibold">Choose the item you desire most</span>—your dream prize awaits!
                          </p>
                      </div>
                  </div>
                </div>
            </div>
          </div>
      </section>
      
      <div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title fw-bold" id="entryModalLabel">Official Entry for: <span id="modalPrizeTitle"></span></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                  <form id="officialEntryForm" method="POST" action="process_entry.php" class="needs-validation" enctype="multipart/form-data" novalidate>
                      <input type="hidden" name="prize_id" id="modalPrizeId">
                      <input type="hidden" name="prize_name" id="modalPrizeName">
                      
                      <div class="form-step active" id="step-1">
                          
                          <div class="mb-3">
                              <label for="fullName" class="form-label fw-bold">Full Name</label>
                              <input type="text" class="form-control" id="fullName" name="fullName" 
                                 placeholder="Enter your full name as it should appear" required>
                              <div class="invalid-feedback">Please enter your full name.</div>
                          </div>
                          <div class="row">
                              <div class="col-md-8 mb-3">
                                 <label for="phoneNumber" class="form-label fw-bold">Phone Number</label>
                                 <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                                     placeholder="Enter valid phone number" 
                                     required pattern="[0-9]{10,15}">
                                 <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
                              </div>
                              <div class="col-md-4 mb-3">
                                 <label for="age" class="form-label fw-bold">Age</label>
                                 <input type="number" class="form-control" id="age" name="age" 
                                     placeholder="Your age" min="18" max="120" required>
                                 <div class="invalid-feedback">18 or older to enter.</div>
                              </div>
                          </div>
                          <div class="row g-3 mb-3">
                              <div class="col-12">
                                 <label for="streetAddress" class="form-label fw-bold">Street Address</label>
                                 <input type="text" class="form-control" id="streetAddress" name="streetAddress" 
                                     placeholder="Enter your complete street address" required>
                                 <div class="invalid-feedback">Street address required.</div>
                              </div>
                              <div class="col-md-6">
                                 <label for="city" class="form-label fw-bold">City</label>
                                 <input type="text" class="form-control" id="city" name="city" 
                                     placeholder="Enter your city" required>
                                 <div class="invalid-feedback">City required.</div>
                              </div>
                              <div class="col-md-6">
                                 <label for="zipCode" class="form-label fw-bold">Zip / Postal Code</label>
                                 <input type="text" class="form-control" id="zipCode" name="zipCode" 
                                     placeholder="ZIP or postal code" required>
                                 <div class="invalid-feedback">Zip or postal code*.</div>
                              </div>
							  <div class="col-md-12">
                                <label for="heardAbout" class="form-label fw-bold">How did you hear about us?</label>
                                <select class="form-select" id="heardAbout" name="heardAbout" required>
                                    <option value="" disabled selected>Select one option</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Twitter/X">Twitter/X</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="YouTube">YouTube</option>
                                    <option value="GoogleSearch">Google Search</option>
                                    <option value="Friend/WordOfMouth">Friend / Word of Mouth</option>
                                    <option value="Others">Other</option>
                                </select>
                                <div class="invalid-feedback">Please tell us how you heard about the giveaway.</div>
                            </div>
                          </div>
                          
                          <div class="d-grid mt-4">
                              <button type="button" id="continueToStep2" class="btn btn-lg btn-select-prize btn-gradient text-white fw-bold">
                              CONTINUE
                              </button>
                          </div>
                      </div>
                      
                      <div class="form-step" id="step-2">
                          
                          <div class="alert alert-info small" role="alert" style="font-size: 14px;">
                              We require a valid ID to verify your identity and age (18+). These files are securely managed.
                          </div>
                          
                          <div class="mb-4">
    <label for="idFront" class="form-label fw-bold">Valid ID: Front Side</label>
    <div class="file-upload-box position-relative" id="idFrontContainer">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
        <span class="file-upload-label d-block" id="idFrontFileName">Click or drag a file here</span>
        <span class="file-upload-text-muted d-block">Accepted: JPG, PNG, PDF. Max: 5MB.</span>
        <input type="file" id="idFront" name="idFront" accept="image/*, application/pdf" required>
    </div>
    <div class="invalid-feedback">Please upload the front side of your valid ID.</div>
</div>
                          
                          <div class="mb-4">
    <label for="idBack" class="form-label fw-bold">Valid ID: Back Side</label>
    <div class="file-upload-box position-relative" id="idBackContainer">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
        <span class="file-upload-label d-block" id="idBackFileName">Click or drag a file here</span>
        <span class="file-upload-text-muted d-block">Accepted: JPG, PNG, PDF. Max: 5MB.</span>
        <input type="file" id="idBack" name="idBack" accept="image/*, application/pdf" required>
    </div>
    <div class="invalid-feedback">Please upload the back side of your valid ID.</div>
</div>
                          
                          <p class="text-muted small mt-4">
                              By clicking 'Verify', you submit all information (personal details and ID uploads) and acknowledge that you have read and agree to the 
                              <a href="#" class="text-decoration-none">Official Rules</a> and <strong>Terms & Conditions</strong>. 
                              You consent to be contacted regarding the giveaway results.
                          </p>
                          
                          <div class="d-grid mt-4">
                              <button type="submit" id="finalSubmitBtn" class="btn btn-lg btn-gradient text-white fw-bold">
                              <i class="fas fa-check-circle me-2"></i> Verify
                              </button>
                          </div>
                          
                      </div>
                      
                  </form>
                </div>
            </div>
          </div>
      </div>
      <?php include "footer.php" ?>
      <script src="assets/js/bootstrap.bundle.min.js"></script>
      <script>
    document.addEventListener('DOMContentLoaded', function () {
        const entryModal = document.getElementById('entryModal');
        const form = document.getElementById('officialEntryForm');
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const continueBtn = document.getElementById('continueToStep2');
        const backBtn = document.getElementById('backToStep1');
        const finalSubmitBtn = document.getElementById('finalSubmitBtn');

        // --- Custom File Input Handlers ---
        const fileInputs = [
            { input: document.getElementById('idFront'), nameDisplay: document.getElementById('idFrontFileName'), container: document.getElementById('idFrontContainer') },
            { input: document.getElementById('idBack'), nameDisplay: document.getElementById('idBackFileName'), container: document.getElementById('idBackContainer') }
        ];

        fileInputs.forEach(item => {
            // Update display name when a file is selected
            item.input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    item.nameDisplay.textContent = this.files[0].name;
                    item.nameDisplay.classList.remove('text-danger');
                    item.nameDisplay.classList.add('text-success');
                } else {
                    item.nameDisplay.textContent = 'Click or drag a file here';
                    item.nameDisplay.classList.add('text-danger');
                    item.nameDisplay.classList.remove('text-success');
                }
                
                // Manually apply/remove the invalid class to the container
                if (form.classList.contains('was-validated')) {
                    if (this.checkValidity()) {
                        item.container.classList.remove('is-invalid');
                    } else {
                        item.container.classList.add('is-invalid');
                    }
                }
            });
        });

        // Function to check validity of Step 1 fields
        function isStep1Valid() {
            let isValid = true;
            const step1Fields = step1.querySelectorAll('[required]');
            step1Fields.forEach(field => {
                if (!field.checkValidity()) {
                    isValid = false;
                }
            });
            return isValid;
        }

        // Event listener for opening the modal (existing logic)
        entryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const prizeId = button.getAttribute('data-prize-id');
            const prizeName = button.getAttribute('data-prize-name');

            // Update the modal's content
            entryModal.querySelector('#modalPrizeTitle').textContent = prizeName;
            entryModal.querySelector('#modalPrizeId').value = prizeId;
            entryModal.querySelector('#modalPrizeName').value = prizeName;

            // Reset to Step 1 and clear validation
            step1.classList.add('active');
            step2.classList.remove('active');
            form.classList.remove('was-validated');
            
            // Reset file upload displays
            fileInputs.forEach(item => {
                item.nameDisplay.textContent = 'Click or drag a file here';
                item.nameDisplay.classList.remove('text-success', 'text-danger');
                item.container.classList.remove('is-invalid');
            });

            // Reset button text
            finalSubmitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Verify';
            finalSubmitBtn.disabled = false;
        });

        // Event listener for Continue Button (Step 1 to Step 2)
        continueBtn.addEventListener('click', function () {
            // Apply validation to Step 1 fields only
            step1.querySelectorAll('[required]').forEach(field => {
                field.classList.add('was-validated');
            });

            if (isStep1Valid()) {
                // Remove validation classes from step 1 fields visually before transition
                step1.querySelectorAll('[required]').forEach(field => {
                    field.classList.remove('was-validated');
                });
                
                // Transition to Step 2
                step1.classList.remove('active');
                step2.classList.add('active');
                
            } else {
                // If invalid, force validation visuals on step 1
                form.classList.add('was-validated');
            }
        });

        // Event listener for Back Button (Step 2 to Step 1)
        backBtn.addEventListener('click', function () {
            step2.classList.remove('active');
            step1.classList.add('active');
            form.classList.remove('was-validated'); // Clear overall validation state
            fileInputs.forEach(item => {
                item.container.classList.remove('is-invalid');
            });
        });

        // Final Form Submission Handler (Step 2)
        form.addEventListener('submit', event => {
            let isFormValid = form.checkValidity();
            
            // Manually check file inputs and apply invalid class to containers
            fileInputs.forEach(item => {
                if (!item.input.checkValidity()) {
                    item.container.classList.add('is-invalid');
                    item.nameDisplay.classList.add('text-danger');
                    isFormValid = false; // Propagate invalid state
                } else {
                    item.container.classList.remove('is-invalid');
                    item.nameDisplay.classList.remove('text-danger');
                    item.nameDisplay.classList.add('text-success');
                }
            });

            if (!isFormValid) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                // Form is valid - Show "Verifying..." state
                finalSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Verifying...';
                finalSubmitBtn.disabled = true;
            }
        }, false);
		
		
		// Check if the submission cookie is present on the client side
    const hasSubmitted = document.cookie.includes('giveaway_submitted=true');

    if (hasSubmitted) {
        // If the user has submitted, attach a special handler to the buttons

        // Get all prize selection buttons
        const selectButtons = document.querySelectorAll('.btn-select-prize');

        selectButtons.forEach(button => {
            // Remove the existing modal trigger attributes
            button.removeAttribute('data-bs-toggle');
            button.removeAttribute('data-bs-target');
            
            // Add a click handler to display the SweetAlert instantly
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Stop any default link/button action
                
                Swal.fire({
                    title: 'Entry Recorded!',
                    html: 'You have already submitted an entry for the giveaway. Only one entry is permitted per person.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4f46e5' // Using your brand color
                });
            });
        });
    }
    });
</script>
      
   </body>
</html>