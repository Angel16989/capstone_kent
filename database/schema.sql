CREATE DATABASE IF NOT EXISTS l9_gym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE l9_gym;

-- Roles & Users
CREATE TABLE user_roles (
  id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(30) NOT NULL UNIQUE  -- admin, staff, trainer, member
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role_id TINYINT UNSIGNED NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  phone VARCHAR(30),
  emergency_contact VARCHAR(120),
  password_hash VARCHAR(255) NOT NULL,
  gender ENUM('male','female','other') NULL,
  dob DATE NULL,
  address VARCHAR(255),
  status ENUM('active','suspended','deleted') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES user_roles(id)
) ENGINE=InnoDB;

-- Trainers (extend users)
CREATE TABLE trainers (
  user_id INT UNSIGNED PRIMARY KEY,
  bio TEXT,
  certifications TEXT,
  rate_per_session DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Membership Plans (one-time fee determines active duration)
CREATE TABLE membership_plans (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  duration_days SMALLINT UNSIGNED NOT NULL, -- e.g., 30, 90, 365
  price DECIMAL(10,2) NOT NULL,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Packages (monthly billables: can include trainer or extras)
CREATE TABLE packages (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  monthly_price DECIMAL(10,2) NOT NULL,
  includes_trainer TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Member's current/previous memberships
CREATE TABLE memberships (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  package_id INT UNSIGNED NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,  -- start_date + duration_days
  status ENUM('active','expired','cancelled') DEFAULT 'active',
  auto_renew TINYINT(1) DEFAULT 0,
  total_fee DECIMAL(10,2) NOT NULL, -- plan fee (one-time)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
  FOREIGN KEY (package_id) REFERENCES packages(id),
  INDEX idx_memberships_member (member_id),
  INDEX idx_memberships_status (status)
) ENGINE=InnoDB;

-- Classes & Scheduling
CREATE TABLE classes (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  location VARCHAR(120) DEFAULT 'Main Studio',
  capacity SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  trainer_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (trainer_id) REFERENCES users(id), -- trainer is a user with trainer role
  INDEX idx_classes_time (start_time, end_time)
) ENGINE=InnoDB;

-- Bookings with waitlist support
CREATE TABLE bookings (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  status ENUM('booked','cancelled','waitlist','attended','no_show') DEFAULT 'booked',
  waitlist_position SMALLINT UNSIGNED NULL,
  booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_member_class (member_id, class_id),
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB;

-- Attendance (class or general check-in)
CREATE TABLE attendance (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NULL,           -- null for general gym check-in
  check_in DATETIME NOT NULL,
  check_out DATETIME NULL,
  source ENUM('gate','kiosk','manual') DEFAULT 'kiosk',
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id),
  INDEX idx_attendance_member_time (member_id, check_in)
) ENGINE=InnoDB;

-- Payments (membership, class, package renewals)
CREATE TABLE payments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  membership_id INT UNSIGNED NULL,
  booking_id INT UNSIGNED NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('card','paypal','cash') DEFAULT 'card',
  status ENUM('paid','pending','failed','refunded') DEFAULT 'paid',
  txn_ref VARCHAR(191) NULL,
  invoice_no VARCHAR(50) NULL,
  paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (membership_id) REFERENCES memberships(id),
  FOREIGN KEY (booking_id) REFERENCES bookings(id)
) ENGINE=InnoDB;

-- Workout & Nutrition Plans
CREATE TABLE workout_plans (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  trainer_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  details TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (trainer_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE nutrition_plans (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  trainer_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  details TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (trainer_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Equipment & Maintenance
CREATE TABLE equipment (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  serial_no VARCHAR(120) NULL,
  status ENUM('available','in_use','maintenance','retired') DEFAULT 'available',
  maintenance_due DATE NULL,
  notes TEXT
) ENGINE=InnoDB;

-- Feedback & Reviews
CREATE TABLE feedback (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  trainer_id INT UNSIGNED NULL,
  class_id INT UNSIGNED NULL,
  rating TINYINT UNSIGNED,
  comments TEXT,
  reply_text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id),
  FOREIGN KEY (trainer_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB;

-- Announcements / CMS-lite
CREATE TABLE announcements (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_by INT UNSIGNED NOT NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- Helpful seed data
INSERT INTO user_roles (name) VALUES ('admin'), ('staff'), ('trainer'), ('member');

INSERT INTO membership_plans (name, description, duration_days, price)
VALUES
('Monthly', '30-day access', 30, 49.00),
('Quarterly', '90-day access', 90, 129.00),
('Yearly', '365-day access', 365, 399.00);

INSERT INTO packages (name, description, monthly_price, includes_trainer)
VALUES
('Basic', 'Gym access + group classes', 0.00, 0),
('Plus', 'Adds sauna + towels', 19.00, 0),
('PT Add-on', '1 PT session / week', 199.00, 1);
