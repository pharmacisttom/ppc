# PHARMACY STANDARD TRACEABILITY MATRIX
## เมทริกซ์ความสอดคล้องด้านเภสัชกรรมปฐมภูมิและความปลอดภัยด้านยา (Traceability Matrix)
**เอกสารเลขที่:** AGY-PCU-PHARM-003  
**มาตรฐานอ้างอิง:** มาตรฐานวิชาชีพเภสัชกรรมปฐมภูมิ, แนวทางการจัดการด้านยาในระบบบริการปฐมภูมิ, เกณฑ์ RDU Community (สธ.), กฎหมายคุ้มครองข้อมูลส่วนบุคคล (PDPA)  

---

## 1. ปรัชญาความปลอดภัยทางคลินิก (Clinical Safety Disclaimer & Principle)

> [!IMPORTANT]
> **ระบบสนับสนุนการตัดสินใจทางคลินิก (Clinical & Pharmaceutical Decision Support Only):**  
> ระบบ PCU Smart Pharmacy ทำหน้าที่เป็นเครื่องมือเฝ้าระวัง แจ้งเตือน และสนับสนุนข้อมูลทางเภสัชกรรมแก่บุคลากรทางการแพทย์  
> **ระบบต้องไม่สั่ง หยุดยา, เพิ่มยา, ลดขนาดยา หรือเปลี่ยนยาโดยอัตโนมัติ**  
> ข้อความแจ้งเตือนทั้งหมดในระบบจะใช้ระดับ:
> 1. `INFO` (ข้อมูลเพื่อทราบ)
> 2. `REVIEW` (ควรทบทวนเพิ่มเติม)
> 3. `HIGH` (ความเสี่ยงสูงทางคลินิก)
> 4. `CRITICAL` (ความเสี่ยงวิกฤติต้องยืนยันก่อนดำเนินการ)  
> **การตัดสินใจขั้นสุดท้าย (Final Decision) เป็นของแพทย์, เภสัชกร หรือบุคลากรวิชาชีพผู้มีอำนาจตามกฎหมายเท่านั้น**

---

## 2. เมทริกซ์การตรวจสอบย้อนกลับมาตรฐานด้านยา (Standard Traceability Matrix)

ตารางนี้เป็นหัวใจสำคัญตามข้อกำหนด **"ทุก Feature ที่สร้างเพื่อรองรับมาตรฐานต้องตอบได้ว่า Requirement ไหน, Module ไหน, ข้อมูลมาจากไหน, ใครรับผิดชอบ, Evidence คืออะไร, KPI คืออะไร, ผลปัจจุบันเป็นอย่างไร, Gap คืออะไร และ Corrective Action คืออะไร"**

