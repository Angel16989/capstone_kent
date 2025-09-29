<?php
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$success_message = '';
$error_message = '';
$messages = [];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user data with all related information
    $stmt = $pdo->prepare("
        SELECT u.*, 
               ufp.height, ufp.current_weight, ufp.target_weight as goal_weight, ufp.fitness_level, 
               ufp.medical_conditions, ufp.primary_goal, ufp.activity_level,
               up.filename as profile_picture_url,
               mp.name as plan_name, m.status as membership_status
        FROM users u 
        LEFT JOIN user_fitness_profile ufp ON u.id = ufp.user_id 
        LEFT JOIN user_photos up ON u.id = up.user_id AND up.is_profile_picture = 1
        LEFT JOIN memberships m ON u.id = m.member_id AND m.status = 'active'
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
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
                // Validate and sanitize input
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $state = trim($_POST['state'] ?? '');
                $postcode = trim($_POST['postcode'] ?? '');
                
                if (empty($first_name) || empty($last_name) || empty($email)) {
                    $error_message = "First name, last name, and email are required.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error_message = "Please enter a valid email address.";
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            first_name = ?, last_name = ?, email = ?, phone = ?, 
                            address = ?, city = ?, state = ?, postcode = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $first_name, $last_name, $email, $phone,
                        $address, $city, $state, $postcode, $user_id
                    ]);
                    $success_message = "Personal information updated successfully!";
                }
                break;
                
            case 'update_fitness':
                // Validate and sanitize fitness data
                $height = floatval($_POST['height'] ?? 0);
                $current_weight = floatval($_POST['current_weight'] ?? 0);
                $target_weight = floatval($_POST['goal_weight'] ?? 0);
                $fitness_level = trim($_POST['fitness_level'] ?? '');
                $medical_conditions = trim($_POST['medical_conditions'] ?? '');
                
                if ($height <= 0 || $current_weight <= 0) {
                    $error_message = "Height and current weight must be greater than 0.";
                } elseif (empty($fitness_level)) {
                    $error_message = "Fitness level is required.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO user_fitness_profile 
                        (user_id, height, current_weight, target_weight, fitness_level, medical_conditions)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        height = VALUES(height), current_weight = VALUES(current_weight), 
                        target_weight = VALUES(target_weight), fitness_level = VALUES(fitness_level),
                        medical_conditions = VALUES(medical_conditions)
                    ");
                    $stmt->execute([
                        $user_id, $height, $current_weight, $target_weight,
                        $fitness_level, $medical_conditions
                    ]);
                    $success_message = "Fitness profile updated successfully!";
                }
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
                            INSERT INTO user_photos (user_id, filename, original_name, file_size, mime_type, is_profile_picture)
                            VALUES (?, ?, ?, ?, ?, 1)
                        ");
                        $stmt->execute([$user_id, $new_filename, $_FILES['profile_photo']['name'], $_FILES['profile_photo']['size'], $_FILES['profile_photo']['type']]);
                        $success_message = "Profile photo updated successfully!";
                    } else {
                        $error_message = "Failed to upload photo.";
                    }
                }
                break;
                
            case 'add_weight_entry':
                // Validate weight entry input
                if (empty($_POST['weight']) || empty($_POST['record_date'])) {
                    $error_message = "Please fill in all weight entry fields.";
                    break;
                }
                
                $weight = filter_var($_POST['weight'], FILTER_VALIDATE_FLOAT);
                $record_date = filter_var($_POST['record_date'], FILTER_SANITIZE_STRING);
                
                if ($weight === false || $weight <= 0) {
                    $error_message = "Please enter a valid weight.";
                    break;
                }
                
                // Create weight_progress table if it doesn't exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS weight_progress (
                        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        user_id INT UNSIGNED NOT NULL,
                        weight DECIMAL(5,2) NOT NULL,
                        recorded_date DATE NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    ) ENGINE=InnoDB
                ");
                
                $stmt = $pdo->prepare("
                    INSERT INTO weight_progress (user_id, weight, recorded_date)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user_id, $weight, $record_date]);
                $success_message = "Weight entry added successfully!";
                break;
                
            case 'update_nutrition':
                // Validate nutrition input
                $diet_type = isset($_POST['diet_type']) ? filter_var($_POST['diet_type'], FILTER_SANITIZE_STRING) : '';
                $daily_calories = isset($_POST['daily_calories']) ? (int)$_POST['daily_calories'] : 0;
                $daily_protein = isset($_POST['daily_protein']) ? (int)$_POST['daily_protein'] : 0;
                $daily_carbs = isset($_POST['daily_carbs']) ? (int)$_POST['daily_carbs'] : 0;
                $daily_fats = isset($_POST['daily_fats']) ? (int)$_POST['daily_fats'] : 0;
                $restrictions = isset($_POST['restrictions']) ? filter_var($_POST['restrictions'], FILTER_SANITIZE_STRING) : '';
                
                // Validate numeric values
                if ($daily_calories < 0) $daily_calories = 0;
                if ($daily_protein < 0) $daily_protein = 0;
                if ($daily_carbs < 0) $daily_carbs = 0;
                if ($daily_fats < 0) $daily_fats = 0;
                
                // Create nutrition profile table if it doesn't exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS user_nutrition_profiles (
                        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        user_id INT UNSIGNED NOT NULL UNIQUE,
                        diet_type VARCHAR(100),
                        daily_calories INT UNSIGNED DEFAULT 0,
                        daily_protein INT UNSIGNED DEFAULT 0,
                        daily_carbs INT UNSIGNED DEFAULT 0,
                        daily_fats INT UNSIGNED DEFAULT 0,
                        restrictions TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    ) ENGINE=InnoDB
                ");
                
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
                    $user_id, $diet_type, $daily_calories, $daily_protein,
                    $daily_carbs, $daily_fats, $restrictions
                ]);
                $success_message = "Nutrition profile updated successfully!";
                break;
                
            case 'add_goal':
                // Validate goal input
                if (empty($_POST['goal_type']) || empty($_POST['target_value']) || empty($_POST['target_date'])) {
                    $error_message = "Please fill in all required goal fields.";
                    break;
                }
                
                $goal_type = filter_var($_POST['goal_type'], FILTER_SANITIZE_STRING);
                $target_value = filter_var($_POST['target_value'], FILTER_VALIDATE_FLOAT);
                $target_date = filter_var($_POST['target_date'], FILTER_SANITIZE_STRING);
                $description = isset($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : '';
                
                if ($target_value === false || $target_value <= 0) {
                    $error_message = "Please enter a valid target value.";
                    break;
                }
                
                // Validate date format
                if (!DateTime::createFromFormat('Y-m-d', $target_date)) {
                    $error_message = "Please enter a valid target date.";
                    break;
                }
                
                // Create goals table if it doesn't exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS user_goals (
                        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        user_id INT UNSIGNED NOT NULL,
                        goal_type VARCHAR(100) NOT NULL,
                        target_value DECIMAL(10,2) NOT NULL,
                        target_date DATE NOT NULL,
                        description TEXT,
                        status ENUM('active','completed','cancelled') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    ) ENGINE=InnoDB
                ");
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_goals (user_id, goal_type, target_value, target_date, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, $goal_type, $target_value, $target_date, $description
                ]);
                $success_message = "Goal added successfully!";
                break;
                
            case 'change_password':
                // Validate required fields
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $user_password_hash = $user['password_hash'] ?? '';
                
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error_message = "All password fields are required.";
                } elseif (empty($user_password_hash)) {
                    $error_message = "User password not found. Please contact support.";
                } elseif (password_verify($current_password, $user_password_hash)) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 8) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                            $stmt->execute([$hashed_password, $user_id]);
                            $success_message = "Password changed successfully!";
                        } else {
                            $error_message = "New password must be at least 8 characters long.";
                        }
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
                   ufp.height, ufp.current_weight, ufp.target_weight as goal_weight, ufp.fitness_level, 
                   ufp.medical_conditions,
                   up.filename as profile_picture_url,
                   mp.name as plan_name, m.status as membership_status
            FROM users u 
            LEFT JOIN user_fitness_profile ufp ON u.id = ufp.user_id 
            LEFT JOIN user_photos up ON u.id = up.user_id AND up.is_profile_picture = 1
            LEFT JOIN memberships m ON u.id = m.member_id AND m.status = 'active'
            LEFT JOIN membership_plans mp ON m.plan_id = mp.id
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
        WHERE published_at IS NOT NULL 
        ORDER BY published_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment history
    // Get comprehensive payment history with invoices
    $stmt = $pdo->prepare("
        SELECT p.*, mp.name as plan_name, mp.description as plan_description,
               m.start_date, m.end_date
        FROM payments p
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE p.member_id = ?
        ORDER BY p.paid_at DESC
    ");
    $stmt->execute([$user_id]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent check-ins with duration calculation
    $stmt = $pdo->prepare("
        SELECT *, 
               TIMESTAMPDIFF(MINUTE, checkin_time, COALESCE(checkout_time, NOW())) as duration_calc,
               CASE WHEN checkout_time IS NULL THEN 'active' ELSE 'completed' END as status
        FROM member_checkins 
        WHERE member_id = ? 
        ORDER BY checkin_time DESC 
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $check_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT um.*, u.first_name as sender_name
        FROM user_messages um
        LEFT JOIN users u ON um.sender_id = u.id
        WHERE um.recipient_id = ? OR um.sender_id = ?
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
    <link href="assets/css/profile-layout-fix.css" rel="stylesheet">
    <link href="assets/css/universal-footer.css" rel="stylesheet">
    <link href="assets/css/chatbot.css" rel="stylesheet">
    <style>
        body {
            background: #0d0d0d;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        /* === PROFILE PAGE CHATBOT FIXES === */
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

        /* === PROFILE PAGE LAYOUT FIXES === */
        .container {
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 0 15px !important;
        }

        /* Fix button styling */
        .btn {
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.3s ease !important;
            border: none !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF4444, #FF6666) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3) !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #FF6666, #FF8888) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(255, 68, 68, 0.4) !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            color: white !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ffed4e) !important;
            color: #000 !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
            color: white !important;
        }

        /* Fix navigation tabs */
        .nav-pills .nav-link {
            background: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 10px !important;
            margin: 0 5px !important;
            transition: all 0.3s ease !important;
        }

        .nav-pills .nav-link:hover {
            background: rgba(255, 68, 68, 0.3) !important;
            color: white !important;
            transform: translateY(-2px) !important;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #FF4444, #FF6666) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3) !important;
        }

        /* Fix form controls */
        .form-control {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 2px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 10px !important;
            color: white !important;
            padding: 0.75rem 1rem !important;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: #FF4444 !important;
            color: white !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 68, 68, 0.25) !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Fix labels */
        .form-label {
            color: #FF4444 !important;
            font-weight: 600 !important;
        }

        /* Fix cards */
        .feature-card {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 2px solid rgba(255, 68, 68, 0.2) !important;
            border-radius: 15px !important;
            color: white !important;
            padding: 2rem !important;
            margin-bottom: 2rem !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
        }

        .feature-card:hover {
            transform: translateY(-5px) !important;
            border-color: rgba(255, 68, 68, 0.4) !important;
            box-shadow: 0 8px 40px rgba(255, 68, 68, 0.2) !important;
        }

        /* Fix tables */
        .table {
            color: white !important;
            background: transparent !important;
        }

        .table-dark {
            background: rgba(255, 68, 68, 0.2) !important;
            color: white !important;
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 68, 68, 0.1) !important;
            color: white !important;
        }

        /* Fix badges */
        .badge {
            font-size: 0.8em !important;
            padding: 0.4em 0.8em !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, #FF4444, #FF6666) !important;
        }

        .badge.bg-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, #ffc107, #ffed4e) !important;
            color: #000 !important;
        }

        /* Fix alerts */
        .alert {
            border-radius: 10px !important;
            border: none !important;
            font-weight: 600 !important;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(32, 201, 151, 0.2)) !important;
            color: #28a745 !important;
            border: 2px solid rgba(40, 167, 69, 0.3) !important;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(200, 35, 51, 0.2)) !important;
            color: #dc3545 !important;
            border: 2px solid rgba(220, 53, 69, 0.3) !important;
        }

        /* Fix responsive layout */
        @media (max-width: 768px) {
            .container {
                padding: 0 10px !important;
            }
            
            .nav-pills {
                flex-wrap: wrap !important;
            }
            
            .nav-pills .nav-link {
                margin: 2px !important;
                font-size: 0.9rem !important;
            }
            
            .feature-card {
                padding: 1.5rem !important;
            }
        }
        
        .profile-hero-enhanced {
            background: 
                radial-gradient(1000px 600px at 15% 0%, rgba(255,68,68,.15), transparent 60%),
                radial-gradient(800px 500px at 85% 100%, rgba(255,215,0,.08), transparent 60%),
                linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111111 50%, #000000 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
            min-height: 60vh;
            display: flex;
            align-items: center;
        }
        
        .profile-hero-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 40px 60px, rgba(0,229,168,.2), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(108,92,231,.3), transparent);
            background-repeat: repeat;
            background-size: 200px 150px;
            animation: particle-drift 25s linear infinite;
            pointer-events: none;
            opacity: 0.6;
        }
        
        @keyframes particle-drift {
            0% { transform: translateY(0px) translateX(0px); }
            100% { transform: translateY(-200px) translateX(100px); }
        }
        
        /* Fallback Footer Styles - Ensure Visibility */
        .l9-premium-footer {
            background: linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111111 50%, #000000 100%) !important;
            color: #ffffff !important;
            padding: 3rem 0 1rem !important;
            margin-top: 3rem !important;
            border-top: 2px solid #FF4444 !important;
            position: relative !important;
            z-index: 10 !important;
        }
        
        .footer-content {
            position: relative;
            z-index: 2;
        }
        
        .brand-text {
            background: linear-gradient(135deg, #FF4444, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }
        
        .footer-link {
            color: rgba(255,255,255,0.8) !important;
            text-decoration: none !important;
            padding: 0.5rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }
        
        .footer-link:hover {
            color: #FF4444 !important;
        }
        
        .social-link {
            color: rgba(255,255,255,0.8) !important;
            font-size: 1.5rem !important;
            margin: 0 0.5rem !important;
        }
        
        .social-link:hover {
            color: #FF4444 !important;
        }
        
        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23ffffff10" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.1;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
            object-fit: cover;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
        }
        
        .profile-main {
            background: #111111;
            min-height: auto;
            padding: 1rem 0;
            position: relative;
        }
        
        .nav-pills {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,107,53,0.2);
        }
        
        .nav-pills .nav-link {
            background: transparent;
            color: #ff6b35;
            margin: 0.25rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            padding: 0.75rem 1.5rem;
        }
        
        .nav-pills .nav-link:hover {
            background: rgba(255,107,53,0.1);
            border-color: rgba(255,107,53,0.3);
            transform: translateY(-2px);
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 20px rgba(255,107,53,0.4);
        }
        
        .feature-card {
            background: linear-gradient(145deg, #1a1a1a 0%, #222222 100%);
            border: 1px solid rgba(255,107,53,0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,107,53,0.4);
            box-shadow: 0 8px 40px rgba(255,107,53,0.2);
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #ffffff;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255,255,255,0.08);
            border-color: #ff6b35;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255,107,53,0.25);
        }
        
        .form-label {
            color: #ff6b35;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,107,53,0.4);
        }
        
        .text-primary {
            color: #ff6b35 !important;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: rgba(34,197,94,0.1);
            color: #22c55e;
            border-left: 4px solid #22c55e;
        }
        
        .alert-danger {
            background: rgba(239,68,68,0.1);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }
        
        .upload-zone {
            background: rgba(255,107,53,0.05);
            border: 2px dashed rgba(255,107,53,0.3);
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            background: rgba(255,107,53,0.08);
            border-color: rgba(255,107,53,0.5);
        }
        
        @media (max-width: 768px) {
            .nav-pills {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-pills .nav-link {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
                margin: 0.2rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .feature-card {
                padding: 1.5rem;
            }
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

    <!-- Enhanced Profile Hero Section -->
    <div class="profile-hero-enhanced">
        <div class="container">
            <div class="profile-card">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 text-center">
                        <div class="profile-picture-section">
                            <?php if (!empty($user['profile_picture_url'])): ?>
                                <div class="profile-avatar-wrapper">
                                    <img src="assets/img/profiles/<?= htmlspecialchars($user['profile_picture_url']) ?>" alt="Profile Picture" class="profile-avatar-enhanced">
                                    <div class="avatar-status-badge">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="profile-avatar-wrapper">
                                    <div class="profile-avatar-enhanced profile-avatar-placeholder">
                                        <i class="fas fa-user fa-4x"></i>
                                    </div>
                                    <div class="avatar-upload-hint">
                                        <i class="fas fa-camera"></i>
                                        <span>Upload Photo</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['membership_status']) && !empty($user['plan_name'])): ?>
                                <div class="membership-badge">
                                    <i class="fas fa-crown"></i>
                                    <span><?= htmlspecialchars($user['plan_name']) ?> Member</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7">
                        <div class="profile-info-section">
                            <div class="user-name-section">
                                <h1 class="user-name"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h1>
                                <div class="user-title">L9 Fitness Member</div>
                            </div>
                            
                            <div class="user-details">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Email Address</span>
                                        <span class="detail-value"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($user['phone'])): ?>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Phone Number</span>
                                        <span class="detail-value"><?= htmlspecialchars($user['phone']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Member Since</span>
                                        <span class="detail-value"><?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($user['fitness_level'])): ?>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Fitness Level</span>
                                        <span class="detail-value fitness-level"><?= ucfirst(htmlspecialchars($user['fitness_level'])) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <div class="stat-number">128</div>
                                    <div class="stat-label">Workouts</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">47</div>
                                    <div class="stat-label">Days Active</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">12.5</div>
                                    <div class="stat-label">Kg Lost</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Profile Section -->
    <main>
    <section class="profile-main">
        <div class="container">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

        <!-- Navigation Pills -->
        <div class="profile-navigation">
            <ul class="nav nav-pills mb-4 d-flex flex-wrap justify-content-center" id="profileTabs" role="tablist">
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
        </div>

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
                                <p><?= htmlspecialchars($announcement['body']) ?></p>
                                <small><i class="fas fa-clock"></i> <?= date('M j, Y g:i A', strtotime($announcement['published_at'])) ?></small>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="fas fa-credit-card text-primary"></i> Payment History & Invoices</h4>
                        <span class="badge bg-success"><?= count($payment_history) ?> payments</span>
                    </div>
                    
                    <?php if ($payment_history): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Invoice</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_history as $payment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= date('M j, Y', strtotime($payment['paid_at'])) ?></strong><br>
                                                <small class="text-muted"><?= date('g:i A', strtotime($payment['paid_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($payment['plan_name'] ?? 'L9 Fitness Service') ?></strong>
                                                    <?php if ($payment['plan_description']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($payment['plan_description']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($payment['start_date'] && $payment['end_date']): ?>
                                                        <br><small class="text-info">
                                                            <?= date('M j', strtotime($payment['start_date'])) ?> - <?= date('M j, Y', strtotime($payment['end_date'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <h6 class="text-success mb-0">$<?= number_format($payment['amount'], 2) ?></h6>
                                            </td>
                                            <td>
                                                <span class="badge bg-outline-secondary">
                                                    <?= ucfirst($payment['method']) ?>
                                                </span>
                                                <?php if ($payment['txn_ref']): ?>
                                                    <br><small class="text-muted"><?= substr($payment['txn_ref'], -8) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payment['invoice_no']): ?>
                                                    <code class="text-primary"><?= $payment['invoice_no'] ?></code>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($payment['invoice_no']): ?>
                                                    <a href="api/download_invoice.php?invoice=<?= $payment['invoice_no'] ?>" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="Download Invoice <?= $payment['invoice_no'] ?>"
                                                       target="_blank">
                                                        <i class="fas fa-download"></i> Invoice
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($payment['status'] === 'paid'): ?>
                                                    <button class="btn btn-outline-success btn-sm" 
                                                            onclick="showPaymentDetails(<?= htmlspecialchars(json_encode($payment)) ?>)">
                                                        <i class="fas fa-eye"></i> Details
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Payment Statistics -->
                        <div class="row mt-4">
                            <?php
                            $total_paid = array_sum(array_column($payment_history, 'amount'));
                            $successful_payments = count(array_filter($payment_history, fn($p) => $p['status'] === 'paid'));
                            $last_payment = $payment_history[0] ?? null;
                            ?>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-success">$<?= number_format($total_paid, 2) ?></h5>
                                    <small>Total Paid</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-primary"><?= $successful_payments ?></h5>
                                    <small>Successful Payments</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-info"><?= count($payment_history) ?></h5>
                                    <small>Total Transactions</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-warning">
                                        <?= $last_payment ? date('M j', strtotime($last_payment['paid_at'])) : 'N/A' ?>
                                    </h5>
                                    <small>Last Payment</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                            <h5>No payment history</h5>
                            <p class="text-muted">Start your fitness journey with a membership!</p>
                            <a href="memberships.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Get Membership
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Check-ins Tab -->
            <div class="tab-pane fade" id="checkins" role="tabpanel">
                <div class="feature-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="fas fa-clock text-primary"></i> Check-in History</h4>
                        <span class="badge bg-primary"><?= count($check_logs) ?> total visits</span>
                    </div>
                    
                    <?php if ($check_logs): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Check-in Time</th>
                                        <th>Check-out Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Area</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($check_logs as $log): ?>
                                        <tr class="<?= $log['status'] === 'active' ? 'table-warning' : '' ?>">
                                            <td>
                                                <strong><?= date('M j, Y', strtotime($log['checkin_time'])) ?></strong><br>
                                                <small class="text-muted"><?= date('l', strtotime($log['checkin_time'])) ?></small>
                                            </td>
                                            <td>
                                                <i class="bi bi-box-arrow-in-right text-success"></i>
                                                <?= date('g:i A', strtotime($log['checkin_time'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($log['checkout_time']): ?>
                                                    <i class="bi bi-box-arrow-right text-danger"></i>
                                                    <?= date('g:i A', strtotime($log['checkout_time'])) ?>
                                                <?php else: ?>
                                                    <span class="text-warning">🔥 Still active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($log['duration_minutes']): ?>
                                                    <span class="badge bg-info"><?= $log['duration_minutes'] ?> min</span>
                                                <?php elseif ($log['checkout_time']): ?>
                                                    <span class="badge bg-info"><?= $log['duration_calc'] ?> min</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><?= $log['duration_calc'] ?> min</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($log['status'] === 'active'): ?>
                                                    <span class="badge bg-success">🔥 Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">✅ Complete</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-outline-primary">
                                                    <?= ucfirst(str_replace('_', ' ', $log['facility_area'] ?? 'Gym Floor')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Check-in Statistics -->
                        <div class="row mt-4">
                            <?php
                            $total_visits = count($check_logs);
                            $active_sessions = count(array_filter($check_logs, fn($log) => $log['status'] === 'active'));
                            $total_duration = array_sum(array_map(fn($log) => $log['duration_minutes'] ?? $log['duration_calc'] ?? 0, $check_logs));
                            $avg_duration = $total_visits > 0 ? round($total_duration / $total_visits) : 0;
                            ?>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-primary"><?= $total_visits ?></h5>
                                    <small>Total Visits</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-warning"><?= $active_sessions ?></h5>
                                    <small>Active Sessions</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-info"><?= $avg_duration ?> min</h5>
                                    <small>Avg Duration</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <h5 class="text-success"><?= round($total_duration / 60, 1) ?>h</h5>
                                    <small>Total Time</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-dumbbell fa-4x text-muted mb-3"></i>
                            <h5>No check-ins yet</h5>
                            <p class="text-muted">Start your fitness journey by checking in to the gym!</p>
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Go to Dashboard
                            </a>
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
    </section>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">💳 Payment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadInvoiceFromModal()">
                    <i class="fas fa-download"></i> Download Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .stat-card h5 {
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 1.5em;
    }
    
    .stat-card small {
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(255, 68, 68, 0.05);
    }
    
    .badge.bg-outline-primary {
        background: transparent !important;
        border: 1px solid #0d6efd;
        color: #0d6efd;
    }
    
    .badge.bg-outline-secondary {
        background: transparent !important;
        border: 1px solid #6c757d;
        color: #6c757d;
    }
    
    .checkin-list, .payment-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .history-card {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }
    
    .history-card .card-header {
        border: none;
        background: linear-gradient(135deg, #ff4444, #ff6666) !important;
        color: white;
        font-weight: bold;
    }
    
    .history-card .card-header.bg-gradient-success {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
    }
    
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }
    
    .modal-header.bg-primary {
        background: linear-gradient(135deg, #ff4444, #ff6666) !important;
    }
</style>

<script>
let currentPaymentData = null;

function showPaymentDetails(payment) {
    currentPaymentData = payment;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Payment Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Amount:</strong></td><td class="text-success">$${parseFloat(payment.amount).toFixed(2)}</td></tr>
                    <tr><td><strong>Date:</strong></td><td>${new Date(payment.paid_at).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</td></tr>
                    <tr><td><strong>Method:</strong></td><td>${payment.method.charAt(0).toUpperCase() + payment.method.slice(1)}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-${payment.status === 'paid' ? 'success' : 'warning'}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></td></tr>
                    ${payment.txn_ref ? `<tr><td><strong>Transaction ID:</strong></td><td><code>${payment.txn_ref}</code></td></tr>` : ''}
                    ${payment.invoice_no ? `<tr><td><strong>Invoice:</strong></td><td><code class="text-primary">${payment.invoice_no}</code></td></tr>` : ''}
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Service Details</h6>
                <table class="table table-sm">
                    <tr><td><strong>Plan:</strong></td><td>${payment.plan_name || 'L9 Fitness Service'}</td></tr>
                    ${payment.plan_description ? `<tr><td><strong>Description:</strong></td><td>${payment.plan_description}</td></tr>` : ''}
                    ${payment.start_date ? `<tr><td><strong>Start Date:</strong></td><td>${new Date(payment.start_date).toLocaleDateString()}</td></tr>` : ''}
                    ${payment.end_date ? `<tr><td><strong>End Date:</strong></td><td>${new Date(payment.end_date).toLocaleDateString()}</td></tr>` : ''}
                </table>
            </div>
        </div>
        
        <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle"></i>
            <strong>Payment Confirmed!</strong> Thank you for choosing L9 Fitness. Welcome to beast mode! 🔥
        </div>
    `;
    
    document.getElementById('paymentDetailsContent').innerHTML = content;
    
    const modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
    modal.show();
}

function downloadInvoiceFromModal() {
    if (currentPaymentData && currentPaymentData.invoice_no) {
        window.open(`api/download_invoice.php?invoice=${currentPaymentData.invoice_no}`, '_blank');
    }
}

// Auto-refresh active check-ins every 30 seconds
setInterval(function() {
    const activeElements = document.querySelectorAll('.table-warning');
    if (activeElements.length > 0) {
        // Only refresh if there are active check-ins
        location.reload();
    }
}, 30000);

// Show notification for downloads
document.addEventListener('click', function(e) {
    if (e.target.closest('a[href*="download_invoice"]')) {
        setTimeout(() => {
            showNotification('Invoice download started! 📄', 'success');
        }, 500);
    }
});

function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Auto-open specific tab if requested from dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Check if a specific tab should be opened
    const openTab = localStorage.getItem('openTab');
    if (openTab) {
        const targetTab = document.getElementById(openTab + '-tab');
        if (targetTab) {
            targetTab.click();
            localStorage.removeItem('openTab');
            
            // Show notification about where to find the history
            setTimeout(() => {
                showNotification('🏋️ Check-in history loaded! Scroll down to see your gym visits.', 'success');
            }, 500);
        }
    }
    
    // Also check URL hash for direct tab access
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const tabButton = document.getElementById(hash + '-tab');
        if (tabButton) {
            tabButton.click();
            
            if (hash === 'checkins') {
                setTimeout(() => {
                    showNotification('🏋️ Check-in history is ready! Your gym visits are listed below.', 'info');
                }, 500);
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
