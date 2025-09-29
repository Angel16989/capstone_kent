<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Check if user is logged in and is a trainer
if (!is_logged_in()) {
    http_responfunction handleSendMessage($pdo, $trainer_id) {
    global $current_user;
    
    $to_user = $_POST['to_user'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($to_user) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    $trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$current_user = current_user();
if (!$current_user || ($current_user['role_id'] !== 3 && $current_user['role_id'] !== 1)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$action = $_GET['action'] ?? '';
$trainer_id = $current_user['id'];

try {
    switch ($action) {
        case 'call_in_sick':
            handleCallInSick($pdo, $trainer_id);
            break;
        
        case 'upload_suggestion':
            handleUploadSuggestion($pdo, $trainer_id);
            break;
        
        case 'send_message':
            handleSendMessage($pdo, $trainer_id);
            break;
        
        case 'mark_file_completed':
            handleMarkFileCompleted($pdo, $trainer_id);
            break;
        
        case 'add_customer_suggestion':
            handleAddCustomerSuggestion($pdo, $trainer_id);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Trainer Action Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

function handleCallInSick($pdo, $trainer_id) {
    $class_id = $_POST['class_id'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (empty($class_id) || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Verify the class belongs to this trainer
    $class_check = $pdo->prepare("SELECT name, date, time FROM classes WHERE id = ? AND trainer_id = ?");
    $class_check->execute([$class_id, $trainer_id]);
    $class_info = $class_check->fetch();
    
    if (!$class_info) {
        echo json_encode(['success' => false, 'message' => 'Class not found or access denied']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Create sick leave record
        $sick_leave_stmt = $pdo->prepare("
            INSERT INTO trainer_sick_leaves (trainer_id, class_id, reason, status, created_at) 
            VALUES (?, ?, ?, 'submitted', NOW())
        ");
        $sick_leave_stmt->execute([$trainer_id, $class_id, $reason]);
        
        // Update class status to cancelled
        $update_class = $pdo->prepare("UPDATE classes SET status = 'cancelled' WHERE id = ?");
        $update_class->execute([$class_id]);
        
        // Get all members who booked this class
        $members_query = $pdo->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.email 
            FROM class_bookings cb 
            JOIN users u ON cb.user_id = u.id 
            WHERE cb.class_id = ? AND cb.status = 'confirmed'
        ");
        $members_query->execute([$class_id]);
        $affected_members = $members_query->fetchAll();
        
        // Send notifications to affected members
        $notification_stmt = $pdo->prepare("
            INSERT INTO user_notifications (user_id, title, message, type, created_at) 
            VALUES (?, ?, ?, 'class_cancelled', NOW())
        ");
        
        $class_date = date('M j, Y g:i A', strtotime($class_info['date'] . ' ' . $class_info['time']));
        $notification_title = "Class Cancelled: " . $class_info['name'];
        $notification_message = "Unfortunately, the {$class_info['name']} class scheduled for {$class_date} has been cancelled due to trainer illness. We apologize for any inconvenience.";
        
        foreach ($affected_members as $member) {
            $notification_stmt->execute([$member['id'], $notification_title, $notification_message]);
        }
        
        // Update booking statuses
        $update_bookings = $pdo->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE class_id = ?");
        $update_bookings->execute([$class_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Sick leave submitted and ' . count($affected_members) . ' members notified'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function handleUploadSuggestion($pdo, $trainer_id) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        return;
    }
    
    $file_path = null;
    
    // Handle file upload if present
    if (isset($_FILES['suggestion_file']) && $_FILES['suggestion_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/trainer_suggestions/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_info = pathinfo($_FILES['suggestion_file']['name']);
        $file_name = 'suggestion_' . $trainer_id . '_' . time() . '.' . $file_info['extension'];
        $file_path = $upload_dir . $file_name;
        
        // Validate file size (5MB max)
        if ($_FILES['suggestion_file']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size too large (max 5MB)']);
            return;
        }
        
        // Validate file type
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            return;
        }
        
        if (!move_uploaded_file($_FILES['suggestion_file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'message' => 'File upload failed']);
            return;
        }
        
        $file_path = 'uploads/trainer_suggestions/' . $file_name; // Store relative path
    }
    
    // Insert suggestion into database
    $stmt = $pdo->prepare("
        INSERT INTO trainer_suggestions (trainer_id, title, description, file_path, status, created_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$trainer_id, $title, $description, $file_path]);
    
        // Notify admin about new suggestion
        $admin_notification = $pdo->prepare("
            INSERT INTO admin_notifications (title, message, type, created_at) 
            VALUES (?, ?, 'trainer_suggestion', NOW())
        ");
        
        $trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];
        $notification_title = "New Trainer Suggestion";
        $notification_message = "{$trainer_name} has submitted a new suggestion: {$title}";
        
        $admin_notification->execute([$notification_title, $notification_message]);    echo json_encode(['success' => true, 'message' => 'Suggestion uploaded successfully']);
}

function handleSendMessage($pdo, $trainer_id) {
    $to_user = $_POST['to_user'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($to_user) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
        $trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];    if ($to_user === 'admin') {
        // Send message to admin
        $stmt = $pdo->prepare("
            INSERT INTO trainer_messages (trainer_id, to_admin, subject, message, created_at) 
            VALUES (?, 1, ?, ?, NOW())
        ");
        $stmt->execute([$trainer_id, $subject, $message]);
        
        // Create admin notification
        $admin_notification = $pdo->prepare("
            INSERT INTO admin_notifications (title, message, type, created_at) 
            VALUES (?, ?, 'trainer_message', NOW())
        ");
        
        $notification_title = "Message from Trainer: {$subject}";
        $notification_message = "Trainer {$trainer_name} sent: " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '');
        
        $admin_notification->execute([$notification_title, $notification_message]);
        
        echo json_encode(['success' => true, 'message' => 'Message sent to admin']);
        
    } elseif ($to_user === 'all_members') {
        // Send message to all members in trainer's upcoming classes
        $members_query = $pdo->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name 
            FROM class_bookings cb 
            JOIN classes c ON cb.class_id = c.id 
            JOIN users u ON cb.user_id = u.id 
            WHERE c.trainer_id = ? AND c.date >= CURDATE() AND cb.status = 'confirmed'
        ");
        $members_query->execute([$trainer_id]);
        $members = $members_query->fetchAll();
        
        if (empty($members)) {
            echo json_encode(['success' => false, 'message' => 'No class members found']);
            return;
        }
        
        // Send notification to each member
        $notification_stmt = $pdo->prepare("
            INSERT INTO user_notifications (user_id, title, message, type, created_at) 
            VALUES (?, ?, ?, 'trainer_message', NOW())
        ");
        
        $notification_title = "Message from Trainer {$trainer_name}: {$subject}";
        
        foreach ($members as $member) {
            $notification_stmt->execute([$member['id'], $notification_title, $message]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent to ' . count($members) . ' class members'
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid recipient']);
    }
}

function handleMarkFileCompleted($pdo, $trainer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $file_id = $input['file_id'] ?? '';
    
    if (empty($file_id)) {
        echo json_encode(['success' => false, 'message' => 'File ID is required']);
        return;
    }
    
    // Verify the file is assigned to this trainer
    $check_stmt = $pdo->prepare("SELECT id FROM customer_files WHERE id = ? AND assigned_trainer_id = ?");
    $check_stmt->execute([$file_id, $trainer_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'File not found or access denied']);
        return;
    }
    
    // Update file status
    $update_stmt = $pdo->prepare("UPDATE customer_files SET status = 'completed' WHERE id = ?");
    $update_stmt->execute([$file_id]);
    
    // Notify admin
    $trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];
    $admin_notification = $pdo->prepare("
        INSERT INTO admin_notifications (title, message, type, created_at) 
        VALUES (?, ?, 'file_completed', NOW())
    ");
    
    $notification_title = "Customer File Completed";
    $notification_message = "Trainer {$trainer_name} has completed review of customer file #{$file_id}";
    
    $admin_notification->execute([$notification_title, $notification_message]);
    
    echo json_encode(['success' => true, 'message' => 'File marked as completed']);
}

function handleAddCustomerSuggestion($pdo, $trainer_id) {
    $customer_id = $_POST['customer_id'] ?? '';
    $file_id = $_POST['file_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $suggestion = $_POST['suggestion'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($customer_id) || empty($title) || empty($suggestion)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Verify file access
    $check_stmt = $pdo->prepare("SELECT id FROM customer_files WHERE id = ? AND assigned_trainer_id = ?");
    $check_stmt->execute([$file_id, $trainer_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }
    
    // Insert customer suggestion
    $stmt = $pdo->prepare("
        INSERT INTO customer_suggestions (customer_id, trainer_id, file_id, title, suggestion, priority, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'sent', NOW())
    ");
    $stmt->execute([$customer_id, $trainer_id, $file_id, $title, $suggestion, $priority]);
    
    // Send notification to customer
    $trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];
    $notification_stmt = $pdo->prepare("
        INSERT INTO user_notifications (user_id, title, message, type, created_at) 
        VALUES (?, ?, ?, 'trainer_suggestion', NOW())
    ");
    
    $notification_title = "New Suggestion from Trainer {$trainer_name}";
    $notification_message = "Your trainer has provided a new suggestion: {$title}. Check your profile to view details.";
    
    $notification_stmt->execute([$customer_id, $notification_title, $notification_message]);
    
    echo json_encode(['success' => true, 'message' => 'Suggestion sent to customer']);
}
?>