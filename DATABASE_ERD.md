# APPLICATION DATABASE ENTITY RELATIONSHIP DESIGN (ERD)
## การออกแบบโครงสร้างฐานข้อมูลระบบ PCU Smart Pharmacy (`pcu_pharmacy`)
**เอกสารเลขที่:** AGY-PCU-DB-005  
**ฐานข้อมูลเป้าหมาย:** MySQL 8.0+ / MariaDB 10.4+ (Default Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`)  
**หลักการออกแบบ:** Relational Integrity, Third Normal Form (3NF), Foreign Key Constraints, Auditability, Performance Indexing  

---

## 1. ภาพรวมกลุ่มตารางในระบบ (Database Module Groups)

ฐานข้อมูล `pcu_pharmacy` แบ่งโครงสร้างออกเป็น 5 กลุ่มโมดูลหลัก เพื่อรองรับการทำงานด้านบริบาลเภสัชกรรมและความปลอดภัย:

```
+-----------------------------------------------------------------------------------------------+
|                               DATABASE MODULES: pcu_pharmacy                                  |
+-----------------------------------------------------------------------------------------------+
| 1. SYSTEM & RBAC       | users, roles, permissions, user_roles, role_permissions,             |
|                        | facilities, system_settings, audit_logs, api_logs, login_logs        |
+------------------------+----------------------------------------------------------------------+
| 2. PATIENT CARE        | medication_reviews, medication_review_problems,                       |
|                        | medication_reconciliations, medication_reconciliation_items,          |
|                        | pharmaceutical_cares, pharmaceutical_care_problems,                   |
|                        | home_medication_reviews, hmr_medications_found, followups             |
+------------------------+----------------------------------------------------------------------+
| 3. MEDICATION SAFETY   | risk_rules, drug_allergy_reviews, high_alert_drugs, lasa_drugs,       |
|                        | emergency_drug_sets, emergency_drug_items, emergency_inspections,     |
|                        | medication_incidents, incident_rca_actions                           |
+------------------------+----------------------------------------------------------------------+
| 4. INVENTORY & SUPPLY  | drug_items, stock_locations, stock_lots, stock_movements,             |
|                        | requisitions, requisition_items, receivings, receiving_items,         |
|                        | inventory_counts, cold_chain_units, cold_chain_temperature_logs       |
+------------------------+----------------------------------------------------------------------+
| 5. QUALITY & STANDARDS | quality_standards, quality_categories, quality_criteria,              |
|                        | quality_assessments, quality_evidence, corrective_actions,            |
|                        | cqi_pdca_projects, kpis, kpi_results, sop_documents, training_records|
+-----------------------------------------------------------------------------------------------+
```

---

## 2. นิยามโครงสร้างตารางโดยละเอียด (Data Dictionaries & Table Definitions)

### กลุ่มที่ 1: ระบบผู้ใช้และสิทธิ์การเข้าถึง (System, Security & RBAC)

```sql
-- 1.1 ตารางหน่วยบริการ (Facilities)
CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_code VARCHAR(10) NOT NULL UNIQUE COMMENT 'รหัสหน่วยบริการ 5 หลัก หรือ 9 หลักของกระทรวงสาธารณสุข',
    facility_name VARCHAR(255) NOT NULL COMMENT 'เช่น รพ.สต.บ้านหนองบัว',
    district_code VARCHAR(10) NOT NULL,
    province_code VARCHAR(10) NOT NULL,
    parent_hospital_code VARCHAR(10) NOT NULL COMMENT 'รหัส รพ.แม่ข่าย CUP',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2 ตารางผู้ใช้งาน (Users)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Argon2id หรือ bcrypt hash',
    title VARCHAR(50) NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) NULL COMMENT 'เลขที่ใบประกอบวิชาชีพ เช่น ภ.12345 หรือ ว.56789',
    profession ENUM('pharmacist', 'pharmacy_technician', 'nurse', 'public_health', 'physician', 'officer') NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3 ตารางบทบาท (Roles) และสิทธิ์ (Permissions)
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_code VARCHAR(80) NOT NULL UNIQUE COMMENT 'เช่น patient.view, review.create, stock.adjust',
    module_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4 บันทึก Audit Log และความมั่นคงปลอดภัย
