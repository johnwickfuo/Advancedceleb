<?php
// payment.php
require_once 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'get_setting.php';

// 1. Get the Tracking Number (Invoice ID) from the URL
$tracking_number = $_GET['ref'] ?? null;
$booking = null;
$amount_due = 0.00;
$currency_symbol = '$'; // Default
$payment_status = '';
$recipient_name = '';
$is_cameo = false;

// 2. Fetch Booking Data from the database
if ($tracking_number) {
    try {
        $sql = "SELECT b.id, b.amount, b.status, b.user_name, b.booking_type, c.name AS celebrity_name
                FROM bookings b
                LEFT JOIN celebrities c ON c.id = b.celebrity_id
                WHERE b.booking_reference = :tracking_number";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tracking_number', $tracking_number);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $booking_id = $booking['id'];
            $payment_status = $booking['status'];
            $is_cameo = ($booking['booking_type'] ?? 'event') === 'cameo';
            
            // Calculate display variables
            $amount_due = number_format((float)$booking['amount'], 2);
            
            $header_text = $is_cameo ? "CAMEO VIDEO #{$tracking_number}" : "INVOICE #{$tracking_number}";
            $recipient_name = $is_cameo ? ($booking['celebrity_name'] ?? 'Celebrity') : $booking['user_name'];

        } else {
            // No booking found for this reference
            $header_text = "INVOICE NOT FOUND";
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Error',
                'text' => 'The requested invoice could not be found.',
            ];
            header('Location: index.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Payment Page Error: " . $e->getMessage());
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'System Error',
            'text' => 'A system error occurred while fetching invoice details.',
        ];
        header('Location: index.php');
        exit;
    }
} else {
    // 4. Enforce conditional loading (no 'ref' parameter in URL)
    $header_text = "ACCESS DENIED";
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Missing Invoice ID',
        'text' => 'Please use the link provided to access this payment page.',
    ];
    header('Location: index.php');
    exit;
}

