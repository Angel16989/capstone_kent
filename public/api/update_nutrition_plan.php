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
    
    $plan_name = trim($_POST['plan_name'] ?? '');
    $diet_type = $_POST['diet_type'] ?? 'standard';
    $daily_calories = filter_input(INPUT_POST, 'daily_calories', FILTER_VALIDATE_INT);
    $daily_protein = filter_input(INPUT_POST, 'daily_protein', FILTER_VALIDATE_FLOAT);
    $daily_carbs = filter_input(INPUT_POST, 'daily_carbs', FILTER_VALIDATE_FLOAT);
    $daily_fat = filter_input(INPUT_POST, 'daily_fat', FILTER_VALIDATE_FLOAT);
    $meals_per_day = filter_input(INPUT_POST, 'meals_per_day', FILTER_VALIDATE_INT) ?: 3;
    $food_allergies = trim($_POST['food_allergies'] ?? '');
    $food_preferences = trim($_POST['food_preferences'] ?? '');
    
    if (!$plan_name) {
        throw new Exception('Plan name is required');
    }
    
    // Validate enum values
    $valid_diet_types = ['standard', 'vegetarian', 'vegan', 'keto', 'paleo', 'mediterranean', 'low_carb', 'high_protein'];
    if (!in_array($diet_type, $valid_diet_types)) {
        throw new Exception('Invalid diet type');
    }
    
    // Convert comma-separated strings to JSON arrays
    $food_allergies_json = null;
    if ($food_allergies) {
        $allergies_array = array_map('trim', explode(',', $food_allergies));
        $food_allergies_json = json_encode($allergies_array);
    }
    
    $food_preferences_json = null;
    if ($food_preferences) {
        $preferences_array = array_map('trim', explode(',', $food_preferences));
        $food_preferences_json = json_encode($preferences_array);
    }
    
    // First, deactivate any existing active plans
    $stmt = $pdo->prepare('UPDATE user_nutrition_profiles SET is_active = 0 WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    // Create new nutrition plan
    $stmt = $pdo->prepare('INSERT INTO user_nutrition_profiles (user_id, plan_name, diet_type, daily_calories, daily_protein, daily_carbs, daily_fat, meals_per_day, food_allergies, food_preferences, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
    $stmt->execute([$user['id'], $plan_name, $diet_type, $daily_calories, $daily_protein, $daily_carbs, $daily_fat, $meals_per_day, $food_allergies_json, $food_preferences_json]);
    
    echo json_encode(['success' => true, 'message' => 'Nutrition plan updated successfully']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}