-- ==============================================================================
-- PCU SMART PHARMACY & PRIMARY HEALTHCARE SYSTEM
-- STANDALONE DATABASE SCHEMA & SEED DATA
-- ==============================================================================
-- Architecture: Strictly isolated to Localhost MySQL / MariaDB (Port 3306)
-- Target Database: pcu_pharmacy
-- Safety Rule: NEVER alters or writes to JHCIS Database (Port 3333).
--              JHCIS DB remains 100% READ-ONLY for hospital data integrity.
-- Generated At: 2026-09-23
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `pcu_pharmacy` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pcu_pharmacy`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- ----------------------------------------------------------------------
-- Table structure for `audit_logs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `patient_pid` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `payload_before` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload_before`)),
  `payload_after` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload_after`)),
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `patient_pid` (`patient_pid`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `cold_chain_temperature_logs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `cold_chain_temperature_logs`;
CREATE TABLE `cold_chain_temperature_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `session` enum('morning','afternoon') NOT NULL,
  `current_temp` decimal(4,1) NOT NULL,
  `min_recorded` decimal(4,1) DEFAULT NULL,
  `max_recorded` decimal(4,1) DEFAULT NULL,
  `is_excursion` tinyint(1) DEFAULT 0,
  `corrective_action` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `unit_id` (`unit_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `record_date` (`record_date`),
  CONSTRAINT `cold_chain_temperature_logs_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `cold_chain_units` (`unit_id`),
  CONSTRAINT `cold_chain_temperature_logs_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `cold_chain_units`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `cold_chain_units`;
CREATE TABLE `cold_chain_units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `unit_code` varchar(50) NOT NULL,
  `unit_name` varchar(150) NOT NULL,
  `min_temp` decimal(4,1) DEFAULT 2.0,
  `max_temp` decimal(4,1) DEFAULT 8.0,
  `model_info` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`unit_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `cold_chain_units_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `cold_chain_units` (2 rows)
INSERT INTO `cold_chain_units` (`unit_id`, `facility_id`, `unit_code`, `unit_name`, `min_temp`, `max_temp`, `model_info`, `is_active`) VALUES
  (1, 1, 'FRIDGE-PCU-01', 'ตู้เย็นชีววัตถุและวัคซีนหลัก (SANYO/Panasonic MPR)', 2.0, 8.0, 'Haier HXC-158 Biomedical', 1),
  (2, 1, 'FRIDGE-PCU-02', 'ตู้เย็นสำรองและเก็บอินซูลินผู้ป่วย', 2.0, 8.0, 'รุ่นสองประตู มีเทอร์โมมิเตอร์ Min-Max', 1);

-- ----------------------------------------------------------------------
-- Table structure for `drug_master_configs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `drug_master_configs`;
CREATE TABLE `drug_master_configs` (
  `drugcode` varchar(50) NOT NULL,
  `drugnamethai` varchar(255) DEFAULT NULL,
  `druggenericname` varchar(255) DEFAULT NULL,
  `is_nlem` tinyint(1) DEFAULT 1,
  `is_ham` tinyint(1) DEFAULT 0,
  `is_lasa` tinyint(1) DEFAULT 0,
  `is_antibiotic` tinyint(1) DEFAULT 0,
  `is_cold_chain` tinyint(1) DEFAULT 0,
  `is_emergency` tinyint(1) DEFAULT 0,
  `is_herbal` tinyint(1) DEFAULT 0,
  `min_stock` int(11) DEFAULT 10,
  `max_stock` int(11) DEFAULT 100,
  `caution` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`drugcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dump for `drug_master_configs` (1 rows)
INSERT INTO `drug_master_configs` (`drugcode`, `drugnamethai`, `druggenericname`, `is_nlem`, `is_ham`, `is_lasa`, `is_antibiotic`, `is_cold_chain`, `is_emergency`, `is_herbal`, `min_stock`, `max_stock`, `caution`, `updated_by`, `updated_at`) VALUES
  (1000001, 'พาราเซตามอล 500 มก.', 'Paracetamol 500 mg tab', 1, 0, 0, 0, 0, 0, 0, 100, 1000, 'ห้ามรับประทานเกินวันละ 8 เม็ด', 1, '2026-09-23 22:42:18');

-- ----------------------------------------------------------------------
-- Table structure for `evidence_criterion_mapping`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `evidence_criterion_mapping`;
CREATE TABLE `evidence_criterion_mapping` (
  `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
  `evidence_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`mapping_id`),
  UNIQUE KEY `uq_evidence_criterion` (`evidence_id`,`criterion_id`),
  KEY `criterion_id` (`criterion_id`),
  CONSTRAINT `evidence_criterion_mapping_ibfk_1` FOREIGN KEY (`evidence_id`) REFERENCES `quality_evidence` (`evidence_id`) ON DELETE CASCADE,
  CONSTRAINT `evidence_criterion_mapping_ibfk_2` FOREIGN KEY (`criterion_id`) REFERENCES `quality_criteria` (`criterion_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `facilities`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `facilities`;
CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_code` varchar(10) NOT NULL,
  `facility_name` varchar(255) NOT NULL,
  `district_code` varchar(10) NOT NULL,
  `province_code` varchar(10) NOT NULL,
  `parent_hospital_code` varchar(10) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`facility_id`),
  UNIQUE KEY `facility_code` (`facility_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `facilities` (3 rows)
INSERT INTO `facilities` (`facility_id`, `facility_code`, `facility_name`, `district_code`, `province_code`, `parent_hospital_code`, `is_active`, `created_at`) VALUES
  (1, 1996, 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย', 2106, 21, 10670, 1, '2026-09-23 11:48:59'),
  (2, 5433, 'โรงพยาบาลส่งเสริมสุขภาพตำบลเนินพระ', 2101, 21, 10670, 1, '2026-09-23 11:48:59'),
  (3, 10670, 'โรงพยาบาลระยอง (แม่ข่าย CUP)', 2101, 21, 10670, 1, '2026-09-23 11:48:59');

-- ----------------------------------------------------------------------
-- Table structure for `high_alert_drugs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `high_alert_drugs`;
CREATE TABLE `high_alert_drugs` (
  `ham_id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_code` varchar(24) NOT NULL,
  `generic_name` varchar(255) NOT NULL,
  `risk_category` varchar(100) NOT NULL,
  `precautions` text NOT NULL,
  `double_check_required` tinyint(1) DEFAULT 1,
  `storage_instructions` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ham_id`),
  UNIQUE KEY `drug_code` (`drug_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `high_alert_drugs` (4 rows)
INSERT INTO `high_alert_drugs` (`ham_id`, `drug_code`, `generic_name`, `risk_category`, `precautions`, `double_check_required`, `storage_instructions`, `created_at`) VALUES
  (1, 'HAM001', 'Insulin Regular / NPH / Mixtard', 'Hypoglycemic Agent', 'เสี่ยงต่อภาวะน้ำตาลในเลือดต่ำรุนแรง (Severe Hypoglycemia) ต้องตรวจสอบขนาดยาและหน่วยยูนิตอย่างรอบคอบ', 1, 'เก็บในตู้เย็น 2-8 °C ห้ามแช่แข็ง ติดสติกเกอร์สีส้มสะท้อนแสง', '2026-09-23 11:48:59'),
  (2, 'HAM002', 'Warfarin Sodium', 'Anticoagulant', 'เสี่ยงต่อภาวะเลือดออกรุนแรง (Major Bleeding) ต้องติดตามค่า INR และสอบถามประวัติการใช้ยาตีกันและสมุนไพร', 1, 'จัดเก็บแยกช่องเฉพาะ ติดป้ายเตือนสีแดง และ Double-check ทุกครั้ง', '2026-09-23 11:48:59'),
  (3, 'HAM003', 'Digoxin', 'Inotropic Agent', 'หน้าต่างการรักษาแคบ (Narrow Therapeutic Index) เสี่ยงเกิด Digoxin Toxicity โดยเฉพาะในผู้สูงอายุหรือไตเสื่อม', 1, 'จัดเก็บแยกพร้อมป้ายเตือนระดับความปลอดภัย', '2026-09-23 11:48:59'),
  (4, 'HAM004', 'Morphine / Pethidine Injection', 'Narcotics / Opioids', 'กดการหายใจ (Respiratory Depression) และความดันโลหิตต่ำ ต้องเก็บในตู้เซฟล็อกสองชั้นและมีบัญชีคุมพิเศษ', 1, 'เก็บในตู้เก็บยาเสพติดล็อก 2 ชั้น พร้อมสมุดเบิกจ่ายเฉพาะ', '2026-09-23 11:48:59');

-- ----------------------------------------------------------------------
-- Table structure for `home_medication_reviews`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `home_medication_reviews`;
CREATE TABLE `home_medication_reviews` (
  `hmr_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `patient_pid` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `team_members` varchar(255) DEFAULT NULL,
  `gps_lat` decimal(10,8) DEFAULT NULL,
  `gps_lng` decimal(11,8) DEFAULT NULL,
  `storage_condition` enum('good','moderate','poor') NOT NULL,
  `expired_drugs_found` int(11) DEFAULT 0,
  `duplicate_drugs_found` int(11) DEFAULT 0,
  `traditional_herbs_found` text DEFAULT NULL,
  `adherence_score` decimal(5,2) DEFAULT NULL,
  `summary_problems` text NOT NULL,
  `interventions_taken` text NOT NULL,
  `follow_up_plan` text DEFAULT NULL,
  `photo_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photo_urls`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hmr_id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `home_medication_reviews_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `home_medication_reviews_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `kpi_results`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `kpi_results`;
CREATE TABLE `kpi_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `kpi_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `period_year` int(11) NOT NULL,
  `period_month` int(11) NOT NULL,
  `numerator_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `denominator_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `result_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `evidence_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`result_id`),
  KEY `kpi_id` (`kpi_id`),
  KEY `facility_id` (`facility_id`),
  KEY `evidence_id` (`evidence_id`),
  CONSTRAINT `kpi_results_ibfk_1` FOREIGN KEY (`kpi_id`) REFERENCES `kpis` (`kpi_id`),
  CONSTRAINT `kpi_results_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`),
  CONSTRAINT `kpi_results_ibfk_3` FOREIGN KEY (`evidence_id`) REFERENCES `quality_evidence` (`evidence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `kpis`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `kpis`;
CREATE TABLE `kpis` (
  `kpi_id` int(11) NOT NULL AUTO_INCREMENT,
  `kpi_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `numerator_desc` varchar(255) NOT NULL,
  `denominator_desc` varchar(255) NOT NULL,
  `target_value` decimal(8,2) NOT NULL,
  `unit` varchar(50) DEFAULT '%',
  `frequency` enum('monthly','quarterly','yearly') DEFAULT 'monthly',
  `calculation_source` enum('jhcis_auto','manual_verified','hybrid') NOT NULL,
  `standard_reference` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`kpi_id`),
  UNIQUE KEY `kpi_code` (`kpi_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `kpis` (5 rows)
INSERT INTO `kpis` (`kpi_id`, `kpi_code`, `name`, `description`, `category`, `numerator_desc`, `denominator_desc`, `target_value`, `unit`, `frequency`, `calculation_source`, `standard_reference`, `is_active`, `effective_date`, `expiry_date`) VALUES
  (1, 'KPI-RDU-01', 'ร้อยละการสั่งใช้ยาปฏิชีวนะในโรคติดเชื้อทางเดินหายใจส่วนบน (URI)', NULL, 'RDU', 'จำนวนครั้งที่สั่งยาปฏิชีวนะในผู้ป่วย URI', 'จำนวนผู้ป่วยนอก URI ทั้งหมด', 20.00, '%', 'monthly', 'jhcis_auto', 'มาตรฐานปฐมภูมิ มิติที่ 4', 1, '0000-00-00', NULL),
  (2, 'KPI-RDU-02', 'ร้อยละการสั่งใช้ยาปฏิชีวนะในโรคอุจจาระร่วงเฉียบพลัน (Acute Diarrhea)', NULL, 'RDU', 'จำนวนครั้งที่สั่งยาปฏิชีวนะในผู้ป่วย Diarrhea', 'จำนวนผู้ป่วยนอก Diarrhea ทั้งหมด', 20.00, '%', 'monthly', 'jhcis_auto', 'มาตรฐานปฐมภูมิ มิติที่ 4', 1, '0000-00-00', NULL),
  (3, 'KPI-SAFE-01', 'อัตราการเกิดอุบัติการณ์แพ้ยาซ้ำในหน่วยบริการ (Zero Recurrent ADR)', NULL, 'Safety', 'จำนวนครั้งที่เกิดอาการแพ้ยาซ้ำในระบบ', 'จำนวนผู้ป่วยมีประวัติแพ้ยาที่มารับบริการ', 0.00, '%', 'monthly', 'jhcis_auto', 'Patient Safety Goals', 1, '0000-00-00', NULL),
  (4, 'KPI-SAFE-02', 'ร้อยละการทบทวนยาในผู้ป่วยโรคเรื้อรังที่ใช้ยาตั้งแต่ 5 รายการ (Polypharmacy Review)', NULL, 'Clinical', 'จำนวนผู้ป่วย Polypharmacy ที่ได้รับการทบทวนยา', 'จำนวนผู้ป่วย Polypharmacy NCDs ทั้งหมด', 80.00, '%', 'monthly', 'hybrid', 'มาตรฐานวิชาชีพเภสัชกรรมปฐมภูมิ', 1, '0000-00-00', NULL),
  (5, 'KPI-COLD-01', 'ร้อยละการบันทึกอุณหภูมิตู้เย็นเก็บวัคซีนครบถ้วน (เช้า-บ่าย)', NULL, 'Storage', 'จำนวนรอบที่บันทึกอุณหภูมิครบถ้วน', 'จำนวนรอบที่ต้องบันทึกทั้งหมดในเดือน', 100.00, '%', 'monthly', 'jhcis_auto', 'GSP & Vaccine Safety', 1, '0000-00-00', NULL);

-- ----------------------------------------------------------------------
-- Table structure for `lasa_drugs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `lasa_drugs`;
CREATE TABLE `lasa_drugs` (
  `lasa_id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_code_1` varchar(24) NOT NULL,
  `drug_name_1` varchar(255) NOT NULL,
  `tall_man_1` varchar(255) NOT NULL,
  `drug_code_2` varchar(24) NOT NULL,
  `drug_name_2` varchar(255) NOT NULL,
  `tall_man_2` varchar(255) NOT NULL,
  `lasa_type` enum('look_alike','sound_alike','both') NOT NULL,
  `warning_note` text DEFAULT NULL,
  `storage_separation_required` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`lasa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `lasa_drugs` (4 rows)
INSERT INTO `lasa_drugs` (`lasa_id`, `drug_code_1`, `drug_name_1`, `tall_man_1`, `drug_code_2`, `drug_name_2`, `tall_man_2`, `lasa_type`, `warning_note`, `storage_separation_required`) VALUES
  (1, 'LASA01', 'Prednisone 5 mg tab', 'predniSONE', 'LASA02', 'Prednisolone 5 mg tab', 'prednisoLONE', 'both', 'ชื่อยาและขนาดใกล้เคียงกัน เสี่ยงจ่ายผิดขนาดและการออกฤทธิ์ต่างกัน', 1),
  (2, 'LASA03', 'Hydralazine 25 mg tab', 'hydrALAzine', 'LASA04', 'Hydroxyzine 10 mg tab', 'hydrOXYzine', 'sound_alike', 'ยาลดความดันโลหิตสูงกับยาแก้แพ้ เสี่ยงจ่ายสลับกันทำให้ความดันตกหรือรักษาผิดโรค', 1),
  (3, 'LASA05', 'Metformin 500 mg tab', 'metFORMIN', 'LASA06', 'Methotrexate 2.5 mg tab', 'methoTREXate', 'sound_alike', 'ยาเบาหวานกับยากดภูมิคุ้มกัน หากจ่ายผิดเป็นพิษร้ายแรงต่อชีวิต', 1),
  (4, 'LASA07', 'Amlodipine 5 mg tab', 'amLODIPine', 'LASA08', 'Amitriptyline 10 mg tab', 'amITRIPtyline', 'sound_alike', 'ยาลดความดันกับยาต้านซึมเศร้า เสี่ยงเกิดผลข้างเคียงหัวใจเต้นผิดจังหวะ', 1);

-- ----------------------------------------------------------------------
-- Table structure for `medication_incidents`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `medication_incidents`;
CREATE TABLE `medication_incidents` (
  `incident_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `incident_date` datetime NOT NULL,
  `report_date` datetime NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `incident_stage` enum('prescribing','transcribing','dispensing','administration','monitoring','storage') NOT NULL,
  `severity_category` enum('A','B','C','D','E','F','G','H','I') NOT NULL,
  `is_near_miss` tinyint(1) DEFAULT 0,
  `drugs_involved` text NOT NULL,
  `incident_description` text NOT NULL,
  `immediate_action_taken` text NOT NULL,
  `root_cause_analysis` text DEFAULT NULL,
  `preventive_action` text DEFAULT NULL,
  `responsible_person` varchar(150) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('reported','investigating','rca_completed','closed') DEFAULT 'reported',
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`incident_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `medication_incidents_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `medication_reconciliation_items`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `medication_reconciliation_items`;
CREATE TABLE `medication_reconciliation_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `recon_id` int(11) NOT NULL,
  `prior_drug_name` varchar(255) NOT NULL,
  `prior_dose` varchar(100) DEFAULT NULL,
  `current_drug_name` varchar(255) DEFAULT NULL,
  `current_dose` varchar(100) DEFAULT NULL,
  `discrepancy_type` enum('no_discrepancy','intended_addition','intended_discontinuation','intended_dosage_change','unintended_omission','unintended_commission','unintended_dosage_difference','unintended_duplication') NOT NULL,
  `clinical_decision` enum('continue','stop','modify','substitute','refer_back') NOT NULL,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `recon_id` (`recon_id`),
  CONSTRAINT `medication_reconciliation_items_ibfk_1` FOREIGN KEY (`recon_id`) REFERENCES `medication_reconciliations` (`recon_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `medication_reconciliations`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `medication_reconciliations`;
CREATE TABLE `medication_reconciliations` (
  `recon_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `patient_pid` int(11) NOT NULL,
  `recon_date` date NOT NULL,
  `transition_type` enum('hospital_discharge','hospital_referral','home_visit','clinic_transition') NOT NULL,
  `source_facility` varchar(255) NOT NULL,
  `practitioner_id` int(11) NOT NULL,
  `status` enum('draft','in_progress','completed') DEFAULT 'draft',
  `clinical_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recon_id`),
  KEY `practitioner_id` (`practitioner_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `medication_reconciliations_ibfk_1` FOREIGN KEY (`practitioner_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `medication_reconciliations_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `medication_review_problems`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `medication_review_problems`;
CREATE TABLE `medication_review_problems` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `review_id` int(11) NOT NULL,
  `drug_code` varchar(24) DEFAULT NULL,
  `drug_name` varchar(255) NOT NULL,
  `drp_category` enum('unnecessary_medication','need_additional_therapy','ineffective_medication','dose_too_low','dose_too_high','adverse_drug_reaction','drug_interaction','non_adherence','duplicate_therapy','administration_problem','other') NOT NULL,
  `problem_description` text NOT NULL,
  `evidence_detail` text DEFAULT NULL,
  `assessment` text NOT NULL,
  `recommendation` text NOT NULL,
  `prescriber_response` enum('accepted_fully','accepted_partially','rejected_with_reason','pending') DEFAULT 'pending',
  `prescriber_comment` text DEFAULT NULL,
  `outcome` enum('resolved','partially_resolved','unresolved','unknown') DEFAULT 'unknown',
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`problem_id`),
  KEY `review_id` (`review_id`),
  CONSTRAINT `medication_review_problems_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `medication_reviews` (`review_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `medication_reviews`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `medication_reviews`;
CREATE TABLE `medication_reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `patient_pid` int(11) NOT NULL,
  `patient_cid_hash` char(64) NOT NULL,
  `review_date` date NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_type` enum('routine','polypharmacy','high_risk','referral','post_discharge') NOT NULL,
  `total_medications` int(11) DEFAULT 0,
  `clinical_summary` text DEFAULT NULL,
  `status` enum('draft','completed','communicated','resolved') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `facility_id` (`facility_id`),
  KEY `patient_pid` (`patient_pid`),
  KEY `review_date` (`review_date`),
  CONSTRAINT `medication_reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `medication_reviews_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `pcu_allergy_cards`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pcu_allergy_cards`;
CREATE TABLE `pcu_allergy_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_no` varchar(50) NOT NULL,
  `pid` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `hospital_code` varchar(10) NOT NULL DEFAULT '01996',
  `hospital_name` varchar(255) NOT NULL DEFAULT 'รพ.สต.บ้านดอกกราย เครือข่าย รพ.ปลวกแดง',
  `issuer_name` varchar(150) NOT NULL,
  `allergies_snapshot` text NOT NULL,
  `print_count` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_no` (`card_no`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Table structure for `pcu_anticonvulsant_registry`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pcu_anticonvulsant_registry`;
CREATE TABLE `pcu_anticonvulsant_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `drug_code` varchar(50) NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `daily_dose` varchar(100) NOT NULL DEFAULT '1x1 hs',
  `indication` varchar(150) NOT NULL DEFAULT 'Generalized Tonic-Clonic Seizure',
  `seizure_control` enum('free_gt6m','occasional','uncontrolled') NOT NULL DEFAULT 'free_gt6m',
  `last_seizure_date` date DEFAULT NULL,
  `hla_b1502_status` enum('negative','positive','not_tested') NOT NULL DEFAULT 'not_tested',
  `latest_tdm_level` decimal(5,2) DEFAULT NULL,
  `tdm_date` date DEFAULT NULL,
  `adherence_score` enum('high','medium','low') NOT NULL DEFAULT 'high',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dump for `pcu_anticonvulsant_registry` (8 rows)
INSERT INTO `pcu_anticonvulsant_registry` (`id`, `pid`, `drug_code`, `drug_name`, `daily_dose`, `indication`, `seizure_control`, `last_seizure_date`, `hla_b1502_status`, `latest_tdm_level`, `tdm_date`, `adherence_score`, `notes`, `created_at`, `updated_at`) VALUES
  (9, 3679, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2013-04-15', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (10, 5425, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2007-01-26', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (11, 5428, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2015-03-23', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (12, 5468, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2012-09-02', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (13, 6841, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2013-02-22', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (14, 7971, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2013-07-31', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (15, 8404, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2014-08-22', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (16, 8523, '1PNB', 'PHENOBARBITAL TAB 30 MG.', '1x1 hs', 'Epilepsy/Seizure Disorder', 'free_gt6m', '2014-12-30', 'not_tested', NULL, NULL, 'high', 'ดึงข้อมูลประวัติการรับยากันชักจาก JHCIS visitdrug', '2026-09-23 23:36:10', '2026-09-23 23:36:10');

-- ----------------------------------------------------------------------
-- Table structure for `pcu_ckd_registry`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pcu_ckd_registry`;
CREATE TABLE `pcu_ckd_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `ckd_stage` varchar(10) NOT NULL DEFAULT 'Stage 3a',
  `latest_egfr` decimal(5,2) NOT NULL DEFAULT 52.00,
  `latest_cr` decimal(4,2) NOT NULL DEFAULT 1.30,
  `lab_date` date DEFAULT NULL,
  `dialysis_status` enum('none','hemodialysis','peritoneal','transplant') NOT NULL DEFAULT 'none',
  `nephrotoxic_alerts` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dump for `pcu_ckd_registry` (18 rows)
INSERT INTO `pcu_ckd_registry` (`id`, `pid`, `ckd_stage`, `latest_egfr`, `latest_cr`, `lab_date`, `dialysis_status`, `nephrotoxic_alerts`, `notes`, `created_at`, `updated_at`) VALUES
  (32, 3494, 'Stage 5', 8.50, 5.60, '2013-03-08', 'hemodialysis', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (33, 3796, 'Stage 3a', 52.00, 1.30, '2013-03-08', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (34, 5062, 'Stage 1', 92.00, 0.80, '2022-06-19', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (35, 5109, 'Stage 3a', 52.00, 1.30, '2011-03-08', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (36, 5138, 'Stage 3a', 52.00, 1.30, '2018-10-01', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (37, 5486, 'Stage 3a', 54.00, 1.25, '2022-06-19', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (38, 5608, 'Stage 3a', 52.00, 1.30, '2013-03-11', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (39, 5719, 'Stage 3a', 54.00, 1.25, '2022-06-18', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (40, 5821, 'Stage 3a', 52.00, 1.30, '2015-08-01', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (41, 6142, 'Stage 5', 12.00, 4.20, '2022-06-19', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (42, 6580, 'Stage 3a', 52.00, 1.30, '2022-05-30', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (43, 6759, 'Stage 3a', 52.00, 1.30, '2017-10-01', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (44, 7021, 'Stage 3a', 52.00, 1.30, '2025-09-30', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (45, 7036, 'Stage 4', 24.00, 2.40, '2022-06-19', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (46, 7431, 'Stage 3a', 52.00, 1.30, '2020-02-04', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (47, 9282, 'Stage 3a', 52.00, 1.30, '2026-09-23', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (48, 9941, 'Stage 3a', 52.00, 1.30, '2019-05-29', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10'),
  (49, 10492, 'Stage 3a', 52.00, 1.30, '2021-12-06', 'none', 'เฝ้าระวังยาขับทางไต (NSAIDs/Metformin)', 'ดึงข้อมูลประวัติวินิจฉัยโรคไตเรื้อรังจาก JHCIS personchronic', '2026-09-23 23:36:10', '2026-09-23 23:36:10');

-- ----------------------------------------------------------------------
-- Table structure for `pcu_g6pd_registry`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pcu_g6pd_registry`;
CREATE TABLE `pcu_g6pd_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `screening_date` date NOT NULL,
  `enzyme_activity` varchar(50) NOT NULL DEFAULT 'Deficient (< 10%)',
  `who_class` varchar(50) NOT NULL DEFAULT 'Class II (Severe deficiency)',
  `hemolysis_history` varchar(100) DEFAULT 'เคยมีภาวะซีดเฉียบพลัน/ปัสสาวะสีโค้กหลังทานยาซัลฟา',
  `high_risk_drugs` text DEFAULT NULL,
  `g6pd_card_status` enum('not_issued','issued','pending') DEFAULT 'issued',
  `g6pd_card_no` varchar(50) DEFAULT NULL,
  `card_issue_date` date DEFAULT NULL,
  `assessing_pharmacist` varchar(100) DEFAULT 'ภญ.กานดา บริบาลเภสัช',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Table structure for `pcu_warfarin_registry`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pcu_warfarin_registry`;
CREATE TABLE `pcu_warfarin_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `indication` varchar(150) NOT NULL DEFAULT 'Atrial Fibrillation (AF)',
  `target_inr_min` decimal(3,1) NOT NULL DEFAULT 2.0,
  `target_inr_max` decimal(3,1) NOT NULL DEFAULT 3.0,
  `current_weekly_dose` decimal(5,2) NOT NULL DEFAULT 15.00,
  `latest_inr` decimal(4,2) DEFAULT NULL,
  `latest_inr_date` date DEFAULT NULL,
  `inr_status` enum('in_range','below_target','above_target','critical') DEFAULT 'in_range',
  `bleeding_history` text DEFAULT NULL,
  `bleeding_risk_score` varchar(50) DEFAULT 'Low (HAS-BLED 1)',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------
-- Table structure for `permissions`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_code` varchar(80) NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_code` (`permission_code`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `permissions` (32 rows)
INSERT INTO `permissions` (`permission_id`, `permission_code`, `module_name`, `description`) VALUES
  (1, 'patient.view', 'patient', 'ดูข้อมูลเวชระเบียนผู้ป่วย'),
  (2, 'patient.cid.unmask', 'patient', 'ปลดล็อกดูเลขบัตร ปชช. เต็ม'),
  (3, 'medication.view', 'patient', 'ดูประวัติการใช้ยา'),
  (4, 'allergy.view', 'allergy', 'ดูประวัติแพ้ยา'),
  (5, 'allergy.manage', 'allergy', 'บันทึกและปรับปรุงประวัติแพ้ยา'),
  (6, 'safety.risk.view', 'safety', 'ดูการแจ้งเตือนความเสี่ยงยา'),
  (7, 'safety.rule.configure', 'safety', 'แก้ไขกฎเกณฑ์คัดกรองความปลอดภัย'),
  (8, 'safety.ham_lasa.manage', 'safety', 'จัดการบัญชี HAM และ LASA'),
  (9, 'safety.emergency.inspect', 'safety', 'ตรวจสอบชุดยาฉุกเฉิน'),
  (10, 'review.create', 'review', 'สร้างการทบทวนวรรณกรรมยาและบันทึก DRP'),
  (11, 'review.approve', 'review', 'รับรองผลการทบทวนยา'),
  (12, 'reconciliation.manage', 'reconciliation', 'จัดทำ Medication Reconciliation'),
  (13, 'pharm_care.soap', 'pharm_care', 'บันทึก SOAP Note และแผนการบริบาล'),
  (14, 'hmr.record', 'hmr', 'บันทึกการเยี่ยมบ้านด้านยา'),
  (15, 'counseling.record', 'counseling', 'บันทึกการให้คำปรึกษาการใช้ยา'),
  (16, 'telepharmacy.conduct', 'telepharmacy', 'ให้บริการและบันทึกผล Telepharmacy'),
  (17, 'stock.view', 'inventory', 'ดูยอดสต็อกยาและวันหมดอายุ'),
  (18, 'stock.adjust', 'inventory', 'ปรับปรุงยอดสต็อกและตัดจ่ายยา'),
  (19, 'requisition.create', 'inventory', 'สร้างใบเบิกยา รพ.สต.'),
  (20, 'requisition.approve', 'inventory', 'อนุมัติใบเบิกยา'),
  (21, 'coldchain.log', 'coldchain', 'บันทึกอุณหภูมิตู้เย็นยา'),
  (22, 'coldchain.excursion', 'coldchain', 'บันทึกมาตรการแก้ไขกรณีอุณหภูมิหลุดเกณฑ์'),
  (23, 'incident.report', 'incident', 'รายงานอุบัติการณ์ความคลาดเคลื่อนทางยา'),
  (24, 'incident.rca', 'incident', 'วิเคราะห์สาเหตุและปิดเคส RCA'),
  (25, 'quality.assess', 'quality', 'ประเมินตนเองตามมาตรฐานปฐมภูมิ'),
  (26, 'evidence.upload', 'quality', 'อัปโหลดหลักฐานเชิงประจักษ์'),
  (27, 'evidence.review', 'quality', 'ตรวจสอบและอนุมัติหลักฐาน'),
  (28, 'kpi.manage', 'quality', 'จัดการสูตรและบันทึกผล KPI'),
  (29, 'report.export', 'report', 'ส่งออกรายงาน Excel/PDF'),
  (30, 'user.manage', 'system', 'จัดการผู้ใช้และสิทธิ์'),
  (31, 'settings.manage', 'system', 'ตั้งค่าระบบ'),
  (32, 'audit.view', 'system', 'เปิดดู Audit Log');

-- ----------------------------------------------------------------------
-- Table structure for `pharmaceutical_cares`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `pharmaceutical_cares`;
CREATE TABLE `pharmaceutical_cares` (
  `care_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `patient_pid` int(11) NOT NULL,
  `care_date` date NOT NULL,
  `pharmacist_id` int(11) NOT NULL,
  `subjective` text DEFAULT NULL,
  `objective` text DEFAULT NULL,
  `assessment` text NOT NULL,
  `plan` text NOT NULL,
  `outcome` enum('improved','stable','worsened','unresolved') DEFAULT 'stable',
  `next_follow_up` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`care_id`),
  KEY `pharmacist_id` (`pharmacist_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `pharmaceutical_cares_ibfk_1` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `pharmaceutical_cares_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `quality_assessments`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `quality_assessments`;
CREATE TABLE `quality_assessments` (
  `assessment_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `assessment_year` int(11) NOT NULL,
  `status` enum('not_assessed','not_started','in_progress','evidence_missing','ready','passed_internal_review','need_improvement') DEFAULT 'not_assessed',
  `score_achieved` decimal(5,2) DEFAULT 0.00,
  `gap_identified` text DEFAULT NULL,
  `corrective_action_plan` text DEFAULT NULL,
  `assessed_by` int(11) NOT NULL,
  `assessed_at` datetime NOT NULL,
  `target_completion_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assessment_id`),
  KEY `facility_id` (`facility_id`),
  KEY `assessed_by` (`assessed_by`),
  CONSTRAINT `quality_assessments_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`),
  CONSTRAINT `quality_assessments_ibfk_2` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `quality_categories`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `quality_categories`;
CREATE TABLE `quality_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `standard_id` int(11) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `ordering` int(11) DEFAULT 1,
  PRIMARY KEY (`category_id`),
  KEY `standard_id` (`standard_id`),
  CONSTRAINT `quality_categories_ibfk_1` FOREIGN KEY (`standard_id`) REFERENCES `quality_standards` (`standard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `quality_categories` (5 rows)
INSERT INTO `quality_categories` (`category_id`, `standard_id`, `category_code`, `category_name`, `ordering`) VALUES
  (1, 1, 'DIM-1', 'มิติที่ 1: การนำองค์กรและการบริหารจัดการ (Governance & Leadership)', 1),
  (2, 1, 'DIM-2', 'มิติที่ 2: ประชากรเป้าหมาย ชุมชน และผู้มีส่วนได้ส่วนเสีย (Target Population & Community)', 2),
  (3, 1, 'DIM-3', 'มิติที่ 3: ด้านบุคลากร (Workforce & Competency)', 3),
  (4, 1, 'DIM-4', 'มิติที่ 4: ระบบบริการสุขภาพและระบบยา (Service Delivery & Medication Safety)', 4),
  (5, 1, 'DIM-5', 'มิติที่ 5: ผลลัพธ์และตัวชี้วัดคุณภาพ (Clinical Outcomes & KPIs)', 5);

-- ----------------------------------------------------------------------
-- Table structure for `quality_criteria`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `quality_criteria`;
CREATE TABLE `quality_criteria` (
  `criterion_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `criterion_code` varchar(30) NOT NULL,
  `criterion_name` varchar(255) NOT NULL,
  `subcriterion` varchar(100) DEFAULT NULL,
  `requirement` text NOT NULL,
  `guidance` text DEFAULT NULL,
  `evidence_required` text NOT NULL,
  `weight` decimal(5,2) DEFAULT 1.00,
  `target` decimal(5,2) DEFAULT 100.00,
  `responsible_role` varchar(50) DEFAULT 'PCU Pharmacist',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`criterion_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `quality_criteria_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `quality_categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `quality_criteria` (10 rows)
INSERT INTO `quality_criteria` (`criterion_id`, `category_id`, `criterion_code`, `criterion_name`, `subcriterion`, `requirement`, `guidance`, `evidence_required`, `weight`, `target`, `responsible_role`, `is_active`) VALUES
  (1, 1, '1.2.1', 'คณะกรรมการเภสัชกรรมและการบำบัด (PTC)', 'การกำกับนโยบายยา', 'มีคณะกรรมการ PTC ระดับปฐมภูมิหรือมีตัวแทนใน PTC เครือข่าย CUP เพื่อกำหนดนโยบายระบบยาและ RDU', 'ประชุมอย่างน้อยปีละ 2 ครั้ง', 'คำสั่งแต่งตั้งคณะกรรมการ, รายงานการประชุม PTC', 1.50, 100.00, 'DISTRICT_PHARM', 1),
  (2, 2, '2.2.1', 'การบริบาลเภสัชกรรมที่บ้าน (HMR)', 'การดูแลกลุ่มเปราะบาง', 'มีระบบบริบาลเภสัชกรรมที่บ้านร่วมกับทีม 3 หมอ ค้นหาปัญหา DRP ในผู้ป่วยติดบ้าน/ติดเตียง และยาเหลือใช้', 'บันทึก HMR ครบถ้วนพร้อมภาพถ่าย', 'รายงานผลการเยี่ยมบ้าน HMR, ทะเบียนส่งคืนยาเหลือใช้', 2.00, 80.00, 'PCU_PHARM', 1),
  (3, 3, '3.2.1', 'สมรรถนะบุคลากรด้านยา', 'การประเมินความรู้และทักษะ', 'บุคลากรผู้ปฏิบัติงานด้านยาได้รับการอบรมเรื่อง RDU, High Alert Drugs และผ่านการประเมินสมรรถนะประจำปี', 'เกณฑ์ผ่าน ≥ 80%', 'ใบประกาศนียบัตรอบรม, แบบประเมิน Competency', 1.00, 100.00, 'FACILITY_ADMIN', 1),
  (4, 4, '4.3.1', 'การจัดการคลังยาตามหลัก FEFO', 'การควบคุมสต็อกและวันหมดอายุ', 'คลังยามีการควบคุมอุณหภูมิ มี Stock Card และจัดจ่ายยาตามหลัก First Expired, First Out (FEFO) 100%', 'ตรวจสอบยอดคงคลังตรงกับระบบจริง', 'รายงาน Stock Card อิเล็กทรอนิกส์, บันทึกตรวจนับคลังยา', 2.50, 100.00, 'PHARM_TECH', 1),
  (5, 4, '4.3.2', 'การควบคุมลูกโซ่ความเย็น (Cold Chain)', 'การเก็บรักษาวัคซีนและยาชีววัตถุ', 'มีระบบบันทึกอุณหภูมิตู้เย็น 2-8 °C วันละ 2 ครั้ง (เช้า-บ่าย) และมีแนวทางจัดการเมื่ออุณหภูมิหลุดเกณฑ์', 'อุณหภูมิต้องอยู่ระหว่าง 2.0 - 8.0 °C', 'กราฟ Temperature Log ย้อนหลัง, บันทึกเหตุการณ์ Excursion Action', 2.50, 100.00, 'PHARM_TECH', 1),
  (6, 4, '4.4.1', 'ระบบคัดกรองและป้องกันการแพ้ยาซ้ำ', 'Patient Safety Goals', 'มีระบบเฝ้าระวัง แจ้งเตือนประวัติแพ้ยาตรงกันและแพ้ข้ามกลุ่ม (Cross-sensitivity) อัตราเกิดแพ้ยาซ้ำเป็นศูนย์', 'Zero Recurrent ADR', 'Audit Log การแจ้งเตือนแพ้ยา, รายงานอุบัติการณ์ ADR', 3.00, 100.00, 'PCU_PHARM', 1),
  (7, 4, '4.4.2', 'การจัดการยากลุ่มเสี่ยงสูง (HAM) และ LASA', 'การป้องกันข้อผิดพลาดทางยา', 'มีบัญชีรายชื่อ HAM/LASA แยกเก็บยา ติดป้ายเตือน และใช้ Tall Man Lettering ในระบบสั่งจ่าย', 'ติดสัญลักษณ์เตือน 100%', 'ภาพถ่ายจุดเก็บยาแยกต่างหาก, รายการ Tall Man ในระบบ', 2.00, 100.00, 'PCU_PHARM', 1),
  (8, 4, '4.4.3', 'การประสานรายการยา (Med Reconciliation)', 'รอยต่อการส่งต่อผู้ป่วย', 'มีกระบวนการทำ Med Reconciliation ในผู้ป่วยส่งต่อหรือส่งกลับจาก รพ.แม่ข่าย เพื่อป้องกันยาตกหล่น/ซ้ำซ้อน', 'ทำ Med Reconcile ≥ 80%', 'Reconciled Medication List, บันทึกแก้ไข Discrepancy', 2.00, 80.00, 'PCU_PHARM', 1),
  (9, 5, '5.1.1', 'การใช้ยาปฏิชีวนะอย่างสมเหตุผล (RDU URI)', 'ตัวชี้วัด RDU', 'อัตราการสั่งใช้ยาปฏิชีวนะในโรคติดเชื้อทางเดินหายใจส่วนบน (URI) ไม่เกินร้อยละ 20', 'เป้าหมาย ≤ 20.0%', 'รายงานตัวชี้วัด RDU จาก JHCIS API Dashboard', 3.00, 20.00, 'PCU_PHARM', 1),
  (10, 5, '5.1.2', 'การใช้ยาปฏิชีวนะในอุจจาระร่วงเฉียบพลัน (RDU Diarrhea)', 'ตัวชี้วัด RDU', 'อัตราการสั่งใช้ยาปฏิชีวนะในโรคอุจจาระร่วงเฉียบพลัน (Acute Diarrhea) ไม่เกินร้อยละ 20', 'เป้าหมาย ≤ 20.0%', 'รายงานตัวชี้วัด RDU จาก JHCIS API Dashboard', 3.00, 20.00, 'PCU_PHARM', 1);

-- ----------------------------------------------------------------------
-- Table structure for `quality_evidence`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `quality_evidence`;
CREATE TABLE `quality_evidence` (
  `evidence_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `evidence_type` enum('pdf','word','excel','image','url','meeting_record','policy','sop','report','photo','training_record') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size_kb` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`evidence_id`),
  KEY `facility_id` (`facility_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `quality_evidence_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`),
  CONSTRAINT `quality_evidence_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `quality_standards`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `quality_standards`;
CREATE TABLE `quality_standards` (
  `standard_id` int(11) NOT NULL AUTO_INCREMENT,
  `standard_year` int(11) NOT NULL,
  `standard_version` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`standard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `quality_standards` (1 rows)
INSERT INTO `quality_standards` (`standard_id`, `standard_year`, `standard_version`, `title`, `effective_date`, `expiry_date`, `is_active`, `created_at`) VALUES
  (1, 2568, 1.0, 'มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570 (ตาม พ.ร.บ. ระบบสุขภาพปฐมภูมิ พ.ศ. 2562)', '2568-01-01', '2570-12-31', 1, '2026-09-23 11:48:59');

-- ----------------------------------------------------------------------
-- Table structure for `risk_rules`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `risk_rules`;
CREATE TABLE `risk_rules` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(50) NOT NULL,
  `category` enum('allergy','polypharmacy','duplicate','drug_disease','renal','elderly','pediatric','pregnancy','ham','lasa','interaction') NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `severity` enum('info','review','high','critical') NOT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trigger_condition`)),
  `alert_message` text NOT NULL,
  `clinical_guideline` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `role_permissions`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `role_permissions` (88 rows)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
  (1, 1),
  (1, 2),
  (1, 3),
  (1, 4),
  (1, 5),
  (1, 6),
  (1, 7),
  (1, 8),
  (1, 9),
  (1, 10),
  (1, 11),
  (1, 12),
  (1, 13),
  (1, 14),
  (1, 15),
  (1, 16),
  (1, 17),
  (1, 18),
  (1, 19),
  (1, 20),
  (1, 21),
  (1, 22),
  (1, 23),
  (1, 24),
  (1, 25),
  (1, 26),
  (1, 27),
  (1, 28),
  (1, 29),
  (1, 30),
  (1, 31),
  (1, 32),
  (4, 1),
  (4, 2),
  (4, 3),
  (4, 4),
  (4, 5),
  (4, 6),
  (4, 8),
  (4, 9),
  (4, 10),
  (4, 11),
  (4, 12),
  (4, 13),
  (4, 14),
  (4, 15),
  (4, 16),
  (4, 17),
  (4, 18),
  (4, 19),
  (4, 21),
  (4, 22),
  (4, 23),
  (4, 24),
  (4, 25),
  (4, 26),
  (4, 27),
  (4, 28),
  (4, 29),
  (4, 32),
  (5, 1),
  (5, 3),
  (5, 4),
  (5, 6),
  (5, 9),
  (5, 17),
  (5, 18),
  (5, 19),
  (5, 21),
  (5, 22),
  (5, 23),
  (5, 26),
  (5, 29),
  (6, 1),
  (6, 2),
  (6, 3),
  (6, 4),
  (6, 5),
  (6, 6),
  (6, 9),
  (6, 12),
  (6, 14),
  (6, 15),
  (6, 21),
  (6, 22),
  (6, 23),
  (6, 26),
  (6, 29);

-- ----------------------------------------------------------------------
-- Table structure for `roles`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `access_level` tinyint(4) NOT NULL DEFAULT 3,
  `level_name` varchar(100) DEFAULT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `roles` (12 rows)
INSERT INTO `roles` (`role_id`, `role_name`, `access_level`, `level_name`, `display_name`, `description`) VALUES
  (1, 'SUPER_ADMIN', 1, 'ระดับ 1: บริหารระบบและแม่ข่าย CUP', 'ผู้ดูแลระบบสูงสุด', 'สิทธิ์เต็มรูปแบบทั่วทั้งเครือข่าย'),
  (2, 'DISTRICT_PHARM', 1, 'ระดับ 1: บริหารระบบและแม่ข่าย CUP', 'เภสัชกรระดับอำเภอ', 'กำกับดูแลระบบยาและ RDU ภาพรวม CUP'),
  (3, 'HOSPITAL_PHARM', 2, 'ระดับ 2: บริบาลเภสัชกรรมปฐมภูมิ', 'เภสัชกร รพ.แม่ข่าย', 'ประสานงานส่งต่อผู้ป่วยและ Med Reconciliation'),
  (4, 'PCU_PHARM', 2, 'ระดับ 2: บริบาลเภสัชกรรมปฐมภูมิ', 'เภสัชกรปฐมภูมิ', 'บริบาลเภสัชกรรม คัดกรองความปลอดภัย ทบทวนยา และดูแลคลังยา รพ.สต.'),
  (5, 'PHARM_TECH', 3, 'ระดับ 3: ปฏิบัติการยาและเวชปฏิบัติ', 'เจ้าพนักงานเภสัชกรรม', 'จัดยา จัดการสต็อก FEFO ตรวจสอบอุณหภูมิตู้เย็น'),
  (6, 'NURSE', 3, 'ระดับ 3: ปฏิบัติการยาและเวชปฏิบัติ', 'พยาบาลวิชาชีพ', 'คัดกรองผู้ป่วย ฉีดวัคซีน บริหารยาฉุกเฉิน รายงานอุบัติการณ์'),
  (7, 'PUBLIC_HEALTH', 4, 'ระดับ 4: ส่งเสริมสุขภาพชุมชน (ทีม 3 หมอ)', 'นักวิชาการสาธารณสุข', 'ร่วมทีม 3 หมอเยี่ยมบ้าน (HMR) และงานส่งเสริมสุขภาพ'),
  (8, 'FACILITY_ADMIN', 4, 'ระดับ 4: ผู้ดูแลประจำหน่วยบริการ รพ.สต.', 'ผู้ดูแลระบบ รพ.สต.', 'จัดการผู้ใช้และตั้งค่าระบบใน รพ.สต.'),
  (9, 'QUALITY_OFFICER', 5, 'ระดับ 5: ประกันคุณภาพและสถิติ', 'ผู้รับผิดชอบงานคุณภาพ', 'ประเมินตนเองตามมาตรฐาน รวบรวมหลักฐานเชิงประจักษ์'),
  (10, 'DATA_OFFICER', 5, 'ระดับ 5: ประกันคุณภาพและสถิติ', 'เจ้าหน้าที่ข้อมูลและสถิติ', 'จัดการตัวชี้วัด KPI และส่งออกรายงาน'),
  (11, 'VIEWER', 5, 'ระดับ 5: นิเทศงานและผู้ตรวจเยี่ยม', 'ผู้ตรวจเยี่ยม / นิเทศงาน', 'ดูรายงานและแดชบอร์ดภาพรวมแบบอ่านอย่างเดียว'),
  (12, 'AUDITOR', 5, 'ระดับ 5: ผู้ตรวจสอบอิสระภายนอก', 'ผู้ตรวจสอบภายนอก', 'ตรวจสอบ Audit log และหลักฐานตามมาตรฐาน');

-- ----------------------------------------------------------------------
-- Table structure for `sop_documents`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `sop_documents`;
CREATE TABLE `sop_documents` (
  `sop_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `sop_code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `effective_date` date NOT NULL,
  `review_date` date NOT NULL,
  `owner_role` varchar(100) NOT NULL,
  `approver_name` varchar(150) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `status` enum('draft','active','under_review','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sop_id`),
  UNIQUE KEY `sop_code` (`sop_code`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `sop_documents_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `stock_locations`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `stock_locations`;
CREATE TABLE `stock_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `location_type` enum('main_store','dispensary','refrigerator','emergency_kit','sub_station') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`location_id`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `stock_locations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `stock_locations` (4 rows)
INSERT INTO `stock_locations` (`location_id`, `facility_id`, `location_name`, `location_type`, `is_active`) VALUES
  (1, 1, 'ห้องจ่ายยา รพ.สต.บ้านหนองบัว', 'dispensary', 1),
  (2, 1, 'คลังยาหลัก รพ.สต.', 'main_store', 1),
  (3, 1, 'ตู้เย็นเก็บวัคซีนและยาชีววัตถุ 1', 'refrigerator', 1),
  (4, 1, 'กล่องยาช่วยชีวิตฉุกเฉิน (CPR Kit)', 'emergency_kit', 1);

-- ----------------------------------------------------------------------
-- Table structure for `stock_lots`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `stock_lots`;
CREATE TABLE `stock_lots` (
  `lot_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `drug_code` varchar(24) NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `lot_number` varchar(50) NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity_received` int(11) NOT NULL DEFAULT 0,
  `quantity_balance` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `manufacturer` varchar(150) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `status` enum('active','quarantine','expired','exhausted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lot_id`),
  KEY `location_id` (`location_id`),
  KEY `facility_id` (`facility_id`),
  KEY `drug_code` (`drug_code`),
  KEY `expiry_date` (`expiry_date`),
  CONSTRAINT `stock_lots_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `stock_locations` (`location_id`),
  CONSTRAINT `stock_lots_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `stock_movements`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
  `movement_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `lot_id` int(11) NOT NULL,
  `movement_type` enum('receive','dispense','adjust_in','adjust_out','transfer','return','destroy') NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `quantity_changed` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`movement_id`),
  KEY `lot_id` (`lot_id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`lot_id`) REFERENCES `stock_lots` (`lot_id`),
  CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Table structure for `system_settings`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `data_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `is_smart_detected` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `system_settings` (46 rows)
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `data_type`, `description`, `is_smart_detected`, `updated_at`, `updated_by`) VALUES
  ('app_name', 'PCU Smart Pharmacy Hub', 'general', 'string', NULL, 0, '2026-09-23 23:26:15', 1),
  ('audit_trail_logging', 1, 'automation', 'boolean', 'เก็บบันทึกประวัติการเปลี่ยนแปลงข้อมูลละเอียด (Audit Log)', 0, '2026-09-23 17:24:37', NULL),
  ('auto_fefo_deduct', 1, 'automation', 'boolean', 'ตัดจ่ายยาตามล็อตที่หมดอายุก่อนอัตโนมัติ (Smart FEFO)', 0, '2026-09-23 17:24:37', NULL),
  ('cds_interaction_alert_level', 'moderate', 'safety', 'string', 'ระดับการแจ้งเตือน Drug Interaction ขั้นต่ำ (major, moderate, minor)', 0, '2026-09-23 17:24:37', NULL),
  ('check_g6pd_enabled', 1, 'safety', 'boolean', 'เปิดใช้การตรวจสอบภาวะพร่องเอนไซม์ G6PD อัตโนมัติ', 0, '2026-09-23 17:24:37', NULL),
  ('coldchain_temp_max', 8.0, 'safety', 'number', 'อุณหภูมิสูงสุดที่ปลอดภัยสำหรับตู้เย็นเก็บยา (°C)', 0, '2026-09-23 17:24:37', NULL),
  ('coldchain_temp_min', 2.0, 'safety', 'number', 'อุณหภูมิต่ำสุดที่ปลอดภัยสำหรับตู้เย็นเก็บยา (°C)', 0, '2026-09-23 17:24:37', NULL),
  ('csrf_token', '', 'general', 'string', NULL, 0, '2026-09-23 23:26:15', 1),
  ('director_name', 'นายสุขสันต์ มุ่งบริการ', 'facility', 'string', 'ชื่อผู้อำนวยการ รพ.สต. / หัวหน้าหน่วยบริการ', 0, '2026-09-23 22:15:25', 2),
  ('director_position', 'ผู้อำนวยการโรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย', 'facility', 'string', 'ตำแหน่งหัวหน้าหน่วยบริการ', 0, '2026-09-23 22:15:25', 2),
  ('dispensary_reorder_pct', 20, 'inventory', 'number', 'จุดสั่งเติมยาคลังยานอก (% ของคลังยาใน)', 0, '2026-09-23 17:24:37', NULL),
  ('district_code', 6, 'facility', 'string', 'รหัสอำเภอ (ปลวกแดง)', 1, '2026-09-23 23:05:13', 2),
  ('egfr_alert_threshold', 60, 'safety', 'number', 'เกณฑ์ eGFR เตือนปรับขนาดยาผู้ป่วยไตเสื่อม (ml/min/1.73m2)', 0, '2026-09-23 17:24:37', NULL),
  ('egfr_critical_threshold', 30, 'safety', 'number', 'เกณฑ์ eGFR วิกฤต ห้ามใช้ยาเสี่ยงต่อไต (ml/min/1.73m2)', 0, '2026-09-23 17:24:37', NULL),
  ('facility_address', 51, 'facility', 'string', NULL, 1, '2026-09-23 17:35:14', NULL),
  ('facility_code', 01081, 'facility', 'string', 'รหัสสถานพยาบาล (HOSPCODE 5 หลัก)', 1, '2026-09-23 23:26:15', 1),
  ('facility_code_9', 199600, 'facility', 'string', 'รหัสสถานพยาบาลใหม่ 9 หลัก', 1, '2026-09-23 23:05:13', 2),
  ('facility_name', 'รพ.สต. มาบยางพร', 'facility', 'string', 'ชื่อหน่วยบริการสาธารณสุข', 1, '2026-09-23 23:26:15', 1),
  ('facility_phone', '038-027123', 'facility', 'string', 'เบอร์โทรศัพท์ติดต่อ รพ.สต.', 0, '2026-09-23 22:15:25', 2),
  ('fefo_alert_days_1', 30, 'inventory', 'number', 'เกณฑ์เตือนยาใกล้หมดอายุระดับวิกฤต (วัน)', 0, '2026-09-23 17:24:37', NULL),
  ('fefo_alert_days_2', 90, 'inventory', 'number', 'เกณฑ์เตือนยาใกล้หมดอายุระดับเฝ้าระวัง (วัน)', 0, '2026-09-23 17:24:37', NULL),
  ('fefo_alert_days_3', 180, 'inventory', 'number', 'เกณฑ์เตือนยาใกล้หมดอายุระดับแจ้งเตือนล่วงหน้า (วัน)', 0, '2026-09-23 17:24:37', NULL),
  ('had_double_check', 1, 'safety', 'boolean', 'บังคับตรวจสอบ 2 คนสำหรับยาความเสี่ยงสูง (High Alert Drugs)', 0, '2026-09-23 17:24:37', NULL),
  ('health_zone', 6, 'facility', 'string', 'เขตสุขภาพที่', 1, '2026-09-23 23:05:13', 2),
  ('hospital_code', 10978, 'general', 'string', NULL, 0, '2026-09-23 23:26:15', 1),
  ('hospital_name', 'โรงพยาบาลปลวกแดง', 'general', 'string', NULL, 0, '2026-09-23 23:26:15', 1),
  ('jhcis_db_host', '127.0.0.1', 'connection', 'string', 'JHCIS Database Host', 0, '2026-09-23 17:24:37', NULL),
  ('jhcis_db_name', 'jhcisdb', 'connection', 'string', 'JHCIS Database Name', 0, '2026-09-23 17:24:37', NULL),
  ('jhcis_db_port', 3333, 'connection', 'number', 'JHCIS MySQL Port', 0, '2026-09-23 17:24:37', NULL),
  ('jhcis_db_user', 'root', 'connection', 'string', 'JHCIS Database Username', 0, '2026-09-23 17:24:37', NULL),
  ('lead_pharmacist_license', 'ภ.25894', 'facility', 'string', 'เลขที่ใบประกอบวิชาชีพเภสัชกรรม', 0, '2026-09-23 22:15:25', 2),
  ('lead_pharmacist_name', 'ภก. ธนพงศ์ สุขใจ', 'facility', 'string', 'เภสัชกรปฐมภูมิผู้ควบคุมคลังยาและบริการ', 0, '2026-09-23 22:15:25', 2),
  ('max_stock_multiplier', 3.0, 'inventory', 'number', 'ตัวคูณระดับคงคลังสูงสุด (Max Stock = AMC x ตัวคูณ)', 0, '2026-09-23 17:24:37', NULL),
  ('moph_open_data_sync', 1, 'connection', 'boolean', 'เชื่อมต่อมาตรฐานรหัสยา 24 หลักกระทรวงสาธารณสุข', 0, '2026-09-23 17:24:37', NULL),
  ('parent_hospital_code', 10670, 'facility', 'string', 'รหัส รพ.แม่ข่าย (CUP)', 1, '2026-09-23 23:05:13', 2),
  ('parent_hospital_name', 'โรงพยาบาลปลวกแดง (แม่ข่าย CUP)', 'facility', 'string', 'ชื่อโรงพยาบาลแม่ข่าย (CUP)', 1, '2026-09-23 23:05:13', 2),
  ('pharmacy_technician_name', 'น.ส. นภัสสร เภสัชกรน้อย', 'facility', 'string', 'เจ้าพนักงานเภสัชกรรมประจำหน่วยบริการ', 0, '2026-09-23 22:15:25', 2),
  ('province_code', 21, 'facility', 'string', 'รหัสจังหวัด (ระยอง)', 1, '2026-09-23 23:05:13', 2),
  ('safety_stock_multiplier', 1.5, 'inventory', 'number', 'ตัวคูณระดับคงคลังปลอดภัย (Safety Stock = AMC x ตัวคูณ)', 0, '2026-09-23 17:24:37', NULL),
  ('smart_cds_realtime', 1, 'automation', 'boolean', 'ประมวลผลความปลอดภัยทางยาแบบ Real-time', 0, '2026-09-23 17:24:37', NULL),
  ('stock_committee_1', 'ภก. ธนพงศ์ สุขใจ', 'inventory', 'string', 'กรรมการตรวจนับพัสดุคนที่ 1 (สำหรับ รบ. 301)', 0, '2026-09-23 17:24:37', NULL),
  ('stock_committee_2', 'น.ส. นภัสสร เภสัชกรน้อย', 'inventory', 'string', 'กรรมการตรวจนับพัสดุคนที่ 2', 0, '2026-09-23 17:24:37', NULL),
  ('stock_committee_3', 'นางวรรณา รักษ์สุขภาพ', 'inventory', 'string', 'กรรมการตรวจนับพัสดุคนที่ 3', 0, '2026-09-23 17:24:37', NULL),
  ('subdistrict_code', 4, 'facility', 'string', 'รหัสตำบล (แม่น้ำคู้)', 1, '2026-09-23 23:05:13', 2),
  ('training_mode', 0, 'automation', 'boolean', 'โหมดฝึกอบรม / ข้อมูลจำลอง (0=ใช้งานจริง, 1=ฝึกอบรม)', 0, '2026-09-23 17:24:37', NULL),
  ('village_no', 6, 'facility', 'string', 'หมู่ที่', 1, '2026-09-23 23:05:13', 2);

-- ----------------------------------------------------------------------
-- Table structure for `user_roles`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `user_roles` (5 rows)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
  (1, 1),
  (2, 4),
  (3, 6),
  (4, 5),
  (5, 2);

-- ----------------------------------------------------------------------
-- Table structure for `users`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `profession` enum('pharmacist','pharmacy_technician','nurse','public_health','physician','officer') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `facility_id` (`facility_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data dump for `users` (5 rows)
INSERT INTO `users` (`user_id`, `facility_id`, `username`, `password_hash`, `title`, `firstname`, `lastname`, `license_number`, `profession`, `email`, `phone`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
  (1, 1, 'admin', '$2y$10$6W69O/p2MSJ7p68cn57rM.M9Htkm2EzWySObcJdC8WmLuYIoTLYwq', 'ภก.', 'จัตุพล', 'กันทะมูล', 'ภ.27080', 'pharmacist', 'admin@pcu.health.go.th', 0830856460, 1, '2026-09-23 23:30:08', '2026-09-23 11:48:59', '2026-09-23 23:30:08'),
  (2, 1, 'pcu.pharm', '$2y$10$6W69O/p2MSJ7p68cn57rM.M9Htkm2EzWySObcJdC8WmLuYIoTLYwq', 'ภญ.', 'กานดา', 'บริบาลเภสัช', 'ภ.33445', 'pharmacist', 'kanda.p@pcu.health.go.th', '081-2222222', 1, '2026-09-23 20:44:46', '2026-09-23 11:48:59', '2026-09-23 20:44:46'),
  (3, 1, 'nurse.somjai', '$2y$10$6W69O/p2MSJ7p68cn57rM.M9Htkm2EzWySObcJdC8WmLuYIoTLYwq', 'พว.', 'สมใจ', 'รักการพยาบาล', 'พ.55667', 'nurse', 'somjai.n@pcu.health.go.th', '081-3333333', 1, NULL, '2026-09-23 11:48:59', '2026-09-23 11:48:59'),
  (4, 1, 'tech.wirat', '$2y$10$6W69O/p2MSJ7p68cn57rM.M9Htkm2EzWySObcJdC8WmLuYIoTLYwq', 'นาย', 'วิรัช', 'จัดยาสมบูรณ์', 'จพ.77889', 'pharmacy_technician', 'wirat.t@pcu.health.go.th', '081-4444444', 1, NULL, '2026-09-23 11:48:59', '2026-09-23 11:48:59'),
  (5, 3, 'district.pharm', '$2y$10$6W69O/p2MSJ7p68cn57rM.M9Htkm2EzWySObcJdC8WmLuYIoTLYwq', 'ภก.', 'ปรีชา', 'เครือข่ายอำเภอ', 'ภ.99001', 'pharmacist', 'preecha.c@hospital.go.th', '081-5555555', 1, NULL, '2026-09-23 11:48:59', '2026-09-23 11:48:59');

SET FOREIGN_KEY_CHECKS = 1;
