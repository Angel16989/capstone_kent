-- Additional dummy data for customer_files table
-- Files forwarded by admin to trainers for review

INSERT INTO customer_files (customer_id, assigned_trainer_id, file_name, file_path, notes, forwarded_at) VALUES
-- Files for Sarah Warriors (ID: 28)
(2, 28, 'fitness_assessment_john_doe.pdf', '/uploads/customer_files/fitness_assessment_john_doe.pdf', 'Initial fitness assessment - client has knee concerns, focus on low-impact exercises', '2024-01-08 10:30:00'),
(3, 28, 'progress_report_sarah_smith.pdf', '/uploads/customer_files/progress_report_sarah_smith.pdf', 'Monthly progress report - excellent improvements in cardiovascular endurance', '2024-01-07 14:15:00'),

-- Files for Mike Thunder (ID: 29)
(4, 29, 'strength_training_plan_mike_johnson.pdf', '/uploads/customer_files/strength_training_plan_mike_johnson.pdf', 'Custom strength training program - client wants to focus on compound lifts', '2024-01-09 11:45:00'),
(5, 29, 'injury_assessment_lisa_brown.pdf', '/uploads/customer_files/injury_assessment_lisa_brown.pdf', 'Shoulder injury assessment - recommend physical therapy before resuming heavy lifting', '2024-01-06 16:20:00'),
(6, 29, 'nutrition_plan_david_wilson.pdf', '/uploads/customer_files/nutrition_plan_david_wilson.pdf', 'Meal plan for muscle gain - 2500 calories, high protein focus', '2024-01-05 09:30:00'),

-- Files for Lisa Storm (ID: 30)
(7, 30, 'cardio_assessment_jennifer_garcia.pdf', '/uploads/customer_files/cardio_assessment_jennifer_garcia.pdf', 'Cardiovascular fitness evaluation - excellent baseline, ready for advanced cardio', '2024-01-10 13:00:00'),

-- Files for Emma Zen (ID: 31)
(8, 31, 'yoga_progress_maria_rodriguez.pdf', '/uploads/customer_files/yoga_progress_maria_rodriguez.pdf', 'Yoga practice progress - client has improved flexibility significantly', '2024-01-11 08:45:00'),
(9, 31, 'meditation_journal_susan_taylor.pdf', '/uploads/customer_files/meditation_journal_susan_taylor.pdf', 'Weekly meditation practice log - consistent improvement in stress reduction', '2024-01-04 15:30:00'),

-- Files for Jake Beast (ID: 32)
(10, 32, 'crossfit_evaluation_robert_anderson.pdf', '/uploads/customer_files/crossfit_evaluation_robert_anderson.pdf', 'CrossFit readiness assessment - strong candidate for advanced programming', '2024-01-12 12:15:00'),
(11, 32, 'performance_metrics_chris_davis.pdf', '/uploads/customer_files/performance_metrics_chris_davis.pdf', 'WOD performance tracking - PR in multiple lifts, recommend competition prep', '2024-01-03 17:45:00'),

-- Files for Alex Viper (ID: 33)
(12, 33, 'combat_assessment_mark_thompson.pdf', '/uploads/customer_files/combat_assessment_mark_thompson.pdf', 'Martial arts skill evaluation - natural talent, recommend advanced techniques', '2024-01-13 10:00:00'),

-- Files for Mike Trainer (ID: 90)
(13, 90, 'personal_training_contract_amy_white.pdf', '/uploads/customer_files/personal_training_contract_amy_white.pdf', 'New client contract - 12-week transformation program', '2024-01-14 14:30:00'),
(14, 90, 'body_composition_analysis_brian_miller.pdf', '/uploads/customer_files/body_composition_analysis_brian_miller.pdf', 'Body fat analysis results - 8% reduction in 6 weeks, excellent progress', '2024-01-02 11:15:00'),
(15, 90, 'workout_log_karen_jones.pdf', '/uploads/customer_files/workout_log_karen_jones.pdf', 'Detailed workout tracking - consistent attendance, good form technique', '2024-01-01 16:00:00');