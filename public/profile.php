<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_once __DIR__ . '/../app/helpers/validator.php'; 
require_login(); 

$pageTitle = "Profile Management";
$pageCSS = "/assets/css/profile.css";

$u = current_user(); 
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_profile') {
            $phone = sanitize($_POST['phone'] ?? ''); 
            $address = sanitize($_POST['address'] ?? '');
            
            $stmt = $pdo->prepare('UPDATE users SET phone=?, address=? WHERE id=?'); 
            $stmt->execute([$phone, $address, $u['id']]); 
            $message = 'Profile updated successfully!';
            
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Verify current password
            if (!password_verify($current_password, $u['password'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hashed_password, $u['id']]);
                $message = 'Password changed successfully!';
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred. Please try again.';
    }
}

// Get updated user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?'); 
$stmt->execute([$u['id']]); 
$row = $stmt->fetch();

// Get membership info
$membership = null;
$stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.user_id = ? AND m.status = "active" AND m.end_date > NOW()');
$stmt->execute([$u['id']]);
$membership = $stmt->fetch();

// Get recent bookings
$stmt = $pdo->prepare('SELECT c.title, c.start_time, b.status FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? ORDER BY c.start_time DESC LIMIT 5');
$stmt->execute([$u['id']]);
$recent_bookings = $stmt->fetchAll();

// Get workout stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ? AND status = "confirmed"');
$stmt->execute([$u['id']]);
$stats = $stmt->fetch();
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Profile Dashboard -->
<div class="profile-hero py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-4">
        <div class="profile-sidebar">
          <div class="profile-header text-center">
            <div class="profile-avatar">
              <?php echo strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
            </div>
            <h3 class="mt-3"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($row['email']); ?></p>
            
            <?php if ($membership): ?>
              <div class="membership-badge active">
                <i class="bi bi-trophy-fill"></i>
                <?php echo htmlspecialchars($membership['plan_name']); ?>
                <small class="d-block">Expires: <?php echo date('M d, Y', strtotime($membership['end_date'])); ?></small>
              </div>
            <?php else: ?>
              <div class="membership-badge inactive">
                <i class="bi bi-exclamation-triangle"></i>
                No Active Membership
                <a href="<?php echo BASE_URL; ?>memberships.php" class="d-block mt-1">Get Membership</a>
              </div>
            <?php endif; ?>
          </div>
          
          <!-- Quick Stats -->
          <div class="profile-stats mt-4">
            <div class="stat-item">
              <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
              <div class="stat-label">Classes Attended</div>
            </div>
            <div class="stat-item">
              <div class="stat-number"><?php echo date('M Y', strtotime($row['created_at'])); ?></div>
              <div class="stat-label">Member Since</div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-8">
        <div class="profile-content">
          <?php if($message): ?>
            <div class="alert alert-success alert-modern mb-4">
              <i class="bi bi-check-circle-fill me-2"></i>
              <?php echo $message; ?>
            </div>
          <?php endif; ?>
          
          <?php if($error): ?>
            <div class="alert alert-danger alert-modern mb-4">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <?php echo $error; ?>
            </div>
          <?php endif; ?>
          
          <!-- Profile Tabs -->
          <div class="profile-tabs">
            <ul class="nav nav-tabs" id="profileTabs">
              <li class="nav-item">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                  <i class="bi bi-person me-2"></i>Personal Info
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                  <i class="bi bi-lock me-2"></i>Security
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">
                  <i class="bi bi-calendar me-2"></i>My Bookings
                </button>
              </li>
            </ul>
          </div>
          
          <div class="tab-content" id="profileTabsContent">
            <!-- Personal Info Tab -->
            <div class="tab-pane fade show active" id="info">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Personal Information</h4>
                  <p class="text-muted">Update your profile details</p>
                </div>
                
                <form method="post" class="profile-form">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="action" value="update_profile">
                  
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="firstName" 
                               value="<?php echo htmlspecialchars($row['first_name']); ?>" disabled>
                        <label for="firstName">
                          <i class="bi bi-person me-2"></i>First Name
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="lastName" 
                               value="<?php echo htmlspecialchars($row['last_name']); ?>" disabled>
                        <label for="lastName">
                          <i class="bi bi-person me-2"></i>Last Name
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="email" class="form-control" id="email" 
                               value="<?php echo htmlspecialchars($row['email']); ?>" disabled>
                        <label for="email">
                          <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>">
                        <label for="phone">
                          <i class="bi bi-telephone me-2"></i>Phone Number
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>">
                        <label for="address">
                          <i class="bi bi-geo-alt me-2"></i>Address
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            
            <!-- Security Tab -->
            <div class="tab-pane fade" id="password">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Change Password</h4>
                  <p class="text-muted">Keep your account secure</p>
                </div>
                
                <form method="post" class="profile-form">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="action" value="change_password">
                  
                  <div class="row g-4">
                    <div class="col-12">
                      <div class="form-floating">
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <label for="current_password">
                          <i class="bi bi-lock me-2"></i>Current Password
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <label for="new_password">
                          <i class="bi bi-key me-2"></i>New Password
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                        <label for="confirm_password">
                          <i class="bi bi-key me-2"></i>Confirm Password
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-shield-lock me-2"></i>Change Password
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            
            <!-- Bookings Tab -->
            <div class="tab-pane fade" id="bookings">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Recent Bookings</h4>
                  <p class="text-muted">Your class booking history</p>
                </div>
                
                <?php if (!empty($recent_bookings)): ?>
                  <div class="booking-list">
                    <?php foreach ($recent_bookings as $booking): ?>
                      <div class="booking-item">
                        <div class="booking-info">
                          <h5><?php echo htmlspecialchars($booking['title']); ?></h5>
                          <p class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?php echo date('M d, Y g:i A', strtotime($booking['start_time'])); ?>
                          </p>
                        </div>
                        <div class="booking-status">
                          <?php if ($booking['status'] === 'confirmed'): ?>
                            <span class="badge bg-success">Confirmed</span>
                          <?php elseif ($booking['status'] === 'waitlist'): ?>
                            <span class="badge bg-warning">Waitlist</span>
                          <?php else: ?>
                            <span class="badge bg-secondary"><?php echo ucfirst($booking['status']); ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  
                  <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-outline-primary">
                      <i class="bi bi-plus-circle me-2"></i>Book More Classes
                    </a>
                  </div>
                <?php else: ?>
                  <div class="empty-state text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                    <h5>No Bookings Yet</h5>
                    <p class="text-muted">Start your fitness journey by booking your first class!</p>
                    <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-primary">
                      <i class="bi bi-calendar-plus me-2"></i>Browse Classes
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
