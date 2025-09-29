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
    
    $goal_id = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);
    $current_value = filter_input(INPUT_POST, 'current_value', FILTER_VALIDATE_FLOAT);
    
    if (!$goal_id || $current_value === false) {
        throw new Exception('Goal ID and current value are required');
    }
    
    // Verify goal belongs to user
    $stmt = $pdo->prepare('SELECT id, target_value FROM user_goals WHERE id = ? AND user_id = ?');
    $stmt->execute([$goal_id, $user['id']]);
    $goal = $stmt->fetch();
    
    if (!$goal) {
        throw new Exception('Goal not found');
    }
    
    // Update goal progress
    $stmt = $pdo->prepare('UPDATE user_goals SET current_value = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->execute([$current_value, $goal_id, $user['id']]);
    
    // Check if goal is completed
    if ($goal['target_value'] && $current_value >= $goal['target_value']) {
        $stmt = $pdo->prepare('UPDATE user_goals SET status = "completed" WHERE id = ? AND user_id = ?');
        $stmt->execute([$goal_id, $user['id']]);
        echo json_encode(['success' => true, 'message' => 'Goal progress updated - Congratulations, goal completed!']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Goal progress updated successfully']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}