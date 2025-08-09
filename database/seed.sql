USE l9_gym;
INSERT INTO user_roles (name) VALUES ('admin'),('staff'),('trainer'),('member');
-- bcrypt for Password123
INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES
(1,'Admin','User','admin@l9.local','$2y$10$0kQWk0MCpUqnzmp2MZQWueJ2mmszM7yJx9o2qvux9R0b0Qw4w0H3S'),
(3,'Tina','Trainer','tina@l9.local','$2y$10$0kQWk0MCpUqnzmp2MZQWueJ2mmszM7yJx9o2qvux9R0b0Qw4w0H3S'),
(4,'Mia','Member','mia@l9.local','$2y$10$0kQWk0MCpUqnzmp2MZQWueJ2mmszM7yJx9o2qvux9R0b0Qw4w0H3S');
INSERT INTO membership_plans (name, description, duration_days, price) VALUES
('Monthly','30-day access',30,49.00),('Quarterly','90-day access',90,129.00),('Yearly','365-day access',365,399.00);
-- Trainer Tina teaches some demo classes
INSERT INTO classes (title, description, location, capacity, start_time, end_time, trainer_id) VALUES
('Strength 101','Intro strength training','Main Studio',20, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY + INTERVAL 1 HOUR), 2),
('Morning Spin','Cardio blast','Spin Room',15, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY + INTERVAL 1 HOUR), 2),
('Evening Yoga','Flexibility and calm','Studio B',25, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY + INTERVAL 1 HOUR), 2);
