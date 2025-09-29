<?php
/**
 * Offline Plans API
 * Provides diet and workout plans data for offline caching
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$user = current_user();
$action = $_GET['action'] ?? 'all';

try {
    $response = ['success' => true, 'data' => [], 'timestamp' => time()];
    
    switch ($action) {
        case 'diet_plans':
            // Get user's diet/nutrition plans
            $stmt = $pdo->prepare("
                SELECT np.*, mp.name as meal_plan_name, np.created_at, np.updated_at
                FROM user_nutrition_profiles np
                LEFT JOIN meal_plans mp ON np.meal_plan_id = mp.id
                WHERE np.user_id = ? AND np.is_active = 1
                ORDER BY np.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $diet_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get meal details for each plan
            foreach ($diet_plans as &$plan) {
                if ($plan['meal_plan_id']) {
                    $stmt = $pdo->prepare("
                        SELECT md.*, f.name as food_name, f.calories_per_100g, f.protein_per_100g, f.carbs_per_100g, f.fat_per_100g
                        FROM meal_details md
                        JOIN foods f ON md.food_id = f.id
                        WHERE md.meal_plan_id = ?
                        ORDER BY md.meal_type, md.order_index
                    ");
                    $stmt->execute([$plan['meal_plan_id']]);
                    $plan['meals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            $response['data']['diet_plans'] = $diet_plans;
            break;
            
        case 'workout_plans':
            // Get user's workout plans
            $stmt = $pdo->prepare("
                SELECT wp.*, wt.name as template_name, wp.created_at, wp.updated_at
                FROM user_workout_plans wp
                LEFT JOIN workout_templates wt ON wp.template_id = wt.id
                WHERE wp.user_id = ? AND wp.is_active = 1
                ORDER BY wp.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $workout_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get workout details for each plan
            foreach ($workout_plans as &$plan) {
                if ($plan['template_id']) {
                    $stmt = $pdo->prepare("
                        SELECT we.*, e.name as exercise_name, e.description, e.muscle_groups, e.equipment_needed,
                               e.difficulty_level, e.instructions
                        FROM workout_exercises we
                        JOIN exercises e ON we.exercise_id = e.id
                        WHERE we.template_id = ?
                        ORDER BY we.day_number, we.order_index
                    ");
                    $stmt->execute([$plan['template_id']]);
                    $plan['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            $response['data']['workout_plans'] = $workout_plans;
            break;
            
        case 'progress':
            // Get recent progress data
            $stmt = $pdo->prepare("
                SELECT 'weight' as type, weight as value, recorded_date as date, notes
                FROM weight_progress 
                WHERE user_id = ? 
                ORDER BY recorded_date DESC 
                LIMIT 30
            ");
            $stmt->execute([$user['id']]);
            $weight_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("
                SELECT 'workout' as type, exercise_name, sets_completed, reps_completed, weight_used, workout_date as date
                FROM workout_progress 
                WHERE user_id = ? 
                ORDER BY workout_date DESC 
                LIMIT 50
            ");
            $stmt->execute([$user['id']]);
            $workout_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['data']['progress'] = [
                'weight' => $weight_progress,
                'workouts' => $workout_progress
            ];
            break;
            
        case 'user_profile':
            // Get user fitness profile
            $stmt = $pdo->prepare("
                SELECT ufp.*, u.first_name, u.last_name, u.email
                FROM user_fitness_profile ufp
                JOIN users u ON ufp.user_id = u.id
                WHERE ufp.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response['data']['user_profile'] = $profile;
            break;
            
        case 'all':
        default:
            // Get all data for offline caching
            
            // Diet plans
            $stmt = $pdo->prepare("
                SELECT np.*, mp.name as meal_plan_name
                FROM user_nutrition_profiles np
                LEFT JOIN meal_plans mp ON np.meal_plan_id = mp.id
                WHERE np.user_id = ? AND np.is_active = 1
            ");
            $stmt->execute([$user['id']]);
            $diet_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($diet_plans as &$plan) {
                if ($plan['meal_plan_id']) {
                    $stmt = $pdo->prepare("
                        SELECT md.*, f.name as food_name, f.calories_per_100g, f.protein_per_100g, f.carbs_per_100g, f.fat_per_100g
                        FROM meal_details md
                        JOIN foods f ON md.food_id = f.id
                        WHERE md.meal_plan_id = ?
                        ORDER BY md.meal_type, md.order_index
                    ");
                    $stmt->execute([$plan['meal_plan_id']]);
                    $plan['meals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            // Workout plans
            $stmt = $pdo->prepare("
                SELECT wp.*, wt.name as template_name
                FROM user_workout_plans wp
                LEFT JOIN workout_templates wt ON wp.template_id = wt.id
                WHERE wp.user_id = ? AND wp.is_active = 1
            ");
            $stmt->execute([$user['id']]);
            $workout_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($workout_plans as &$plan) {
                if ($plan['template_id']) {
                    $stmt = $pdo->prepare("
                        SELECT we.*, e.name as exercise_name, e.description, e.muscle_groups, e.equipment_needed,
                               e.difficulty_level, e.instructions
                        FROM workout_exercises we
                        JOIN exercises e ON we.exercise_id = e.id
                        WHERE we.template_id = ?
                        ORDER BY we.day_number, we.order_index
                    ");
                    $stmt->execute([$plan['template_id']]);
                    $plan['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            // User profile
            $stmt = $pdo->prepare("
                SELECT ufp.*, u.first_name, u.last_name, u.email
                FROM user_fitness_profile ufp
                JOIN users u ON ufp.user_id = u.id
                WHERE ufp.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Recent progress
            $stmt = $pdo->prepare("
                SELECT weight, recorded_date, notes
                FROM weight_progress 
                WHERE user_id = ? 
                ORDER BY recorded_date DESC 
                LIMIT 10
            ");
            $stmt->execute([$user['id']]);
            $weight_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['data'] = [
                'diet_plans' => $diet_plans,
                'workout_plans' => $workout_plans,
                'user_profile' => $profile,
                'recent_progress' => $weight_progress,
                'offline_mode' => false,
                'last_sync' => time()
            ];
            break;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Offline Plans API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch plans data',
        'message' => $e->getMessage()
    ]);
}
?>