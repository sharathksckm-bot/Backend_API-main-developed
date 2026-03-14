-- NEET College Predictor Database Schema
-- Version 1.0
-- 
-- Run this SQL script on the ohcampus_beta MySQL database

-- ====================
-- Table: neet_cutoffs
-- Stores previous year counseling cutoff ranks
-- ====================
CREATE TABLE IF NOT EXISTS `neet_cutoffs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `year` INT(4) NOT NULL,
    `state` VARCHAR(100) NOT NULL COMMENT 'State name (e.g., Karnataka, Tamil Nadu)',
    `counseling_type` VARCHAR(50) NOT NULL DEFAULT 'State Quota' COMMENT 'State Quota / All India Quota / Deemed Universities',
    `college_id` INT(11) NULL COMMENT 'Reference to college table if available',
    `college_name` VARCHAR(255) NOT NULL,
    `course` VARCHAR(50) NOT NULL DEFAULT 'MBBS' COMMENT 'MBBS, BDS, AYUSH',
    `category` VARCHAR(20) NOT NULL COMMENT 'GEN, OBC, SC, ST, EWS, GM, 2A, 2B, 3A, 3B',
    `round` VARCHAR(20) NOT NULL DEFAULT 'Round 1' COMMENT 'Round 1 / Round 2 / Mop-up',
    `opening_rank` INT(11) NOT NULL DEFAULT 0,
    `closing_rank` INT(11) NOT NULL,
    `college_type` VARCHAR(50) NOT NULL DEFAULT 'Government' COMMENT 'Government / Private / Deemed',
    `annual_fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_year` (`year`),
    INDEX `idx_state` (`state`),
    INDEX `idx_category` (`category`),
    INDEX `idx_course` (`course`),
    INDEX `idx_counseling_type` (`counseling_type`),
    INDEX `idx_closing_rank` (`closing_rank`),
    INDEX `idx_search` (`year`, `state`, `category`, `course`, `counseling_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Table: neet_state_rules
-- Stores eligibility rules for state counseling
-- ====================
CREATE TABLE IF NOT EXISTS `neet_state_rules` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `state` VARCHAR(100) NOT NULL UNIQUE,
    `domicile_required` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = True, 0 = False',
    `private_open_seats` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = True, 0 = False - If domicile mismatch, can still see private',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Table: neet_predictor_logs
