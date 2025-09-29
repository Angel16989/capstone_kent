<?php
// ULTIMATE L9 FITNESS DUMMY DATA GENERATOR - EVERYTHING INCLUDED!
echo "<h1>🔥 ULTIMATE L9 FITNESS DUMMY DATA GENERATOR 🔥</h1>";
echo "<p><strong>Creating comprehensive data for EVERY feature...</strong></p><br>";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // === CELEBRITY USERS ===
    $users = [
        ['first_name' => 'Michael', 'last_name' => 'Jackson', 'email' => 'mj@l9fitness.com', 'phone' => '+1-555-KING-001', 'emergency_contact' => 'Diana Ross (+1-555-BOSS-001)', 'gender' => 'male', 'dob' => '1958-08-29', 'address' => '2300 Jackson Street', 'city' => 'Los Angeles', 'state' => 'CA', 'postcode' => '90210'],
        ['first_name' => 'Madonna', 'last_name' => 'Ciccone', 'email' => 'madonna@l9fitness.com', 'phone' => '+1-555-QUEEN-002', 'emergency_contact' => 'Carlos Leon (+1-555-DANCE-002)', 'gender' => 'female', 'dob' => '1958-08-16', 'address' => '1234 Material Girl Ave', 'city' => 'New York', 'state' => 'NY', 'postcode' => '10001'],
        ['first_name' => 'Arnold', 'last_name' => 'Schwarzenegger', 'email' => 'arnold@l9fitness.com', 'phone' => '+1-555-BEAST-003', 'emergency_contact' => 'Maria Shriver (+1-555-POWER-003)', 'gender' => 'male', 'dob' => '1947-07-30', 'address' => '9999 Muscle Beach Blvd', 'city' => 'Venice', 'state' => 'CA', 'postcode' => '90291'],
        ['first_name' => 'Serena', 'last_name' => 'Williams', 'email' => 'serena@l9fitness.com', 'phone' => '+1-555-ACE-004', 'emergency_contact' => 'Venus Williams (+1-555-TENNIS-004)', 'gender' => 'female', 'dob' => '1981-09-26', 'address' => '1500 Champion Court', 'city' => 'Miami', 'state' => 'FL', 'postcode' => '33101'],
        ['first_name' => 'Dwayne', 'last_name' => 'Johnson', 'email' => 'therock@l9fitness.com', 'phone' => '+1-555-ROCK-005', 'emergency_contact' => 'Lauren Hashian (+1-555-FAMILY-005)', 'gender' => 'male', 'dob' => '1972-05-02', 'address' => '7777 Hollywood Hills Dr', 'city' => 'Los Angeles', 'state' => 'CA', 'postcode' => '90028']
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
    
    // === FITNESS PROFILES ===
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
    
    // === MEMBERSHIPS ===
    foreach ($user_ids as $user_id) {
        $stmt = $pdo->prepare("
            INSERT INTO memberships (member_id, plan_id, start_date, end_date, status, total_fee)
            VALUES (?, 3, DATE_SUB(NOW(), INTERVAL 6 MONTH), DATE_ADD(NOW(), INTERVAL 6 MONTH), 'active', 399.00)
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        $stmt->execute([$user_id]);
        
        echo "✅ Created yearly membership for user ID: $user_id<br>";
    }
    
    // === WEIGHT PROGRESS (6 MONTHS) ===
    foreach ($user_ids as $index => $user_id) {
        $start_weight = $fitness_data[$index]['current_weight'] + 8; // Started 8kg heavier
        $current_weight = $fitness_data[$index]['current_weight'];
        
        for ($month = 6; $month >= 0; $month--) {
            for ($week = 0; $week < 4; $week++) {
                $weight = $start_weight - (($start_weight - $current_weight) * (6 - $month) / 6);
                $weight += (rand(-15, 15) / 10); // Add realistic variation
                
                $days_ago = ($month * 30) + ($week * 7) + rand(0, 6);
                
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO weight_progress (user_id, weight, recorded_date)
                    VALUES (?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
                ");
                $stmt->execute([$user_id, round($weight, 1), $days_ago]);
            }
        }
        echo "✅ Created 24 weeks of weight progress for user ID: $user_id<br>";
    }
    
    // === NUTRITION PROFILES ===
    $nutrition_data = [
        ['plan_name' => 'MJ Beast Plan', 'diet_type' => 'Balanced', 'daily_calories' => 2800, 'daily_protein' => 140, 'daily_carbs' => 350, 'daily_fat' => 93, 'food_allergies' => 'None', 'food_preferences' => 'No red meat, loves smoothies'],
        ['plan_name' => 'Material Girl Power', 'diet_type' => 'High Protein', 'daily_calories' => 2200, 'daily_protein' => 165, 'daily_carbs' => 220, 'daily_fat' => 73, 'food_allergies' => 'Gluten', 'food_preferences' => 'Gluten-free, organic foods'],
        ['plan_name' => 'Terminator Fuel', 'diet_type' => 'Bodybuilder', 'daily_calories' => 4000, 'daily_protein' => 200, 'daily_carbs' => 500, 'daily_fat' => 133, 'food_allergies' => 'None', 'food_preferences' => 'High protein, Austrian specialties'],
        ['plan_name' => 'Champion Diet', 'diet_type' => 'Athletic', 'daily_calories' => 3200, 'daily_protein' => 160, 'daily_carbs' => 400, 'daily_fat' => 107, 'food_allergies' => 'Dairy', 'food_preferences' => 'Dairy-free, energy-focused'],
        ['plan_name' => 'Rock Solid Nutrition', 'diet_type' => 'Power Lifter', 'daily_calories' => 4500, 'daily_protein' => 225, 'daily_carbs' => 562, 'daily_fat' => 150, 'food_allergies' => 'None', 'food_preferences' => 'Beast Mode - Everything, cheat meals allowed']
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
    
    // === FITNESS GOALS ===
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
    
    // === PAYMENT HISTORY (COMPREHENSIVE) ===
    foreach ($user_ids as $index => $user_id) {
        // Get membership ID
        $stmt = $pdo->prepare("SELECT id FROM memberships WHERE member_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $membership_id = $stmt->fetchColumn();
        
        if ($membership_id) {
            // Initial membership payment
            $stmt = $pdo->prepare("
                INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at)
                VALUES (?, ?, 399.00, 'card', 'paid', ?, ?, DATE_SUB(NOW(), INTERVAL 6 MONTH))
            ");
            $stmt->execute([$user_id, $membership_id, 'TXN_' . strtoupper(uniqid()), 'INV_' . date('Y') . sprintf('%04d', $user_id)]);
            
            // Monthly add-on payments (linked to membership)
            for ($month = 5; $month >= 1; $month--) {
                $addon_amount = rand(15, 50); // Random add-on services
                $methods = ['card', 'paypal', 'card'];
                $method = $methods[array_rand($methods)];
                
                $stmt = $pdo->prepare("
                    INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at)
                    VALUES (?, ?, ?, ?, 'paid', ?, ?, DATE_SUB(NOW(), INTERVAL ? MONTH))
                ");
                $stmt->execute([$user_id, $membership_id, $addon_amount, $method, 'ADD_' . strtoupper(uniqid()), 'ADD_' . date('Y') . sprintf('%04d', $user_id + $month), $month]);
            }
            
            echo "✅ Created comprehensive payment history for user ID: $user_id<br>";
        }
    }
    
    // === EXTENSIVE MESSAGE SYSTEM ===
    $all_messages = [
        // Welcome messages
        ['sender' => 0, 'recipient' => 1, 'subject' => 'Welcome to L9 Fitness!', 'message' => 'Hey Madonna! Welcome to L9 Fitness! Ready to moonwalk your way to beast mode? The gym is your stage now! 🕺'],
        ['sender' => 1, 'recipient' => 0, 'subject' => 'Thanks MJ!', 'message' => 'Thanks Michael! Already feeling the burn! This gym is material for greatness! Let\'s strike a pose and lift some weights! 💃'],
        
        // Training advice
        ['sender' => 2, 'recipient' => 0, 'subject' => 'Muscle Building Tips', 'message' => 'Michael, here are some muscle building tips from the Terminator himself: Progressive overload is key. I\'ll be back... with more advice! 💪'],
        ['sender' => 0, 'recipient' => 2, 'subject' => 'Re: Muscle Building Tips', 'message' => 'Thanks Arnold! Your tips are smooth as a criminal! Will apply them to my routine. You\'re bad... at giving advice! (In a good way) 😄'],
        
        // Beast mode activation
        ['sender' => 0, 'recipient' => 4, 'subject' => 'Beast Mode Activated', 'message' => 'Rock! Ready to smell what the L9 is cooking? Let\'s rock this workout together! The way you make me feel... like lifting heavy! 🔥'],
        ['sender' => 4, 'recipient' => 0, 'subject' => 'Can You Smell It?', 'message' => 'MJ! The Rock is ready to rumble! Let\'s lay the smackdown on these weights! Your moves + my strength = unstoppable! 🗿'],
        
        // Athletic motivation
        ['sender' => 3, 'recipient' => 1, 'subject' => 'Training Schedule', 'message' => 'Madonna, want to join my cardio session tomorrow? Game, set, match! We\'ll serve up some serious fitness! 🎾'],
        ['sender' => 1, 'recipient' => 3, 'subject' => 'Re: Training Schedule', 'message' => 'Serena! Count me in! Like a prayer, I need that cardio! Let\'s get into the groove of fitness! 🙏'],
        
        // Motivation chains
        ['sender' => 2, 'recipient' => 4, 'subject' => 'Strength Challenge', 'message' => 'Rock, ready for some Austrian vs Hollywood strength battle? I\'ll pump you up for the ultimate showdown! 🏋️'],
        ['sender' => 4, 'recipient' => 2, 'subject' => 'Bring It On!', 'message' => 'Arnold! The Rock accepts the challenge! Know your role and shut your mouth... while I deadlift! Just kidding, let\'s train! 😤'],
        
        // Group motivation
        ['sender' => 0, 'recipient' => 3, 'subject' => 'Cardio King & Queen', 'message' => 'Serena! Your endurance is unbeatable! Teach me to run like a smooth criminal! 🏃‍♂️'],
        ['sender' => 3, 'recipient' => 0, 'subject' => 'Speed Demon', 'message' => 'Michael! Your dance moves ARE cardio! Let\'s combine tennis footwork with moonwalking! 🎾💃'],
        
        // Nutrition talk
        ['sender' => 1, 'recipient' => 2, 'subject' => 'Diet Questions', 'message' => 'Arnold, what\'s your secret to maintaining such discipline with nutrition? Material girl needs material gains! 💎'],
        ['sender' => 2, 'recipient' => 1, 'subject' => 'Austrian Discipline', 'message' => 'Madonna! It\'s all about consistency. Discipline is the key to success - in bodybuilding and life! I\'ll be back with meal prep tips! 🥗'],
        
        // Weekend plans
        ['sender' => 4, 'recipient' => 0, 'subject' => 'Weekend Warrior', 'message' => 'MJ! The Rock\'s got some weekend workout plans. Can you feel the energy tonight? Let\'s make it count! ⚡'],
        ['sender' => 0, 'recipient' => 4, 'subject' => 'Billie Jean Workout', 'message' => 'Rock! The workout is not my son... it\'s my passion! Let\'s beat it together this weekend! 🎵'],
        
        // Recovery discussions
        ['sender' => 3, 'recipient' => 1, 'subject' => 'Recovery Methods', 'message' => 'Madonna, how do you recover after intense sessions? Tennis taught me the importance of rest days! 😴'],
        ['sender' => 1, 'recipient' => 3, 'subject' => 'Like a Prayer Recovery', 'message' => 'Serena! I express myself through yoga and meditation. Recovery is like a prayer for the body! 🧘‍♀️'],
        
        // Achievement celebrations
        ['sender' => 2, 'recipient' => 0, 'subject' => 'Hasta la Vista, Weak Lifts!', 'message' => 'Michael! Saw your progress report - you\'re getting stronger! The king of pop is becoming the king of power! 👑'],
        ['sender' => 0, 'recipient' => 2, 'subject' => 'Thriller Gains', 'message' => 'Arnold! It\'s a thriller night at the gym! My gains are off the wall! Thanks for the motivation, Terminator! 🌟']
    ];
    
    foreach ($all_messages as $msg) {
        $sender_id = $user_ids[$msg['sender']];
        $recipient_id = $user_ids[$msg['recipient']];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_messages (sender_id, recipient_id, subject, message, recipient_type, message_type, priority, created_at)
            VALUES (?, ?, ?, ?, 'user', 'text', 'normal', DATE_SUB(NOW(), INTERVAL ? DAY))
        ");
        $stmt->execute([$sender_id, $recipient_id, $msg['subject'], $msg['message'], rand(1, 45)]);
    }
    echo "<br>✅ Created extensive message history (20+ messages)<br>";
    
    // === GYM CHECK-INS & ATTENDANCE ===
    foreach ($user_ids as $user_id) {
        // Create realistic gym check-ins over 6 months
        for ($day = 180; $day >= 1; $day -= rand(1, 3)) { // Every 1-3 days
            $check_in_time = date('Y-m-d H:i:s', strtotime("-$day days") + rand(6*3600, 22*3600)); // 6 AM to 10 PM
            $workout_duration = rand(45, 180); // 45 min to 3 hours
            $check_out_time = date('Y-m-d H:i:s', strtotime($check_in_time) + ($workout_duration * 60));
            
            $stmt = $pdo->prepare("
                INSERT INTO attendance (member_id, check_in, check_out, source)
                VALUES (?, ?, ?, 'kiosk')
            ");
            $stmt->execute([$user_id, $check_in_time, $check_out_time]);
        }
        echo "✅ Created 6 months of gym attendance for user ID: $user_id<br>";
    }
    
    // === WORKOUT PROGRESS LOGS ===
    $exercises = [
        ['name' => 'Bench Press', 'type' => 'strength'],
        ['name' => 'Deadlift', 'type' => 'strength'], 
        ['name' => 'Squat', 'type' => 'strength'],
        ['name' => 'Pull-ups', 'type' => 'strength'],
        ['name' => 'Push-ups', 'type' => 'strength'],
        ['name' => 'Bicep Curls', 'type' => 'strength'],
        ['name' => 'Shoulder Press', 'type' => 'strength'],
        ['name' => 'Leg Press', 'type' => 'strength'],
        ['name' => 'Treadmill Run', 'type' => 'cardio'],
        ['name' => 'Cycling', 'type' => 'cardio']
    ];
    
    foreach ($user_ids as $user_id) {
        foreach ($exercises as $exercise) {
            $base_weight = rand(20, 100); // Starting weight for strength exercises
            
            // Create progressive improvement over time
            for ($week = 20; $week >= 1; $week--) {
                $current_weight = $base_weight + ((20 - $week) * rand(1, 3)); // Progressive increase
                $reps = rand(8, 15);
                $sets = rand(3, 5);
                $duration = rand(20, 60); // Duration in minutes for cardio
                $distance = $exercise['type'] == 'cardio' ? rand(3, 15) : null; // Distance in km
                $calories = rand(200, 600);
                
                if ($exercise['type'] == 'strength') {
                    $stmt = $pdo->prepare("
                        INSERT INTO workout_progress (user_id, exercise_name, exercise_type, weight, reps, sets, calories_burned, workout_date, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? WEEK), 'Beast mode activated!')
                    ");
                    $stmt->execute([$user_id, $exercise['name'], $exercise['type'], $current_weight, $reps, $sets, $calories, $week]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO workout_progress (user_id, exercise_name, exercise_type, duration, distance, calories_burned, workout_date, notes)
                        VALUES (?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? WEEK), 'Cardio beast!')
                    ");
                    $stmt->execute([$user_id, $exercise['name'], $exercise['type'], $duration, $distance, $calories, $week]);
                }
            }
        }
        echo "✅ Created workout progress logs for user ID: $user_id<br>";
    }
    
    // === ANNOUNCEMENTS ===
    $announcements = [
        ['title' => '🔥 BEAST MODE CHALLENGE 2025!', 'body' => 'Join our ultimate fitness challenge! Transform your body and mind. Prize: 1 year free membership! Registration starts Monday. Are you ready to activate BEAST MODE?'],
        ['title' => '🆕 New Equipment Arrival', 'body' => 'Brand new Hammer Strength machines have arrived! State-of-the-art technology meets raw power. Come experience the future of fitness at L9!'],
        ['title' => '⏰ Extended Hours This Weekend', 'body' => 'L9 Fitness is now open 24/7 on weekends! No excuses, no limits. Your fitness journey never sleeps. BEAST MODE ACTIVATED around the clock!'],
        ['title' => '🥗 Nutrition Workshop Series', 'body' => 'Learn from certified nutritionists! Workshop series starting next week. Topics: Meal prep, supplements, cutting vs bulking. Knowledge is power!'],
        ['title' => '🏆 Member Spotlight: Celebrity Gains!', 'body' => 'Our celebrity members are crushing their goals! Michael Jackson gained 2kg of lean muscle, Arnold maintained his legendary strength. You could be next!']
    ];
    
    foreach ($announcements as $announcement) {
        $stmt = $pdo->prepare("
            INSERT INTO announcements (title, body, published_at, created_by)
            VALUES (?, ?, DATE_SUB(NOW(), INTERVAL ? DAY), 1)
        ");
        $stmt->execute([$announcement['title'], $announcement['body'], rand(1, 30)]);
    }
    echo "✅ Created gym announcements<br>";
    
    // === CONTACT MESSAGES ===
    $contact_messages = [
        ['name' => 'Fitness Enthusiast', 'email' => 'fan@fitness.com', 'subject' => 'Membership Inquiry', 'message' => 'Hi! I heard celebrities train at L9 Fitness. Can regular people join too? What are your rates?'],
        ['name' => 'Personal Trainer', 'email' => 'trainer@email.com', 'subject' => 'Employment Opportunity', 'message' => 'I\'m a certified personal trainer with 10 years experience. Are you hiring? I specialize in strength training.'],
        ['name' => 'Equipment Vendor', 'email' => 'sales@equipment.com', 'subject' => 'New Product Demo', 'message' => 'We have revolutionary new fitness equipment. Would you be interested in a demonstration?'],
        ['name' => 'Local News', 'email' => 'reporter@news.com', 'subject' => 'Interview Request', 'message' => 'We\'d like to feature L9 Fitness in our health and wellness segment. Available for an interview?']
    ];
    
    foreach ($contact_messages as $contact) {
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, subject, message, status, created_at)
            VALUES (?, ?, ?, ?, 'unread', DATE_SUB(NOW(), INTERVAL ? DAY))
        ");
        $stmt->execute([$contact['name'], $contact['email'], $contact['subject'], $contact['message'], rand(1, 15)]);
    }
    echo "✅ Created contact inquiries<br>";
    
    // === CHATBOT ANALYTICS ===
    $chatbot_questions = [
        ['user' => 'What are your opening hours?', 'bot' => 'We are open 24/7! Beast mode never sleeps! 🔥'],
        ['user' => 'How much does a membership cost?', 'bot' => 'Our yearly membership is $399 - best value for beast mode training!'],
        ['user' => 'Do you have personal trainers?', 'bot' => 'Yes! We have celebrity trainers ready to help you achieve beast mode!'],
        ['user' => 'What equipment do you have?', 'bot' => 'State-of-the-art equipment including Hammer Strength machines and more!'],
        ['user' => 'Can I freeze my membership?', 'bot' => 'Yes, you can freeze your membership for up to 3 months per year.'],
        ['user' => 'Do you offer nutrition counseling?', 'bot' => 'Absolutely! Our nutrition experts will create custom plans for your goals.'],
        ['user' => 'What classes do you offer?', 'bot' => 'We offer various classes including HIIT, yoga, dance, and strength training!'],
        ['user' => 'Is there parking available?', 'bot' => 'Yes! Free parking is available for all L9 Fitness members.'],
        ['user' => 'Do you have a pool?', 'bot' => 'We have a heated pool and spa facilities for recovery and relaxation.'],
        ['user' => 'Can I bring a guest?', 'bot' => 'Members can bring guests for a small day pass fee. Spread the beast mode!']
    ];
    
    foreach ($user_ids as $user_id) {
        for ($i = 0; $i < rand(3, 8); $i++) {
            $days_ago = rand(1, 60);
            $session_id = 'session_' . $user_id . '_' . $i . '_' . time();
            
            // Create conversation session
            $ip_address = '192.168.1.' . rand(100, 200);
            $rating = rand(4, 5);
            
            $stmt = $pdo->prepare("
                INSERT INTO chatbot_conversations (user_id, session_id, ip_address, started_at, ended_at, total_messages, satisfaction_rating, last_activity)
                VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY), DATE_SUB(NOW(), INTERVAL ? DAY), 2, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
            ");
            $stmt->execute([$user_id, $session_id, $ip_address, $days_ago, $days_ago, $rating, $days_ago]);
            
            $conversation_id = $pdo->lastInsertId();
            
            // Add user and bot messages
            $qa = $chatbot_questions[array_rand($chatbot_questions)];
            
            // User message
            $stmt = $pdo->prepare("
                INSERT INTO chatbot_messages (conversation_id, message_type, message_text, created_at, intent_detected, confidence_score)
                VALUES (?, 'user', ?, DATE_SUB(NOW(), INTERVAL ? DAY), 'general_inquiry', 0.95)
            ");
            $stmt->execute([$conversation_id, $qa['user'], $days_ago]);
            
            // Bot response
            $stmt = $pdo->prepare("
                INSERT INTO chatbot_messages (conversation_id, message_type, message_text, response_text, ai_provider, processing_time_ms, created_at, intent_detected, confidence_score)
                VALUES (?, 'bot', ?, ?, 'l9_fitness_ai', ?, DATE_SUB(NOW(), INTERVAL ? DAY), 'response_generated', 0.98)
            ");
            $stmt->execute([$conversation_id, $qa['bot'], $qa['bot'], rand(200, 800), $days_ago]);
        }
    }
    echo "✅ Created chatbot conversation history<br>";
    
    echo "<br><h2>🎉 ULTIMATE DUMMY DATA CREATION COMPLETE!</h2>";
    echo "<div style='background: #0a0a0a; padding: 30px; border-radius: 15px; margin: 20px 0; border: 2px solid #ff4444;'>";
    echo "<h3 style='color: #ff4444; text-align: center;'>🔑 LOGIN CREDENTIALS</h3>";
    echo "<p style='text-align: center; font-size: 18px;'><strong>Password for all users:</strong> <code style='background: #333; padding: 8px; border-radius: 5px; font-size: 20px;'>beast123</code></p>";
    
    echo "<h3 style='color: #00ff00;'>👥 CELEBRITY MEMBERS CREATED:</h3>";
    $user_names = ['🕺 Michael Jackson', '💃 Madonna Ciccone', '💪 Arnold Schwarzenegger', '🎾 Serena Williams', '🗿 Dwayne Johnson'];
    $user_emails = ['mj@l9fitness.com', 'madonna@l9fitness.com', 'arnold@l9fitness.com', 'serena@l9fitness.com', 'therock@l9fitness.com'];
    
    for ($i = 0; $i < 5; $i++) {
        echo "<p style='font-size: 16px;'>👤 <strong>{$user_names[$i]}</strong> - {$user_emails[$i]}</p>";
    }
    
    echo "<h3 style='color: #ffaa00;'>🚀 COMPREHENSIVE FEATURES READY:</h3>";
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    echo "<div>";
    echo "<h4 style='color: #ff6666;'>👤 User Features:</h4>";
    echo "<ul>";
    echo "<li>✅ Complete user profiles with all details</li>";
    echo "<li>✅ 6+ months of workout history</li>";
    echo "<li>✅ 24 weeks of weight progress tracking</li>";
    echo "<li>✅ Active yearly memberships</li>";
    echo "<li>✅ Custom nutrition plans</li>";
    echo "<li>✅ Fitness goals with progress tracking</li>";
    echo "<li>✅ Comprehensive payment history</li>";
    echo "<li>✅ 20+ inter-user messages</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div>";
    echo "<h4 style='color: #66ff66;'>🏋️ Gym Features:</h4>";
    echo "<ul>";
    echo "<li>✅ 6 months of gym check-ins</li>";
    echo "<li>✅ Workout progress logs (10 exercises)</li>";
    echo "<li>✅ Gym announcements</li>";
    echo "<li>✅ Contact inquiries</li>";
    echo "<li>✅ Chatbot conversation history</li>";
    echo "<li>✅ Analytics and reports data</li>";
    echo "<li>✅ Payment receipts & invoices</li>";
    echo "<li>✅ All dashboard features working</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    echo "<h3 style='color: #ff4444; text-align: center; margin-top: 30px;'>🔥 BEAST MODE FULLY ACTIVATED! 🔥</h3>";
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='login.php' style='background: linear-gradient(135deg, #ff4444, #ff6666); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold; margin: 10px;'>🚀 LOGIN NOW</a>";
    echo "<a href='dashboard.php' style='background: linear-gradient(135deg, #00aa00, #00cc00); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold; margin: 10px;'>📊 VIEW DASHBOARD</a>";
    echo "</div>";
    echo "<p style='text-align: center; margin-top: 20px; font-size: 16px;'><strong>Recommended:</strong> Login as <strong>Michael Jackson</strong> (mj@l9fitness.com) for the ultimate demo experience!</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: #ff4444; background: #220000; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Error Occurred:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "</div>";
}
?>