<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_login(); 

$pageTitle = "Dashboard";
$pageCSS = ["/assets/css/dashboard.css", "/assets/css/dashboard-enhanced.css", "/assets/css/dashboard-comprehensive.css"];

$user = current_user();

// Check for welcome parameter (first-time login)
$isWelcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';
$welcomeMessage = $_SESSION['welcome_message'] ?? null;
$showWelcomeTour = $_SESSION['show_welcome_tour'] ?? false;

// Clear welcome messages after displaying
if ($welcomeMessage) {
    unset($_SESSION['welcome_message']);
    unset($_SESSION['show_welcome_tour']);
}

// Get membership info
$membership = null;
$stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name, mp.price FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = "active" AND m.end_date > NOW()');
$stmt->execute([$user['id']]);
$membership = $stmt->fetch();

// Get fitness profile
$fitness_profile = null;
$stmt = $pdo->prepare('SELECT * FROM user_fitness_profile WHERE user_id = ?');
$stmt->execute([$user['id']]);
$fitness_profile = $stmt->fetch();

// Get latest weight progress
$latest_weight = null;
$stmt = $pdo->prepare('SELECT * FROM weight_progress WHERE user_id = ? ORDER BY recorded_date DESC LIMIT 1');
$stmt->execute([$user['id']]);
$latest_weight = $stmt->fetch();

// Get weight progress for chart (last 6 entries)
$weight_history = [];
$stmt = $pdo->prepare('SELECT weight, recorded_date FROM weight_progress WHERE user_id = ? ORDER BY recorded_date DESC LIMIT 6');
$stmt->execute([$user['id']]);
$weight_history = array_reverse($stmt->fetchAll());

