-- L9 Fitness Gym - Comprehensive Dummy Data
USE l9_gym;

-- Insert more sample users (members, trainers, staff)
INSERT INTO users (role_id, first_name, last_name, email, password_hash, gender, dob, phone, address, emergency_contact, created_at) VALUES
-- More members
(4, 'John', 'Smith', 'john.smith.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1990-05-15', '+1234567890', '123 Main St, City', 'Jane Smith +1234567891', NOW()),
(4, 'Emma', 'Davis', 'emma.davis.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1988-12-03', '+1234567892', '456 Oak Ave, City', 'Mike Davis +1234567893', NOW()),
(4, 'Alex', 'Brown', 'alex.brown.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1995-08-20', '+1234567894', '789 Pine St, City', 'Sarah Brown +1234567895', NOW()),
(4, 'Lisa', 'Garcia', 'lisa.garcia.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1992-11-12', '+1234567896', '321 Elm St, City', 'Carlos Garcia +1234567897', NOW()),
(4, 'David', 'Miller', 'david.miller.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1987-03-28', '+1234567898', '654 Maple Ave, City', 'Anna Miller +1234567899', NOW()),
(4, 'Sarah', 'Johnson', 'sarah.johnson.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1993-07-14', '+1234567800', '987 Cedar St, City', 'Tom Johnson +1234567801', NOW()),
(4, 'Michael', 'Wilson', 'michael.wilson.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1989-09-05', '+1234567802', '147 Birch Ave, City', 'Laura Wilson +1234567803', NOW()),
(4, 'Jessica', 'Martinez', 'jessica.martinez.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1991-01-22', '+1234567804', '258 Spruce St, City', 'Roberto Martinez +1234567805', NOW()),
(4, 'Ryan', 'Anderson', 'ryan.anderson.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1994-06-18', '+1234567806', '369 Willow Ave, City', 'Maria Anderson +1234567807', NOW()),
(4, 'Amanda', 'Taylor', 'amanda.taylor.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1986-04-30', '+1234567808', '741 Poplar St, City', 'James Taylor +1234567809', NOW()),

-- More trainers
(3, 'Jake', 'Beast', 'jake.beast.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1985-02-10', '+1234567810', '852 Trainer Blvd, City', 'Emergency Contact +1234567811', NOW()),
(3, 'Emma', 'Zen', 'emma.zen.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1988-11-25', '+1234567812', '963 Yoga Studio, City', 'Emergency Contact +1234567813', NOW()),
(3, 'Alex', 'Viper', 'alex.viper.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1983-07-08', '+1234567814', '159 Combat Center, City', 'Emergency Contact +1234567815', NOW()),
(3, 'Mike', 'Thunder', 'mike.thunder.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1980-12-15', '+1234567816', '753 Power House, City', 'Emergency Contact +1234567817', NOW()),

-- More staff
(2, 'Rachel', 'Green', 'rachel.green.new@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1990-08-22', '+1234567818', '456 Staff Quarters, City', 'Emergency Contact +1234567819', NOW());

-- Insert trainer details for new trainers
INSERT INTO trainers (user_id, bio, certifications, rate_per_session) VALUES
(151, 'Elite CrossFit trainer with 10+ years experience. Specializes in functional fitness and high-intensity training.', 'CrossFit Level 3, USAW Weightlifting, CPR/AED', 85.00),
(152, 'Certified yoga instructor and mindfulness coach. Helps members find balance through movement and meditation.', 'RYT-500, Mindfulness Coaching, Nutrition Specialist', 70.00),
(153, 'Combat sports expert with background in MMA and boxing. Teaches self-defense and conditioning.', 'Boxing Coach, MMA Conditioning, Self-Defense Instructor', 75.00),
(154, 'Powerlifting specialist and strength coach. Builds champions through progressive overload training.', 'CSCS, USA Powerlifting, Olympic Weightlifting', 80.00);

