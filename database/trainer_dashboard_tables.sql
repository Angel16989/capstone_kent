-- Additional tables for Trainer Dashboard functionality

-- Table for trainer sick leaves
CREATE TABLE IF NOT EXISTS trainer_sick_leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    class_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('submitted', 'approved', 'denied') DEFAULT 'submitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Table for trainer suggestions
CREATE TABLE IF NOT EXISTS trainer_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    file_path VARCHAR(500),
    status ENUM('pending', 'reviewed', 'approved', 'rejected') DEFAULT 'pending',
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for trainer messages
CREATE TABLE IF NOT EXISTS trainer_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    from_user_id INT,
    to_admin BOOLEAN DEFAULT FALSE,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for customer files forwarded by admin to trainers
CREATE TABLE IF NOT EXISTS customer_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    assigned_trainer_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    notes TEXT,
    forwarded_by INT NOT NULL,
    forwarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'completed') DEFAULT 'pending',
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (forwarded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for class bookings (if not exists)
CREATE TABLE IF NOT EXISTS class_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled', 'pending') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (user_id, class_id)
);

-- Table for classes (if not exists or needs updates)
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    trainer_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration INT DEFAULT 60, -- Duration in minutes
    capacity INT DEFAULT 20,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for user notifications
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'class_cancelled', 'trainer_message') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for admin notifications
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'trainer_suggestion', 'trainer_message') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample data for testing

-- Sample classes for testing (assuming trainer with ID 1 exists)
INSERT IGNORE INTO classes (name, description, trainer_id, date, time, duration, capacity) VALUES
('HIIT Bootcamp', 'High-intensity interval training for maximum results', 1, CURDATE() + INTERVAL 1 DAY, '09:00:00', 45, 15),
('Strength Training', 'Build muscle and increase strength', 1, CURDATE() + INTERVAL 2 DAY, '10:30:00', 60, 12),
('Yoga Flow', 'Relaxing yoga session for flexibility and mindfulness', 1, CURDATE() + INTERVAL 3 DAY, '07:00:00', 75, 20),
('Cardio Blast', 'Fat-burning cardio workout', 1, CURDATE() + INTERVAL 4 DAY, '18:00:00', 30, 25);

-- Sample class bookings (assuming users with IDs 2, 3, 4 exist)
INSERT IGNORE INTO class_bookings (user_id, class_id, status) VALUES
(2, 1, 'confirmed'),
(3, 1, 'confirmed'),
(4, 1, 'confirmed'),
(2, 2, 'confirmed'),
(3, 3, 'confirmed');

-- Table for customer suggestions from trainers
CREATE TABLE IF NOT EXISTS customer_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    trainer_id INT NOT NULL,
    file_id INT,
    title VARCHAR(255) NOT NULL,
    suggestion TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('sent', 'read', 'acknowledged') DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES customer_files(id) ON DELETE SET NULL
);

-- Sample trainer messages
INSERT IGNORE INTO trainer_messages (trainer_id, from_user_id, subject, message) VALUES
(1, NULL, 'Welcome!', 'Welcome to the L9 Fitness trainer system! This is your message center.'),
(1, 2, 'Question about workout', 'Hi! I have a question about the HIIT workout from yesterday. Can you provide some modifications for knee issues?');