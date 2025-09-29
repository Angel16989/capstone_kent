-- Sample fitness data for Manish (user_id: 9)
USE l9_gym;

-- Insert fitness profile for Manish
INSERT INTO user_fitness_profile (user_id, height, current_weight, target_weight, fitness_level, primary_goal, secondary_goals, medical_conditions, activity_level, preferred_workout_time)
VALUES (9, 175.5, 85.2, 78.0, 'intermediate', 'weight_loss', 
        '["muscle_gain", "strength"]', 
        'None reported', 
        'very_active', 
        'morning')
ON DUPLICATE KEY UPDATE 
    height = VALUES(height),
    current_weight = VALUES(current_weight),
    target_weight = VALUES(target_weight),
    updated_at = CURRENT_TIMESTAMP;

-- Insert weight progress data (showing progress over last 3 months)
INSERT INTO weight_progress (user_id, weight, body_fat_percentage, muscle_mass, recorded_date, notes)
VALUES 
    (9, 88.5, 18.5, 32.1, DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'Starting weight - motivated to begin journey'),
    (9, 87.2, 17.8, 32.8, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 'Good progress with diet changes'),
    (9, 86.1, 17.2, 33.2, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'Started strength training consistently'),
    (9, 85.8, 16.9, 33.5, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'Seeing muscle definition improvements'),
    (9, 85.5, 16.5, 33.8, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Energy levels much better'),
    (9, 85.2, 16.2, 34.0, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Strength gains are noticeable'),
    (9, 84.9, 15.8, 34.2, CURDATE(), 'Feeling great, clothes fitting better!')
ON DUPLICATE KEY UPDATE weight = VALUES(weight);

-- Insert nutrition plan for Manish (vegetarian preference)
INSERT INTO nutrition_plans (user_id, plan_name, diet_type, daily_calories, daily_protein, daily_carbs, daily_fat, meals_per_day, food_allergies, food_preferences, meal_plan, is_active)
VALUES (9, 'Manish Weight Loss Plan', 'vegetarian', 2200, 140.0, 200.0, 75.0, 5, 
        '["nuts"]', 
        '["high_protein", "low_sugar", "whole_grains"]',
        '{"breakfast": "Oatmeal with fruits and protein powder", "mid_morning": "Greek yogurt with berries", "lunch": "Quinoa bowl with vegetables and paneer", "afternoon": "Protein smoothie", "dinner": "Grilled tofu with vegetables and brown rice"}',
        TRUE)
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active);

-- Insert workout progress (recent workouts)
INSERT INTO workout_progress (user_id, exercise_name, exercise_type, sets, reps, weight, duration, calories_burned, workout_date, notes)
VALUES 
    -- Strength training sessions
    (9, 'Bench Press', 'strength', 3, 10, 75.0, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Good form, increased weight'),
    (9, 'Squats', 'strength', 4, 12, 80.0, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Deep squats, excellent range'),
    (9, 'Deadlifts', 'strength', 3, 8, 100.0, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Personal best!'),
    
    -- Cardio sessions
    (9, 'Treadmill Running', 'cardio', NULL, NULL, NULL, 45, 420, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Good pace, felt strong'),
    (9, 'Cycling', 'cardio', NULL, NULL, NULL, 60, 480, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '15km distance covered'),
    
    -- More strength training
    (9, 'Pull-ups', 'strength', 3, 8, NULL, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Unassisted pull-ups!'),
    (9, 'Overhead Press', 'strength', 3, 10, 45.0, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Shoulder strength improving'),
    
    -- Recent cardio
    (9, 'Elliptical', 'cardio', NULL, NULL, NULL, 30, 350, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Low impact day')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- Insert user goals for Manish
INSERT INTO user_goals (user_id, goal_type, title, description, target_value, current_value, unit, target_date, status, priority)
VALUES 
    (9, 'weight', 'Reach Target Weight', 'Lose weight from 88.5kg to 78kg through consistent training and nutrition', 78.0, 84.9, 'kg', DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'active', 'high'),
    (9, 'strength', 'Bench Press 100kg', 'Increase bench press from current 75kg to 100kg', 100.0, 75.0, 'kg', DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'active', 'medium'),
    (9, 'endurance', 'Run 10K in Under 45 Minutes', 'Improve cardiovascular endurance and running speed', 45.0, 52.0, 'minutes', DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'active', 'medium'),
    (9, 'body_measurement', 'Reduce Body Fat to 12%', 'Lower body fat percentage from current 16% to 12%', 12.0, 15.8, 'percentage', DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 'active', 'high'),
    (9, 'habit', 'Workout 5 Days Per Week', 'Maintain consistent workout schedule throughout the year', 5.0, 4.5, 'days_per_week', DATE_ADD(CURDATE(), INTERVAL 12 MONTH), 'active', 'high'),
    (9, 'performance', 'Complete 15 Pull-ups', 'Increase unassisted pull-up count from 8 to 15', 15.0, 8.0, 'reps', DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'active', 'medium')
ON DUPLICATE KEY UPDATE target_value = VALUES(target_value);

-- Create some class bookings for Manish (attended classes)
INSERT INTO bookings (member_id, class_id, status)
SELECT 9, id, 'attended'
FROM classes 
WHERE class_type IN ('Strength Training', 'HIIT', 'Cardio Blast', 'Functional Fitness')
LIMIT 12
ON DUPLICATE KEY UPDATE status = VALUES(status);