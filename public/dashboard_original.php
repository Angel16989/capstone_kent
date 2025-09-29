<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_login(); 

$pageTitle = "Dashboard";
$pageCSS = "/assets/css/dashboard.css";

$user = current_user();

// Get membership info
$membership = null;
$stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name, mp.price FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = "active" AND m.end_date > NOW()');
$stmt->execute([$user['id']]);
$membership = $stmt->fetch();

// Get upcoming bookings
$stmt = $pdo->prepare('SELECT c.title, c.start_time, c.end_time, b.status FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND c.start_time > NOW() ORDER BY c.start_time ASC LIMIT 5');
$stmt->execute([$user['id']]);
$upcoming_classes = $stmt->fetchAll();

// Get recent activity
$stmt = $pdo->prepare('SELECT c.title, c.start_time, b.status, b.booked_at FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND c.start_time < NOW() ORDER BY c.start_time DESC LIMIT 5');
$stmt->execute([$user['id']]);
$recent_activity = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total_bookings FROM bookings WHERE member_id = ? AND status = "booked"');
$stmt->execute([$user['id']]);
$stats_total = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as month_bookings FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND b.status = "booked" AND MONTH(c.start_time) = MONTH(NOW()) AND YEAR(c.start_time) = YEAR(NOW())');
$stmt->execute([$user['id']]);
$stats_month = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as upcoming_count FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND c.start_time > NOW()');
$stmt->execute([$user['id']]);
$stats_upcoming = $stmt->fetchColumn();