-- Insert more comprehensive classes with proper scheduling
INSERT INTO classes (title, description, location, capacity, start_time, end_time, trainer_id, instructor_name, schedule_day, difficulty, status) VALUES
-- Today's classes (current date)
('Beast Mode HIIT', 'High-intensity interval training designed to push your limits. Explosive movements, cardio bursts, and strength challenges.', 'Main Studio', 15, CONCAT(CURDATE(), ' 06:00:00'), CONCAT(CURDATE(), ' 07:00:00'), 11, 'Jake Beast', 'Monday', 'Advanced', 'active'),
('Warrior Strength', 'Build serious muscle and strength with compound movements. Deadlifts, squats, presses, and more.', 'Strength Zone', 12, CONCAT(CURDATE(), ' 07:30:00'), CONCAT(CURDATE(), ' 08:45:00'), 4, 'Mike Thompson', 'Monday', 'Intermediate', 'active'),
('Cardio Storm', 'Heart-pumping cardio session mixing treadmills, bikes, and functional movements.', 'Cardio Area', 20, CONCAT(CURDATE(), ' 09:00:00'), CONCAT(CURDATE(), ' 09:45:00'), 11, 'Jake Beast', 'Monday', 'Beginner', 'active'),
('Zen Flow Yoga', 'Restore balance with mindful yoga flows. Perfect recovery session for hardcore training.', 'Yoga Studio', 18, CONCAT(CURDATE(), ' 10:30:00'), CONCAT(CURDATE(), ' 11:30:00'), 12, 'Emma Zen', 'Monday', 'Beginner', 'active'),
('CrossFit Chaos', 'Functional fitness at its finest. WODs that will test your limits and build mental toughness.', 'CrossFit Area', 16, CONCAT(CURDATE(), ' 17:00:00'), CONCAT(CURDATE(), ' 18:00:00'), 11, 'Jake Beast', 'Monday', 'Advanced', 'active'),
('Combat Training', 'Learn striking techniques, footwork, and conditioning. Boxing, kickboxing, and martial arts fundamentals.', 'Combat Zone', 14, CONCAT(CURDATE(), ' 18:30:00'), CONCAT(CURDATE(), ' 19:45:00'), 13, 'Alex Viper', 'Monday', 'Intermediate', 'active'),
('Power Hour', 'Maximum intensity strength training. Heavy lifting, explosive movements, and serious gains.', 'Power Zone', 10, CONCAT(CURDATE(), ' 19:30:00'), CONCAT(CURDATE(), ' 20:30:00'), 14, 'Mike Thunder', 'Monday', 'Advanced', 'active'),

-- Tomorrow's classes
('Morning Flow Yoga', 'Start your day with gentle yoga and meditation. Perfect for all levels.', 'Yoga Studio', 20, DATE_ADD(CONCAT(CURDATE(), ' 07:00:00'), INTERVAL 1 DAY), DATE_ADD(CONCAT(CURDATE(), ' 08:00:00'), INTERVAL 1 DAY), 12, 'Emma Zen', 'Tuesday', 'Beginner', 'active'),
('Strength Foundations', 'Master the basics of strength training. Perfect for beginners building their foundation.', 'Strength Zone', 15, DATE_ADD(CONCAT(CURDATE(), ' 09:00:00'), INTERVAL 1 DAY), DATE_ADD(CONCAT(CURDATE(), ' 10:00:00'), INTERVAL 1 DAY), 4, 'Mike Thompson', 'Tuesday', 'Beginner', 'active'),
('HIIT Blast', '45 minutes of pure intensity. Burn fat and build endurance with this high-energy workout.', 'Main Studio', 18, DATE_ADD(CONCAT(CURDATE(), ' 11:00:00'), INTERVAL 1 DAY), DATE_ADD(CONCAT(CURDATE(), ' 11:45:00'), INTERVAL 1 DAY), 11, 'Jake Beast', 'Tuesday', 'Intermediate', 'active'),
('Boxing Fundamentals', 'Learn proper boxing technique, footwork, and combinations. Great for fitness and self-defense.', 'Combat Zone', 12, DATE_ADD(CONCAT(CURDATE(), ' 17:30:00'), INTERVAL 1 DAY), DATE_ADD(CONCAT(CURDATE(), ' 18:30:00'), INTERVAL 1 DAY), 13, 'Alex Viper', 'Tuesday', 'Intermediate', 'active'),
('Power Building', 'Progressive overload training for serious strength gains. Compound movements and heavy weights.', 'Power Zone', 8, DATE_ADD(CONCAT(CURDATE(), ' 19:00:00'), INTERVAL 1 DAY), DATE_ADD(CONCAT(CURDATE(), ' 20:15:00'), INTERVAL 1 DAY), 14, 'Mike Thunder', 'Tuesday', 'Advanced', 'active'),

