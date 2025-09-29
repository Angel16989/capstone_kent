<?php
/**
 * L9 Fitness Class System Setup
 * Adds comprehensive class schedule and booking system
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>🏋️ L9 FITNESS CLASS SYSTEM SETUP 🏋️</h2>";

// First, ensure we have trainers
echo "<h3>👨‍💪 Setting Up Trainers:</h3>";
try {
    // Clear existing trainers first
    $pdo->exec("DELETE FROM trainers");
    
    // Add comprehensive trainer roster
    $trainers = [
        [
            'first_name' => 'Sarah',
            'last_name' => 'Warriors',
            'email' => 'sarah.warriors@l9fitness.com',
            'specialization' => 'HIIT & Combat Training',
            'bio' => 'Former MMA fighter turned fitness warrior. Specializes in high-intensity combat training and functional fitness.',
            'rate' => 85.00
        ],
        [
            'first_name' => 'Mike',
            'last_name' => 'Thunder',
            'email' => 'mike.thunder@l9fitness.com',
            'specialization' => 'Strength & Powerlifting',
            'bio' => 'Powerlifting champion with 10+ years experience. Master of compound movements and strength building.',
            'rate' => 90.00
        ],
        [
            'first_name' => 'Lisa',
            'last_name' => 'Storm',
            'email' => 'lisa.storm@l9fitness.com',
            'specialization' => 'Cardio & Endurance',
            'bio' => 'Former marathon runner and triathlete. Expert in cardiovascular conditioning and endurance training.',
            'rate' => 75.00
        ],
        [
            'first_name' => 'Emma',
            'last_name' => 'Zen',
            'email' => 'emma.zen@l9fitness.com',
            'specialization' => 'Yoga & Flexibility',
            'bio' => 'Certified yoga instructor and mindfulness coach. Brings balance to your hardcore training routine.',
            'rate' => 70.00
        ],
        [
            'first_name' => 'Jake',
            'last_name' => 'Beast',
            'email' => 'jake.beast@l9fitness.com',
            'specialization' => 'CrossFit & Functional Training',
            'bio' => 'CrossFit Level 4 trainer. Specializes in functional movements and metabolic conditioning.',
            'rate' => 95.00
        ],
        [
            'first_name' => 'Alex',
            'last_name' => 'Viper',
            'email' => 'alex.viper@l9fitness.com',
            'specialization' => 'Boxing & Martial Arts',
            'bio' => 'Professional boxer and martial arts instructor. Teaches striking, defense, and combat conditioning.',
            'rate' => 100.00
        ]
    ];
    
    // Get trainer role ID
    $stmt = $pdo->prepare("SELECT id FROM user_roles WHERE name = 'trainer'");
    $stmt->execute();
    $trainer_role = $stmt->fetch();
    $trainer_role_id = $trainer_role ? $trainer_role['id'] : 3;
    
    foreach ($trainers as $trainer) {
        // Create user account for trainer
        $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $trainer_role_id,
            $trainer['first_name'],
            $trainer['last_name'],
            $trainer['email'],
            password_hash('trainer123', PASSWORD_DEFAULT)
        ]);
        $user_id = $pdo->lastInsertId();
        
        // Create trainer profile
        $stmt = $pdo->prepare("INSERT INTO trainers (user_id, bio, certifications, rate_per_session) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $trainer['bio'],
            $trainer['specialization'],
            $trainer['rate']
        ]);
        
        echo "<p>✅ Created trainer: {$trainer['first_name']} {$trainer['last_name']} - {$trainer['specialization']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error setting up trainers: " . $e->getMessage() . "</p>";
}

// Clear existing classes and add comprehensive schedule
echo "<h3>📅 Setting Up Class Schedule:</h3>";
try {
    // Clear existing test classes
    $pdo->exec("DELETE FROM classes");
    
    // Get trainer IDs
    $trainers = $pdo->query("SELECT u.id, u.first_name, u.last_name, t.certifications FROM users u JOIN trainers t ON u.id = t.user_id")->fetchAll();
    
    $class_schedule = [];
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Create comprehensive weekly schedule
    foreach ($days as $day_index => $day) {
        $date = new DateTime();
        $date->modify("next $day");
        
        // Morning classes (6 AM - 11 AM)
        $morning_classes = [
            [
                'title' => 'Beast Mode HIIT',
                'description' => 'High-intensity interval training designed to push your limits. Explosive movements, cardio bursts, and strength challenges.',
                'time' => '06:00',
                'duration' => 60,
                'trainer_specialty' => 'HIIT',
                'location' => 'Studio A',
                'capacity' => 15,
                'difficulty' => 'Advanced',
                'equipment' => 'Kettlebells, Battle Ropes, Plyometric Boxes'
            ],
            [
                'title' => 'Warrior Strength',
                'description' => 'Build serious muscle and strength with compound movements. Deadlifts, squats, presses, and more.',
                'time' => '07:30',
                'duration' => 75,
                'trainer_specialty' => 'Strength',
                'location' => 'Weight Room',
                'capacity' => 12,
                'difficulty' => 'Intermediate',
                'equipment' => 'Barbells, Dumbbells, Power Racks'
            ],
            [
                'title' => 'Cardio Storm',
                'description' => 'Heart-pumping cardio session mixing treadmills, bikes, and functional movements.',
                'time' => '09:00',
                'duration' => 45,
                'trainer_specialty' => 'Cardio',
                'location' => 'Cardio Zone',
                'capacity' => 20,
                'difficulty' => 'Beginner',
                'equipment' => 'Treadmills, Bikes, Rowing Machines'
            ],
            [
                'title' => 'Zen Flow Yoga',
                'description' => 'Restore balance with mindful yoga flows. Perfect recovery session for hardcore training.',
                'time' => '10:30',
                'duration' => 60,
                'trainer_specialty' => 'Yoga',
                'location' => 'Studio B',
                'capacity' => 18,
                'difficulty' => 'Beginner',
                'equipment' => 'Yoga Mats, Blocks, Straps'
            ]
        ];
        
        // Evening classes (5 PM - 8 PM)
        $evening_classes = [
            [
                'title' => 'CrossFit Chaos',
                'description' => 'Functional fitness at its finest. WODs that will test your limits and build mental toughness.',
                'time' => '17:00',
                'duration' => 60,
                'trainer_specialty' => 'CrossFit',
                'location' => 'CrossFit Box',
                'capacity' => 16,
                'difficulty' => 'Advanced',
                'equipment' => 'Olympic Bars, Rings, Assault Bikes'
            ],
            [
                'title' => 'Combat Training',
                'description' => 'Learn striking techniques, footwork, and conditioning. Boxing, kickboxing, and martial arts fundamentals.',
                'time' => '18:30',
                'duration' => 75,
                'trainer_specialty' => 'Boxing',
                'location' => 'Combat Zone',
                'capacity' => 14,
                'difficulty' => 'Intermediate',
                'equipment' => 'Heavy Bags, Pads, Gloves'
            ],
            [
                'title' => 'Power Hour',
                'description' => 'Maximum intensity strength training. Heavy lifting, explosive movements, and serious gains.',
                'time' => '19:30',
                'duration' => 60,
                'trainer_specialty' => 'Strength',
                'location' => 'Weight Room',
                'capacity' => 10,
                'difficulty' => 'Advanced',
                'equipment' => 'Heavy Barbells, Chains, Bands'
            ]
        ];
        
        // Add classes for this day
        $daily_classes = array_merge($morning_classes, $evening_classes);
        
        foreach ($daily_classes as $class_info) {
            // Find appropriate trainer
            $trainer = null;
            foreach ($trainers as $t) {
                if (strpos($t['certifications'], $class_info['trainer_specialty']) !== false) {
                    $trainer = $t;
                    break;
                }
            }
            
            if (!$trainer) {
                $trainer = $trainers[array_rand($trainers)]; // Fallback to random trainer
            }
            
            $start_time = clone $date;
            $start_time->setTime(...explode(':', $class_info['time']));
            
            $end_time = clone $start_time;
            $end_time->add(new DateInterval('PT' . $class_info['duration'] . 'M'));
            
            $stmt = $pdo->prepare("INSERT INTO classes (title, description, location, capacity, start_time, end_time, trainer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $class_info['title'],
                $class_info['description'] . "\n\nDifficulty: " . $class_info['difficulty'] . "\nEquipment: " . $class_info['equipment'],
                $class_info['location'],
                $class_info['capacity'],
                $start_time->format('Y-m-d H:i:s'),
                $end_time->format('Y-m-d H:i:s'),
                $trainer['id']
            ]);
            
            echo "<p>✅ {$day}: {$class_info['title']} at {$class_info['time']} with {$trainer['first_name']} {$trainer['last_name']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error setting up classes: " . $e->getMessage() . "</p>";
}

// Create recurring classes for next 4 weeks
echo "<h3>🔄 Creating 4-Week Schedule:</h3>";
try {
    // Get all classes from this week
    $current_classes = $pdo->query("SELECT * FROM classes ORDER BY start_time")->fetchAll();
    
    for ($week = 1; $week <= 3; $week++) { // Add 3 more weeks
        foreach ($current_classes as $class) {
            $start_time = new DateTime($class['start_time']);
            $end_time = new DateTime($class['end_time']);
            
            $start_time->add(new DateInterval('P' . ($week * 7) . 'D'));
            $end_time->add(new DateInterval('P' . ($week * 7) . 'D'));
            
            $stmt = $pdo->prepare("INSERT INTO classes (title, description, location, capacity, start_time, end_time, trainer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $class['title'],
                $class['description'],
                $class['location'],
                $class['capacity'],
                $start_time->format('Y-m-d H:i:s'),
                $end_time->format('Y-m-d H:i:s'),
                $class['trainer_id']
            ]);
        }
        echo "<p>✅ Week " . ($week + 1) . " schedule created</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating recurring schedule: " . $e->getMessage() . "</p>";
}

// Show final statistics
echo "<div style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; margin: 20px 0; border-radius: 10px; text-align: center;'>";
echo "<h2>🎯 CLASS SYSTEM COMPLETE!</h2>";

try {
    $trainer_count = $pdo->query("SELECT COUNT(*) FROM trainers")->fetchColumn();
    $class_count = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $weekly_classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    
    echo "<p>✅ <strong>Expert Trainers:</strong> $trainer_count professionals</p>";
    echo "<p>✅ <strong>Total Classes:</strong> $class_count scheduled sessions</p>";
    echo "<p>✅ <strong>This Week:</strong> $weekly_classes classes available</p>";
    echo "<p>💪 <strong>L9 Fitness class system is ready to dominate!</strong></p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Some stats may not be available</p>";
}

echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h4>🚀 Next Steps:</h4>";
echo "<ul>";
echo "<li><strong>Browse Classes:</strong> <a href='public/classes.php' target='_blank'>View Class Schedule</a></li>";
echo "<li><strong>Test Booking:</strong> Ensure you have an active membership first</li>";
echo "<li><strong>Class Types:</strong> HIIT, Strength, Cardio, Yoga, CrossFit, Combat, Power Training</li>";
echo "<li><strong>Difficulty Levels:</strong> Beginner, Intermediate, Advanced</li>";
echo "</ul>";
echo "</div>";
?>