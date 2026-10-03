<style>
 .file-upload-box {border: 2px dashed #007bff;border-radius: 8px;padding: 20px;text-align: center;background-color: #f8f9fa;cursor: pointer;transition: all 0.3s ease;height: 150px;display: flex;flex-direction: column;justify-content: center;align-items: center;overflow: hidden;}.file-upload-box:hover {border-color: #0056b3;background-color: #e9ecef;}.file-upload-box input[type="file"] {position: absolute;top: 0;left: 0;width: 100%;height: 100%;opacity: 0;cursor: pointer;z-index: 10;}.file-upload-box .file-upload-label {font-weight: bold;color: #343a40;}.file-upload-box .file-upload-text-muted {font-size: 0.85rem;color: #6c757d;}.file-upload-box i {color: #007bff;}.gift-card-upload-box {border-color: #dc3545;}.gift-card-upload-box i {color: #dc3545;}.gift-card-upload-box:hover {border-color: #c82333;background-color: #f0f0f0;}.copy-icon-btn {padding: 0.25rem 0.4rem;font-size: 0.8rem;line-height: 1;border-radius: 0.3rem;border: 1px solid #ccc;background-color: #fff;color: #495057;transition: all 0.2s;display: inline-flex;align-items: center;justify-content: center;}.copy-icon-btn:hover {background-color: #e9ecef;border-color: #adb5bd;}.bank-detail-row {display: flex;justify-content: space-between;align-items: center;padding: 8px 0;font-size:13px;}.bank-detail-row .detail-label {flex: 0 0 35%;color: #6c757d;}.bank-detail-row .detail-value {flex-grow: 1;font-weight: bold;text-align: right;padding-right: 10px;word-break: break-all;}.bank-detail-row .detail-copy {flex-shrink: 0;}.bank-detail-group .bank-detail-row:not(:last-child) {border-bottom: 1px solid #dee2e6;}.btn-md {transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);position: relative;overflow: hidden;transform-style: preserve-3d;}.btn-md::before {content: '';position: absolute;top: -50%;left: -50%;width: 200%;height: 200%;background: linear-gradient( 45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70% );transform: translateX(-100%) translateY(-100%) rotate(45deg);transition: transform 0.6s ease;}.btn-md:hover::before {transform: translateX(100%) translateY(100%) rotate(45deg);}.btn-md::after {content: '';position: absolute;inset: -3px;border-radius: inherit;background: linear-gradient(45deg, currentColor, transparent, currentColor);opacity: 0;filter: blur(10px);transition: opacity 0.4s ease;z-index: -1;}.btn-md:hover::after {opacity: 0.6;animation: glow-pulse 1.5s ease-in-out infinite;}@keyframes glow-pulse {0%, 100% {filter: blur(10px);opacity: 0.6;}50% {filter: blur(15px);opacity: 0.8;}}.btn-md:hover {transform: translateY(-8px) scale(1.05) rotateX(5deg);box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3), 0 5px 15px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.2);filter: brightness(1.1);}.btn-md:active {transform: translateY(-2px) scale(1.02);transition: all 0.1s ease;box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);}.btn-bank-transfer {color: #fff;background: linear-gradient(135deg, #0275df 0%, #0056b3 100%);border: 2px solid #0275df;position: relative;}.btn-bank-transfer:hover {color: #fff !important;background: linear-gradient(135deg, #0056b3 0%, #003d82 100%) !important;border-color: #0056b3 !important;box-shadow: 0 15px 35px rgba(2, 117, 223, 0.5), 0 5px 15px rgba(2, 117, 223, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;}.btn-crypto-payments {color: #fff !important;background: linear-gradient(135deg, #343a40 0%, #1a1d20 100%) !important;border: 2px solid #525256 !important;position: relative !important;}.btn-crypto-payments:hover {color: #fff !important;background: linear-gradient(135deg, #23272b 0%, #0d0f10 100%) !important;border-color: #ffc107 !important;box-shadow: 0 15px 35px rgba(255, 193, 7, 0.5), 0 5px 15px rgba(255, 193, 7, 0.3), inset 0 1px 0 rgba(255, 193, 7, 0.2) !important;}.btn-gift-cards {color: #fff !important;background: linear-gradient(135deg, #fd7e14 0%, #e66b0d 100%) !important;border: 2px solid #fd7e14 !important;position: relative;}.btn-gift-cards:hover {color: #fff;background: linear-gradient(135deg, #e66b0d 0%, #cc5c0a 100%);border-color: #e66b0d;box-shadow: 0 15px 35px rgba(253, 126, 20, 0.5), 0 5px 15px rgba(253, 126, 20, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;}@keyframes float-in {from {opacity: 0;transform: translateY(20px);}to {opacity: 1;transform: translateY(0);}}.btn-md {animation: float-in 0.6s ease-out backwards;}.btn-md:nth-child(1) {animation-delay: 0.1s;}.btn-md:nth-child(2) {animation-delay: 0.2s;}.btn-md:nth-child(3) {animation-delay: 0.3s;}.btn-lg i {margin-right: 0.5rem;}.input-group-text{padding: .375rem 1rem;}
</style>

<?php

if ($bank_enabled ?? true):
?>
<div class="modal fade" id="bankDetailsModal" tabindex="-1" aria-labelledby="bankDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(145deg, var(--primary-dark, #0f172a) 0%, #1a0000 100%); border-bottom: 3px solid var(--secondary, #FFC107);">
                <h5 class="modal-title text-white" id="bankDetailsLabel">Complete Bank Transfer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="payment_method" value="bank">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id ?? ''); ?>">
                <input type="hidden" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number ?? ''); ?>">
                <div class="modal-body">
                    <p class="fw-bold text-dark">Please transfer funds to the following account:</p>
                    
                    <div class="mb-4 p-3 bg-light border rounded bank-detail-group">
                        <div class="bank-detail-row">
                            <span class="detail-label">Bank Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($bank_name ?? 'Placeholder Bank'); ?></span>
                            <button type="button" class="copy-icon-btn copy-btn detail-copy" data-copy-text="<?php echo htmlspecialchars($bank_name ?? 'Placeholder Bank'); ?>" title="Copy Bank Name">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="bank-detail-row">
                            <span class="detail-label">Account Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($bank_account_name ?? 'Placeholder Account'); ?></span>
                            <button type="button" class="copy-icon-btn copy-btn detail-copy" data-copy-text="<?php echo htmlspecialchars($bank_account_name ?? 'Placeholder Account'); ?>" title="Copy Account Name">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="bank-detail-row">
                            <span class="detail-label">Sort Code:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($bank_sort_code ?? '00-00-00'); ?></span>
                            <button type="button" class="copy-icon-btn copy-btn detail-copy" data-copy-text="<?php echo htmlspecialchars($bank_sort_code ?? '00-00-00'); ?>" title="Copy Sort Code">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="bank-detail-row">
                            <span class="detail-label">Account Number:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($bank_account_number ?? '12345678'); ?></span>
                            <button type="button" class="copy-icon-btn copy-btn detail-copy" data-copy-text="<?php echo htmlspecialchars($bank_account_number ?? '12345678'); ?>" title="Copy Account Number">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bankAmount" class="form-label fw-bold">Amount Paid (required)</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?></span>
                            <input type="number" step="0.01" min="0.01" class="form-control rounded-0 rounded-end border-0 border-top border-end border-bottom" id="bankAmount" name="amount" placeholder="e.g., 500.00" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bankProof" class="form-label fw-bold">Upload Payment Proof (Screenshot/Receipt)</label>
                        <div class="file-upload-box position-relative">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                            <span class="file-upload-label d-block" id="bankProofFileName">Click or drag a file here</span>
                            <span class="file-upload-text-muted d-block">Accepted: JPG, PNG, PDF. Max: 5MB.</span>
                            <input type="file" id="bankProof" name="payment_proof" accept="image/*, application/pdf" required>
                        </div>
                        <div class="invalid-feedback">Please upload your payment proof.</div>
                    </div>

                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #e63946, #c1121f); border: none; letter-spacing: 0.5px; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(193, 18, 31, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                        Submit Payment Proof
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($crypto_enabled ?? true): ?>
<div class="modal fade" id="cryptoDetailsModal" tabindex="-1" aria-labelledby="cryptoDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(145deg, var(--primary-dark, #0f172a) 0%, #1a0000 100%); border-bottom: 3px solid var(--secondary, #FFC107);">
                <h5 class="modal-title text-white" id="cryptoDetailsLabel">Complete Crypto Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="payment_method" value="crypto">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id ?? ''); ?>">
                <input type="hidden" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number ?? ''); ?>">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label for="cryptoCurrency" class="form-label fw-bold">Select Cryptocurrency</label>
                        <select class="form-select rounded" id="cryptoCurrency" name="currency" required>
                            <option value="">-- Select One --</option>
                            <?php if (!empty($crypto_btc ?? '')): ?><option value="btc">Bitcoin (BTC)</option><?php endif; ?>
                            <?php if (!empty($crypto_eth ?? '')): ?><option value="eth">Ethereum (ETH)</option><?php endif; ?>
                            <?php if (!empty($crypto_ltc ?? '')): ?><option value="ltc">Litecoin (LTC)</option><?php endif; ?>
                            <?php if (!empty($crypto_usdt ?? '')): ?><option value="usdt">Tether (USDT)</option><?php endif; ?>
                        </select>
                    </div>

                    <div id="cryptoAddressDisplay" class="mb-4 p-3 bg-light border rounded" style="display: none;">
                        <label for="cryptoAddressInput" class="form-label fw-bold text-dark">Payment Address</label>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-0 rounded-start" id="cryptoAddressInput" value="" readonly> 
                            <button type="button" class="btn btn-warning  copy-btn" data-copy-target="#cryptoAddressInput" title="Copy Address">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="form-text text-danger mt-1">ONLY send the selected currency to this address!</small>
                    </div>

                    <div class="mb-3">
                        <label for="cryptoAmount" class="form-label fw-bold">Amount Paid (in USD or Crypto equivalent)</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?></span>
                            <input type="text" class="form-control rounded-0 rounded-end border-0 border-top border-end border-bottom" id="cryptoAmount" name="amount" placeholder="e.g., $100.00 or 0.0025 BTC" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="cryptoProof" class="form-label fw-bold">Upload Payment Proof (Txn Screenshot/ID)</label>
                        <div class="file-upload-box position-relative">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                            <span class="file-upload-label d-block" id="cryptoProofFileName">Click or drag a file here</span>
                            <span class="file-upload-text-muted d-block">Proof must show Transaction ID (TXID) or hash.</span>
                            <input type="file" id="cryptoProof" name="payment_proof" accept="image/*, application/pdf" required>
                        </div>
                        <div class="invalid-feedback">Please upload your payment proof.</div>
                    </div>

                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #e63946, #c1121f); border: none; letter-spacing: 0.5px; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(193, 18, 31, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                        Submit Payment Proof
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($giftcard_enabled ?? true): ?>
<div class="modal fade" id="giftcardDetailsModal" tabindex="-1" aria-labelledby="giftcardDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(145deg, var(--primary-dark, #0f172a) 0%, #1a0000 100%); border-bottom: 3px solid var(--secondary, #FFC107);">
                <h5 class="modal-title text-white" id="giftcardDetailsLabel">Complete Gift Card Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="payment_method" value="giftcard">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id ?? ''); ?>">
                <input type="hidden" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number ?? ''); ?>">
                <div class="modal-body">
                    
                    <p class="text-danger fw-bold">Important Note:</p>
                    <p class="mb-4"><?php echo htmlspecialchars($giftcard_note ?? 'Please scratch off the silver strip and ensure the code is clearly visible.'); ?></p>

                    <div class="mb-3">
                        <label for="giftcardAmount" class="form-label fw-bold">Gift Card Value (required)</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?></span>
                            <input type="number" step="1" min="1" class="form-control rounded-0 rounded-end border-0 border-top border-end border-bottom" id="giftcardAmount" name="amount" placeholder="e.g., 50" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="giftcardProofFront" class="form-label fw-bold">Upload Gift Card FRONT Image</label>
                        <div class="file-upload-box gift-card-upload-box position-relative">
                            <i class="fas fa-camera fa-2x mb-2"></i>
                            <span class="file-upload-label d-block" id="giftcardProofFrontFileName">Click or drag a file here</span>
                            <span class="file-upload-text-muted d-block">Accepted: JPG, PNG. Max: 5MB.</span>
                            <input type="file" id="giftcardProofFront" name="proof_front" accept="image/*" required>
                        </div>
                        <div class="invalid-feedback">Please upload the front of the gift card.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="giftcardProofBack" class="form-label fw-bold">Upload Gift Card BACK Image (showing PIN/Code)</label>
                        <div class="file-upload-box gift-card-upload-box position-relative">
                            <i class="fas fa-camera fa-2x mb-2"></i>
                            <span class="file-upload-label d-block" id="giftcardProofBackFileName">Click or drag a file here</span>
                            <span class="file-upload-text-muted d-block">Accepted: JPG, PNG. Max: 5MB.</span>
                            <input type="file" id="giftcardProofBack" name="proof_back" accept="image/*" required>
                        </div>
                        <div class="invalid-feedback">Please upload the back of the gift card (showing code).</div>
                    </div>

                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #e63946, #c1121f); border: none; letter-spacing: 0.5px; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(193, 18, 31, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                        Submit Gift Card Proofs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
