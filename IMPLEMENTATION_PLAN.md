# PCU SMART PHARMACY — MASTER IMPLEMENTATION PLAN
## แผนแม่บทการพัฒนาและติดตั้งระบบบริหารจัดการด้านยาและเภสัชกรรมปฐมภูมิ
**เอกสารเลขที่:** AGY-PCU-PLAN-010  
**โครงการ:** PCU Smart Pharmacy & Medication Management System  
**สถาปัตยกรรม:** PHP 8.2 Modern Modular MVC + JHCIS Read-Only API Gateway + MySQL Application DB  
**แนวทางปฏิบัติ:** AI Development Protocol (PLAN -> BUILD -> RUN -> TEST -> FIX -> VERIFY -> DOCUMENT)  

---

## 1. ผลการตรวจรับระยะสำรวจ (Phase 0: Discovery & Standard Mapping Status)

| รายการภารกิจใน Phase 0 | เอกสารอ้างอิง | สถานะ |
|---|---|:---:|
| 1. สำรวจ Technology Stack และ Host Environment | `PROJECT_DISCOVERY.md` | ✅ **เรียบร้อย** |
| 2. ตรวจสอบพอร์ตและ Schema จริงของฐานข้อมูล JHCIS | `PROJECT_DISCOVERY.md` | ✅ **เรียบร้อย (Port 3333, DB jhcisdb)** |
| 3. จัดทำความสอดคล้องตามมาตรฐานหน่วยบริการปฐมภูมิ 2568–2570 | `PRIMARY_CARE_STANDARD_MAPPING.md` | ✅ **เรียบร้อย** |
| 4. จัดทำ Standard Traceability Matrix ด้านยาและความปลอดภัย | `PHARMACY_STANDARD_MATRIX.md` | ✅ **เรียบร้อย** |
| 5. ออกแบบสถาปัตยกรรม Multi-tier Isolation และ PDPA | `ARCHITECTURE.md` | ✅ **เรียบร้อย** |
| 6. ออกแบบ Schema ฐานข้อมูลแอปพลิเคชัน 5 กลุ่มโมดูล (ERD) | `DATABASE_ERD.md` | ✅ **เรียบร้อย** |
| 7. กำหนดข้อกำหนด API Gateway และ Query Logic จริงของ JHCIS | `API_MAPPING.md` | ✅ **เรียบร้อย** |
| 8. ออกแบบเมทริกซ์สิทธิ์ 12 บทบาท (RBAC Matrix) | `RBAC_MATRIX.md` | ✅ **เรียบร้อย** |
| 9. ออกแบบเครื่องมือตัวชี้วัดคุณภาพ (KPI Engine Matrix) | `KPI_MATRIX.md` | ✅ **เรียบร้อย** |
| 10. ออกแบบศูนย์รวมหลักฐานเชิงประจักษ์ (Evidence Matrix) | `EVIDENCE_MATRIX.md` | ✅ **เรียบร้อย** |

---

## 2. แผนการพัฒนาระยะถัดไป (Phases 1 ถึง 35 จัดกลุ่ม 6 กลุ่มงานหลัก)

