-- Test data for unit testing

-- Test users
INSERT INTO users (username, password, role, full_name, email, status) VALUES 
('test_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Test Admin', 'admin@test.edu', 'active'),
('test_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', 'Test Lecturer', 'lecturer@test.edu', 'active'),
('test_lecturer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', 'Test Lecturer 2', 'lecturer2@test.edu', 'active');

-- Test courses
INSERT INTO courses (course_name, course_code, description, created_by) VALUES 
('Software Engineering', 'CS401', 'Advanced software engineering principles', 1),
('Database Systems', 'CS301', 'Database design and implementation', 1);

-- Test projects
INSERT INTO projects (project_name, description, student_name, student_id, course_id) VALUES 
('E-Commerce Platform', 'Online shopping system', 'John Doe', 'S1001', 1),
('Library Management', 'Digital library system', 'Jane Smith', 'S1002', 1),
('Student Portal', 'Student information system', 'Bob Johnson', 'S1003', 2);

-- Test rubric criteria
INSERT INTO rubric_criteria (criterion_name, max_score, course_id) VALUES 
('Functionality', 30, 1),
('Code Quality', 25, 1),
('Documentation', 20, 1),
('User Interface', 25, 1),
('Database Design', 30, 2),
('Performance', 25, 2);

-- Test evaluations
INSERT INTO evaluations (project_id, lecturer_id, total_score, feedback) VALUES 
(1, 2, 85.5, 'Good implementation with minor issues'),
(2, 2, 92.0, 'Excellent work with comprehensive features');

-- Test evaluation scores
INSERT INTO evaluation_scores (evaluation_id, criterion_id, score) VALUES 
(1, 1, 28),
(1, 2, 22),
(1, 3, 18),
(1, 4, 17.5),
(2, 1, 30),
(2, 2, 24),
(2, 3, 20),
(2, 4, 18);