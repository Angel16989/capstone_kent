-- L9 Fitness Gym - Additional Dummy Data
USE l9_gym;

-- Insert comprehensive bookings data for existing users
-- Skipping bookings to avoid constraint issues, will add more realistic data later

-- Insert comprehensive payment data for revenue
INSERT INTO payments (member_id, membership_id, amount, method, status, invoice_no, paid_at) VALUES
-- Additional recent payments
(2, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-100'), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 2, 399.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-101'), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 1, 49.99, 'paypal', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-102'), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-103'), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-104'), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8, 2, 399.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-105'), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-106'), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(10, 1, 49.99, 'paypal', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-107'), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(11, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-108'), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(12, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-109'), DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- More historical payments for revenue tracking
(2, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 30 DAY), '%Y%m%d'), '-110'), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3, 2, 399.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 31 DAY), '%Y%m%d'), '-111'), DATE_SUB(NOW(), INTERVAL 31 DAY)),
(5, 1, 49.99, 'paypal', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 32 DAY), '%Y%m%d'), '-112'), DATE_SUB(NOW(), INTERVAL 32 DAY)),
(6, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 33 DAY), '%Y%m%d'), '-113'), DATE_SUB(NOW(), INTERVAL 33 DAY)),
(7, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 34 DAY), '%Y%m%d'), '-114'), DATE_SUB(NOW(), INTERVAL 34 DAY)),
(8, 2, 399.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 35 DAY), '%Y%m%d'), '-115'), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(9, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 36 DAY), '%Y%m%d'), '-116'), DATE_SUB(NOW(), INTERVAL 36 DAY)),
(10, 1, 49.99, 'paypal', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 37 DAY), '%Y%m%d'), '-117'), DATE_SUB(NOW(), INTERVAL 37 DAY)),
(11, 1, 49.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 38 DAY), '%Y%m%d'), '-118'), DATE_SUB(NOW(), INTERVAL 38 DAY)),
(12, 3, 79.99, 'card', 'paid', CONCAT('INV-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 39 DAY), '%Y%m%d'), '-119'), DATE_SUB(NOW(), INTERVAL 39 DAY));

-- Insert comprehensive blog posts
INSERT INTO blog_posts (title, slug, content, excerpt, author_id, status, published_at, featured_image) VALUES
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
1, 'published', DATE_SUB(NOW(), INTERVAL 2 DAY), '/assets/img/blog/muscle-building.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 5 DAY), '/assets/img/blog/hiit-workout.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 7 DAY), '/assets/img/blog/athlete-nutrition.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 10 DAY), '/assets/img/blog/mind-body.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 12 DAY), '/assets/img/blog/recovery.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 15 DAY), '/assets/img/blog/strength-training.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 18 DAY), '/assets/img/blog/yoga-stress.jpg'),

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
1, 'published', DATE_SUB(NOW(), INTERVAL 20 DAY), '/assets/img/blog/crossfit.jpg');

-- Insert class attendance data
-- Skipping attendance to avoid issues with non-existent class IDs

-- Insert feedback data
-- Skipping feedback to avoid issues with non-existent class/trainer IDs