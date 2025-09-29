<?php
require_once __DIR__ . '/../../config/config.php'; 
require_once __DIR__ . '/../../app/helpers/auth.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_login();
    $user = current_user();
    
    $goal_type = $_POST['goal_type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_value = filter_input(INPUT_POST, 'target_value', FILTER_VALIDATE_FLOAT);
    $current_value = filter_input(INPUT_POST, 'current_value', FILTER_VALIDATE_FLOAT) ?: 0;
    $unit = trim($_POST['unit'] ?? '');
    $target_date = $_POST['target_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    if (!$goal_type || !$title) {
        throw new Exception('Goal type and title are required');
    }
    
    // Validate enum values
    $valid_goal_types = ['weight', 'strength', 'endurance', 'habit', 'body_measurement', 'performance'];
    $valid_priorities = ['low', 'medium', 'high'];
    
    if (!in_array($goal_type, $valid_goal_types)) {
        throw new Exception('Invalid goal type');
    }
    
    if (!in_array($priority, $valid_priorities)) {
        throw new Exception('Invalid priority');
    }
    
    // Validate target date if provided
    if ($target_date && !empty($target_date)) {
        $date = DateTime::createFromFormat('Y-m-d', $target_date);
        if (!$date || $date->format('Y-m-d') !== $target_date) {
            throw new Exception('Invalid target date format');
        }
    } else {
        $target_date = null;
    }
    
    // Create new goal
    $stmt = $pdo->prepare('INSERT INTO user_goals (user_id, goal_type, title, description, target_value, current_value, unit, target_date, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")');
    $stmt->execute([$user['id'], $goal_type, $title, $description, $target_value, $current_value, $unit, $target_date, $priority]);
    
    echo json_encode(['success' => true, 'message' => 'Goal created successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}