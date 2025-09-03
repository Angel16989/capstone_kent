<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$pageTitle = "Payment Successful";
$pageCSS = "/assets/css/checkout-success.css";

$invoice_no = $_GET['invoice'] ?? null;
$payment = null;

if ($invoice_no) {
    $stmt = $pdo->prepare("
        SELECT p.*, m.start_date, m.end_date, mp.name as plan_name, mp.duration_days, u.first_name 
        FROM payments p 
        JOIN memberships m ON p.membership_id = m.id 
        JOIN membership_plans mp ON m.plan_id = mp.id 
        JOIN users u ON p.member_id = u.id 
        WHERE p.invoice_no = ? AND p.member_id = ?
    ");
    $stmt->execute([$invoice_no, $_SESSION['user']['id']]);
    $payment = $stmt->fetch();
}

if (!$payment) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="success-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Header -->
                <div class="success-header text-center mb-5">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h1 class="success-title">Payment Successful!</h1>
                    <p class="success-subtitle">
                        Welcome to L9 Fitness, <?php echo htmlspecialchars($payment['first_name']); ?>! 
                        Your membership is now active.
                    </p>
                </div>

                <!-- Payment Details Card -->
                <div class="payment-details-card mb-4">
                    <div class="card-header">
                        <h3><i class="bi bi-receipt me-2"></i>Payment Receipt</h3>
                        <div class="invoice-number">Invoice #<?php echo htmlspecialchars($payment['invoice_no']); ?></div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label>Membership Plan:</label>
                                    <value><?php echo htmlspecialchars($payment['plan_name']); ?> Membership</value>
                                </div>
                                <div class="detail-group">
                                    <label>Duration:</label>
                                    <value><?php echo $payment['duration_days']; ?> days</value>
                                </div>
                                <div class="detail-group">
                                    <label>Start Date:</label>
                                    <value><?php echo date('M d, Y', strtotime($payment['start_date'])); ?></value>
                                </div>
                                <div class="detail-group">
                                    <label>End Date:</label>
                                    <value><?php echo date('M d, Y', strtotime($payment['end_date'])); ?></value>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label>Amount Paid:</label>
                                    <value class="amount">$<?php echo number_format($payment['amount'], 2); ?></value>
                                </div>
                                <div class="detail-group">
                                    <label>Payment Method:</label>
                                    <value><?php echo ucfirst($payment['method']); ?></value>
                                </div>
                                <div class="detail-group">
                                    <label>Transaction ID:</label>
                                    <value><?php echo htmlspecialchars($payment['txn_ref']); ?></value>
                                </div>
                                <div class="detail-group">
                                    <label>Payment Date:</label>
                                    <value><?php echo date('M d, Y g:i A', strtotime($payment['paid_at'])); ?></value>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What's Next Section -->
                <div class="whats-next-card mb-4">
                    <h4><i class="bi bi-rocket-takeoff me-2"></i>What's Next?</h4>
                    <div class="next-steps">
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="step-content">
                                <h6>Book Your First Class</h6>
                                <p>Browse our class schedule and reserve your spots in advance</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="step-content">
                                <h6>Complete Your Profile</h6>
                                <p>Add your fitness goals, preferences, and emergency contact</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="bi bi-door-open"></i>
                            </div>
                            <div class="step-content">
                                <h6>Visit the Gym</h6>
                                <p>Come in for a quick tour and get your access card</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons text-center">
                    <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Book Classes
                    </a>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-primary btn-lg me-3">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-person-circle me-2"></i>
                        Profile
                    </a>
                </div>

                <!-- Contact Support -->
                <div class="support-section text-center mt-5">
                    <p class="text-muted">
                        Need help? <a href="<?php echo BASE_URL; ?>contact.php">Contact our support team</a> 
                        or call us at <strong>(555) 123-4567</strong>
                    </p>
                </div>

                <!-- Print Receipt -->
                <div class="print-section text-center mt-3">
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-2"></i>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect to dashboard after 30 seconds
setTimeout(function() {
    const redirectUrl = '<?php echo BASE_URL; ?>dashboard.php';
    window.location.href = redirectUrl;
}, 30000);

// Confetti effect
function createConfetti() {
    const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#f0932b'];
    const confettiCount = 150;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'fixed';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDelay = Math.random() * 3 + 's';
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.width = (Math.random() * 10 + 5) + 'px';
        confetti.style.height = confetti.style.width;
        confetti.style.zIndex = '9999';
        confetti.style.borderRadius = '50%';
        confetti.style.animation = 'fall linear infinite';
        confetti.style.opacity = '0.8';
        
        document.body.appendChild(confetti);
        
        // Remove confetti after animation
        setTimeout(() => {
            if (confetti.parentNode) {
                confetti.parentNode.removeChild(confetti);
            }
        }, 5000);
    }
}

// Trigger confetti on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(createConfetti, 500);
});
</script>

<style>
@keyframes fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
    }
}

@media print {
    .action-buttons,
    .support-section,
    .print-section {
        display: none !important;
    }
    
    .success-container {
        padding: 0 !important;
    }
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