| ข้อกำหนดมาตรฐาน (Standard Requirement) | โมดูลระบบ (System Module) | แหล่งข้อมูล (Data Source) | ตัวชี้วัดที่เกี่ยวข้อง (KPI) | เอกสารหลักฐานเชิงประจักษ์ (Evidence) | ผู้รับผิดชอบ (Responsible Role) | สถานะการประเมิน (Assessment Status) | ช่องว่างปัจจุบัน (Identified Gap) | มาตรการแก้ไข (Corrective Action) |
|---|---|---|---|---|---|---|---|---|
| **REQ-01: การคัดกรองและเฝ้าระวังประวัติแพ้ยา (Drug Allergy Management)** | `Module: Drug Allergy Engine` | JHCIS: `personalergic`, `cdrugallergysymtom`, `cdrug` | - อัตราการเกิดแพ้ยาซ้ำ (Zero Recurrent ADR)<br>- ร้อยละการบันทึกประวัติแพ้ยาครบถ้วนสมบูรณ์ | - Log การแจ้งเตือนประวัติแพ้ยา<br>- รายงาน ADR รายบุคคล<br>- รายงานสถิติการทบทวนการแพ้ยา | เภสัชกรปฐมภูมิ / พยาบาลคัดกรอง | In Progress (Phase 0 Setup) | ฐานข้อมูล JHCIS เดิมบันทึกเป็น Free-text ในบางครั้ง ทำให้ค้นหา Cross-sensitivity ยาก | ติดตั้ง Master Mapping Group (Cross-sensitivity table เช่น Penicillin - Cephalosporin) |
| **REQ-02: ระบบทบทวนการใช้ยา (Structured Medication Review)** | `Module: Medication Review & DRP` | JHCIS: `visit`, `visitdrug`, `visitdiag` + App DB: `medication_reviews` | - ร้อยละผู้ป่วยโรคเรื้อรังที่ใช้ยาตั้งแต่ 5 รายการขึ้นไปได้รับการทำ Medication Review | - แบบบันทึก DRP ตามมาตรฐาน PCNE<br>- สรุปผล Prescriber Response | เภสัชกรปฐมภูมิ / District Pharmacist | In Progress | รพ.สต. ขาดแบบฟอร์ม DRP ที่เป็นมาตรฐานสากล | พัฒนาฟอร์มจำแนก DRP 11 กลุ่ม พร้อมบันทึกข้อเสนอแนะและผลลัพธ์ |
| **REQ-03: การประสานรายการยาในรอยต่อการดูแล (Medication Reconciliation)** | `Module: Med Reconciliation` | JHCIS: `visitdrug`, `visitrefer` + App DB: `medication_reconciliations` | - อัตราการทำ Med Reconcile ในผู้ป่วยส่งกลับจาก รพ.แม่ข่าย (เป้าหมาย ≥ 80%) | - Reconciled Medication List<br>- บันทึกการจำแนก Discrepancy (Intended vs Unintended) | เภสัชกรปฐมภูมิ / พยาบาลวิชาชีพ | In Progress | ข้อมูลรายการยาจาก รพ.แม่ข่าย มักส่งมาเป็นกระดาษหรือไม่ได้เชื่อมต่อ | พัฒนาหน้าจอเปรียบเทียบยาเดิม-ยาใหม่ พร้อมฟังก์ชัน Import/Type-in และจับคู่ยาอัตโนมัติ |
| **REQ-04: การบริบาลเภสัชกรรมผู้ป่วยโรคเรื้อรัง (Pharmaceutical Care & Adherence)** | `Module: Pharmaceutical Care` | JHCIS: `personchronic`, `visit` + App DB: `pharmaceutical_cares` | - ร้อยละผู้ป่วย DM/HT ที่ควบคุมระดับโรคได้ดี<br>- ร้อยละผู้ป่วยที่มีผลประเมิน Adherence ผ่านเกณฑ์ | - บันทึก SOAP Note ทางเภสัชกรรม<br>- ผลคะแนนแบบประเมินความร่วมมือในการใช้ยา | เภสัชกรปฐมภูมิ | In Progress | การบันทึกปัญหาการใช้ยายังกระจัดกระจายอยู่ในช่อง Symptom หรือ Diag Note | จัดทำ Pharmaceutical Care Timeline รายบุคคล รวบรวมข้อมูลทุก Visit ในหน้าเดียว |
| **REQ-05: การเยี่ยมบ้านด้านยา (Home Medication Review - HMR)** | `Module: Home Medication Review` | JHCIS: `visithomehealthindividual` + App DB: `home_medication_reviews` | - จำนวนผู้ป่วยติดบ้าน/ติดเตียงและกลุ่มเสี่ยงที่ได้รับบริการ HMR | - รายงาน HMR พร้อมภาพถ่ายยาเหลือใช้ที่บ้านและสภาพการจัดเก็บ<br>- พิกัด GPS บ้านผู้ป่วย | เภสัชกรปฐมภูมิ / ทีมหมอคนที่ 2 (พยาบาล/จนท.สาธารณสุข) | In Progress | ยังไม่มีระบบคัดกรองยาหมดอายุและยาซ้ำซ้อนที่ตรวจพบในตู้ยาประจำบ้าน | สร้างระบบบันทึก Actual Meds Found at Home แยกต่างหากจากยาที่สั่งจ่ายใน รพ.สต. |
| **REQ-06: การใช้ยาอย่างสมเหตุผลในชุมชนและสถานบริการ (RDU & Stewardship)** | `Module: RDU & Antibiotic Stewardship` | JHCIS: `visit`, `visitdiag`, `visitdrug`, `cdrug` | - อัตราการใช้ยาปฏิชีวนะใน URI (เป้าหมาย ≤ 20%)<br>- อัตราการใช้ยาปฏิชีวนะใน Acute Diarrhea (เป้าหมาย ≤ 20%) | - Dashboard แสดงแนวโน้มการสั่งใช้ยาปฏิชีวนะ<br>- รายงาน Prescription Indicator แยกตามผู้สั่งใช้ | เภสัชกรปฐมภูมิ / ผู้สั่งใช้ยา / ผอ.รพ.สต. | In Progress | รพ.สต. ไม่สามารถตรวจดูตัวชี้วัด RDU แบบ Real-time ได้ ต้องรอสรุปรายไตรมาส | สร้าง Real-time RDU Calculation Engine ดึงข้อมูล Visit JHCIS มาแสดงผลบน Dashboard |
| **REQ-07: การบริหารจัดการคลังยาตามหลัก FEFO (Inventory Management)** | `Module: Drug Inventory & Stock` | JHCIS: `cdrug` + App DB: `stock_lots`, `stock_movements` | - อัตราสต็อกยาขาดคราว (Stockout Rate = 0%)<br>- สัดส่วนมูลค่ายาหมดอายุต่อมูลค่ายาคงคลัง (เป้าหมาย < 0.5%) | - Stock Card ทางอิเล็กทรอนิกส์<br>- บันทึกตรวจนับคลังยาประจำเดือน/ประจำปี | เจ้าพนักงานเภสัชกรรม / ผู้ดูแลคลังยา | In Progress | การเบิกจ่ายยาใน JHCIS ดั้งเดิมไม่ได้ผูกกับ Lot No. และ Expiry Date แบบ Real-time FEFO | ออกแบบ Stock Lot Management บังคับตัด Lot ยาที่หมดอายุก่อนเป็นลำดับแรก (FEFO) |
| **REQ-08: การควบคุมระบบลูกโซ่ความเย็น (Cold Chain Management)** | `Module: Cold Chain` | App DB: `cold_chain_temperature_logs`, `cold_chain_units` | - ร้อยละของรอบการตรวจอุณหภูมิที่สมบูรณ์ (เป้าหมาย 100% 2 ครั้ง/วัน)<br>- อัตราการรายงานเหตุอุณหภูมิหลุดเกณฑ์ (100% Excursion Response) | - กราฟบันทึกอุณหภูมิตู้เย็นเก็บยา/วัคซีน (2-8 °C)<br>- เอกสารบันทึกมาตรการแก้ไขกรณีอุณหภูมิหลุดเกณฑ์ | ผู้รับผิดชอบงานคลังยาและวัคซีน | In Progress | การจดอุณหภูมิลงกระดาษหน้าตู้เย็น เสี่ยงต่อการสูญหายและไม่มีการแจ้งเตือนทันที | พัฒนาระบบบันทึกอุณหภูมิเช้า-เย็น พร้อมระบบแจ้งเตือน Excursion อัตโนมัติ |
| **REQ-09: การจัดการยากลุ่มเสี่ยงสูงและยาชื่อพ้องมองคล้าย (HAM & LASA)** | `Module: HAM & LASA Safety` | JHCIS: `cdrug` + App DB: `high_alert_drugs`, `lasa_drugs` | - ร้อยละของการติดสัญลักษณ์แจ้งเตือน Tall Man Lettering และ Warning Label (100%) | - บัญชีรายชื่อ HAM/LASA ประจำปี<br>- ภาพถ่ายการจัดเก็บยาแยกจุดและป้ายสัญลักษณ์เตือนภัย | เภสัชกรปฐมภูมิ / เจ้าพนักงานเภสัชกรรม | In Progress | เจ้าหน้าที่หน้าห้องยายังต้องอาศัยการสังเกตด้วยสายตา เสี่ยงต่อการหยิบยาผิด | ติดตั้งระบบแปลงชื่อยาอัตโนมัติด้วย Tall Man Lettering (เช่น predniSONE vs prednisoLONE) |
| **REQ-10: การจัดการชุดยาฉุกเฉิน (Emergency Medication Management)** | `Module: Emergency Drug Kit` | App DB: `emergency_drug_sets`, `emergency_drug_inspections` | - ความพร้อมใช้ของชุดยาฉุกเฉินและกล่องช่วยฟื้นคืนชีพ (100% Ready-to-use) | - Checklist การตรวจสอบยาหมดอายุและจำนวนตามเกณฑ์รายเดือน/หลังใช้งาน | พยาบาลห้องฉุกเฉิน / เภสัชกรผู้ตรวจสอบ | In Progress | กล่องยาฉุกเฉินอาจมียาหมดอายุหรือยาไม่ครบหากไม่มีระบบเตือนรอบตรวจ | พัฒนาระบบ Checklist อิเล็กทรอนิกส์ พร้อมแจ้งเตือนก่อนยาล็อตฉุกเฉินหมดอายุ 90 วัน |
| **REQ-11: การจัดการอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incident)** | `Module: Medication Incident & CQI` | App DB: `medication_incidents`, `corrective_actions` | - อัตราการรายงาน Near Miss เพื่อการพัฒนาเชิงป้องกัน (Target > 10 รายการ/ปี)<br>- ร้อยละการแก้ไขปัญหาเชิงระบบหลังเกิด Incident (100% RCA Closure) | - รายงาน Medication Error Report<br>- เอกสารบันทึกการวิเคราะห์สาเหตุที่แท้จริง (RCA) และมาตรการป้องกัน | คณะกรรมการบริหารความเสี่ยง รพ.สต. | In Progress | บุคลากรมีความกังวลเรื่องการถูกตำหนิ จึงไม่กล้ารายงานข้อผิดพลาด | ออกแบบระบบรายงานอุบัติการณ์แบบ Non-punitive / Anonymous Option พร้อมหมวดหมู่ NCC MERP |
| **REQ-12: การบริการเภสัชกรรมทางไกล (Telepharmacy)** | `Module: Telepharmacy Support` | App DB: `telepharmacy_sessions` + JHCIS Patient ID | - ร้อยละผู้ป่วยที่ได้รับ Telepharmacy มีความเข้าใจการใช้ยา (Teach-back score ≥ 80%) | - บันทึกการยินยอมรับบริการ (Informed Consent)<br>- บันทึกผลการให้คำปรึกษาทางไกลและการส่งมอบยา | เภสัชกรผู้ให้บริการ Telepharmacy | In Progress | ยังไม่มีระบบบันทึกเวชระเบียนเฉพาะสำหรับการปรึกษาด้านยาทางไกล | ออกแบบฟอร์ม Telepharmacy บันทึก Consent, วิดีโอเลขอ้างอิง, ผล Teach-back และแผนดูแล |
| **REQ-13: การคุ้มครองข้อมูลส่วนบุคคลและประวัติการเข้าถึง (PDPA & Audit Trail)** | `Module: Security & Audit Logging` | App DB: `audit_logs`, `login_logs` + Web Gateway | - อัตราการบันทึก Audit Log ของทุก Transaction ที่เข้าถึงข้อมูลผู้ป่วย (100% Monitored) | - รายงาน Audit Logs การเปิดดูเวชระเบียนรายบุคคล<br>- รายงานสิทธิ์การเข้าถึงแยกตาม Role | Cybersecurity Officer / System Admin | In Progress | JHCIS 5.6 เดิมไม่มีระบบบันทึก Audit Log อย่างละเอียดเมื่อมีการเปิดดูข้อมูล | วางระบบ Gateway บันทึก User, IP, Timestamp, Patient PID และ Action ทุกครั้ง |

