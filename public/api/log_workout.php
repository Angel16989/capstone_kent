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
    
    $exercise_name = trim($_POST['exercise_name'] ?? '');
    $exercise_type = $_POST['exercise_type'] ?? '';
    $sets = filter_input(INPUT_POST, 'sets', FILTER_VALIDATE_INT);
    $reps = filter_input(INPUT_POST, 'reps', FILTER_VALIDATE_INT);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    $calories_burned = filter_input(INPUT_POST, 'calories_burned', FILTER_VALIDATE_INT);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$exercise_name) {
        throw new Exception('Please enter an exercise name');
    }
    
    if (!$exercise_type) {
        throw new Exception('Please select an exercise type');
    }
    
    $valid_types = ['strength', 'cardio', 'flexibility', 'balance', 'sports'];
    if (!in_array($exercise_type, $valid_types)) {
        throw new Exception('Invalid exercise type');
    }
    
    // Insert workout progress
    $stmt = $pdo->prepare('INSERT INTO workout_progress (user_id, exercise_name, exercise_type, sets, reps, weight, duration, calories_burned, workout_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)');
    $stmt->execute([$user['id'], $exercise_name, $exercise_type, $sets, $reps, $weight, $duration, $calories_burned, $notes]);
    
    echo json_encode(['success' => true, 'message' => 'Workout logged successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}