<?php

require_once 'config.php';


$limit = 3;
$offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : 0;

$stmt = $pdo->prepare("SELECT * FROM testimonials ORDER BY submission_date DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if any results were found
if (empty($testimonials)) {
    // Return an empty string or a JSON object indicating no more data
    http_response_code(204); // No Content
    exit;
}


ob_start();


foreach ($testimonials as $t):
    $image = !empty($t['client_image_path']) ? htmlspecialchars($t['client_image_path']) : 'assets/images/default-avatar.svg';
    $rating = 5; 
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= '<i class="bi bi-star-fill text-gold me-1"></i>';
    }
    $submission_date = date("M d, Y", strtotime($t['submission_date'])); 
?>
    <div class="col load-more-item">
        <div class="card h-100 verified-testimonial-card shadow-lg">
            <div class="testimonial-stars-premium mb-3">
                <?= $stars ?>
            </div>

            <p class="testimonial-quote-text">“<?= htmlspecialchars($t['testimonial_text']) ?>”</p>

            <div class="mt-auto testimonial-footer-premium">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <img src="<?= $image ?>" class="testimonial-avatar" alt="Client">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($t['client_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($t['client_location']) ?></small>
                        </div>
                    </div>
                    <span class="testimonial-service-badge"><?= htmlspecialchars($t['product_service']) ?></span>
                </div>
                <div class="text-end">
                    <small class="text-secondary opacity-50"><?= $submission_date ?></small>
                </div>
            </div>
        </div>
    </div>
<?php
endforeach;


echo ob_get_clean();
?>