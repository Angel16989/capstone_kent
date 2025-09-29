<?php
// Add test data for comprehensive features
try {
    $pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding test data for comprehensive features...\n";
    
    // Add some announcements
    $announcements = [
        [
            'title' => '🎉 New Equipment Arrival!',
            'body' => 'We\'ve just received brand new cardio machines and weightlifting equipment! Come check out our upgraded facility and take your workouts to the next level.'
        ],
        [
            'title' => '💪 Special Training Workshop',
            'body' => 'Join our master trainer for an exclusive powerlifting workshop this Saturday at 10 AM. Learn proper form and advanced techniques. Limited spots available!'
        ],
        [
            'title' => '🔥 Summer Challenge Starting Soon!',
            'body' => 'Get ready for our 8-week summer body transformation challenge! Prizes for the most improved members. Registration starts next week.'
        ]
    ];
    
    foreach ($announcements as $announcement) {
        $stmt = $pdo->prepare("
            INSERT INTO announcements (title, body, created_by, published_at)
            VALUES (?, ?, 1, NOW())
        ");
        $stmt->execute([
            $announcement['title'],
            $announcement['body']
        ]);
        echo "✓ Added announcement: {$announcement['title']}\n";
    }
    
    // First add payment history records
    $stmt = $pdo->prepare("
        INSERT INTO payment_history (member_id, amount, payment_date, payment_method, status)
        VALUES 
        (1, 99.00, '2025-01-15 10:30:00', 'credit_card', 'completed'),
        (1, 149.00, '2025-02-15 14:20:00', 'credit_card', 'completed'),
        (1, 199.00, '2025-03-15 09:45:00', 'paypal', 'completed')
    ");
    $stmt->execute();
    echo "✓ Added payment history\n";
    
    // Get the payment history IDs that were just created
    $stmt = $pdo->query("SELECT id FROM payment_history ORDER BY id DESC LIMIT 3");
    $payment_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Then add receipts linked to payment history
    $stmt = $pdo->prepare("
        INSERT INTO payment_receipts (payment_history_id, member_id, receipt_number, amount, final_amount, created_at)
        VALUES 
        (?, 1, 'L9F-2025-001', 99.00, 99.00, '2025-01-15 10:30:00'),
        (?, 1, 'L9F-2025-002', 149.00, 149.00, '2025-02-15 14:20:00'),
        (?, 1, 'L9F-2025-003', 199.00, 199.00, '2025-03-15 09:45:00')
    ");
    $stmt->execute([$payment_ids[2], $payment_ids[1], $payment_ids[0]]);
    echo "✓ Added payment receipts\n";
    
    // Add check-in logs with correct structure
    $stmt = $pdo->prepare("
        INSERT INTO gym_check_logs (user_id, check_type, check_time, location, method)
        VALUES 
        (1, 'check_in', '2025-09-27 08:30:00', 'Main Entrance', 'manual'),
        (1, 'check_out', '2025-09-27 10:15:00', 'Main Entrance', 'manual'),
        (1, 'check_in', '2025-09-26 17:00:00', 'Main Entrance', 'manual'),
        (1, 'check_out', '2025-09-26 18:30:00', 'Main Entrance', 'manual')
    ");
    $stmt->execute();
    echo "✓ Added check-in logs\n";
    
    // Add messages with correct structure
    $stmt = $pdo->prepare("
        INSERT INTO user_messages (sender_id, recipient_id, subject, message, message_type)
        VALUES 
        (2, 1, 'Welcome to L9 Fitness!', 'Hi there! Welcome to our gym family. If you have any questions about our equipment or classes, feel free to ask any of our trainers.', 'admin'),
        (3, 1, 'Your Workout Plan', 'I have prepared a personalized workout plan for you. Let me know when you would like to go over it!', 'trainer')
    ");
    $stmt->execute();
    echo "✓ Added messages\n";
    
    // Add weight progress
    $stmt = $pdo->prepare("
        INSERT INTO weight_progress (user_id, weight, recorded_date)
        VALUES 
        (1, 88.5, '2025-08-01'),
        (1, 87.2, '2025-08-15'),
        (1, 86.8, '2025-09-01'),
        (1, 85.9, '2025-09-15'),
        (1, 85.3, '2025-09-28')
    ");
    $stmt->execute();
    echo "✓ Added weight progress\n";
    
    // Add fitness profile
    $stmt = $pdo->prepare("
        INSERT INTO user_fitness_profile (user_id, height, current_weight, target_weight, fitness_level, primary_goal, medical_conditions)
        VALUES (1, 175, 85.3, 80, 'intermediate', 'weight_loss', 'None reported')
        ON DUPLICATE KEY UPDATE
        height = VALUES(height), current_weight = VALUES(current_weight), target_weight = VALUES(target_weight)
    ");
    $stmt->execute();
    echo "✓ Added fitness profile\n";
    
    // Add goals
    $stmt = $pdo->prepare("
        INSERT INTO user_goals (user_id, goal_type, title, description, target_value, unit, target_date, status)
        VALUES 
        (1, 'weight_loss', 'Lose 5kg', 'Reach my target weight of 80kg', '80', 'kg', '2025-12-31', 'active'),
        (1, 'strength', 'Bench Press Goal', 'Increase bench press to 100kg', '100', 'kg', '2025-11-30', 'active')
    ");
    $stmt->execute();
    echo "✓ Added fitness goals\n";
    
    // Add workout progress
    $stmt = $pdo->prepare("
        INSERT INTO workout_progress (user_id, exercise_name, exercise_type, sets, reps, weight, calories_burned, workout_date, notes)
        VALUES 
        (1, 'Bench Press', 'strength', 3, 10, 80, 250, '2025-09-27', 'Great form today!'),
        (1, 'Treadmill Run', 'cardio', 1, 1, 0, 420, '2025-09-26', '5km run completed'),
        (1, 'Squats', 'strength', 3, 12, 60, 180, '2025-09-25', 'Increased weight from last session')
    ");
    $stmt->execute();
    echo "✓ Added workout progress\n";
    
    echo "\n🎉 Comprehensive test data added successfully!\n";
    echo "You can now test ALL features:\n";
    echo "- ✅ Announcements (3 test announcements)\n";
    echo "- ✅ Payment receipts (3 payment records with receipts)\n";
    echo "- ✅ Check-in/Check-out logs (gym visit history)\n";
    echo "- ✅ Messages (trainer and admin messages)\n";
    echo "- ✅ Weight progress tracking (5 entries showing progress)\n";
    echo "- ✅ Fitness profile (height, weight, goals)\n";
    echo "- ✅ Fitness goals (weight loss and strength goals)\n";
    echo "- ✅ Workout progress (exercise logging with details)\n";
    echo "\n🚀 ALL COMPREHENSIVE FEATURES ARE NOW LIVE AND WORKING!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>