-- Stores predictor usage for tracking and counselor follow-up
-- ====================
CREATE TABLE IF NOT EXISTS `neet_predictor_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NULL COMMENT 'User ID if logged in',
    `rank` INT(11) NOT NULL,
    `category` VARCHAR(20) NOT NULL,
    `domicile_state` VARCHAR(100) NULL,
    `preferred_state` VARCHAR(100) NULL,
    `counseling_type` VARCHAR(50) NULL,
    `course` VARCHAR(50) NULL DEFAULT 'MBBS',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Insert default state rules for major states
-- ====================
INSERT INTO `neet_state_rules` (`state`, `domicile_required`, `private_open_seats`, `notes`) VALUES
('Karnataka', 1, 1, 'Domicile required for government quota. Private seats open for all.'),
('Tamil Nadu', 1, 0, 'Strict domicile requirement for both government and most private seats.'),
('Maharashtra', 1, 1, 'Domicile required for state quota. Management quota open.'),
('Uttar Pradesh', 1, 1, 'Domicile required for state quota. Some private seats available.'),
('Rajasthan', 1, 1, 'Domicile required for state quota.'),
('Kerala', 1, 1, 'Domicile required for state quota.'),
('West Bengal', 1, 1, 'Domicile required for state quota.'),
('Gujarat', 1, 1, 'Domicile required for state quota.'),
('Andhra Pradesh', 1, 1, 'Domicile required for state quota.'),
('Telangana', 1, 1, 'Domicile required for state quota.'),
('Madhya Pradesh', 1, 1, 'Domicile required for state quota.'),
('Bihar', 1, 1, 'Domicile required for state quota.'),
('Punjab', 1, 1, 'Domicile required for state quota.'),
('Haryana', 1, 1, 'Domicile required for state quota.'),
('Delhi', 0, 1, 'All India Quota dominant. Less state-specific restrictions.'),
('Odisha', 1, 1, 'Domicile required for state quota.'),
('Jharkhand', 1, 1, 'Domicile required for state quota.'),
('Assam', 1, 1, 'Domicile required for state quota.'),
('Chhattisgarh', 1, 1, 'Domicile required for state quota.'),
('Uttarakhand', 1, 1, 'Domicile required for state quota.')
ON DUPLICATE KEY UPDATE state = state;

-- ====================
-- Sample Data for Testing (2024 NEET Cutoffs)
-- ====================
INSERT INTO `neet_cutoffs` (`year`, `state`, `counseling_type`, `college_name`, `course`, `category`, `round`, `opening_rank`, `closing_rank`, `college_type`, `annual_fee`) VALUES
-- Karnataka Government Colleges
(2024, 'Karnataka', 'State Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'GEN', 'Round 1', 1, 850, 'Government', 50000),
(2024, 'Karnataka', 'State Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'OBC', 'Round 1', 100, 2500, 'Government', 50000),
(2024, 'Karnataka', 'State Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'SC', 'Round 1', 5000, 15000, 'Government', 25000),
(2024, 'Karnataka', 'State Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'ST', 'Round 1', 10000, 35000, 'Government', 25000),
(2024, 'Karnataka', 'State Quota', 'Mysore Medical College and Research Institute', 'MBBS', 'GEN', 'Round 1', 200, 3500, 'Government', 50000),
(2024, 'Karnataka', 'State Quota', 'Mysore Medical College and Research Institute', 'MBBS', 'OBC', 'Round 1', 500, 6000, 'Government', 50000),
(2024, 'Karnataka', 'State Quota', 'KIMS Hubli', 'MBBS', 'GEN', 'Round 1', 1000, 8000, 'Government', 50000),
(2024, 'Karnataka', 'State Quota', 'KIMS Hubli', 'MBBS', 'OBC', 'Round 1', 2000, 12000, 'Government', 50000),
-- Karnataka Private Colleges
(2024, 'Karnataka', 'State Quota', 'JSS Medical College, Mysore', 'MBBS', 'GEN', 'Round 1', 5000, 25000, 'Private', 2500000),
(2024, 'Karnataka', 'State Quota', 'KMC Manipal', 'MBBS', 'GEN', 'Round 1', 3000, 18000, 'Private', 2800000),
(2024, 'Karnataka', 'State Quota', 'MS Ramaiah Medical College', 'MBBS', 'GEN', 'Round 1', 4000, 22000, 'Private', 2600000),
-- All India Quota
(2024, 'Karnataka', 'All India Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'GEN', 'Round 1', 1, 5500, 'Government', 50000),
(2024, 'Karnataka', 'All India Quota', 'Bangalore Medical College and Research Institute', 'MBBS', 'OBC', 'Round 1', 2000, 15000, 'Government', 50000),
(2024, 'Karnataka', 'All India Quota', 'Mysore Medical College and Research Institute', 'MBBS', 'GEN', 'Round 1', 500, 12000, 'Government', 50000),
-- Tamil Nadu
(2024, 'Tamil Nadu', 'State Quota', 'Madras Medical College', 'MBBS', 'GEN', 'Round 1', 1, 500, 'Government', 13000),
(2024, 'Tamil Nadu', 'State Quota', 'Stanley Medical College', 'MBBS', 'GEN', 'Round 1', 100, 1500, 'Government', 13000),
(2024, 'Tamil Nadu', 'State Quota', 'Kilpauk Medical College', 'MBBS', 'GEN', 'Round 1', 200, 2500, 'Government', 13000),
-- Maharashtra
(2024, 'Maharashtra', 'State Quota', 'Grant Medical College, Mumbai', 'MBBS', 'GEN', 'Round 1', 1, 1200, 'Government', 60000),
(2024, 'Maharashtra', 'State Quota', 'Seth GS Medical College, Mumbai', 'MBBS', 'GEN', 'Round 1', 50, 800, 'Government', 60000),
(2024, 'Maharashtra', 'State Quota', 'BJ Medical College, Pune', 'MBBS', 'GEN', 'Round 1', 100, 2000, 'Government', 60000),
-- Delhi
(2024, 'Delhi', 'All India Quota', 'Maulana Azad Medical College', 'MBBS', 'GEN', 'Round 1', 1, 100, 'Government', 10000),
(2024, 'Delhi', 'All India Quota', 'VMMC and Safdarjung Hospital', 'MBBS', 'GEN', 'Round 1', 50, 250, 'Government', 10000),
(2024, 'Delhi', 'All India Quota', 'Lady Hardinge Medical College', 'MBBS', 'GEN', 'Round 1', 100, 400, 'Government', 10000),
(2024, 'Delhi', 'All India Quota', 'UCMS and GTB Hospital', 'MBBS', 'GEN', 'Round 1', 150, 600, 'Government', 10000);

-- ====================
-- Add More Sample BDS Data
-- ====================
INSERT INTO `neet_cutoffs` (`year`, `state`, `counseling_type`, `college_name`, `course`, `category`, `round`, `opening_rank`, `closing_rank`, `college_type`, `annual_fee`) VALUES
(2024, 'Karnataka', 'State Quota', 'Government Dental College, Bangalore', 'BDS', 'GEN', 'Round 1', 15000, 45000, 'Government', 35000),
(2024, 'Karnataka', 'State Quota', 'SDM Dental College, Dharwad', 'BDS', 'GEN', 'Round 1', 25000, 80000, 'Private', 800000),
(2024, 'Tamil Nadu', 'State Quota', 'Madras Dental College', 'BDS', 'GEN', 'Round 1', 10000, 35000, 'Government', 13000),
(2024, 'Maharashtra', 'State Quota', 'Government Dental College, Mumbai', 'BDS', 'GEN', 'Round 1', 12000, 40000, 'Government', 50000);