---

## 3. กฎเกณฑ์การคัดกรองความปลอดภัยทางยา (Medication Safety Rule Engine Specifications)

ระบบจะทำการประเมินความปลอดภัยโดยอัตโนมัติ (Rule-based Decision Support) ในทุกขั้นตอนที่มีการเรียกดูหรือเตรียมยา โดยครอบคลุม 14 มิติความเสี่ยง:

```
                                    +-----------------------------------------+
                                    |     MEDICATION SAFETY RULE ENGINE       |
                                    +-----------------------------------------+
                                                         |
         +-------------------+---------------------------+---------------------------+-------------------+
         |                   |                           |                           |                   |
         v                   v                           v                           v                   v
+-----------------+ +-----------------+         +-----------------+         +-----------------+ +-----------------+
|  DRUG ALLERGY   | |  POLYPHARMACY   |         |  DRUG-DISEASE   |         | SPECIAL POPULAT.| |  HAM & LASA     |
| - Exact Match   | | - Active Meds≥5 |         | - NSAID in CKD  |         | - Beers Criteria| | - High Alert    |
| - Cross Allergy | | - Repeated Med  |         | - Steroid in DM |         | - Pediatric Dose| | - Look-Alike    |
| - Class Allergy | | - Duplicate Rx  |         | - Beta-block/Ast|         | - Pregnancy Risk| | - Sound-Alike   |
+-----------------+ +-----------------+         +-----------------+         +-----------------+ +-----------------+
```

