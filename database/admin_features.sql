-- Additional tables for comprehensive admin features

-- Additional tables for comprehensive admin features

-- Additional tables for comprehensive admin features

-- Blog Categories (create first for foreign key references)
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog and News Management
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    excerpt VARCHAR(500),
    author_id INT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured_image VARCHAR(255),
    tags VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL
);

-- Blog Post Categories (many-to-many)
CREATE TABLE IF NOT EXISTS blog_post_categories (
    post_id INT,
    category_id INT,
    PRIMARY KEY (post_id, category_id)
);

-- In-app Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    target_audience ENUM('all', 'members', 'trainers', 'admins') DEFAULT 'all',
    created_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL
);

-- Equipment Tracking
CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    location VARCHAR(100),
    status ENUM('active', 'maintenance', 'out_of_order', 'retired') DEFAULT 'active',
    last_maintenance DATE,
    next_maintenance DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Equipment Maintenance History
CREATE TABLE IF NOT EXISTS equipment_maintenance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT,
    maintenance_type VARCHAR(100),
    description TEXT,
    cost DECIMAL(10,2),
    performed_by VARCHAR(100),
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_maintenance DATE
);

-- Feedback System
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    feedback_type ENUM('class', 'trainer', 'facility', 'general') DEFAULT 'general',
    related_id INT, -- class_id or trainer_id depending on type
    rating INT CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    status ENUM('pending', 'reviewed', 'responded') DEFAULT 'pending',
    admin_response TEXT,
    responded_by INT,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Content Management Pages
CREATE TABLE IF NOT EXISTS cms_pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_key VARCHAR(50) UNIQUE NOT NULL, -- 'faq', 'about', 'contact', 'privacy', 'terms'
    title VARCHAR(255) NOT NULL,
    content TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Class Attendance Tracking
CREATE TABLE IF NOT EXISTS class_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    user_id INT,
    attendance_date DATE,
    check_in_time TIME,
    check_out_time TIME,
    status ENUM('present', 'late', 'absent') DEFAULT 'present',
    notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (class_id, user_id, attendance_date)
);

-- Usage Reports
CREATE TABLE IF NOT EXISTS usage_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(100), -- 'login', 'class_booking', 'payment', 'profile_update', etc.
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default CMS pages
INSERT IGNORE INTO cms_pages (page_key, title, content) VALUES
('faq', 'Frequently Asked Questions', '<h2>Common Questions</h2><p>FAQ content will be added here.</p>'),
('about', 'About L9 Fitness', '<h2>About Our Gym</h2><p>About content will be added here.</p>'),
('contact', 'Contact Us', '<h2>Get In Touch</h2><p>Contact information will be added here.</p>'),
('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2><p>Privacy policy content will be added here.</p>'),
('terms', 'Terms of Service', '<h2>Terms and Conditions</h2><p>Terms of service content will be added here.</p>');

-- Insert sample equipment
INSERT IGNORE INTO gym_equipment (name, category, location, status) VALUES
('Treadmill Pro', 'Cardio', 'Main Floor', 'active'),
('Dumbbell Set', 'Strength', 'Weight Room', 'active'),
('Yoga Mats', 'Flexibility', 'Studio A', 'active'),
('Rowing Machine', 'Cardio', 'Main Floor', 'maintenance'),
('Bench Press', 'Strength', 'Weight Room', 'active');

-- Insert sample blog categories
INSERT IGNORE INTO blog_categories (name, slug, description) VALUES
('Fitness Tips', 'fitness-tips', 'Health and fitness advice'),
('Nutrition', 'nutrition', 'Diet and nutrition guides'),
('Success Stories', 'success-stories', 'Member transformation stories'),
('Events', 'events', 'Gym events and announcements');