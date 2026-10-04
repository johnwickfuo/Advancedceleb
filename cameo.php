<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['cameo_csrf'])) {
    $_SESSION['cameo_csrf'] = bin2hex(random_bytes(32));
}

$h = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$error_msg = '';
$has_payment_options = $bank_enabled || $crypto_enabled || $giftcard_enabled;
$celebrities = [];
try {
    $stmt = $pdo->query("SELECT id, name, description, profile_picture, cameo_price
                         FROM celebrities
                         ORDER BY name ASC");
    $celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Unable to fetch cameo talent: ' . $e->getMessage());
    $error_msg = 'Cameo bookings are temporarily unavailable. Please try again later.';
}

$selected_id = (int)($_POST['celebrity_id'] ?? $_GET['celebrity_id'] ?? 0);
$selected_celebrity = null;
foreach ($celebrities as $candidate) {
    if ((int)$candidate['id'] === $selected_id) {
        $selected_celebrity = $candidate;
        break;
    }
}

$form_name = trim((string)($_POST['user_name'] ?? ''));
$form_email = trim((string)($_POST['delivery_email'] ?? ''));
$form_whatsapp = trim((string)($_POST['delivery_whatsapp'] ?? ''));
$form_script = trim((string)($_POST['cameo_script'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_cameo'])) {
    $token = (string)($_POST['csrf_token'] ?? '');
    $whatsapp_digits = preg_replace('/\D+/', '', $form_whatsapp);
    $script_length = function_exists('mb_strlen') ? mb_strlen($form_script, 'UTF-8') : strlen($form_script);

    if (!hash_equals($_SESSION['cameo_csrf'], $token)) {
        $error_msg = 'The form session expired. Refresh the page and try again.';
    } elseif (!$has_payment_options) {
        $error_msg = 'Cameo payment is temporarily unavailable. Please try again later.';
    } elseif ($selected_id < 1 || $form_name === '' || strlen($form_name) > 255 ||
        !filter_var($form_email, FILTER_VALIDATE_EMAIL) || strlen($form_email) > 255 ||
        !preg_match('/^[1-9][0-9]{7,14}$/', $whatsapp_digits) ||
        $form_script === '' || $script_length > 5000) {
        $error_msg = 'Choose an available celebrity and provide your name, valid email, international WhatsApp number and script (maximum 5,000 characters).';
    } else {
        try {
            // Never trust a browser-supplied price; all talent has a cameo button,
            // but checkout requires an admin-configured positive video price.
            $stmt = $pdo->prepare("SELECT id, name, cameo_price FROM celebrities
                                   WHERE id = ? AND cameo_price > 0");
            $stmt->execute([$selected_id]);
            $celebrity = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$celebrity) {
                $error_msg = 'This celebrity’s cameo price has not yet been set. Please contact support or choose another celebrity.';
            } else {
                $reference = 'CAM-' . strtoupper(bin2hex(random_bytes(6)));
                $whatsapp = '+' . $whatsapp_digits;
                $amount = $celebrity['cameo_price'];

                // event_date retains an order date for compatibility with the existing
                // NOT NULL bookings schema. The admin UI shows created_at for cameos.
                $stmt = $pdo->prepare(
                    "INSERT INTO bookings
                     (booking_reference, booking_type, celebrity_id, user_name, user_email,
                      user_phone, event_date, event_type, event_details, amount, status,
                      cameo_script, delivery_email, delivery_whatsapp)
                     VALUES (?, 'cameo', ?, ?, ?, ?, CURDATE(), 'Cameo Video', '', ?, 'Pending', ?, ?, ?)"
                );
                $stmt->execute([
                    $reference, $selected_id, $form_name, $form_email, $whatsapp,
                    $amount, $form_script, $form_email, $whatsapp
                ]);

                // Sending an email must never prevent a successfully saved request.
                try {
                    require_once 'include/email_core_functions.php';
                    $smtp_config_missing = false;
                    $safe_name = $h($form_name);
                    $safe_celebrity = $h($celebrity['name']);
                    $safe_ref = $h($reference);
                    $safe_email = $h($form_email);
                    $safe_phone = $h($whatsapp);
                    $safe_script = nl2br($h($form_script));
                    $safe_amount = $h(format_currency($amount));

                    $customer_body = "<h2>Cameo Request Received</h2>
                        <p>Hello {$safe_name},</p>
                        <p>We received your cameo video request for <strong>{$safe_celebrity}</strong>.</p>
                        <p>Reference: <strong>{$safe_ref}</strong><br>Price: <strong>{$safe_amount}</strong></p>
                        <p>Please complete the payment and submit the requested proof using the payment page.
                        Your payment will remain pending until it is reviewed.</p>
                        <p>Video delivery contacts: {$safe_email} / {$safe_phone}</p>";
                    send_email_notification($site_settings, $form_email,
                        'Cameo Request Received - ' . $reference, $customer_body, $smtp_config_missing);

                    $admin_email = $site_settings['contact_email'] ?? '';
                    if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                        $admin_body = "<h2>New Cameo Video Request</h2>
                            <p><strong>Reference:</strong> {$safe_ref}</p>
                            <p><strong>Celebrity:</strong> {$safe_celebrity}</p>
                            <p><strong>Customer:</strong> {$safe_name}</p>
                            <p><strong>Delivery Email:</strong> {$safe_email}<br>
                            <strong>Delivery WhatsApp:</strong> {$safe_phone}</p>
                            <p><strong>Price:</strong> {$safe_amount}</p>
                            <p><strong>Requested words:</strong><br>{$safe_script}</p>
                            <p>Open the Cameo Requests section of your admin bookings to review it.</p>";
                        send_email_notification($site_settings, $admin_email,
                            '[NEW CAMEO] ' . $reference, $admin_body, $smtp_config_missing);
                    }
                } catch (Throwable $email_error) {
                    error_log('Cameo email notification failed: ' . $email_error->getMessage());
                }

                $_SESSION['cameo_csrf'] = bin2hex(random_bytes(32));
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Cameo Request Created',
                    'text' => 'Your video request has been saved. Proceed with payment and submit the payment proof. Reference: ' . $reference
                ];
                header('Location: payment.php?ref=' . urlencode($reference));
                exit;
            }
        } catch (PDOException $e) {
            error_log('Cameo booking failed: ' . $e->getMessage());
            $error_msg = 'We could not save your request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'head.php'; ?>
    <title>Request a Cameo Video | <?php echo $h($site_settings['site_title'] ?? 'Celebrity Booking'); ?></title>
    <style>
        .cameo-hero {
            background: linear-gradient(135deg, rgba(176,0,0,.9), rgba(15,23,42,.94)),
                url('assets/img/booking_hero_bg.jpg') center/cover no-repeat;
            padding: 105px 0 65px;
            color: #fff;
        }
        .cameo-hero h1 { color: #fff; font-family: 'Playfair Display',serif; font-weight: 800; }
        .cameo-card { background: white; border-radius: 18px; padding: clamp(22px, 4vw, 44px); }
        .cameo-card .form-label { font-weight: 700; }
        .cameo-photo { width: 72px; height: 72px; object-fit: cover; border-radius: 50%; }
        .cameo-price { color: #b00000; font-weight: 800; font-size: 1.35rem; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<section class="cameo-hero text-center">
    <div class="container">
        <p class="text-uppercase fw-bold" style="letter-spacing: 3px; color: #ffc107;">Personalized Video Request</p>
        <h1>Request a Cameo Video</h1>
        <p class="lead mx-auto" style="max-width: 700px;">Send the words you want the talent to say and tell us where to deliver your video.</p>
    </div>
</section>
<section class="py-5 bg-light">
    <div class="container" style="max-width: 850px;">
        <div class="cameo-card shadow-sm">
            <?php if ($error_msg): ?>
                <div class="alert alert-danger" role="alert"><?php echo $h($error_msg); ?></div>
            <?php endif; ?>
            <?php if (!$celebrities): ?>
                <div class="alert alert-info">No celebrities are available at the moment. Please check back later.</div>
                <a href="performers.php" class="btn btn-outline-secondary">Back to Celebrity Roster</a>
            <?php else: ?>
                <?php if (!$has_payment_options): ?>
                    <div class="alert alert-warning">Cameo requests are temporarily paused because no payment method is enabled.</div>
                <?php endif; ?>
                <?php if ($selected_celebrity): ?>
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <img src="<?php echo $h($selected_celebrity['profile_picture'] ?: 'assets/img/perf_default.jpg'); ?>"
                             alt="<?php echo $h($selected_celebrity['name']); ?>" class="cameo-photo"
                             onerror="this.src='assets/img/perf_default.jpg';">
                        <div>
                            <div class="fw-bold fs-5"><?php echo $h($selected_celebrity['name']); ?></div>
                            <div class="cameo-price"><?php echo ((float)($selected_celebrity['cameo_price'] ?? 0) > 0) ? $h(format_currency($selected_celebrity['cameo_price'])) : 'Cameo price not yet set'; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <form method="post" action="cameo.php" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?php echo $h($_SESSION['cameo_csrf']); ?>">
                    <h3 class="mb-4" style="font-family:'Playfair Display',serif;">Cameo Video Details</h3>
                    <div class="mb-4">
                        <label for="celebrity_id" class="form-label">Choose Celebrity</label>
                        <select name="celebrity_id" id="celebrity_id" class="form-select" required>
                            <option value="">-- Select a celebrity --</option>
                            <?php foreach ($celebrities as $cel): ?>
                                <?php $is_priced = isset($cel['cameo_price']) && (float)$cel['cameo_price'] > 0; ?>
                                <?php $price_label = $is_priced ? format_currency($cel['cameo_price']) : 'Price not yet set'; ?>
                                <option value="<?php echo (int)$cel['id']; ?>"
                                    data-price="<?php echo $h($price_label); ?>"
                                    data-priced="<?php echo $is_priced ? '1' : '0'; ?>"
                                    <?php echo $selected_id === (int)$cel['id'] ? 'selected' : ''; ?>>
                                    <?php echo $h($cel['name']); ?> — <?php echo $h($price_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="small mt-2">Cameo price: <strong id="cameo_price_display" class="text-danger"><?php echo $selected_celebrity ? (((float)($selected_celebrity['cameo_price'] ?? 0) > 0) ? $h(format_currency($selected_celebrity['cameo_price'])) : 'Price not yet set') : 'Choose a celebrity'; ?></strong></div>
                        <div id="cameo_price_notice" class="small text-muted mt-1">All celebrities can be requested. Checkout requires the admin to configure their individual cameo price.</div>
                    </div>
                    <div class="mb-4">
                        <label for="cameo_script" class="form-label">Words you want the celebrity to say</label>
                        <textarea name="cameo_script" id="cameo_script" class="form-control" rows="7" maxlength="5000" required
                            placeholder="Write the message exactly as you would like it said. Include the recipient's name, pronunciation notes, and any important details."><?php echo $h($form_script); ?></textarea>
                        <small class="text-muted">Required; up to 5,000 characters. Requests are subject to talent review and approval.</small>
                    </div>
                    <h3 class="mb-4 mt-5" style="font-family:'Playfair Display',serif;">Your Delivery Information</h3>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Your Full Name</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" maxlength="255"
                            value="<?php echo $h($form_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_email" class="form-label">Delivery Email</label>
                        <input type="email" name="delivery_email" id="delivery_email" class="form-control" maxlength="255"
                            value="<?php echo $h($form_email); ?>" required autocomplete="email">
                    </div>
                    <div class="mb-4">
                        <label for="delivery_whatsapp" class="form-label">Delivery WhatsApp (with country code)</label>
                        <input type="tel" name="delivery_whatsapp" id="delivery_whatsapp" class="form-control" maxlength="40"
                            value="<?php echo $h($form_whatsapp); ?>" placeholder="+2348012345678" required autocomplete="tel">
                        <small class="text-muted">The completed video can be delivered through the provided email and WhatsApp number.</small>
                    </div>
                    <div class="small text-muted mb-3">
                        When you continue, your request is created and you are directed to the same payment options used for celebrity bookings. Payment proof is reviewed before fulfillment.
                    </div>
                    <button type="submit" id="submit_cameo" name="submit_cameo" class="btn btn-gold w-100 py-3 fs-5 fw-bold"
                        <?php echo ($has_payment_options && $selected_celebrity && (float)($selected_celebrity['cameo_price'] ?? 0) > 0) ? '' : 'disabled'; ?>>
                        Continue to Payment <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<script>
    const cameoSelect = document.getElementById('celebrity_id');
    if (cameoSelect) {
        const updateCameoPrice = function () {
            const option = cameoSelect.options[cameoSelect.selectedIndex];
            document.getElementById('cameo_price_display').textContent = option?.dataset.price || 'Choose a celebrity';
            const button = document.getElementById('submit_cameo');
            if (button) {
                button.disabled = !<?php echo $has_payment_options ? 'true' : 'false'; ?> || option?.dataset.priced !== '1';
            }
        };
        cameoSelect.addEventListener('change', updateCameoPrice);
        updateCameoPrice();
    }
</script>
<?php include 'footer.php'; ?>
</body>
</html>
