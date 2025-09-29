-- Comprehensive nutrition table with different name to avoid conflicts
USE l9_gym;

-- User Nutrition Profiles (comprehensive)
CREATE TABLE IF NOT EXISTS user_nutrition_profiles (
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