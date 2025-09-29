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
    
    $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
    $target_weight = filter_input(INPUT_POST, 'target_weight', FILTER_VALIDATE_FLOAT);
    $fitness_level = $_POST['fitness_level'] ?? 'beginner';
    $primary_goal = $_POST['primary_goal'] ?? 'general_fitness';
    $activity_level = $_POST['activity_level'] ?? 'moderately_active';
    $medical_conditions = trim($_POST['medical_conditions'] ?? '');
    
    // Validate enum values
    $valid_fitness_levels = ['beginner', 'intermediate', 'advanced'];
    $valid_primary_goals = ['weight_loss', 'muscle_gain', 'strength', 'endurance', 'general_fitness'];
    $valid_activity_levels = ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active'];
    
    if (!in_array($fitness_level, $valid_fitness_levels)) {
        throw new Exception('Invalid fitness level');
    }
    
    if (!in_array($primary_goal, $valid_primary_goals)) {
        throw new Exception('Invalid primary goal');
    }
    
    if (!in_array($activity_level, $valid_activity_levels)) {
        throw new Exception('Invalid activity level');
    }
    
    // Check if profile exists
    $stmt = $pdo->prepare('SELECT id FROM user_fitness_profile WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $existing = $stmt->fetchColumn();
    
    if ($existing) {
        // Update existing profile
        $stmt = $pdo->prepare('UPDATE user_fitness_profile SET height = ?, target_weight = ?, fitness_level = ?, primary_goal = ?, activity_level = ?, medical_conditions = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
        $stmt->execute([$height, $target_weight, $fitness_level, $primary_goal, $activity_level, $medical_conditions, $user['id']]);
    } else {
        // Create new profile
        $stmt = $pdo->prepare('INSERT INTO user_fitness_profile (user_id, height, target_weight, fitness_level, primary_goal, activity_level, medical_conditions) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user['id'], $height, $target_weight, $fitness_level, $primary_goal, $activity_level, $medical_conditions]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Fitness profile updated successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}