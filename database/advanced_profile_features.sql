-- Additional comprehensive tables for advanced profile features
USE l9_gym;

-- User Profile Photos
CREATE TABLE IF NOT EXISTS user_profile_photos (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  photo_path VARCHAR(255) NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- In-App Announcements
CREATE TABLE IF NOT EXISTS announcements_user (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  announcement_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_announcement_user (announcement_id, user_id)
) ENGINE=InnoDB;

-- Waitlist Management
CREATE TABLE IF NOT EXISTS class_waitlist (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id INT UNSIGNED NOT NULL,
  member_id INT UNSIGNED NOT NULL,
  position INT UNSIGNED NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('waiting', 'moved_to_booking', 'cancelled') DEFAULT 'waiting',
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_class_member_waitlist (class_id, member_id)
) ENGINE=InnoDB;

-- Payment History (Enhanced)
CREATE TABLE IF NOT EXISTS payment_history (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  payment_id VARCHAR(100) NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'AUD',
  payment_method ENUM('paypal', 'stripe', 'bank_transfer', 'cash') DEFAULT 'paypal',
  payment_type ENUM('membership', 'class', 'personal_training', 'merchandise') DEFAULT 'membership',
  status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  description TEXT NULL,
  invoice_number VARCHAR(50) NULL,
  receipt_url VARCHAR(255) NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Check-in/Check-out Logging
CREATE TABLE IF NOT EXISTS member_checkins (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  member_id INT UNSIGNED NOT NULL,
  checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  checkout_time TIMESTAMP NULL,
  duration_minutes INT UNSIGNED NULL,
  facility_area ENUM('gym_floor', 'cardio_area', 'weights_area', 'group_class', 'pool', 'spa') DEFAULT 'gym_floor',
  notes TEXT NULL,
  FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_member_checkin_date (member_id, checkin_time)
) ENGINE=InnoDB;

-- Trainer Attendance Tracking
CREATE TABLE IF NOT EXISTS trainer_attendance (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  trainer_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NULL,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NULL,
  status ENUM('present', 'absent', 'late', 'sick', 'emergency') DEFAULT 'present',
  notes TEXT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Messages with Trainers and Admin
CREATE TABLE IF NOT EXISTS user_messages (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  sender_id INT UNSIGNED NOT NULL,
  recipient_id INT UNSIGNED NOT NULL,
  recipient_type ENUM('member', 'trainer', 'admin') NOT NULL,
  subject VARCHAR(200) NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  read_at TIMESTAMP NULL,
  message_type ENUM('general', 'class_related', 'payment_related', 'complaint', 'suggestion') DEFAULT 'general',
  priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  attachment_path VARCHAR(255) NULL,
  parent_message_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_message_id) REFERENCES user_messages(id) ON DELETE SET NULL,
  INDEX idx_recipient_unread (recipient_id, is_read),
  INDEX idx_conversation (sender_id, recipient_id, created_at)
) ENGINE=InnoDB;

-- Payment Receipts and Invoicing
CREATE TABLE IF NOT EXISTS payment_receipts (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  payment_history_id INT UNSIGNED NOT NULL,
  receipt_number VARCHAR(50) NOT NULL UNIQUE,
  invoice_number VARCHAR(50) NULL,
  member_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  tax_amount DECIMAL(10,2) DEFAULT 0.00,
  discount_amount DECIMAL(10,2) DEFAULT 0.00,
  final_amount DECIMAL(10,2) NOT NULL,
  receipt_data JSON NULL,
  pdf_path VARCHAR(255) NULL,
  email_sent BOOLEAN DEFAULT FALSE,
  email_sent_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (payment_history_id) REFERENCES payment_history(id) ON DELETE CASCADE,
  FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Trainer Class Updates/Notifications
CREATE TABLE IF NOT EXISTS trainer_class_updates (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  trainer_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  update_type ENUM('schedule_change', 'cancellation', 'substitute', 'location_change', 'capacity_change') NOT NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  message TEXT NULL,
  affect_members BOOLEAN DEFAULT TRUE,
  notification_sent BOOLEAN DEFAULT FALSE,
  notification_sent_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;