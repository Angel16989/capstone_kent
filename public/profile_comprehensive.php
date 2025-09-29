<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 
require_once __DIR__ . '/../app/helpers/validator.php'; 
require_login(); 

$pageTitle = "Profile Management";
$pageCSS = ["/assets/css/profile.css", "/assets/css/dashboard-enhanced.css", "/assets/css/profile-enhanced.css"];

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
        if ($action === 'upload_photo') {
            if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Please select a valid image file');
            }
            
            $file = $_FILES['profile_photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Only JPG, PNG, GIF, and WebP images are allowed');
            }
            
            if ($file['size'] > $max_size) {
                throw new Exception('File size must be less than 5MB');
            }
            
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/uploads/profile_photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $u['id'] . '_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Deactivate old profile pictures
                $stmt = $pdo->prepare('UPDATE user_photos SET is_profile_picture = 0 WHERE user_id = ?');
                $stmt->execute([$u['id']]);
                
                // Insert new photo record
                $stmt = $pdo->prepare('INSERT INTO user_photos (user_id, filename, original_name, file_size, mime_type, is_profile_picture) VALUES (?, ?, ?, ?, ?, 1)');
                $stmt->execute([$u['id'], $filename, $file['name'], $file['size'], $file['type']]);
                
                $message = 'Profile photo uploaded successfully!';
            } else {
                throw new Exception('Failed to upload photo');
            }
            
        } elseif ($action === 'update_profile') {
            $phone = sanitize($_POST['phone'] ?? ''); 
            $address = sanitize($_POST['address'] ?? '');
            $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');
            $dob = $_POST['dob'] ?? null;
            $gender = $_POST['gender'] ?? null;
            
            $stmt = $pdo->prepare('UPDATE users SET phone=?, address=?, emergency_contact=?, dob=?, gender=? WHERE id=?'); 
            $stmt->execute([$phone, $address, $emergency_contact, $dob, $gender, $u['id']]); 
            $message = 'Profile updated successfully!';
            
        } elseif ($action === 'send_message') {
            $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_VALIDATE_INT);
            $subject = sanitize($_POST['subject'] ?? '');
            $message_text = sanitize($_POST['message'] ?? '');
            $message_type = $_POST['message_type'] ?? 'general';
            $priority = $_POST['priority'] ?? 'medium';
            
            if (!$recipient_id || !$message_text) {
                throw new Exception('Recipient and message are required');
            }
            
            $stmt = $pdo->prepare('INSERT INTO user_messages (sender_id, recipient_id, subject, message, message_type, priority) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$u['id'], $recipient_id, $subject, $message_text, $message_type, $priority]);
            $message = 'Message sent successfully!';
            
        } elseif ($action === 'mark_announcement_read') {
            $announcement_id = filter_input(INPUT_POST, 'announcement_id', FILTER_VALIDATE_INT);
            if ($announcement_id) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO user_announcement_reads (user_id, announcement_id) VALUES (?, ?)');
                $stmt->execute([$u['id'], $announcement_id]);
            }
            
        } elseif ($action === 'update_preferences') {
            $notifications_email = isset($_POST['notifications_email']) ? 1 : 0;
            $notifications_sms = isset($_POST['notifications_sms']) ? 1 : 0;
            $notifications_push = isset($_POST['notifications_push']) ? 1 : 0;
            $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
            $class_reminders = isset($_POST['class_reminders']) ? 1 : 0;
            $payment_reminders = isset($_POST['payment_reminders']) ? 1 : 0;
            $privacy_level = $_POST['privacy_level'] ?? 'members_only';
            $measurement_system = $_POST['measurement_system'] ?? 'metric';
            
            $stmt = $pdo->prepare('INSERT INTO user_preferences (user_id, notifications_email, notifications_sms, notifications_push, marketing_emails, class_reminders, payment_reminders, privacy_level, measurement_system) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE notifications_email=VALUES(notifications_email), notifications_sms=VALUES(notifications_sms), notifications_push=VALUES(notifications_push), marketing_emails=VALUES(marketing_emails), class_reminders=VALUES(class_reminders), payment_reminders=VALUES(payment_reminders), privacy_level=VALUES(privacy_level), measurement_system=VALUES(measurement_system)');
            
            $stmt->execute([$u['id'], $notifications_email, $notifications_sms, $notifications_push, $marketing_emails, $class_reminders, $payment_reminders, $privacy_level, $measurement_system]);
            $message = 'Preferences updated successfully!';
            
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

// Get user profile photo
$profile_photo = null;
$stmt = $pdo->prepare('SELECT filename FROM user_photos WHERE user_id = ? AND is_profile_picture = 1 ORDER BY uploaded_at DESC LIMIT 1');
$stmt->execute([$u['id']]);
$profile_photo = $stmt->fetchColumn();

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

// Get user preferences
$user_preferences = null;
$stmt = $pdo->prepare('SELECT * FROM user_preferences WHERE user_id = ?');
$stmt->execute([$u['id']]);
$user_preferences = $stmt->fetch();

// Get unread announcements
$unread_announcements = [];
$stmt = $pdo->prepare('SELECT a.* FROM announcements a LEFT JOIN user_announcement_reads uar ON a.id = uar.announcement_id AND uar.user_id = ? WHERE a.is_active = 1 AND a.start_date <= NOW() AND (a.end_date IS NULL OR a.end_date > NOW()) AND uar.id IS NULL AND (a.target_audience = "all" OR a.target_audience = "members") ORDER BY a.priority DESC, a.created_at DESC LIMIT 5');
$stmt->execute([$u['id']]);
$unread_announcements = $stmt->fetchAll();

// Get payment history
$payment_history = [];
$stmt = $pdo->prepare('SELECT * FROM payment_receipts WHERE user_id = ? ORDER BY payment_date DESC LIMIT 10');
$stmt->execute([$u['id']]);
$payment_history = $stmt->fetchAll();

// Get check-in/check-out logs
$gym_logs = [];
$stmt = $pdo->prepare('SELECT * FROM gym_check_logs WHERE user_id = ? ORDER BY check_time DESC LIMIT 10');
$stmt->execute([$u['id']]);
$gym_logs = $stmt->fetchAll();

// Get messages
$messages = [];
$stmt = $pdo->prepare('SELECT m.*, u.first_name, u.last_name FROM user_messages m JOIN users u ON m.sender_id = u.id WHERE m.recipient_id = ? ORDER BY m.sent_at DESC LIMIT 10');
$stmt->execute([$u['id']]);
$messages = $stmt->fetchAll();

// Get trainers for messaging
$trainers = [];
$stmt = $pdo->prepare('SELECT u.id, u.first_name, u.last_name FROM users u JOIN user_roles ur ON u.role_id = ur.id WHERE ur.name = "trainer" ORDER BY u.first_name');
$stmt->execute();
$trainers = $stmt->fetchAll();

// Get waitlists
$waitlists = [];
$stmt = $pdo->prepare('SELECT w.*, c.title as class_title, c.start_time FROM waitlists w JOIN classes c ON w.class_id = c.id WHERE w.member_id = ? ORDER BY w.joined_at DESC LIMIT 5');
$stmt->execute([$u['id']]);
$waitlists = $stmt->fetchAll();

// Get membership info
$membership = null;
$stmt = $pdo->prepare('SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = "active" AND m.end_date > NOW()');
$stmt->execute([$u['id']]);
$membership = $stmt->fetch();

// Get workout stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total_bookings FROM bookings WHERE member_id = ? AND status = "attended"');
$stmt->execute([$u['id']]);
$stats = $stmt->fetch();
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>