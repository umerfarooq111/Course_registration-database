-- 1. Insert Departments safely
INSERT IGNORE INTO Department (department_name) VALUES 
('Computer Science'), 
('Artificial Intelligence'), 
('Civil Engineering'),
('Mechanical Engineering'),
('Electrical Engineering');

-- 2. Insert an Admin safely
INSERT IGNORE INTO Admin (admin_name, email, password) VALUES 
('Main Campus Admin', 'admin@university.com', 'admin123');

-- 3. Insert Instructors safely
INSERT IGNORE INTO Instructor (instructor_name, email, department_id) VALUES 
('Dr. Alan Turing', 'alan.turing@university.com', 1),    -- CS
('Dr. Geoffrey Hinton', 'g.hinton@university.com', 2),   -- AI
('Dr. John Smeaton', 'j.smeaton@university.com', 3),     -- Civil
('Dr. James Watt', 'j.watt@university.com', 4),          -- Mechanical
('Dr. Nikola Tesla', 'n.tesla@university.com', 5);       -- Electrical

-- 4. Insert Courses safely
INSERT IGNORE INTO Course (title, credit_hr, max_capacity, department_id, admin_id) VALUES 
('Introduction to Programming', 3, 30, 1, 1),           -- Course ID 1 (CS)
('Deep Learning Foundation', 4, 25, 2, 1),              -- Course ID 2 (AI)
('Structural Analysis I', 3, 40, 3, 1),                 -- Course ID 3 (Civil)
('Thermodynamics Principles', 3, 35, 4, 1),             -- Course ID 4 (Mechanical)
('Circuit Analysis', 4, 30, 5, 1);                      -- Course ID 5 (EE)

-- 5. Insert Prerequisites safely
INSERT IGNORE INTO Pre_Requisite (course_id, required_course_id) VALUES 
(2, 1);

-- 6. Insert Course Sections safely
INSERT IGNORE INTO Course_Section (course_id, instructor_id, enrollment_count) VALUES 
(1, 1, 0), -- Intro to Programming with Dr. Alan Turing
(2, 2, 0), -- Deep Learning with Dr. Geoffrey Hinton
(3, 3, 0), -- Structural Analysis with Dr. John Smeaton
(4, 4, 0), -- Thermodynamics with Dr. James Watt
(5, 5, 0); -- Circuit Analysis with Dr. Nikola Tesla
