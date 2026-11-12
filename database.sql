-- Gaskiya Medical Diagnostics and Research Laboratory - Database Schema
-- Compatible with MySQL / MariaDB (InfinityFree phpMyAdmin)

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'scientist', 'receptionist') DEFAULT 'scientist',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin Account (Username: admin | Password: password123)
INSERT INTO `users` (`full_name`, `username`, `password_hash`, `role`) VALUES
('Chief Medical Laboratory Scientist', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE `username`=`username`;

CREATE TABLE IF NOT EXISTS `test_catalog` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `test_code` VARCHAR(20) NOT NULL UNIQUE,
  `test_name` VARCHAR(150) NOT NULL,
  `department` VARCHAR(80) NOT NULL,
  `sample_type` VARCHAR(60) NOT NULL,
  `reference_range` VARCHAR(120) DEFAULT 'See Comment',
  `unit` VARCHAR(30) DEFAULT '',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `test_catalog` (`test_code`, `test_name`, `department`, `sample_type`, `reference_range`, `unit`, `price`) VALUES
('HEM-001', 'Full Blood Count (FBC)', 'Hematology', 'EDTA Whole Blood', 'WBC: 4.0-11.0 | Hb: 12.0-17.5', 'x10^9/L | g/dL', 4500.00),
('HEM-002', 'Malaria Parasite (MP) Microscopy', 'Parasitology', 'EDTA Whole Blood', 'No Malaria Parasite Seen', 'N/A', 1500.00),
('CHM-001', 'Fasting Blood Sugar (FBS)', 'Clinical Chemistry', 'Fluoride Oxalate', '3.9 - 5.8', 'mmol/L', 2000.00),
('CHM-002', 'Lipid Profile (Total Chol, HDL, LDL, TG)', 'Clinical Chemistry', 'Plain / Gel Serum', 'Total Chol: < 5.2', 'mmol/L', 8500.00),
('CHM-003', 'Liver Function Test (LFT)', 'Clinical Chemistry', 'Plain / Gel Serum', 'AST: 5-40 | ALT: 7-56', 'U/L', 9000.00),
('CHM-004', 'Electrolytes, Urea & Creatinine (E/U/Cr)', 'Clinical Chemistry', 'Lithium Heparin', 'Cr: 60-115 | Urea: 2.5-7.1', 'umol/L | mmol/L', 8500.00),
('MIC-001', 'Widal Agglutination Test', 'Serology / Immunology', 'Plain Serum', 'Significant Titre >= 1:160', 'Titre', 2500.00),
('MIC-002', 'Urinalysis (Complete)', 'Microbiology', 'Mid-Stream Urine', 'Clear, Amber, pH 5.0-7.0', 'N/A', 2000.00),
('MOL-001', 'Hepatitis B Surface Antigen (HBsAg) ELISA', 'Molecular & Virology', 'Serum', 'Non-Reactive', 'Index', 3500.00)
ON DUPLICATE KEY UPDATE `test_code`=`test_code`;

CREATE TABLE IF NOT EXISTS `patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lab_no` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(120) NOT NULL,
  `age` VARCHAR(20) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `phone` VARCHAR(30) DEFAULT '',
  `referring_doctor` VARCHAR(120) DEFAULT 'Self / Walk-in',
  `clinical_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lab_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `accession_no` VARCHAR(35) NOT NULL UNIQUE,
  `patient_id` INT NOT NULL,
  `test_id` INT NOT NULL,
  `result_value` TEXT,
  `flag` ENUM('Normal', 'High', 'Low', 'Critical', 'Pending') DEFAULT 'Pending',
  `scientist_comment` TEXT,
  `verified_by` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('Pending', 'Completed', 'Verified') DEFAULT 'Pending',
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`test_id`) REFERENCES `test_catalog`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_ref` VARCHAR(30) NOT NULL UNIQUE,
  `patient_name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(120) DEFAULT '',
  `preferred_date` DATE NOT NULL,
  `test_requested` VARCHAR(180) NOT NULL,
  `collection_type` ENUM('Walk-in Clinic', 'Home Sample Collection') DEFAULT 'Walk-in Clinic',
  `status` ENUM('Scheduled', 'Sample Collected', 'Cancelled') DEFAULT 'Scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
