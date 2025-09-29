<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 

if (!is_admin()) { 
    // Redirect to admin login page instead of showing error
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI'])); 
    exit(); 
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
    $stmt = $pdo->query('SELECT SUM(amount) as month_revenue FROM payments WHERE MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW()) AND status = "paid"');
    $month_revenue = $stmt->fetchColumn() ?: 0;

    // Trainer stats
    $stmt = $pdo->query('SELECT COUNT(*) as active_trainers FROM users WHERE role_id = 3');
    $active_trainers = $stmt->fetchColumn();

    // Blog posts stats
    $stmt = $pdo->query('SELECT COUNT(*) as total_posts FROM blog_posts WHERE status = "published"');
    $total_posts = $stmt->fetchColumn();
    
    // Equipment stats
    $stmt = $pdo->query('SELECT COUNT(*) as total_equipment FROM gym_equipment');
    $total_equipment = $stmt->fetchColumn();
    
    // Feedback stats
    $stmt = $pdo->query('SELECT COUNT(*) as pending_feedback FROM feedback WHERE reply_text IS NULL OR reply_text = ""');
    $pending_feedback = $stmt->fetchColumn();
    
    // Today's attendance
    $stmt = $pdo->query('SELECT COUNT(*) as today_attendance FROM class_attendance WHERE DATE(attendance_date) = CURDATE()');
    $today_attendance = $stmt->fetchColumn();
    
    // Recent activity
    $stmt = $pdo->query('
        SELECT "new_user" as type, CONCAT(first_name, " ", last_name) as title, created_at as activity_time 
        FROM users 
        WHERE role_id != 1 AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT "booking" as type, CONCAT("Class booking: ", c.title) as title, b.booked_at as activity_time
        FROM bookings b 
        JOIN classes c ON b.class_id = c.id 
        WHERE b.booked_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT "payment" as type, CONCAT("Payment: $", amount) as title, paid_at as activity_time
        FROM payments 
        WHERE paid_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = "paid"
        ORDER BY activity_time DESC 
        LIMIT 10
    ');
    $recent_activities = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Admin stats error: " . $e->getMessage());
    $total_users = $active_memberships = $upcoming_classes = $total_bookings = $month_revenue = $active_trainers = $total_posts = $total_equipment = $pending_feedback = $today_attendance = 0;
    $recent_activities = [];
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Admin Hero Section -->

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
          <p class="card-description">Manage all user accounts, roles, and permissions</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $total_users; ?></span>
            <span class="stat-label">Total Users</span>
          </div>
        </div>
        <a href="<?php echo BASE_URL; ?>users.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Users
        </a>
      </div>
    </div>

    <!-- Membership Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card memberships-card">
        <div class="card-icon">
          <i class="bi bi-trophy-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Membership Plans</h4>
          <p class="card-description">Manage membership plans, pricing, and active subscriptions</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $active_memberships; ?></span>
            <span class="stat-label">Active Members</span>
          </div>
        </div>
        <a href="admin_memberships.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Plans
        </a>
      </div>
    </div>

    <!-- Class Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card classes-card">
        <div class="card-icon">
          <i class="bi bi-calendar-event-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Class Management</h4>
          <p class="card-description">Schedule classes, manage instructors, and monitor attendance</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $upcoming_classes; ?></span>
            <span class="stat-label">Upcoming Classes</span>
          </div>
        </div>
        <a href="admin_classes.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Classes
        </a>
      </div>
    </div>

    <!-- Bookings Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card bookings-card">
        <div class="card-icon">
          <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Bookings & Reservations</h4>
          <p class="card-description">Monitor class bookings, handle cancellations, and manage capacity</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $total_bookings; ?></span>
            <span class="stat-label">Total Bookings</span>
          </div>
        </div>
        <a href="admin_bookings.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Bookings
        </a>
      </div>
    </div>

    <!-- Revenue Analytics -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card revenue-card">
        <div class="card-icon">
          <i class="bi bi-cash-stack"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Revenue Analytics</h4>
          <p class="card-description">Track payments, revenue trends, and financial performance</p>
          <div class="card-stats">
            <span class="stat-number">$<?php echo number_format($month_revenue, 0); ?></span>
            <span class="stat-label">This Month</span>
          </div>
        </div>
        <a href="admin_reports.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Analytics
        </a>
      </div>
    </div>

    <!-- Trainer Utilization Reports -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card trainer-reports-card">
        <div class="card-icon">
          <i class="bi bi-person-workspace"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Trainer Utilization</h4>
          <p class="card-description">Track trainer performance, class assignments, and utilization metrics</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $active_trainers; ?></span>
            <span class="stat-label">Active Trainers</span>
          </div>
        </div>
        <a href="trainer_reports.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Reports
        </a>
      </div>
    </div>

    <!-- Blog & News Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card blog-card">
        <div class="card-icon">
          <i class="bi bi-newspaper"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Blog & News</h4>
          <p class="card-description">Create and manage blog posts, news updates, and in-app notifications</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $total_posts; ?></span>
            <span class="stat-label">Published Posts</span>
          </div>
        </div>
        <a href="blog_management.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Content
        </a>
      </div>
    </div>

    <!-- Equipment Tracking -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card equipment-card">
        <div class="card-icon">
          <i class="bi bi-tools"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Equipment Tracking</h4>
          <p class="card-description">Monitor gym equipment status, maintenance schedules, and usage</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $total_equipment; ?></span>
            <span class="stat-label">Total Equipment</span>
          </div>
        </div>
        <a href="equipment_tracking.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Track Equipment
        </a>
      </div>
    </div>

    <!-- Feedback Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card feedback-card">
        <div class="card-icon">
          <i class="bi bi-chat-quote"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Feedback Management</h4>
          <p class="card-description">Review and respond to member feedback on classes and trainers</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $pending_feedback; ?></span>
            <span class="stat-label">Pending Reviews</span>
          </div>
        </div>
        <a href="feedback_management.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Feedback
        </a>
      </div>
    </div>

    <!-- Content Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card content-card">
        <div class="card-icon">
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Content Management</h4>
          <p class="card-description">Edit pages like FAQ, About Us, Contact, Privacy, and Terms</p>
          <div class="card-stats">
            <span class="stat-number">5</span>
            <span class="stat-label">Pages Managed</span>
          </div>
        </div>
        <a href="content_management.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Edit Pages
        </a>
      </div>
    </div>

    <!-- Schedule & Attendance -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card schedule-card">
        <div class="card-icon">
          <i class="bi bi-calendar-week"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Schedule & Attendance</h4>
          <p class="card-description">Monitor class schedules, track attendance, and view popularity</p>
          <div class="card-stats">
            <span class="stat-number"><?php echo $today_attendance; ?></span>
            <span class="stat-label">Today's Attendance</span>
          </div>
        </div>
        <a href="schedule_attendance.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Schedule
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
          <p class="card-description">Configure system policies, security, and admin preferences</p>
          <div class="card-stats">
            <span class="stat-number">✓</span>
            <span class="stat-label">System OK</span>
          </div>
        </div>
        <a href="admin_settings.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Settings
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
                      New User Registration
                    <?php elseif ($activity['type'] === 'booking'): ?>
                      Class Booking Activity
                    <?php elseif ($activity['type'] === 'payment'): ?>
                      Payment Processed
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
                <i class="bi bi-shield-check"></i>
              </div>
              <div class="activity-content">
                <h6>System Status: Normal</h6>
                <p>All administrative functions operating correctly</p>
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