?>
<?php
$is_donation = (strpos($tracking_number, 'DON-') === 0);
?>
<!DOCTYPE html>
<html lang="en">
   <head>
     <title><?php echo htmlspecialchars($header_text, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
     <?php include "head.php"; ?>
    <style>
        .payment-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            color: #fff;
            padding: 6.5rem 0 2rem;
            text-align: center;
            position: relative;
        }
        .payment-hero::before {
            display: none !important;
        }
        .payment-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.75rem;
        }
        .payment-hero p.subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 1rem;
        }
        .payment-hero .total-due {
            color: #c29b57;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .payment-hero p.note {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1.25rem;
        }
    </style>
   </head>
   <body>
      <?php include "header.php" ?>
      <?php
          if (isset($_SESSION['alert'])) {
              $alert = $_SESSION['alert'];
              unset($_SESSION['alert']);
              
              $alert_type = json_encode($alert['type'] ?? 'info');
              $alert_title = json_encode($alert['title'] ?? '');
              $alert_text = json_encode($alert['text'] ?? '');
              $alert_html = isset($alert['html']) ? json_encode($alert['html']) : 'null';
              $alert_redirect = isset($alert['redirect']) ? json_encode($alert['redirect']) : 'null';
              $confirm_btn_text = json_encode($alert['confirmButtonText'] ?? 'OK');
              
              echo "<script>
                  document.addEventListener('DOMContentLoaded', function() {
                      var alertConfig = {
                          icon: {$alert_type},
                          title: {$alert_title},
                          confirmButtonColor: '#c29b57',
                          confirmButtonText: {$confirm_btn_text}
                      };
                      var alertHtml = {$alert_html};
                      if (alertHtml) {
                          alertConfig.html = alertHtml;
                      } else {
                          alertConfig.text = {$alert_text};
                      }
                      Swal.fire(alertConfig).then(function(result) {
                          var redirectUrl = {$alert_redirect};
                          if (redirectUrl) {
                              window.location.href = redirectUrl;
                          }
                      });
                  });
              </script>";
          }
      ?>
      <section class="payment-hero shadow-lg">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 0.25rem; font-size: 0.85rem;">
                <?php echo $is_donation ? 'Charitable Donation Payment' : ($is_cameo ? 'Cameo Video Payment' : 'Secure Booking Payment'); ?>
            </p>
            <h1><?php echo htmlspecialchars($header_text, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="subtitle mx-auto mb-3" style="max-width: 600px;">
                <?php echo $is_donation ? 'Contribution by' : ($is_cameo ? 'Video request for' : 'Booking for'); ?> <strong class="text-white"><?php echo htmlspecialchars($recipient_name ?? 'Client'); ?></strong>
            </p>
            <div class="total-due">
                TOTAL DUE: <?php echo format_currency((float)$booking['amount']); ?>
            </div>
            <p class="note mx-auto mb-3" style="max-width: 600px;">
                <?php echo $is_donation ? 'Kindly select your payment option below to complete your generous contribution.' : ($is_cameo ? 'Choose a payment option and submit your payment proof. The request remains pending until verification.' : 'Kindly proceed with payment to confirm your exclusive booking.'); ?>
            </p>
            <div class="mt-3">
                <a href="index.php" class="btn btn-sm rounded-pill px-4 py-2 fw-bold" style="border: 2px solid #c29b57; color: #c29b57; transition: all 0.3s; background: transparent; font-size: 0.85rem;" onmouseover="this.style.background='#c29b57'; this.style.color='#000';" onmouseout="this.style.background='transparent'; this.style.color='#c29b57';">
                    <i class="bi bi-house-door-fill me-2"></i> BACK TO HOME
                </a>
            </div>
        </div>
      </section>


      <section class="py-5 bg-light">
          <div class="container">
             <div class="mx-auto text-center" style="max-width: 600px;">
                <h2 class="fw-bolder mb-1 text-dark">SECURE PAYMENT OPTIONS</h2>
             </div>
             <p class="text-center text-muted mb-4">All transactions are handled safely, ensuring your information stays secure.</p>
             <div class="row justify-content-center">
                <div class="col-12 text-center">
                  <div class="d-grid gap-2 d-sm-flex justify-content-sm-center flex-column" style="max-width: 300px; margin: 0 auto;">
                     <?php if ($bank_enabled): ?>
                     <a class="btn btn-md btn-bank-transfer rounded-pill" href="#bankDetailsModal" data-bs-toggle="modal">
                     <i class="fas fa-university me-2"></i> Bank Transfer
                     </a>
                     <?php endif; ?>
                     <?php if ($crypto_enabled): ?>
                     <a class="btn btn-md btn-crypto-payments rounded-pill" href="#cryptoDetailsModal" data-bs-toggle="modal">
                     <i class="fab fa-btc me-2"></i> Crypto Payments
                     </a>
                     <?php endif; ?>
                     <?php if ($giftcard_enabled): ?>
                     <a class="btn btn-md btn-gift-cards rounded-pill" href="#giftcardDetailsModal" data-bs-toggle="modal">
                     <i class="fas fa-gift me-2"></i> Gift Cards
                     </a>
                     <?php endif; ?>
                     <?php if (!$bank_enabled && !$crypto_enabled && !$giftcard_enabled): ?>
                     <p class="text-danger lead">No active payment methods found.</p>
                     <?php endif; ?>
                  </div>
                </div>
             </div>
          </div>
      </section>

      <section class="py-5 bg-white border-top">
          <div class="container">
              <div class="mx-auto text-center mb-5" style="max-width: 600px;">
                  <h2 class="fw-bolder mb-1 text-dark">PAYMENT ROADMAP</h2>
                  <p class="text-muted">A simple 4-step process to <?php echo $is_cameo ? 'complete your cameo video request' : 'secure your exclusive booking'; ?>.</p>
              </div>
              
              <div class="row g-4 justify-content-center">
                  <div class="col-lg-3 col-md-6">
                      <div class="text-center p-4 h-100">
                          <div class="mb-3">
                              <span class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle shadow-sm" style="width: 60px; height: 60px;">
                                  <i class="bi bi-1-circle-fill fs-3"></i>
                              </span>
                          </div>
                          <h5 class="text-white">Select Method</h5>
                          <p class="small text-muted">Choose between Bank Transfer, Crypto, or Gift Cards above.</p>
                      </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                      <div class="text-center p-4 h-100">
                          <div class="mb-3">
                              <span class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle shadow-sm" style="width: 60px; height: 60px;">
                                  <i class="bi bi-2-circle-fill fs-3"></i>
                              </span>
                          </div>
                          <h5 class="text-white">Make Payment</h5>
                          <p class="small text-muted">Follow the specific instructions provided in the payment modal.</p>
                      </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                      <div class="text-center p-4 h-100">
                          <div class="mb-3">
                              <span class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle shadow-sm" style="width: 60px; height: 60px;">
                                  <i class="bi bi-3-circle-fill fs-3"></i>
                              </span>
                          </div>
                          <h5 class="text-white">Verify Proof</h5>
                          <p class="small text-muted">Submit your receipt for verification</p>
                      </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                      <div class="text-center p-4 h-100">
                          <div class="mb-3">
                              <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-sm" style="width: 60px; height: 60px;">
                                  <i class="bi bi-check-circle-fill fs-3"></i>
                              </span>
                          </div>
                          <h5 class="text-white">Booking Confirmed</h5>
                          <p class="small text-muted"><?php echo $is_cameo ? 'After verification and talent approval, the video will be coordinated for delivery.' : 'Once confirmed, your booking arrangement proceeds'; ?></p>
                      </div>
                  </div>
              </div>
          </div>
      </section>
      
      <?php include "include/payment_modals.php" ?>
      <?php include "footer.php" ?>
      
      <?php include "custom_script.php" ?>
   </body>
</html>