// Get active nutrition plan
$nutrition_plan = null;
$stmt = $pdo->prepare('SELECT * FROM user_nutrition_profiles WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$user['id']]);
$nutrition_plan = $stmt->fetch();

// Get active goals
$active_goals = [];
$stmt = $pdo->prepare('SELECT * FROM user_goals WHERE user_id = ? AND status = "active" ORDER BY priority DESC, created_at DESC LIMIT 4');
$stmt->execute([$user['id']]);
$active_goals = $stmt->fetchAll();

// Get recent workouts
$recent_workouts = [];
$stmt = $pdo->prepare('SELECT * FROM workout_progress WHERE user_id = ? ORDER BY workout_date DESC LIMIT 5');
$stmt->execute([$user['id']]);
$recent_workouts = $stmt->fetchAll();

// Get upcoming bookings
$stmt = $pdo->prepare('SELECT c.title, c.start_time, c.end_time, b.status FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND c.start_time > NOW() ORDER BY c.start_time ASC LIMIT 5');
$stmt->execute([$user['id']]);
$upcoming_classes = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total_attended FROM bookings WHERE member_id = ? AND status = "attended"');
$stmt->execute([$user['id']]);
$stats_attended = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as month_bookings FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND b.status IN ("booked", "attended") AND MONTH(c.start_time) = MONTH(NOW()) AND YEAR(c.start_time) = YEAR(NOW())');
$stmt->execute([$user['id']]);
$stats_month = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as upcoming_count FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? AND c.start_time > NOW()');
$stmt->execute([$user['id']]);
$stats_upcoming = $stmt->fetchColumn();

// Calculate fitness progress percentages
$weight_progress = 0;
if ($fitness_profile && $fitness_profile['target_weight'] && $latest_weight) {
    $start_weight = 88.5; // Default starting weight, you can adjust this
    $current_weight = (float)$latest_weight['weight'];
    $target_weight = (float)$fitness_profile['target_weight'];
    
    if ($start_weight != $target_weight) {
        $weight_progress = min(100, max(0, (($start_weight - $current_weight) / ($start_weight - $target_weight)) * 100));
    }
}

// Get announcements
$stmt = $pdo->prepare("
    SELECT a.*, 
           (SELECT COUNT(*) FROM announcements_user au WHERE au.announcement_id = a.id AND au.user_id = ?) as is_read
    FROM announcements a 
    WHERE a.published_at IS NOT NULL 
    ORDER BY a.published_at DESC 
    LIMIT 3
");
$stmt->execute([$user['id']]);
$announcements = $stmt->fetchAll();

// Get recent messages (skip for now - need to check table structure)
$recent_messages = [];

// Get check-in status
$stmt = $pdo->prepare("SELECT * FROM member_checkins WHERE member_id = ? AND checkout_time IS NULL ORDER BY checkin_time DESC LIMIT 1");
$stmt->execute([$user['id']]);
$current_checkin = $stmt->fetch();

// Get payment history count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM payment_receipts WHERE member_id = ?");
$stmt->execute([$user['id']]);
$payment_count = $stmt->fetchColumn();

// Get recent check-ins (last 10)
$stmt = $pdo->prepare("
    SELECT *, 
           TIMESTAMPDIFF(MINUTE, checkin_time, COALESCE(checkout_time, NOW())) as duration_calc
    FROM member_checkins 
    WHERE member_id = ? 
    ORDER BY checkin_time DESC 
    LIMIT 10
");
$stmt->execute([$user['id']]);
$recent_checkins = $stmt->fetchAll();

// Get payment history with details
$stmt = $pdo->prepare("
    SELECT p.*, mp.name as plan_name, m.start_date, m.end_date
    FROM payments p
    LEFT JOIN memberships m ON p.membership_id = m.id
    LEFT JOIN membership_plans mp ON m.plan_id = mp.id
    WHERE p.member_id = ?
    ORDER BY p.paid_at DESC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$payment_history = $stmt->fetchAll();

// Check if user has completed profile
$profile_complete = !empty($user['phone']) && !empty($user['address']) && isset($user['emergency_contact']) && !empty($user['emergency_contact']);
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<?php if ($welcomeMessage): ?>
<div class="welcome-alert-container">
  <div class="container">
    <div class="alert alert-success alert-dismissible fade show welcome-alert" role="alert">
      <div class="d-flex align-items-center">
        <div class="welcome-icon me-3">
          <i class="fas fa-dumbbell"></i>
        </div>
        <div class="flex-grow-1">
          <h5 class="alert-heading mb-1">🎉 <?php echo htmlspecialchars($welcomeMessage); ?></h5>
          <?php if ($showWelcomeTour): ?>
            <p class="mb-0">Ready to start your fitness journey? Let's set up your profile and goals!</p>
          <?php else: ?>
            <p class="mb-0">Let's crush today's workout! 💪</p>
          <?php endif; ?>
        </div>
        <?php if ($showWelcomeTour): ?>
        <div class="welcome-actions">
          <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-sm btn-outline-success me-2">
            <i class="fas fa-user-cog"></i> Complete Profile
          </a>
          <a href="<?php echo BASE_URL; ?>memberships.php" class="btn btn-sm btn-success">
            <i class="fas fa-star"></i> Choose Plan
          </a>
        </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>

<style>
.welcome-alert-container {
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(10px);
  border-bottom: 2px solid rgba(255,68,68,0.1);
}
.welcome-alert {
  background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(22,163,74,0.15));
  border: 2px solid rgba(34,197,94,0.3);
  border-radius: 15px;
  color: #ffffff;
  margin-bottom: 0;
}
.welcome-alert .alert-heading {
  color: #22c55e;
}
.welcome-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}
.welcome-actions .btn {
  font-weight: 600;
  border-radius: 20px;
}
.btn-outline-success {
  border-color: #22c55e;
  color: #22c55e;
}
.btn-outline-success:hover {
  background-color: #22c55e;
  border-color: #22c55e;
}
</style>

<?php endif; ?>

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
          <?php elseif ($fitness_profile && $latest_weight): ?>
            <div class="progress-overview mt-3">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="progress-stat">
                    <div class="progress-icon">🎯</div>
                    <div class="progress-info">
                      <div class="progress-value"><?php echo number_format($weight_progress, 1); ?>%</div>
                      <div class="progress-label">Weight Goal Progress</div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="progress-stat">
                    <div class="progress-icon">💪</div>
                    <div class="progress-info">
                      <div class="progress-value"><?php echo $stats_attended; ?></div>
                      <div class="progress-label">Classes Completed</div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="progress-stat">
                    <div class="progress-icon">🔥</div>
                    <div class="progress-info">
                      <div class="progress-value"><?php echo count($active_goals); ?></div>
                      <div class="progress-label">Active Goals</div>
                    </div>
                  </div>
                </div>
              </div>
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
    <!-- Personal Information Card -->
    <div class="col-lg-6 col-md-6">
      <div class="dashboard-card personal-info-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">👤 Personal Information</h5>
            <p class="card-description">Manage your personal details and emergency contacts</p>
          </div>
        </div>
        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <label>Full Name</label>
              <div class="info-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
            </div>
            <div class="info-item">
              <label>Email</label>
              <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-item">
              <label>Phone</label>
              <div class="info-value"><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '<span class="text-muted">Not provided</span>'; ?></div>
            </div>
            <div class="info-item">
              <label>Emergency Contact</label>
              <div class="info-value"><?php echo isset($user['emergency_contact']) && $user['emergency_contact'] ? htmlspecialchars($user['emergency_contact']) : '<span class="text-muted">Not provided</span>'; ?></div>
            </div>
            <div class="info-item">
              <label>Address</label>
              <div class="info-value"><?php echo $user['address'] ? htmlspecialchars($user['address']) : '<span class="text-muted">Not provided</span>'; ?></div>
            </div>
            <div class="info-item">
              <label>Date of Birth</label>
              <div class="info-value"><?php echo isset($user['dob']) && $user['dob'] ? date('F j, Y', strtotime($user['dob'])) : '<span class="text-muted">Not provided</span>'; ?></div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-dashboard">
            <i class="bi bi-pencil"></i> Update Information
          </a>
        </div>
      </div>
    </div>

    <!-- Fitness Profile Card -->
    <div class="col-lg-6 col-md-6">
      <div class="dashboard-card fitness-profile-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-heart-pulse"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">💪 Fitness Profile</h5>
            <p class="card-description">Track your fitness journey and goals</p>
          </div>
        </div>
        <div class="card-body">
          <?php if ($fitness_profile): ?>
            <div class="fitness-stats">
              <div class="row g-3">
                <div class="col-6">
                  <div class="fitness-stat">
                    <div class="stat-value"><?php echo $fitness_profile['height'] ? number_format($fitness_profile['height'], 1) . ' cm' : 'N/A'; ?></div>
                    <div class="stat-label">Height</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="fitness-stat">
                    <div class="stat-value"><?php echo $latest_weight ? number_format($latest_weight['weight'], 1) . ' kg' : 'N/A'; ?></div>
                    <div class="stat-label">Current Weight</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="fitness-stat">
                    <div class="stat-value"><?php echo $fitness_profile['target_weight'] ? number_format($fitness_profile['target_weight'], 1) . ' kg' : 'N/A'; ?></div>
                    <div class="stat-label">Target Weight</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="fitness-stat">
                    <div class="stat-value"><?php echo ucfirst($fitness_profile['fitness_level']); ?></div>
                    <div class="stat-label">Fitness Level</div>
                  </div>
                </div>
              </div>
              
              <?php if ($fitness_profile['target_weight'] && $latest_weight): ?>
                <div class="progress-section mt-4">
                  <div class="progress-header">
                    <span>Weight Goal Progress</span>
                    <span class="progress-percentage"><?php echo number_format($weight_progress, 1); ?>%</span>
                  </div>
                  <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $weight_progress; ?>%"></div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="empty-state text-center py-4">
              <i class="bi bi-clipboard-data display-1 text-muted mb-3"></i>
              <h6>No Fitness Profile Yet</h6>
              <p class="text-muted">Set up your fitness profile to track your progress!</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <button class="btn btn-dashboard" onclick="openFitnessModal()">
            <i class="bi bi-gear"></i> Manage Fitness Profile
          </button>
        </div>
      </div>
    </div>

    <!-- Nutrition Plan Card -->
    <div class="col-lg-6 col-md-6">
      <div class="dashboard-card nutrition-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-apple"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">🥗 Nutrition Plan</h5>
            <p class="card-description">Manage your diet and nutrition goals</p>
          </div>
        </div>
        <div class="card-body">
          <?php if ($nutrition_plan): ?>
            <div class="nutrition-overview">
              <div class="plan-header">
                <h6><?php echo htmlspecialchars($nutrition_plan['plan_name']); ?></h6>
                <span class="diet-badge diet-<?php echo $nutrition_plan['diet_type']; ?>">
                  <?php echo ucfirst(str_replace('_', ' ', $nutrition_plan['diet_type'])); ?>
                </span>
              </div>
              
              <div class="nutrition-macros mt-3">
                <div class="row g-2">
                  <div class="col-3">
                    <div class="macro-stat">
                      <div class="macro-value"><?php echo $nutrition_plan['daily_calories'] ?: 'N/A'; ?></div>
                      <div class="macro-label">Calories</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="macro-stat">
                      <div class="macro-value"><?php echo $nutrition_plan['daily_protein'] ? number_format($nutrition_plan['daily_protein'], 0) . 'g' : 'N/A'; ?></div>
                      <div class="macro-label">Protein</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="macro-stat">
                      <div class="macro-value"><?php echo $nutrition_plan['daily_carbs'] ? number_format($nutrition_plan['daily_carbs'], 0) . 'g' : 'N/A'; ?></div>
                      <div class="macro-label">Carbs</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="macro-stat">
                      <div class="macro-value"><?php echo $nutrition_plan['daily_fat'] ? number_format($nutrition_plan['daily_fat'], 0) . 'g' : 'N/A'; ?></div>
                      <div class="macro-label">Fat</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="meals-info mt-3">
                <small class="text-muted">
                  <i class="bi bi-clock"></i> <?php echo $nutrition_plan['meals_per_day']; ?> meals per day
                </small>
              </div>
            </div>
          <?php else: ?>
            <div class="empty-state text-center py-4">
              <i class="bi bi-apple display-1 text-muted mb-3"></i>
              <h6>No Nutrition Plan</h6>
              <p class="text-muted">Create a personalized nutrition plan to reach your goals!</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <button class="btn btn-dashboard" onclick="openNutritionModal()">
            <i class="bi bi-plus-circle"></i> <?php echo $nutrition_plan ? 'Edit Plan' : 'Create Plan'; ?>
          </button>
        </div>
      </div>
    </div>

    <!-- Goals Card -->
    <div class="col-lg-6 col-md-6">
      <div class="dashboard-card goals-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-bullseye"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">🎯 Active Goals</h5>
            <p class="card-description">Track your fitness and personal goals</p>
          </div>
        </div>
        <div class="card-body">
          <?php if (!empty($active_goals)): ?>
            <div class="goals-list">
              <?php foreach($active_goals as $goal): ?>
                <div class="goal-item">
                  <div class="goal-header">
                    <h6 class="goal-title"><?php echo htmlspecialchars($goal['title'] ?? 'Untitled Goal'); ?></h6>
                    <span class="goal-priority priority-<?php echo $goal['priority']; ?>">
                      <?php echo ucfirst($goal['priority']); ?>
                    </span>
                  </div>
                  
                  <?php if ($goal['target_value'] && $goal['current_value'] !== null): ?>
                    <div class="goal-progress">
                      <?php 
                      $progress_percentage = $goal['target_value'] != 0 ? ($goal['current_value'] / $goal['target_value']) * 100 : 0;
                      $progress_percentage = min(100, max(0, $progress_percentage));
                      ?>
                      <div class="progress-info">
                        <span><?php echo number_format($goal['current_value'], 1); ?> / <?php echo number_format($goal['target_value'], 1); ?> <?php echo htmlspecialchars($goal['unit'] ?? ''); ?></span>
                        <span><?php echo number_format($progress_percentage, 1); ?>%</span>
                      </div>
                      <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress_percentage; ?>%"></div>
                      </div>
                    </div>
                  <?php endif; ?>
                  
                  <?php if ($goal['target_date']): ?>
                    <div class="goal-deadline">
                      <small class="text-muted">
                        <i class="bi bi-calendar"></i> Target: <?php echo date('M j, Y', strtotime($goal['target_date'])); ?>
                      </small>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-state text-center py-4">
              <i class="bi bi-bullseye display-1 text-muted mb-3"></i>
              <h6>No Active Goals</h6>
              <p class="text-muted">Set some goals to stay motivated and track your progress!</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <button class="btn btn-dashboard" onclick="openGoalsModal()">
            <i class="bi bi-plus-circle"></i> Manage Goals
          </button>
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
          <button class="quick-action-btn" onclick="openWeightModal()" 
                  title="Log your current weight and track progress">
            <i class="bi bi-graph-up"></i>
            <span>⚖️ Log Weight</span>
          </button>
          <button class="quick-action-btn" onclick="openWorkoutModal()" 
                  title="Record your workout and track your exercises">
            <i class="bi bi-trophy"></i>
            <span>💪 Log Workout</span>
          </button>
          <button class="quick-action-btn" onclick="openNutritionModal()" 
                  title="Manage your nutrition plan and dietary preferences">
            <i class="bi bi-apple"></i>
            <span>🥗 Nutrition Plan</span>
          </button>
          <button class="quick-action-btn" onclick="location.href='<?php echo BASE_URL; ?>memberships.php'" 
                  title="View membership options and upgrade">
            <i class="bi bi-arrow-up-circle"></i>
            <span>⬆️ Upgrade Plan</span>
          </button>
          <button class="quick-action-btn" onclick="openGoalsModal()" 
                  title="Set and track your fitness goals">
            <i class="bi bi-target"></i>
            <span>🎯 Set Goals</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Workouts Section -->
  <?php if (!empty($recent_workouts)): ?>
  <div class="row mt-5">
    <div class="col-12">
      <div class="workouts-section">
        <h3 class="section-title mb-4">
          <i class="bi bi-activity me-2"></i>Recent Workouts
        </h3>
        <div class="row g-3">
          <?php foreach($recent_workouts as $workout): ?>
            <div class="col-lg-4 col-md-6">
              <div class="workout-card">
                <div class="workout-header">
                  <div class="workout-type-icon">
                    <?php if ($workout['exercise_type'] === 'strength'): ?>
                      <i class="bi bi-trophy-fill text-warning"></i>
                    <?php elseif ($workout['exercise_type'] === 'cardio'): ?>
                      <i class="bi bi-heart-pulse-fill text-danger"></i>
                    <?php else: ?>
                      <i class="bi bi-activity text-primary"></i>
                    <?php endif; ?>
                  </div>
                  <div class="workout-info">
                    <h6><?php echo htmlspecialchars($workout['exercise_name']); ?></h6>
                    <small class="text-muted"><?php echo date('M j, Y', strtotime($workout['workout_date'])); ?></small>
                  </div>
                </div>
                <div class="workout-details">
                  <?php if ($workout['exercise_type'] === 'strength'): ?>
                    <div class="detail-item">
                      <span>Sets:</span> <strong><?php echo $workout['sets']; ?></strong>
                    </div>
                    <div class="detail-item">
                      <span>Reps:</span> <strong><?php echo $workout['reps']; ?></strong>
                    </div>
                    <?php if ($workout['weight']): ?>
                      <div class="detail-item">
                        <span>Weight:</span> <strong><?php echo number_format($workout['weight'], 1); ?>kg</strong>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if ($workout['duration']): ?>
                      <div class="detail-item">
                        <span>Duration:</span> <strong><?php echo $workout['duration']; ?> min</strong>
                      </div>
                    <?php endif; ?>
                    <?php if ($workout['calories_burned']): ?>
                      <div class="detail-item">
                        <span>Calories:</span> <strong><?php echo $workout['calories_burned']; ?></strong>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
                <?php if ($workout['notes']): ?>
                  <div class="workout-notes">
                    <small><?php echo htmlspecialchars($workout['notes']); ?></small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
          <button class="btn btn-outline-primary" onclick="openWorkoutModal()">
            <i class="bi bi-plus-circle me-2"></i>💪 Log New Workout
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

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
                    <?php if ($class['status'] === 'booked'): ?>
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
</div>

<!-- Modals for Quick Actions -->
<!-- Weight Logging Modal -->
<div class="modal fade" id="weightModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">⚖️ Log Weight Progress</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="weightForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current Weight (kg)</label>
            <input type="number" class="form-control" name="weight" step="0.1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Body Fat % (optional)</label>
            <input type="number" class="form-control" name="body_fat" step="0.1" min="0" max="50">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Log Weight</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Workout Logging Modal -->
<div class="modal fade" id="workoutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">💪 Log Workout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="workoutForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Exercise Name</label>
            <input type="text" class="form-control" name="exercise_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Exercise Type</label>
            <select class="form-control" name="exercise_type" required onchange="toggleWorkoutFields()">
              <option value="">Select Type</option>
              <option value="strength">Strength Training</option>
              <option value="cardio">Cardio</option>
              <option value="flexibility">Flexibility</option>
              <option value="balance">Balance</option>
              <option value="sports">Sports</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 strength-fields" style="display:none;">
              <div class="mb-3">
                <label class="form-label">Sets</label>
                <input type="number" class="form-control" name="sets" min="1">
              </div>
            </div>
            <div class="col-md-6 strength-fields" style="display:none;">
              <div class="mb-3">
                <label class="form-label">Reps</label>
                <input type="number" class="form-control" name="reps" min="1">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 strength-fields" style="display:none;">
              <div class="mb-3">
                <label class="form-label">Weight (kg)</label>
                <input type="number" class="form-control" name="weight" step="0.5" min="0">
              </div>
            </div>
            <div class="col-md-6 cardio-fields" style="display:none;">
              <div class="mb-3">
                <label class="form-label">Duration (minutes)</label>
                <input type="number" class="form-control" name="duration" min="1">
              </div>
            </div>
          </div>
          <div class="mb-3 cardio-fields" style="display:none;">
            <label class="form-label">Calories Burned</label>
            <input type="number" class="form-control" name="calories_burned" min="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Log Workout</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Comprehensive Features Section -->
<div class="container py-5">
  <div class="row g-4">
    <!-- Check-in/Check-out Card -->
    <div class="col-lg-6">
      <div class="dashboard-card check-in-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-geo-alt"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">🏋️ Gym Check-in</h5>
            <p class="card-description">Track your gym visits</p>
          </div>
        </div>
        <div class="card-body text-center">
          <?php if ($current_checkin): ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle"></i> Currently checked in since <?php echo date('g:i A', strtotime($current_checkin['checkin_time'])); ?>
            </div>
            <button class="btn btn-danger btn-lg" onclick="checkOut()">
              <i class="bi bi-box-arrow-right"></i> Check Out
            </button>
          <?php else: ?>
            <button class="btn btn-success btn-lg" onclick="checkIn()">
              <i class="bi bi-box-arrow-in-right"></i> Check In
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Announcements Card -->
    <div class="col-lg-6">
      <div class="dashboard-card announcements-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-megaphone"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">📢 Gym Announcements</h5>
            <p class="card-description">Latest updates from L9 Fitness</p>
          </div>
        </div>
        <div class="card-body">
          <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $announcement): ?>
              <div class="announcement-item <?php echo $announcement['is_read'] ? '' : 'unread'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6><?php echo htmlspecialchars($announcement['title']); ?></h6>
                    <p class="mb-2"><?php echo htmlspecialchars(substr($announcement['body'], 0, 100)); ?>...</p>
                    <small class="text-muted">
                      <i class="bi bi-clock"></i> <?php echo date('M j, g:i A', strtotime($announcement['published_at'])); ?>
                    </small>
                  </div>
                  <?php if (!$announcement['is_read']): ?>
                    <span class="badge bg-primary">New</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted">
              <i class="bi bi-info-circle"></i> No announcements at the moment
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <a href="profile.php#announcements" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
      </div>
    </div>

    <!-- Messages Card -->
    <div class="col-lg-6">
      <div class="dashboard-card messages-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-chat-dots"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">💬 Messages</h5>
            <p class="card-description">Communication with trainers & admin</p>
          </div>
        </div>
        <div class="card-body">
          <?php if (!empty($recent_messages)): ?>
            <?php foreach (array_slice($recent_messages, 0, 3) as $message): ?>
              <div class="message-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6><?php echo htmlspecialchars($message['subject']); ?></h6>
                    <p class="mb-1"><?php echo htmlspecialchars(substr($message['message'], 0, 80)); ?>...</p>
                    <small class="text-muted">From: <?php echo htmlspecialchars($message['sender_name'] . ' ' . $message['sender_last']); ?></small>
                  </div>
                  <small class="text-muted"><?php echo date('M j', strtotime($message['created_at'])); ?></small>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted">
              <i class="bi bi-envelope"></i> No messages yet
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <a href="profile.php#messages" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus"></i> Send Message
          </a>
          <a href="profile.php#messages" class="btn btn-outline-secondary btn-sm">View All</a>
        </div>
      </div>
    </div>

    <!-- Payment History Card -->
    <div class="col-lg-6">
      <div class="dashboard-card payment-card">
        <div class="card-header">
          <div class="card-icon">
            <i class="bi bi-credit-card"></i>
          </div>
          <div class="card-title-section">
            <h5 class="card-title">💳 Payment History</h5>
            <p class="card-description">View receipts & payment records</p>
          </div>
        </div>
        <div class="card-body text-center">
          <div class="payment-stats">
            <div class="stat-value"><?php echo $payment_count; ?></div>
            <div class="stat-label">Payment Records</div>
          </div>
          <?php if ($membership): ?>
            <div class="current-membership mt-3">
              <div class="membership-badge">
                <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($membership['plan_name']); ?>
              </div>
              <small class="text-muted">
                Active until <?php echo date('M j, Y', strtotime($membership['end_date'])); ?>
              </small>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <a href="profile.php#payments" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-receipt"></i> View Receipts
          </a>
          <a href="memberships.php" class="btn btn-outline-secondary btn-sm">Upgrade Plan</a>
        </div>
      </div>
    </div>
  </div>

  <!-- History Section -->
  <div class="row mt-5">
    <div class="col-lg-6 mb-4">
      <div class="dashboard-card history-card">
        <div class="card-header bg-gradient-primary">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">
              <i class="bi bi-clock-history"></i> Recent Check-ins
            </h5>
            <span class="badge bg-light text-dark"><?php echo count($recent_checkins); ?> visits</span>
          </div>
        </div>
        <div class="card-body">
          <?php if (count($recent_checkins) > 0): ?>
            <div class="checkin-list">
              <?php foreach (array_slice($recent_checkins, 0, 5) as $checkin): ?>
                <div class="checkin-item d-flex justify-content-between align-items-center py-2 border-bottom">
                  <div>
                    <div class="fw-bold"><?php echo date('M j, Y', strtotime($checkin['checkin_time'])); ?></div>
                    <small class="text-muted">
                      <?php echo date('g:i A', strtotime($checkin['checkin_time'])); ?>
                      <?php if ($checkin['checkout_time']): ?>
                        - <?php echo date('g:i A', strtotime($checkin['checkout_time'])); ?>
                      <?php else: ?>
                        - <span class="text-success">Active</span>
                      <?php endif; ?>
                    </small>
                  </div>
                  <div class="text-end">
                    <?php if ($checkin['checkout_time']): ?>
                      <span class="badge bg-secondary"><?php echo $checkin['duration_minutes'] ?? $checkin['duration_calc']; ?> min</span>
                    <?php else: ?>
                      <span class="badge bg-success">🔥 Active</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
              <a href="profile.php#checkins" class="btn btn-outline-primary btn-sm" onclick="localStorage.setItem('openTab', 'checkins')">
                <i class="bi bi-list"></i> View All History
              </a>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="bi bi-clock display-4 text-muted mb-3"></i>
              <p class="text-muted">No check-ins yet</p>
              <button class="btn btn-primary" onclick="checkIn()">
                <i class="bi bi-box-arrow-in-right"></i> First Check-in
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="col-lg-6 mb-4">
      <div class="dashboard-card history-card">
        <div class="card-header bg-gradient-success">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">
              <i class="bi bi-receipt"></i> Payment History
            </h5>
            <span class="badge bg-light text-dark"><?php echo count($payment_history); ?> payments</span>
          </div>
        </div>
        <div class="card-body">
          <?php if (count($payment_history) > 0): ?>
            <div class="payment-list">
              <?php foreach (array_slice($payment_history, 0, 5) as $payment): ?>
                <div class="payment-item d-flex justify-content-between align-items-center py-2 border-bottom">
                  <div>
                    <div class="fw-bold">$<?php echo number_format($payment['amount'], 2); ?></div>
                    <small class="text-muted">
                      <?php echo $payment['plan_name'] ?? 'Service Payment'; ?> • 
                      <?php echo date('M j, Y', strtotime($payment['paid_at'])); ?>
                    </small>
                  </div>
                  <div class="text-end">
                    <span class="badge bg-<?php echo $payment['status'] === 'paid' ? 'success' : 'warning'; ?>">
                      <?php echo ucfirst($payment['status']); ?>
                    </span>
                    <?php if ($payment['invoice_no']): ?>
                      <br><small class="text-muted">Invoice: <?php echo $payment['invoice_no']; ?></small>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
              <a href="profile.php#payments" class="btn btn-outline-success btn-sm">
                <i class="bi bi-list"></i> View Complete History
              </a>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="bi bi-receipt display-4 text-muted mb-3"></i>
              <p class="text-muted">No payments yet</p>
              <a href="memberships.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Get Membership
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions Section -->
  <div class="row mt-5">
    <div class="col-12">
      <h4 class="text-gradient mb-4">🚀 Quick Actions</h4>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="profile.php#photos" class="quick-action-btn">
        <i class="bi bi-camera"></i>
        <span>Upload Photo</span>
      </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="classes.php" class="quick-action-btn">
        <i class="bi bi-calendar-plus"></i>
        <span>Book Class</span>
      </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="profile.php#fitness" class="quick-action-btn">
        <i class="bi bi-clipboard-data"></i>
        <span>Log Workout</span>
      </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="profile.php#messages" class="quick-action-btn">
        <i class="bi bi-chat-text"></i>
        <span>Contact Trainer</span>
      </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="profile.php#checkins" class="quick-action-btn" onclick="localStorage.setItem('openTab', 'checkins')">
        <i class="bi bi-clock-history"></i>
        <span>🏋️ Visit History</span>
      </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
      <a href="profile.php" class="quick-action-btn">
        <i class="bi bi-person-gear"></i>
        <span>Profile Settings</span>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/dashboard-modals.php'; ?>

<style>
/* === DASHBOARD CHATBOT FIXES - EXACT SAME AS PROFILE.PHP === */
#simpleChatbot {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 999999 !important;
    display: block !important;
    visibility: visible !important;
    pointer-events: auto !important;
}

