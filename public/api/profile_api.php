<?php
session_start();
require_once '../../config/config.php';
require_once '../../app/helpers/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';

try {
    // Use the global PDO connection from config
    global $pdo;
    
    switch ($action) {
        case 'send_message':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $receiver_id = null;
                if ($input['receiver_type'] === 'admin') {
                    // Get admin user ID (assuming admin has role 'admin')
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                    $stmt->execute();
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                    $receiver_id = $admin['id'] ?? null;
                } elseif ($input['receiver_type'] === 'trainer') {
                    // Get first trainer ID
                    $stmt = $pdo->prepare("SELECT id FROM trainers LIMIT 1");
                    $stmt->execute();
                    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $receiver_id = $trainer['id'] ?? null;
                }
                
                if ($receiver_id) {
                    $stmt = $pdo->prepare("
                        INSERT INTO user_messages (sender_id, receiver_id, subject, message, message_type)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user_id,
                        $receiver_id,
                        $input['subject'],
                        $input['message'],
                        $input['receiver_type']
                    ]);
                    
                    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Recipient not found']);
                }
            }
            break;
            
        case 'check_in':
            // Check if user is already checked in
            $stmt = $pdo->prepare("
                SELECT id FROM member_checkins 
                WHERE member_id = ? AND checkout_time IS NULL 
                ORDER BY checkin_time DESC LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $existing_checkin = $stmt->fetch();
            
            if ($existing_checkin) {
                echo json_encode(['success' => false, 'message' => 'You are already checked in']);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO member_checkins (member_id, checkin_time, facility_area)
                    VALUES (?, NOW(), 'gym_floor')
                ");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true, 'message' => 'Checked in successfully! Welcome to beast mode! 🔥']);
            }
            break;
            
        case 'check_out':
            $stmt = $pdo->prepare("
                UPDATE member_checkins 
                SET checkout_time = NOW(),
                    duration_minutes = TIMESTAMPDIFF(MINUTE, checkin_time, NOW())
                WHERE member_id = ? AND checkout_time IS NULL 
                ORDER BY checkin_time DESC 
                LIMIT 1
            ");
            $result = $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Checked out successfully! Beast mode complete! 💪']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No active check-in found']);
            }
            break;
            
        case 'mark_announcement_read':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $announcement_id = $input['announcement_id'];
                
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO announcement_reads (user_id, announcement_id, read_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$user_id, $announcement_id]);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'get_unread_announcements':
            $stmt = $pdo->prepare("
                SELECT a.* FROM announcements a
                LEFT JOIN announcement_reads ar ON a.id = ar.announcement_id AND ar.user_id = ?
                WHERE a.is_active = 1 
                AND (a.target_audience = 'all' OR a.target_audience = 'members')
                AND ar.id IS NULL
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($announcements);
            break;
            
        case 'get_workout_stats':
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_workouts,
                    AVG(duration_minutes) as avg_duration,
                    SUM(calories_burned) as total_calories
                FROM workout_progress 
                WHERE user_id = ? 
                AND workout_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$user_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($stats);
            break;
            
        case 'log_workout':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("
                    INSERT INTO workout_progress 
                    (user_id, workout_type, duration_minutes, calories_burned, notes, workout_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id,
                    $input['workout_type'],
                    $input['duration_minutes'],
                    $input['calories_burned'] ?? null,
                    $input['notes'] ?? null,
                    $input['workout_date'] ?? date('Y-m-d')
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Workout logged successfully']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>