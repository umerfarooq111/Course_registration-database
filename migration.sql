USE `university.db`;

-- Add semester to Student if it doesn't exist
-- We use a stored procedure to safely add the column if it doesn't exist
DELIMITER $$
CREATE PROCEDURE AddSemesterColumn()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Student' AND TABLE_SCHEMA = 'university.db' AND COLUMN_NAME = 'semester'
    ) THEN
        ALTER TABLE Student ADD COLUMN semester INT DEFAULT 1;
    END IF;
END$$
DELIMITER ;
CALL AddSemesterColumn();
DROP PROCEDURE AddSemesterColumn;

CREATE TABLE IF NOT EXISTS Course_Offering (
    offering_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    section_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES Course(course_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES Course_Section(section_id) ON DELETE CASCADE
);

-- Delete existing dummy offerings if any to avoid duplicates when rerunning
DELETE FROM Course_Offering;

-- Insert dummy offerings
INSERT INTO Course_Offering (course_id, semester, section_id) VALUES
(1, 1, 1), -- Intro to Programming is semester 1, section 1
(2, 2, 2), -- Deep Learning is semester 2, section 2
(3, 1, 3), -- Structural Analysis is semester 1, section 3
(4, 2, 4), -- Thermodynamics is semester 2, section 4
(5, 1, 5); -- Circuit Analysis is semester 1, section 5

-- Update students to have a semester
UPDATE Student SET semester = 1 WHERE student_id IN (1, 3, 6);
UPDATE Student SET semester = 2 WHERE student_id IN (2, 4);
