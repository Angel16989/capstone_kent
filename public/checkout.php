<?php
/**
 * Enhanced Checkout Page with PayPal Integration
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/paypal_config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: ' . BASE_URL . 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$pageTitle = "Checkout";
$pageCSS = "/assets/css/checkout.css";

$errors = [];
$success = '';

// Get plan details from session or URL
$plan_id = $_GET['plan_id'] ?? $_SESSION['checkout_plan_id'] ?? null;
$plan = null;

if ($plan_id) {
    $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ? AND is_active = 1");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    
    if ($plan) {
        $_SESSION['checkout_plan_id'] = $plan_id;
    }
}

if (!$plan) {
    header('Location: ' . BASE_URL . 'memberships.php');
    exit;
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $user_id = $_SESSION['user']['id'];
        $payment_method = trim($_POST['payment_method'] ?? '');
        $billing_name = trim($_POST['billing_name'] ?? '');
        $billing_email = trim($_POST['billing_email'] ?? '');
        $billing_phone = trim($_POST['billing_phone'] ?? '');
        $billing_address = trim($_POST['billing_address'] ?? '');
        $billing_city = trim($_POST['billing_city'] ?? '');
        $billing_state = trim($_POST['billing_state'] ?? '');
        $billing_zip = trim($_POST['billing_zip'] ?? '');
        
        // Payment details based on method
        $card_number = $card_name = $card_expiry = $card_cvv = '';
        if ($payment_method === 'card') {
            $card_number = trim($_POST['card_number'] ?? '');
            $card_name = trim($_POST['card_name'] ?? '');
            $card_expiry = trim($_POST['card_expiry'] ?? '');
            $card_cvv = trim($_POST['card_cvv'] ?? '');
        }

        // Basic validation
        $valid_payment_methods = ['card', 'paypal', 'apple_pay', 'google_pay', 'afterpay', 'klarna', 'venmo', 'bank_transfer'];
        if (empty($payment_method) || !in_array($payment_method, $valid_payment_methods)) {
            $errors[] = 'Please select a valid payment method';
        }
        if (empty($billing_name)) {
            $errors[] = 'Billing name is required';
        }
        if (empty($billing_email) || !filter_var($billing_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid billing email is required';
        }
        if (empty($billing_address)) {
            $errors[] = 'Billing address is required';
        }
        if (empty($billing_city)) {
            $errors[] = 'City is required';
        }
        if (empty($billing_zip)) {
            $errors[] = 'Postcode is required';
        } elseif (!preg_match('/^\d{4}$/', $billing_zip)) {
            $errors[] = 'Valid Australian postcode (4 digits) is required';
        }

        // Card validation if card payment
        if ($payment_method === 'card') {
            if (empty($card_number) || !preg_match('/^\d{13,19}$/', str_replace([' ', '-'], '', $card_number))) {
                $errors[] = 'Valid card number is required';
            }
            if (empty($card_name)) {
                $errors[] = 'Name on card is required';
            }
            if (empty($card_expiry) || !preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
                $errors[] = 'Valid expiry date (MM/YY) is required';
            }
            if (empty($card_cvv) || !preg_match('/^\d{3,4}$/', $card_cvv)) {
                $errors[] = 'Valid CVV is required';
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // Check if user has active membership
            $stmt = $pdo->prepare("SELECT * FROM memberships WHERE member_id = ? AND status = 'active' AND end_date > NOW()");
            $stmt->execute([$user_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Extend existing membership
                $start_date = max(new DateTime(), new DateTime($existing['end_date']));
            } else {
                $start_date = new DateTime();
            }
            
            $end_date = clone $start_date;
            $end_date->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
            
            // Create or update membership
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE memberships SET plan_id = ?, end_date = ?, total_fee = ?, status = 'active' WHERE member_id = ? AND status = 'active'");
                $stmt->execute([$plan_id, $end_date->format('Y-m-d H:i:s'), $plan['price'], $user_id]);
                $membership_id = $existing['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, total_fee, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$user_id, $plan_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s'), $plan['price']]);
                $membership_id = $pdo->lastInsertId();
            }

            // Generate invoice number
            $invoice_no = 'INV-' . date('Y') . '-' . str_pad($membership_id, 6, '0', STR_PAD_LEFT);

            // Create payment record with appropriate status
            $payment_status = in_array($payment_method, ['bank_transfer']) ? 'pending' : 'paid';
            $stmt = $pdo->prepare("INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $txn_ref = strtoupper($payment_method) . '-' . time() . '-' . $user_id;
            $stmt->execute([$user_id, $membership_id, $plan['price'], $payment_method, $payment_status, $txn_ref, $invoice_no]);

            // Update user billing info
            $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
            $full_address = $billing_address . ', ' . $billing_city . ', ' . $billing_state . ' ' . $billing_zip;
            $stmt->execute([$billing_phone, $full_address, $user_id]);

            $pdo->commit();

            // Clear checkout session
            unset($_SESSION['checkout_plan_id']);

            // Redirect to success page
            header('Location: ' . BASE_URL . 'checkout-success.php?invoice=' . $invoice_no);
            exit;
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        $errors[] = 'Payment processing failed. Please try again.';
    }
}

// Get user info for pre-filling
$user = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$user_details = $stmt->fetch();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="checkout-container">
    <div class="container py-5">
        <div class="row">
            <!-- Progress Steps -->
            <div class="col-12 mb-4">
                <div class="checkout-progress">
                    <div class="step completed">
                        <div class="step-number">1</div>
                        <div class="step-text">Select Plan</div>
                    </div>
                    <div class="step-line completed"></div>
                    <div class="step active">
                        <div class="step-number">2</div>
                        <div class="step-text">Payment</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-text">Complete</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <div class="checkout-form-card">
                    <h2 class="mb-4">
                        <i class="bi bi-credit-card-2-front me-2"></i>
                        Complete Your Purchase
                    </h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="checkoutForm" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <!-- Payment Method -->
                        <div class="section-header">
                            <h4><i class="bi bi-wallet2 me-2"></i>Payment Method</h4>
                        </div>

                        <div class="payment-methods mb-4">
                            <!-- Credit/Debit Cards -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked required>
                                <label class="form-check-label" for="card">
                                    <div class="payment-method-content">
                                        <i class="bi bi-credit-card me-2"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Credit/Debit Card</span>
                                            <small class="text-muted d-block">Secure payment with your card</small>
                                        </div>
                                        <div class="card-logos">
                                            <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" class="card-logo" title="Visa">
                                            <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard" class="card-logo" title="Mastercard">
                                            <img src="https://img.icons8.com/color/48/amex.png" alt="American Express" class="card-logo" title="American Express">
                                            <img src="https://img.icons8.com/color/48/discover.png" alt="Discover" class="card-logo" title="Discover">
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- PayPal -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <div class="payment-method-content">
                                        <i class="bi bi-paypal me-2" style="color: #0070ba;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">PayPal</span>
                                            <small class="text-muted d-block">Pay with your PayPal balance or linked cards</small>
                                        </div>
                                        <div class="paypal-logo">
                                            <img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" alt="PayPal" style="height: 24px;">
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Apple Pay -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="apple_pay" value="apple_pay">
                                <label class="form-check-label" for="apple_pay">
                                    <div class="payment-method-content">
                                        <i class="bi bi-apple me-2" style="color: #000000;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Apple Pay</span>
                                            <small class="text-muted d-block">Touch ID or Face ID required</small>
                                        </div>
                                        <div class="apple-pay-logo">
                                            <svg width="40" height="16" viewBox="0 0 40 16">
                                                <path d="M6.4 1.8c-.4 0-.8.1-1.1.3-.3.2-.5.5-.5.9 0 .3.1.6.3.8.2.2.5.3.8.3.4 0 .7-.1 1-.3.3-.2.4-.5.4-.8 0-.4-.1-.7-.4-.9-.2-.2-.5-.3-.8-.3zm-.1 2.4c-.6 0-1.1-.3-1.1-.9 0-.6.5-.9 1.1-.9.6 0 1.1.3 1.1.9 0 .6-.5.9-1.1.9zM2.2 2.1c-.3 0-.6.1-.8.3-.2.2-.3.5-.3.8 0 .3.1.6.3.8.2.2.5.3.8.3.3 0 .6-.1.8-.3.2-.2.3-.5.3-.8 0-.3-.1-.6-.3-.8-.2-.2-.5-.3-.8-.3zm0 1.6c-.4 0-.7-.3-.7-.7 0-.4.3-.7.7-.7.4 0 .7.3.7.7 0 .4-.3.7-.7.7z" fill="#000"/>
                                                <text x="10" y="11" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="8" fill="#000">Pay</text>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Google Pay -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="google_pay" value="google_pay">
                                <label class="form-check-label" for="google_pay">
                                    <div class="payment-method-content">
                                        <i class="bi bi-google me-2" style="color: #4285f4;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Google Pay</span>
                                            <small class="text-muted d-block">Quick checkout with Google</small>
                                        </div>
                                        <div class="google-pay-logo">
                                            <svg width="40" height="16" viewBox="0 0 40 16">
                                                <path d="M19.526 2.635c0-1.378-.027-2.635-.052-3.697H15.02l-.039 2.197h-.08c-.52-.729-1.676-2.197-3.723-2.197-2.798 0-4.953 2.381-4.953 5.879 0 3.457 2.092 5.721 4.901 5.721 1.676 0 2.798-.729 3.276-1.635h.066v1.197c0 2.172-.976 3.408-2.774 3.408-1.456 0-2.381-.976-2.798-1.935l-2.459 1.027c.728 1.768 2.565 3.905 5.296 3.905 3.062 0 5.296-1.768 5.296-6.07V2.635h-.014zm-2.774 8.65c-1.456 0-2.512-1.197-2.512-2.931 0-1.735 1.056-2.957 2.512-2.957 1.443 0 2.512 1.222 2.512 2.957 0 1.734-1.069 2.931-2.512 2.931z" fill="#4285f4"/>
                                                <path d="M29.692 7.826c-1.456 0-2.947.625-3.566 1.768l2.197.911c.378-.521.976-.729 1.456-.729.833 0 1.508.521 1.534 1.378v.105c-.26-.157-.833-.391-1.534-.391-1.404 0-2.825.781-2.825 2.238 0 1.378 1.196 2.264 2.577 2.264 1.04 0 1.612-.456 1.976-1.014h.065v.808h2.485V9.48c0-2.33-1.742-3.654-4.365-3.654zm-.183 7.217c-.482 0-1.157-.235-1.157-.807 0-.729.807-.976 1.508-.976.625 0 .911.131 1.313.313-.105.859-.846 1.47-1.664 1.47z" fill="#ea4335"/>
                                                <path d="M40 6.043L36.426 15.89h-2.668l1.456-3.566-3.605-8.281h2.798l2.238 5.774h.04l2.159-5.774H40z" fill="#fbbc05"/>
                                                <path d="M6.738 15.89h2.668V6.043H6.738v9.847z" fill="#34a853"/>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Afterpay -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="afterpay" value="afterpay">
                                <label class="form-check-label" for="afterpay">
                                    <div class="payment-method-content">
                                        <i class="bi bi-calendar-event me-2" style="color: #b2fce4;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Afterpay</span>
                                            <small class="text-muted d-block">Buy now, pay in 4 installments</small>
                                        </div>
                                        <div class="afterpay-logo">
                                            <svg width="60" height="16" viewBox="0 0 60 16">
                                                <text x="0" y="12" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#b2fce4">afterpay</text>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Klarna -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="klarna" value="klarna">
                                <label class="form-check-label" for="klarna">
                                    <div class="payment-method-content">
                                        <i class="bi bi-credit-card-2-front me-2" style="color: #ffb3c7;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Klarna</span>
                                            <small class="text-muted d-block">Pay in 30 days or split in 4</small>
                                        </div>
                                        <div class="klarna-logo">
                                            <svg width="50" height="16" viewBox="0 0 50 16">
                                                <text x="0" y="12" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#ffb3c7">Klarna</text>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Venmo -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="venmo" value="venmo">
                                <label class="form-check-label" for="venmo">
                                    <div class="payment-method-content">
                                        <i class="bi bi-phone me-2" style="color: #3d95ce;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Venmo</span>
                                            <small class="text-muted d-block">Pay with your Venmo account</small>
                                        </div>
                                        <div class="venmo-logo">
                                            <svg width="45" height="16" viewBox="0 0 45 16">
                                                <text x="0" y="12" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#3d95ce">Venmo</text>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Bank Transfer -->
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    <div class="payment-method-content">
                                        <i class="bi bi-bank me-2" style="color: #28a745;"></i>
                                        <div class="payment-info">
                                            <span class="payment-title">Bank Transfer</span>
                                            <small class="text-muted d-block">Direct transfer from your bank</small>
                                        </div>
                                        <div class="bank-logo">
                                            <i class="bi bi-shield-check" style="color: #28a745; font-size: 20px;"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div id="cardDetails" class="payment-details-section">
                            <div class="card-details-header">
                                <h5><i class="bi bi-credit-card me-2"></i>Card Information</h5>
                                <div class="security-badges">
                                    <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>SSL Encrypted</span>
                                    <span class="badge bg-primary"><i class="bi bi-lock me-1"></i>PCI Compliant</span>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="card_number" class="form-label">Card Number *</label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control" id="card_number" name="card_number" 
                                               placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number">
                                        <span class="input-group-text" id="card-type-indicator">
                                            <i class="bi bi-credit-card text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="card_name" class="form-label">Name on Card *</label>
                                    <input type="text" class="form-control form-control-lg" id="card_name" name="card_name" 
                                           placeholder="John Doe" autocomplete="cc-name">
                                </div>
                                <div class="col-md-6">
                                    <label for="card_expiry" class="form-label">Expiry Date *</label>
                                    <input type="text" class="form-control form-control-lg" id="card_expiry" name="card_expiry" 
                                           placeholder="MM/YY" maxlength="5" autocomplete="cc-exp">
                                </div>
                                <div class="col-md-6">
                                    <label for="card_cvv" class="form-label">CVV *</label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                               placeholder="123" maxlength="4" autocomplete="cc-csc">
                                        <span class="input-group-text" title="3-4 digit security code">
                                            <i class="bi bi-question-circle"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Afterpay Details -->
                        <div id="afterpayDetails" class="payment-details-section" style="display: none;">
                            <div class="afterpay-info">
                                <h5><i class="bi bi-calendar-event me-2"></i>Afterpay Payment Plan</h5>
                                <div class="installment-breakdown">
                                    <div class="installment-item">
                                        <span>Today:</span>
                                        <span class="amount">$<?php echo number_format((float)$plan['price'] / 4, 2); ?></span>
                                    </div>
                                    <div class="installment-item">
                                        <span>2 weeks:</span>
                                        <span class="amount">$<?php echo number_format((float)$plan['price'] / 4, 2); ?></span>
                                    </div>
                                    <div class="installment-item">
                                        <span>4 weeks:</span>
                                        <span class="amount">$<?php echo number_format((float)$plan['price'] / 4, 2); ?></span>
                                    </div>
                                    <div class="installment-item">
                                        <span>6 weeks:</span>
                                        <span class="amount">$<?php echo number_format((float)$plan['price'] / 4, 2); ?></span>
                                    </div>
                                </div>
                                <p class="text-muted small mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    No interest, no additional fees if you pay on time.
                                </p>
                            </div>
                        </div>

                        <!-- Bank Transfer Details -->
                        <div id="bankTransferDetails" class="payment-details-section" style="display: none;">
                            <div class="bank-transfer-info">
                                <h5><i class="bi bi-bank me-2"></i>Bank Transfer Instructions</h5>
                                <div class="alert alert-info">
                                    <p class="mb-2"><strong>Account Details:</strong></p>
                                    <p class="mb-1"><strong>Account Name:</strong> L9 Fitness Gym LLC</p>
                                    <p class="mb-1"><strong>Account Number:</strong> 1234567890</p>
                                    <p class="mb-1"><strong>Routing Number:</strong> 123456789</p>
                                    <p class="mb-1"><strong>Reference:</strong> Your email address</p>
                                    <p class="mb-0 small text-muted mt-2">
                                        <i class="bi bi-clock me-1"></i>
                                        Bank transfers may take 1-3 business days to process.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information -->
                        <div class="section-header">
                            <h4><i class="bi bi-person-lines-fill me-2"></i>Billing Information</h4>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="billing_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control form-control-lg" id="billing_name" name="billing_name" 
                                       value="<?php echo htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="billing_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control form-control-lg" id="billing_email" name="billing_email" 
                                       value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="billing_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control form-control-lg" id="billing_phone" name="billing_phone" 
                                       value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>" placeholder="+1 (555) 123-4567">
                            </div>
                            <div class="col-md-6">
                                <label for="billing_address" class="form-label">Street Address *</label>
                                <input type="text" class="form-control form-control-lg" id="billing_address" name="billing_address" 
                                       placeholder="123 Main Street" required>
                            </div>
                            <div class="col-md-4">
                                <label for="billing_city" class="form-label">City *</label>
                                <input type="text" class="form-control form-control-lg" id="billing_city" name="billing_city" 
                                       placeholder="Sydney" required>
                            </div>
                            <div class="col-md-4">
                                <label for="billing_state" class="form-label">State/Territory</label>
                                <select class="form-control form-control-lg australian-form" id="billing_state" name="billing_state">
                                    <option value="">Select State/Territory</option>
                                    <option value="AU-NSW">New South Wales</option>
                                    <option value="AU-VIC">Victoria</option>
                                    <option value="AU-QLD">Queensland</option>
                                    <option value="AU-WA">Western Australia</option>
                                    <option value="AU-SA">South Australia</option>
                                    <option value="AU-TAS">Tasmania</option>
                                    <option value="AU-ACT">Australian Capital Territory</option>
                                    <option value="AU-NT">Northern Territory</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="billing_zip" class="form-label">Postcode *</label>
                                <input type="text" class="form-control form-control-lg" id="billing_zip" name="billing_zip" 
                                       placeholder="2000" pattern="[0-9]{4}" maxlength="4" required>
                            </div>
                        </div>

                        <!-- Terms Agreement -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="<?php echo BASE_URL; ?>terms.php" target="_blank">Terms of Service</a> 
                                and <a href="<?php echo BASE_URL; ?>privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                            <i class="bi bi-lock-fill me-2"></i>
                            <span class="btn-text">Complete Purchase - $<?php echo number_format((float)$plan['price'], 2); ?></span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>

                        <div class="security-info text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                Your payment information is encrypted and secure
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary-card">
                    <h4 class="mb-4">
                        <i class="bi bi-receipt me-2"></i>
                        Order Summary
                    </h4>

                    <div class="plan-summary">
                        <div class="plan-info">
                            <div class="plan-name"><?php echo htmlspecialchars($plan['name']); ?> Membership</div>
                            <div class="plan-description"><?php echo htmlspecialchars($plan['description']); ?></div>
                            <div class="plan-duration"><?php echo $plan['duration_days']; ?> days access</div>
                        </div>
                        
                        <div class="plan-price">
                            $<?php echo number_format((float)$plan['price'], 2); ?>
                        </div>
                    </div>

                    <hr>

                    <div class="pricing-breakdown">
                        <div class="pricing-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format((float)$plan['price'], 2); ?></span>
                        </div>
                        <div class="pricing-row">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="pricing-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format((float)$plan['price'], 2); ?></span>
                        </div>
                    </div>

                    <div class="benefits-list">
                        <h6 class="mb-3">What's Included:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Full gym access</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>All group classes</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Locker room access</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Member support</li>
                            <?php if ($plan['name'] !== 'Monthly'): ?>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Premium equipment</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Guest passes</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="payment-security-info mt-4">
                        <h6 class="mb-3">🔒 Secure Payment Options:</h6>
                        <div class="security-features">
                            <div class="security-item">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                <span>256-bit SSL encryption</span>
                            </div>
                            <div class="security-item">
                                <i class="bi bi-credit-card text-primary me-2"></i>
                                <span>PCI DSS compliant</span>
                            </div>
                            <div class="security-item">
                                <i class="bi bi-lock text-warning me-2"></i>
                                <span>No stored card data</span>
                            </div>
                            <div class="security-item">
                                <i class="bi bi-arrow-repeat text-info me-2"></i>
                                <span>30-day money back</span>
                            </div>
                        </div>
                    </div>

                    <div class="guarantee-badge">
                        <i class="bi bi-shield-check me-2"></i>
                        30-Day Money-Back Guarantee
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method switching
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    const afterpayDetails = document.getElementById('afterpayDetails');
    const bankTransferDetails = document.getElementById('bankTransferDetails');
    
    function hideAllPaymentDetails() {
        const allDetails = [cardDetails, afterpayDetails, bankTransferDetails];
        allDetails.forEach(detail => {
            if (detail) {
                detail.style.display = 'none';
                detail.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    }
    
    function showPaymentDetails(method) {
        hideAllPaymentDetails();
        
        switch(method) {
            case 'card':
                if (cardDetails) {
                    cardDetails.style.display = 'block';
                    cardDetails.querySelectorAll('input[required]').forEach(input => {
                        input.setAttribute('required', '');
                    });
                }
                break;
            case 'afterpay':
                if (afterpayDetails) {
                    afterpayDetails.style.display = 'block';
                }
                break;
            case 'bank_transfer':
                if (bankTransferDetails) {
                    bankTransferDetails.style.display = 'block';
                }
                break;
            case 'paypal':
            case 'apple_pay':
            case 'google_pay':
            case 'klarna':
            case 'venmo':
                // These will redirect to external payment processors
                break;
        }
    }
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            showPaymentDetails(this.value);
            updateSubmitButton(this.value);
        });
    });
    
    // Initialize with default selection
    const defaultMethod = document.querySelector('input[name="payment_method"]:checked');
    if (defaultMethod) {
        showPaymentDetails(defaultMethod.value);
    }
    
    function updateSubmitButton(method) {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        
        switch(method) {
            case 'card':
                btnText.textContent = `Complete Purchase - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'paypal':
                btnText.textContent = `Continue to PayPal - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'apple_pay':
                btnText.textContent = `Pay with Apple Pay - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'google_pay':
                btnText.textContent = `Pay with Google Pay - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'afterpay':
                btnText.textContent = `Continue to Afterpay - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'klarna':
                btnText.textContent = `Continue to Klarna - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'venmo':
                btnText.textContent = `Pay with Venmo - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            case 'bank_transfer':
                btnText.textContent = `Confirm Bank Transfer - $<?php echo number_format((float)$plan['price'], 2); ?>`;
                break;
            default:
                btnText.textContent = `Complete Purchase - $<?php echo number_format((float)$plan['price'], 2); ?>`;
        }
    }

    // Card number formatting and type detection
    const cardNumberInput = document.getElementById('card_number');
    const cardTypeIndicator = document.getElementById('card-type-indicator');
    
    if (cardNumberInput && cardTypeIndicator) {
        function detectCardType(number) {
            const patterns = {
                visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
                mastercard: /^5[1-5][0-9]{14}$/,
                amex: /^3[47][0-9]{13}$/,
                discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
                diners: /^3[0689][0-9]{11}$/,
                jcb: /^(?:2131|1800|35\d{3})\d{11}$/
            };
            
            const cleanNumber = number.replace(/\s+/g, '');
            
            for (let [type, pattern] of Object.entries(patterns)) {
                if (pattern.test(cleanNumber) || cleanNumber.match(new RegExp('^' + pattern.source.substring(1, 2)))) {
                    return type;
                }
            }
            return 'unknown';
        }
        
        function updateCardTypeIndicator(type) {
            const icons = {
                visa: '<img src="https://img.icons8.com/color/24/visa.png" alt="Visa" title="Visa">',
                mastercard: '<img src="https://img.icons8.com/color/24/mastercard.png" alt="Mastercard" title="Mastercard">',
                amex: '<img src="https://img.icons8.com/color/24/amex.png" alt="American Express" title="American Express">',
                discover: '<img src="https://img.icons8.com/color/24/discover.png" alt="Discover" title="Discover">',
                diners: '<i class="bi bi-credit-card text-info" title="Diners Club"></i>',
                jcb: '<i class="bi bi-credit-card text-warning" title="JCB"></i>',
                unknown: '<i class="bi bi-credit-card text-muted"></i>'
            };
            
            cardTypeIndicator.innerHTML = icons[type] || icons.unknown;
        }
        
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
                
                // Detect and show card type
                const cardType = detectCardType(value);
                updateCardTypeIndicator(cardType);
                
                // Update maxlength based on card type
                if (cardType === 'amex') {
                    e.target.maxLength = 17; // 15 digits + 2 spaces
                    document.getElementById('card_cvv').maxLength = 4;
                    document.getElementById('card_cvv').placeholder = '1234';
                } else {
                    e.target.maxLength = 19; // 16 digits + 3 spaces
                    document.getElementById('card_cvv').maxLength = 3;
                    document.getElementById('card_cvv').placeholder = '123';
                }
            }
        });
        
        // Initialize card type indicator
        updateCardTypeIndicator('unknown');
    }

    // Expiry date formatting
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }

    // CVV formatting
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // Phone formatting
    const phoneInput = document.getElementById('billing_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `+1 (${value}`;
                } else if (value.length <= 6) {
                    value = `+1 (${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `+1 (${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            e.target.value = value;
        });
    }

    // Form submission with payment method handling
    const form = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Handle external payment methods
        if (['apple_pay', 'google_pay'].includes(selectedMethod)) {
            e.preventDefault();
            handleDigitalWalletPayment(selectedMethod);
            return;
        }
        
        if (selectedMethod === 'paypal') {
            e.preventDefault();
            handlePayPalPayment();
            return;
        }
        
        if (['afterpay', 'klarna', 'venmo'].includes(selectedMethod)) {
            e.preventDefault();
            handleThirdPartyPayment(selectedMethod);
            return;
        }
        
        // Regular form validation for card and bank transfer
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            submitBtn.disabled = true;
            const processingText = selectedMethod === 'bank_transfer' ? 'Confirming Transfer...' : 'Processing Payment...';
            submitBtn.querySelector('.btn-text').textContent = processingText;
            submitBtn.querySelector('.spinner-border').classList.remove('d-none');
        }
        form.classList.add('was-validated');
    });
    
    function handleDigitalWalletPayment(method) {
        showMessage(`${method.replace('_', ' ')} integration coming soon! Please use card or PayPal.`, 'info');
    }
    
    function handlePayPalPayment() {
        // Create PayPal order via API
        fetch('<?php echo BASE_URL; ?>api/paypal_checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_order',
                plan_id: <?php echo $plan['id']; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.approval_url) {
                window.location.href = data.approval_url;
            } else {
                showMessage('PayPal setup error. Please try another payment method.', 'error');
            }
        })
        .catch(error => {
            console.error('PayPal Error:', error);
            showMessage('PayPal connection error. Please try again.', 'error');
        });
    }
    
    function handleThirdPartyPayment(method) {
        const methodNames = {
            afterpay: 'Afterpay',
            klarna: 'Klarna',
            venmo: 'Venmo'
        };
        showMessage(`${methodNames[method]} integration coming soon! Please use card or PayPal.`, 'info');
    }
    
    function showMessage(message, type) {
        // Remove existing messages
        document.querySelectorAll('.payment-message').forEach(msg => msg.remove());
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} payment-message position-fixed`;
        messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 350px;';
        messageDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'} me-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(messageDiv);
        
        // Auto-hide after 4 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 4000);
    }
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
