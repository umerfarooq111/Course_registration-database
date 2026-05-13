USE `university.db`;

-- Add batch column to Student table if it doesn't exist
-- Batch can be the year of enrollment, e.g., 2024
ALTER TABLE Student ADD COLUMN batch INT DEFAULT 2024;

-- Create Registration_Limits table
CREATE TABLE IF NOT EXISTS Registration_Limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch INT NOT NULL,
    semester INT NOT NULL,
    max_courses INT NOT NULL,
    UNIQUE KEY unique_batch_semester (batch, semester)
);

-- Insert some default limits
INSERT IGNORE INTO Registration_Limits (batch, semester, max_courses) VALUES 
(2024, 1, 5),
(2024, 2, 6),
(2023, 3, 5),
(2023, 4, 6);
