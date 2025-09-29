<?php
/**
 * My Bookings Page
 * Shows user's class booking status: confirmed, rejected, pending
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_login();

$pageTitle = "My Bookings";
$pageCSS = "assets/css/dashboard.css";

$user = current_user();

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    try {
        $booking_id = (int)$_POST['booking_id'];
        
        // Verify the booking belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND member_id = ? AND status IN ('pending', 'confirmed')");
        $stmt->execute([$booking_id, $user['id']]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking_id]);
            $success_message = "Booking cancelled successfully!";
        } else {
            $error_message = "Invalid booking or booking cannot be cancelled.";
        }
    } catch (Exception $e) {
        $error_message = "Error cancelling booking: " . $e->getMessage();
    }
}

// Get user's bookings with class details
$stmt = $pdo->prepare("
    SELECT b.*, c.name as class_name, c.description as class_description, 
           c.date, c.time, c.duration, c.instructor, c.capacity,
           u_conf.first_name as confirmed_by_name, u_conf.last_name as confirmed_by_lastname,
           u_rej.first_name as rejected_by_name, u_rej.last_name as rejected_by_lastname,
           (SELECT COUNT(*) FROM bookings b2 WHERE b2.class_id = c.id AND b2.status = 'confirmed') as confirmed_spots
    FROM bookings b
    JOIN classes c ON b.class_id = c.id
    LEFT JOIN users u_conf ON b.confirmed_by = u_conf.id
    LEFT JOIN users u_rej ON b.rejected_by = u_rej.id
    WHERE b.member_id = ?
    ORDER BY c.date DESC, c.time DESC, b.booked_at DESC
");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group bookings by status
$grouped_bookings = [
    'pending' => [],
    'confirmed' => [],
    'rejected' => [],
    'cancelled' => [],
    'waitlist' => [],
    'attended' => [],
    'no_show' => []
];

foreach ($bookings as $booking) {
    $grouped_bookings[$booking['status']][] = $booking;
}

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="dashboard-hero">
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="display-6 fw-bold mb-2">
                            <i class="fas fa-calendar-check text-danger"></i>
                            My Class Bookings
                        </h1>
                        <p class="text-muted">Track your class reservations and their status</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Book New Class
                    </a>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <?php if (empty($bookings)): ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3 text-muted">No Bookings Yet</h3>
            <p class="text-muted">You haven't booked any classes yet.</p>
            <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Browse Classes
            </a>
        </div>
    <?php else: ?>
        <!-- Booking Status Tabs -->
        <ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    <i class="fas fa-clock text-warning"></i> Pending 
                    <?php if (count($grouped_bookings['pending']) > 0): ?>
                        <span class="badge bg-warning text-dark"><?php echo count($grouped_bookings['pending']); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirmed" type="button" role="tab">
                    <i class="fas fa-check-circle text-success"></i> Confirmed
                    <?php if (count($grouped_bookings['confirmed']) > 0): ?>
                        <span class="badge bg-success"><?php echo count($grouped_bookings['confirmed']); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    <i class="fas fa-times-circle text-danger"></i> Rejected
                    <?php if (count($grouped_bookings['rejected']) > 0): ?>
                        <span class="badge bg-danger"><?php echo count($grouped_bookings['rejected']); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                    <i class="fas fa-history text-info"></i> History
                    <?php 
                    $history_count = count($grouped_bookings['attended']) + count($grouped_bookings['no_show']) + count($grouped_bookings['cancelled']);
                    if ($history_count > 0): ?>
                        <span class="badge bg-info"><?php echo $history_count; ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="bookingTabContent">
            <!-- Pending Bookings -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <?php if (empty($grouped_bookings['pending'])): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clock text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-muted">No Pending Bookings</h4>
                        <p class="text-muted">All your bookings have been processed.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($grouped_bookings['pending'] as $booking): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <?php echo renderBookingCard($booking, 'pending'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Confirmed Bookings -->
            <div class="tab-pane fade" id="confirmed" role="tabpanel">
                <?php if (empty($grouped_bookings['confirmed'])): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-muted">No Confirmed Bookings</h4>
                        <p class="text-muted">Book a class to see confirmed reservations here.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($grouped_bookings['confirmed'] as $booking): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <?php echo renderBookingCard($booking, 'confirmed'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rejected Bookings -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <?php if (empty($grouped_bookings['rejected'])): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-muted">No Rejected Bookings</h4>
                        <p class="text-muted">Great! None of your bookings have been rejected.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($grouped_bookings['rejected'] as $booking): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <?php echo renderBookingCard($booking, 'rejected'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- History -->
            <div class="tab-pane fade" id="history" role="tabpanel">
                <?php 
                $history_bookings = array_merge(
                    $grouped_bookings['attended'], 
                    $grouped_bookings['no_show'], 
                    $grouped_bookings['cancelled']
                );
                if (empty($history_bookings)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-muted">No History Yet</h4>
                        <p class="text-muted">Your completed and cancelled bookings will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($history_bookings as $booking): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <?php echo renderBookingCard($booking, 'history'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
function renderBookingCard($booking, $tab_type) {
    $status_config = [
        'pending' => ['icon' => 'clock', 'color' => 'warning', 'bg' => 'warning'],
        'confirmed' => ['icon' => 'check-circle', 'color' => 'success', 'bg' => 'success'],
        'rejected' => ['icon' => 'times-circle', 'color' => 'danger', 'bg' => 'danger'],
        'cancelled' => ['icon' => 'ban', 'color' => 'secondary', 'bg' => 'secondary'],
        'waitlist' => ['icon' => 'list', 'color' => 'info', 'bg' => 'info'],
        'attended' => ['icon' => 'user-check', 'color' => 'success', 'bg' => 'success'],
        'no_show' => ['icon' => 'user-times', 'color' => 'danger', 'bg' => 'danger']
    ];
    
    $config = $status_config[$booking['status']] ?? $status_config['pending'];
    $class_date = new DateTime($booking['date']);
    $is_past = $class_date < new DateTime();
    
    ob_start();
    ?>
    <div class="card booking-card border-<?php echo $config['color']; ?>">
        <div class="card-header bg-<?php echo $config['bg']; ?> bg-opacity-10 border-<?php echo $config['color']; ?>">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-<?php echo $config['color']; ?>">
                    <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                    <?php echo ucfirst($booking['status']); ?>
                </h6>
                <small class="text-muted">
                    Booked: <?php echo date('M j', strtotime($booking['booked_at'])); ?>
                </small>
            </div>
        </div>
        
        <div class="card-body">
            <h5 class="card-title mb-2"><?php echo htmlspecialchars($booking['class_name']); ?></h5>
            
            <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-calendar text-muted me-2"></i>
                    <span><?php echo $class_date->format('l, F j, Y'); ?></span>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-clock text-muted me-2"></i>
                    <span><?php echo date('g:i A', strtotime($booking['time'])); ?> 
                    (<?php echo $booking['duration']; ?> min)</span>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-user text-muted me-2"></i>
                    <span><?php echo htmlspecialchars($booking['instructor']); ?></span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-users text-muted me-2"></i>
                    <span><?php echo $booking['confirmed_spots']; ?>/<?php echo $booking['capacity']; ?> spots filled</span>
                </div>
            </div>
            
            <?php if ($booking['status'] === 'confirmed' && $booking['confirmed_at']): ?>
                <div class="alert alert-success alert-sm">
                    <i class="fas fa-check-circle"></i>
                    <strong>Confirmed</strong> on <?php echo date('M j, Y', strtotime($booking['confirmed_at'])); ?>
                    <?php if ($booking['confirmed_by_name']): ?>
                        by <?php echo htmlspecialchars($booking['confirmed_by_name'] . ' ' . $booking['confirmed_by_lastname']); ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($booking['status'] === 'rejected'): ?>
                <div class="alert alert-danger alert-sm">
                    <i class="fas fa-times-circle"></i>
                    <strong>Rejected</strong> 
                    <?php if ($booking['rejected_at']): ?>
                        on <?php echo date('M j, Y', strtotime($booking['rejected_at'])); ?>
                    <?php endif; ?>
                    <?php if ($booking['rejected_by_name']): ?>
                        by <?php echo htmlspecialchars($booking['rejected_by_name'] . ' ' . $booking['rejected_by_lastname']); ?>
                    <?php endif; ?>
                </div>
                <?php if ($booking['rejection_reason']): ?>
                    <div class="alert alert-warning alert-sm">
                        <i class="fas fa-info-circle"></i>
                        <strong>Reason:</strong> <?php echo htmlspecialchars($booking['rejection_reason']); ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($booking['status'] === 'pending'): ?>
                <div class="alert alert-warning alert-sm">
                    <i class="fas fa-clock"></i>
                    <strong>Awaiting confirmation</strong> - We'll notify you once reviewed
                </div>
            <?php endif; ?>
            
            <?php if ($booking['class_description']): ?>
                <p class="card-text small text-muted">
                    <?php echo htmlspecialchars(substr($booking['class_description'], 0, 100)); ?>
                    <?php if (strlen($booking['class_description']) > 100) echo '...'; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="card-footer bg-transparent">
            <?php if ($booking['status'] === 'pending' || ($booking['status'] === 'confirmed' && !$is_past)): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="cancel_booking">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                            onclick="return confirm('Are you sure you want to cancel this booking?')">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($booking['status'] === 'rejected'): ?>
                <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-redo"></i> Book Another Class
                </a>
            <?php endif; ?>
            
            <?php if ($booking['status'] === 'confirmed' && !$is_past): ?>
                <span class="badge bg-success">
                    <i class="fas fa-check"></i> Ready to Attend
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<style>
.booking-card {
    transition: all 0.3s ease;
    border-width: 2px;
}

.booking-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: 1px solid #dee2e6;
    margin-bottom: -1px;
}

.nav-tabs .nav-link.active {
    color: #FF4444;
    background-color: #fff;
    border-color: #FF4444 #FF4444 #fff;
}

.nav-tabs .nav-link:hover {
    border-color: #FF4444;
    color: #FF4444;
}

.badge {
    font-size: 0.7rem;
}

.dashboard-hero {
    background: linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111111 50%, #000000 100%);
    color: #ffffff;
    min-height: 200px;
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>