<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once '../config.php'; 
require_once '../get_setting.php';

$shipment_id = $_GET['id'] ?? null;

if (!$shipment_id) {
    $_SESSION['message'] = "Invalid Shipment ID.";
    $_SESSION['message_type'] = "danger";
    header("location: shipment_manager.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->execute([$shipment_id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipment) {
    $_SESSION['message'] = "Shipment not found.";
    $_SESSION['message_type'] = "danger";
    header("location: shipment_manager.php");
    exit;
}

$service_types = [
    'Standard' => 'Standard Logistics',
    'Express' => 'Express Air Cargo',
    'International' => 'Global International',
    'Freight' => 'Heavy Freight Shipping',
    'Door-to-Door' => 'Door-to-Door Quick Delivery',
    'Specialized' => 'Specialized Handling (Fragile/Secure)',
    'Eco' => 'Eco-Friendly Shipping'
];

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Edit Shipment #<?php echo htmlspecialchars($shipment['tracking_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
	<style>
        :root {--bs-crimson-dark: #6D0000;--bs-crimson-light: #B00000;--bs-gold: #FFC107;--crimson-gradient: linear-gradient(135deg, var(--bs-crimson-light), var(--bs-crimson-dark));--bs-crimson-faded: #b000001a;--sidebar-width: 260px;--sidebar-bg: #212529;}
        body {background-color: #f4f7f6;font-family: 'Work Sans', sans-serif;}
        .form-control, .form-select {border-radius: 8px;border: 1px solid #ced4da;padding: 0.7rem 1rem;font-size: 0.9rem;transition: all 0.2s;}
        .form-control:focus, .form-select:focus {border-color: var(--bs-crimson-light);box-shadow: 0 0 0 0.25rem var(--bs-crimson-faded);}
        .form-label {font-weight: 700;color: #495057;margin-bottom: 0.4rem;font-size: 0.85rem;text-transform: uppercase;letter-spacing: 0.5px;}
        .card-enhanced {border: none;border-radius: 12px;box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);background: #fff;margin-bottom: 1.5rem;}
        .card-header {background: #fafafa;border-bottom: 1px solid #f1f3f5;font-weight: 800;color: #343a40;text-transform: uppercase;letter-spacing: 1px;font-size: 0.8rem;}
        .tracking-number-badge {background: #e9ecef;padding: 10px 15px;border-radius: 8px;font-family: 'JetBrains Mono', monospace;font-weight: 800;color: var(--bs-crimson-dark);border: 1px solid #ced4da;display: block;text-align: center;font-size: 1.1rem;}
        .img-preview-container { position: relative; width: 100px; height: 100px; overflow: hidden; border-radius: 8px; border: 1px solid #eee; }
        .img-preview-container img { width: 100%; height: 100%; object-fit: cover; }
        #drag-drop-area {border: 2px dashed #dee2e6;background: #fcfcfc;border-radius: 12px;transition: all 0.3s;}
        #drag-drop-area.drag-over {border-color: var(--bs-crimson-light);background: #fff5f5;}
	</style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        
          <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bolder mb-0">Edit Shipment <small class="text-muted">#<?php echo htmlspecialchars($shipment['tracking_number']); ?></small></h4>
                    <div>
                        <a href="packing_slip.php?id=<?php echo $shipment['id']; ?>" target="_blank" class="btn btn-sm btn-crimson fw-bold me-2">
                            <i class="bi bi-file-earmark-text-fill me-1"></i> Packing Slip
                        </a>
                        <a href="shipment_manager.php" class="btn btn-sm btn-outline-secondary fw-bold">
                            <i class="bi bi-arrow-left me-2"></i> Back to Fleet
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <form action="process_shipment.php" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="update_shipment" value="1">
                    <input type="hidden" name="shipment_id" value="<?php echo $shipment['id']; ?>">
                    
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
                                <input type="text" class="form-control" id="sender_name" name="sender_name" value="<?php echo htmlspecialchars($shipment['sender_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="sender_phone" name="sender_phone" value="<?php echo htmlspecialchars($shipment['sender_phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_address" class="form-label">Address</label>
                                <textarea class="form-control" id="sender_address" name="sender_address" rows="3" required><?php echo htmlspecialchars($shipment['sender_address']); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted"><i class="bi bi-box-arrow-in-down-left me-2"></i>Receiver</h6>
                            <hr class="my-2">
                            <div class="mb-3">
                                <label for="receiver_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name" value="<?php echo htmlspecialchars($shipment['receiver_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="receiver_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="receiver_email" name="receiver_email" value="<?php echo htmlspecialchars($shipment['receiver_email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="receiver_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="receiver_phone" name="receiver_phone" value="<?php echo htmlspecialchars($shipment['receiver_phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="receiver_address" class="form-label">Address</label>
                                <textarea class="form-control" id="receiver_address" name="receiver_address" rows="3" required><?php echo htmlspecialchars($shipment['receiver_address']); ?></textarea>
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
                        <input type="text" class="form-control fw-bold text-center" id="tracking_number" name="tracking_number" value="<?php echo htmlspecialchars($shipment['tracking_number']); ?>" style="font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; color: var(--bs-crimson-dark); background-color: #fff;" required>
                    </div>

                    <div class="mb-3">
                        <label for="service_type" class="form-label">Service Type</label>
                        <select class="form-select" id="service_type" name="service_type" required>
                            <?php foreach ($service_types as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($shipment['service_type'] == $val) ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estimated_delivery" class="form-label">Estimated Delivery Date & Time</label>
                        <input type="datetime-local" class="form-control" id="estimated_delivery" name="estimated_delivery" value="<?php echo date('Y-m-d\TH:i', strtotime($shipment['estimated_delivery'])); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status" required>
                            <option value="Unpaid" <?php echo ($shipment['payment_status'] == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                            <option value="Paid" <?php echo ($shipment['payment_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="Payment on Delivery" <?php echo ($shipment['payment_status'] == 'Payment on Delivery') ? 'selected' : ''; ?>>Payment on Delivery</option>
                        </select>
                    </div>
                    
                    <div id="payment-details-container" class="mb-3">
						<div class="row g-3">
							<div class="col-6">
								<label for="amount" class="form-label">Amount Due</label>
								<input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($shipment['amount']); ?>">
							</div>
                            <div class="col-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <?php foreach ($currencies as $code => $symbol): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($shipment['currency'] == $code) ? 'selected' : ''; ?>>
                                            <?php echo $code; ?> (<?php echo $symbol; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
						</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity</label>
                              <input type="number" min="1" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($shipment['quantity']); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="weight" class="form-label">Weight</label>
                            <input type="number" step="0.01" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($shipment['weight']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="dimensions" class="form-label">DM (cm)</label>
                            <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?php echo htmlspecialchars($shipment['dimensions']); ?>" placeholder="Eg: 30x20x15" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Shipment Status</label>
                        <select class="form-select" id="status" name="status" required>
                                <option value="Shipment information received" <?php echo ($shipment['status'] == 'Shipment information received' || $shipment['status'] == 'Information Received') ? 'selected' : ''; ?>>Shipment information received</option>
                                <option value="Shipment received at origin facility" <?php echo ($shipment['status'] == 'Shipment received at origin facility') ? 'selected' : ''; ?>>Shipment received at origin facility</option>
                                <option value="Departed origin facility" <?php echo ($shipment['status'] == 'Departed origin facility') ? 'selected' : ''; ?>>Departed origin facility</option>
                                <option value="In transit to next facility" <?php echo ($shipment['status'] == 'In transit to next facility') ? 'selected' : ''; ?>>In transit to next facility</option>
                                <option value="Delay in transit" <?php echo ($shipment['status'] == 'Delay in transit') ? 'selected' : ''; ?>>Delay in transit</option>
                                <option value="Arrived at sorting facility" <?php echo ($shipment['status'] == 'Arrived at sorting facility') ? 'selected' : ''; ?>>Arrived at sorting facility</option>
                                <option value="On hold" <?php echo ($shipment['status'] == 'On hold') ? 'selected' : ''; ?>>On hold</option>
                                <option value="Rescheduled for delivery" <?php echo ($shipment['status'] == 'Rescheduled for delivery') ? 'selected' : ''; ?>>Rescheduled for delivery</option>
                                <option value="Customs clearance in progress" <?php echo ($shipment['status'] == 'Customs clearance in progress') ? 'selected' : ''; ?>>Customs clearance in progress</option>
                                <option value="Awaiting customs documentation" <?php echo ($shipment['status'] == 'Awaiting customs documentation') ? 'selected' : ''; ?>>Awaiting customs documentation</option>
                                <option value="Cleared by customs" <?php echo ($shipment['status'] == 'Cleared by customs') ? 'selected' : ''; ?>>Cleared by customs</option>
                                <option value="Out for delivery" <?php echo ($shipment['status'] == 'Out for delivery') ? 'selected' : ''; ?>>Out for delivery</option>
                                <option value="Delivery attempted" <?php echo ($shipment['status'] == 'Delivery attempted') ? 'selected' : ''; ?>>Delivery attempted</option>
                                <option value="Available for pickup" <?php echo ($shipment['status'] == 'Available for pickup') ? 'selected' : ''; ?>>Available for pickup</option>
                                <option value="Delivered successfully" <?php echo ($shipment['status'] == 'Delivered successfully') ? 'selected' : ''; ?>>Delivered successfully</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="update_location" class="form-label">Current Update Location</label>
                        <input type="text" class="form-control" id="update_location" name="update_location" placeholder="e.g. London, UK (Leave empty to use Receiver Address)">
                        <div class="form-text mt-1 text-muted">This location will be saved to the tracking history for this update.</div>
                    </div>

                    <div class="mb-3">
                        <label for="update_datetime" class="form-label">Update Date & Time</label>
                        <input type="datetime-local" class="form-control" id="update_datetime" name="update_datetime">
                        <div class="form-text mt-1 text-muted">Leave empty to use current system time.</div>
                    </div>

                    <div class="mb-3">
                        <label for="package_description" class="form-label">Package Contents</label>
                        <textarea class="form-control" id="package_description" name="package_description" rows="3" required><?php echo htmlspecialchars($shipment['package_description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="handling_instructions" class="form-label">Handling Instructions / Special Notes</label>
                        <textarea class="form-control" id="handling_instructions" name="handling_instructions" rows="3" placeholder="e.g. Fragile, Handle with care, Keep dry"><?php echo htmlspecialchars($shipment['handling_instructions'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
    <div class="card-enhanced border-0">
        <div class="card-header border-0 py-3 px-4">
            <span class="mb-0 fw-bold">Package Images</span>
        </div>
        <div class="card-body">
            <?php 
            $has_images = !empty($shipment['package_images']);
            $images = $has_images ? explode(',', $shipment['package_images']) : ['assets/images/package.PNG'];
            ?>
            <div class="d-flex flex-wrap gap-3 mb-4">
                <?php foreach ($images as $img): ?>
                <div class="img-preview-container shadow-sm position-relative">
                    <img src="../<?php echo htmlspecialchars($img); ?>" alt="Shipment Image">
                    <?php if (!$has_images): ?>
                        <span class="badge bg-secondary position-absolute bottom-0 start-50 translate-middle-x mb-2" style="font-size: 0.6rem;">Default Placeholder</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($has_images): ?>
                <div class="alert alert-info border-0 shadow-sm small py-2">
                    <i class="bi bi-info-circle me-1"></i> Note: Uploading new images will replace existing ones.
                </div>
            <?php endif; ?>

            <div id="drag-drop-area" 
                 class="d-flex flex-column align-items-center justify-content-center p-4 rounded-3 border border-2 border-dashed position-relative"
                 tabindex="0" 
                 style="min-height: 150px; background-color: var(--bs-crimson-faded); transition: all 0.2s ease, border-color 0.2s ease;">
                
                <i class="bi bi-cloud-arrow-up-fill fs-2 mb-2" style="color: var(--bs-crimson-dark);"></i>
                <p class="small fw-bold mb-1" style="color: var(--bs-crimson-dark);">Drag & Drop Your Images Here</p>
                <p class="text-muted mb-1 small">or</p>
                <button type="button" class="btn btn-sm btn-outline-dark fw-bold px-3 py-1 file-select-btn">Browse Files</button>
                <p class="small text-muted mt-2 mb-0" style="font-size: 0.75rem;">(Maximum 5 images, JPG/PNG only)</p>

                <div class="spinner-border text-dark position-absolute top-50 start-50 translate-middle d-none" role="status" id="upload-spinner">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <input type="file" class="form-control d-none" id="package_images" name="package_images[]" accept="image/jpeg, image/png" multiple>
            <div id="image-previews" class="row g-3 mt-3"></div>
        </div>
    </div>
</div>

        <div class="col-12 text-center my-4">
            <button type="submit" class="btn btn-crimson fw-bold btn-lg w-50">
                <i class="bi bi-check-circle me-2"></i> UPDATE SHIPMENT
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
        paymentDetailsContainer.style.display = isUnpaid ? 'block' : 'none';
        if (isUnpaid) {
            amountInput.setAttribute('required', 'required');
            currencySelect.setAttribute('required', 'required');
        } else {
            amountInput.removeAttribute('required');
            currencySelect.removeAttribute('required');
        }
    }

    paymentStatusSelect.addEventListener('change', togglePaymentDetails);
    togglePaymentDetails();
    
    // --- Image Uploader Logic (Enhanced Design & Fixed Bug) ---
    const dragDropArea = document.getElementById('drag-drop-area');
    const fileInput = document.getElementById('package_images');
    const imagePreviews = document.getElementById('image-previews');
    const selectFileButton = dragDropArea.querySelector('.file-select-btn');
    const uploadSpinner = document.getElementById('upload-spinner');

    // 1. Click Listener for the new button
    selectFileButton.addEventListener('click', (e) => {
        e.stopPropagation(); // Avoid triggering click on dragDropArea
        fileInput.click(); // Trigger the hidden file input
    });

    // 2. Click Listener for the entire drag-drop-area (for accessibility/general click)
    dragDropArea.addEventListener('click', (e) => {
        // Only trigger if the click isn't on the button itself or inside a preview/spinner
        if (!e.target.closest('.file-select-btn') && !e.target.closest('#image-previews') && !e.target.closest('#upload-spinner')) {
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
        dragDropArea.classList.add('drag-over');
    });

    dragDropArea.addEventListener('dragleave', () => {
        dragDropArea.classList.remove('drag-over');
    });

    dragDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropArea.classList.remove('drag-over');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        }
    });

    // 5. Function to process and preview files
    function handleFiles(files) {
        imagePreviews.innerHTML = '';
        if (uploadSpinner) uploadSpinner.classList.remove('d-none');

        const fileArray = Array.from(files).slice(0, 5);
        
        if (fileArray.length === 0) {
            if (uploadSpinner) uploadSpinner.classList.add('d-none');
            return;
        }

        let loadedCount = 0;
        fileArray.forEach(file => {
            if (!file.type.startsWith('image/')) {
                console.warn(`File ${file.name} is not an image.`);
                loadedCount++;
                if (loadedCount === fileArray.length && uploadSpinner) {
                    uploadSpinner.classList.add('d-none');
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
                if (loadedCount === fileArray.length && uploadSpinner) {
                    uploadSpinner.classList.add('d-none');
                }
            };
            reader.onerror = () => {
                console.error("Error reading file:", file.name);
                loadedCount++;
                if (loadedCount === fileArray.length && uploadSpinner) {
                    uploadSpinner.classList.add('d-none');
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Pre-fill Update Date & Time with local timezone-corrected current time
    const updateDatetimeField = document.getElementById('update_datetime');
    if (updateDatetimeField) {
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now - offset)).toISOString().slice(0, 16);
        updateDatetimeField.value = localISOTime;
    }
</script>
</body>
</html>
