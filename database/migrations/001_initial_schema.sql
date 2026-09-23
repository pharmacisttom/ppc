-- ==============================================================================
-- PCU SMART PHARMACY: INITIAL SCHEMA MIGRATION
-- Database: pcu_pharmacy (Port 3306)
-- Target: MySQL 8.0+ / MariaDB 10.4+
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. FACILITIES & ORGANIZATIONS
DROP TABLE IF EXISTS facilities;
CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_code VARCHAR(10) NOT NULL UNIQUE,
    facility_name VARCHAR(255) NOT NULL,
    district_code VARCHAR(10) NOT NULL,
    province_code VARCHAR(10) NOT NULL,
    parent_hospital_code VARCHAR(10) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS, ROLES & PERMISSIONS (RBAC)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    title VARCHAR(50) NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) NULL,
    profession ENUM('pharmacist', 'pharmacy_technician', 'nurse', 'public_health', 'physician', 'officer') NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_code VARCHAR(80) NOT NULL UNIQUE,
    module_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS role_permissions;
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    facility_id INT NULL,
    action_type VARCHAR(50) NOT NULL,
    module_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) NULL,
    patient_pid INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    payload_before JSON NULL,
    payload_after JSON NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (patient_pid),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CLINICAL PHARMACEUTICAL CARE & SAFETY
