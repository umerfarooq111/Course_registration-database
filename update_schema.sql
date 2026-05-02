USE `university.db`;

DROP PROCEDURE IF EXISTS DropSectionIdFromOffering;
DELIMITER $$
CREATE PROCEDURE DropSectionIdFromOffering()
BEGIN
    DECLARE fk_name VARCHAR(64);
    
    SELECT CONSTRAINT_NAME INTO fk_name 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'Course_Offering' 
      AND COLUMN_NAME = 'section_id' 
      AND TABLE_SCHEMA = 'university.db' 
      AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1;
      
    IF fk_name IS NOT NULL THEN
        SET @s = CONCAT('ALTER TABLE Course_Offering DROP FOREIGN KEY ', fk_name);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
    
    IF EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Course_Offering' AND TABLE_SCHEMA = 'university.db' AND COLUMN_NAME = 'section_id'
    ) THEN
        ALTER TABLE Course_Offering DROP COLUMN section_id;
    END IF;
END$$
DELIMITER ;
CALL DropSectionIdFromOffering();
DROP PROCEDURE DropSectionIdFromOffering;

DROP PROCEDURE IF EXISTS AddStudentColumns;
DELIMITER $$
CREATE PROCEDURE AddStudentColumns()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Student' AND TABLE_SCHEMA = 'university.db' AND COLUMN_NAME = 'cgpa'
    ) THEN
        ALTER TABLE Student ADD COLUMN cgpa DECIMAL(3,2) DEFAULT 0.00;
    END IF;
    
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Student' AND TABLE_SCHEMA = 'university.db' AND COLUMN_NAME = 'credits_completed'
    ) THEN
        ALTER TABLE Student ADD COLUMN credits_completed INT DEFAULT 0;
    END IF;
END$$
DELIMITER ;
CALL AddStudentColumns();
DROP PROCEDURE AddStudentColumns;