-- More classes for the week
('Wednesday Warrior', 'Full-body functional training combining strength, cardio, and mobility work.', 'Main Studio', 16, DATE_ADD(CONCAT(CURDATE(), ' 06:30:00'), INTERVAL 2 DAY), DATE_ADD(CONCAT(CURDATE(), ' 07:30:00'), INTERVAL 2 DAY), 11, 'Jake Beast', 'Wednesday', 'Intermediate', 'active'),
('Yoga Power Flow', 'Dynamic yoga sequence combining strength and flexibility. Challenge your body and mind.', 'Yoga Studio', 14, DATE_ADD(CONCAT(CURDATE(), ' 08:00:00'), INTERVAL 2 DAY), DATE_ADD(CONCAT(CURDATE(), ' 09:15:00'), INTERVAL 2 DAY), 12, 'Emma Zen', 'Wednesday', 'Intermediate', 'active'),
('Olympic Lifting', 'Master the snatch and clean & jerk. Technical lifting for serious athletes.', 'Strength Zone', 6, DATE_ADD(CONCAT(CURDATE(), ' 10:00:00'), INTERVAL 2 DAY), DATE_ADD(CONCAT(CURDATE(), ' 11:00:00'), INTERVAL 2 DAY), 14, 'Mike Thunder', 'Wednesday', 'Advanced', 'active'),
('Cardio Kickboxing', 'High-energy cardio workout combining kickboxing moves with dance elements.', 'Combat Zone', 20, DATE_ADD(CONCAT(CURDATE(), ' 18:00:00'), INTERVAL 2 DAY), DATE_ADD(CONCAT(CURDATE(), ' 19:00:00'), INTERVAL 2 DAY), 13, 'Alex Viper', 'Wednesday', 'Beginner', 'active'),

-- Weekend classes
('Saturday Strength', 'Weekend strength session focusing on progressive overload and technique.', 'Strength Zone', 18, DATE_ADD(CONCAT(CURDATE(), ' 09:00:00'), INTERVAL 5 DAY), DATE_ADD(CONCAT(CURDATE(), ' 10:30:00'), INTERVAL 5 DAY), 4, 'Mike Thompson', 'Saturday', 'Intermediate', 'active'),
('Weekend Warrior HIIT', 'Start your weekend with an energy boost. High-intensity full-body workout.', 'Main Studio', 22, DATE_ADD(CONCAT(CURDATE(), ' 10:00:00'), INTERVAL 5 DAY), DATE_ADD(CONCAT(CURDATE(), ' 11:00:00'), INTERVAL 5 DAY), 11, 'Jake Beast', 'Saturday', 'Intermediate', 'active'),
('Restorative Yoga', 'Deep relaxation and recovery yoga. Perfect for unwinding and recharging.', 'Yoga Studio', 16, DATE_ADD(CONCAT(CURDATE(), ' 11:30:00'), INTERVAL 5 DAY), DATE_ADD(CONCAT(CURDATE(), ' 12:45:00'), INTERVAL 5 DAY), 12, 'Emma Zen', 'Saturday', 'Beginner', 'active'),
('Sunday Funday Fitness', 'Fun, high-energy group fitness class. Mix of cardio, strength, and games.', 'Main Studio', 25, DATE_ADD(CONCAT(CURDATE(), ' 14:00:00'), INTERVAL 6 DAY), DATE_ADD(CONCAT(CURDATE(), ' 15:30:00'), INTERVAL 6 DAY), 11, 'Jake Beast', 'Sunday', 'Beginner', 'active');

-- Insert comprehensive bookings data
INSERT INTO bookings (member_id, class_id, status, booked_at) VALUES
-- Today's class bookings
(2, 207, 'confirmed', NOW()),
(3, 207, 'confirmed', NOW()),
(5, 207, 'confirmed', NOW()),
(6, 208, 'confirmed', NOW()),
(7, 208, 'confirmed', NOW()),
(8, 209, 'confirmed', NOW()),
(9, 209, 'confirmed', NOW()),
(10, 209, 'confirmed', NOW()),
(11, 210, 'confirmed', NOW()),
(12, 210, 'confirmed', NOW()),
(2, 211, 'confirmed', NOW()),
(3, 212, 'confirmed', NOW()),
(4, 212, 'confirmed', NOW()),
(5, 213, 'confirmed', NOW()),
(6, 213, 'confirmed', NOW()),

-- Tomorrow's bookings
(7, 214, 'confirmed', NOW()),
(8, 214, 'confirmed', NOW()),
(9, 215, 'confirmed', NOW()),
(10, 215, 'confirmed', NOW()),
(11, 216, 'confirmed', NOW()),
(12, 216, 'confirmed', NOW()),
(2, 217, 'confirmed', NOW()),
(3, 218, 'confirmed', NOW()),

