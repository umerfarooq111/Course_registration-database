USE `university.db`;

DELETE FROM Course_Offering;

-- Insert dummy offerings based on actual course_section data
INSERT INTO Course_Offering (course_id, semester, section_id) VALUES
(1, 1, 1),
(1, 2, 2);