#chatToggle {
    width: 90px !important;
    height: 90px !important;
    border-radius: 50% !important;
    border: 3px solid rgba(255, 68, 68, 0.5) !important;
    background: linear-gradient(135deg, #FF4444, #FFD700, #FF4444) !important;
    background-size: 200% 200% !important;
    color: white !important;
    font-size: 28px !important;
    cursor: pointer !important;
    box-shadow: 0 20px 40px rgba(255, 68, 68, 0.4), 0 0 30px rgba(255, 215, 0, 0.3) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.4s ease !important;
    backdrop-filter: blur(15px) !important;
    position: relative !important;
    font-weight: 600 !important;
    animation: chatbotFloat 3s ease-in-out infinite !important;
}

#chatToggle:hover {
    transform: scale(1.1) !important;
    box-shadow: 0 25px 50px rgba(255, 68, 68, 0.6), 0 0 40px rgba(255, 215, 0, 0.5) !important;
}

#chatWindow {
    position: fixed !important;
    bottom: 120px !important;
    right: 20px !important;
    width: 380px !important;
    height: 500px !important;
    background: linear-gradient(135deg, rgba(0,0,0,0.95), rgba(26,26,26,0.95)) !important;
    backdrop-filter: blur(25px) !important;
    border: 2px solid rgba(255, 68, 68, 0.3) !important;
    border-radius: 20px !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 10px 30px rgba(255, 68, 68, 0.3) !important;
    display: none !important;
    flex-direction: column !important;
    overflow: hidden !important;
    z-index: 999998 !important;
}

