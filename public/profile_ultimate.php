<?php
session_start();
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$success_message = '';
$error_message = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user data with all related information
    $stmt = $pdo->prepare("
        SELECT u.*, 
               ufp.height, ufp.current_weight, ufp.goal_weight, ufp.fitness_level, 
               ufp.medical_conditions, ufp.emergency_contact, ufp.emergency_phone,
               up.profile_picture_url,
               m.plan_name, m.status as membership_status
        FROM users u 
        LEFT JOIN user_fitness_profile ufp ON u.id = ufp.user_id 
        LEFT JOIN user_photos up ON u.id = up.user_id AND up.is_profile_picture = 1
        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_personal':
                $stmt = $pdo->prepare("
                    UPDATE users SET 
                        first_name = ?, last_name = ?, email = ?, phone = ?, 
                        address = ?, city = ?, state = ?, postcode = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'],
                    $_POST['address'], $_POST['city'], $_POST['state'], $_POST['postcode'], $user_id
                ]);
                $success_message = "Personal information updated successfully!";
                break;
                
            case 'update_fitness':
                $stmt = $pdo->prepare("
                    INSERT INTO user_fitness_profile 
                    (user_id, height, current_weight, goal_weight, fitness_level, medical_conditions, emergency_contact, emergency_phone)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    height = VALUES(height), current_weight = VALUES(current_weight), 
                    goal_weight = VALUES(goal_weight), fitness_level = VALUES(fitness_level),
                    medical_conditions = VALUES(medical_conditions), emergency_contact = VALUES(emergency_contact),
                    emergency_phone = VALUES(emergency_phone)
                ");
                $stmt->execute([
                    $user_id, $_POST['height'], $_POST['current_weight'], $_POST['goal_weight'],
                    $_POST['fitness_level'], $_POST['medical_conditions'], $_POST['emergency_contact'], $_POST['emergency_phone']
                ]);
                $success_message = "Fitness profile updated successfully!";
                break;
                
            case 'upload_photo':
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'assets/img/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                        // Remove old profile picture
                        $stmt = $pdo->prepare("UPDATE user_photos SET is_profile_picture = 0 WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        
                        // Add new profile picture
                        $stmt = $pdo->prepare("
                            INSERT INTO user_photos (user_id, photo_url, photo_type, is_profile_picture)
                            VALUES (?, ?, 'profile', 1)
                        ");
                        $stmt->execute([$user_id, $upload_path]);
                        $success_message = "Profile photo updated successfully!";
                    } else {
                        $error_message = "Failed to upload photo.";
                    }
                }
                break;
                
            case 'add_weight_entry':
                $stmt = $pdo->prepare("
                    INSERT INTO weight_progress (user_id, weight, recorded_date)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user_id, $_POST['weight'], $_POST['record_date']]);
                $success_message = "Weight entry added successfully!";
                break;
                
            case 'update_nutrition':
                $stmt = $pdo->prepare("
                    INSERT INTO user_nutrition_profiles 
                    (user_id, diet_type, daily_calories, daily_protein, daily_carbs, daily_fats, restrictions)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    diet_type = VALUES(diet_type), daily_calories = VALUES(daily_calories),
                    daily_protein = VALUES(daily_protein), daily_carbs = VALUES(daily_carbs),
                    daily_fats = VALUES(daily_fats), restrictions = VALUES(restrictions)
                ");
                $stmt->execute([
                    $user_id, $_POST['diet_type'], $_POST['daily_calories'], $_POST['daily_protein'],
                    $_POST['daily_carbs'], $_POST['daily_fats'], $_POST['restrictions']
                ]);
                $success_message = "Nutrition profile updated successfully!";
                break;
                
            case 'add_goal':
                $stmt = $pdo->prepare("
                    INSERT INTO user_goals (user_id, goal_type, target_value, target_date, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, $_POST['goal_type'], $_POST['target_value'], $_POST['target_date'], $_POST['description']
                ]);
                $success_message = "Goal added successfully!";
                break;
                
            case 'change_password':
                if (password_verify($_POST['current_password'], $user['password'])) {
                    if ($_POST['new_password'] === $_POST['confirm_password']) {
                        $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "New passwords do not match.";
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
                break;
        }
        
        // Refresh user data after updates
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   ufp.height, ufp.current_weight, ufp.goal_weight, ufp.fitness_level, 
                   ufp.medical_conditions, ufp.emergency_contact, ufp.emergency_phone,
                   up.photo_url as profile_picture_url,
                   m.plan_name, m.status as membership_status
            FROM users u 
            LEFT JOIN user_fitness_profile ufp ON u.id = ufp.user_id 
            LEFT JOIN user_photos up ON u.id = up.user_id AND up.is_profile_picture = 1
            LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user's weight progress
    $stmt = $pdo->prepare("SELECT * FROM weight_progress WHERE user_id = ? ORDER BY recorded_date DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $weight_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user's nutrition profile
    $stmt = $pdo->prepare("SELECT * FROM user_nutrition_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $nutrition_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get user's goals
    $stmt = $pdo->prepare("SELECT * FROM user_goals WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $user_goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get announcements
    $stmt = $pdo->prepare("
        SELECT * FROM announcements 
        WHERE (target_audience = 'all' OR target_audience = 'members') 
        AND is_active = 1 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment history
    $stmt = $pdo->prepare("
        SELECT pr.*, p.amount, p.payment_date, m.plan_name
        FROM payment_receipts pr
        LEFT JOIN payments p ON pr.payment_id = p.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        WHERE pr.user_id = ?
        ORDER BY pr.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent check-ins
    $stmt = $pdo->prepare("
        SELECT * FROM gym_check_logs 
        WHERE user_id = ? 
        ORDER BY check_in_time DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $check_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT um.*, u.first_name as sender_name
        FROM user_messages um
        LEFT JOIN users u ON um.sender_id = u.id
        WHERE um.receiver_id = ? OR um.sender_id = ?
        ORDER BY um.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - L9 Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/profile-enhanced.css" rel="stylesheet">
    <style>
        .profile-hero {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        .nav-pills .nav-link {
            background: #2d2d2d;
            color: #ff6b35;
            margin: 0 0.5rem 0.5rem 0;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
        }
        
        .feature-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            border-color: #ff6b35;
            transform: translateY(-2px);
        }
        
        .announcement-card {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .progress-chart {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .upload-zone {
            border: 2px dashed #ff6b35;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            background: rgba(255, 107, 53, 0.1);
        }
        
        .message-item {
            background: #2d2d2d;
            border-left: 4px solid #ff6b35;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
        
        .payment-receipt {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .check-log {
            background: #2d2d2d;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <strong>L9 FITNESS</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="classes.php">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="memberships.php">Memberships</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Hero Section -->
    <div class="profile-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <?php if ($user['profile_picture_url']): ?>
                        <img src="<?= htmlspecialchars($user['profile_picture_url']) ?>" alt="Profile Picture" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar d-flex align-items-center justify-content-center" style="background: #333;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-9">
                    <h1 class="mb-2"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                    <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <?php if ($user['membership_status']): ?>
                        <p class="mb-0"><i class="fas fa-crown"></i> <?= htmlspecialchars($user['plan_name']) ?> Member</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Navigation Pills -->
        <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                    <i class="fas fa-user"></i> Personal Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="fitness-tab" data-bs-toggle="pill" data-bs-target="#fitness" type="button" role="tab">
                    <i class="fas fa-dumbbell"></i> Fitness Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos" type="button" role="tab">
                    <i class="fas fa-camera"></i> Photos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="announcements-tab" data-bs-toggle="pill" data-bs-target="#announcements" type="button" role="tab">
                    <i class="fas fa-bullhorn"></i> Announcements
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="pill" data-bs-target="#payments" type="button" role="tab">
                    <i class="fas fa-credit-card"></i> Payment History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="checkins-tab" data-bs-toggle="pill" data-bs-target="#checkins" type="button" role="tab">
                    <i class="fas fa-clock"></i> Check-ins
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="messages-tab" data-bs-toggle="pill" data-bs-target="#messages" type="button" role="tab">
                    <i class="fas fa-comments"></i> Messages
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                    <i class="fas fa-shield-alt"></i> Security
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabsContent">
            <!-- Personal Information Tab -->
            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-user text-primary"></i> Personal Information</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_personal">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <select class="form-control" name="state">
                                        <option value="">Select State</option>
                                        <option value="NSW" <?= ($user['state'] ?? '') === 'NSW' ? 'selected' : '' ?>>New South Wales</option>
                                        <option value="VIC" <?= ($user['state'] ?? '') === 'VIC' ? 'selected' : '' ?>>Victoria</option>
                                        <option value="QLD" <?= ($user['state'] ?? '') === 'QLD' ? 'selected' : '' ?>>Queensland</option>
                                        <option value="WA" <?= ($user['state'] ?? '') === 'WA' ? 'selected' : '' ?>>Western Australia</option>
                                        <option value="SA" <?= ($user['state'] ?? '') === 'SA' ? 'selected' : '' ?>>South Australia</option>
                                        <option value="TAS" <?= ($user['state'] ?? '') === 'TAS' ? 'selected' : '' ?>>Tasmania</option>
                                        <option value="ACT" <?= ($user['state'] ?? '') === 'ACT' ? 'selected' : '' ?>>Australian Capital Territory</option>
                                        <option value="NT" <?= ($user['state'] ?? '') === 'NT' ? 'selected' : '' ?>>Northern Territory</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Postcode</label>
                                    <input type="text" class="form-control" name="postcode" value="<?= htmlspecialchars($user['postcode'] ?? '') ?>" pattern="[0-9]{4}">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Personal Information</button>
                    </form>
                </div>
            </div>

            <!-- Fitness Profile Tab -->
            <div class="tab-pane fade" id="fitness" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="feature-card">
                            <h4><i class="fas fa-dumbbell text-primary"></i> Fitness Profile</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_fitness">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Height (cm)</label>
                                            <input type="number" class="form-control" name="height" value="<?= htmlspecialchars($user['height'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Current Weight (kg)</label>
                                            <input type="number" step="0.1" class="form-control" name="current_weight" value="<?= htmlspecialchars($user['current_weight'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Goal Weight (kg)</label>
                                            <input type="number" step="0.1" class="form-control" name="goal_weight" value="<?= htmlspecialchars($user['goal_weight'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Fitness Level</label>
                                            <select class="form-control" name="fitness_level">
                                                <option value="">Select Level</option>
                                                <option value="beginner" <?= ($user['fitness_level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                                <option value="intermediate" <?= ($user['fitness_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                                <option value="advanced" <?= ($user['fitness_level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                                <option value="elite" <?= ($user['fitness_level'] ?? '') === 'elite' ? 'selected' : '' ?>>Elite</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Medical Conditions</label>
                                    <textarea class="form-control" name="medical_conditions" rows="3"><?= htmlspecialchars($user['medical_conditions'] ?? '') ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Emergency Contact</label>
                                            <input type="text" class="form-control" name="emergency_contact" value="<?= htmlspecialchars($user['emergency_contact'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Emergency Phone</label>
                                            <input type="tel" class="form-control" name="emergency_phone" value="<?= htmlspecialchars($user['emergency_phone'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Fitness Profile</button>
                            </form>
                        </div>
                        
                        <!-- Weight Progress -->
                        <div class="feature-card">
                            <h5><i class="fas fa-chart-line text-primary"></i> Weight Progress</h5>
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="action" value="add_weight_entry">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" class="form-control" name="weight" placeholder="Weight (kg)" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control" name="record_date" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Add</button>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if ($weight_progress): ?>
                                <div class="progress-chart">
                                    <?php foreach ($weight_progress as $entry): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><?= date('M j, Y', strtotime($entry['recorded_date'])) ?></span>
                                            <strong><?= number_format($entry['weight'], 1) ?> kg</strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Nutrition Profile -->
                        <div class="feature-card">
                            <h5><i class="fas fa-apple-alt text-primary"></i> Nutrition Profile</h5>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_nutrition">
                                <div class="mb-3">
                                    <label class="form-label">Diet Type</label>
                                    <select class="form-control" name="diet_type">
                                        <option value="">Select Diet</option>
                                        <option value="regular" <?= ($nutrition_profile['diet_type'] ?? '') === 'regular' ? 'selected' : '' ?>>Regular</option>
                                        <option value="vegetarian" <?= ($nutrition_profile['diet_type'] ?? '') === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                                        <option value="vegan" <?= ($nutrition_profile['diet_type'] ?? '') === 'vegan' ? 'selected' : '' ?>>Vegan</option>
                                        <option value="keto" <?= ($nutrition_profile['diet_type'] ?? '') === 'keto' ? 'selected' : '' ?>>Keto</option>
                                        <option value="paleo" <?= ($nutrition_profile['diet_type'] ?? '') === 'paleo' ? 'selected' : '' ?>>Paleo</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Daily Calories</label>
                                    <input type="number" class="form-control" name="daily_calories" value="<?= htmlspecialchars($nutrition_profile['daily_calories'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Daily Protein (g)</label>
                                    <input type="number" class="form-control" name="daily_protein" value="<?= htmlspecialchars($nutrition_profile['daily_protein'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Daily Carbs (g)</label>
                                    <input type="number" class="form-control" name="daily_carbs" value="<?= htmlspecialchars($nutrition_profile['daily_carbs'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Daily Fats (g)</label>
                                    <input type="number" class="form-control" name="daily_fats" value="<?= htmlspecialchars($nutrition_profile['daily_fats'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Restrictions</label>
                                    <textarea class="form-control" name="restrictions" rows="2"><?= htmlspecialchars($nutrition_profile['restrictions'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Update Nutrition</button>
                            </form>
                        </div>
                        
                        <!-- Goals -->
                        <div class="feature-card">
                            <h5><i class="fas fa-target text-primary"></i> Goals</h5>
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="action" value="add_goal">
                                <div class="mb-2">
                                    <select class="form-control form-control-sm" name="goal_type" required>
                                        <option value="">Goal Type</option>
                                        <option value="weight_loss">Weight Loss</option>
                                        <option value="weight_gain">Weight Gain</option>
                                        <option value="muscle_gain">Muscle Gain</option>
                                        <option value="strength">Strength</option>
                                        <option value="endurance">Endurance</option>
                                        <option value="flexibility">Flexibility</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm" name="target_value" placeholder="Target Value" required>
                                </div>
                                <div class="mb-2">
                                    <input type="date" class="form-control form-control-sm" name="target_date" required>
                                </div>
                                <div class="mb-2">
                                    <textarea class="form-control form-control-sm" name="description" rows="2" placeholder="Description"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Add Goal</button>
                            </form>
                            
                            <?php if ($user_goals): ?>
                                <?php foreach ($user_goals as $goal): ?>
                                    <div class="mb-2 p-2" style="background: #2d2d2d; border-radius: 5px;">
                                        <small class="text-primary"><?= ucwords(str_replace('_', ' ', $goal['goal_type'])) ?></small><br>
                                        <strong><?= htmlspecialchars($goal['target_value']) ?></strong><br>
                                        <small>Target: <?= date('M j, Y', strtotime($goal['target_date'])) ?></small>
                                        <?php if ($goal['description']): ?>
                                            <br><small><?= htmlspecialchars($goal['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos Tab -->
            <div class="tab-pane fade" id="photos" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="feature-card">
                            <h4><i class="fas fa-camera text-primary"></i> Profile Photo</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_photo">
                                <div class="upload-zone mb-3" onclick="document.getElementById('profile_photo').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #ff6b35;"></i>
                                    <h5>Click to upload photo</h5>
                                    <p>Maximum file size: 5MB<br>Supported formats: JPG, PNG, GIF</p>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card">
                            <h5>Current Profile Photo</h5>
                            <?php if ($user['profile_picture_url']): ?>
                                <img src="<?= htmlspecialchars($user['profile_picture_url']) ?>" alt="Current Profile" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-user fa-5x text-muted"></i>
                                    <p class="mt-3">No profile photo uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-pane fade" id="announcements" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-bullhorn text-primary"></i> Gym Announcements</h4>
                    <?php if ($announcements): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-card">
                                <h5><?= htmlspecialchars($announcement['title']) ?></h5>
                                <p><?= htmlspecialchars($announcement['content']) ?></p>
                                <small><i class="fas fa-clock"></i> <?= date('M j, Y g:i A', strtotime($announcement['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p>No announcements at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment History Tab -->
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-credit-card text-primary"></i> Payment History & Receipts</h4>
                    <?php if ($payment_history): ?>
                        <?php foreach ($payment_history as $payment): ?>
                            <div class="payment-receipt">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6><?= htmlspecialchars($payment['plan_name'] ?? 'Membership Payment') ?></h6>
                                        <p class="mb-1">Amount: <strong>$<?= number_format($payment['amount'], 2) ?></strong></p>
                                        <small>Payment Date: <?= date('M j, Y', strtotime($payment['payment_date'])) ?></small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="download_receipt.php?id=<?= $payment['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-download"></i> Receipt
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p>No payment history found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Check-ins Tab -->
            <div class="tab-pane fade" id="checkins" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-clock text-primary"></i> Check-in History</h4>
                    <?php if ($check_logs): ?>
                        <?php foreach ($check_logs as $log): ?>
                            <div class="check-log">
                                <div>
                                    <strong><?= date('M j, Y', strtotime($log['check_in_time'])) ?></strong><br>
                                    <small>Check-in: <?= date('g:i A', strtotime($log['check_in_time'])) ?></small>
                                    <?php if ($log['check_out_time']): ?>
                                        <small> - Check-out: <?= date('g:i A', strtotime($log['check_out_time'])) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($log['check_out_time']): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">In Progress</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p>No check-in history found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages Tab -->
            <div class="tab-pane fade" id="messages" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-comments text-primary"></i> Messages</h4>
                    
                    <!-- Send Message Form -->
                    <div class="mb-4">
                        <h6>Send New Message</h6>
                        <form method="POST">
                            <input type="hidden" name="action" value="send_message">
                            <div class="mb-3">
                                <select class="form-control" name="receiver_type" required>
                                    <option value="">Send to...</option>
                                    <option value="admin">Admin</option>
                                    <option value="trainer">Trainer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="4" placeholder="Your message..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                    
                    <hr>
                    
                    <!-- Message History -->
                    <h6>Message History</h6>
                    <?php if ($messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong><?= htmlspecialchars($message['subject']) ?></strong>
                                    <small><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></small>
                                </div>
                                <p class="mb-2"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                <small>From: <?= htmlspecialchars($message['sender_name'] ?? 'System') ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <p>No messages found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="feature-card">
                    <h4><i class="fas fa-shield-alt text-primary"></i> Security Settings</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>L9 FITNESS</h5>
                    <p>Transform your body, transform your life.</p>
                </div>
                <div class="col-md-6">
                    <h6>Quick Links</h6>
                    <a href="privacy.php" class="footer-link">Privacy Policy</a>
                    <a href="terms.php" class="footer-link">Terms of Service</a>
                    <a href="contact.php" class="footer-link">Contact Us</a>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2025 L9 Fitness. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // File upload preview
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.maxWidth = '200px';
                    preview.style.borderRadius = '10px';
                    preview.className = 'mt-3';
                    
                    var uploadZone = document.querySelector('.upload-zone');
                    var existingPreview = uploadZone.querySelector('img');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    uploadZone.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>