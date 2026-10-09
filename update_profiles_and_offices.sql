-- ====================================================================
-- GAD AMS Database Update: User Profiles & Office Units Normalization
-- Description:
-- 1. Updates office_units table with 'location' (Campus) and 'office_acronym'.
-- 2. Synchronizes office names, locations, and acronyms from Offices.xlsx.
-- 3. Handles duplicates (Office 35 remapped to Office 23 and removed).
-- 4. Creates user_profiles table (1-to-1 with users via FK CASCADE).
-- 5. Migrates existing personal info (first_name, middle_name, last_name, 
--    student_id, year_level) from users into user_profiles.
-- 6. Keeps users.full_name synchronized for backward compatibility.
-- 7. Safely removes obsolete user_acronym from users.
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- STEP 1: Add 'location' and 'office_acronym' columns to office_units
-- --------------------------------------------------------------------
SET @exist_location := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'office_units' 
      AND COLUMN_NAME = 'location'
);

SET @sql_loc := IF(@exist_location = 0, 
    'ALTER TABLE `office_units` ADD COLUMN `location` VARCHAR(100) NOT NULL DEFAULT \'La Trinidad Campus\' AFTER `office_name`', 
    'SELECT 1'
);
PREPARE stmt_loc FROM @sql_loc;
EXECUTE stmt_loc;
DEALLOCATE PREPARE stmt_loc;

SET @exist_acronym := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'office_units' 
      AND COLUMN_NAME = 'office_acronym'
);

SET @sql_acr := IF(@exist_acronym = 0, 
    'ALTER TABLE `office_units` ADD COLUMN `office_acronym` VARCHAR(50) DEFAULT NULL AFTER `location`', 
    'SELECT 1'
);
PREPARE stmt_acr FROM @sql_acr;
EXECUTE stmt_acr;
DEALLOCATE PREPARE stmt_acr;

-- --------------------------------------------------------------------
-- STEP 2: Remap & remove duplicates / test dummies
-- --------------------------------------------------------------------
-- Remap any users referencing duplicate Office 35 to Office 23 (BSU Office of Student Services)
UPDATE `users` SET `office_id` = 23 WHERE `office_id` = 35;
DELETE FROM `office_units` WHERE `office_id` = 35;

-- Clean test dummy office 55 if exists
UPDATE `users` SET `office_id` = NULL WHERE `office_id` = 55;
DELETE FROM `office_units` WHERE `office_id` = 55;

