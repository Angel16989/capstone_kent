<?php
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

$user = get_current_user();
$success_message = '';
$error_message = '';

// Handle class booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_class'])) {
    if (!$user) {
        $error_message = 'Please login to book classes.';
    } else {
        $class_id = (int)$_POST['class_id'];
        
        try {
            // Check if user has active membership
            if (!is_array($user) || !isset($user['id'])) {
                $error_message = 'Invalid user session. Please login again.';
            } else {
                $stmt = $pdo->prepare("SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = 'active' AND m.end_date > NOW()");
                $stmt->execute([$user['id']]);
                $membership = $stmt->fetch();
            
            if (!$membership) {
                $error_message = 'You need an active membership to book classes. <a href="memberships.php" style="color: #00ff88;">Purchase a membership</a>';
            } else {
                // Check if class exists
                $stmt = $pdo->prepare("SELECT c.*, u.first_name, u.last_name FROM classes c JOIN users u ON c.trainer_id = u.id WHERE c.id = ?");
                $stmt->execute([$class_id]);
                $class = $stmt->fetch();
                
                if (!$class) {
                    $error_message = 'Class not found.';
                } else {
                    // Check current bookings
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE class_id = ? AND status = 'booked'");
                    $stmt->execute([$class_id]);
                    $current_bookings = $stmt->fetchColumn();
                    
                    if ($current_bookings >= $class['capacity']) {
                        $error_message = 'This class is fully booked.';
                    } else {
                        // Check if already booked
                        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE member_id = ? AND class_id = ? AND status != 'cancelled'");
                        $stmt->execute([$user['id'], $class_id]);
                        
                        if ($stmt->fetch()) {
                            $error_message = 'You have already booked this class.';
                        } else {
                            // Book the class
                            $stmt = $pdo->prepare("INSERT INTO bookings (member_id, class_id, status, booked_at) VALUES (?, ?, 'booked', NOW())");
                            $stmt->execute([$user['id'], $class_id]);
                            
                            $success_message = "Successfully booked '{$class['title']}' on " . date('M j, Y \a\t g:i A', strtotime($class['start_time'])) . "!";
                        }
                    }
                }
            }
            }
        } catch (Exception $e) {
            $error_message = 'Booking failed. Please try again. Error: ' . $e->getMessage();
        }
    }
}

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    if ($user && is_array($user) && isset($user['id'])) {
        $booking_id = (int)$_POST['booking_id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND member_id = ?");
            $stmt->execute([$booking_id, $user['id']]);
            
            if ($stmt->rowCount() > 0) {
                $success_message = 'Booking cancelled successfully.';
            } else {
                $error_message = 'Could not cancel booking.';
            }
        } catch (Exception $e) {
            $error_message = 'Cancellation failed. Please try again.';
        }
    } else {
        $error_message = 'Invalid user session. Please login again.';
    }
}

