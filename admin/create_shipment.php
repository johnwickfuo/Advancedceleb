<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once '../config.php'; 
require_once '../get_setting.php';

// Generate a unique tracking number
$tracking_number = 'AWB-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>New Merchandise Delivery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
	<style>
	/* Base Color Variables */
        :root {--bs-crimson-dark: #6D0000;--bs-crimson-light: #B00000;--bs-gold: #FFC107;--crimson-gradient: linear-gradient(135deg, var(--bs-crimson-light), var(--bs-crimson-dark));--bs-crimson-faded: #b000001a;--sidebar-width: 260px;--sidebar-bg: #212529;}
        body {background-color: #f4f7f6;font-family: 'Work Sans', sans-serif;}
        .form-control, .form-select {border-radius: 8px;border: 1px solid #ced4da;padding: 0.7rem 1rem;font-size: 0.9rem;transition: all 0.2s;}
        .form-control:focus, .form-select:focus {border-color: var(--bs-crimson-light);box-shadow: 0 0 0 0.25rem var(--bs-crimson-faded);}
        .form-label {font-weight: 700;color: #495057;margin-bottom: 0.4rem;font-size: 0.85rem;text-transform: uppercase;letter-spacing: 0.5px;}
        .card-enhanced {border: none;border-radius: 12px;box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);background: #fff;margin-bottom: 1.5rem;}
        .card-header {background: #fafafa;border-bottom: 1px solid #f1f3f5;font-weight: 800;color: #343a40;text-transform: uppercase;letter-spacing: 1px;font-size: 0.8rem;}
        #drag-drop-area {border: 2px dashed #dee2e6;background: #fcfcfc;border-radius: 12px;transition: all 0.3s;}
        #drag-drop-area.drag-over {border-color: var(--bs-crimson-light);background: #fff5f5;}
        .tracking-number-badge {background: #e9ecef;padding: 10px 15px;border-radius: 8px;font-family: 'JetBrains Mono', monospace;font-weight: 800;color: var(--bs-crimson-dark);border: 1px solid #ced4da;display: block;text-align: center;font-size: 1.1rem;}
	</style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        
          <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bolder mb-0">New Merchandise Delivery</h4>
                    <a href="shipment_manager.php" class="btn btn-sm btn-outline-secondary fw-bold">
                        <i class="bi bi-list-ul me-2"></i> Manage Fleet
                    </a>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <form action="process_shipment.php" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-enhanced border-0 h-100">
                <div class="card-header border-0 py-3 px-4">
                    <span class="mb-0 fw-bold">Party Details</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted"><i class="bi bi-box-arrow-up-right me-2"></i>Sender</h6>
                            <hr class="my-2">
                            <div class="mb-3">
                                <label for="sender_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="sender_name" name="sender_name" placeholder="Eg: John Doe" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="sender_phone" name="sender_phone" placeholder="Eg: +1 381 727 171" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_address" class="form-label">Address</label>
                                <textarea class="form-control" id="sender_address" name="sender_address" placeholder="Eg: 24 Marina Road, WallStreet USA" rows="3" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted"><i class="bi bi-box-arrow-in-down-left me-2"></i>Receiver</h6>
                            <hr class="my-2">
                            <div class="mb-3">
                                <label for="receiver_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name" placeholder="Eg: Jane Smith" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="receiver_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="receiver_email" name="receiver_email" placeholder="Eg: jane.smith@example.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="receiver_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="receiver_phone" name="receiver_phone" placeholder="Eg: +44 7000 123 456" required>
                            </div>
                            <div class="mb-3">
                                <label for="receiver_address" class="form-label">Address</label>
                                <textarea class="form-control" id="receiver_address" name="receiver_address" placeholder="Eg: 10 Oxford Street, London, UK" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-enhanced border-0 h-100">
                <div class="card-header border-0 py-3 px-4">
                    <span class="mb-0 fw-bold">Shipment Details</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="tracking_number" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control fw-bold text-center" id="tracking_number" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number); ?>" style="font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; color: var(--bs-crimson-dark); background-color: #fff;" required>
                        <div class="form-text mt-1 text-muted">A tracking number is generated automatically, but you can customize it if desired.</div>
                    </div>

                    <div class="mb-3">
                        <label for="service_type" class="form-label">Service Type</label>
                        <select class="form-select" id="service_type" name="service_type" required>
                            <option value="Standard" selected>Standard Logistics</option>
                            <option value="Express">Express Air Cargo</option>
                            <option value="International">Global International</option>
                            <option value="Freight">Heavy Freight Shipping</option>
                            <option value="Door-to-Door">Door-to-Door Quick Delivery</option>
                            <option value="Specialized">Specialized Handling (Fragile/Secure)</option>
                            <option value="Eco">Eco-Friendly Shipping</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estimated_delivery" class="form-label">Estimated Delivery Date & Time</label>
                        <input type="datetime-local" class="form-control" id="estimated_delivery" name="estimated_delivery" required value="<?php echo date('Y-m-d\TH:i', strtotime('+7 days')); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status" required>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Paid">Paid</option>
                            <option value="Payment on Delivery">Payment on Delivery</option>
                        </select>
                    </div>
                    
                    
                    <div id="payment-details-container" class="mb-3">
						<div class="row g-3">
							<div class="col-6">
								<label for="amount" class="form-label">Amount Due</label>
								<input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Eg: 95.00">
							</div>
							
							
							
							
						<?php
$currencies = [
'USD'=>'$','EUR'=>'€','GBP'=>'£','NGN'=>'₦','CAD'=>'C$','AUD'=>'A$','NZD'=>'NZ$','JPY'=>'¥','CNY'=>'¥','INR'=>'₹','ZAR'=>'R',
'CHF'=>'CHF','SEK'=>'kr','NOK'=>'kr','DKK'=>'kr','RUB'=>'₽','BRL'=>'R$','MXN'=>'MX$','SAR'=>'﷼','AED'=>'د.إ','EGP'=>'E£',
'KES'=>'KSh','GHS'=>'₵','UGX'=>'USh','TZS'=>'TSh','PKR'=>'₨','BDT'=>'৳','LKR'=>'Rs','THB'=>'฿','MYR'=>'RM','IDR'=>'Rp',
'KRW'=>'₩','HKD'=>'HK$','SGD'=>'S$','TRY'=>'₺','PLN'=>'zł','CZK'=>'Kč','HUF'=>'Ft','RON'=>'lei','ILS'=>'₪','KWD'=>'KD',
'QAR'=>'﷼','OMR'=>'﷼','BHD'=>'BD','JOD'=>'JD','MAD'=>'DH','DZD'=>'DA','TND'=>'DT','ETB'=>'Br','AOA'=>'Kz','MWK'=>'MK',
'MZN'=>'MT','ZMW'=>'ZK','BWP'=>'P','NAD'=>'N$','XOF'=>'CFA','XAF'=>'FCFA','LRD'=>'L$','SLL'=>'Le','GMD'=>'D','CVE'=>'$',
'BBD'=>'Bds$','BSD'=>'B$','BZD'=>'BZ$','JMD'=>'J$','TTD'=>'TT$','XCD'=>'EC$','KYD'=>'CI$','BMD'=>'BD$','FJD'=>'FJ$','WST'=>'T',
'TOP'=>'T$','PGK'=>'K','VUV'=>'VT','MOP'=>'P','PHP'=>'₱','MMK'=>'K','LAK'=>'₭','KHR'=>'៛','VND'=>'₫','IRR'=>'﷼','IQD'=>'د.ع',
'LBP'=>'ل.ل','SYR'=>'£','YER'=>'﷼','AFN'=>'؋','NPR'=>'₨','BTN'=>'Nu.','MVR'=>'Rf','SCR'=>'₨','MUR'=>'₨','BND'=>'B$','KZT'=>'₸',
'UZS'=>'лв','TMT'=>'m','GEL'=>'₾','AMD'=>'֏','AZN'=>'₼','BYN'=>'Br','MDL'=>'L','UAH'=>'₴','HRK'=>'kn','RSD'=>'дин','ISK'=>'kr',
'BGN'=>'лв','MKD'=>'ден','ALL'=>'L','BAM'=>'KM','ARS'=>'AR$','CLP'=>'CLP$','COP'=>'COL$','PEN'=>'S/','PYG'=>'₲','UYU'=>'$U',
'VEF'=>'Bs','BOB'=>'Bs','GTQ'=>'Q','HNL'=>'L','NIO'=>'C$','CRC'=>'₡','DOP'=>'RD$','HTG'=>'G','CUC'=>'CUC$','CUP'=>'₱','SRD'=>'SRD$'
];
?>

<div class="col-6">
    <label for="currency" class="form-label">Currency</label>
    <select class="form-select" id="currency" name="currency">
        <?php foreach ($currencies as $code => $symbol): ?>
            <option value="<?= htmlspecialchars($code) ?>"><?= $code ?> (<?= htmlspecialchars($symbol) ?>)</option>
        <?php endforeach; ?>
    </select>
</div>

						
						
						
						
						</div>
						<div class="form-text mt-2">The recipient will receive an invoice email for this amount.</div>
					</div>
                    
                    
                    

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity</label>
                              <input type="number" min="1" class="form-control" id="quantity" name="quantity" placeholder="Eg: 1" required value="1">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="weight" class="form-label">Weight</label>
                            <input type="number" step="0.01" class="form-control" id="weight" name="weight" placeholder="Eg: 5.25" required>
                        </div>
                        <div class="col-md-4">
                            <label for="dimensions" class="form-label">DM (cm)</label>
                            <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="Eg: 30x20x15" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="package_description" class="form-label">Package Contents</label>
                        <textarea class="form-control" id="package_description" name="package_description" placeholder="Eg: Electronics and accessories" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="handling_instructions" class="form-label">Handling Instructions / Special Notes</label>
                        <textarea class="form-control" id="handling_instructions" name="handling_instructions" placeholder="e.g. Fragile, Handle with care, Keep dry" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="initial_status" class="form-label">Initial Status</label>
                        <select class="form-select" id="initial_status" name="initial_status" required>
                                <option value="Shipment information received" selected>Shipment information received</option>
                                <option value="Shipment received at origin facility">Shipment received at origin facility</option>
                                <option value="Departed origin facility">Departed origin facility</option>
                                <option value="In transit to next facility">In transit to next facility</option>
                                <option value="Delay in transit">Delay in transit</option>
                                <option value="Arrived at sorting facility">Arrived at sorting facility</option>
                                <option value="On hold">On hold</option>
                                <option value="Rescheduled for delivery">Rescheduled for delivery</option>
                                <option value="Customs clearance in progress">Customs clearance in progress</option>
                                <option value="Awaiting customs documentation">Awaiting customs documentation</option>
                                <option value="Cleared by customs">Cleared by customs</option>
                                <option value="Out for delivery">Out for delivery</option>
                                <option value="Delivery attempted">Delivery attempted</option>
                                <option value="Available for pickup">Available for pickup</option>
                                <option value="Delivered successfully">Delivered successfully</option>
                        </select>
                        <div class="form-text mt-1 text-muted">This will be the first entry in the tracking history and the initial shipment state.</div>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="col-lg-12">
    <div class="card-enhanced border-0">
        <div class="card-header border-0 py-3 px-4">
            <span class="mb-0 fw-bold">Package Images (Multi-upload)</span>
        </div>
        <div class="card-body">
            <div id="drag-drop-area" 
                 class="d-flex flex-column align-items-center justify-content-center p-4 p-md-5 rounded-3 border border-2 border-dashed position-relative"
                 tabindex="0" 
                 style="min-height: 200px; background-color: var(--bs-crimson-faded); transition: all 0.2s ease, border-color 0.2s ease;">
                
                <i class="bi bi-cloud-arrow-up-fill fs-1 mb-3" style="color: var(--bs-crimson-dark);"></i>
                <p class="h5 fw-bold mb-1" style="color: var(--bs-crimson-dark);">Drag & Drop Your Images Here</p>
                <p class="text-muted mb-2">or</p>
                <button type="button" class="btn btn-sm btn-outline-dark fw-bold px-4 py-2 file-select-btn">Browse Files</button>
                <p class="small text-muted mt-3 mb-0">(Maximum 5 images, JPG/PNG only)</p>

                <div class="spinner-border text-dark position-absolute top-50 start-50 translate-middle d-none" role="status" id="upload-spinner">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <input type="file" class="form-control d-none" id="package_images" name="package_images[]" accept="image/jpeg, image/png" multiple>
            
            <div id="image-previews" class="row g-3 mt-3">
                </div>
        </div>
    </div>
</div>

        <div class="col-12 text-center my-4">
            <button type="submit" class="btn btn-crimson fw-bold btn-lg w-50">
                <i class="bi bi-save me-2"></i> Save Shipment
            </button>
        </div>

    </div>
</form>

            </div>
            <?php include "footer.php" ?>
    </div>
</div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Form Validation (Existing Code) ---
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                })
            })
    })()
    
    
    
    
    const paymentStatusSelect = document.getElementById('payment_status');
    const paymentDetailsContainer = document.getElementById('payment-details-container');
    const amountInput = document.getElementById('amount');
    const currencySelect = document.getElementById('currency');

    function togglePaymentDetails() {
        const isUnpaid = paymentStatusSelect.value === 'Unpaid';
        
        if (isUnpaid) {
            // Show the amount/currency inputs and make them required
            paymentDetailsContainer.style.display = 'block';
            amountInput.setAttribute('required', 'required');
            currencySelect.setAttribute('required', 'required');
        } else {
            // Hide the amount/currency inputs and remove required attribute
            paymentDetailsContainer.style.display = 'none';
            amountInput.removeAttribute('required');
            currencySelect.removeAttribute('required');
        }
    }

    // Attach event listener and run on page load
    paymentStatusSelect.addEventListener('change', togglePaymentDetails);
    
    // Initial call to set the correct state on load (it defaults to 'Unpaid', so it will be shown)
    togglePaymentDetails();
    
    
    
    
    
    // --- Image Uploader Logic (Enhanced Design & Fixed Bug) ---
    const dragDropArea = document.getElementById('drag-drop-area');
    const fileInput = document.getElementById('package_images');
    const imagePreviews = document.getElementById('image-previews');
    const selectFileButton = dragDropArea.querySelector('.file-select-btn'); // Get the new button
    const uploadSpinner = document.getElementById('upload-spinner');

    // 1. Click Listener for the new button
    selectFileButton.addEventListener('click', () => {
        fileInput.click(); // Trigger the hidden file input
    });

    // 2. Click Listener for the entire drag-drop-area (for accessibility/general click)
    dragDropArea.addEventListener('click', (e) => {
        // Only trigger if the click isn't on the button itself or inside a preview
        if (!e.target.closest('.file-select-btn') && !e.target.closest('#image-previews')) {
            fileInput.click();
        }
    });

    // 3. File Selection Handler: Handle files chosen via click or drag/drop
    fileInput.addEventListener('change', (event) => {
        handleFiles(event.target.files);
    });

    // 4. Drag/Drop Event Handlers (Visual Feedback)
    dragDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropArea.classList.add('drag-over'); // Add class for styling
    });

    dragDropArea.addEventListener('dragleave', () => {
        dragDropArea.classList.remove('drag-over'); // Remove class
    });

    dragDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropArea.classList.remove('drag-over'); // Remove class
        
        // Check if the drop event has files
        if (e.dataTransfer.files.length) {
            // Set the files on the hidden input to be uploaded with the form
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        }
    });

    // 5. Function to process and preview files
    function handleFiles(files) {
        imagePreviews.innerHTML = ''; // Clear existing previews
        uploadSpinner.classList.remove('d-none'); // Show spinner

        // Convert FileList to Array and limit to 5 files
        const fileArray = Array.from(files).slice(0, 5);
        
        if (fileArray.length === 0) {
            uploadSpinner.classList.add('d-none'); // Hide spinner if no files
            return;
        }

        let loadedCount = 0;
        fileArray.forEach(file => {
            // Simple validation
            if (!file.type.startsWith('image/')) {
                console.warn(`File ${file.name} is not an image.`);
                loadedCount++; // Still count invalid files to hide spinner
                if (loadedCount === fileArray.length) {
                    uploadSpinner.classList.add('d-none'); // Hide spinner when all processed
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const previewHTML = `
                    <div class="col-4 col-sm-3 col-lg-2">
                        <div class="card overflow-hidden shadow-sm border-2" style="height: 100px; border-color: var(--bs-crimson-light) !important;">
                            <img src="${e.target.result}" class="card-img-top w-100 h-100 object-fit-cover" alt="Image Preview">
                        </div>
                    </div>
                `;
                imagePreviews.insertAdjacentHTML('beforeend', previewHTML);
                loadedCount++;
                if (loadedCount === fileArray.length) {
                    uploadSpinner.classList.add('d-none'); // Hide spinner when all processed
                }
            };
            reader.onerror = () => {
                console.error("Error reading file:", file.name);
                loadedCount++;
                if (loadedCount === fileArray.length) {
                    uploadSpinner.classList.add('d-none'); // Hide spinner on error too
                }
            };
            reader.readAsDataURL(file);
        });

        // If more than 5 were selected, inform the user (optional)
        if (files.length > 5) {
            alert("Only the first 5 images were selected for upload.");
        }
    }
</script>
</body>
</html>
