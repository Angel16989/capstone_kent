-- L9 Fitness Gym - Sample Data
USE l9_gym;

-- Insert sample user roles (already in schema.sql)
-- INSERT INTO user_roles (name) VALUES ('admin'), ('staff'), ('trainer'), ('member');

-- Insert sample users with proper password hashes (Password123)
INSERT INTO users (role_id, first_name, last_name, email, password_hash, gender, dob, address, created_at) VALUES
(1, 'Admin', 'User', 'admin@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1985-01-15', '123 Admin Street, Admin City', NOW()),
(4, 'Tina', 'Johnson', 'tina@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1992-03-22', '456 Member Ave, Fitness City', NOW()),
(4, 'Mia', 'Rodriguez', 'mia@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1988-07-10', '789 Beast Street, Gym Town', NOW()),
(3, 'Mike', 'Thompson', 'mike@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1980-11-05', '321 Trainer Blvd, Muscle City', NOW()),
(2, 'Sarah', 'Wilson', 'sarah@l9.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1987-09-18', '654 Staff Road, Gym Central', NOW());

-- Insert trainer details
INSERT INTO trainers (user_id, bio, certifications, rate_per_session) VALUES
(4, 'Certified personal trainer with 8+ years experience in strength training and HIIT workouts.', 'NASM-CPT, CrossFit Level 2, Nutrition Coaching', 75.00);

-- Insert sample membership plans (already in schema.sql)
-- INSERT INTO membership_plans (name, description, duration_days, price) VALUES...

-- Insert sample packages (already in schema.sql)
-- INSERT INTO packages (name, description, monthly_price, includes_trainer) VALUES...

-- Insert sample memberships
INSERT INTO memberships (member_id, plan_id, package_id, start_date, end_date, status, total_fee) VALUES
(2, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active', 49.00),
(3, 3, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'active', 399.00);

-- Insert sample classes
INSERT INTO classes (title, description, location, capacity, start_time, end_time, trainer_id) VALUES
('Beast Mode HIIT', 'High-intensity interval training for maximum calorie burn', 'Studio A', 15, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 1 HOUR), 4),
('Strength & Power', 'Heavy lifting and power movements', 'Strength Zone', 10, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 90 MINUTE), 4),
('Mindful Yoga Flow', 'Relaxing yoga session for flexibility and mindfulness', 'Studio B', 20, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 75 MINUTE), 4);

-- Insert sample bookings
INSERT INTO bookings (member_id, class_id, status, booked_at) VALUES
(2, 1, 'booked', NOW()),
(3, 2, 'booked', NOW()),
(2, 3, 'booked', NOW());

-- Insert sample equipment
INSERT INTO equipment (name, serial_no, status, maintenance_due, notes) VALUES
('Treadmill Pro X1', 'TM001', 'available', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Regular maintenance scheduled'),
('Power Rack Beast', 'PR002', 'available', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Heavy duty power rack'),
('Spin Bike Elite', 'SB003', 'maintenance', CURDATE(), 'Chain needs adjustment'),
('Dumbbells Set 5-50kg', 'DB004', 'available', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'Full weight set');

-- Insert sample payments
INSERT INTO payments (member_id, membership_id, amount, method, status, invoice_no, paid_at) VALUES
(2, 1, 49.00, 'card', 'paid', 'INV-2024-001', NOW()),
(3, 2, 399.00, 'card', 'paid', 'INV-2024-002', NOW());

-- Insert sample announcements
INSERT INTO announcements (title, body, created_by, published_at) VALUES
('New Class Schedule!', 'We are excited to announce new HIIT classes starting next Monday. Beast Mode HIIT will push your limits!', 1, NOW()),
('Gym Maintenance Notice', 'The gym will be closed for equipment maintenance on Sunday from 6 AM to 10 AM.', 1, DATE_SUB(NOW(), INTERVAL 1 DAY));
