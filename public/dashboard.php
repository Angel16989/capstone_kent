<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_login(); 

$pageTitle = "Dashboard";
$pageCSS = "/assets/css/dashboard.css";
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="dashboard-hero">
  <div class="container py-5">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="hero-content">
          <div class="welcome-badge mb-3">
            <i class="bi bi-lightning-charge-fill"></i>
            Beast Mode Active
          </div>
          <h1 class="display-5 fw-bold mb-3">
            What's up, <span class="text-gradient"><?php echo htmlspecialchars(current_user()['name']); ?>!</span>
          </h1>
          <p class="lead">Time to dominate. Your limits are waiting to be shattered.</p>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="quick-stats">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-info">
              <div class="stat-value">12</div>
              <div class="stat-label">Classes This Month</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- Membership Card -->
    <div class="col-lg-4 col-md-6">
      <div class="dashboard-card membership-card">
        <div class="card-icon">
          <i class="bi bi-gem"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">Membership</h5>
          <p class="card-description">Manage your current plan, view renewal dates, and upgrade options.</p>
          <div class="card-status">
            <div class="status-indicator active"></div>
            <span>Active Plan</span>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="memberships.php">
            <span>Manage Plan</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Classes Card -->
    <div class="col-lg-4 col-md-6">
      <div class="dashboard-card classes-card">
        <div class="card-icon">
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">Classes & Bookings</h5>
          <p class="card-description">Book new classes, view your schedule, and manage reservations.</p>
          <div class="card-status">
            <div class="status-indicator upcoming"></div>
            <span>3 Upcoming</span>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="classes.php">
            <span>Browse Classes</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Profile Card -->
    <div class="col-lg-4 col-md-6">
      <div class="dashboard-card profile-card">
        <div class="card-icon">
          <i class="bi bi-person-gear"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">Profile Settings</h5>
          <p class="card-description">Update your personal information, preferences, and account settings.</p>
          <div class="card-status">
            <div class="status-indicator complete"></div>
            <span>Profile Complete</span>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="profile.php">
            <span>Edit Profile</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity Section -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="activity-section">
        <h3 class="section-title mb-4">Recent Activity</h3>
        <div class="row g-3">
          <div class="col-lg-6">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-trophy-fill"></i>
              </div>
              <div class="activity-content">
                <h6>Completed HIIT Class</h6>
                <p>Great job on yesterday's high-intensity workout!</p>
                <span class="activity-time">2 days ago</span>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="activity-card">
              <div class="activity-icon">
                <i class="bi bi-calendar-plus"></i>
              </div>
              <div class="activity-content">
                <h6>Booked Yoga Session</h6>
                <p>Mindful Flow Yoga scheduled for tomorrow at 7 PM</p>
                <span class="activity-time">1 day ago</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
