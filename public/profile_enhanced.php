<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_once __DIR__ . '/../app/helpers/validator.php'; 
require_login(); 

$pageTitle = "Profile Management";
$pageCSS = ["/assets/css/profile.css", "/assets/css/dashboard-enhanced.css"];

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
            $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');
            $dob = $_POST['dob'] ?? null;
            $gender = $_POST['gender'] ?? null;
            
            $stmt = $pdo->prepare('UPDATE users SET phone=?, address=?, emergency_contact=?, dob=?, gender=? WHERE id=?'); 
            $stmt->execute([$phone, $address, $emergency_contact, $dob, $gender, $u['id']]); 
            $message = 'Profile updated successfully!';
            
        } elseif ($action === 'update_fitness_profile') {
            $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
            $target_weight = filter_input(INPUT_POST, 'target_weight', FILTER_VALIDATE_FLOAT);
            $fitness_level = $_POST['fitness_level'] ?? 'beginner';
            $primary_goal = $_POST['primary_goal'] ?? 'general_fitness';
            $activity_level = $_POST['activity_level'] ?? 'moderately_active';
            $medical_conditions = trim($_POST['medical_conditions'] ?? '');
            $preferred_workout_time = $_POST['preferred_workout_time'] ?? null;
            
            // Check if profile exists
            $stmt = $pdo->prepare('SELECT id FROM user_fitness_profile WHERE user_id = ?');
            $stmt->execute([$u['id']]);
            $existing = $stmt->fetchColumn();
            
            if ($existing) {
                $stmt = $pdo->prepare('UPDATE user_fitness_profile SET height = ?, target_weight = ?, fitness_level = ?, primary_goal = ?, activity_level = ?, medical_conditions = ?, preferred_workout_time = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
                $stmt->execute([$height, $target_weight, $fitness_level, $primary_goal, $activity_level, $medical_conditions, $preferred_workout_time, $u['id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO user_fitness_profile (user_id, height, target_weight, fitness_level, primary_goal, activity_level, medical_conditions, preferred_workout_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$u['id'], $height, $target_weight, $fitness_level, $primary_goal, $activity_level, $medical_conditions, $preferred_workout_time]);
            }
            $message = 'Fitness profile updated successfully!';
            
        } elseif ($action === 'update_nutrition_plan') {
            $plan_name = trim($_POST['plan_name'] ?? '');
            $diet_type = $_POST['diet_type'] ?? 'standard';
            $daily_calories = filter_input(INPUT_POST, 'daily_calories', FILTER_VALIDATE_INT);
            $daily_protein = filter_input(INPUT_POST, 'daily_protein', FILTER_VALIDATE_FLOAT);
            $daily_carbs = filter_input(INPUT_POST, 'daily_carbs', FILTER_VALIDATE_FLOAT);
            $daily_fat = filter_input(INPUT_POST, 'daily_fat', FILTER_VALIDATE_FLOAT);
            $meals_per_day = filter_input(INPUT_POST, 'meals_per_day', FILTER_VALIDATE_INT) ?: 3;
            $food_allergies = trim($_POST['food_allergies'] ?? '');
            $food_preferences = trim($_POST['food_preferences'] ?? '');
            
            if (!$plan_name) {
                throw new Exception('Plan name is required');
            }
            
            // Convert comma-separated strings to JSON arrays
            $food_allergies_json = null;
            if ($food_allergies) {
                $allergies_array = array_map('trim', explode(',', $food_allergies));
                $food_allergies_json = json_encode($allergies_array);
            }
            
            $food_preferences_json = null;
            if ($food_preferences) {
                $preferences_array = array_map('trim', explode(',', $food_preferences));
                $food_preferences_json = json_encode($preferences_array);
            }
            
            // Deactivate existing plans
            $stmt = $pdo->prepare('UPDATE user_nutrition_profiles SET is_active = 0 WHERE user_id = ?');
            $stmt->execute([$u['id']]);
            
            // Create new plan
            $stmt = $pdo->prepare('INSERT INTO user_nutrition_profiles (user_id, plan_name, diet_type, daily_calories, daily_protein, daily_carbs, daily_fat, meals_per_day, food_allergies, food_preferences, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
            $stmt->execute([$u['id'], $plan_name, $diet_type, $daily_calories, $daily_protein, $daily_carbs, $daily_fat, $meals_per_day, $food_allergies_json, $food_preferences_json]);
            $message = 'Nutrition plan updated successfully!';
            
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Verify current password
            if (!password_verify($current_password, $u['password_hash'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $stmt->execute([$hashed_password, $u['id']]);
                $message = 'Password changed successfully!';
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred: ' . $e->getMessage();
    }
}

// Get updated user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?'); 
$stmt->execute([$u['id']]); 
$row = $stmt->fetch();

// Get fitness profile
$fitness_profile = null;
$stmt = $pdo->prepare('SELECT * FROM user_fitness_profile WHERE user_id = ?');
$stmt->execute([$u['id']]);
$fitness_profile = $stmt->fetch();

// Get active nutrition plan
$nutrition_plan = null;
$stmt = $pdo->prepare('SELECT * FROM user_nutrition_profiles WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$u['id']]);
$nutrition_plan = $stmt->fetch();

// Get weight progress (last 10 entries)
$weight_history = [];
$stmt = $pdo->prepare('SELECT * FROM weight_progress WHERE user_id = ? ORDER BY recorded_date DESC LIMIT 10');
$stmt->execute([$u['id']]);
$weight_history = $stmt->fetchAll();

// Get active goals
$active_goals = [];
$stmt = $pdo->prepare('SELECT * FROM user_goals WHERE user_id = ? AND status = "active" ORDER BY priority DESC, created_at DESC');
$stmt->execute([$u['id']]);
$active_goals = $stmt->fetchAll();

// Get recent workouts
$recent_workouts = [];
$stmt = $pdo->prepare('SELECT * FROM workout_progress WHERE user_id = ? ORDER BY workout_date DESC LIMIT 10');
$stmt->execute([$u['id']]);
$recent_workouts = $stmt->fetchAll();

// Get membership info
$membership = null;
$stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = "active" AND m.end_date > NOW()');
$stmt->execute([$u['id']]);
$membership = $stmt->fetch();

// Get recent bookings
$stmt = $pdo->prepare('SELECT c.title, c.start_time, b.status FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.member_id = ? ORDER BY c.start_time DESC LIMIT 5');
$stmt->execute([$u['id']]);
$recent_bookings = $stmt->fetchAll();

// Get workout stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total_bookings FROM bookings WHERE member_id = ? AND status = "attended"');
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
              <div class="stat-number"><?php echo count($active_goals); ?></div>
              <div class="stat-label">Active Goals</div>
            </div>
            <div class="stat-item">
              <div class="stat-number"><?php echo count($weight_history); ?></div>
              <div class="stat-label">Weight Entries</div>
            </div>
            <div class="stat-item">
              <div class="stat-number"><?php echo date('M Y', strtotime($row['created_at'])); ?></div>
              <div class="stat-label">Member Since</div>
            </div>
          </div>
          
          <!-- Quick Actions -->
          <div class="profile-actions mt-4">
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-speedometer2 me-2"></i>Back to Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>classes.php" class="btn btn-outline-primary w-100">
              <i class="bi bi-calendar-plus me-2"></i>Book Classes
            </a>
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
                <button class="nav-link" id="fitness-tab" data-bs-toggle="tab" data-bs-target="#fitness" type="button">
                  <i class="bi bi-heart-pulse me-2"></i>Fitness Profile
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" data-bs-target="#nutrition" type="button">
                  <i class="bi bi-apple me-2"></i>Nutrition
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button">
                  <i class="bi bi-graph-up me-2"></i>Progress
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="goals-tab" data-bs-toggle="tab" data-bs-target="#goals" type="button">
                  <i class="bi bi-bullseye me-2"></i>Goals
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                  <i class="bi bi-lock me-2"></i>Security
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
                  <p class="text-muted">Update your profile details and emergency contacts</p>
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
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                               value="<?php echo htmlspecialchars($row['emergency_contact'] ?? ''); ?>">
                        <label for="emergency_contact">
                          <i class="bi bi-person-exclamation me-2"></i>Emergency Contact
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="date" class="form-control" id="dob" name="dob" 
                               value="<?php echo htmlspecialchars($row['dob'] ?? ''); ?>">
                        <label for="dob">
                          <i class="bi bi-calendar me-2"></i>Date of Birth
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="gender" name="gender">
                          <option value="">Select Gender</option>
                          <option value="male" <?php echo ($row['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                          <option value="female" <?php echo ($row['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                          <option value="other" <?php echo ($row['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <label for="gender">
                          <i class="bi bi-person me-2"></i>Gender
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
            
            <!-- Fitness Profile Tab -->
            <div class="tab-pane fade" id="fitness">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Fitness Profile</h4>
                  <p class="text-muted">Set up your fitness goals and preferences</p>
                </div>
                
                <form method="post" class="profile-form">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="action" value="update_fitness_profile">
                  
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="height" name="height" step="0.1" min="100" max="250"
                               value="<?php echo $fitness_profile['height'] ?? ''; ?>">
                        <label for="height">
                          <i class="bi bi-ruler me-2"></i>Height (cm)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.1" min="30" max="200"
                               value="<?php echo $fitness_profile['target_weight'] ?? ''; ?>">
                        <label for="target_weight">
                          <i class="bi bi-bullseye me-2"></i>Target Weight (kg)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="fitness_level" name="fitness_level">
                          <option value="beginner" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                          <option value="intermediate" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                          <option value="advanced" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                        <label for="fitness_level">
                          <i class="bi bi-speedometer me-2"></i>Fitness Level
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="primary_goal" name="primary_goal">
                          <option value="weight_loss" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                          <option value="muscle_gain" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'muscle_gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                          <option value="strength" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'strength' ? 'selected' : ''; ?>>Strength</option>
                          <option value="endurance" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'endurance' ? 'selected' : ''; ?>>Endurance</option>
                          <option value="general_fitness" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'general_fitness' ? 'selected' : ''; ?>>General Fitness</option>
                        </select>
                        <label for="primary_goal">
                          <i class="bi bi-target me-2"></i>Primary Goal
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="activity_level" name="activity_level">
                          <option value="sedentary" <?php echo ($fitness_profile['activity_level'] ?? '') === 'sedentary' ? 'selected' : ''; ?>>Sedentary</option>
                          <option value="lightly_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'lightly_active' ? 'selected' : ''; ?>>Lightly Active</option>
                          <option value="moderately_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'moderately_active' ? 'selected' : ''; ?>>Moderately Active</option>
                          <option value="very_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'very_active' ? 'selected' : ''; ?>>Very Active</option>
                          <option value="extra_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'extra_active' ? 'selected' : ''; ?>>Extra Active</option>
                        </select>
                        <label for="activity_level">
                          <i class="bi bi-activity me-2"></i>Activity Level
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="preferred_workout_time" name="preferred_workout_time">
                          <option value="">Select Preferred Time</option>
                          <option value="early_morning" <?php echo ($fitness_profile['preferred_workout_time'] ?? '') === 'early_morning' ? 'selected' : ''; ?>>Early Morning</option>
                          <option value="morning" <?php echo ($fitness_profile['preferred_workout_time'] ?? '') === 'morning' ? 'selected' : ''; ?>>Morning</option>
                          <option value="afternoon" <?php echo ($fitness_profile['preferred_workout_time'] ?? '') === 'afternoon' ? 'selected' : ''; ?>>Afternoon</option>
                          <option value="evening" <?php echo ($fitness_profile['preferred_workout_time'] ?? '') === 'evening' ? 'selected' : ''; ?>>Evening</option>
                          <option value="late_evening" <?php echo ($fitness_profile['preferred_workout_time'] ?? '') === 'late_evening' ? 'selected' : ''; ?>>Late Evening</option>
                        </select>
                        <label for="preferred_workout_time">
                          <i class="bi bi-clock me-2"></i>Preferred Workout Time
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <div class="form-floating">
                        <textarea class="form-control" id="medical_conditions" name="medical_conditions" style="height: 100px"><?php echo htmlspecialchars($fitness_profile['medical_conditions'] ?? ''); ?></textarea>
                        <label for="medical_conditions">
                          <i class="bi bi-heart me-2"></i>Medical Conditions / Injuries (optional)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Fitness Profile
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            
            <!-- Nutrition Tab -->
            <div class="tab-pane fade" id="nutrition">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Nutrition Plan</h4>
                  <p class="text-muted">Set up your dietary preferences and nutrition goals</p>
                </div>
                
                <form method="post" class="profile-form">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="action" value="update_nutrition_plan">
                  
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="plan_name" name="plan_name" required
                               value="<?php echo htmlspecialchars($nutrition_plan['plan_name'] ?? ''); ?>" 
                               placeholder="e.g., My Weight Loss Plan">
                        <label for="plan_name">
                          <i class="bi bi-journal-text me-2"></i>Plan Name
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="diet_type" name="diet_type" required>
                          <option value="standard" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'standard' ? 'selected' : ''; ?>>Standard</option>
                          <option value="vegetarian" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                          <option value="vegan" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'vegan' ? 'selected' : ''; ?>>Vegan</option>
                          <option value="keto" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'keto' ? 'selected' : ''; ?>>Keto</option>
                          <option value="paleo" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'paleo' ? 'selected' : ''; ?>>Paleo</option>
                          <option value="mediterranean" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'mediterranean' ? 'selected' : ''; ?>>Mediterranean</option>
                          <option value="low_carb" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'low_carb' ? 'selected' : ''; ?>>Low Carb</option>
                          <option value="high_protein" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'high_protein' ? 'selected' : ''; ?>>High Protein</option>
                        </select>
                        <label for="diet_type">
                          <i class="bi bi-apple me-2"></i>Diet Type
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="daily_calories" name="daily_calories" min="1000" max="5000"
                               value="<?php echo $nutrition_plan['daily_calories'] ?? ''; ?>" 
                               placeholder="e.g., 2200">
                        <label for="daily_calories">
                          <i class="bi bi-fire me-2"></i>Daily Calories
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select class="form-control" id="meals_per_day" name="meals_per_day">
                          <option value="3" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 3 ? 'selected' : ''; ?>>3 Meals</option>
                          <option value="4" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 4 ? 'selected' : ''; ?>>4 Meals</option>
                          <option value="5" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 5 ? 'selected' : ''; ?>>5 Meals</option>
                          <option value="6" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 6 ? 'selected' : ''; ?>>6 Meals</option>
                        </select>
                        <label for="meals_per_day">
                          <i class="bi bi-clock me-2"></i>Meals Per Day
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="daily_protein" name="daily_protein" step="0.1" min="0"
                               value="<?php echo $nutrition_plan['daily_protein'] ?? ''; ?>" 
                               placeholder="e.g., 140">
                        <label for="daily_protein">
                          <i class="bi bi-egg me-2"></i>Daily Protein (g)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="daily_carbs" name="daily_carbs" step="0.1" min="0"
                               value="<?php echo $nutrition_plan['daily_carbs'] ?? ''; ?>" 
                               placeholder="e.g., 200">
                        <label for="daily_carbs">
                          <i class="bi bi-bread-slice me-2"></i>Daily Carbs (g)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-floating">
                        <input type="number" class="form-control" id="daily_fat" name="daily_fat" step="0.1" min="0"
                               value="<?php echo $nutrition_plan['daily_fat'] ?? ''; ?>" 
                               placeholder="e.g., 75">
                        <label for="daily_fat">
                          <i class="bi bi-droplet me-2"></i>Daily Fat (g)
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="food_allergies" name="food_allergies" 
                               placeholder="e.g., nuts, dairy, shellfish (comma separated)"
                               value="<?php echo isset($nutrition_plan['food_allergies']) ? htmlspecialchars(str_replace(['["', '"]', '","'], ['', '', ', '], $nutrition_plan['food_allergies'])) : ''; ?>">
                        <label for="food_allergies">
                          <i class="bi bi-exclamation-triangle me-2"></i>Food Allergies
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="food_preferences" name="food_preferences" 
                               placeholder="e.g., high protein, low sugar (comma separated)"
                               value="<?php echo isset($nutrition_plan['food_preferences']) ? htmlspecialchars(str_replace(['["', '"]', '","'], ['', '', ', '], $nutrition_plan['food_preferences'])) : ''; ?>">
                        <label for="food_preferences">
                          <i class="bi bi-heart me-2"></i>Food Preferences
                        </label>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Nutrition Plan
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            
            <!-- Progress Tab -->
            <div class="tab-pane fade" id="progress">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Progress Tracking</h4>
                  <p class="text-muted">View your fitness journey and progress over time</p>
                </div>
                
                <!-- Weight Progress -->
                <?php if (!empty($weight_history)): ?>
                  <div class="mb-4">
                    <h5><i class="bi bi-graph-up me-2"></i>Weight Progress</h5>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Weight (kg)</th>
                            <th>Body Fat %</th>
                            <th>Notes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($weight_history as $entry): ?>
                            <tr>
                              <td><?php echo date('M d, Y', strtotime($entry['recorded_date'])); ?></td>
                              <td><strong><?php echo number_format($entry['weight'], 1); ?> kg</strong></td>
                              <td><?php echo $entry['body_fat_percentage'] ? number_format($entry['body_fat_percentage'], 1) . '%' : 'N/A'; ?></td>
                              <td><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php endif; ?>
                
                <!-- Recent Workouts -->
                <?php if (!empty($recent_workouts)): ?>
                  <div class="mb-4">
                    <h5><i class="bi bi-trophy me-2"></i>Recent Workouts</h5>
                    <div class="row g-3">
                      <?php foreach($recent_workouts as $workout): ?>
                        <div class="col-lg-6">
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
                                <?php if ($workout['sets']): ?><div class="detail-item"><span>Sets:</span> <strong><?php echo $workout['sets']; ?></strong></div><?php endif; ?>
                                <?php if ($workout['reps']): ?><div class="detail-item"><span>Reps:</span> <strong><?php echo $workout['reps']; ?></strong></div><?php endif; ?>
                                <?php if ($workout['weight']): ?><div class="detail-item"><span>Weight:</span> <strong><?php echo number_format($workout['weight'], 1); ?>kg</strong></div><?php endif; ?>
                              <?php else: ?>
                                <?php if ($workout['duration']): ?><div class="detail-item"><span>Duration:</span> <strong><?php echo $workout['duration']; ?> min</strong></div><?php endif; ?>
                                <?php if ($workout['calories_burned']): ?><div class="detail-item"><span>Calories:</span> <strong><?php echo $workout['calories_burned']; ?></strong></div><?php endif; ?>
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
                  </div>
                <?php endif; ?>
                
                <?php if (empty($weight_history) && empty($recent_workouts)): ?>
                  <div class="empty-state text-center py-5">
                    <i class="bi bi-graph-up display-1 text-muted mb-3"></i>
                    <h5>No Progress Data Yet</h5>
                    <p class="text-muted">Start logging your workouts and weight to track your progress!</p>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-primary">
                      <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Goals Tab -->
            <div class="tab-pane fade" id="goals">
              <div class="profile-card">
                <div class="card-header-modern">
                  <h4>Fitness Goals</h4>
                  <p class="text-muted">Track your fitness goals and milestones</p>
                </div>
                
                <?php if (!empty($active_goals)): ?>
                  <div class="goals-list">
                    <?php foreach($active_goals as $goal): ?>
                      <div class="goal-item">
                        <div class="goal-header">
                          <h6 class="goal-title"><?php echo htmlspecialchars($goal['title']); ?></h6>
                          <span class="goal-priority priority-<?php echo $goal['priority']; ?>">
                            <?php echo ucfirst($goal['priority']); ?>
                          </span>
                        </div>
                        
                        <?php if ($goal['description']): ?>
                          <p class="text-muted mb-2"><?php echo htmlspecialchars($goal['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($goal['target_value'] && $goal['current_value'] !== null): ?>
                          <div class="goal-progress">
                            <?php 
                            $progress_percentage = $goal['target_value'] != 0 ? ($goal['current_value'] / $goal['target_value']) * 100 : 0;
                            $progress_percentage = min(100, max(0, $progress_percentage));
                            ?>
                            <div class="progress-info">
                              <span><?php echo number_format($goal['current_value'], 1); ?> / <?php echo number_format($goal['target_value'], 1); ?> <?php echo htmlspecialchars($goal['unit']); ?></span>
                              <span><?php echo number_format($progress_percentage, 1); ?>%</span>
                            </div>
                            <div class="progress">
                              <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress_percentage; ?>%"></div>
                            </div>
                          </div>
                        <?php endif; ?>
                        
                        <?php if ($goal['target_date']): ?>
                          <div class="goal-deadline mt-2">
                            <small class="text-muted">
                              <i class="bi bi-calendar"></i> Target: <?php echo date('M j, Y', strtotime($goal['target_date'])); ?>
                            </small>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="empty-state text-center py-5">
                    <i class="bi bi-bullseye display-1 text-muted mb-3"></i>
                    <h5>No Active Goals</h5>
                    <p class="text-muted">Set some goals to stay motivated and track your progress!</p>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-primary">
                      <i class="bi bi-plus-circle me-2"></i>Set Goals
                    </a>
                  </div>
                <?php endif; ?>
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
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>