-- More bookings for variety
(4, 219, 'confirmed', NOW()),
(5, 220, 'confirmed', NOW()),
(6, 221, 'confirmed', NOW()),
(7, 222, 'confirmed', NOW()),
(8, 223, 'confirmed', NOW()),
(9, 224, 'confirmed', NOW()),
(10, 225, 'confirmed', NOW()),
(11, 226, 'confirmed', NOW()),
(12, 227, 'confirmed', NOW()),
(2, 228, 'confirmed', NOW()),
(3, 229, 'confirmed', NOW()),
(4, 230, 'confirmed', NOW()),
(5, 231, 'confirmed', NOW()),
(6, 232, 'confirmed', NOW());

-- Insert comprehensive payment data for revenue
INSERT INTO payments (member_id, membership_id, amount, method, status, invoice_no, paid_at, description) VALUES
-- Recent payments (last 30 days)
(2, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-001'), DATE_SUB(NOW(), INTERVAL 1 DAY), 'Monthly Basic Membership'),
(3, 2, 399.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-002'), DATE_SUB(NOW(), INTERVAL 2 DAY), 'Annual Premium Membership'),
(5, 1, 49.99, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-003'), DATE_SUB(NOW(), INTERVAL 3 DAY), 'Monthly Basic Membership'),
(6, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-004'), DATE_SUB(NOW(), INTERVAL 4 DAY), 'Monthly Premium Membership'),
(7, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-005'), DATE_SUB(NOW(), INTERVAL 5 DAY), 'Monthly Basic Membership'),
(8, 2, 399.99, 'bank_transfer', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-006'), DATE_SUB(NOW(), INTERVAL 6 DAY), 'Annual Premium Membership'),
(9, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-007'), DATE_SUB(NOW(), INTERVAL 7 DAY), 'Monthly Premium Membership'),
(10, 1, 49.99, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-008'), DATE_SUB(NOW(), INTERVAL 8 DAY), 'Monthly Basic Membership'),
(11, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-009'), DATE_SUB(NOW(), INTERVAL 9 DAY), 'Monthly Basic Membership'),
(12, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-010'), DATE_SUB(NOW(), INTERVAL 10 DAY), 'Monthly Premium Membership'),

-- More payments for revenue tracking
(2, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 30 DAY), '%Y%m%d'), '-011'), DATE_SUB(NOW(), INTERVAL 30 DAY), 'Monthly Basic Membership'),
(3, 2, 399.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 31 DAY), '%Y%m%d'), '-012'), DATE_SUB(NOW(), INTERVAL 31 DAY), 'Annual Premium Membership'),
(5, 1, 49.99, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 32 DAY), '%Y%m%d'), '-013'), DATE_SUB(NOW(), INTERVAL 32 DAY), 'Monthly Basic Membership'),
(6, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 33 DAY), '%Y%m%d'), '-014'), DATE_SUB(NOW(), INTERVAL 33 DAY), 'Monthly Premium Membership'),
(7, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 34 DAY), '%Y%m%d'), '-015'), DATE_SUB(NOW(), INTERVAL 34 DAY), 'Monthly Basic Membership'),
(8, 2, 399.99, 'bank_transfer', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 35 DAY), '%Y%m%d'), '-016'), DATE_SUB(NOW(), INTERVAL 35 DAY), 'Annual Premium Membership'),
(9, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 36 DAY), '%Y%m%d'), '-017'), DATE_SUB(NOW(), INTERVAL 36 DAY), 'Monthly Premium Membership'),
(10, 1, 49.99, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 37 DAY), '%Y%m%d'), '-018'), DATE_SUB(NOW(), INTERVAL 37 DAY), 'Monthly Basic Membership'),
(11, 1, 49.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 38 DAY), '%Y%m%d'), '-019'), DATE_SUB(NOW(), INTERVAL 38 DAY), 'Monthly Basic Membership'),
(12, 3, 79.99, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 39 DAY), '%Y%m%d'), '-020'), DATE_SUB(NOW(), INTERVAL 39 DAY), 'Monthly Premium Membership'),

