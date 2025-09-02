<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

$pageTitle = "Fitness Classes";
$pageCSS = "/assets/css/classes.css";

// Handle class booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }
    
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Please log in to book classes']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $class_id = (int)($_POST['class_id'] ?? 0);
    $user_id = $_SESSION['user']['id'];
    
    try {
        if ($action === 'book') {
            // Check if class exists and get details
            $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
            $stmt->execute([$class_id]);
            $class = $stmt->fetch();
            
            if (!$class) {
                echo json_encode(['success' => false, 'message' => 'Class not found']);
                exit;
            }
            
            // Check if user already booked
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE member_id = ? AND class_id = ?");
            $stmt->execute([$user_id, $class_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already booked this class']);
                exit;
            }
            
            // Check available spots
            $stmt = $pdo->prepare("SELECT COUNT(*) as booked FROM bookings WHERE class_id = ? AND status = 'confirmed'");
            $stmt->execute([$class_id]);
            $booked_count = $stmt->fetchColumn();
            
            $available_spots = $class['capacity'] - $booked_count;
            $status = $available_spots > 0 ? 'confirmed' : 'waitlist';
            
            // Create booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, class_id, status, booking_date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $class_id, $status]);
            
            $message = $status === 'confirmed' ? 'Class booked successfully!' : 'Added to waitlist. You\'ll be notified if a spot opens up.';
            echo json_encode(['success' => true, 'message' => $message, 'status' => $status]);
            exit;
            
        } elseif ($action === 'cancel') {
            // Cancel booking
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ? AND class_id = ?");
            $stmt->execute([$user_id, $class_id]);
            
            // If someone was on waitlist, move them up
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE class_id = ? AND status = 'waitlist' ORDER BY booking_date ASC LIMIT 1");
            $stmt->execute([$class_id]);
            $waitlist_booking = $stmt->fetch();
            
            if ($waitlist_booking) {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$waitlist_booking['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Hero Section -->
<div class="classes-hero bg-dark text-white py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="hero-content">
          <h1 class="display-4 fw-bold mb-3">
            <span class="text-gradient">Fitness Classes</span> That Transform
          </h1>
          <p class="lead mb-4">
            Join our expert-led classes designed to push your limits, build strength, and achieve your fitness goals. 
            From high-intensity cardio to mindful yoga sessions - find your perfect workout.
          </p>
          
          <?php if (!is_logged_in()): ?>
            <div class="hero-actions">
              <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary btn-lg me-3"
                 title="Join thousands of members transforming their lives. Get unlimited access to classes, personal training, and exclusive events.">
                <i class="bi bi-lightning-charge me-2"></i>🔥 Start Your Transformation
              </a>
              <a href="<?php echo BASE_URL; ?>memberships.php" class="btn btn-outline-light btn-lg"
                 title="Explore our flexible membership plans designed to fit your lifestyle and fitness goals. From basic access to premium packages.">
                <i class="bi bi-gem me-2"></i>💎 View Membership Plans
              </a>
            </div>
          <?php else: ?>
            <div class="hero-stats">
              <div class="stat-item">
                <i class="bi bi-calendar-check text-primary"></i>
                <span>🎯 Browse classes below and reserve your spots!</span>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <div class="hero-image">
          <img src="<?php echo BASE_URL; ?>assets/img/fitness/hero.svg" alt="Fitness Classes" class="img-fluid" style="max-height: 300px;">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="classes-hero">
  <div class="container text-center py-5">
    <div class="hero-badge mb-3">
      <svg width="20" height="20" fill="currentColor" class="bi bi-heart-pulse-fill">
        <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15.09c3.221-2.219 5.298-4.25 6.525-6.09a4.5 4.5 0 0 0-.187-6.075 3.5 3.5 0 0 0-4.76.025L8 4.705 6.422 2.95a3.5 3.5 0 0 0-4.76-.025A4.5 4.5 0 0 0 1.475 9z"/>
        <path d="M8 6a.5.5 0 0 1 .5.5v1.716l.802.267c.112.037.233.077.349.145.11.063.17.138.192.235a.5.5 0 0 1-.293.605l-.755-.252V7.5A.5.5 0 0 1 8 6z"/>
      </svg>
      Beast Training Zones
    </div>
    <h1 class="display-4 fw-bold mb-3">Warrior<br><span class="text-gradient">Sessions</span></h1>
    <p class="lead mb-4">Join hardcore training sessions designed to forge champions and break limits</p>
  </div>
</div>

<div class="container py-5">
  <!-- Filter tabs -->
  <div class="class-filters mb-5">
    <nav class="nav nav-pills justify-content-center">
      <a class="nav-link active filter-btn" data-filter="all" 
         title="Show all available fitness classes across all categories">
        🌟 All Classes
      </a>
      <a class="nav-link filter-btn" data-filter="strength" 
         title="Build muscle and power with weight training, functional movements, and resistance exercises">
        💪 Strength Training
      </a>
      <a class="nav-link filter-btn" data-filter="cardio" 
         title="High-energy workouts to boost endurance, burn calories, and strengthen your cardiovascular system">
        ⚡ Cardio Blast
      </a>
      <a class="nav-link filter-btn" data-filter="yoga" 
         title="Mind-body connection through flowing movements, breathing techniques, and flexibility training">
        🧘 Yoga & Mindfulness
      </a>
      <a class="nav-link filter-btn" data-filter="dance" 
         title="Fun, rhythmic workouts combining dance choreography with full-body fitness training">
        💃 Dance Fitness
      </a>
    </nav>
  </div>

  <?php 
  // Get user's bookings if logged in
  $user_bookings = [];
  if (is_logged_in()) {
      $stmt = $pdo->prepare('SELECT class_id, status FROM bookings WHERE member_id = ?');
      $stmt->execute([$_SESSION['user']['id']]);
      while ($booking = $stmt->fetch()) {
          $user_bookings[$booking['class_id']] = $booking['status'];
      }
  }
  
  $stmt = $pdo->query('SELECT c.*, u.first_name, u.last_name FROM classes c JOIN users u ON u.id=c.trainer_id WHERE c.start_time > NOW() ORDER BY start_time ASC LIMIT 12');
  $classes = $stmt->fetchAll();
  ?>
  
  <div class="row g-4" id="classGrid">
    <?php if(!empty($classes)): ?>
      <?php foreach($classes as $index => $cls): ?>
        <?php
        // Assign class types based on class title/description for more realistic categorization
        $class_type = 'strength'; // default
        $title_lower = strtolower($cls['title']);
        
        if (strpos($title_lower, 'yoga') !== false || strpos($title_lower, 'meditation') !== false || strpos($title_lower, 'stretch') !== false) {
          $class_type = 'yoga';
        } elseif (strpos($title_lower, 'cardio') !== false || strpos($title_lower, 'hiit') !== false || strpos($title_lower, 'spin') !== false || strpos($title_lower, 'cycling') !== false) {
          $class_type = 'cardio';
        } elseif (strpos($title_lower, 'dance') !== false || strpos($title_lower, 'zumba') !== false || strpos($title_lower, 'rhythm') !== false) {
          $class_type = 'dance';
        } elseif (strpos($title_lower, 'strength') !== false || strpos($title_lower, 'weight') !== false || strpos($title_lower, 'power') !== false || strpos($title_lower, 'muscle') !== false) {
          $class_type = 'strength';
        } else {
          // Cycle through types for variety if no keywords match
          $class_types = ['strength', 'cardio', 'yoga', 'dance'];
          $class_type = $class_types[$index % 4];
        }
        
        $icons = [
          'strength' => 'bi-trophy-fill',
          'cardio' => 'bi-lightning-charge-fill', 
          'yoga' => 'bi-flower3',
          'dance' => 'bi-music-note-beamed'
        ];
        
        $class_descriptions = [
          'strength' => 'Build muscle, increase power, and sculpt your physique with progressive resistance training.',
          'cardio' => 'High-energy workout that boosts endurance, burns calories, and strengthens your cardiovascular system.',
          'yoga' => 'Connect mind and body through flowing movements, breathing techniques, and mindful stretching.',
          'dance' => 'Fun, rhythmic workout combining dance moves with fitness training for a full-body experience.'
        ];
        
        $colors = [
          'strength' => 'strength-class',
          'cardio' => 'cardio-class',
          'yoga' => 'yoga-class', 
          'dance' => 'dance-class'
        ];
        
        $start_time = new DateTime($cls['start_time']);
        $end_time = new DateTime($cls['end_time']);
        $duration = $start_time->diff($end_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        
        // Get actual booking count
        $stmt_bookings = $pdo->prepare("SELECT COUNT(*) as booked FROM bookings WHERE class_id = ? AND status = 'confirmed'");
        $stmt_bookings->execute([$cls['id']]);
        $booked_count = $stmt_bookings->fetchColumn();
        
        $capacity = $cls['capacity'] ?? 20;
        $spots_left = $capacity - $booked_count;
        $is_booked = isset($user_bookings[$cls['id']]);
        $booking_status = $user_bookings[$cls['id']] ?? null;
        ?>
        
        <div class="col-lg-4 col-md-6 class-item" data-type="<?php echo $class_type; ?>">
          <div class="class-card <?php echo $colors[$class_type]; ?>">
            <div class="class-header">
              <div class="class-type-badge">
                <i class="bi <?php echo $icons[$class_type]; ?>"></i>
                <?php echo ucfirst($class_type); ?>
              </div>
              
              <?php if($spots_left <= 5): ?>
                <div class="urgency-badge">Almost Full!</div>
              <?php endif; ?>
            </div>

            <div class="class-body">
              <div class="class-icon">
                <i class="bi <?php echo $icons[$class_type]; ?>"></i>
              </div>
              
              <h3 class="class-title"><?php echo htmlspecialchars($cls['title']); ?></h3>
              
              <!-- Enhanced class description -->
              <p class="class-description mb-3">
                <?php if (!empty($cls['description'])): ?>
                  <?php echo htmlspecialchars(substr($cls['description'], 0, 120)); ?><?php echo strlen($cls['description']) > 120 ? '...' : ''; ?>
                <?php else: ?>
                  <?php echo $class_descriptions[$class_type]; ?>
                <?php endif; ?>
              </p>
              
              <!-- Class image based on type -->
              <div class="class-image mb-3">
                <?php
                $class_images = [
                  'strength' => 'assets/img/fitness/strength.svg',
                  'cardio' => 'assets/img/fitness/spin.svg', 
                  'yoga' => 'assets/img/fitness/yoga.svg',
                  'dance' => 'assets/img/fitness/hero.svg'
                ];
                ?>
                <img src="<?php echo BASE_URL . $class_images[$class_type]; ?>" alt="<?php echo ucfirst($class_type); ?> Class" class="img-fluid rounded" style="height: 120px; width: 100%; object-fit: cover;">
              </div>
              
              <div class="class-meta">
                <div class="meta-item">
                  <i class="bi bi-calendar3"></i>
                  <?php echo $start_time->format('M d, Y'); ?>
                </div>
                <div class="meta-item">
                  <i class="bi bi-clock"></i>
                  <?php echo $start_time->format('g:i A'); ?> - <?php echo $end_time->format('g:i A'); ?>
                </div>
                <div class="meta-item">
                  <i class="bi bi-stopwatch"></i>
                  <?php echo $duration_minutes; ?> minutes
                </div>
                <div class="meta-item">
                  <i class="bi bi-geo-alt"></i>
                  <?php echo htmlspecialchars($cls['location'] ?? 'Main Studio'); ?>
                </div>
              </div>

              <p class="class-description"><?php echo htmlspecialchars($cls['description'] ?? 'An amazing fitness class that will challenge and inspire you.'); ?></p>

              <div class="trainer-info">
                <div class="trainer-avatar">
                  <?php echo strtoupper(substr($cls['first_name'], 0, 1) . substr($cls['last_name'], 0, 1)); ?>
                </div>
                <div class="trainer-details">
                  <div class="trainer-name"><?php echo htmlspecialchars($cls['first_name'] . ' ' . $cls['last_name']); ?></div>
                  <div class="trainer-title">Certified Trainer</div>
                </div>
              </div>

              <div class="class-capacity">
                <div class="capacity-bar">
                  <div class="capacity-fill" style="width: <?php echo ($capacity > 0 ? (($capacity - $spots_left) / $capacity) * 100 : 0); ?>%"></div>
                </div>
                <div class="capacity-text">
                  <?php if ($spots_left > 0): ?>
                    <span class="spots-left"><?php echo $spots_left; ?> spots left</span>
                  <?php else: ?>
                    <span class="spots-left text-warning">Class Full - Waitlist Available</span>
                  <?php endif; ?>
                  <span class="total-capacity"><?php echo $capacity; ?> max</span>
                </div>
              </div>

              <?php if (is_logged_in()): ?>
                <?php if ($is_booked): ?>
                  <button class="btn-book-class booked" data-class-id="<?php echo $cls['id']; ?>" data-action="cancel" 
                          title="You're currently enrolled in this class. Click to cancel your booking and free up your spot for others.">
                    <span class="btn-text">
                      <?php echo $booking_status === 'confirmed' ? '✅ Enrolled - Cancel Booking' : '⏳ On Waitlist - Remove from List'; ?>
                    </span>
                    <i class="bi bi-x-circle-fill"></i>
                  </button>
                <?php else: ?>
                  <button class="btn-book-class" data-class-id="<?php echo $cls['id']; ?>" data-action="book"
                          title="<?php echo $spots_left > 0 ? 'Reserve your spot in this class. You can cancel anytime before the session starts.' : 'Class is full, but you can join the waitlist. We\'ll notify you if a spot opens up!'; ?>">
                    <span class="btn-text">
                      <?php echo $spots_left > 0 ? '🎯 Reserve My Spot' : '📋 Join Waitlist'; ?>
                    </span>
                    <i class="bi <?php echo $spots_left > 0 ? 'bi-calendar-plus' : 'bi-clock-history'; ?>"></i>
                  </button>
                <?php endif; ?>
              <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn-book-class"
                   title="Create an account or login to book classes, track your progress, and join our fitness community.">
                  <span class="btn-text">🔐 Login to Book Classes</span>
                  <i class="bi bi-box-arrow-in-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="empty-state py-5">
          <div class="empty-state-content">
            <i class="bi bi-calendar-event display-1 text-primary mb-4"></i>
            <h3 class="mb-3">No Scheduled Classes Right Now</h3>
            <p class="lead text-muted mb-4">Don't worry! Check out our amazing class categories below and come back soon for upcoming sessions.</p>
            
            <!-- Call to action for non-logged in users -->
            <?php if (!is_logged_in()): ?>
              <div class="empty-state-actions">
                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary btn-lg me-3"
                   title="Create your free account and get access to all fitness classes, progress tracking, and exclusive member benefits.">
                  <i class="bi bi-person-plus me-2"></i>🚀 Join L9 Fitness Community
                </a>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-primary btn-lg"
                   title="Already a member? Sign in to book classes, view your schedule, and track your fitness journey.">
                  <i class="bi bi-box-arrow-in-right me-2"></i>🔑 Member Login
                </a>
              </div>
              <p class="text-muted mt-3"><small>💡 Create an account to book classes, track progress, and join our fitness community</small></p>
            <?php else: ?>
              <p class="text-muted">📅 Stay tuned for new class schedules!</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Class Types Section -->
<div class="class-types-section">
  <div class="container py-5">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-5 fw-bold mb-3">Class Categories</h2>
        <p class="lead">Find the perfect class type that matches your fitness goals</p>
      </div>
    </div>
    
    <div class="row g-4">
      <!-- Strength Training Category -->
      <div class="col-lg-3 col-md-6">
        <div class="category-card strength-category" data-bs-toggle="collapse" data-bs-target="#strengthDetails" aria-expanded="false">
          <div class="category-icon">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <h4>💪 Strength Training</h4>
          <p>Build muscle, increase power, and sculpt your physique with our strength-focused classes</p>
          <ul class="category-features">
            <li>✅ Weight lifting techniques</li>
            <li>✅ Functional movements</li>
            <li>✅ Progressive overload</li>
          </ul>
          <div class="category-expand">
            <i class="bi bi-chevron-down"></i>
            <span>Click to see what we do</span>
          </div>
          <!-- Expandable Details INSIDE CARD -->
          <div class="collapse mt-3" id="strengthDetails">
            <div class="category-details">
              <h5>🏋️ What You'll Do in Strength Classes:</h5>
              <div class="row g-2">
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Barbell Squats</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Deadlifts</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Bench Press</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Pull-ups</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Kettlebell Swings</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Overhead Press</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Core Strengthening</span>
                  </div>
                    <div class="activity-item">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>Form Correction</span>
                  </div>
                </div>
              </div>
              <div class="benefits mt-3">
                <h6>🎯 Benefits:</h6>
                <div class="benefit-tags">
                  <span class="badge bg-primary">Muscle Growth</span>
                  <span class="badge bg-success">Increased Strength</span>
                  <span class="badge bg-warning">Better Posture</span>
                  <span class="badge bg-info">Fat Loss</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Cardio Blast Category -->
      <div class="col-lg-3 col-md-6">
        <div class="category-card cardio-category" data-bs-toggle="collapse" data-bs-target="#cardioDetails" aria-expanded="false">
          <div class="category-icon">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <h4>⚡ Cardio Blast</h4>
          <p>High-energy classes that boost endurance and burn calories effectively</p>
          <ul class="category-features">
            <li>✅ HIIT workouts</li>
            <li>✅ Circuit training</li>
            <li>✅ Heart rate zones</li>
          </ul>
          <div class="category-expand">
            <i class="bi bi-chevron-down"></i>
            <span>Click to see what we do</span>
          </div>
          <!-- Expandable Details INSIDE CARD -->
          <div class="collapse mt-3" id="cardioDetails">
            <div class="category-details">
              <h5>🔥 What You'll Do in Cardio Classes:</h5>
              <div class="row g-2">
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Burpees</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Mountain Climbers</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Jump Squats</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Boxing Combos</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Sprint Intervals</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Plyometric Jumps</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Battle Ropes</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-warning"></i>
                    <span>Agility Drills</span>
                  </div>
                </div>
              </div>
              <div class="benefits mt-3">
                <h6>🎯 Benefits:</h6>
                <div class="benefit-tags">
                  <span class="badge bg-danger">Calorie Burn</span>
                  <span class="badge bg-primary">Endurance</span>
                  <span class="badge bg-success">Heart Health</span>
                  <span class="badge bg-warning">Energy Boost</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Yoga & Mindfulness Category -->
      <div class="col-lg-3 col-md-6">
        <div class="category-card yoga-category" data-bs-toggle="collapse" data-bs-target="#yogaDetails" aria-expanded="false">
          <div class="category-icon">
            <i class="bi bi-flower3"></i>
          </div>
          <h4>🧘 Yoga & Mindfulness</h4>
          <p>Connect mind and body through flowing movements and breathing techniques</p>
          <ul class="category-features">
            <li>✅ Flexibility & balance</li>
            <li>✅ Stress reduction</li>
            <li>✅ Mindful movement</li>
          </ul>
          <div class="category-expand">
            <i class="bi bi-chevron-down"></i>
            <span>Click to see what we do</span>
          </div>
          <!-- Expandable Details INSIDE CARD -->
          <div class="collapse mt-3" id="yogaDetails">
            <div class="category-details">
              <h5>🌸 What You'll Do in Yoga Classes:</h5>
              <div class="row g-2">
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Sun Salutations</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Warrior Poses</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Balance Poses</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Deep Stretching</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Breathing Exercises</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Meditation</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Core Strengthening</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-info"></i>
                    <span>Relaxation</span>
                  </div>
                </div>
              </div>
              <div class="benefits mt-3">
                <h6>🎯 Benefits:</h6>
                <div class="benefit-tags">
                  <span class="badge bg-info">Flexibility</span>
                  <span class="badge bg-success">Stress Relief</span>
                  <span class="badge bg-primary">Balance</span>
                  <span class="badge bg-secondary">Mental Clarity</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Dance Fitness Category -->
      <div class="col-lg-3 col-md-6">
        <div class="category-card dance-category" data-bs-toggle="collapse" data-bs-target="#danceDetails" aria-expanded="false">
          <div class="category-icon">
            <i class="bi bi-music-note-beamed"></i>
          </div>
          <h4>💃 Dance Fitness</h4>
          <p>Fun, rhythmic workouts that combine dance moves with fitness training</p>
          <ul class="category-features">
            <li>✅ Choreographed routines</li>
            <li>✅ Rhythm & coordination</li>
            <li>✅ Full-body workout</li>
          </ul>
          <div class="category-expand">
            <i class="bi bi-chevron-down"></i>
            <span>Click to see what we do</span>
          </div>
          <!-- Expandable Details INSIDE CARD -->
          <div class="collapse mt-3" id="danceDetails">
            <div class="category-details">
              <h5>🎵 What You'll Do in Dance Classes:</h5>
              <div class="row g-2">
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Zumba Moves</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Hip-Hop Choreography</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Latin Dance Steps</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Cardio Dance</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Bollywood Dance</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Dance Conditioning</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Rhythm Training</span>
                  </div>
                  <div class="activity-item">
                    <i class="bi bi-check-circle text-danger"></i>
                    <span>Cool-down Moves</span>
                  </div>
                </div>
              </div>
              <div class="benefits mt-3">
                <h6>🎯 Benefits:</h6>
                <div class="benefit-tags">
                  <span class="badge bg-danger">Fun Cardio</span>
                  <span class="badge bg-warning">Coordination</span>
                  <span class="badge bg-success">Mood Boost</span>
                  <span class="badge bg-primary">Social Connection</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
// Class filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const classItems = document.querySelectorAll('.class-item');
  
  filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      
      // Update active button
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      
      const filter = btn.dataset.filter;
      
      // Filter classes
      classItems.forEach(item => {
        if (filter === 'all' || item.dataset.type === filter) {
          item.style.display = 'block';
          item.style.animation = 'fadeInUp 0.5s ease';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
  
  // Enhanced booking functionality
  document.querySelectorAll('.btn-book-class').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // If it's a login link, don't handle booking
      if (this.getAttribute('href')) {
        window.location.href = this.getAttribute('href');
        return;
      }
      
      const classId = this.dataset.classId;
      const action = this.dataset.action;
      
      if (!classId) return;
      
      // Show loading state
      const originalContent = this.innerHTML;
      this.innerHTML = '<span class="btn-text">Processing...</span><i class="bi bi-arrow-clockwise spin"></i>';
      this.disabled = true;
      
      // Create form data
      const formData = new FormData();
      formData.append('class_id', classId);
      formData.append('action', action);
      formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
      
      // Send request
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success message
          showMessage(data.message, 'success');
          
          // Update button based on action
          if (action === 'book') {
            if (data.status === 'confirmed') {
              this.innerHTML = '<span class="btn-text">Booked - Click to Cancel</span><i class="bi bi-check-circle-fill"></i>';
              this.classList.add('booked');
              this.dataset.action = 'cancel';
            } else {
              this.innerHTML = '<span class="btn-text">On Waitlist - Click to Cancel</span><i class="bi bi-clock-fill"></i>';
              this.classList.add('waitlist');
              this.dataset.action = 'cancel';
            }
          } else {
            this.innerHTML = '<span class="btn-text">Book Class</span><i class="bi bi-arrow-right"></i>';
            this.classList.remove('booked', 'waitlist');
            this.dataset.action = 'book';
          }
        } else {
          // Show error message
          showMessage(data.message || 'Something went wrong', 'error');
        }
        
        this.disabled = false;
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('Network error. Please try again.', 'error');
        this.innerHTML = originalContent;
        this.disabled = false;
      });
    });
  });

  // Category card interactions
  document.querySelectorAll('.category-card').forEach(card => {
    card.addEventListener('click', function() {
      const targetId = this.getAttribute('data-bs-target');
      const targetElement = document.querySelector(targetId);
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      
      // Toggle aria-expanded
      this.setAttribute('aria-expanded', !isExpanded);
      
      // Add visual feedback
      if (!isExpanded) {
        this.style.transform = 'translateY(-4px)';
        this.style.borderColor = 'var(--l9-primary)';
        showMessage(`💡 Exploring ${this.querySelector('h4').textContent} activities!`, 'info');
      } else {
        this.style.transform = '';
        this.style.borderColor = '';
      }
    });
    
    // Add hover effects
    card.addEventListener('mouseenter', function() {
      if (this.getAttribute('aria-expanded') !== 'true') {
        this.style.transform = 'translateY(-2px)';
      }
    });
    
    card.addEventListener('mouseleave', function() {
      if (this.getAttribute('aria-expanded') !== 'true') {
        this.style.transform = '';
      }
    });
  });
            this.classList.remove('booked', 'waitlist');
            this.dataset.action = 'book';
          }
          
          // Refresh page after 2 seconds to update capacity
          setTimeout(() => {
            window.location.reload();
          }, 2000);
          
        } else {
          showMessage(data.message, 'error');
          this.innerHTML = originalContent;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred. Please try again.', 'error');
        this.innerHTML = originalContent;
      })
      .finally(() => {
        this.disabled = false;
      });
    });
  });
  
  // Message display function
  function showMessage(message, type) {
    // Remove existing messages
    document.querySelectorAll('.booking-message').forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} booking-message position-fixed`;
    messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    messageDiv.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
        ${message}
      </div>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
      messageDiv.remove();
    }, 3000);
  }
});
</script>

<style>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spin {
  animation: spin 1s linear infinite;
}

.btn-book-class.booked {
  background: linear-gradient(135deg, #28a745, #20c997) !important;
}

.btn-book-class.waitlist {
  background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
}

.btn-book-class:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.booking-message {
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