#chatWindow.show {
    display: flex !important;
}

.chat-header {
    background: linear-gradient(135deg, #FF4444, #FFD700) !important;
    color: white !important;
    padding: 18px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-radius: 20px 20px 0 0 !important;
}

.chat-header .title {
    font-weight: bold !important;
    font-size: 18px !important;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5) !important;
}

#chatClose {
    background: rgba(255, 255, 255, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: white !important;
    font-size: 24px !important;
    cursor: pointer !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

#chatClose:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    transform: scale(1.1) !important;
}

#chatMessages {
    flex: 1 !important;
    padding: 25px !important;
    overflow-y: auto !important;
    background: rgba(0, 0, 0, 0.3) !important;
}

.chat-message {
    margin-bottom: 15px !important;
    padding: 15px 20px !important;
    border-radius: 20px !important;
    max-width: 85% !important;
    word-wrap: break-word !important;
}

.chat-message.user {
    background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(138, 43, 226, 0.2)) !important;
    color: white !important;
    margin-left: auto !important;
    border-bottom-right-radius: 5px !important;
}

.chat-message.bot {
    background: linear-gradient(135deg, rgba(255, 68, 68, 0.2), rgba(255, 215, 0, 0.2)) !important;
    color: white !important;
    margin-right: auto !important;
    border-bottom-left-radius: 5px !important;
}

