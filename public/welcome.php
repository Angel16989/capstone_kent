<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_login();

$pageTitle = "Welcome to L9 Fitness";
$pageCSS = "assets/css/home.css";

$user = current_user();

// Check if user has completed basic setup
global $pdo;
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM user_fitness_profile WHERE user_id = ?');
$stmt->execute([$user['id']]);
$hasProfile = $stmt->fetchColumn() > 0;

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM memberships WHERE member_id = ? AND status = "active" AND end_date > NOW()');
$stmt->execute([$user['id']]);
$hasMembership = $stmt->fetchColumn() > 0;

include __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- Welcome Hero Section -->
<div class="hero-section">
  <div class="container py-5">
    <div class="row align-items-center min-vh-75">
      <div class="col-lg-8 mx-auto text-center">
        <div class="hero-content animate-fadeInUp">
          <div class="hero-badge mb-4">
            <svg width="20" height="20" fill="currentColor" class="bi bi-lightning-charge-fill">
              <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
            </svg>
            Welcome to L9 Fitness
          </div>
          
          <h1 class="display-3 fw-bold mb-4">
            Ready to <br>
            <span class="text-gradient">Transform</span>?
          </h1>
          
          <p class="lead mb-5">
            Welcome to L9 Fitness, <strong><?php echo htmlspecialchars($user['first_name']); ?></strong>! <br>
            Let's get you set up for success. Your fitness journey starts here.
          </p>

          <div class="setup-progress mb-5">
            <h4 class="mb-4">Complete Your Setup</h4>
            <div class="row g-4">
              <div class="col-md-6">
                <div class="setup-card <?php echo $hasProfile ? 'completed' : ''; ?>">
                  <div class="setup-icon">
                    <?php if ($hasProfile): ?>
                      <i class="fas fa-check-circle text-success"></i>
                    <?php else: ?>
                      <i class="fas fa-user-cog"></i>
                    <?php endif; ?>
                  </div>
                  <h5>Complete Profile</h5>
                  <p>Set up your fitness goals, preferences, and personal information.</p>
                  <?php if (!$hasProfile): ?>
                    <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-primary">
                      <i class="fas fa-arrow-right"></i> Complete Profile
                    </a>
                  <?php else: ?>
                    <div class="text-success fw-bold">
                      <i class="fas fa-check"></i> Profile Complete
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="setup-card <?php echo $hasMembership ? 'completed' : ''; ?>">
                  <div class="setup-icon">
                    <?php if ($hasMembership): ?>
                      <i class="fas fa-check-circle text-success"></i>
                    <?php else: ?>
                      <i class="fas fa-star"></i>
                    <?php endif; ?>
                  </div>
                  <h5>Choose Membership</h5>
                  <p>Select the perfect plan for your fitness goals and schedule.</p>
                  <?php if (!$hasMembership): ?>
                    <a href="<?php echo BASE_URL; ?>memberships.php" class="btn btn-warning">
                      <i class="fas fa-star"></i> Choose Plan
                    </a>
                  <?php else: ?>
                    <div class="text-success fw-bold">
                      <i class="fas fa-check"></i> Membership Active
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <?php if ($hasProfile && $hasMembership): ?>
            <div class="alert alert-success mb-4">
              <h5><i class="fas fa-check-circle"></i> Setup Complete!</h5>
              <p class="mb-0">You're all set! Ready to start your fitness journey?</p>
            </div>
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-success btn-lg px-5 py-3">
              <i class="fas fa-tachometer-alt"></i> Go to Dashboard
            </a>
          <?php else: ?>
            <p class="text-muted">Complete the steps above to unlock your full L9 Fitness experience!</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.setup-progress {
  max-width: 600px;
  margin: 0 auto;
}

.setup-card {
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  height: 100%;
}

.setup-card:hover {
  border-color: rgba(255, 68, 68, 0.3);
  transform: translateY(-5px);
}

.setup-card.completed {
  border-color: rgba(34, 197, 94, 0.5);
  background: rgba(34, 197, 94, 0.1);
}

.setup-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #FF4444;
}

.setup-card.completed .setup-icon {
  color: #22c55e;
}

.setup-card h5 {
  color: #ffffff;
  margin-bottom: 1rem;
}

.setup-card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 1.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, #FF4444, #FFD700);
  border: none;
  border-radius: 25px;
  font-weight: 600;
  padding: 0.8rem 1.5rem;
}

.btn-warning {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  border: none;
  border-radius: 25px;
  font-weight: 600;
  padding: 0.8rem 1.5rem;
  color: #000;
}

.btn-success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  border: none;
  border-radius: 25px;
  font-weight: 600;
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>