```
+---------------------------------------------------------------------------------------------------+
|                           PHASED IMPLEMENTATION ROADMAP (PHASES 1-35)                             |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 1: CORE PLATFORM & JHCIS GATEWAY (Phases 1 - 6)                                         |
|  - Phase 1: Architecture & Project Structure Setup (Composer, Autoloader, Environment Config)     |
|  - Phase 2: Database Migration & Schema Creation (pcu_pharmacy บน MySQL 3306)                     |
|  - Phase 3: Authentication & Security Core (Argon2id, Session Hijack Prevention, CSRF)            |
|  - Phase 4: Role-Based Access Control (RBAC Middleware & Permission Engine)                       |
|  - Phase 5: JHCIS Read-Only API Gateway Implementation & Port 3333 Connectivity Verifier          |
|  - Phase 6: Patient Search & CID Masking Security Engine                                          |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 2: CLINICAL PHARMACY & MEDICATION SAFETY (Phases 7 - 14)                                |
|  - Phase 7: Patient Pharmaceutical Profile & Comprehensive Timeline                               |
|  - Phase 8: Medication History & Chronological Filter                                             |
|  - Phase 9: Drug Allergy Management & Cross-Sensitivity Screening                                 |
|  - Phase 10: Medication Safety Decision Support Rule Engine (14 Risk Dimensions)                  |
|  - Phase 11: Structured Medication Review & DRP Management (PCNE Classification)                  |
|  - Phase 12: Medication Reconciliation Engine (Hospital Transition of Care)                       |
|  - Phase 13: Pharmaceutical Care & Longitudinal SOAP Notes                                        |
|  - Phase 14: Chronic Disease Medication Management (DM, HT, CKD, COPD, Asthma)                    |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 3: COMMUNITY CARE & RATIONAL DRUG USE (Phases 15 - 16, 26 - 27)                         |
|  - Phase 15: Home Medication Review (HMR) with GPS & Photo Evidence Support                       |
|  - Phase 16: Rational Drug Use (RDU) & Antibiotic Stewardship Dashboard                           |
|  - Phase 26: SOP & Document Control Center                                                        |
|  - Phase 27: Dynamic Quality KPI Engine (Automated JHCIS & Verified Entry)                        |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 4: INVENTORY, FEFO & DRUG SAFETY STORAGE (Phases 17 - 22)                               |
|  - Phase 17: Drug Inventory & Electronic Stock Card (Real-time FEFO Engine)                       |
|  - Phase 18: Drug Procurement & Requisition Workflow (Mother Hospital - PCU)                     |
|  - Phase 19: Expiry & Waste Management Dashboard (≤30, 31-90, 91-180 Days)                        |
|  - Phase 20: Cold Chain Management & Temperature Monitoring Logs (Morning/Afternoon)              |
|  - Phase 21: High Alert Medications (HAM) & LASA Tall Man Lettering System                        |
|  - Phase 22: Medication Incident, Near Miss & Root Cause Analysis (NCC MERP Index)                |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 5: QUALITY STANDARDS, SELF-ASSESSMENT & EVIDENCE (Phases 23 - 25, 28 - 29)              |
|  - Phase 23: Quality Standard Engine (Configurable 2568-2570 Standard Specs)                     |
|  - Phase 24: Standard Self-Assessment & Gap Analysis Workflow                                     |
|  - Phase 25: Evidence Center Repository (Zero-Duplication Many-to-Many Architecture)              |
|  - Phase 28: Executive PCU Dashboard (Traffic Light & Clinical Alerts)                            |
|  - Phase 29: District / CUP Multi-Facility Dashboard (Network Level)                              |
+---------------------------------------------------------------------------------------------------+
| MILESTONE 6: REPORTING, SECURITY HARDENING & PRODUCTION (Phases 30 - 35)                          |
|  - Phase 30: Comprehensive Reporting Engine (Excel, CSV, Print-Ready HTML/PDF)                    |
|  - Phase 31: Cybersecurity Hardening, Input Sanitization & Penetration Testing                    |
|  - Phase 32: Performance & Query Index Optimization                                               |
|  - Phase 33: Automated Backup & Disaster Recovery Verification                                    |
|  - Phase 34: User Acceptance Testing (UAT) with 13 Seed Clinical Scenarios (Training Mode)        |
|  - Phase 35: Production Readiness Review, Documentation & Go-Live                                 |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. รายละเอียดและเกณฑ์การตรวจรับของ PHASE 1 (Architecture & Project Foundation)

เมื่อผู้ว่าจ้าง/ผู้ใช้งานอนุมัติแผน Phase 0 จะเข้าสู่ **PHASE 1** โดยมีขอบเขตงานดังนี้:
1. **การวางโครงสร้างระบบ (Project Directory Structure):**
   * `app/Core/`: Router, Controller, Database, Request, Response, Session, CSRF, View Engine
   * `app/Gateway/`: JHCIS Gateway, JHCIS Connection Pool, Encoding Normalizer, Cache
   * `app/Models/`: ActiveRecord / Repository Pattern models
   * `app/Services/`: Clinical Rule Engine, Medication Safety, RDU Calculator, KPI Engine
   * `app/Views/`: Thai Healthcare Theme, Responsive Layouts (Sarabun/Noto Sans Thai font)
   * `public/`: Assets (CSS, JS, Icons), `index.php` (Single Front Controller)
   * `config/`: Database, App, Security, JHCIS config
   * `database/migrations/`: SQL migration files
   * `database/seeds/`: Master and Training mode mock seeds
2. **การตั้งค่า Environment (`.env`):**
   * ค่าเชื่อมต่อ JHCIS Read-Only (Port 3333)
   * ค่าเชื่อมต่อ Application DB (Port 3306)
   * `APP_MODE=training` หรือ `production`
   * `APP_KEY` สำหรับ Session Encryption
3. **การทดสอบความพร้อมในการเชื่อมต่อ (Connectivity Verification):**
   * ทดสอบ JHCIS Gateway ต่อตรงเข้า port 3333 ดึงรายการยา 6,712 รายการ และแสดงผล Health Status

---
*เอกสารนี้จัดทำเพื่อการขออนุมัติดำเนินการเข้าสู่ Phase 1 ตาม AI Development Protocol*