.chat-input-container {
    display: flex !important;
    gap: 15px !important;
    align-items: center !important;
    padding: 20px !important;
    background: rgba(0, 0, 0, 0.5) !important;
    border-top: 1px solid rgba(255, 68, 68, 0.3) !important;
}

#chatInput {
    flex: 1 !important;
    padding: 15px 20px !important;
    border: 2px solid rgba(255, 68, 68, 0.3) !important;
    border-radius: 25px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    font-size: 16px !important;
    outline: none !important;
}

#chatInput::placeholder {
    color: rgba(255, 255, 255, 0.6) !important;
}

#chatInput:focus {
    border-color: #FF4444 !important;
    box-shadow: 0 0 20px rgba(255, 68, 68, 0.3) !important;
}

#chatSend {
    background: linear-gradient(135deg, #FF4444, #FFD700) !important;
    color: white !important;
    border: none !important;
    padding: 15px 25px !important;
    border-radius: 25px !important;
    cursor: pointer !important;
    font-size: 16px !important;
    font-weight: bold !important;
    transition: all 0.3s ease !important;
}

#chatSend:hover {
    background: linear-gradient(135deg, #FF6666, #FFE135) !important;
    transform: scale(1.05) !important;
}

@keyframes chatbotFloat {
    0%, 100% { 
        transform: translateY(0px) scale(1);
    }
    50% { 
        transform: translateY(-10px) scale(1.02);
    }
}

