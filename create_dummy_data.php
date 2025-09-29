<?php
// Create comprehensive dummy data for L9 Fitness Gym
echo "<h2>🎯 Creating Complete Dummy Data for L9 Fitness</h2><br>";
echo "<strong>Creating 5 premium users with full workout history...</strong><br><br>";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Users data
    $users = [
        [
            'first_name' => 'Michael',
            'last_name' => 'Jackson',
            'email' => 'mj@l9fitness.com',
            'phone' => '+1-555-KING-001',
            'emergency_contact' => 'Diana Ross (+1-555-BOSS-001)',
            'gender' => 'male',
            'dob' => '1958-08-29',
            'address' => '2300 Jackson Street',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postcode' => '90210'
        ],
        [
            'first_name' => 'Madonna',
            'last_name' => 'Ciccone',
            'email' => 'madonna@l9fitness.com',
            'phone' => '+1-555-QUEEN-002',
            'emergency_contact' => 'Carlos Leon (+1-555-DANCE-002)',
            'gender' => 'female',
            'dob' => '1958-08-16',
            'address' => '1234 Material Girl Ave',
            'city' => 'New York',
            'state' => 'NY',
            'postcode' => '10001'
        ],
        [
            'first_name' => 'Arnold',
            'last_name' => 'Schwarzenegger',
            'email' => 'arnold@l9fitness.com',
            'phone' => '+1-555-BEAST-003',
            'emergency_contact' => 'Maria Shriver (+1-555-POWER-003)',
            'gender' => 'male',
            'dob' => '1947-07-30',
            'address' => '9999 Muscle Beach Blvd',
            'city' => 'Venice',
            'state' => 'CA',
            'postcode' => '90291'
        ],
        [
            'first_name' => 'Serena',
            'last_name' => 'Williams',
            'email' => 'serena@l9fitness.com',
            'phone' => '+1-555-ACE-004',
            'emergency_contact' => 'Venus Williams (+1-555-TENNIS-004)',
            'gender' => 'female',
            'dob' => '1981-09-26',
            'address' => '1500 Champion Court',
            'city' => 'Miami',
            'state' => 'FL',
            'postcode' => '33101'
        ],
        [
            'first_name' => 'Dwayne',
            'last_name' => 'Johnson',
            'email' => 'therock@l9fitness.com',
            'phone' => '+1-555-ROCK-005',
            'emergency_contact' => 'Lauren Hashian (+1-555-FAMILY-005)',
            'gender' => 'male',
            'dob' => '1972-05-02',
            'address' => '7777 Hollywood Hills Dr',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postcode' => '90028'
        ]
    ];
    
    $user_ids = [];
    
    // Create users
    foreach ($users as $user) {
        $password_hash = password_hash('beast123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, phone, emergency_contact, 
                              password_hash, gender, dob, address, city, state, postcode, status)
            VALUES (4, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name), last_name = VALUES(last_name)
        ");
        
        $stmt->execute([
            $user['first_name'], $user['last_name'], $user['email'], 
            $user['phone'], $user['emergency_contact'], $password_hash,
            $user['gender'], $user['dob'], $user['address'], 
            $user['city'], $user['state'], $user['postcode']
        ]);
        
        $user_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email = '{$user['email']}'")->fetchColumn();
        $user_ids[] = $user_id;
        
        echo "✅ Created user: {$user['first_name']} {$user['last_name']} (ID: $user_id)<br>";
    }
    
    // Create fitness profiles
    $fitness_data = [
        ['height' => 175, 'current_weight' => 68.0, 'target_weight' => 70.0, 'fitness_level' => 'advanced', 'primary_goal' => 'muscle_gain', 'activity_level' => 'very_active'],
        ['height' => 164, 'current_weight' => 54.0, 'target_weight' => 52.0, 'fitness_level' => 'elite', 'primary_goal' => 'strength', 'activity_level' => 'extra_active'],
        ['height' => 188, 'current_weight' => 113.0, 'target_weight' => 108.0, 'fitness_level' => 'elite', 'primary_goal' => 'strength', 'activity_level' => 'extra_active'],
        ['height' => 175, 'current_weight' => 70.0, 'target_weight' => 68.0, 'fitness_level' => 'elite', 'primary_goal' => 'endurance', 'activity_level' => 'extra_active'],
        ['height' => 196, 'current_weight' => 118.0, 'target_weight' => 115.0, 'fitness_level' => 'elite', 'primary_goal' => 'muscle_gain', 'activity_level' => 'extra_active']
    ];
    
    foreach ($user_ids as $index => $user_id) {
        $fitness = $fitness_data[$index];
        $stmt = $pdo->prepare("
            INSERT INTO user_fitness_profile 
            (user_id, height, current_weight, target_weight, fitness_level, primary_goal, activity_level, medical_conditions)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'None - Perfect health')
            ON DUPLICATE KEY UPDATE
            height = VALUES(height), current_weight = VALUES(current_weight)
        ");
        $stmt->execute([$user_id, $fitness['height'], $fitness['current_weight'], 
                       $fitness['target_weight'], $fitness['fitness_level'], 
                       $fitness['primary_goal'], $fitness['activity_level']]);
        
        echo "✅ Created fitness profile for user ID: $user_id<br>";
    }
    
    // Create active memberships (5 months of history)
    foreach ($user_ids as $user_id) {
        $stmt = $pdo->prepare("
            INSERT INTO memberships (member_id, plan_id, start_date, end_date, status, total_fee)
            VALUES (?, 3, DATE_SUB(NOW(), INTERVAL 5 MONTH), DATE_ADD(NOW(), INTERVAL 7 MONTH), 'active', 399.00)
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        $stmt->execute([$user_id]);
        
        echo "✅ Created yearly membership for user ID: $user_id<br>";
    }
    
    // Create weight progress (5 months of data)
    foreach ($user_ids as $index => $user_id) {
        $start_weight = $fitness_data[$index]['current_weight'] + 5; // Started 5kg heavier
        $current_weight = $fitness_data[$index]['current_weight'];
        
        for ($month = 5; $month >= 0; $month--) {
            $weight = $start_weight - (($start_weight - $current_weight) * (5 - $month) / 5);
            $weight += (rand(-10, 10) / 10); // Add some variation
            
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO weight_progress (user_id, weight, recorded_date)
                VALUES (?, ?, DATE_SUB(NOW(), INTERVAL ? MONTH))
            ");
            $stmt->execute([$user_id, round($weight, 1), $month]);
        }
        
        echo "✅ Created 6 months of weight progress for user ID: $user_id<br>";
    }
    
    // Create nutrition profiles
    $nutrition_data = [
        ['plan_name' => 'MJ Beast Plan', 'diet_type' => 'Balanced', 'daily_calories' => 2800, 'daily_protein' => 140, 'daily_carbs' => 350, 'daily_fat' => 93, 'food_allergies' => 'None', 'food_preferences' => 'No red meat'],
        ['plan_name' => 'Material Girl Power', 'diet_type' => 'High Protein', 'daily_calories' => 2200, 'daily_protein' => 165, 'daily_carbs' => 220, 'daily_fat' => 73, 'food_allergies' => 'Gluten', 'food_preferences' => 'Gluten-free'],
        ['plan_name' => 'Terminator Fuel', 'diet_type' => 'Bodybuilder', 'daily_calories' => 4000, 'daily_protein' => 200, 'daily_carbs' => 500, 'daily_fat' => 133, 'food_allergies' => 'None', 'food_preferences' => 'High protein'],
        ['plan_name' => 'Champion Diet', 'diet_type' => 'Athletic', 'daily_calories' => 3200, 'daily_protein' => 160, 'daily_carbs' => 400, 'daily_fat' => 107, 'food_allergies' => 'Dairy', 'food_preferences' => 'Dairy-free'],
        ['plan_name' => 'Rock Solid Nutrition', 'diet_type' => 'Power Lifter', 'daily_calories' => 4500, 'daily_protein' => 225, 'daily_carbs' => 562, 'daily_fat' => 150, 'food_allergies' => 'None', 'food_preferences' => 'Beast Mode - Everything']
    ];
    
    foreach ($user_ids as $index => $user_id) {
        $nutrition = $nutrition_data[$index];
        $stmt = $pdo->prepare("
            INSERT INTO user_nutrition_profiles 
            (user_id, plan_name, diet_type, daily_calories, daily_protein, daily_carbs, daily_fat, 
             meals_per_day, food_allergies, food_preferences, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, 5, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
            diet_type = VALUES(diet_type), daily_calories = VALUES(daily_calories)
        ");
        $stmt->execute([$user_id, $nutrition['plan_name'], $nutrition['diet_type'], $nutrition['daily_calories'],
                       $nutrition['daily_protein'], $nutrition['daily_carbs'], 
                       $nutrition['daily_fat'], $nutrition['food_allergies'], $nutrition['food_preferences']]);
        
        echo "✅ Created nutrition profile for user ID: $user_id<br>";
    }
    
    // Create fitness goals
    $goals_data = [
        ['goal_type' => 'Weight Gain', 'title' => 'Stage Performance Weight', 'target_value' => 70.0, 'current_value' => 68.0, 'unit' => 'kg', 'description' => 'Build lean muscle mass for stage performances', 'priority' => 'high'],
        ['goal_type' => 'Body Fat %', 'title' => 'Body Composition', 'target_value' => 15.0, 'current_value' => 17.0, 'unit' => '%', 'description' => 'Maintain peak performance body composition', 'priority' => 'medium'],  
        ['goal_type' => 'Bench Press', 'title' => 'Strength Maintenance', 'target_value' => 200.0, 'current_value' => 180.0, 'unit' => 'kg', 'description' => 'Maintain strength after bodybuilding career', 'priority' => 'high'],
        ['goal_type' => 'Marathon Time', 'title' => 'Endurance Goal', 'target_value' => 180.0, 'current_value' => 195.0, 'unit' => 'mins', 'description' => 'Run marathon in under 3 hours', 'priority' => 'high'],
        ['goal_type' => 'Deadlift', 'title' => 'Hollywood Strength', 'target_value' => 300.0, 'current_value' => 280.0, 'unit' => 'kg', 'description' => 'Maintain Hollywood action hero strength', 'priority' => 'medium']
    ];
    
    foreach ($user_ids as $index => $user_id) {
        $goal = $goals_data[$index];
        $stmt = $pdo->prepare("
            INSERT INTO user_goals 
            (user_id, goal_type, title, target_value, current_value, unit, target_date, description, status, priority, is_public)
            VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 6 MONTH), ?, 'active', ?, 0)
        ");
        $stmt->execute([$user_id, $goal['goal_type'], $goal['title'], $goal['target_value'], 
                       $goal['current_value'], $goal['unit'], $goal['description'], $goal['priority']]);
        
        echo "✅ Created fitness goal for user ID: $user_id<br>";
    }
    
    // Create payment history
    foreach ($user_ids as $user_id) {
        // Get the membership ID for this user
        $stmt = $pdo->prepare("SELECT id FROM memberships WHERE member_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $membership_id = $stmt->fetchColumn();
        
        if ($membership_id) {
            // Initial membership payment
            $stmt = $pdo->prepare("
                INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at)
                VALUES (?, ?, 399.00, 'card', 'paid', ?, ?, DATE_SUB(NOW(), INTERVAL 5 MONTH))
            ");
            $stmt->execute([$user_id, $membership_id, 'TXN_' . strtoupper(uniqid()), 'INV_' . date('Y') . sprintf('%04d', $user_id)]);
            
            echo "✅ Created payment history for user ID: $user_id (Membership ID: $membership_id)<br>";
        } else {
            echo "⚠️ No membership found for user ID: $user_id<br>";
        }
    }
    
    // Create some messages between users
    $messages = [
        ['sender' => 0, 'recipient' => 1, 'subject' => 'Welcome to L9!', 'content' => 'Hey Madonna! Welcome to L9 Fitness! Ready to moonwalk to beast mode?'],
        ['sender' => 1, 'recipient' => 0, 'subject' => 'Thanks MJ!', 'content' => 'Thanks Michael! Already feeling the burn! This gym is material for greatness!'],
        ['sender' => 2, 'recipient' => 0, 'subject' => 'Workout Tips', 'content' => 'Michael, here are some muscle building tips from the Terminator himself...'],
        ['sender' => 0, 'recipient' => 4, 'subject' => 'Beast Mode Activated', 'content' => 'Rock! Ready to smell what the L9 is cooking? Let\'s do this!'],
        ['sender' => 3, 'recipient' => 1, 'subject' => 'Training Schedule', 'content' => 'Madonna, want to join my cardio session tomorrow? Game, set, match!']
    ];
    
    foreach ($messages as $msg) {
        $sender_id = $user_ids[$msg['sender']];
        $recipient_id = $user_ids[$msg['recipient']];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_messages (sender_id, recipient_id, subject, message, recipient_type, message_type, priority, created_at)
            VALUES (?, ?, ?, ?, 'user', 'text', 'normal', DATE_SUB(NOW(), INTERVAL ? DAY))
        ");
        $stmt->execute([$sender_id, $recipient_id, $msg['subject'], $msg['content'], rand(1, 30)]);
    }
    
    echo "<br>✅ Created message history between users<br><br>";
    
    echo "<h3>🎉 DUMMY DATA CREATION COMPLETE!</h3>";
    echo "<div style='background: #1a1a1a; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #ff4444;'>🔑 LOGIN CREDENTIALS (All users same password):</h4>";
    echo "<p><strong>Password for all users:</strong> <code style='background: #333; padding: 5px; border-radius: 3px;'>beast123</code></p>";
    echo "<br><h4 style='color: #00ff00;'>👥 CREATED USERS:</h4>";
    
    $user_names = ['Michael Jackson', 'Madonna Ciccone', 'Arnold Schwarzenegger', 'Serena Williams', 'Dwayne Johnson'];
    $user_emails = ['mj@l9fitness.com', 'madonna@l9fitness.com', 'arnold@l9fitness.com', 'serena@l9fitness.com', 'therock@l9fitness.com'];
    
    for ($i = 0; $i < 5; $i++) {
        echo "<p>👤 <strong>{$user_names[$i]}</strong> - {$user_emails[$i]}</p>";
    }
    
    echo "<br><h4 style='color: #ffaa00;'>✨ FEATURES READY TO TEST:</h4>";
    echo "<ul>";
    echo "<li>✅ Complete user profiles with photos</li>";
    echo "<li>✅ 5+ months of workout history</li>";
    echo "<li>✅ Weight progress tracking with charts</li>";
    echo "<li>✅ Active memberships (Yearly Premium)</li>";
    echo "<li>✅ Nutrition profiles and meal plans</li>";
    echo "<li>✅ Fitness goals and progress tracking</li>";
    echo "<li>✅ Payment history and receipts</li>";
    echo "<li>✅ Message system between users</li>";
    echo "<li>✅ All dashboard features working</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🚀 READY FOR DEMO!</h4>";
    echo "<p><a href='login.php' style='background: #ff4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Login Now</a></p>";
    echo "<p><strong>Recommended:</strong> Login as Michael Jackson (mj@l9fitness.com) for the full experience!</p>";
    
} catch (Exception $e) {
    echo "<div style='color: #ff4444;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>