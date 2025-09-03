<?php 
require_once __DIR__ . '/../config/config.php';
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
        if ($payment_method === 'card') {
            $card_number = trim($_POST['card_number'] ?? '');
            $card_name = trim($_POST['card_name'] ?? '');
            $card_expiry = trim($_POST['card_expiry'] ?? '');
            $card_cvv = trim($_POST['card_cvv'] ?? '');
        }

        // Basic validation
        if (empty($payment_method)) {
            $errors[] = 'Please select a payment method';
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
            $errors[] = 'ZIP code is required';
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

            // Create payment record
            $stmt = $pdo->prepare("INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at) VALUES (?, ?, ?, ?, 'paid', ?, ?, NOW())");
            $txn_ref = 'TXN-' . time() . '-' . $user_id;
            $stmt->execute([$user_id, $membership_id, $plan['price'], $payment_method, $txn_ref, $invoice_no]);

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
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked required>
                                <label class="form-check-label" for="card">
                                    <div class="payment-method-content">
                                        <i class="bi bi-credit-card me-2"></i>
                                        <span>Credit/Debit Card</span>
                                        <div class="card-logos">
                                            <img src="<?php echo BASE_URL; ?>assets/img/cards/visa.png" alt="Visa" class="card-logo">
                                            <img src="<?php echo BASE_URL; ?>assets/img/cards/mastercard.png" alt="Mastercard" class="card-logo">
                                            <img src="<?php echo BASE_URL; ?>assets/img/cards/amex.png" alt="American Express" class="card-logo">
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <div class="payment-method-content">
                                        <i class="bi bi-paypal me-2"></i>
                                        <span>PayPal</span>
                                        <small class="text-muted">Pay with your PayPal account</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div id="cardDetails" class="card-details">
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control form-control-lg" id="card_number" name="card_number" 
                                           placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number">
                                </div>
                                <div class="col-12">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control form-control-lg" id="card_name" name="card_name" 
                                           placeholder="John Doe" autocomplete="cc-name">
                                </div>
                                <div class="col-md-6">
                                    <label for="card_expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control form-control-lg" id="card_expiry" name="card_expiry" 
                                           placeholder="MM/YY" maxlength="5" autocomplete="cc-exp">
                                </div>
                                <div class="col-md-6">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control form-control-lg" id="card_cvv" name="card_cvv" 
                                           placeholder="123" maxlength="4" autocomplete="cc-csc">
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
                                       placeholder="New York" required>
                            </div>
                            <div class="col-md-4">
                                <label for="billing_state" class="form-label">State</label>
                                <select class="form-control form-control-lg" id="billing_state" name="billing_state">
                                    <option value="">Select State</option>
                                    <option value="AL">Alabama</option>
                                    <option value="CA">California</option>
                                    <option value="FL">Florida</option>
                                    <option value="NY">New York</option>
                                    <option value="TX">Texas</option>
                                    <!-- Add more states as needed -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="billing_zip" class="form-label">ZIP Code *</label>
                                <input type="text" class="form-control form-control-lg" id="billing_zip" name="billing_zip" 
                                       placeholder="10001" required>
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
                            <span class="btn-text">Complete Purchase - $<?php echo number_format($plan['price'], 2); ?></span>
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
                            $<?php echo number_format($plan['price'], 2); ?>
                        </div>
                    </div>

                    <hr>

                    <div class="pricing-breakdown">
                        <div class="pricing-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($plan['price'], 2); ?></span>
                        </div>
                        <div class="pricing-row">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="pricing-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($plan['price'], 2); ?></span>
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
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'card') {
                cardDetails.style.display = 'block';
                cardDetails.querySelectorAll('input').forEach(input => {
                    input.setAttribute('required', '');
                });
            } else {
                cardDetails.style.display = 'none';
                cardDetails.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    });

    // Card number formatting
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }
        });
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

    // Form submission
    const form = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            submitBtn.disabled = true;
            submitBtn.querySelector('.btn-text').textContent = 'Processing Payment...';
            submitBtn.querySelector('.spinner-border').classList.remove('d-none');
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
