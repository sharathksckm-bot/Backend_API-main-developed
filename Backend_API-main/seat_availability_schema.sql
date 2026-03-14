-- Seat Availability & AI Optimizer Database Schema
-- Version 1.0
-- 
-- Run this SQL script on the ohcampus_beta MySQL database

-- ====================
-- Table: seat_availability
-- Stores real-time counseling seat availability
-- ====================
CREATE TABLE IF NOT EXISTS `seat_availability` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `year` INT(4) NOT NULL,
    `state` VARCHAR(100) NOT NULL,
    `counseling_type` VARCHAR(50) NOT NULL DEFAULT 'State Quota' COMMENT 'State Quota / All India Quota / Deemed',
    `college_id` INT(11) NULL COMMENT 'Reference to college table if available',
    `college_name` VARCHAR(255) NOT NULL,
    `course` VARCHAR(50) NOT NULL DEFAULT 'MBBS',
    `round` VARCHAR(30) NOT NULL COMMENT 'Round 1, Round 2, Mop-up, Stray Vacancy',
    `category` VARCHAR(20) NOT NULL COMMENT 'GEN, OBC, SC, ST, EWS, etc.',
    `total_seats` INT(11) NOT NULL DEFAULT 0,
    `filled_seats` INT(11) NOT NULL DEFAULT 0,
    `available_seats` INT(11) NOT NULL DEFAULT 0,
    `status` ENUM('Active', 'Completed', 'Upcoming') NOT NULL DEFAULT 'Active',
    `source` VARCHAR(50) NOT NULL DEFAULT 'Manual' COMMENT 'Manual / API / CSV Import',
    `external_api_id` VARCHAR(100) NULL COMMENT 'ID from external counseling API if synced',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_year` (`year`),
    INDEX `idx_state` (`state`),
    INDEX `idx_round` (`round`),
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`),
    INDEX `idx_lookup` (`year`, `state`, `counseling_type`, `round`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Table: ai_optimization_logs
-- Stores AI preference optimization requests
-- ====================
CREATE TABLE IF NOT EXISTS `ai_optimization_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NULL COMMENT 'User ID if logged in',
    `rank` INT(11) NOT NULL,
    `category` VARCHAR(20) NOT NULL,
    `state` VARCHAR(100) NULL,
    `priority` VARCHAR(20) NULL DEFAULT 'balanced' COMMENT 'probability / ranking / fee / balanced',
    `colleges_analyzed` INT(11) NOT NULL DEFAULT 0,
    `response_colleges` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Table: external_api_config
-- Stores external counseling API configurations
-- ====================
CREATE TABLE IF NOT EXISTS `external_api_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `api_name` VARCHAR(100) NOT NULL,
    `api_endpoint` VARCHAR(255) NOT NULL,
    `api_key` VARCHAR(255) NULL,
    `state` VARCHAR(100) NULL,
    `counseling_type` VARCHAR(50) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_sync` DATETIME NULL,
    `sync_frequency` VARCHAR(20) DEFAULT 'daily' COMMENT 'hourly / daily / weekly',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- Sample Data for Seat Availability (2025)
-- ====================
INSERT INTO `seat_availability` (`year`, `state`, `counseling_type`, `college_name`, `course`, `round`, `category`, `total_seats`, `filled_seats`, `available_seats`, `status`) VALUES
-- Karnataka Round 1
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'GEN', 150, 145, 5, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'OBC', 80, 78, 2, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'SC', 40, 35, 5, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'ST', 20, 15, 5, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Mysore Medical College', 'MBBS', 'Round 1', 'GEN', 120, 118, 2, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Mysore Medical College', 'MBBS', 'Round 1', 'OBC', 65, 60, 5, 'Active'),
(2025, 'Karnataka', 'State Quota', 'KIMS Hubli', 'MBBS', 'Round 1', 'GEN', 100, 95, 5, 'Active'),
(2025, 'Karnataka', 'State Quota', 'KIMS Hubli', 'MBBS', 'Round 1', 'OBC', 55, 50, 5, 'Active'),
-- Karnataka Round 2
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 2', 'GEN', 5, 3, 2, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Bangalore Medical College', 'MBBS', 'Round 2', 'OBC', 2, 1, 1, 'Active'),
(2025, 'Karnataka', 'State Quota', 'Mysore Medical College', 'MBBS', 'Round 2', 'GEN', 2, 0, 2, 'Active'),
-- All India Quota
(2025, 'Karnataka', 'All India Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'GEN', 50, 48, 2, 'Active'),
(2025, 'Karnataka', 'All India Quota', 'Bangalore Medical College', 'MBBS', 'Round 1', 'OBC', 25, 24, 1, 'Active'),
(2025, 'Karnataka', 'All India Quota', 'Mysore Medical College', 'MBBS', 'Round 1', 'GEN', 40, 38, 2, 'Active'),
-- Tamil Nadu
(2025, 'Tamil Nadu', 'State Quota', 'Madras Medical College', 'MBBS', 'Round 1', 'GEN', 200, 200, 0, 'Completed'),
(2025, 'Tamil Nadu', 'State Quota', 'Stanley Medical College', 'MBBS', 'Round 1', 'GEN', 150, 148, 2, 'Active'),
-- Maharashtra
(2025, 'Maharashtra', 'State Quota', 'Grant Medical College', 'MBBS', 'Round 1', 'GEN', 180, 175, 5, 'Active'),
(2025, 'Maharashtra', 'State Quota', 'Seth GS Medical College', 'MBBS', 'Round 1', 'GEN', 160, 155, 5, 'Active');

-- ====================
-- Sample External API Config
-- ====================
INSERT INTO `external_api_config` (`api_name`, `api_endpoint`, `state`, `counseling_type`, `is_active`, `sync_frequency`) VALUES
('MCC NEET UG', 'https://mcc.nic.in/api/seats', NULL, 'All India Quota', 0, 'daily'),
('KEA Karnataka', 'https://kea.kar.nic.in/api/availability', 'Karnataka', 'State Quota', 0, 'hourly'),
('TN Medical Selection', 'https://tnmedicalselection.org/api', 'Tamil Nadu', 'State Quota', 0, 'daily');
