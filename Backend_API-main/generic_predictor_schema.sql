-- Generic College Predictor Database Schema
-- Supports KCET, COMEDK, and JEE exams
-- Version: 1.0

-- Main cutoffs table with JSON column for reservation-based cutoffs
CREATE TABLE IF NOT EXISTS `generic_college_cutoffs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_type` VARCHAR(50) NOT NULL COMMENT 'KCET, COMEDK, JEE',
    `year` INT(4) NOT NULL,
    `round` VARCHAR(50) NOT NULL DEFAULT 'Round 1',
    `category` VARCHAR(100) DEFAULT NULL COMMENT 'Engineering, Pharmacy, Nursing, etc.',
    `college_name` VARCHAR(500) NOT NULL,
    `course` VARCHAR(255) NOT NULL,
    `college_type` TINYINT(1) DEFAULT 1 COMMENT '1=Government, 2=Private',
    `address` TEXT DEFAULT NULL,
    `url` VARCHAR(500) DEFAULT NULL,
    `accreditation` VARCHAR(100) DEFAULT NULL,
    `affiliated_to` VARCHAR(255) DEFAULT NULL,
    `cutoff_data` JSON NOT NULL COMMENT 'JSON object with reservation codes as keys and closing ranks as values',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_exam_type` (`exam_type`),
    INDEX `idx_year` (`year`),
    INDEX `idx_round` (`round`),
    INDEX `idx_category` (`category`),
    INDEX `idx_college_name` (`college_name`(255)),
    INDEX `idx_course` (`course`),
    INDEX `idx_exam_year_round` (`exam_type`, `year`, `round`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Predictor usage logs
CREATE TABLE IF NOT EXISTS `generic_predictor_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL,
    `exam_type` VARCHAR(50) NOT NULL,
    `rank` INT(11) NOT NULL,
    `reservation` VARCHAR(50) NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `course` VARCHAR(255) DEFAULT NULL,
    `year` INT(4) DEFAULT NULL,
    `round` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_exam_type` (`exam_type`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example KCET cutoff_data JSON structure:
-- {
--     "GM": 6491,
--     "GMH": 7963,
--     "GMK": 8135,
--     "GMR": 9067,
--     "1G": 10362,
--     "2AG": 10866,
--     "2BG": 12715,
--     "3AG": 7157,
--     "3BG": 7979,
--     "SCG": 27948,
--     "STG": 32536
-- }

-- Example COMEDK cutoff_data JSON structure:
-- {
--     "GM": 5000,
--     "OBC": 7000,
--     "SC": 15000,
--     "ST": 20000
-- }

-- Example JEE cutoff_data JSON structure:
-- {
--     "GEN": 10000,
--     "OBC-NCL": 25000,
--     "SC": 50000,
--     "ST": 80000,
--     "EWS": 30000,
--     "GEN-PwD": 100000
-- }
