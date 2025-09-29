<?php
/**
 * Booking Management Admin Panel
 * Allows admins to confirm/reject class bookings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/services/BookingNotificationService.php';
require_login();

if (!is_admin()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$pageTitle = "Booking Management";
$pageCSS = "assets/css/admin.css";

// Initialize notification service
$notificationService = new BookingNotificationService($pdo);

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $booking_id = (int)$_POST['booking_id'];
        $action = $_POST['action'];
        $admin_id = current_user()['id'];
        
        if ($action === 'confirm') {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'confirmed', confirmed_at = NOW(), confirmed_by = ?
                WHERE id = ? AND status = 'pending'
            ");
            $result = $stmt->execute([$admin_id, $booking_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Send notification email
                $emailSent = $notificationService->sendBookingConfirmation($booking_id);
                $success_message = "Booking confirmed successfully!" . 
                    ($emailSent ? " Email notification sent." : " (Email notification failed)");
            } else {
                $error_message = "Failed to confirm booking. It may already be processed.";
            }
            
        } elseif ($action === 'reject') {
            $rejection_reason = $_POST['rejection_reason'] ?? '';
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_reason = ?
                WHERE id = ? AND status = 'pending'
            ");
            $result = $stmt->execute([$admin_id, $rejection_reason, $booking_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Send notification email
                $emailSent = $notificationService->sendBookingRejection($booking_id);
                $success_message = "Booking rejected successfully!" . 
                    ($emailSent ? " Email notification sent." : " (Email notification failed)");
            } else {
                $error_message = "Failed to reject booking. It may already be processed.";
            }
            
        } elseif ($action === 'bulk_confirm') {
            $booking_ids = $_POST['booking_ids'] ?? [];
            if (!empty($booking_ids)) {
                $placeholders = str_repeat('?,', count($booking_ids) - 1) . '?';
                $stmt = $pdo->prepare("
                    UPDATE bookings 
                    SET status = 'confirmed', confirmed_at = NOW(), confirmed_by = ?
                    WHERE id IN ($placeholders) AND status = 'pending'
                ");
                $result = $stmt->execute(array_merge([$admin_id], $booking_ids));
                
                if ($result) {
                    $confirmed_count = $stmt->rowCount();
                    $success_message = "$confirmed_count bookings confirmed successfully!";
                    
                    // Send notification emails for each confirmed booking
                    $email_success_count = 0;
                    foreach ($booking_ids as $id) {
                        if ($notificationService->sendBookingConfirmation($id)) {
                            $email_success_count++;
                        }
                    }
                    
                    $success_message .= " ($email_success_count email notifications sent)";
                } else {
                    $error_message = "Failed to confirm bookings.";
                }
            }
        }
        
    } catch (Exception $e) {
        $error_message = "Error processing booking: " . $e->getMessage();
    }
}

// Get booking statistics
$stats = [];
$stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count,
        COUNT(CASE WHEN DATE(booked_at) = CURDATE() THEN 1 END) as today_count
    FROM bookings 
    GROUP BY status
");
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row;
}

// Get pending bookings with user and class details
$stmt = $pdo->prepare("
    SELECT b.*, c.title as class_name, c.start_time, c.end_time, c.capacity,
           u.first_name, u.last_name, u.email,
           CONCAT(t.first_name, ' ', t.last_name) as instructor_name,
           (SELECT COUNT(*) FROM bookings b2 WHERE b2.class_id = c.id AND b2.status = 'confirmed') as confirmed_count
    FROM bookings b
    JOIN classes c ON b.class_id = c.id
    JOIN users u ON b.member_id = u.id
    JOIN users t ON c.trainer_id = t.id
    WHERE b.status = 'pending'
    ORDER BY b.booked_at DESC
");
$stmt->execute();
$pending_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent confirmed/rejected bookings
$stmt = $pdo->prepare("
    SELECT b.*, c.title as class_name, c.start_time, c.end_time,
           u.first_name, u.last_name, u.email,
           admin_u.first_name as admin_first_name, admin_u.last_name as admin_last_name
    FROM bookings b
    JOIN classes c ON b.class_id = c.id
    JOIN users u ON b.member_id = u.id
    LEFT JOIN users admin_u ON (b.confirmed_by = admin_u.id OR b.rejected_by = admin_u.id)
    WHERE b.status IN ('confirmed', 'rejected')
    ORDER BY COALESCE(b.confirmed_at, b.rejected_at) DESC
    LIMIT 50
");
$stmt->execute();
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-calendar-check text-primary"></i>
                        Booking Management
                    </h1>
                    <p class="text-muted">Manage class booking confirmations and rejections</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>admin.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </div>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning"><?php echo $stats['pending']['count'] ?? 0; ?></h4>
                    <p class="mb-0">Pending Bookings</p>
                    <small class="text-muted"><?php echo $stats['pending']['today_count'] ?? 0; ?> today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success"><?php echo $stats['confirmed']['count'] ?? 0; ?></h4>
                    <p class="mb-0">Confirmed</p>
                    <small class="text-muted"><?php echo $stats['confirmed']['today_count'] ?? 0; ?> today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h4 class="text-danger"><?php echo $stats['rejected']['count'] ?? 0; ?></h4>
                    <p class="mb-0">Rejected</p>
                    <small class="text-muted"><?php echo $stats['rejected']['today_count'] ?? 0; ?> today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                    <h4 class="text-info"><?php echo array_sum(array_column($stats, 'count')); ?></h4>
                    <p class="mb-0">Total Bookings</p>
                    <small class="text-muted">All time</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-warning"></i>
                        Pending Bookings (<?php echo count($pending_bookings); ?>)
                    </h5>
                    <?php if (!empty($pending_bookings)): ?>
                        <div>
                            <button type="button" class="btn btn-success btn-sm" onclick="bulkConfirm()">
                                <i class="fas fa-check-double"></i> Bulk Confirm
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-body">
                    <?php if (empty($pending_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3 text-muted">All caught up!</h4>
                            <p class="text-muted">No pending bookings to review.</p>
                        </div>
                    <?php else: ?>
                        <form id="bulkForm">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>Member</th>
                                            <th>Class</th>
                                            <th>Date & Time</th>
                                            <th>Instructor</th>
                                            <th>Capacity</th>
                                            <th>Booked At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="booking_ids[]" value="<?php echo $booking['id']; ?>" class="form-check-input booking-checkbox">
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['class_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $class_date = new DateTime($booking['start_time']);
                                                    echo $class_date->format('M j, Y'); 
                                                    ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($booking['start_time'])) . ' - ' . date('g:i A', strtotime($booking['end_time'])); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($booking['instructor_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $booking['confirmed_count'] >= $booking['capacity'] ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo $booking['confirmed_count']; ?>/<?php echo $booking['capacity']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M j, g:i A', strtotime($booking['booked_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="confirmBooking(<?php echo $booking['id']; ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="showRejectModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>', '<?php echo htmlspecialchars($booking['class_name']); ?>')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Actions Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-info"></i>
                        Recent Actions
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <p class="text-muted">No recent booking actions.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Action Date</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_bookings, 0, 10) as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($booking['class_name']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo date('M j', strtotime($booking['start_time'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, g:i A', strtotime($booking['confirmed_at'] ?: $booking['rejected_at'])); ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php if ($booking['admin_first_name']): ?>
                                                        <?php echo htmlspecialchars($booking['admin_first_name'] . ' ' . $booking['admin_last_name']); ?>
                                                    <?php else: ?>
                                                        System
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Booking Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="booking_id" id="rejectBookingId">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        You are about to reject the booking for <strong id="rejectMemberName"></strong> 
                        for the class <strong id="rejectClassName"></strong>.
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for rejection:</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                  placeholder="Please provide a reason for rejecting this booking..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmBooking(bookingId) {
    if (confirm('Are you sure you want to confirm this booking?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="confirm">
            <input type="hidden" name="booking_id" value="${bookingId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(bookingId, memberName, className) {
    document.getElementById('rejectBookingId').value = bookingId;
    document.getElementById('rejectMemberName').textContent = memberName;
    document.getElementById('rejectClassName').textContent = className;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function bulkConfirm() {
    const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one booking to confirm.');
        return;
    }
    
    if (confirm(`Are you sure you want to confirm ${checkedBoxes.length} booking(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="bulk_confirm">';
        
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'booking_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.booking-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update select all when individual checkboxes change
document.querySelectorAll('.booking-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const total = document.querySelectorAll('.booking-checkbox').length;
        const checked = document.querySelectorAll('.booking-checkbox:checked').length;
        document.getElementById('selectAll').indeterminate = checked > 0 && checked < total;
        document.getElementById('selectAll').checked = checked === total;
    });
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.badge {
    font-size: 0.75rem;
}

.text-muted {
    color: #6c757d !important;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>