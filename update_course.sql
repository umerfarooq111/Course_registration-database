USE `university.db`;

DELIMITER $$
CREATE PROCEDURE AddCourseTypeColumn()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Course' AND TABLE_SCHEMA = 'university.db' AND COLUMN_NAME = 'course_type'
    ) THEN
        ALTER TABLE Course ADD COLUMN course_type ENUM('Core', 'Elective') DEFAULT 'Core';
    END IF;
END$$
DELIMITER ;
CALL AddCourseTypeColumn();
DROP PROCEDURE AddCourseTypeColumn;