// Get classes with booking counts
try {
    $stmt = $pdo->query("SELECT c.*, u.first_name, u.last_name, 
                        (SELECT COUNT(*) FROM bookings WHERE class_id = c.id AND status = 'booked') as current_bookings 
                        FROM classes c 
                        JOIN users u ON c.trainer_id = u.id 
                        WHERE c.start_time > NOW() 
                        ORDER BY c.start_time ASC LIMIT 20");
    $classes = $stmt->fetchAll();
} catch (Exception $e) {
    $classes = [];
}

// Get user's bookings if logged in
$user_bookings = [];
if ($user && is_array($user) && isset($user['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT class_id, id as booking_id FROM bookings WHERE member_id = ? AND status = 'booked'");
        $stmt->execute([$user['id']]);
        $results = $stmt->fetchAll();
        
        if (is_array($results) && !empty($results)) {
            $user_bookings = array_column($results, 'booking_id', 'class_id');
        }
    } catch (Exception $e) {
        $user_bookings = [];
    }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Custom Classes Page Styles -->
<style>
/* --- L9 Fitness Classes Styles — ULTRA INTENSE HARDCORE MODE --- */
:root {
  --l9-primary: #FF4444;
  --l9-accent: #FFD700;
  --l9-secondary: #00CCFF;
  --l9-tertiary: #FF6B6B;
  --l9-quaternary: #4ECDC4;
  --l9-dark: #0a0a0a;
  --l9-card-bg: rgba(255,255,255,.05);
  --l9-border: rgba(255,68,68,.12);
  --l9-primary-rgb: 255, 68, 68;
  --l9-accent-rgb: 255, 215, 0;
  --l9-secondary-rgb: 0, 204, 255;
  
  /* Class type colors - ULTRA INTENSE */
  --strength-color: #ff4444;
  --cardio-color: #00ccff;
  --yoga-color: #ffd700;
  --dance-color: #ff6b6b;
}

body {
  background: 
    linear-gradient(135deg, #0a0a0a 0%, #1a0b2e 25%, #16213e 50%, #0f1419 75%, #0a0a0a 100%),
    radial-gradient(circle at 25% 75%, rgba(255, 68, 68, 0.12) 0%, transparent 50%),
    radial-gradient(circle at 75% 25%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(0, 204, 255, 0.06) 0%, transparent 50%);
  color: #e9ecff;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: 
    radial-gradient(2px 2px at 25px 35px, rgba(255,68,68,.5), transparent),
    radial-gradient(2px 2px at 50px 80px, rgba(255,215,0,.4), transparent),
    radial-gradient(1px 1px at 100px 50px, rgba(0,204,255,.4), transparent);
  background-repeat: repeat;
  background-size: 140px 120px;
  animation: ultra-particle-drift 35s linear infinite;
  pointer-events: none;
  opacity: 0.5;
  z-index: -1;
}

@keyframes ultra-particle-drift {
  0% { transform: translateY(0px) translateX(0px) rotate(0deg); }
  25% { transform: translateY(-40px) translateX(15px) rotate(90deg); }
  50% { transform: translateY(-80px) translateX(-10px) rotate(180deg); }
  75% { transform: translateY(-120px) translateX(20px) rotate(270deg); }
  100% { transform: translateY(-160px) translateX(0px) rotate(360deg); }
}

.container { 
  max-width: 1200px; 
  margin: 0 auto; 
  padding: 20px;
}

h1 {
  text-align: center;
  font-size: 3.5rem;
  font-weight: 900;
  margin: 40px 0;
  background: linear-gradient(90deg, var(--l9-primary), var(--l9-accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.class-card {
  background: 
    linear-gradient(135deg, rgba(255,255,255,.06) 0%, rgba(255,255,255,.01) 100%),
    rgba(0,0,0,.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,68,68,.15);
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  height: 100%;
  position: relative;
  box-shadow: 
    0 8px 32px rgba(0,0,0,.4),
    0 0 0 1px rgba(255,255,255,.05),
    inset 0 1px 0 rgba(255,255,255,.08);
  margin-bottom: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
}

.class-image {
  width: 100%;
  height: 200px;
  border-radius: 16px 16px 0 0;
  overflow: hidden;
  position: relative;
  background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
  background-size: cover;
  background-position: center;
  display: block;
}

.class-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  filter: brightness(0.9) contrast(1.15) saturate(1.1);
  display: block;
  border-radius: 16px 16px 0 0;
}

.class-image:hover img {
  transform: scale(1.05);
  filter: brightness(0.95) contrast(1.2) saturate(1.15);
}

.class-image::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%),
    linear-gradient(45deg, rgba(255,68,68,0.05) 0%, rgba(255,215,0,0.05) 100%);
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.class-card:hover .class-image::after {
  background: 
    linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.4) 100%),
    linear-gradient(45deg, rgba(255,68,68,0.1) 0%, rgba(255,215,0,0.1) 100%);
}

/* Fallback backgrounds for failed images */
.class-image.fallback-bg {
  background: 
    linear-gradient(135deg, var(--l9-primary), var(--l9-accent)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">🏋️</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
  background-repeat: no-repeat, no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
}

.class-image.strength-class.fallback-bg {
  background: 
    linear-gradient(135deg, rgba(255,68,68,.9), rgba(255,107,107,.7)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">🏋️</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
}

.class-image.cardio-class.fallback-bg {
  background: 
    linear-gradient(135deg, rgba(0,204,255,.9), rgba(81,230,217,.7)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">🏃</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
}

.class-image.yoga-class.fallback-bg {
  background: 
    linear-gradient(135deg, rgba(255,215,0,.9), rgba(198,247,237,.7)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">🧘</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
}

.class-image.dance-class.fallback-bg {
  background: 
    linear-gradient(135deg, rgba(255,107,107,.9), rgba(246,135,179,.7)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">💃</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
}

.class-image.hiit-class.fallback-bg {
  background: 
    linear-gradient(135deg, rgba(255,68,68,.9), rgba(0,204,255,.7)),
    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text y=".9em" font-size="50">⚡</text></svg>');
  background-size: cover, 60px 60px;
  background-position: center, center;
}

.class-content {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.class-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 16px;
  color: #f3f5ff;
  text-align: center;
  line-height: 1.3;
}

.class-meta {
  margin-bottom: 16px;
}

.class-actions {
  margin-top: auto;
  padding-top: 16px;
}

.class-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, 
    rgba(255,68,68,.15), 
    rgba(255,215,0,.12), 
    rgba(0,204,255,.1), 
    rgba(255,107,107,.08)
  );
  opacity: 0;
  transition: opacity 0.6s ease;
  border-radius: 26px;
  z-index: -1;
}

.class-card:hover::before {
  opacity: 1;
}

.class-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 
    0 20px 60px rgba(0,0,0,.6),
    0 0 40px rgba(255,68,68,.25),
    0 0 80px rgba(255,215,0,.15),
    0 0 0 1px rgba(255,215,0,.3),
    inset 0 1px 0 rgba(255,255,255,.15);
  border-color: rgba(255,215,0,.3);
}

.class-title { 
  background: linear-gradient(90deg, var(--l9-primary), var(--l9-accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8rem; 
  font-weight: 700;
  margin: 0 0 20px 0; 
  text-transform: uppercase;
  letter-spacing: 1px;
  text-align: center;
}

/* Enhanced Buttons */
.btn { 
  font-weight: 600;
  border-radius: 12px;
  padding: 12px 24px;
  transition: all 0.3s ease;
  text-transform: none;
  letter-spacing: 0.3px;
  text-decoration: none;
  display: inline-block;
  margin: 5px;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(90deg, var(--l9-primary), #5dd8ff);
  color: white;
  box-shadow: 0 6px 20px rgba(255,68,68,.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255,68,68,.4);
  color: white;
}

.btn-success {
  background: linear-gradient(90deg, var(--l9-accent), #00b894);
  color: #000;
  box-shadow: 0 6px 20px rgba(255,215,0,.3);
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255,215,0,.4);
  color: #000;
}

.btn-danger {
  background: linear-gradient(90deg, #ff6b6b, #ee5a52);
  color: white;
  box-shadow: 0 6px 20px rgba(255,107,107,.3);
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255,107,107,.4);
  color: white;
}

.btn-outline {
  border: 2px solid var(--l9-primary);
  color: var(--l9-primary);
  background: transparent;
}

.btn-outline:hover {
  background: var(--l9-primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255,68,68,.3);
}

/* Enhanced Class Cards Meta */
.class-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin: 20px 0;
}

.meta-item {
  display: flex;
  align-items: center;
  color: #bfc5ff;
  font-size: 0.95rem;
}

.meta-item strong {
  color: var(--l9-accent);
  margin-right: 8px;
  min-width: 60px;
}

.class-description {
  color: #cdd3ff;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 20px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.trainer-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding: 12px;
  background: rgba(255,255,255,.05);
  border-radius: 12px;
}

.trainer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 0.85rem;
}

.trainer-name {
  font-weight: 600;
  color: #f3f5ff;
  font-size: 0.9rem;
}

.trainer-title {
  font-size: 0.8rem;
  color: #a8b3ff;
}

.class-capacity {
  margin-bottom: 20px;
}

.capacity-bar {
  height: 6px;
  background: rgba(255,255,255,.1);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 8px;
}

.capacity-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--l9-accent), #00b894);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.capacity-text {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
}

.spots-left {
  color: var(--l9-accent);
  font-weight: 600;
}

.total-capacity {
  color: #8892b0;
}

/* Status Badges */
.status-badge {
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-block;
  margin: 5px;
}

.status-booked {
  background: rgba(255,215,0,.2);
  color: var(--l9-accent);
  border: 1px solid var(--l9-accent);
}

.status-full {
  background: rgba(255,107,107,.2);
  color: #ff6b6b;
  border: 1px solid #ff6b6b;
}

/* Alerts */
.alert {
  padding: 20px;
  border-radius: 12px;
  margin: 20px 0;
  text-align: center;
  font-weight: 600;
  border-left: 4px solid;
}

.alert-success {
  background: rgba(52,211,153,.15);
  color: #ccffe9;
  border-left-color: var(--l9-accent);
}

.alert-danger {
  background: rgba(255,107,107,.15);
  color: #ffe6e6;
  border-left-color: #ff6b6b;
}

/* Empty State */
.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-state .bi {
  color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
  h1 { font-size: 2.5rem; }
  .class-card { padding: 20px; margin: 15px 0; }
  .btn { padding: 10px 20px; font-size: 0.9rem; }
  .class-meta { grid-template-columns: 1fr; }
}
    </style>
<!-- Hero Section -->
<div class="classes-hero bg-dark text-white py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="hero-content">
          <h1 class="display-4 fw-bold mb-3">
            <span class="text-gradient">🏋️ L9 FITNESS CLASSES 🏋️</span>
          </h1>
          <p class="lead mb-4">
            Transform your body with our expert-led fitness classes. Push your limits and achieve your goals.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                ✅ <?= $success_message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                ❌ <?= $error_message ?>
            </div>
        <?php endif; ?>
        
    <div class="row">
        <?php if (empty($classes)): ?>
            <div class="col-12">
                <div class="class-card text-center">
                    <div class="class-content">
                        <h3>No classes available</h3>
                        <p>Please run the class setup first: <a href="../setup_classes.php" style="color: var(--l9-accent);">Setup Classes</a></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($classes as $class): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <?php
                $is_booked = isset($user_bookings[$class['id']]);
                $is_full = $class['current_bookings'] >= $class['capacity'];
                ?>
                <div class="class-card">
                    <?php
                    // Determine class image and CSS class based on type or name
                    $class_images = [
                        // Strength Training Images
                        'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600&h=400&fit=crop&crop=center', // deadlift
                        'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center', // gym workout
                        'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600&h=400&fit=crop&crop=center', // gym equipment
                        'https://images.unsplash.com/photo-1583500178690-f7b6da9b5a99?w=600&h=400&fit=crop&crop=center', // barbell
                        'https://images.unsplash.com/photo-1605296867424-35fc25c9212a?w=600&h=400&fit=crop&crop=center', // kettlebell
                        'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600&h=400&fit=crop&crop=center', // dumbbell
                        'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600&h=400&fit=crop&crop=center', // squat
                        'https://images.unsplash.com/photo-1566351433537-7f62bce2e37c?w=600&h=400&fit=crop&crop=center', // bench press
                    ];
                    
                    $class_image = $class_images[array_rand($class_images)]; // random default
                    $class_css_class = '';
                    $class_name_lower = strtolower($class['title']);
                    
                    // Strength Training
                    if (strpos($class_name_lower, 'strength') !== false || strpos($class_name_lower, 'weight') !== false || strpos($class_name_lower, 'muscle') !== false || strpos($class_name_lower, 'power') !== false || strpos($class_name_lower, 'lift') !== false) {
                        $strength_images = [
                            'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1583500178690-f7b6da9b5a99?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1605296867424-35fc25c9212a?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $strength_images[array_rand($strength_images)];
                        $class_css_class = 'strength-class';
                    }
                    // Yoga Classes
                    elseif (strpos($class_name_lower, 'yoga') !== false || strpos($class_name_lower, 'zen') !== false || strpos($class_name_lower, 'mindful') !== false || strpos($class_name_lower, 'flow') !== false || strpos($class_name_lower, 'meditation') !== false) {
                        $yoga_images = [
                            'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1506629905607-91fc45c8005f?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $yoga_images[array_rand($yoga_images)];
                        $class_css_class = 'yoga-class';
                    }
                    // Spin/Cycling
                    elseif (strpos($class_name_lower, 'spin') !== false || strpos($class_name_lower, 'cycle') !== false || strpos($class_name_lower, 'bike') !== false) {
                        $spin_images = [
                            'https://images.unsplash.com/photo-1558618666-fbd6c13b7725?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1616803689943-5601631c7fec?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $spin_images[array_rand($spin_images)];
                        $class_css_class = 'cardio-class';
                    }
                    // Cardio
                    elseif (strpos($class_name_lower, 'cardio') !== false || strpos($class_name_lower, 'run') !== false || strpos($class_name_lower, 'endurance') !== false || strpos($class_name_lower, 'aerobic') !== false) {
                        $cardio_images = [
                            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1506629905607-91fc45c8005f?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $cardio_images[array_rand($cardio_images)];
                        $class_css_class = 'cardio-class';
                    }
                    // Dance
                    elseif (strpos($class_name_lower, 'dance') !== false || strpos($class_name_lower, 'zumba') !== false || strpos($class_name_lower, 'rhythm') !== false) {
                        $dance_images = [
                            'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1524863479829-916d8e77f114?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $dance_images[array_rand($dance_images)];
                        $class_css_class = 'dance-class';
                    }
                    // HIIT/Circuit
                    elseif (strpos($class_name_lower, 'hiit') !== false || strpos($class_name_lower, 'interval') !== false || strpos($class_name_lower, 'circuit') !== false || strpos($class_name_lower, 'bootcamp') !== false || strpos($class_name_lower, 'crossfit') !== false) {
                        $hiit_images = [
                            'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1605296867424-35fc25c9212a?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $hiit_images[array_rand($hiit_images)];
                        $class_css_class = 'hiit-class';
                    }
                    // Boxing/Martial Arts
                    elseif (strpos($class_name_lower, 'boxing') !== false || strpos($class_name_lower, 'kickboxing') !== false || strpos($class_name_lower, 'martial') !== false || strpos($class_name_lower, 'mma') !== false) {
                        $boxing_images = [
                            'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1605296867424-35fc25c9212a?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $boxing_images[array_rand($boxing_images)];
                        $class_css_class = 'hiit-class';
                    }
                    // Pilates
                    elseif (strpos($class_name_lower, 'pilates') !== false || strpos($class_name_lower, 'core') !== false || strpos($class_name_lower, 'balance') !== false || strpos($class_name_lower, 'stretch') !== false) {
                        $pilates_images = [
                            'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=400&fit=crop&crop=center',
                            'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=600&h=400&fit=crop&crop=center',
                        ];
                        $class_image = $pilates_images[array_rand($pilates_images)];
                        $class_css_class = 'yoga-class';
                    }
                    ?>
                    
                    <div class="class-image <?= $class_css_class ?>">
                        <img src="<?= $class_image ?>" alt="<?= htmlspecialchars($class['title']) ?>" loading="lazy" 
                             onload="this.style.opacity='1';" 
                             onerror="this.style.display='none'; this.parentElement.classList.add('fallback-bg');"
                             style="opacity: 0; transition: opacity 0.3s ease;">
                    </div>
                    
                    <div class="class-content">
                        <h3 class="class-title"><?= htmlspecialchars($class['title']) ?></h3>
                        
                        <div class="class-meta">
                        <div class="meta-item">
                            <strong>📅 Date:</strong> <?= date('M j, Y', strtotime($class['start_time'])) ?>
                        </div>
                        <div class="meta-item">
                            <strong>🕐 Time:</strong> <?= date('g:i A', strtotime($class['start_time'])) ?> - <?= date('g:i A', strtotime($class['end_time'])) ?>
                        </div>
                        <div class="meta-item">
                            <strong>👨‍💪 Trainer:</strong> <?= htmlspecialchars($class['first_name'] . ' ' . $class['last_name']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>📍 Location:</strong> <?= htmlspecialchars($class['location']) ?>
                        </div>
                    </div>

                    <div style="margin: 20px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span><strong>👥 Capacity:</strong></span>
                            <span style="color: var(--l9-accent); font-weight: 600;"><?= $class['current_bookings'] ?>/<?= $class['capacity'] ?> spots</span>
                        </div>
                        <div class="capacity-bar">
                            <div class="capacity-fill" style="width: <?= ($class['current_bookings'] / $class['capacity']) * 100 ?>%;"></div>
                        </div>
                    </div>
                    
                    <p style="color: var(--l9-text-muted); line-height: 1.6; margin: 20px 0;">
                        <?= htmlspecialchars(substr($class['description'], 0, 180)) ?><?= strlen($class['description']) > 180 ? '...' : '' ?>
                    </p>
                    
                    <div style="margin-top: 25px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <?php if (!$user): ?>
                            <a href="login.php" class="btn btn-outline">🔒 Login to Book</a>
                        <?php elseif ($is_booked): ?>
                            <span class="status-badge status-booked">✅ Booked</span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= $user_bookings[$class['id']] ?>">
                                <button type="submit" name="cancel_booking" class="btn btn-danger"
                                        onclick="return confirm('Cancel this booking?')">
                                    ❌ Cancel Booking
                                </button>
                            </form>
                        <?php elseif ($is_full): ?>
                            <span class="status-badge status-full">🚫 Class Full</span>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <button type="submit" name="book_class" class="btn btn-success">💪 Book This Beast</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    </div> <!-- Close class-content -->
                </div>
            </div> <!-- Close grid column -->
            <?php endforeach; ?>
        <?php endif; ?>
    </div> <!-- Close row -->
        
    
    <!-- Action Section -->
    <div class="text-center mt-5 pt-4 border-top">
        <a href="index.php" class="btn btn-primary me-3">🏠 Back to Home</a>
        <?php if ($user): ?>
            <a href="dashboard.php" class="btn btn-outline-light me-2">📊 My Dashboard</a>
            <a href="memberships.php" class="btn btn-outline-warning">💳 My Membership</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>