// Check if user has completed profile
$profile_complete = !empty($user['phone']) && !empty($user['address']);
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
            What's up, <span class="text-gradient"><?php echo htmlspecialchars($user['first_name']); ?>!</span>
          </h1>
          <p class="lead">Time to dominate. Your limits are waiting to be shattered.</p>
          
          <?php if (!$membership): ?>
            <div class="alert alert-warning mt-3">
              <i class="bi bi-exclamation-triangle me-2"></i>
              You don't have an active membership. <a href="<?php echo BASE_URL; ?>memberships.php" class="alert-link">Get one now!</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="quick-stats">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-info">
              <div class="stat-value"><?php echo $stats_month; ?></div>
              <div class="stat-label">Classes This Month</div>
            </div>
          </div>
          
          <div class="stat-card mt-3">
            <div class="stat-icon">
              <i class="bi bi-clock"></i>
            </div>
            <div class="stat-info">
              <div class="stat-value"><?php echo $stats_upcoming; ?></div>
              <div class="stat-label">Upcoming Bookings</div>
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
      <div class="dashboard-card membership-card" data-bs-toggle="collapse" data-bs-target="#membershipDetails" aria-expanded="false">
        <div class="card-icon">
          <i class="bi bi-gem"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">💎 Membership</h5>
          <p class="card-description">Manage your current plan, view renewal dates, and upgrade options.</p>
          <div class="card-status">
            <?php if ($membership): ?>
              <div class="status-indicator active"></div>
              <span><?php echo htmlspecialchars($membership['plan_name']); ?> - Expires <?php echo date('M d', strtotime($membership['end_date'])); ?></span>
            <?php else: ?>
              <div class="status-indicator inactive"></div>
              <span>No Active Plan</span>
            <?php endif; ?>
          </div>
          <div class="card-expand mt-2">
            <i class="bi bi-chevron-down"></i>
            <small>Click to see membership features</small>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="<?php echo BASE_URL; ?>memberships.php" 
             title="<?php echo $membership ? 'View your current membership details, renewal dates, and upgrade options' : 'Choose from our flexible membership plans to unlock unlimited class access and premium features'; ?>">
            <span><?php echo $membership ? '💎 Manage My Plan' : '🚀 Get Membership'; ?></span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
      
      <!-- Expandable Membership Details -->
      <div class="collapse mt-3" id="membershipDetails">
        <div class="dashboard-details">
          <h6>🎯 What You Get with Membership:</h6>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Unlimited Classes</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>24/7 Gym Access</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Personal Training</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Nutrition Guidance</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Progress Tracking</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Equipment Access</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Guest Passes</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-success"></i>
                <span>Member Events</span>
              </div>
            </div>
          </div>
          <?php if ($membership): ?>
            <div class="membership-stats mt-3">
              <div class="stat-badge">
                <i class="bi bi-calendar-check"></i>
                <span>Active Since <?php echo date('M Y', strtotime($membership['start_date'] ?? 'now')); ?></span>
              </div>
              <div class="stat-badge">
                <i class="bi bi-trophy"></i>
                <span><?php echo $stats_month; ?> Classes This Month</span>
              </div>
            </div>
          <?php else: ?>
            <div class="upgrade-prompt mt-3">
              <p class="text-primary"><strong>🚀 Ready to unlock all features?</strong></p>
              <small class="text-muted">Join thousands of members achieving their fitness goals!</small>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Classes Card -->
    <div class="col-lg-4 col-md-6">
      <div class="dashboard-card classes-card" data-bs-toggle="collapse" data-bs-target="#classesDetails" aria-expanded="false">
        <div class="card-icon">
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">🏃‍♂️ Classes & Bookings</h5>
          <p class="card-description">Book new classes, view your schedule, and manage reservations.</p>
          <div class="card-status">
            <?php if ($stats_upcoming > 0): ?>
              <div class="status-indicator upcoming"></div>
              <span><?php echo $stats_upcoming; ?> Upcoming</span>
            <?php else: ?>
              <div class="status-indicator inactive"></div>
              <span>No Bookings</span>
            <?php endif; ?>
          </div>
          <div class="card-expand mt-2">
            <i class="bi bi-chevron-down"></i>
            <small>Click to see class types available</small>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="<?php echo BASE_URL; ?>classes.php" 
             title="Explore our diverse fitness classes, book your favorites, and manage your workout schedule">
            <span>🏃‍♂️ Browse & Book Classes</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
      
      <!-- Expandable Classes Details -->
      <div class="collapse mt-3" id="classesDetails">
        <div class="dashboard-details">
          <h6>🎯 Available Class Types:</h6>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-trophy text-danger"></i>
                <span>Strength Training</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-lightning-charge text-warning"></i>
                <span>Cardio Blast</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-flower3 text-info"></i>
                <span>Yoga & Mindfulness</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-music-note text-success"></i>
                <span>Dance Fitness</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-check-circle text-primary"></i>
                <span>Expert Trainers</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-primary"></i>
                <span>Small Group Sizes</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-primary"></i>
                <span>Flexible Scheduling</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle text-primary"></i>
                <span>Progress Tracking</span>
              </div>
            </div>
          </div>
          <div class="class-stats mt-3">
            <div class="stat-badge">
              <i class="bi bi-calendar-plus"></i>
              <span><?php echo $stats_total; ?> Total Classes Completed</span>
            </div>
            <div class="stat-badge">
              <i class="bi bi-clock-history"></i>
              <span><?php echo $stats_upcoming; ?> Upcoming Sessions</span>
            </div>
          </div>
        </div>
      </div>
    </div>
      </div>
    </div>

    <!-- Profile Card -->
    <div class="col-lg-4 col-md-6">
      <div class="dashboard-card profile-card" data-bs-toggle="collapse" data-bs-target="#profileDetails" aria-expanded="false">
        <div class="card-icon">
          <i class="bi bi-person-gear"></i>
        </div>
        <div class="card-content">
          <h5 class="card-title">👤 Profile Settings</h5>
          <p class="card-description">Update your personal information, preferences, and account settings.</p>
          <div class="card-status">
            <?php if ($profile_complete): ?>
              <div class="status-indicator complete"></div>
              <span>Profile Complete</span>
            <?php else: ?>
              <div class="status-indicator warning"></div>
              <span>Profile Incomplete</span>
            <?php endif; ?>
          </div>
          <div class="card-expand mt-2">
            <i class="bi bi-chevron-down"></i>
            <small>Click to see profile features</small>
          </div>
        </div>
        <div class="card-footer">
          <a class="btn btn-dashboard" href="<?php echo BASE_URL; ?>profile.php" 
             title="<?php echo $profile_complete ? 'Update your personal information, emergency contacts, and account preferences' : 'Complete your profile by adding contact information and emergency details for better service'; ?>">
            <span><?php echo $profile_complete ? '⚙️ Edit Profile' : '📝 Complete Profile'; ?></span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
      
      <!-- Expandable Profile Details -->
      <div class="collapse mt-3" id="profileDetails">
        <div class="dashboard-details">
          <h6>⚙️ Profile Management:</h6>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-person-badge text-info"></i>
                <span>Personal Info</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-shield-lock text-warning"></i>
                <span>Password Security</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-bell text-primary"></i>
                <span>Notifications</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-heart-pulse text-danger"></i>
                <span>Fitness Goals</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-item">
                <i class="bi bi-gear text-secondary"></i>
                <span>Preferences</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-calendar-date text-success"></i>
                <span>Availability</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-telephone text-info"></i>
                <span>Contact Info</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-bookmark text-purple"></i>
                <span>Saved Classes</span>
              </div>
            </div>
          </div>
          <div class="profile-stats mt-3">
            <div class="stat-badge">
              <i class="bi bi-person-check"></i>
              <span>Member Since <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></span>
            </div>
            <div class="stat-badge">
              <i class="bi bi-activity"></i>
              <span><?php echo $profile_complete ? 'Complete Profile' : 'Needs Completion'; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions Row -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="quick-actions-card">
        <h5>⚡ Quick Actions</h5>
        <p class="text-muted">Common tasks you might want to do</p>
        <div class="quick-actions-grid">
          <button class="quick-action-btn" onclick="location.href='<?php echo BASE_URL; ?>classes.php'" 
                  title="Find and book your next fitness class">
            <i class="bi bi-calendar-plus"></i>
            <span>📅 Book a Class</span>
          </button>
          <button class="quick-action-btn" onclick="location.href='<?php echo BASE_URL; ?>profile.php'" 
                  title="Update your fitness goals and preferences">
            <i class="bi bi-target"></i>
            <span>🎯 Set Goals</span>
          </button>
          <button class="quick-action-btn" onclick="location.href='<?php echo BASE_URL; ?>memberships.php'" 
                  title="View membership options and upgrade">
            <i class="bi bi-arrow-up-circle"></i>
            <span>⬆️ Upgrade Plan</span>
          </button>
          <button class="quick-action-btn" onclick="showComingSoon()" 
                  title="Track your fitness progress over time">
            <i class="bi bi-graph-up"></i>
            <span>📊 View Progress</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Upcoming Classes Section -->
  <?php if (!empty($upcoming_classes)): ?>
  <div class="row mt-5">
    <div class="col-12">
      <div class="upcoming-section">
        <h3 class="section-title mb-4">
          <i class="bi bi-calendar-event me-2"></i>Upcoming Classes
        </h3>
        <div class="row g-3">
          <?php foreach($upcoming_classes as $class): ?>
            <div class="col-lg-6">
              <div class="upcoming-card">
                <div class="upcoming-time">
                  <div class="time-day"><?php echo date('d', strtotime($class['start_time'])); ?></div>
                  <div class="time-month"><?php echo date('M', strtotime($class['start_time'])); ?></div>
                </div>
                <div class="upcoming-info">
                  <h6><?php echo htmlspecialchars($class['title']); ?></h6>
                  <p class="upcoming-datetime">
                    <i class="bi bi-clock me-1"></i>
                    <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                    <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                  </p>
                  <div class="upcoming-status">
                    <?php if ($class['status'] === 'confirmed'): ?>
                      <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>Confirmed
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning">
                        <i class="bi bi-clock me-1"></i>Waitlist
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
          <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-outline-primary" 
             title="Discover new classes and book your next workout session">
            <i class="bi bi-plus-circle me-2"></i>🔥 Book More Classes
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recent Activity Section -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="activity-section">
        <h3 class="section-title mb-4">
          <i class="bi bi-activity me-2"></i>Recent Activity
        </h3>
        
        <?php if (!empty($recent_activity)): ?>
          <div class="row g-3">
            <?php foreach($recent_activity as $activity): ?>
              <div class="col-lg-6">
                <div class="activity-card">
                  <div class="activity-icon">
                    <i class="bi bi-trophy-fill"></i>
                  </div>
                  <div class="activity-content">
                    <h6>Completed <?php echo htmlspecialchars($activity['title']); ?></h6>
                    <p>Great job on your <?php echo strtolower($activity['title']); ?> session!</p>
                    <span class="activity-time">
                      <?php 
                      $time_diff = time() - strtotime($activity['start_time']);
                      if ($time_diff < 3600) echo floor($time_diff/60) . ' minutes ago';
                      elseif ($time_diff < 86400) echo floor($time_diff/3600) . ' hours ago';
                      else echo floor($time_diff/86400) . ' days ago';
                      ?>
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state text-center py-5">
            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
            <h5>No Recent Activity</h5>
            <p class="text-muted">🌟 Start your fitness journey by booking your first class!</p>
            <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary" 
               title="Browse our extensive class schedule and book your first workout">
              <i class="bi bi-calendar-plus me-2"></i>🏃‍♂️ Browse Classes
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
