-- Additional dummy data for trainer_messages table
-- Messages from members and admin to trainers

INSERT INTO trainer_messages (trainer_id, from_user_id, message, created_at) VALUES
-- Messages for Sarah Warriors (ID: 28)
(28, 2, 'Hi Sarah, I loved your HIIT class yesterday! Can you recommend some home workouts?', '2024-01-10 09:15:00'),
(28, 3, 'Sarah, I have a question about the nutrition plan you mentioned in class.', '2024-01-09 14:30:00'),
(28, 1, 'Sarah, please review the attached client assessment and provide feedback.', '2024-01-08 11:00:00'),

-- Messages for Mike Thunder (ID: 29)
(29, 4, 'Mike, your Power Hour class is amazing! When is the next strength training session?', '2024-01-11 16:45:00'),
(29, 5, 'Mike, I need to reschedule my Warrior Strength class this week.', '2024-01-10 10:20:00'),
(29, 6, 'Mike, thanks for the great workout tips! My bench press improved significantly.', '2024-01-09 08:15:00'),
(29, 1, 'Mike, the gym equipment maintenance schedule needs your approval.', '2024-01-07 13:30:00'),

-- Messages for Lisa Storm (ID: 30)
(30, 7, 'Lisa, your Cardio Storm class really got my heart pumping! Great session!', '2024-01-12 12:00:00'),
(30, 8, 'Lisa, do you have any recommendations for improving my running endurance?', '2024-01-11 15:30:00'),
(30, 1, 'Lisa, please check the cardio equipment inventory and report any issues.', '2024-01-06 09:45:00'),

-- Messages for Emma Zen (ID: 31)
(31, 9, 'Emma, your Zen Flow Yoga class was so relaxing. I slept better than usual!', '2024-01-13 07:30:00'),
(31, 10, 'Emma, can you suggest some meditation techniques for stress relief?', '2024-01-12 18:20:00'),
(31, 11, 'Emma, I loved the breathing exercises you taught us. Very helpful!', '2024-01-11 20:15:00'),
(31, 12, 'Emma, the yoga mats need to be replaced in Studio B.', '2024-01-10 14:00:00'),
(31, 1, 'Emma, please submit your class attendance reports for last month.', '2024-01-05 16:30:00'),

-- Messages for Jake Beast (ID: 32)
(32, 13, 'Jake, CrossFit Chaos was insane! My muscles are still sore but in a good way.', '2024-01-14 11:45:00'),
(32, 14, 'Jake, what supplements do you recommend for muscle recovery?', '2024-01-13 13:20:00'),
(32, 15, 'Jake, thanks for pushing me during the WOD. I PR\'d my clean and jerk!', '2024-01-12 17:10:00'),
(32, 1, 'Jake, the CrossFit area needs additional chalk. Please reorder supplies.', '2024-01-04 10:15:00'),

-- Messages for Alex Viper (ID: 33)
(33, 16, 'Alex, your Combat Training class was exactly what I needed to relieve stress!', '2024-01-15 08:30:00'),
(33, 17, 'Alex, can you teach me some self-defense moves for everyday situations?', '2024-01-14 19:45:00'),
(33, 18, 'Alex, the punching bags in the combat area need maintenance.', '2024-01-13 12:00:00'),
(33, 1, 'Alex, please review the new member applications for combat classes.', '2024-01-03 14:20:00'),

-- Messages for Mike Trainer (ID: 90)
(90, 19, 'Mike, your training style is exactly what I was looking for. Results already showing!', '2024-01-16 15:30:00'),
(90, 20, 'Mike, I need to adjust my workout plan due to a minor injury.', '2024-01-15 09:45:00'),
(90, 21, 'Mike, thanks for the motivation during our session. You\'re an amazing trainer!', '2024-01-14 16:20:00'),
(90, 22, 'Mike, the weight room equipment calibration needs checking.', '2024-01-12 11:30:00'),
(90, 23, 'Mike, can we schedule a personal training assessment?', '2024-01-11 13:15:00'),
(90, 1, 'Mike, please update the client progress tracking system.', '2024-01-02 08:00:00');