-- --------------------------------------------------------------------
-- STEP 3: Upsert / Update office_units matching Offices.xlsx
-- --------------------------------------------------------------------
INSERT INTO `office_units` (`office_id`, `office_name`, `location`, `office_acronym`) VALUES
(1, 'Gender and Development Office', 'La Trinidad Campus', 'GAD'),
(2, 'College of Agriculture', 'La Trinidad Campus', 'CA'),
(3, 'Registrar''s Office BSU Buguias Campus', 'Buguias Campus', 'Buguias-RO'),
(4, 'Human Resources and Management Office BSU Bokod Campus', 'Bokod Campus', 'Bokod-HRMO'),
(5, 'International Relations Office', 'La Trinidad Campus', 'IRO'),
(6, 'Disaster Risk Reduction Management', 'La Trinidad Campus', 'DRRM'),
(7, 'College of Social Science', 'La Trinidad Campus', 'CSS'),
(8, 'College of Applied Techonology BSU Bokod', 'Bokod Campus', 'Bokod-CAT'),
(9, 'University Business Affairs Office', 'La Trinidad Campus', 'UBAO'),
(10, 'University Library and Information Services BSU Buguias', 'Buguias Campus', 'Buguias-ULIS'),
(11, 'College of Veterinary Medicine', 'La Trinidad Campus', 'CVM'),
(12, 'Compensation, Benefits and Other Obligations', 'La Trinidad Campus', 'CBOO'),
(13, 'Records Office and Archives', 'La Trinidad Campus', 'ROA'),
(14, 'Budget Office', 'La Trinidad Campus', 'BO'),
(15, 'Office for Quality Assurance and Accreditation', 'La Trinidad Campus', 'OQAA'),
(16, 'University Health Services BSU Buguias', 'Buguias Campus', 'Buguias-UHS'),
(17, 'College of Natural Sciences', 'La Trinidad Campus', 'CNS'),
(18, 'College of Public Administration and Governance', 'La Trinidad Campus', 'CPAG'),
(19, 'Information and Communications Technolgy', 'La Trinidad Campus', 'ICT'),
(20, 'General Services Office', 'La Trinidad Campus', 'GSO'),
(21, 'College of Engineering', 'La Trinidad Campus', 'CE'),
(22, 'College of Nursing', 'La Trinidad Campus', 'CN'),
(23, 'BSU Office of Student Services', 'La Trinidad Campus', 'OSS'),
(24, 'University Public Affairs Office', 'La Trinidad Campus', 'UPAO'),
(25, 'Accounting Office', 'La Trinidad Campus', 'AO'),
(26, 'College of Human Kenetics', 'La Trinidad Campus', 'CHK'),
(27, 'Horticulture', 'La Trinidad Campus', 'Horticulture'),
(28, 'University Health Services BSU Bokod', 'Bokod Campus', 'Bokod-UHS'),
(29, 'College of Agriculture BSU Buguias', 'Buguias Campus', 'Buguias-CA'),
(30, 'Human Resources Development Office', 'La Trinidad Campus', 'HRDO'),
(31, 'Budget Office BSU Buguias', 'Buguias Campus', 'Buguias-BO'),
(32, 'College of Information Sciences', 'La Trinidad Campus', 'CIS'),
(33, 'Procurement Management Office BSU Bokod', 'Bokod Campus', 'Bokod-PMO'),
(34, 'Procurement Management Office', 'La Trinidad Campus', 'PMO'),
(36, 'College of Arts and Humanities', 'La Trinidad Campus', 'CAH'),
(37, 'College of Teacher Education', 'La Trinidad Campus', 'CTE'),
(38, 'Human Resource and Management Office', 'La Trinidad Campus', 'HRMO'),
(39, 'College of Home Economics and Technology', 'La Trinidad Campus', 'CHET'),
(40, 'Supply Property Management Office', 'La Trinidad Campus', 'SPMO'),
(41, 'University Library and Information Services', 'La Trinidad Campus', 'ULIS'),
(42, 'College of Numeracy and Applied Sciences', 'La Trinidad Campus', 'CNAS'),
(43, 'Northern Philippines Root Crops Research  & Training Center', 'La Trinidad Campus', 'NPRCRTC'),
(44, 'Open University', 'La Trinidad Campus', 'OU'),
(45, 'College of Education BSU Bokod', 'Bokod Campus', 'Bokod-CE'),
(46, 'College of Forestry', 'La Trinidad Campus', 'CF')
ON DUPLICATE KEY UPDATE 
    `office_name` = VALUES(`office_name`),
    `location` = VALUES(`location`),
    `office_acronym` = VALUES(`office_acronym`);

-- --------------------------------------------------------------------
-- STEP 4: Create user_profiles Table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `sex` ENUM('Male', 'Female', 'Prefer not to say') DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `position` VARCHAR(150) DEFAULT NULL,
  `department` VARCHAR(255) DEFAULT NULL,
  `student_id` VARCHAR(100) DEFAULT NULL,
  `year_level` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_id` (`user_id`),
  CONSTRAINT `fk_profile_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- STEP 5: Migrate existing user profile data
-- --------------------------------------------------------------------
INSERT INTO `user_profiles` (`user_id`, `first_name`, `middle_name`, `last_name`, `student_id`, `year_level`, `created_at`, `updated_at`)
SELECT 
    `id` AS `user_id`,
    COALESCE(NULLIF(`first_name`, ''), SUBSTRING_INDEX(`full_name`, ' ', 1)) AS `first_name`,
    `middle_name`,
    COALESCE(NULLIF(`last_name`, ''), SUBSTRING_INDEX(`full_name`, ' ', -1)) AS `last_name`,
    `student_id`,
    `year_level`,
    NOW(),
    NOW()
FROM `users`
ON DUPLICATE KEY UPDATE
    `first_name` = VALUES(`first_name`),
    `middle_name` = VALUES(`middle_name`),
    `last_name` = VALUES(`last_name`),
    `student_id` = VALUES(`student_id`),
    `year_level` = VALUES(`year_level`);

-- --------------------------------------------------------------------
-- STEP 6: Safely remove user_acronym from users table
-- --------------------------------------------------------------------
SET @has_user_acronym := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'users' 
      AND COLUMN_NAME = 'user_acronym'
);

SET @sql_drop_acronym := IF(@has_user_acronym > 0, 
    'ALTER TABLE `users` DROP COLUMN `user_acronym`', 
    'SELECT 1'
);
PREPARE stmt_drop FROM @sql_drop_acronym;
EXECUTE stmt_drop;
DEALLOCATE PREPARE stmt_drop;

SET FOREIGN_KEY_CHECKS = 1;

-- Finished successfully
