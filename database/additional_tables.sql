-- Additional tables for comprehensive user profile and fitness tracking

USE l9_gym;

-- User Fitness Profile
CREATE TABLE IF NOT EXISTS user_fitness_profile (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  height DECIMAL(5,2) NULL, -- in cm
  current_weight DECIMAL(5,2) NULL, -- in kg
  target_weight DECIMAL(5,2) NULL,
  fitness_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
  primary_goal ENUM('weight_loss', 'muscle_gain', 'strength', 'endurance', 'general_fitness') DEFAULT 'general_fitness',
  secondary_goals TEXT NULL, -- JSON array of additional goals
  medical_conditions TEXT NULL,
  injuries TEXT NULL,
  activity_level ENUM('sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active') DEFAULT 'moderately_active',
  preferred_workout_time ENUM('early_morning', 'morning', 'afternoon', 'evening', 'late_evening') NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Weight Progress Tracking
CREATE TABLE IF NOT EXISTS weight_progress (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  weight DECIMAL(5,2) NOT NULL,
  body_fat_percentage DECIMAL(4,2) NULL,
  muscle_mass DECIMAL(5,2) NULL,
  recorded_date DATE NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_weight_user_date (user_id, recorded_date)
) ENGINE=InnoDB;

-- Nutrition Plans
CREATE TABLE IF NOT EXISTS nutrition_plans (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_name VARCHAR(100) NOT NULL,
  diet_type ENUM('standard', 'vegetarian', 'vegan', 'keto', 'paleo', 'mediterranean', 'low_carb', 'high_protein') DEFAULT 'standard',
  daily_calories INT UNSIGNED NULL,
  daily_protein DECIMAL(5,2) NULL, -- in grams
  daily_carbs DECIMAL(5,2) NULL, -- in grams
  daily_fat DECIMAL(5,2) NULL, -- in grams
  meals_per_day TINYINT UNSIGNED DEFAULT 3,
  food_allergies TEXT NULL, -- JSON array
  food_preferences TEXT NULL, -- JSON array
  meal_plan TEXT NULL, -- JSON object with meal plans
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Workout Progress
CREATE TABLE IF NOT EXISTS workout_progress (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  exercise_name VARCHAR(100) NOT NULL,
  exercise_type ENUM('cardio', 'strength', 'flexibility', 'balance', 'sports') DEFAULT 'strength',
  sets INT UNSIGNED NULL,
  reps INT UNSIGNED NULL,
  weight DECIMAL(6,2) NULL, -- in kg
  duration INT UNSIGNED NULL, -- in minutes for cardio
  distance DECIMAL(6,2) NULL, -- in km for cardio
  calories_burned INT UNSIGNED NULL,
  workout_date DATE NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_workout_user_date (user_id, workout_date)
) ENGINE=InnoDB;

-- User Goals
CREATE TABLE IF NOT EXISTS user_goals (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  goal_type ENUM('weight', 'strength', 'endurance', 'habit', 'body_measurement', 'performance') NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  target_value DECIMAL(10,2) NULL,
  current_value DECIMAL(10,2) DEFAULT 0,
  unit VARCHAR(20) NULL, -- kg, reps, minutes, etc.
  target_date DATE NULL,
  status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
  priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
  is_public BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample data will be inserted through the dashboard interface