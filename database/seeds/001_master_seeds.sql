-- ==============================================================================
-- PCU SMART PHARMACY: MASTER SEEDS
-- Target Database: pcu_pharmacy (Port 3306)
-- ==============================================================================

SET NAMES utf8mb4;

-- 1. FACILITIES
INSERT INTO facilities (facility_id, facility_code, facility_name, district_code, province_code, parent_hospital_code) VALUES
(1, '05432', 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านหนองบัว', '2101', '21', '10670'),
(2, '05433', 'โรงพยาบาลส่งเสริมสุขภาพตำบลเนินพระ', '2101', '21', '10670'),
(3, '10670', 'โรงพยาบาลระยอง (แม่ข่าย CUP)', '2101', '21', '10670')
ON DUPLICATE KEY UPDATE facility_name=VALUES(facility_name);

-- 2. ROLES
INSERT INTO roles (role_id, role_name, display_name, description) VALUES
(1, 'SUPER_ADMIN', 'ผู้ดูแลระบบสูงสุด', 'สิทธิ์เต็มรูปแบบทั่วทั้งเครือข่าย'),
(2, 'DISTRICT_PHARM', 'เภสัชกรระดับอำเภอ', 'กำกับดูแลระบบยาและ RDU ภาพรวม CUP'),
(3, 'HOSPITAL_PHARM', 'เภสัชกร รพ.แม่ข่าย', 'ประสานงานส่งต่อผู้ป่วยและ Med Reconciliation'),
(4, 'PCU_PHARM', 'เภสัชกรปฐมภูมิ', 'บริบาลเภสัชกรรม คัดกรองความปลอดภัย ทบทวนยา และดูแลคลังยา รพ.สต.'),
(5, 'PHARM_TECH', 'เจ้าพนักงานเภสัชกรรม', 'จัดยา จัดการสต็อก FEFO ตรวจสอบอุณหภูมิตู้เย็น'),
(6, 'NURSE', 'พยาบาลวิชาชีพ', 'คัดกรองผู้ป่วย ฉีดวัคซีน บริหารยาฉุกเฉิน รายงานอุบัติการณ์'),
(7, 'PUBLIC_HEALTH', 'นักวิชาการสาธารณสุข', 'ร่วมทีม 3 หมอเยี่ยมบ้าน (HMR) และงานส่งเสริมสุขภาพ'),
(8, 'FACILITY_ADMIN', 'ผู้ดูแลระบบ รพ.สต.', 'จัดการผู้ใช้และตั้งค่าระบบใน รพ.สต.'),
(9, 'QUALITY_OFFICER', 'ผู้รับผิดชอบงานคุณภาพ', 'ประเมินตนเองตามมาตรฐาน รวบรวมหลักฐานเชิงประจักษ์'),
(10, 'DATA_OFFICER', 'เจ้าหน้าที่ข้อมูลและสถิติ', 'จัดการตัวชี้วัด KPI และส่งออกรายงาน'),
(11, 'VIEWER', 'ผู้ตรวจเยี่ยม / นิเทศงาน', 'ดูรายงานและแดชบอร์ดภาพรวมแบบอ่านอย่างเดียว'),
(12, 'AUDITOR', 'ผู้ตรวจสอบภายนอก', 'ตรวจสอบ Audit log และหลักฐานตามมาตรฐาน')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- 3. PERMISSIONS
INSERT INTO permissions (permission_code, module_name, description) VALUES
('patient.view', 'patient', 'ดูข้อมูลเวชระเบียนผู้ป่วย'),
('patient.cid.unmask', 'patient', 'ปลดล็อกดูเลขบัตร ปชช. เต็ม'),
('medication.view', 'patient', 'ดูประวัติการใช้ยา'),
('allergy.view', 'allergy', 'ดูประวัติแพ้ยา'),
('allergy.manage', 'allergy', 'บันทึกและปรับปรุงประวัติแพ้ยา'),
('safety.risk.view', 'safety', 'ดูการแจ้งเตือนความเสี่ยงยา'),
('safety.rule.configure', 'safety', 'แก้ไขกฎเกณฑ์คัดกรองความปลอดภัย'),
('safety.ham_lasa.manage', 'safety', 'จัดการบัญชี HAM และ LASA'),
('safety.emergency.inspect', 'safety', 'ตรวจสอบชุดยาฉุกเฉิน'),
('review.create', 'review', 'สร้างการทบทวนวรรณกรรมยาและบันทึก DRP'),
('review.approve', 'review', 'รับรองผลการทบทวนยา'),
('reconciliation.manage', 'reconciliation', 'จัดทำ Medication Reconciliation'),
('pharm_care.soap', 'pharm_care', 'บันทึก SOAP Note และแผนการบริบาล'),
('hmr.record', 'hmr', 'บันทึกการเยี่ยมบ้านด้านยา'),
('counseling.record', 'counseling', 'บันทึกการให้คำปรึกษาการใช้ยา'),
('telepharmacy.conduct', 'telepharmacy', 'ให้บริการและบันทึกผล Telepharmacy'),
('stock.view', 'inventory', 'ดูยอดสต็อกยาและวันหมดอายุ'),
('stock.adjust', 'inventory', 'ปรับปรุงยอดสต็อกและตัดจ่ายยา'),
('requisition.create', 'inventory', 'สร้างใบเบิกยา รพ.สต.'),
('requisition.approve', 'inventory', 'อนุมัติใบเบิกยา'),
('coldchain.log', 'coldchain', 'บันทึกอุณหภูมิตู้เย็นยา'),
('coldchain.excursion', 'coldchain', 'บันทึกมาตรการแก้ไขกรณีอุณหภูมิหลุดเกณฑ์'),
('incident.report', 'incident', 'รายงานอุบัติการณ์ความคลาดเคลื่อนทางยา'),
('incident.rca', 'incident', 'วิเคราะห์สาเหตุและปิดเคส RCA'),
('quality.assess', 'quality', 'ประเมินตนเองตามมาตรฐานปฐมภูมิ'),
('evidence.upload', 'quality', 'อัปโหลดหลักฐานเชิงประจักษ์'),
('evidence.review', 'quality', 'ตรวจสอบและอนุมัติหลักฐาน'),
('kpi.manage', 'quality', 'จัดการสูตรและบันทึกผล KPI'),
('report.export', 'report', 'ส่งออกรายงาน Excel/PDF'),
('user.manage', 'system', 'จัดการผู้ใช้และสิทธิ์'),
('settings.manage', 'system', 'ตั้งค่าระบบ'),
('audit.view', 'system', 'เปิดดู Audit Log')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- 4. ROLE PERMISSIONS MAPPING
-- Super Admin has all
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, permission_id FROM permissions;

-- PCU Pharmacist (Role 4)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, permission_id FROM permissions 
WHERE permission_code IN (
    'patient.view', 'patient.cid.unmask', 'medication.view', 'allergy.view', 'allergy.manage',
    'safety.risk.view', 'safety.ham_lasa.manage', 'safety.emergency.inspect',
    'review.create', 'review.approve', 'reconciliation.manage', 'pharm_care.soap',
    'hmr.record', 'counseling.record', 'telepharmacy.conduct',
    'stock.view', 'stock.adjust', 'requisition.create', 'coldchain.log', 'coldchain.excursion',
    'incident.report', 'incident.rca', 'quality.assess', 'evidence.upload', 'evidence.review',
    'kpi.manage', 'report.export', 'audit.view'
);

-- Nurse (Role 6)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, permission_id FROM permissions 
WHERE permission_code IN (
    'patient.view', 'patient.cid.unmask', 'medication.view', 'allergy.view', 'allergy.manage',
    'safety.risk.view', 'safety.emergency.inspect', 'reconciliation.manage',
    'hmr.record', 'counseling.record', 'coldchain.log', 'coldchain.excursion',
    'incident.report', 'evidence.upload', 'report.export'
);

-- Pharmacy Technician (Role 5)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, permission_id FROM permissions 
WHERE permission_code IN (
    'patient.view', 'medication.view', 'allergy.view', 'safety.risk.view', 'safety.emergency.inspect',
    'stock.view', 'stock.adjust', 'requisition.create', 'coldchain.log', 'coldchain.excursion',
    'incident.report', 'evidence.upload', 'report.export'
);

-- 5. SEED USERS (Default Password for all: Password@123)
-- Hash generated via password_hash('Password@123', PASSWORD_BCRYPT)
-- $2y$10$eA3O7R/yN95U999gGq8LTuIq8F9pQ0rP8Xqj0c0iUfZw1WbE0rW3i
INSERT INTO users (user_id, facility_id, username, password_hash, title, firstname, lastname, license_number, profession, email, phone) VALUES
(1, 1, 'admin', '$2y$10$wEvy96z2vLw8vW7Y2zEwceK11gq0z5j4Nl6E2dE0Q1B7qA2vK6E2e', 'ภก.', 'สุรศักดิ์', 'ดูแลระบบ', 'ภ.11223', 'pharmacist', 'admin@pcu.health.go.th', '081-1111111'),
(2, 1, 'pcu.pharm', '$2y$10$wEvy96z2vLw8vW7Y2zEwceK11gq0z5j4Nl6E2dE0Q1B7qA2vK6E2e', 'ภญ.', 'กานดา', 'บริบาลเภสัช', 'ภ.33445', 'pharmacist', 'kanda.p@pcu.health.go.th', '081-2222222'),
(3, 1, 'nurse.somjai', '$2y$10$wEvy96z2vLw8vW7Y2zEwceK11gq0z5j4Nl6E2dE0Q1B7qA2vK6E2e', 'พว.', 'สมใจ', 'รักการพยาบาล', 'พ.55667', 'nurse', 'somjai.n@pcu.health.go.th', '081-3333333'),
(4, 1, 'tech.wirat', '$2y$10$wEvy96z2vLw8vW7Y2zEwceK11gq0z5j4Nl6E2dE0Q1B7qA2vK6E2e', 'นาย', 'วิรัช', 'จัดยาสมบูรณ์', 'จพ.77889', 'pharmacy_technician', 'wirat.t@pcu.health.go.th', '081-4444444'),
(5, 3, 'district.pharm', '$2y$10$wEvy96z2vLw8vW7Y2zEwceK11gq0z5j4Nl6E2dE0Q1B7qA2vK6E2e', 'ภก.', 'ปรีชา', 'เครือข่ายอำเภอ', 'ภ.99001', 'pharmacist', 'preecha.c@hospital.go.th', '081-5555555')
ON DUPLICATE KEY UPDATE firstname=VALUES(firstname);

-- Assign User Roles
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(1, 1), -- admin -> SUPER_ADMIN
(2, 4), -- pcu.pharm -> PCU_PHARM
(3, 6), -- nurse.somjai -> NURSE
(4, 5), -- tech.wirat -> PHARM_TECH
(5, 2); -- district.pharm -> DISTRICT_PHARM

-- 6. QUALITY STANDARDS พ.ศ. 2568-2570
INSERT INTO quality_standards (standard_id, standard_year, standard_version, title, effective_date, expiry_date, is_active) VALUES
(1, 2568, '1.0', 'มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570 (ตาม พ.ร.บ. ระบบสุขภาพปฐมภูมิ พ.ศ. 2562)', '2568-01-01', '2570-12-31', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO quality_categories (category_id, standard_id, category_code, category_name, ordering) VALUES
(1, 1, 'DIM-1', 'มิติที่ 1: การนำองค์กรและการบริหารจัดการ (Governance & Leadership)', 1),
(2, 1, 'DIM-2', 'มิติที่ 2: ประชากรเป้าหมาย ชุมชน และผู้มีส่วนได้ส่วนเสีย (Target Population & Community)', 2),
(3, 1, 'DIM-3', 'มิติที่ 3: ด้านบุคลากร (Workforce & Competency)', 3),
(4, 1, 'DIM-4', 'มิติที่ 4: ระบบบริการสุขภาพและระบบยา (Service Delivery & Medication Safety)', 4),
(5, 1, 'DIM-5', 'มิติที่ 5: ผลลัพธ์และตัวชี้วัดคุณภาพ (Clinical Outcomes & KPIs)', 5)
ON DUPLICATE KEY UPDATE category_name=VALUES(category_name);

INSERT INTO quality_criteria (criterion_id, category_id, criterion_code, criterion_name, subcriterion, requirement, guidance, evidence_required, weight, target, responsible_role) VALUES
(1, 1, '1.2.1', 'คณะกรรมการเภสัชกรรมและการบำบัด (PTC)', 'การกำกับนโยบายยา', 'มีคณะกรรมการ PTC ระดับปฐมภูมิหรือมีตัวแทนใน PTC เครือข่าย CUP เพื่อกำหนดนโยบายระบบยาและ RDU', 'ประชุมอย่างน้อยปีละ 2 ครั้ง', 'คำสั่งแต่งตั้งคณะกรรมการ, รายงานการประชุม PTC', 1.5, 100.0, 'DISTRICT_PHARM'),
(2, 2, '2.2.1', 'การบริบาลเภสัชกรรมที่บ้าน (HMR)', 'การดูแลกลุ่มเปราะบาง', 'มีระบบบริบาลเภสัชกรรมที่บ้านร่วมกับทีม 3 หมอ ค้นหาปัญหา DRP ในผู้ป่วยติดบ้าน/ติดเตียง และยาเหลือใช้', 'บันทึก HMR ครบถ้วนพร้อมภาพถ่าย', 'รายงานผลการเยี่ยมบ้าน HMR, ทะเบียนส่งคืนยาเหลือใช้', 2.0, 80.0, 'PCU_PHARM'),
(3, 3, '3.2.1', 'สมรรถนะบุคลากรด้านยา', 'การประเมินความรู้และทักษะ', 'บุคลากรผู้ปฏิบัติงานด้านยาได้รับการอบรมเรื่อง RDU, High Alert Drugs และผ่านการประเมินสมรรถนะประจำปี', 'เกณฑ์ผ่าน ≥ 80%', 'ใบประกาศนียบัตรอบรม, แบบประเมิน Competency', 1.0, 100.0, 'FACILITY_ADMIN'),
(4, 4, '4.3.1', 'การจัดการคลังยาตามหลัก FEFO', 'การควบคุมสต็อกและวันหมดอายุ', 'คลังยามีการควบคุมอุณหภูมิ มี Stock Card และจัดจ่ายยาตามหลัก First Expired, First Out (FEFO) 100%', 'ตรวจสอบยอดคงคลังตรงกับระบบจริง', 'รายงาน Stock Card อิเล็กทรอนิกส์, บันทึกตรวจนับคลังยา', 2.5, 100.0, 'PHARM_TECH'),
(5, 4, '4.3.2', 'การควบคุมลูกโซ่ความเย็น (Cold Chain)', 'การเก็บรักษาวัคซีนและยาชีววัตถุ', 'มีระบบบันทึกอุณหภูมิตู้เย็น 2-8 °C วันละ 2 ครั้ง (เช้า-บ่าย) และมีแนวทางจัดการเมื่ออุณหภูมิหลุดเกณฑ์', 'อุณหภูมิต้องอยู่ระหว่าง 2.0 - 8.0 °C', 'กราฟ Temperature Log ย้อนหลัง, บันทึกเหตุการณ์ Excursion Action', 2.5, 100.0, 'PHARM_TECH'),
(6, 4, '4.4.1', 'ระบบคัดกรองและป้องกันการแพ้ยาซ้ำ', 'Patient Safety Goals', 'มีระบบเฝ้าระวัง แจ้งเตือนประวัติแพ้ยาตรงกันและแพ้ข้ามกลุ่ม (Cross-sensitivity) อัตราเกิดแพ้ยาซ้ำเป็นศูนย์', 'Zero Recurrent ADR', 'Audit Log การแจ้งเตือนแพ้ยา, รายงานอุบัติการณ์ ADR', 3.0, 100.0, 'PCU_PHARM'),
(7, 4, '4.4.2', 'การจัดการยากลุ่มเสี่ยงสูง (HAM) และ LASA', 'การป้องกันข้อผิดพลาดทางยา', 'มีบัญชีรายชื่อ HAM/LASA แยกเก็บยา ติดป้ายเตือน และใช้ Tall Man Lettering ในระบบสั่งจ่าย', 'ติดสัญลักษณ์เตือน 100%', 'ภาพถ่ายจุดเก็บยาแยกต่างหาก, รายการ Tall Man ในระบบ', 2.0, 100.0, 'PCU_PHARM'),
(8, 4, '4.4.3', 'การประสานรายการยา (Med Reconciliation)', 'รอยต่อการส่งต่อผู้ป่วย', 'มีกระบวนการทำ Med Reconciliation ในผู้ป่วยส่งต่อหรือส่งกลับจาก รพ.แม่ข่าย เพื่อป้องกันยาตกหล่น/ซ้ำซ้อน', 'ทำ Med Reconcile ≥ 80%', 'Reconciled Medication List, บันทึกแก้ไข Discrepancy', 2.0, 80.0, 'PCU_PHARM'),
(9, 5, '5.1.1', 'การใช้ยาปฏิชีวนะอย่างสมเหตุผล (RDU URI)', 'ตัวชี้วัด RDU', 'อัตราการสั่งใช้ยาปฏิชีวนะในโรคติดเชื้อทางเดินหายใจส่วนบน (URI) ไม่เกินร้อยละ 20', 'เป้าหมาย ≤ 20.0%', 'รายงานตัวชี้วัด RDU จาก JHCIS API Dashboard', 3.0, 20.0, 'PCU_PHARM'),
(10, 5, '5.1.2', 'การใช้ยาปฏิชีวนะในอุจจาระร่วงเฉียบพลัน (RDU Diarrhea)', 'ตัวชี้วัด RDU', 'อัตราการสั่งใช้ยาปฏิชีวนะในโรคอุจจาระร่วงเฉียบพลัน (Acute Diarrhea) ไม่เกินร้อยละ 20', 'เป้าหมาย ≤ 20.0%', 'รายงานตัวชี้วัด RDU จาก JHCIS API Dashboard', 3.0, 20.0, 'PCU_PHARM')
ON DUPLICATE KEY UPDATE requirement=VALUES(requirement);

-- 7. MASTER HIGH ALERT MEDICATIONS (HAM)
INSERT INTO high_alert_drugs (ham_id, drug_code, generic_name, risk_category, precautions, double_check_required, storage_instructions) VALUES
(1, 'HAM001', 'Insulin Regular / NPH / Mixtard', 'Hypoglycemic Agent', 'เสี่ยงต่อภาวะน้ำตาลในเลือดต่ำรุนแรง (Severe Hypoglycemia) ต้องตรวจสอบขนาดยาและหน่วยยูนิตอย่างรอบคอบ', 1, 'เก็บในตู้เย็น 2-8 °C ห้ามแช่แข็ง ติดสติกเกอร์สีส้มสะท้อนแสง'),
(2, 'HAM002', 'Warfarin Sodium', 'Anticoagulant', 'เสี่ยงต่อภาวะเลือดออกรุนแรง (Major Bleeding) ต้องติดตามค่า INR และสอบถามประวัติการใช้ยาตีกันและสมุนไพร', 1, 'จัดเก็บแยกช่องเฉพาะ ติดป้ายเตือนสีแดง และ Double-check ทุกครั้ง'),
(3, 'HAM003', 'Digoxin', 'Inotropic Agent', 'หน้าต่างการรักษาแคบ (Narrow Therapeutic Index) เสี่ยงเกิด Digoxin Toxicity โดยเฉพาะในผู้สูงอายุหรือไตเสื่อม', 1, 'จัดเก็บแยกพร้อมป้ายเตือนระดับความปลอดภัย'),
(4, 'HAM004', 'Morphine / Pethidine Injection', 'Narcotics / Opioids', 'กดการหายใจ (Respiratory Depression) และความดันโลหิตต่ำ ต้องเก็บในตู้เซฟล็อกสองชั้นและมีบัญชีคุมพิเศษ', 1, 'เก็บในตู้เก็บยาเสพติดล็อก 2 ชั้น พร้อมสมุดเบิกจ่ายเฉพาะ')
ON DUPLICATE KEY UPDATE precautions=VALUES(precautions);

-- 8. MASTER LASA DRUGS (Look-Alike Sound-Alike)
INSERT INTO lasa_drugs (lasa_id, drug_code_1, drug_name_1, tall_man_1, drug_code_2, drug_name_2, tall_man_2, lasa_type, warning_note, storage_separation_required) VALUES
(1, 'LASA01', 'Prednisone 5 mg tab', 'predniSONE', 'LASA02', 'Prednisolone 5 mg tab', 'prednisoLONE', 'both', 'ชื่อยาและขนาดใกล้เคียงกัน เสี่ยงจ่ายผิดขนาดและการออกฤทธิ์ต่างกัน', 1),
(2, 'LASA03', 'Hydralazine 25 mg tab', 'hydrALAzine', 'LASA04', 'Hydroxyzine 10 mg tab', 'hydrOXYzine', 'sound_alike', 'ยาลดความดันโลหิตสูงกับยาแก้แพ้ เสี่ยงจ่ายสลับกันทำให้ความดันตกหรือรักษาผิดโรค', 1),
(3, 'LASA05', 'Metformin 500 mg tab', 'metFORMIN', 'LASA06', 'Methotrexate 2.5 mg tab', 'methoTREXate', 'sound_alike', 'ยาเบาหวานกับยากดภูมิคุ้มกัน หากจ่ายผิดเป็นพิษร้ายแรงต่อชีวิต', 1),
(4, 'LASA07', 'Amlodipine 5 mg tab', 'amLODIPine', 'LASA08', 'Amitriptyline 10 mg tab', 'amITRIPtyline', 'sound_alike', 'ยาลดความดันกับยาต้านซึมเศร้า เสี่ยงเกิดผลข้างเคียงหัวใจเต้นผิดจังหวะ', 1)
ON DUPLICATE KEY UPDATE warning_note=VALUES(warning_note);

-- 9. MASTER STOCK LOCATIONS & COLD CHAIN UNITS
INSERT INTO stock_locations (location_id, facility_id, location_name, location_type) VALUES
(1, 1, 'ห้องจ่ายยา รพ.สต.บ้านหนองบัว', 'dispensary'),
(2, 1, 'คลังยาหลัก รพ.สต.', 'main_store'),
(3, 1, 'ตู้เย็นเก็บวัคซีนและยาชีววัตถุ 1', 'refrigerator'),
(4, 1, 'กล่องยาช่วยชีวิตฉุกเฉิน (CPR Kit)', 'emergency_kit')
ON DUPLICATE KEY UPDATE location_name=VALUES(location_name);

INSERT INTO cold_chain_units (unit_id, facility_id, unit_code, unit_name, min_temp, max_temp, model_info) VALUES
(1, 1, 'FRIDGE-PCU-01', 'ตู้เย็นชีววัตถุและวัคซีนหลัก (SANYO/Panasonic MPR)', 2.0, 8.0, 'รุ่นทางการแพทย์ มีระบบสำรองไฟและจอแสดงผลดิจิทัล'),
(2, 1, 'FRIDGE-PCU-02', 'ตู้เย็นสำรองและเก็บอินซูลินผู้ป่วย', 2.0, 8.0, 'รุ่นสองประตู มีเทอร์โมมิเตอร์ Min-Max')
ON DUPLICATE KEY UPDATE unit_name=VALUES(unit_name);

-- 10. RECENT TEMPERATURE LOGS (FOR HEALTH STATUS VERIFICATION)
INSERT INTO cold_chain_temperature_logs (unit_id, record_date, session, current_temp, min_recorded, max_recorded, is_excursion, recorded_by) VALUES
(1, CURRENT_DATE(), 'morning', 4.2, 3.8, 5.1, 0, 4),
(1, CURRENT_DATE(), 'afternoon', 4.8, 4.0, 5.5, 0, 4),
(2, CURRENT_DATE(), 'morning', 5.0, 4.2, 5.8, 0, 4)
ON DUPLICATE KEY UPDATE current_temp=VALUES(current_temp);

-- 11. STOCK LOTS (FEFO SAMPLES IN PCU DISPENSARY)
INSERT INTO stock_lots (lot_id, facility_id, location_id, drug_code, drug_name, lot_number, expiry_date, quantity_received, quantity_balance, unit_cost, supplier, status) VALUES
(1, 1, 1, '1000021', 'Amlodipine 5 mg tablet', 'LOT-AML-26A', DATE_ADD(CURRENT_DATE(), INTERVAL 45 DAY), 1000, 350, 0.45, 'องค์การเภสัชกรรม (GPO)', 'active'),
(2, 1, 1, '1000021', 'Amlodipine 5 mg tablet', 'LOT-AML-26B', DATE_ADD(CURRENT_DATE(), INTERVAL 360 DAY), 2000, 2000, 0.42, 'องค์การเภสัชกรรม (GPO)', 'active'),
(3, 1, 1, '1000055', 'Metformin 500 mg tablet', 'LOT-MET-25X', DATE_ADD(CURRENT_DATE(), INTERVAL 25 DAY), 2000, 400, 0.35, 'องค์การเภสัชกรรม (GPO)', 'active'),
(4, 1, 1, '1000055', 'Metformin 500 mg tablet', 'LOT-MET-26Y', DATE_ADD(CURRENT_DATE(), INTERVAL 400 DAY), 5000, 5000, 0.35, 'องค์การเภสัชกรรม (GPO)', 'active'),
(5, 1, 3, 'HAM001', 'Mixtard 30 HM 100 IU/ml 10 ml vial', 'LOT-MIX-2601', DATE_ADD(CURRENT_DATE(), INTERVAL 180 DAY), 50, 28, 120.00, 'Novo Nordisk', 'active'),
(6, 1, 1, '2000010', 'Amoxicillin 500 mg capsule', 'LOT-AMX-26Z', DATE_ADD(CURRENT_DATE(), INTERVAL 240 DAY), 3000, 1200, 0.85, 'องค์การเภสัชกรรม (GPO)', 'active'),
(7, 1, 1, '2000015', 'Paracetamol 500 mg tablet', 'LOT-PARA-27A', DATE_ADD(CURRENT_DATE(), INTERVAL 500 DAY), 10000, 6500, 0.20, 'องค์การเภสัชกรรม (GPO)', 'active')
ON DUPLICATE KEY UPDATE quantity_balance=VALUES(quantity_balance);

-- 12. MASTER KPIS
INSERT INTO kpis (kpi_id, kpi_code, name, category, numerator_desc, denominator_desc, target_value, unit, calculation_source, standard_reference) VALUES
(1, 'KPI-RDU-01', 'ร้อยละการสั่งใช้ยาปฏิชีวนะในโรคติดเชื้อทางเดินหายใจส่วนบน (URI)', 'RDU', 'จำนวนครั้งที่สั่งยาปฏิชีวนะในผู้ป่วย URI', 'จำนวนผู้ป่วยนอก URI ทั้งหมด', 20.00, '%', 'jhcis_auto', 'มาตรฐานปฐมภูมิ มิติที่ 4'),
(2, 'KPI-RDU-02', 'ร้อยละการสั่งใช้ยาปฏิชีวนะในโรคอุจจาระร่วงเฉียบพลัน (Acute Diarrhea)', 'RDU', 'จำนวนครั้งที่สั่งยาปฏิชีวนะในผู้ป่วย Diarrhea', 'จำนวนผู้ป่วยนอก Diarrhea ทั้งหมด', 20.00, '%', 'jhcis_auto', 'มาตรฐานปฐมภูมิ มิติที่ 4'),
(3, 'KPI-SAFE-01', 'อัตราการเกิดอุบัติการณ์แพ้ยาซ้ำในหน่วยบริการ (Zero Recurrent ADR)', 'Safety', 'จำนวนครั้งที่เกิดอาการแพ้ยาซ้ำในระบบ', 'จำนวนผู้ป่วยมีประวัติแพ้ยาที่มารับบริการ', 0.00, '%', 'jhcis_auto', 'Patient Safety Goals'),
(4, 'KPI-SAFE-02', 'ร้อยละการทบทวนยาในผู้ป่วยโรคเรื้อรังที่ใช้ยาตั้งแต่ 5 รายการ (Polypharmacy Review)', 'Clinical', 'จำนวนผู้ป่วย Polypharmacy ที่ได้รับการทบทวนยา', 'จำนวนผู้ป่วย Polypharmacy NCDs ทั้งหมด', 80.00, '%', 'hybrid', 'มาตรฐานวิชาชีพเภสัชกรรมปฐมภูมิ'),
(5, 'KPI-COLD-01', 'ร้อยละการบันทึกอุณหภูมิตู้เย็นเก็บวัคซีนครบถ้วน (เช้า-บ่าย)', 'Storage', 'จำนวนรอบที่บันทึกอุณหภูมิครบถ้วน', 'จำนวนรอบที่ต้องบันทึกทั้งหมดในเดือน', 100.00, '%', 'jhcis_auto', 'GSP & Vaccine Safety')
ON DUPLICATE KEY UPDATE target_value=VALUES(target_value);
