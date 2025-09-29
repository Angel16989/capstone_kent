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
    
    if (!$goal_id) {
        throw new Exception('Goal ID is required');
    }
    
    // Verify goal belongs to user
    $stmt = $pdo->prepare('SELECT id FROM user_goals WHERE id = ? AND user_id = ?');
    $stmt->execute([$goal_id, $user['id']]);
    $goal = $stmt->fetch();
    
    if (!$goal) {
        throw new Exception('Goal not found');
    }
    
    // Delete goal
    $stmt = $pdo->prepare('DELETE FROM user_goals WHERE id = ? AND user_id = ?');
    $stmt->execute([$goal_id, $user['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Goal deleted successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}