/* Mobile responsive fixes */
@media (max-width: 768px) {
    #chatWindow {
        width: calc(100vw - 40px) !important;
        right: 20px !important;
        left: 20px !important;
    }
    
    #chatToggle {
        width: 70px !important;
        height: 70px !important;
        font-size: 24px !important;
    }
}

/* === GO TO TOP BUTTON FIXES === */
.go-to-top-btn, .scroll-to-top, #goToTop, #scrollToTop {
    position: fixed !important;
    bottom: 20px !important;
    left: 20px !important;
    width: 50px !important;
    height: 50px !important;
    background: linear-gradient(135deg, #FF4444, #FF6666) !important;
    border: none !important;
    border-radius: 50% !important;
    color: white !important;
    font-size: 18px !important;
    cursor: pointer !important;
    z-index: 99998 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: scale(0.8) !important;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    box-shadow: 
        0 6px 20px rgba(255, 68, 68, 0.4),
        0 3px 10px rgba(0, 0, 0, 0.3) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border: 2px solid rgba(255, 255, 255, 0.1) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.go-to-top-btn.show, .scroll-to-top.show, #goToTop.show, #scrollToTop.show {
    opacity: 1 !important;
    visibility: visible !important;
    transform: scale(1) !important;
}

.go-to-top-btn:hover, .scroll-to-top:hover, #goToTop:hover, #scrollToTop:hover {
    background: linear-gradient(135deg, #FF6666, #FF8888) !important;
    transform: scale(1.1) !important;
    box-shadow: 
        0 8px 25px rgba(255, 68, 68, 0.5),
        0 4px 15px rgba(0, 0, 0, 0.4) !important;
}

