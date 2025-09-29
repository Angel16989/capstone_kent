-- Additional comprehensive features for L9 Fitness profile system
USE l9_gym;

-- User profile photos
CREATE TABLE IF NOT EXISTS user_photos (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  is_profile_picture BOOLEAN DEFAULT FALSE,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- In-app announcements
CREATE TABLE IF NOT EXISTS announcements (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  type ENUM('general', 'maintenance', 'promotion', 'class_update', 'emergency') DEFAULT 'general',
  priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  target_audience ENUM('all', 'members', 'trainers', 'staff') DEFAULT 'all',
  start_date DATETIME NOT NULL,
  end_date DATETIME NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- User announcement views/reads
CREATE TABLE IF NOT EXISTS user_announcement_reads (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  announcement_id INT UNSIGNED NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_announcement (user_id, announcement_id)
) ENGINE=InnoDB;

-- Payment history and receipts
CREATE TABLE IF NOT EXISTS payment_receipts (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  payment_id VARCHAR(100) NOT NULL,
  transaction_type ENUM('membership', 'class', 'personal_training', 'merchandise', 'other') DEFAULT 'membership',
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'AUD',
  payment_method ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash', 'other') DEFAULT 'credit_card',
  payment_status ENUM('pending', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
  description TEXT NULL,
  receipt_number VARCHAR(100) UNIQUE NOT NULL,
  invoice_data JSON NULL,
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  processed_by INT UNSIGNED NULL,
  notes TEXT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_payment (user_id, payment_date),
  INDEX idx_receipt_number (receipt_number)
) ENGINE=InnoDB;

-- Check-in/Check-out logging
CREATE TABLE IF NOT EXISTS gym_check_logs (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  check_type ENUM('check_in', 'check_out') NOT NULL,
  check_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  location VARCHAR(100) DEFAULT 'Main Gym',
  method ENUM('card_scan', 'mobile_app', 'manual', 'qr_code') DEFAULT 'card_scan',
  staff_id INT UNSIGNED NULL,
  notes TEXT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_check_time (user_id, check_time)
) ENGINE=InnoDB;

-- Trainer attendance tracking
CREATE TABLE IF NOT EXISTS trainer_attendance (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  trainer_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NULL,
  attendance_date DATE NOT NULL,
  check_in_time TIME NULL,
  check_out_time TIME NULL,
  status ENUM('present', 'late', 'absent', 'sick_leave', 'emergency_leave') DEFAULT 'present',
  hours_worked DECIMAL(4,2) NULL,
  notes TEXT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_trainer_date (trainer_id, attendance_date)
) ENGINE=InnoDB;

-- Messaging system between users, trainers, and admins
CREATE TABLE IF NOT EXISTS user_messages (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  sender_id INT UNSIGNED NOT NULL,
  recipient_id INT UNSIGNED NOT NULL,
  subject VARCHAR(200) NULL,
  message TEXT NOT NULL,
  message_type ENUM('general', 'class_inquiry', 'complaint', 'feedback', 'emergency', 'billing') DEFAULT 'general',
  priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  status ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
  parent_message_id INT UNSIGNED NULL,
  attachments JSON NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP NULL,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_message_id) REFERENCES user_messages(id) ON DELETE SET NULL,
  INDEX idx_recipient_status (recipient_id, status),
  INDEX idx_sender_sent (sender_id, sent_at)
) ENGINE=InnoDB;

-- Trainer class updates and notifications
CREATE TABLE IF NOT EXISTS trainer_class_updates (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  trainer_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  update_type ENUM('schedule_change', 'cancellation', 'substitute', 'capacity_change', 'location_change', 'other') NOT NULL,
  original_value TEXT NULL,
  new_value TEXT NULL,
  reason TEXT NULL,
  effective_date DATETIME NOT NULL,
  notification_sent BOOLEAN DEFAULT FALSE,
  approved_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_trainer_class (trainer_id, class_id),
  INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB;

-- Waitlist management enhancement (extends existing waitlists table)
CREATE TABLE IF NOT EXISTS waitlist_notifications (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  waitlist_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  notification_type ENUM('spot_available', 'class_cancelled', 'time_changed', 'reminder') NOT NULL,
  message TEXT NOT NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  method ENUM('email', 'sms', 'push', 'in_app') DEFAULT 'in_app',
  status ENUM('sent', 'delivered', 'failed', 'read') DEFAULT 'sent',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_sent (user_id, sent_at)
) ENGINE=InnoDB;

-- User preferences and settings
CREATE TABLE IF NOT EXISTS user_preferences (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  notifications_email BOOLEAN DEFAULT TRUE,
  notifications_sms BOOLEAN DEFAULT FALSE,
  notifications_push BOOLEAN DEFAULT TRUE,
  marketing_emails BOOLEAN DEFAULT TRUE,
  class_reminders BOOLEAN DEFAULT TRUE,
  payment_reminders BOOLEAN DEFAULT TRUE,
  language VARCHAR(10) DEFAULT 'en',
  timezone VARCHAR(50) DEFAULT 'Australia/Sydney',
  date_format ENUM('DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD') DEFAULT 'DD/MM/YYYY',
  measurement_system ENUM('metric', 'imperial') DEFAULT 'metric',
  privacy_level ENUM('public', 'members_only', 'private') DEFAULT 'members_only',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_preferences (user_id)
) ENGINE=InnoDB;