// --- END: MODAL HTML ---
?>


<script>
   

    async function copyToClipboard(button, textToCopy) {
        let success = false;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(textToCopy);
                success = true;
            } else {
                // Fallback for non-HTTPS local environments
                const textArea = document.createElement("textarea");
                textArea.value = textToCopy;
                textArea.style.position = "fixed";
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    success = document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: unable to copy', err);
                }
                document.body.removeChild(textArea);
            }
            
            if (success) {
                const icon = button.querySelector('i');
                const originalIconClass = icon.className;
                
                icon.className = 'fas fa-check text-success';
                button.style.borderColor = '#28a745';
                
                setTimeout(() => {
                    icon.className = originalIconClass;
                    button.style.borderColor = '#ccc';
                }, 1500);
            } else {
                throw new Error("Copy command unsuccessful");
            }
        } catch (err) {
            console.error('Failed to copy text: ', err);
            const icon = button.querySelector('i');
            icon.className = 'fas fa-times text-danger';
            button.style.borderColor = '#dc3545';
            
            setTimeout(() => {
                icon.className = 'fas fa-copy'; 
                button.style.borderColor = '#ccc';
            }, 1500);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        // --- 1. Copy Button Logic (Now handles Icons) ---
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                let finalCopyText = this.getAttribute('data-copy-text');

                const targetSelector = this.getAttribute('data-copy-target');
                if (targetSelector) {
                    const targetElement = document.querySelector(targetSelector);
                    if (targetElement && targetElement.value) {
                        finalCopyText = targetElement.value;
                    }
                }

                if (finalCopyText) {
                    copyToClipboard(this, finalCopyText);
                }
            });
        });


        // --- 2. Crypto Currency Selector Logic ---
        const cryptoSelector = document.getElementById('cryptoCurrency');
        const cryptoAddressInput = document.getElementById('cryptoAddressInput');
        const cryptoAddressDisplay = document.getElementById('cryptoAddressDisplay');

        const cryptoAddresses = {
            'btc': '<?php echo $crypto_btc ?? ''; ?>',
            'eth': '<?php echo $crypto_eth ?? ''; ?>',
            'ltc': '<?php echo $crypto_ltc ?? ''; ?>',
            'usdt': '<?php echo $crypto_usdt ?? ''; ?>'
        };

        if (cryptoSelector) {
            cryptoSelector.addEventListener('change', function() {
                const selectedCurrency = this.value;
                if (selectedCurrency && cryptoAddresses[selectedCurrency]) {
                    cryptoAddressInput.value = cryptoAddresses[selectedCurrency];
                    cryptoAddressDisplay.style.display = 'block';
                } else {
                    cryptoAddressInput.value = '';
                    cryptoAddressDisplay.style.display = 'none';
                }
            });
        }


        // --- 3. MODERN FILE INPUT NAME UPDATE FIX & PREVIEW ---
        const fileInputs = document.querySelectorAll('.file-upload-box input[type="file"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileNameSpanId = this.id + 'FileName';
                const fileNameSpan = document.getElementById(fileNameSpanId);
                const box = this.closest('.file-upload-box');
                
                // Clear any existing preview
                const existingPreview = box.querySelector('.img-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                if (this.files.length > 0) {
                    const file = this.files[0];
                    if (fileNameSpan) {
                        fileNameSpan.textContent = file.name;
                    }
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-preview mt-3 rounded shadow-sm';
                            img.style.maxHeight = '150px';
                            img.style.maxWidth = '100%';
                            img.style.objectFit = 'contain';
                            img.style.display = 'block';
                            img.style.margin = '0 auto';
                            box.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    }
                    
                    if (box) {
                        const originalBorderColor = box.classList.contains('gift-card-upload-box') ? '#dc3545' : '#007bff';
                        
                        box.style.borderColor = '#28a745';
                        setTimeout(() => {
                            box.style.borderColor = originalBorderColor;
                        }, 2000); 
                    }
                } else {
                    if (fileNameSpan) {
                        fileNameSpan.textContent = 'Click or drag a file here';
                    }
                }
            });
        });
    });
</script>