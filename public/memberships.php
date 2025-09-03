<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

$pageTitle = "Membership Plans";
$pageCSS = "/assets/css/membership.css";

// Handle membership purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }
    
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Please log in to purchase membership']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    $user_id = $_SESSION['user']['id'];
    
    try {
        if ($action === 'purchase') {
            // Get plan details
            $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ? AND is_active = 1");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();
            
            if (!$plan) {
                echo json_encode(['success' => false, 'message' => 'Invalid membership plan']);
                exit;
            }
            
            // Check if user has active membership
            $stmt = $pdo->prepare("SELECT * FROM memberships WHERE member_id = ? AND status = 'active' AND end_date > NOW()");
            $stmt->execute([$user_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Extend existing membership
                $start_date = max(new DateTime(), new DateTime($existing['end_date']));
            } else {
                $start_date = new DateTime();
            }
            
            $end_date = clone $start_date;
            $end_date->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
            
            // Create membership record
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE memberships SET plan_id = ?, end_date = ?, status = 'active' WHERE member_id = ? AND status = 'active'");
                $stmt->execute([$plan_id, $end_date->format('Y-m-d H:i:s'), $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
                $stmt->execute([$user_id, $plan_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s')]);
            }
            
            // Create payment record (simulated)
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, type, description, status, payment_date) VALUES (?, ?, 'membership', ?, 'completed', NOW())");
            $stmt->execute([$user_id, $plan['price'], $plan['name'] . ' membership purchase']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Membership activated successfully! Welcome to the Beast Pit!',
                'end_date' => $end_date->format('M d, Y')
            ]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="membership-hero">
  <div class="container text-center py-5">
    <div class="hero-badge mb-3">
      <svg width="20" height="20" fill="currentColor" class="bi bi-trophy-fill">
        <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33.076 33.076 0 0 1 2.5.5zm.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935zm10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935z"/>
      </svg>
      WARRIOR MEMBERSHIPS
    </div>
    <h1 class="display-4 fw-bold mb-3">CHOOSE YOUR<br><span class="text-gradient">BATTLE PLAN</span></h1>
    <p class="lead mb-4">Select your weapon for total domination. Every warrior needs the right gear for victory.</p>
  </div>
</div>

<div class="container py-5">
  <?php 
  // Get user's current membership if logged in
  $current_membership = null;
  if (is_logged_in()) {
      $stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = "active" AND m.end_date > NOW()');
      $stmt->execute([$_SESSION['user']['id']]);
      $current_membership = $stmt->fetch();
  }
  
  $plans = $pdo->query('SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC')->fetchAll(); 
  ?>
  
  <?php if ($current_membership): ?>
    <div class="alert alert-success text-center mb-5">
      <div class="d-flex align-items-center justify-content-center">
        <i class="bi bi-trophy-fill me-2"></i>
        <div>
          <strong>Active Beast Mode:</strong> <?php echo htmlspecialchars($current_membership['plan_name']); ?> 
          <span class="ms-2">Expires: <?php echo date('M d, Y', strtotime($current_membership['end_date'])); ?></span>
        </div>
      </div>
    </div>
  <?php endif; ?>
  
  <div class="row g-4 justify-content-center">
    <?php if(!empty($plans)): ?>
      <?php foreach($plans as $index => $p): ?>
        <div class="col-lg-4 col-md-6">
          <div class="membership-card <?php echo $index === 1 ? 'featured' : ''; ?>">
            <?php if($index === 1): ?>
              <div class="popular-badge">MOST BRUTAL</div>
            <?php endif; ?>
            
            <div class="card-header">
              <div class="plan-icon">
                <?php if($p['name'] === 'Monthly'): ?>
                  <svg width="32" height="32" fill="currentColor" class="bi bi-calendar-month">
                    <path d="M2.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1H2zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V5z"/>
                    <path d="M2.5 7.5A.5.5 0 0 1 3 7h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5v-1zM3 10.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                  </svg>
                <?php elseif($p['name'] === 'Quarterly'): ?>
                  <svg width="32" height="32" fill="currentColor" class="bi bi-speedometer2">
                    <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                    <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                  </svg>
                <?php else: ?>
                  <svg width="32" height="32" fill="currentColor" class="bi bi-gem">
                    <path d="M3.1.7a.5.5 0 0 1 .4-.2h9a.5.5 0 0 1 .4.2l2.976 3.974c.149.185.156.45.01.644L8.4 15.3a.5.5 0 0 1-.8 0L.1 5.3a.5.5 0 0 1 0-.6l3-4zm11.386 3.785-1.806-2.41-.776 2.413 2.582-.003zm-3.633.004.961-2.989H4.186l.963 2.995 5.704-.006zM5.47 5.495 8 13.366l2.532-7.876-5.062.005zm-1.371-.999-.78-2.422-1.818 2.425 2.598-.003zM1.499 5.5l5.113 6.817-2.192-6.82L1.5 5.5zm7.889 6.817 5.123-6.83-2.928.002L8.388 12.317z"/>
                  </svg>
                <?php endif; ?>
              </div>
              <h3 class="plan-name"><?php echo htmlspecialchars($p['name']); ?></h3>
              <div class="plan-price">
                <span class="currency">$</span>
                <span class="amount"><?php echo number_format($p['price'], 0); ?></span>
              </div>
              <div class="plan-duration"><?php echo (int)$p['duration_days']; ?> days access</div>
            </div>

            <div class="card-body">
              <p class="plan-description"><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
              
              <ul class="feature-list">
                <?php if($p['name'] === 'Monthly'): ?>
                  <li><i class="bi bi-check-circle-fill"></i> Full battle ground access</li>
                  <li><i class="bi bi-check-circle-fill"></i> Beast training sessions</li>
                  <li><i class="bi bi-check-circle-fill"></i> Warrior locker room</li>
                  <li><i class="bi bi-check-circle-fill"></i> Essential weapons</li>
                <?php elseif($p['name'] === 'Quarterly'): ?>
                  <li><i class="bi bi-check-circle-fill"></i> Everything in Monthly</li>
                  <li><i class="bi bi-check-circle-fill"></i> Premium war machines</li>
                  <li><i class="bi bi-check-circle-fill"></i> Recovery chambers</li>
                  <li><i class="bi bi-check-circle-fill"></i> Nutrition warfare plan</li>
                  <li><i class="bi bi-check-circle-fill"></i> Bring your squad (2/month)</li>
                <?php else: ?>
                  <li><i class="bi bi-check-circle-fill"></i> Everything in Quarterly</li>
                  <li><i class="bi bi-check-circle-fill"></i> Personal destroyer coaching</li>
                  <li><i class="bi bi-check-circle-fill"></i> Unlimited army passes</li>
                  <li><i class="bi bi-check-circle-fill"></i> VIP battle priority</li>
                  <li><i class="bi bi-check-circle-fill"></i> 24/7 beast mode access</li>
                  <li><i class="bi bi-check-circle-fill"></i> Recovery warrior discount</li>
                <?php endif; ?>
              </ul>

              <?php if (is_logged_in()): ?>
                <button class="btn-membership <?php echo $index === 1 ? 'btn-featured' : ''; ?>" 
                        data-plan-id="<?php echo $p['id']; ?>" 
                        data-plan-name="<?php echo htmlspecialchars($p['name']); ?>"
                        data-plan-price="<?php echo $p['price']; ?>">
                  <span class="btn-text">
                    <?php echo $current_membership ? 'UPGRADE POWER' : 'CLAIM POWER'; ?>
                  </span>
                  <i class="bi bi-arrow-right"></i>
                </button>
              <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn-membership <?php echo $index === 1 ? 'btn-featured' : ''; ?>">
                  <span class="btn-text">LOGIN TO CLAIM</span>
                  <i class="bi bi-box-arrow-in-right"></i>
                </a>
              <?php endif; ?>
              
              <div class="money-back">
                <i class="bi bi-shield-check"></i>
                30-day BEAST GUARANTEE
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="empty-state">
          <i class="bi bi-exclamation-triangle display-1 text-muted mb-3"></i>
          <h3>NO WARRIORS FOUND</h3>
          <p class="text-muted">No battle plans available. The gym awaits your return.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Benefits Section -->
<div class="benefits-section">
  <div class="container py-5">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-5 fw-bold mb-3">WHY CHOOSE THE BEAST PIT?</h2>
        <p class="lead">Experience raw power with our hardcore facilities and savage training protocols</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <h4>24/7 WAR ZONE</h4>
          <p>Battle never sleeps. Train whenever the beast awakens</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="bi bi-people"></i>
          </div>
          <h4>BEAST MASTERS</h4>
          <p>Elite warriors who forge champions from the weak</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="bi bi-gear-wide-connected"></i>
          </div>
          <h4>WAR MACHINES</h4>
          <p>Brutal equipment designed to destroy your limits</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="benefit-card">
          <div class="benefit-icon">
            <i class="bi bi-heart-pulse"></i>
          </div>
          <h4>BATTLE GROUPS</h4>
          <p>Savage sessions from warrior yoga to annihilation HIIT</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Membership purchase functionality
  document.querySelectorAll('.btn-membership[data-plan-id]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      const planId = this.dataset.planId;
      const planName = this.dataset.planName;
      const planPrice = this.dataset.planPrice;
      
      // Show confirmation modal
      if (confirm(`Purchase ${planName} membership for $${planPrice}?`)) {
        // Show loading state
        const originalContent = this.innerHTML;
        this.innerHTML = '<span class="btn-text">Processing...</span><i class="bi bi-arrow-clockwise spin"></i>';
        this.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('plan_id', planId);
        formData.append('action', 'purchase');
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
        
        // Send request
        fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage(data.message, 'success');
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
              window.location.href = '<?php echo BASE_URL; ?>dashboard.php';
            }, 2000);
            
          } else {
            showMessage(data.message, 'error');
            this.innerHTML = originalContent;
            this.disabled = false;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('An error occurred. Please try again.', 'error');
          this.innerHTML = originalContent;
          this.disabled = false;
        });
      }
    });
  });
  
  // Message display function
  function showMessage(message, type) {
    // Remove existing messages
    document.querySelectorAll('.membership-message').forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} membership-message position-fixed`;
    messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 350px;';
    messageDiv.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
        ${message}
      </div>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto-hide after 4 seconds
    setTimeout(() => {
      messageDiv.remove();
    }, 4000);
  }
});
</script>

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spin {
  animation: spin 1s linear infinite;
}

.btn-membership:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.membership-message {
  animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