DROP TABLE IF EXISTS medication_reviews;
CREATE TABLE medication_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    patient_cid_hash CHAR(64) NOT NULL,
    review_date DATE NOT NULL,
    reviewer_id INT NOT NULL,
    review_type ENUM('routine', 'polypharmacy', 'high_risk', 'referral', 'post_discharge') NOT NULL,
    total_medications INT DEFAULT 0,
    clinical_summary TEXT NULL,
    status ENUM('draft', 'completed', 'communicated', 'resolved') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    INDEX (patient_pid),
    INDEX (review_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS medication_review_problems;
CREATE TABLE medication_review_problems (
    problem_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    drug_code VARCHAR(24) NULL,
    drug_name VARCHAR(255) NOT NULL,
    drp_category ENUM(
        'unnecessary_medication',
        'need_additional_therapy',
        'ineffective_medication',
        'dose_too_low',
        'dose_too_high',
        'adverse_drug_reaction',
        'drug_interaction',
        'non_adherence',
        'duplicate_therapy',
        'administration_problem',
        'other'
    ) NOT NULL,
    problem_description TEXT NOT NULL,
    evidence_detail TEXT NULL,
    assessment TEXT NOT NULL,
    recommendation TEXT NOT NULL,
    prescriber_response ENUM('accepted_fully', 'accepted_partially', 'rejected_with_reason', 'pending') DEFAULT 'pending',
    prescriber_comment TEXT NULL,
    outcome ENUM('resolved', 'partially_resolved', 'unresolved', 'unknown') DEFAULT 'unknown',
    follow_up_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES medication_reviews(review_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS medication_reconciliations;
CREATE TABLE medication_reconciliations (
    recon_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    recon_date DATE NOT NULL,
    transition_type ENUM('hospital_discharge', 'hospital_referral', 'home_visit', 'clinic_transition') NOT NULL,
    source_facility VARCHAR(255) NOT NULL,
    practitioner_id INT NOT NULL,
    status ENUM('draft', 'in_progress', 'completed') DEFAULT 'draft',
    clinical_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (practitioner_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS medication_reconciliation_items;
CREATE TABLE medication_reconciliation_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    recon_id INT NOT NULL,
    prior_drug_name VARCHAR(255) NOT NULL,
    prior_dose VARCHAR(100) NULL,
    current_drug_name VARCHAR(255) NULL,
    current_dose VARCHAR(100) NULL,
    discrepancy_type ENUM(
        'no_discrepancy',
        'intended_addition',
        'intended_discontinuation',
        'intended_dosage_change',
        'unintended_omission',
        'unintended_commission',
        'unintended_dosage_difference',
        'unintended_duplication'
    ) NOT NULL,
    clinical_decision ENUM('continue', 'stop', 'modify', 'substitute', 'refer_back') NOT NULL,
    reason TEXT NULL,
    FOREIGN KEY (recon_id) REFERENCES medication_reconciliations(recon_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS pharmaceutical_cares;
CREATE TABLE pharmaceutical_cares (
    care_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    care_date DATE NOT NULL,
    pharmacist_id INT NOT NULL,
    subjective TEXT NULL,
    objective TEXT NULL,
    assessment TEXT NOT NULL,
    plan TEXT NOT NULL,
    outcome ENUM('improved', 'stable', 'worsened', 'unresolved') DEFAULT 'stable',
    next_follow_up DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacist_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS home_medication_reviews;
CREATE TABLE home_medication_reviews (
    hmr_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    visit_date DATE NOT NULL,
    visitor_id INT NOT NULL,
    team_members VARCHAR(255) NULL,
    gps_lat DECIMAL(10, 8) NULL,
    gps_lng DECIMAL(11, 8) NULL,
    storage_condition ENUM('good', 'moderate', 'poor') NOT NULL,
    expired_drugs_found INT DEFAULT 0,
    duplicate_drugs_found INT DEFAULT 0,
    traditional_herbs_found TEXT NULL,
    adherence_score DECIMAL(5, 2) NULL,
    summary_problems TEXT NOT NULL,
    interventions_taken TEXT NOT NULL,
    follow_up_plan TEXT NULL,
    photo_urls JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visitor_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SAFETY RULES, HAM, LASA & INCIDENTS
DROP TABLE IF EXISTS risk_rules;
CREATE TABLE risk_rules (
    rule_id INT AUTO_INCREMENT PRIMARY KEY,
    rule_code VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('allergy', 'polypharmacy', 'duplicate', 'drug_disease', 'renal', 'elderly', 'pediatric', 'pregnancy', 'ham', 'lasa', 'interaction') NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    severity ENUM('info', 'review', 'high', 'critical') NOT NULL,
    trigger_condition JSON NOT NULL,
    alert_message TEXT NOT NULL,
    clinical_guideline TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS high_alert_drugs;
CREATE TABLE high_alert_drugs (
    ham_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_code VARCHAR(24) NOT NULL UNIQUE,
    generic_name VARCHAR(255) NOT NULL,
    risk_category VARCHAR(100) NOT NULL,
    precautions TEXT NOT NULL,
    double_check_required TINYINT(1) DEFAULT 1,
    storage_instructions VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS lasa_drugs;
CREATE TABLE lasa_drugs (
    lasa_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_code_1 VARCHAR(24) NOT NULL,
    drug_name_1 VARCHAR(255) NOT NULL,
    tall_man_1 VARCHAR(255) NOT NULL,
    drug_code_2 VARCHAR(24) NOT NULL,
    drug_name_2 VARCHAR(255) NOT NULL,
    tall_man_2 VARCHAR(255) NOT NULL,
    lasa_type ENUM('look_alike', 'sound_alike', 'both') NOT NULL,
    warning_note TEXT NULL,
    storage_separation_required TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS medication_incidents;
CREATE TABLE medication_incidents (
    incident_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    incident_date DATETIME NOT NULL,
    report_date DATETIME NOT NULL,
    reporter_id INT NULL,
    incident_stage ENUM('prescribing', 'transcribing', 'dispensing', 'administration', 'monitoring', 'storage') NOT NULL,
    severity_category ENUM('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I') NOT NULL,
    is_near_miss TINYINT(1) DEFAULT 0,
    drugs_involved TEXT NOT NULL,
    incident_description TEXT NOT NULL,
    immediate_action_taken TEXT NOT NULL,
    root_cause_analysis TEXT NULL,
    preventive_action TEXT NULL,
    responsible_person VARCHAR(150) NULL,
    due_date DATE NULL,
    status ENUM('reported', 'investigating', 'rca_completed', 'closed') DEFAULT 'reported',
    closed_at DATETIME NULL,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. INVENTORY, FEFO & COLD CHAIN
DROP TABLE IF EXISTS stock_locations;
CREATE TABLE stock_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    location_name VARCHAR(100) NOT NULL,
    location_type ENUM('main_store', 'dispensary', 'refrigerator', 'emergency_kit', 'sub_station') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS stock_lots;
CREATE TABLE stock_lots (
    lot_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    location_id INT NOT NULL,
    drug_code VARCHAR(24) NOT NULL,
    drug_name VARCHAR(255) NOT NULL,
    lot_number VARCHAR(50) NOT NULL,
    expiry_date DATE NOT NULL,
    quantity_received INT NOT NULL DEFAULT 0,
    quantity_balance INT NOT NULL DEFAULT 0,
    unit_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    manufacturer VARCHAR(150) NULL,
    supplier VARCHAR(150) NULL,
    status ENUM('active', 'quarantine', 'expired', 'exhausted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES stock_locations(location_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    INDEX (drug_code),
    INDEX (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS stock_movements;
CREATE TABLE stock_movements (
    movement_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    lot_id INT NOT NULL,
    movement_type ENUM('receive', 'dispense', 'adjust_in', 'adjust_out', 'transfer', 'return', 'destroy') NOT NULL,
    reference_no VARCHAR(50) NULL,
    quantity_changed INT NOT NULL,
    balance_after INT NOT NULL,
    operator_id INT NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lot_id) REFERENCES stock_lots(lot_id),
    FOREIGN KEY (operator_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS cold_chain_units;
CREATE TABLE cold_chain_units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    unit_code VARCHAR(50) NOT NULL,
    unit_name VARCHAR(150) NOT NULL,
    min_temp DECIMAL(4, 1) DEFAULT 2.0,
    max_temp DECIMAL(4, 1) DEFAULT 8.0,
    model_info VARCHAR(150) NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS cold_chain_temperature_logs;
CREATE TABLE cold_chain_temperature_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    record_date DATE NOT NULL,
    session ENUM('morning', 'afternoon') NOT NULL,
    current_temp DECIMAL(4, 1) NOT NULL,
    min_recorded DECIMAL(4, 1) NULL,
    max_recorded DECIMAL(4, 1) NULL,
    is_excursion TINYINT(1) DEFAULT 0,
    corrective_action TEXT NULL,
    recorded_by INT NOT NULL,
    verified_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES cold_chain_units(unit_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    INDEX (record_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. QUALITY STANDARDS, EVIDENCE & KPIS
DROP TABLE IF EXISTS quality_standards;
CREATE TABLE quality_standards (
    standard_id INT AUTO_INCREMENT PRIMARY KEY,
    standard_year INT NOT NULL,
    standard_version VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS quality_categories;
CREATE TABLE quality_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    standard_id INT NOT NULL,
    category_code VARCHAR(20) NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    ordering INT DEFAULT 1,
    FOREIGN KEY (standard_id) REFERENCES quality_standards(standard_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS quality_criteria;
CREATE TABLE quality_criteria (
    criterion_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    criterion_code VARCHAR(30) NOT NULL,
    criterion_name VARCHAR(255) NOT NULL,
    subcriterion VARCHAR(100) NULL,
    requirement TEXT NOT NULL,
    guidance TEXT NULL,
    evidence_required TEXT NOT NULL,
    weight DECIMAL(5,2) DEFAULT 1.00,
    target DECIMAL(5,2) DEFAULT 100.00,
    responsible_role VARCHAR(50) DEFAULT 'PCU Pharmacist',
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES quality_categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS quality_assessments;
CREATE TABLE quality_assessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    criterion_id INT NOT NULL,
    assessment_year INT NOT NULL,
    status ENUM(
        'not_assessed',
        'not_started',
        'in_progress',
        'evidence_missing',
        'ready',
        'passed_internal_review',
        'need_improvement'
    ) DEFAULT 'not_assessed',
    score_achieved DECIMAL(5, 2) DEFAULT 0.00,
    gap_identified TEXT NULL,
    corrective_action_plan TEXT NULL,
    assessed_by INT NOT NULL,
    assessed_at DATETIME NOT NULL,
    target_completion_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    FOREIGN KEY (assessed_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS quality_evidence;
CREATE TABLE quality_evidence (
    evidence_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    evidence_type ENUM('pdf', 'word', 'excel', 'image', 'url', 'meeting_record', 'policy', 'sop', 'report', 'photo', 'training_record') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size_kb INT NULL,
    mime_type VARCHAR(100) NULL,
    description TEXT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS evidence_criterion_mapping;
CREATE TABLE evidence_criterion_mapping (
    mapping_id INT AUTO_INCREMENT PRIMARY KEY,
    evidence_id INT NOT NULL,
    criterion_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_evidence_criterion (evidence_id, criterion_id),
    FOREIGN KEY (evidence_id) REFERENCES quality_evidence(evidence_id) ON DELETE CASCADE,
    FOREIGN KEY (criterion_id) REFERENCES quality_criteria(criterion_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS kpis;
CREATE TABLE kpis (
    kpi_id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NOT NULL,
    numerator_desc VARCHAR(255) NOT NULL,
    denominator_desc VARCHAR(255) NOT NULL,
    target_value DECIMAL(8, 2) NOT NULL,
    unit VARCHAR(50) DEFAULT '%',
    frequency ENUM('monthly', 'quarterly', 'yearly') DEFAULT 'monthly',
    calculation_source ENUM('jhcis_auto', 'manual_verified', 'hybrid') NOT NULL,
    standard_reference VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS kpi_results;
CREATE TABLE kpi_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_id INT NOT NULL,
    facility_id INT NOT NULL,
    period_year INT NOT NULL,
    period_month INT NOT NULL,
    numerator_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    denominator_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    result_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    evidence_id INT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kpi_id) REFERENCES kpis(kpi_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    FOREIGN KEY (evidence_id) REFERENCES quality_evidence(evidence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS sop_documents;
CREATE TABLE sop_documents (
    sop_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    sop_code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    effective_date DATE NOT NULL,
    review_date DATE NOT NULL,
    owner_role VARCHAR(100) NOT NULL,
    approver_name VARCHAR(150) NOT NULL,
    file_path VARCHAR(500) NULL,
    status ENUM('draft', 'active', 'under_review', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