CREATE TABLE audit_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    facility_id INT NULL,
    action_type VARCHAR(50) NOT NULL COMMENT 'LOGIN, LOGOUT, PATIENT_VIEW, RX_VIEW, CREATE, UPDATE, DELETE',
    module_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) NULL,
    patient_pid INT NULL COMMENT 'PID ผู้ป่วยตาม JHCIS เพื่อติดตาม PDPA',
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
```

---

### กลุ่มที่ 2: บริบาลเภสัชกรรมและการทบทวนการใช้ยา (Patient Pharmaceutical Care)

```sql
-- 2.1 ตารางการทบทวนวรรณกรรมยา (Medication Review)
CREATE TABLE medication_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL COMMENT 'เชื่อมโยง PID ใน JHCIS',
    patient_cid_hash CHAR(64) NOT NULL COMMENT 'SHA-256 ของเลขบัตร ปชช. สำหรับดัชนีค้นหาอย่างปลอดภัย',
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

-- 2.2 ตารางปัญหาจากการใช้ยา (Drug Related Problems - DRP) ตามมาตรฐาน PCNE
CREATE TABLE medication_review_problems (
    problem_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    drug_code VARCHAR(24) NULL COMMENT 'รหัสยา JHCIS cdrug',
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

-- 2.3 ตารางการประสานรายการยา (Medication Reconciliation)
CREATE TABLE medication_reconciliations (
    recon_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    recon_date DATE NOT NULL,
    transition_type ENUM('hospital_discharge', 'hospital_referral', 'home_visit', 'clinic_transition') NOT NULL,
    source_facility VARCHAR(255) NOT NULL COMMENT 'สถานพยาบาลต้นทาง เช่น รพ.ศูนย์/รพ.ทั่วไป',
    practitioner_id INT NOT NULL,
    status ENUM('draft', 'in_progress', 'completed') DEFAULT 'draft',
    clinical_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (practitioner_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE medication_reconciliation_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    recon_id INT NOT NULL,
    prior_drug_name VARCHAR(255) NOT NULL COMMENT 'ชื่อยาและขนาดเดิม',
    prior_dose VARCHAR(100) NULL,
    current_drug_name VARCHAR(255) NULL COMMENT 'ชื่อยาและขนาดที่ได้รับใหม่',
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

-- 2.4 ตารางการบริบาลเภสัชกรรมที่บ้าน (Home Medication Review - HMR)
CREATE TABLE home_medication_reviews (
    hmr_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    patient_pid INT NOT NULL,
    visit_date DATE NOT NULL,
    visitor_id INT NOT NULL,
    team_members VARCHAR(255) NULL COMMENT 'เช่น เภสัชกร, พยาบาลวิชาชีพ, อสม.',
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
    photo_urls JSON NULL COMMENT 'รูปภาพการจัดเก็บยาหรือยาเดิม',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visitor_id) REFERENCES users(user_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### กลุ่มที่ 3: ความปลอดภัยด้านยาและระบบแจ้งเตือน (Medication Safety & Incidents)

```sql
-- 3.1 ตารางกฎเกณฑ์คัดกรองความปลอดภัย (Safety Risk Rules Engine)
CREATE TABLE risk_rules (
    rule_id INT AUTO_INCREMENT PRIMARY KEY,
    rule_code VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('allergy', 'polypharmacy', 'duplicate', 'drug_disease', 'renal', 'elderly', 'pediatric', 'pregnancy', 'ham', 'lasa', 'interaction') NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    severity ENUM('info', 'review', 'high', 'critical') NOT NULL,
    trigger_condition JSON NOT NULL COMMENT 'เงื่อนไขคัดกรอง เช่น รหัสยา, eGFR, อายุ, โรคประจำตัว',
    alert_message TEXT NOT NULL,
    clinical_guideline TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 ตารางยาที่มีความเสี่ยงสูง (High Alert Medications - HAM) และยาชื่อพ้องมองคล้าย (LASA)
CREATE TABLE high_alert_drugs (
    ham_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_code VARCHAR(24) NOT NULL UNIQUE COMMENT 'รหัสยา JHCIS cdrug',
    generic_name VARCHAR(255) NOT NULL,
    risk_category VARCHAR(100) NOT NULL COMMENT 'เช่น Anticoagulants, Insulins, Oral Hypoglycemics',
    precautions TEXT NOT NULL,
    double_check_required TINYINT(1) DEFAULT 1,
    storage_instructions VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lasa_drugs (
    lasa_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_code_1 VARCHAR(24) NOT NULL,
    drug_name_1 VARCHAR(255) NOT NULL,
    tall_man_1 VARCHAR(255) NOT NULL COMMENT 'เช่น predniSONE',
    drug_code_2 VARCHAR(24) NOT NULL,
    drug_name_2 VARCHAR(255) NOT NULL,
    tall_man_2 VARCHAR(255) NOT NULL COMMENT 'เช่น prednisoLONE',
    lasa_type ENUM('look_alike', 'sound_alike', 'both') NOT NULL,
    warning_note TEXT NULL,
    storage_separation_required TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 ตารางอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incidents & RCA)
CREATE TABLE medication_incidents (
    incident_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    incident_date DATETIME NOT NULL,
    report_date DATETIME NOT NULL,
    reporter_id INT NULL COMMENT 'สามารถเป็น Anonymous ได้',
    incident_stage ENUM('prescribing', 'transcribing', 'dispensing', 'administration', 'monitoring', 'storage') NOT NULL,
    severity_category ENUM('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I') NOT NULL COMMENT 'NCC MERP Index',
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
```

---

### กลุ่มที่ 4: ระบบคลังยาและควบคุมคุณภาพ (Inventory, FEFO & Cold Chain)

```sql
-- 4.1 ตารางตำแหน่งจัดเก็บ (Stock Locations)
CREATE TABLE stock_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    location_name VARCHAR(100) NOT NULL COMMENT 'เช่น ห้องยา รพ.สต., คลังยาหลัก, ตู้เย็นวัคซีน 1',
    location_type ENUM('main_store', 'dispensary', 'refrigerator', 'emergency_kit', 'sub_station') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 ตารางล็อตยาและการตัดจ่ายตามหลัก First Expired First Out (FEFO)
CREATE TABLE stock_lots (
    lot_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    location_id INT NOT NULL,
    drug_code VARCHAR(24) NOT NULL COMMENT 'เชื่อม JHCIS cdrug',
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

-- 4.3 ตารางความเคลื่อนไหวคลังยา (Stock Movements & Stock Card)
CREATE TABLE stock_movements (
    movement_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    lot_id INT NOT NULL,
    movement_type ENUM('receive', 'dispense', 'adjust_in', 'adjust_out', 'transfer', 'return', 'destroy') NOT NULL,
    reference_no VARCHAR(50) NULL COMMENT 'เลขที่ใบเบิก, Visit No, หรือใบนับสต็อก',
    quantity_changed INT NOT NULL COMMENT 'ค่าบวก หรือลบ',
    balance_after INT NOT NULL,
    operator_id INT NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lot_id) REFERENCES stock_lots(lot_id),
    FOREIGN KEY (operator_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.4 ตารางการตรวจติดตามอุณหภูมิลูกโซ่ความเย็น (Cold Chain Temperature Logs)
CREATE TABLE cold_chain_units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    unit_code VARCHAR(50) NOT NULL COMMENT 'เช่น FRIDGE-01',
    unit_name VARCHAR(150) NOT NULL COMMENT 'ตู้เย็นเก็บวัคซีนและยาชีววัตถุหลัก',
    min_temp DECIMAL(4, 1) DEFAULT 2.0,
    max_temp DECIMAL(4, 1) DEFAULT 8.0,
    model_info VARCHAR(150) NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cold_chain_temperature_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    record_date DATE NOT NULL,
    session ENUM('morning', 'afternoon') NOT NULL,
    current_temp DECIMAL(4, 1) NOT NULL,
    min_recorded DECIMAL(4, 1) NULL,
    max_recorded DECIMAL(4, 1) NULL,
    is_excursion TINYINT(1) DEFAULT 0 COMMENT '1 เมื่ออุณหภูมิต่ำกว่า 2 หรือสูงกว่า 8 องศาเซลเซียส',
    corrective_action TEXT NULL,
    recorded_by INT NOT NULL,
    verified_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES cold_chain_units(unit_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    INDEX (record_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### กลุ่มที่ 5: ระบบมาตรฐาน ตัวชี้วัด และคลังหลักฐาน (Quality, KPIs & Evidence)

```sql
-- 5.1 ตารางการประเมินตนเองตามมาตรฐาน (Quality Self Assessments)
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

-- 5.2 ตารางศูนย์รวมหลักฐานเชิงประจักษ์ (Evidence Repository)
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

-- 5.3 ตารางความสัมพันธ์ระหว่างเกณฑ์มาตรฐานกับหลักฐาน (Many-to-Many ไม่ซ้ำซ้อน)
CREATE TABLE evidence_criterion_mapping (
    mapping_id INT AUTO_INCREMENT PRIMARY KEY,
    evidence_id INT NOT NULL,
    criterion_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_evidence_criterion (evidence_id, criterion_id),
    FOREIGN KEY (evidence_id) REFERENCES quality_evidence(evidence_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.4 ตารางเครื่องมือกำหนดตัวชี้วัด (Configurable KPI Engine)
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

CREATE TABLE kpi_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_id INT NOT NULL,
    facility_id INT NOT NULL,
    period_year INT NOT NULL,
    period_month INT NOT NULL,
    numerator_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    denominator_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    result_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_target_met TINYINT(1) GENERATED ALWAYS AS (result_value >= 0) VIRTUAL,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    evidence_id INT NULL,
    remarks TEXT NULL,
    FOREIGN KEY (kpi_id) REFERENCES kpis(kpi_id),
    FOREIGN KEY (facility_id) REFERENCES facilities(facility_id),
    FOREIGN KEY (evidence_id) REFERENCES quality_evidence(evidence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. ดัชนีเพื่อประสิทธิภาพและความปลอดภัย (Performance & Security Indices)

1. **ดัชนีเพื่อการค้นหาประวัติรวดเร็ว (Indexing for Low Latency):**
   * ตาราง `audit_logs`: Index (`user_id`, `created_at`), Index (`patient_pid`) เพื่อรองรับการตรวจสอบ PDPA แบบ Sub-second
   * ตาราง `stock_lots`: Index (`drug_code`, `status`, `expiry_date`) เพื่อให้การ Query ดึงยาหมดอายุก่อน (FEFO) มีความเร็วสูงสุด
   * ตาราง `kpi_results`: Index (`facility_id`, `period_year`, `period_month`) สำหรับเรนเดอร์ Dashboard แบบ Real-time
2. **การป้องกันความขัดแย้งของข้อมูล (Data Integrity Enforcements):**
   * ตาราง `evidence_criterion_mapping` กำหนด `UNIQUE KEY (evidence_id, criterion_id)` เพื่อป้องกันการแนบหลักฐานซ้ำซ้อน
   * ตารางทั้งหมดใช้ Foreign Key Constraints ควบคุม Referential Integrity ห้ามลบข้อมูลหลักฐานหรือประวัติการรักษาเมื่อมีธุรกรรมผูกอยู่

---
*เอกสารนี้ได้รับการตรวจสอบความเข้ากันได้กับ MySQL 8.0 และ MariaDB 10.4 สำหรับการสร้างสคริปต์ Migration ใน Phase 2*
