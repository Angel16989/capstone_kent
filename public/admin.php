<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 

if (!is_admin()) { 
    http_response_code(403); 
    exit('Admins only.'); 
}

$pageTitle = "Admin Dashboard";
$pageCSS = "/assets/css/admin.css";

// Get admin statistics
try {
    // User stats
    $stmt = $pdo->query('SELECT COUNT(*) as total_users FROM users WHERE role_id != 1');
    $total_users = $stmt->fetchColumn();
    
    $stmt = $pdo->query('SELECT COUNT(*) as active_memberships FROM memberships WHERE status = "active" AND end_date > NOW()');
    $active_memberships = $stmt->fetchColumn();
    
    // Class stats
    $stmt = $pdo->query('SELECT COUNT(*) as total_classes FROM classes WHERE start_time > NOW()');
    $upcoming_classes = $stmt->fetchColumn();
    
    $stmt = $pdo->query('SELECT COUNT(*) as total_bookings FROM bookings WHERE status = "confirmed"');
    $total_bookings = $stmt->fetchColumn();
    
    // Revenue stats (this month)
    $stmt = $pdo->query('SELECT SUM(amount) as month_revenue FROM payments WHERE MONTH(payment_date) = MONTH(NOW()) AND YEAR(payment_date) = YEAR(NOW()) AND status = "completed"');
    $month_revenue = $stmt->fetchColumn() ?: 0;
    
    // Recent activity
    $stmt = $pdo->query('
        SELECT "new_user" as type, CONCAT(first_name, " ", last_name) as title, created_at as activity_time 
        FROM users 
        WHERE role_id != 1 AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT "booking" as type, CONCAT("Class booking: ", c.title) as title, b.booking_date as activity_time
        FROM bookings b 
        JOIN classes c ON b.class_id = c.id 
        WHERE b.booking_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT "payment" as type, CONCAT("Payment: $", amount) as title, payment_date as activity_time
        FROM payments 
        WHERE payment_date > DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = "completed"
        ORDER BY activity_time DESC 
        LIMIT 10
    ');
    $recent_activities = $stmt->fetchAll();
    
} catch (Exception $e) {
    $total_users = $active_memberships = $upcoming_classes = $total_bookings = $month_revenue = 0;
    $recent_activities = [];
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Admin Hero Section -->
<div class="admin-hero py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="text-center mb-5">
          <div class="admin-badge mb-3">
            <i class="bi bi-shield-check"></i>
            Beast Command
          </div>
          <h1 class="display-4 fw-bold text-gradient">Beast Master Control</h1>
          <p class="lead">Command the gym empire. Control the warriors. Dominate everything.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Admin Cards Section -->
<div class="container pb-5">
  <div class="row g-4">
    <!-- User Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card users-card">
        <div class="card-icon">
          <i class="bi bi-people-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">User Management</h4>
          <p class="card-description">Manage member accounts, view profiles, and handle user permissions</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $total_users; ?></span>
            <span class="stat-label">Total Members</span>
          </div>
        </div>
        <a href="<?php echo BASE_URL; ?>users.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Users
        </a>
      </div>
    </div>
    
    <!-- Membership Plans -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card memberships-card">
        <div class="card-icon">
          <i class="bi bi-credit-card-2-front"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Membership Plans</h4>
          <p class="card-description">Create and modify membership tiers, pricing, and benefits</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $active_memberships; ?></span>
            <span class="stat-label">Active Memberships</span>
          </div>
        </div>
        <a href="<?php echo BASE_URL; ?>memberships.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Plans
        </a>
      </div>
    </div>
    
    <!-- Class Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card classes-card">
        <div class="card-icon">
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Class Schedules</h4>
          <p class="card-description">Schedule fitness classes, manage instructors, and track attendance</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $upcoming_classes; ?></span>
            <span class="stat-label">Upcoming Classes</span>
          </div>
        </div>
        <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Classes
        </a>
      </div>
    </div>
    
    <!-- Reports & Analytics -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card reports-card">
        <div class="card-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Reports & Analytics</h4>
          <p class="card-description">View membership trends, revenue reports, and usage statistics</p>
          <div class="card-stats">
            <span class="stat-number">$<?php echo number_format($month_revenue); ?></span>
            <span class="stat-label">This Month Revenue</span>
          </div>
        </div>
        <a href="#reports" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Reports
        </a>
      </div>
    </div>
    
    <!-- System Settings -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card settings-card">
        <div class="card-icon">
          <i class="bi bi-gear-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">System Settings</h4>
          <p class="card-description">Configure gym policies, payment settings, and notification preferences</p>
          <div class="card-stats">
            <span class="stat-number">✓</span>
            <span class="stat-label">All Systems OK</span>
          </div>
        </div>
        <a href="admin_settings.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Settings
        </a>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card actions-card">
        <div class="card-icon">
          <i class="bi bi-lightning-charge"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Quick Actions</h4>
          <p class="card-description">Perform common administrative tasks and emergency functions</p>
          <div class="card-stats">
            <span class="stat-number">6</span>
            <span class="stat-label">Quick Actions</span>
          </div>
        </div>
        <a href="#" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Quick Actions
        </a>
      </div>
    </div>
    
    <!-- Chatbot Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card chatbot-card">
        <div class="card-icon">
          <i class="bi bi-robot"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Chatbot Analytics</h4>
          <p class="card-description">Monitor chatbot conversations and user interactions</p>
          <div class="card-stats">
            <span class="stat-number">🤖</span>
            <span class="stat-label">AI Assistant</span>
          </div>
        </div>
        <a href="chatbot_admin.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Conversations
        </a>
      </div>
    </div>
  </div>
  
  <!-- Recent Activity Section -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="admin-activity-section">
        <h3 class="section-title">Recent Administrative Activity</h3>
        <div class="activity-list">
          <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
              <div class="activity-item">
                <div class="activity-icon">
                  <?php if ($activity['type'] === 'new_user'): ?>
                    <i class="bi bi-person-plus"></i>
                  <?php elseif ($activity['type'] === 'booking'): ?>
                    <i class="bi bi-calendar-check"></i>
                  <?php elseif ($activity['type'] === 'payment'): ?>
                    <i class="bi bi-credit-card"></i>
                  <?php endif; ?>
                </div>
                <div class="activity-content">
                  <h6>
                    <?php if ($activity['type'] === 'new_user'): ?>
                      New Member Registration
                    <?php elseif ($activity['type'] === 'booking'): ?>
                      Class Booking
                    <?php elseif ($activity['type'] === 'payment'): ?>
                      Payment Received
                    <?php endif; ?>
                  </h6>
                  <p><?php echo htmlspecialchars($activity['title']); ?></p>
                  <span class="activity-time">
                    <?php 
                    $time_diff = time() - strtotime($activity['activity_time']);
                    if ($time_diff < 3600) echo floor($time_diff/60) . ' minutes ago';
                    elseif ($time_diff < 86400) echo floor($time_diff/3600) . ' hours ago';
                    else echo floor($time_diff/86400) . ' days ago';
                    ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="activity-item">
              <div class="activity-icon">
                <i class="bi bi-info-circle"></i>
              </div>
              <div class="activity-content">
                <h6>No Recent Activity</h6>
                <p>System is running smoothly</p>
                <span class="activity-time">System status</span>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
