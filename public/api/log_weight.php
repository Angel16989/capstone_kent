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
    
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $body_fat = filter_input(INPUT_POST, 'body_fat', FILTER_VALIDATE_FLOAT);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$weight || $weight <= 0) {
        throw new Exception('Please enter a valid weight');
    }
    
    // Insert weight progress
    $stmt = $pdo->prepare('INSERT INTO weight_progress (user_id, weight, body_fat_percentage, recorded_date, notes) VALUES (?, ?, ?, CURDATE(), ?)');
    $stmt->execute([$user['id'], $weight, $body_fat, $notes]);
    
    echo json_encode(['success' => true, 'message' => 'Weight logged successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}