### การจำแนกระดับการแจ้งเตือน (Risk Levels):
1. **INFO (สีฟ้า/เขียว):** ข้อมูลทางคลินิกทั่วไป เช่น ข้อแนะนำการจัดเก็บยา, คำแนะนำการกินยาก่อน/หลังอาหาร
2. **REVIEW (สีเหลือง):** ควรตรวจสอบเพิ่มเติม เช่น ผู้สูงอายุได้รับยาที่มีฤทธิ์ Anticholinergic, ยาซ้ำซ้อนในกลุ่มเดียวกัน
3. **HIGH (สีส้ม):** ความเสี่ยงสูง เช่น การสั่งใช้ยากลุ่ม High Alert Medication (Insulin, Warfarin), การใช้ยาในผู้ป่วยไตเสื่อม (eGFR < 30)
4. **CRITICAL (สีแดงกระพริบ):** ความเสี่ยงวิกฤต เช่น ประวัติแพ้ยาตรงกัน (Direct Allergy Match), ยาที่มีอันตรกิริยารุนแรงระดับห้ามใช้ร่วมกัน (Contraindicated Drug Interaction)

---
*เอกสารนี้ได้รับการออกแบบและรับรองตามแนวปฏิบัติเภสัชกรรมปฐมภูมิระดับสากล*