-- Personal training session payments
(2, NULL, 150.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-021'), DATE_SUB(NOW(), INTERVAL 2 DAY), 'Personal Training Session - 2 hours'),
(3, NULL, 200.00, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-022'), DATE_SUB(NOW(), INTERVAL 3 DAY), 'Personal Training Package - 4 sessions'),
(5, NULL, 75.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-023'), DATE_SUB(NOW(), INTERVAL 4 DAY), 'Personal Training Session - 1 hour'),
(6, NULL, 300.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-024'), DATE_SUB(NOW(), INTERVAL 5 DAY), 'Personal Training Package - 8 sessions'),
(7, NULL, 125.00, 'bank_transfer', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-025'), DATE_SUB(NOW(), INTERVAL 6 DAY), 'Personal Training Session - 1.5 hours'),

-- Class package payments
(8, NULL, 180.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-026'), DATE_SUB(NOW(), INTERVAL 1 DAY), '10-Class Package'),
(9, NULL, 90.00, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-027'), DATE_SUB(NOW(), INTERVAL 2 DAY), '5-Class Package'),
(10, NULL, 360.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-028'), DATE_SUB(NOW(), INTERVAL 3 DAY), '20-Class Package'),
(11, NULL, 45.00, 'card', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-029'), DATE_SUB(NOW(), INTERVAL 4 DAY), 'Drop-in Class Fee'),
(12, NULL, 135.00, 'paypal', 'completed', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-030'), DATE_SUB(NOW(), INTERVAL 5 DAY), '3-Class Package');

-- Insert comprehensive blog posts
INSERT INTO blog_posts (title, slug, content, excerpt, author_id, status, published_at, featured_image, tags, category_id, seo_title, seo_description, reading_time) VALUES
('5 Essential Tips for Building Muscle Mass', '5-essential-tips-building-muscle-mass',
'<h2>Understanding Muscle Growth</h2>
<p>Muscle growth, also known as hypertrophy, occurs when you consistently challenge your muscles with resistance training and proper nutrition. The key is progressive overload - gradually increasing the demands on your muscles over time.</p>

<h2>1. Progressive Overload Training</h2>
<p>Always aim to increase weight, reps, or sets every 1-2 weeks. This constant challenge forces your muscles to adapt and grow stronger.</p>

<h2>2. Protein Intake Optimization</h2>
<p>Consume 1.6-2.2 grams of protein per kilogram of body weight daily. Sources include chicken, fish, eggs, dairy, legumes, and protein supplements.</p>

<h2>3. Caloric Surplus</h2>
<p>To build muscle, you need to consume more calories than you burn. Aim for a 250-500 calorie surplus while focusing on nutrient-dense foods.</p>

<h2>4. Recovery and Sleep</h2>
<p>Muscles grow during recovery, not during workouts. Ensure 7-9 hours of quality sleep and include rest days in your training schedule.</p>

<h2>5. Consistency and Patience</h2>
<p>Building muscle takes time. Stay consistent with your training and nutrition for at least 8-12 weeks to see noticeable results.</p>',
'Master the fundamentals of muscle building with these 5 essential tips that combine training, nutrition, and recovery strategies.',
1, 'published', DATE_SUB(NOW(), INTERVAL 2 DAY), '/assets/img/blog/muscle-building.jpg', '["muscle-building", "strength-training", "nutrition"]', 1,
'5 Essential Tips for Building Muscle Mass | L9 Fitness', 'Learn the fundamental principles of muscle growth with our comprehensive guide covering training, nutrition, and recovery.', 5),

('HIIT Workouts: Maximize Your Time and Results', 'hiit-workouts-maximize-time-results',
'<h2>What is HIIT?</h2>
<p>High-Intensity Interval Training (HIIT) alternates between short bursts of intense exercise and brief recovery periods. This efficient training method burns more calories in less time compared to steady-state cardio.</p>

<h2>Benefits of HIIT</h2>
<ul>
<li><strong>Efficient Fat Burning:</strong> Continue burning calories for hours after your workout</li>
<li><strong>Time-Saving:</strong> Complete effective workouts in 20-30 minutes</li>
<li><strong>Cardiovascular Health:</strong> Improves heart health and endurance</li>
<li><strong>Metabolic Boost:</strong> Increases metabolism and muscle preservation</li>
</ul>

<h2>Sample HIIT Workout</h2>
<p>Try this 20-minute HIIT routine:</p>
<ol>
<li>30 seconds burpees</li>
<li>30 seconds rest</li>
<li>30 seconds mountain climbers</li>
<li>30 seconds rest</li>
<li>30 seconds jump squats</li>
<li>30 seconds rest</li>
</ol>
<p>Repeat 4-6 rounds for maximum results.</p>',
'Discover how High-Intensity Interval Training can revolutionize your fitness routine with maximum results in minimal time.',
1, 'published', DATE_SUB(NOW(), INTERVAL 5 DAY), '/assets/img/blog/hiit-workout.jpg', '["hiit", "cardio", "fat-loss"]', 1,
'HIIT Workouts: Maximize Your Time and Results | L9 Fitness', 'Learn how HIIT training can help you achieve better results in less time with our comprehensive guide.', 4),

('Nutrition for Athletes: Fuel Your Performance', 'nutrition-athletes-fuel-performance',
'<h2>Pre-Workout Nutrition</h2>
<p>Fuel your body 2-3 hours before training with complex carbohydrates and moderate protein. Include foods like oatmeal, bananas, and Greek yogurt for sustained energy.</p>

<h2>Intra-Workout Fuel</h2>
<p>For sessions longer than 90 minutes, consume 30-60g of carbohydrates per hour through sports drinks, gels, or bananas to maintain performance.</p>

<h2>Post-Workout Recovery</h2>
<p>Consume protein and carbohydrates within 30-60 minutes after training. Aim for a 3:1 or 4:1 carbohydrate-to-protein ratio to replenish glycogen and repair muscles.</p>

<h2>Hydration Strategies</h2>
<p>Stay hydrated throughout the day. During intense training, aim to drink 16-32 ounces of water per hour, and consider electrolyte replacement for sessions over 60 minutes.</p>',
'Learn how to properly fuel your body for optimal athletic performance with our comprehensive nutrition guide for athletes.',
1, 'published', DATE_SUB(NOW(), INTERVAL 7 DAY), '/assets/img/blog/athlete-nutrition.jpg', '["nutrition", "athletes", "performance"]', 2,
'Nutrition for Athletes: Fuel Your Performance | L9 Fitness', 'Optimize your athletic performance with proper nutrition timing and fueling strategies.', 6),

('Mental Health and Fitness: The Mind-Body Connection', 'mental-health-fitness-mind-body-connection',
'<h2>The Science Behind Exercise and Mental Health</h2>
<p>Regular exercise releases endorphins, serotonin, and dopamine - neurotransmitters that naturally boost mood and reduce stress. Physical activity also increases brain plasticity and promotes neurogenesis.</p>

<h2>Stress Reduction Benefits</h2>
<p>Exercise serves as a powerful stress reliever by:</p>
<ul>
<li>Reducing cortisol levels</li>
<li>Improving sleep quality</li>
<li>Boosting self-confidence</li>
<li>Providing a healthy distraction from daily worries</li>
</ul>

<h2>Building Resilience Through Training</h2>
<p>Consistent training builds mental toughness and teaches valuable life skills like discipline, perseverance, and goal-setting.</p>

<h2>Mindfulness in Movement</h2>
<p>Activities like yoga and tai chi combine physical movement with mindfulness practices, creating a powerful mind-body connection that enhances both mental and physical well-being.</p>',
'Explore the powerful connection between physical fitness and mental health, and learn how exercise can improve your overall well-being.',
1, 'published', DATE_SUB(NOW(), INTERVAL 10 DAY), '/assets/img/blog/mind-body.jpg', '["mental-health", "mindfulness", "wellness"]', 3,
'Mental Health and Fitness: The Mind-Body Connection | L9 Fitness', 'Discover how physical fitness and mental health are interconnected and learn strategies to improve both.', 7),

('Recovery Techniques Every Athlete Should Know', 'recovery-techniques-every-athlete-should-know',
'<h2>Active Recovery</h2>
<p>Light exercise on rest days promotes blood flow and aids in muscle repair. Activities like walking, swimming, or yoga are perfect for active recovery.</p>

<h2>Sleep Optimization</h2>
<p>Quality sleep is crucial for recovery. Aim for 7-9 hours per night in a cool, dark environment. Consider sleep tracking to optimize your sleep patterns.</p>

<h2>Nutrition for Recovery</h2>
<p>Focus on anti-inflammatory foods like berries, fatty fish, nuts, and leafy greens. Include adequate protein for muscle repair and complex carbohydrates for glycogen replenishment.</p>

<h2>Massage and Mobility Work</h2>
<p>Regular massage, foam rolling, and stretching improve circulation and reduce muscle tension. Incorporate these practices 2-3 times per week.</p>

<h2>Periodization Planning</h2>
<p>Structure your training with planned recovery weeks every 4-6 weeks. This allows your body to fully recover and prevents overtraining syndrome.</p>',
'Master the art of recovery with these essential techniques that will help you train harder, perform better, and prevent injuries.',
1, 'published', DATE_SUB(NOW(), INTERVAL 12 DAY), '/assets/img/blog/recovery.jpg', '["recovery", "athletes", "training"]', 1,
'Recovery Techniques Every Athlete Should Know | L9 Fitness', 'Learn essential recovery strategies to optimize your training and prevent overtraining.', 6),

('Strength Training for Beginners: Getting Started', 'strength-training-beginners-getting-started',
'<h2>Why Strength Training Matters</h2>
<p>Strength training builds muscle, increases bone density, boosts metabolism, and improves functional movement patterns. It\'s essential for long-term health and fitness.</p>

<h2>Basic Equipment Needed</h2>
<p>Start with bodyweight exercises, then progress to dumbbells, barbells, and resistance bands. A good pair of athletic shoes and a yoga mat are also essential.</p>

<h2>Fundamental Movements</h2>
<p>Master these basic patterns before advancing:</p>
<ul>
<li>Squats (bodyweight, then weighted)</li>
<li>Push-ups (wall, knee, then full)</li>
<li>Pull-ups or rows</li>
<li>Planks and core work</li>
</ul>

<h2>Progressive Programming</h2>
<p>Start with 2-3 strength training sessions per week, focusing on compound movements. Gradually increase weight and intensity as you get stronger.</p>

<h2>Common Mistakes to Avoid</h2>
<p>Avoid ego lifting, poor form, and inadequate recovery. Focus on technique first, then progression.</p>',
'Start your strength training journey with confidence using our comprehensive guide for beginners.',
1, 'published', DATE_SUB(NOW(), INTERVAL 15 DAY), '/assets/img/blog/strength-training.jpg', '["strength-training", "beginners", "fitness"]', 1,
'Strength Training for Beginners: Getting Started | L9 Fitness', 'Begin your strength training journey with our comprehensive guide for beginners.', 5),

('Yoga for Stress Relief and Mental Clarity', 'yoga-stress-relief-mental-clarity',
'<h2>Yoga and Stress Reduction</h2>
<p>Yoga combines physical postures, breathing techniques, and meditation to create a powerful stress-relief practice. Regular practice reduces cortisol levels and promotes relaxation.</p>

<h2>Breathing Techniques (Pranayama)</h2>
<p>Learn basic breathing exercises:</p>
<ul>
<li><strong>Deep Breathing:</strong> Inhale for 4 counts, hold for 4, exhale for 4</li>
<li><strong>Alternate Nostril Breathing:</strong> Balances the nervous system</li>
<li><strong>Lion\'s Breath:</strong> Releases tension and frustration</li>
</ul>

<h2>Stress-Relief Poses</h2>
<p>Try these calming poses:</p>
<ul>
<li>Child\'s Pose (Balasana)</li>
<li>Cat-Cow Flow (Marjaryasana-Bitilasana)</li>
<li>Seated Forward Bend (Paschimottanasana)</li>
<li>Corpse Pose (Savasana)</li>
</ul>

<h2>Mindfulness Integration</h2>
<p>Combine yoga with mindfulness practices like body scans and loving-kindness meditation for enhanced stress relief.</p>',
'Discover how yoga can help reduce stress, improve mental clarity, and promote overall well-being.',
1, 'published', DATE_SUB(NOW(), INTERVAL 18 DAY), '/assets/img/blog/yoga-stress.jpg', '["yoga", "stress-relief", "mental-health"]', 3,
'Yoga for Stress Relief and Mental Clarity | L9 Fitness', 'Learn how yoga practices can help reduce stress and improve mental clarity.', 6),

('CrossFit Training: Benefits and Getting Started', 'crossfit-training-benefits-getting-started',
'<h2>What is CrossFit?</h2>
<p>CrossFit is a functional fitness program that combines weightlifting, gymnastics, and metabolic conditioning. It emphasizes constantly varied, high-intensity functional movements.</p>

<h2>CrossFit Benefits</h2>
<ul>
<li><strong>Functional Strength:</strong> Builds real-world strength and mobility</li>
<li><strong>Cardiovascular Fitness:</strong> Improves heart health and endurance</li>
<li><strong>Community:</strong> Strong sense of camaraderie and support</li>
<li><strong>Scalability:</strong> Workouts can be modified for all fitness levels</li>
</ul>

<h2>Getting Started with CrossFit</h2>
<p>Begin with a foundation phase focusing on:</p>
<ol>
<li>Learning proper movement patterns</li>
<li>Building basic strength</li>
<li>Developing work capacity</li>
<li>Understanding CrossFit terminology</li>
</ol>

<h2>Sample WOD (Workout of the Day)</h2>
<p><strong>"Fran":</strong></p>
<ul>
<li>21-15-9 reps of:</li>
<li>Thrusters (95/65 lbs)</li>
<li>Pull-ups</li>
</ul>
<p>Time cap: 10 minutes</p>',
'Explore the world of CrossFit training and learn how this high-intensity fitness program can transform your body and mind.',
1, 'published', DATE_SUB(NOW(), INTERVAL 20 DAY), '/assets/img/blog/crossfit.jpg', '["crossfit", "functional-fitness", "hiit"]', 1,
'CrossFit Training: Benefits and Getting Started | L9 Fitness', 'Discover the benefits of CrossFit training and learn how to get started with this functional fitness program.', 7);

-- Insert class attendance data for better analytics
INSERT INTO class_attendance (class_id, user_id, attendance_date, check_in_time, status) VALUES
(207, 2, CURDATE(), '06:05:00', 'present'),
(207, 3, CURDATE(), '06:02:00', 'present'),
(207, 5, CURDATE(), '06:08:00', 'late'),
(208, 6, CURDATE(), '07:35:00', 'present'),
(208, 7, CURDATE(), '07:32:00', 'present'),
(209, 8, CURDATE(), '09:05:00', 'present'),
(209, 9, CURDATE(), '09:03:00', 'present'),
(209, 10, CURDATE(), '09:07:00', 'present'),
(210, 11, CURDATE(), '10:35:00', 'present'),
(210, 12, CURDATE(), '10:32:00', 'present'),
(211, 2, CURDATE(), '17:05:00', 'present'),
(212, 3, CURDATE(), '18:35:00', 'present'),
(212, 4, CURDATE(), '18:32:00', 'present'),
(213, 5, CURDATE(), '19:35:00', 'present'),
(213, 6, CURDATE(), '19:32:00', 'present');

-- Insert feedback data for better analytics
INSERT INTO feedback (user_id, class_id, trainer_id, rating, comment, feedback_type, related_id, status) VALUES
(2, 207, 11, 5, 'Amazing HIIT session! Jake really pushed me to my limits. Great energy and motivation!', 'class', 207, 'approved'),
(3, 208, 4, 4, 'Good strength training class. Mike provided excellent form corrections.', 'class', 208, 'approved'),
(5, 207, 11, 5, 'Best workout I\'ve had in weeks! The intensity was perfect.', 'class', 207, 'approved'),
(6, 208, 4, 5, 'Mike is an excellent trainer. Very knowledgeable and encouraging.', 'trainer', 4, 'approved'),
(7, 208, 4, 4, 'Solid strength session. Learned some new techniques.', 'class', 208, 'approved'),
(8, 209, 11, 5, 'Cardio Storm was intense but so much fun! Great class.', 'class', 209, 'approved'),
(9, 209, 11, 4, 'Good cardio workout. Jake kept the energy high throughout.', 'trainer', 11, 'approved'),
(10, 209, 11, 5, 'Loved the variety in this cardio class. Never got bored!', 'class', 209, 'approved'),
(11, 210, 12, 5, 'Emma\'s yoga class was so peaceful. Perfect way to start the day.', 'class', 210, 'approved'),
(12, 210, 12, 5, 'Beautiful yoga flow. Emma has such a calming presence.', 'trainer', 12, 'approved');

-- Insert equipment maintenance data
INSERT INTO equipment_maintenance (equipment_id, maintenance_type, description, performed_by, cost, next_due_date) VALUES
(1, 'preventive', 'Regular treadmill maintenance - belt cleaning and lubrication', 15, 0.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 'repair', 'Fixed bent rack upright - replaced damaged part', 15, 45.00, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
(3, 'preventive', 'Chain adjustment and brake pad replacement', 15, 25.00, DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
(4, 'inspection', 'Monthly inspection - all dumbbells in good condition', 15, 0.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- Update equipment status based on maintenance
UPDATE gym_equipment SET status = 'available', last_maintenance = CURDATE() WHERE id IN (1, 4);
UPDATE gym_equipment SET status = 'available', last_maintenance = CURDATE() WHERE id = 2;
UPDATE gym_equipment SET status = 'maintenance', last_maintenance = CURDATE() WHERE id = 3;