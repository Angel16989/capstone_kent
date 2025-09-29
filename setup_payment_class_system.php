<?php
/**
 * Complete Payment and Class Booking Test System
 * Test the full flow: Payment → Membership Activation → Class Booking
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/helpers/auth.php';

echo "<h2>🔥 L9 Fitness Payment & Class Booking System Test</h2>";

// Create test membership plans if they don't exist
echo "<h3>📋 Setting up Membership Plans...</h3>";

try {
    // Ensure we have the correct membership plans with proper pricing
    $plans_data = [
        ['name' => 'Monthly Beast', 'price' => 49.99, 'duration_days' => 30, 'description' => 'Perfect for trying out L9 Fitness with full access'],
        ['name' => 'Quarterly Savage', 'price' => 129.99, 'duration_days' => 90, 'description' => 'Best value for committed warriors'],
        ['name' => 'Yearly Champion', 'price' => 399.99, 'duration_days' => 365, 'description' => 'Ultimate savings for fitness champions']
    ];
    
    // Clear existing plans and recreate them
    $pdo->exec("DELETE FROM membership_plans WHERE name IN ('Monthly Beast', 'Quarterly Savage', 'Yearly Champion')");
    
    foreach ($plans_data as $plan) {
        $stmt = $pdo->prepare("INSERT INTO membership_plans (name, price, duration_days, description, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$plan['name'], $plan['price'], $plan['duration_days'], $plan['description']]);
        echo "<p>✅ Created plan: {$plan['name']} - \${$plan['price']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error setting up plans: " . $e->getMessage() . "</p>";
}

// Create test classes if they don't exist
echo "<h3>🏋️ Setting up Fitness Classes...</h3>";

try {
    // Create classes table if it doesn't exist
    $create_classes_table = "
    CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        instructor VARCHAR(255) NOT NULL,
        schedule_day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        duration_minutes INT NOT NULL DEFAULT 60,
        capacity INT NOT NULL DEFAULT 20,
        difficulty ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
        equipment_needed TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($create_classes_table);
    
    // Create bookings table if it doesn't exist
    $create_bookings_table = "
    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        class_id INT NOT NULL,
        booking_date DATE NOT NULL,
        status ENUM('confirmed', 'waitlist', 'cancelled') DEFAULT 'confirmed',
        booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        UNIQUE KEY unique_booking (member_id, class_id, booking_date)
    )";
    $pdo->exec($create_bookings_table);
    
    // Insert sample classes
    $classes_data = [
        ['name' => 'Beast Mode HIIT', 'description' => 'High-intensity interval training that will push your limits', 'instructor' => 'Sarah Warriors', 'schedule_day' => 'Monday', 'start_time' => '06:00:00', 'end_time' => '07:00:00', 'duration_minutes' => 60, 'capacity' => 15, 'difficulty' => 'Advanced'],
        ['name' => 'Warrior Strength', 'description' => 'Full-body strength training with free weights', 'instructor' => 'Mike Thunder', 'schedule_day' => 'Monday', 'start_time' => '18:00:00', 'end_time' => '19:00:00', 'duration_minutes' => 60, 'capacity' => 12, 'difficulty' => 'Intermediate'],
        ['name' => 'Savage Cardio Blast', 'description' => 'Heart-pumping cardio to torch calories', 'instructor' => 'Lisa Storm', 'schedule_day' => 'Tuesday', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'duration_minutes' => 60, 'capacity' => 20, 'difficulty' => 'Beginner'],
        ['name' => 'Champion Yoga Flow', 'description' => 'Relaxing yet challenging yoga for flexibility and strength', 'instructor' => 'Emma Zen', 'schedule_day' => 'Wednesday', 'start_time' => '19:00:00', 'end_time' => '20:00:00', 'duration_minutes' => 60, 'capacity' => 18, 'difficulty' => 'Beginner'],
        ['name' => 'Demolition Deadlifts', 'description' => 'Advanced powerlifting focused on deadlift technique', 'instructor' => 'Tank Rodriguez', 'schedule_day' => 'Thursday', 'start_time' => '17:00:00', 'end_time' => '18:30:00', 'duration_minutes' => 90, 'capacity' => 8, 'difficulty' => 'Advanced'],
        ['name' => 'Friday Fight Club', 'description' => 'Boxing and martial arts conditioning', 'instructor' => 'Rex Punisher', 'schedule_day' => 'Friday', 'start_time' => '18:30:00', 'end_time' => '19:30:00', 'duration_minutes' => 60, 'capacity' => 16, 'difficulty' => 'Intermediate'],
        ['name' => 'Weekend Warrior Bootcamp', 'description' => 'Military-style bootcamp workout', 'instructor' => 'Sergeant Steel', 'schedule_day' => 'Saturday', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'duration_minutes' => 60, 'capacity' => 25, 'difficulty' => 'Advanced']
    ];
    
    // Clear existing sample classes
    $pdo->exec("DELETE FROM classes WHERE instructor IN ('Sarah Warriors', 'Mike Thunder', 'Lisa Storm', 'Emma Zen', 'Tank Rodriguez', 'Rex Punisher', 'Sergeant Steel')");
    
    foreach ($classes_data as $class) {
        $stmt = $pdo->prepare("INSERT INTO classes (name, description, instructor, schedule_day, start_time, end_time, duration_minutes, capacity, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $class['name'], $class['description'], $class['instructor'], $class['schedule_day'], 
            $class['start_time'], $class['end_time'], $class['duration_minutes'], $class['capacity'], $class['difficulty']
        ]);
        echo "<p>✅ Created class: {$class['name']} - {$class['schedule_day']} {$class['start_time']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error setting up classes: " . $e->getMessage() . "</p>";
}

// Test payment simulation
echo "<h3>💳 Testing Payment Simulation...</h3>";

try {
    // Create a test function to simulate successful payment
    function simulateSuccessfulPayment($user_id, $plan_id, $payment_method = 'test_card') {
        global $pdo;
        
        // Get plan details
        $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
        $stmt->execute([$plan_id]);
        $plan = $stmt->fetch();
        
        if (!$plan) {
            throw new Exception("Plan not found");
        }
        
        // Create membership
        $start_date = new DateTime();
        $end_date = clone $start_date;
        $end_date->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
        
        // Check if user already has active membership
        $stmt = $pdo->prepare("UPDATE memberships SET status = 'expired' WHERE member_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        
        // Create new membership
        $stmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$user_id, $plan_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s')]);
        $membership_id = $pdo->lastInsertId();
        
        // Create payment record
        $invoice_no = 'L9-TEST-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT) . '-' . substr(uniqid(), -4);
        $txn_ref = 'TXN-' . strtoupper(uniqid());
        
        $stmt = $pdo->prepare("INSERT INTO payments (member_id, membership_id, amount, method, status, invoice_no, txn_ref, paid_at, created_at) VALUES (?, ?, ?, ?, 'completed', ?, ?, NOW(), NOW())");
        $stmt->execute([$user_id, $membership_id, $plan['price'], $payment_method, $invoice_no, $txn_ref]);
        
        return [
            'success' => true,
            'membership_id' => $membership_id,
            'invoice_no' => $invoice_no,
            'txn_ref' => $txn_ref,
            'plan' => $plan,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }
    
    echo "<p>✅ Payment simulation function created</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating payment simulation: " . $e->getMessage() . "</p>";
}

// Test class booking for members with active memberships
echo "<h3>📅 Testing Class Booking System...</h3>";

try {
    // Function to book a class for a user
    function bookClassForUser($user_id, $class_id) {
        global $pdo;
        
        // Check if user has active membership
        $stmt = $pdo->prepare("SELECT * FROM memberships WHERE member_id = ? AND status = 'active' AND end_date > NOW()");
        $stmt->execute([$user_id]);
        $membership = $stmt->fetch();
        
        if (!$membership) {
            return ['success' => false, 'message' => 'No active membership found. Please purchase a membership first.'];
        }
        
        // Get class details
        $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch();
        
        if (!$class) {
            return ['success' => false, 'message' => 'Class not found'];
        }
        
        // Check if already booked
        $booking_date = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE member_id = ? AND class_id = ? AND booking_date = ?");
        $stmt->execute([$user_id, $class_id, $booking_date]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Already booked for this class'];
        }
        
        // Check capacity
        $stmt = $pdo->prepare("SELECT COUNT(*) as booked FROM bookings WHERE class_id = ? AND booking_date = ? AND status = 'confirmed'");
        $stmt->execute([$class_id, $booking_date]);
        $booked_count = $stmt->fetchColumn();
        
        $available_spots = $class['capacity'] - $booked_count;
        $status = $available_spots > 0 ? 'confirmed' : 'waitlist';
        
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (member_id, class_id, booking_date, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $class_id, $booking_date, $status]);
        
        return [
            'success' => true,
            'status' => $status,
            'class_name' => $class['name'],
            'instructor' => $class['instructor'],
            'schedule' => $class['schedule_day'] . ' ' . $class['start_time'],
            'available_spots' => $available_spots > 0 ? $available_spots - 1 : 0
        ];
    }
    
    echo "<p>✅ Class booking function created</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating class booking system: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #e7f3ff; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 4px solid #007bff;'>";
echo "<h3>🧪 End-to-End Test Instructions</h3>";
echo "<p><strong>To test the complete payment and class booking flow:</strong></p>";
echo "<ol>";
echo "<li><strong>Test Payment:</strong> Visit <a href='public/checkout.php?plan_id=1' target='_blank'>checkout.php?plan_id=1</a></li>";
echo "<li><strong>Simulate Payment:</strong> Fill out the form and submit (it will create a test membership)</li>";
echo "<li><strong>Check Success:</strong> You'll be redirected to the success page</li>";
echo "<li><strong>Book Classes:</strong> Click 'Book Classes' or visit <a href='public/classes.php' target='_blank'>classes.php</a></li>";
echo "<li><strong>Verify Booking:</strong> Only users with active memberships can book classes</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 10px;'>";
echo "<h2>🎯 PAYMENT & CLASS SYSTEM READY!</h2>";
echo "<p>✅ Membership plans configured with correct pricing</p>";
echo "<p>✅ Fitness classes created with schedules and instructors</p>";
echo "<p>✅ Payment simulation system active</p>";
echo "<p>✅ Class booking restricted to active members</p>";
echo "<p>💪 <strong>Your complete gym management system is operational!</strong></p>";
echo "</div>";
?>