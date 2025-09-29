<?php
/**
 * L9 Fitness Payment Test API
 * Handles all payment and booking testing functionality
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config/config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input) {
        $action = $input['action'] ?? $action;
    }
    
    switch ($action) {
        case 'get_users':
            $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM users ORDER BY created_at DESC LIMIT 20");
            $users = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;
            
        case 'create_user':
            $email = $input['email'] ?? '';
            $first_name = $input['first_name'] ?? '';
            $last_name = $input['last_name'] ?? '';
            $password_hash = password_hash($input['password'] ?? 'password123', PASSWORD_DEFAULT);
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User with this email already exists'
                ]);
                break;
            }
            
            // Get member role ID
            $stmt = $pdo->prepare("SELECT id FROM user_roles WHERE name = 'member'");
            $stmt->execute();
            $member_role = $stmt->fetch();
            $role_id = $member_role ? $member_role['id'] : 4; // Default to 4 if not found
            
            $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$role_id, $first_name, $last_name, $email, $password_hash]);
            
            echo json_encode([
                'success' => true,
                'user_id' => $pdo->lastInsertId(),
                'message' => 'Test user created successfully'
            ]);
            break;
            
        case 'get_plans':
            $stmt = $pdo->query("SELECT * FROM membership_plans WHERE is_active = 1 ORDER BY price ASC");
            $plans = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'plans' => $plans
            ]);
            break;
            
        case 'process_payment':
            $user_id = $input['user_id'] ?? 0;
            $plan_id = $input['plan_id'] ?? 0;
            
            if (!$user_id || !$plan_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID and Plan ID are required'
                ]);
                break;
            }
            
            // Get plan details
            $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();
            
            if (!$plan) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Membership plan not found'
                ]);
                break;
            }
            
            // Deactivate existing memberships
            $stmt = $pdo->prepare("UPDATE memberships SET status = 'expired' WHERE member_id = ? AND status = 'active'");
            $stmt->execute([$user_id]);
            
            // Create new membership
            $start_date = new DateTime();
            $end_date = clone $start_date;
            $end_date->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
            
            $stmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$user_id, $plan_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s')]);
            $membership_id = $pdo->lastInsertId();
            
            // Create payment record
            $invoice_no = 'L9-TEST-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT) . '-' . substr(uniqid(), -4);
            $txn_ref = 'TXN-TEST-' . strtoupper(uniqid());
            
            $stmt = $pdo->prepare("INSERT INTO payments (member_id, membership_id, amount, method, status, invoice_no, txn_ref, paid_at) VALUES (?, ?, ?, ?, 'completed', ?, ?, NOW())");
            $stmt->execute([$user_id, $membership_id, $plan['price'], 'test_payment', $invoice_no, $txn_ref]);
            
            echo json_encode([
                'success' => true,
                'membership_id' => $membership_id,
                'invoice_no' => $invoice_no,
                'txn_ref' => $txn_ref,
                'plan_name' => $plan['name'],
                'amount' => $plan['price'],
                'message' => 'Payment processed successfully! Membership activated.'
            ]);
            break;
            
        case 'get_classes':
            $stmt = $pdo->query("SELECT id, title, description, start_time, end_time, instructor_name, location, capacity FROM classes ORDER BY start_time ASC LIMIT 20");
            $classes = $stmt->fetchAll();
            
            // Format times for display
            foreach ($classes as &$class) {
                $class['start_time'] = date('M j, Y g:i A', strtotime($class['start_time']));
                $class['end_time'] = date('g:i A', strtotime($class['end_time']));
            }
            
            echo json_encode([
                'success' => true,
                'classes' => $classes
            ]);
            break;
            
        case 'test_booking':
            $user_id = $input['user_id'] ?? 0;
            $class_id = $input['class_id'] ?? 0;
            
            if (!$user_id || !$class_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID and Class ID are required'
                ]);
                break;
            }
            
            // Check if user has active membership
            $stmt = $pdo->prepare("SELECT m.*, mp.name as plan_name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.member_id = ? AND m.status = 'active' AND m.end_date > NOW()");
            $stmt->execute([$user_id]);
            $membership = $stmt->fetch();
            
            if (!$membership) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User does not have an active membership. Please purchase a membership plan first.'
                ]);
                break;
            }
            
            // Check if class exists
            $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
            $stmt->execute([$class_id]);
            $class = $stmt->fetch();
            
            if (!$class) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Class not found'
                ]);
                break;
            }
            
            // Check if already booked
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE member_id = ? AND class_id = ? AND status != 'cancelled'");
            $stmt->execute([$user_id, $class_id]);
            
            if ($stmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You have already booked this class'
                ]);
                break;
            }
            
            // Check capacity
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE class_id = ? AND status = 'confirmed'");
            $stmt->execute([$class_id]);
            $current_bookings = $stmt->fetchColumn();
            
            if ($current_bookings >= $class['capacity']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This class is fully booked'
                ]);
                break;
            }
            
            // Create booking
            $stmt = $pdo->prepare("INSERT INTO bookings (member_id, class_id, booking_date, status) VALUES (?, ?, NOW(), 'confirmed')");
            $stmt->execute([$user_id, $class_id]);
            
            echo json_encode([
                'success' => true,
                'booking_id' => $pdo->lastInsertId(),
                'message' => "Successfully booked '{$class['title']}' with active {$membership['plan_name']} membership!"
            ]);
            break;
            
        case 'system_status':
            $status = [];
            
            $status['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $status['plans'] = $pdo->query("SELECT COUNT(*) FROM membership_plans WHERE is_active = 1")->fetchColumn();
            $status['classes'] = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
            $status['memberships'] = $pdo->query("SELECT COUNT(*) FROM memberships WHERE status = 'active'")->fetchColumn();
            $status['payments'] = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'completed'")->fetchColumn();
            $status['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'status' => $status
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>