.go-to-top-btn:active, .scroll-to-top:active, #goToTop:active, #scrollToTop:active {
    transform: scale(0.95) !important;
}

/* Mobile Go to Top responsive fixes */
@media (max-width: 768px) {
    .go-to-top-btn, .scroll-to-top, #goToTop, #scrollToTop {
        bottom: 25px !important;
        left: 25px !important;
        width: 45px !important;
        height: 45px !important;
        font-size: 16px !important;
    }
}

        @media (max-width: 480px) {
            .go-to-top-btn, .scroll-to-top, #goToTop, #scrollToTop {
                bottom: 30px !important;
                left: 30px !important;
                width: 40px !important;
                height: 40px !important;
                font-size: 14px !important;
            }
        }

/* === FOOTER Z-INDEX TRAP FIX === */
/* Prevent footer from trapping floating elements */
.l9-premium-footer, footer, .footer-background, .footer-content, .footer-particles, .footer-sparkles {
    z-index: 1 !important;
    position: relative !important;
}

/* Force chatbot and buttons above EVERYTHING */
#simpleChatbot, #chatToggle, #chatWindow, #goToTop, .go-to-top-btn, .scroll-to-top {
    z-index: 999999 !important;
    position: fixed !important;
}

/* Ensure no stacking context interference */
body {
    position: relative !important;
    z-index: auto !important;
}

main, .main-content, .container {
    position: relative !important;
    z-index: auto !important;
}
</style><script>
// Modal Functions
function openWeightModal() {
  new bootstrap.Modal(document.getElementById('weightModal')).show();
}

function openWorkoutModal() {
  new bootstrap.Modal(document.getElementById('workoutModal')).show();
}

function openNutritionModal() {
  // For now, redirect to profile page
  location.href = '<?php echo BASE_URL; ?>profile.php';
}

function openGoalsModal() {
  // For now, redirect to profile page
  location.href = '<?php echo BASE_URL; ?>profile.php';
}

function openFitnessModal() {
  // For now, redirect to profile page
  location.href = '<?php echo BASE_URL; ?>profile.php';
}

function toggleWorkoutFields() {
  const exerciseType = document.querySelector('[name="exercise_type"]').value;
  const strengthFields = document.querySelectorAll('.strength-fields');
  const cardioFields = document.querySelectorAll('.cardio-fields');
  
  strengthFields.forEach(field => field.style.display = exerciseType === 'strength' ? 'block' : 'none');
  cardioFields.forEach(field => field.style.display = exerciseType === 'cardio' ? 'block' : 'none');
}

// Form Handlers
document.getElementById('weightForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>api/log_weight.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('weightModal')).hide();
      location.reload();
    } else {
      alert('Error logging weight: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while logging weight');
  });
});

document.getElementById('workoutForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>api/log_workout.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('workoutModal')).hide();
      location.reload();
    } else {
      alert('Error logging workout: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while logging workout');
  });
});

// Check-in/Check-out functions
function checkIn() {
  fetch('api/profile_api.php?action=check_in', {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccess(data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      showError(data.message || 'Check-in failed');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showError('Network error occurred');
  });
}

function checkOut() {
  fetch('api/profile_api.php?action=check_out', {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccess(data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      showError(data.message || 'Check-out failed');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showError('Network error occurred');
  });
}

function showSuccess(message) {
  const alert = document.createElement('div');
  alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
  alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
  alert.innerHTML = `
    <i class="bi bi-check-circle"></i> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(alert);
  setTimeout(() => alert.remove(), 5000);
}

function showError(message) {
  const alert = document.createElement('div');
  alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
  alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
  alert.innerHTML = `
    <i class="bi bi-exclamation-circle"></i> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(alert);
  setTimeout(() => alert.remove(), 5000);
}
</script>

<style>
  /* L9 FITNESS DASHBOARD STYLING - BEAST MODE */
  .dashboard-card {
    background: linear-gradient(135deg, rgba(0,0,0,0.8), rgba(26,26,26,0.9)) !important;
    border: 2px solid #ff4444 !important;
    border-radius: 15px !important;
    box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3) !important;
    overflow: hidden !important;
    margin-bottom: 20px !important;
    color: white !important;
  }
  
  .history-card {
    background: linear-gradient(135deg, rgba(0,0,0,0.9), rgba(26,26,26,0.8)) !important;
    border: 2px solid #ff4444 !important;
    box-shadow: 0 8px 25px rgba(255, 68, 68, 0.2) !important;
  }
  
  .history-card .card-header {
    background: linear-gradient(135deg, #ff4444, #ff6666) !important;
    border-bottom: 2px solid #ff2222 !important;
    color: white !important;
    font-weight: bold !important;
  }
  
  .history-card .card-header.bg-gradient-success {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border-bottom: 2px solid #1e7e34 !important;
  }
  
  .history-card .card-body {
    background: rgba(0,0,0,0.7) !important;
    color: white !important;
    border: none !important;
  }
  
  .checkin-item, .payment-item {
    background: rgba(255, 68, 68, 0.05) !important;
    padding: 15px !important;
    margin: 10px 0 !important;
    border-radius: 10px !important;
    border: 1px solid rgba(255, 68, 68, 0.2) !important;
    color: white !important;
    transition: all 0.3s ease !important;
  }
  
  .checkin-item:hover, .payment-item:hover {
    background: rgba(255, 68, 68, 0.1) !important;
    transform: translateX(5px) !important;
    border-color: #ff4444 !important;
  }
  
  .checkin-item:last-child, .payment-item:last-child {
    border-bottom: 1px solid rgba(255, 68, 68, 0.2) !important;
  }
  
  .empty-state {
    background: linear-gradient(135deg, rgba(255, 68, 68, 0.1), rgba(255, 102, 102, 0.05)) !important;
    border: 2px dashed #ff4444 !important;
    border-radius: 15px !important;
    padding: 40px !important;
    color: white !important;
    text-align: center !important;
  }
  
  .empty-state i {
    color: #ff4444 !important;
  }
  
  .card-body {
    background: rgba(0,0,0,0.6) !important;
    color: white !important;
    padding: 25px !important;
  }
  
  .text-muted {
    color: rgba(255,255,255,0.7) !important;
  }
  
  .fw-bold {
    color: #ff4444 !important;
  }
  
  .badge {
    font-size: 0.8em !important;
    padding: 0.4em 0.8em !important;
    font-weight: bold !important;
  }
  
  .badge.bg-success {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border: none !important;
  }
  
  .badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268) !important;
    border: none !important;
  }
  
  .badge.bg-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800) !important;
    color: #000 !important;
  }
  
  .badge.bg-light {
    background: rgba(255,255,255,0.2) !important;
    color: white !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
  }
  
  /* Beast Mode Glow Effects */
  .dashboard-card:hover {
    box-shadow: 0 12px 35px rgba(255, 68, 68, 0.5) !important;
    transform: translateY(-2px) !important;
  }
  
  .btn-outline-primary, .btn-outline-success, .btn-outline-secondary {
    border: 2px solid currentColor !important;
    background: rgba(0,0,0,0.3) !important;
    color: white !important;
    font-weight: bold !important;
  }
  
  .btn-outline-primary:hover {
    background: #ff4444 !important;
    border-color: #ff4444 !important;
    color: white !important;
  }
  
  .btn-outline-success:hover {
    background: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
  }
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>