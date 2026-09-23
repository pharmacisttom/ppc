# ROLE-BASED ACCESS CONTROL (RBAC) MATRIX
## เมทริกซ์การกำหนดบทบาท สิทธิ์ และการเข้าถึงข้อมูล (RBAC Specification)
**เอกสารเลขที่:** AGY-PCU-RBAC-007  
**มาตรฐานอ้างอิง:** ISO 27001 / HIPAA Access Control, PDPA Security Safeguards, National Healthcare Security Guidelines  

---

## 1. นิยามบทบาทผู้ใช้งานในระบบ (System Roles Definition)

ระบบ PCU Smart Pharmacy กำหนดบทบาทการใช้งานออกเป็น 12 บทบาท เพื่อรองรับการทำงานในเครือข่ายบริการสุขภาพปฐมภูมิ (Primary Care Network / CUP):

| รหัสบทบาท (Role Code) | ชื่อบทบาท (Display Name) | ขอบเขตหน้าที่และความรับผิดชอบหลัก |
|---|---|---|
| `SUPER_ADMIN` | ผู้ดูแลระบบสูงสุดระดับองค์กร | บริหารจัดการระบบแม่ข่าย, จัดการสิทธิ์ทั่วทั้งเครือข่าย, ดูแลฐานข้อมูล |
| `DISTRICT_PHARM` | เภสัชกรประจำเครือข่ายระดับอำเภอ | กำกับดูแลนโยบายยาภาพรวมอำเภอ, ตรวจสอบ RDU, อนุมัติการเบิกยาข้าม รพ.สต. |
| `HOSPITAL_PHARM` | เภสัชกรโรงพยาบาลแม่ข่าย | ประสานงานส่งต่อผู้ป่วย, Med Reconciliation รอยต่อ รพ., ให้คำปรึกษายาเฉพาะทาง |
| `PCU_PHARM` | เภสัชกรปฐมภูมิ (ประจำ รพ.สต.) | บริบาลเภสัชกรรม, Med Review, HMR, คัดกรองความปลอดภัย, ดูแลคลังยาและ HAM |
| `PHARM_TECH` | เจ้าพนักงานเภสัชกรรม | จัดยา, จัดการคลังยา FEFO, บันทึกการรับ-เบิกยา, ตรวจสอบอุณหภูมิตู้เย็น |
| `NURSE` | พยาบาลวิชาชีพ | คัดกรองผู้ป่วย, บริหารยาฉุกเฉิน, ฉีดวัคซีน, รายงานอุบัติการณ์ความคลาดเคลื่อน |
| `PUBLIC_HEALTH` | นักวิชาการสาธารณสุข / จพ.สาธารณสุข | ร่วมทีม 3 หมอเยี่ยมบ้าน (HMR), จัดการสุขศึกษาในชุมชน, คัดกรอง NCDs |
| `FACILITY_ADMIN` | ผู้ดูแลระบบประจำ รพ.สต. | จัดการบัญชีผู้ใช้ในหน่วยบริการ, กำหนดค่าอุปกรณ์และตู้เย็น, ดูแลเครือข่ายภายใน |
| `QUALITY_OFFICER` | ผู้รับผิดชอบงานพัฒนาคุณภาพ | ประเมินตนเองตามมาตรฐานปฐมภูมิ, รวบรวมและจัดเก็บ Evidence, ขับเคลื่อน PDCA |
| `DATA_OFFICER` | เจ้าหน้าที่ข้อมูลและสถิติ | จัดการตัวชี้วัด KPI, ตรวจสอบความถูกต้องของข้อมูล, ส่งออกรายงานทางสถิติ |
| `VIEWER` | ผู้เยี่ยมสำรวจ / นิเทศงาน | ดูรายงานสรุป, ดูแดชบอร์ดความพร้อม, ตรวจดูผลการดำเนินงานแบบ Read-Only |
| `AUDITOR` | ผู้ตรวจสอบอิสระภายนอก | ตรวจสอบ Audit Log, ตรวจสอบหลักฐานเชิงประจักษ์, ประเมินความสอดคล้องตามมาตรฐาน |

---

## 2. ตารางแมปสิทธิ์การเข้าถึงโดยละเอียด (Permission Matrix)

สัญลักษณ์สิทธิ์:
* **C:** Create (สร้าง/บันทึกใหม่)
* **R:** Read (เปิดอ่าน/เรียกดูข้อมูล)
* **U:** Update (แก้ไข/ปรับปรุงข้อมูล)
* **D:** Delete (ลบข้อมูล - มักจำกัดเฉพาะผู้ดูแล)
* **A:** Approve / Verify (อนุมัติ/รับรองความถูกต้อง)
* **X:** Export (ส่งออกรายงาน/พิมพ์เอกสาร)
* **-:** ไม่อนุญาตให้เข้าถึง (No Access)

| โมดูลและสิทธิ์ในระบบ (Permission Code) | SUPER ADMIN | DISTRICT PHARM | HOSPITAL PHARM | PCU PHARM | PHARM TECH | NURSE | PUBLIC HEALTH | FACILITY ADMIN | QUALITY OFFICER | DATA OFFICER | VIEWER | AUDITOR |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1. ผู้ป่วยและประวัติ (Patient & Med Profile)** | | | | | | | | | | | | |
| `patient.view` (ดูเวชระเบียน/ข้อมูลพื้นฐาน) | R | R | R | R | R | R | R | R | R | R | R | - |
| `patient.cid.unmask` (ปลดล็อกดูเลขบัตร ปชช. เต็ม) | R | R | R | R | - | R | - | - | - | - | - | - |
| `medication.view` (ดูประวัติการใช้ยา) | R | R | R | R | R | R | R | - | R | R | R | - |
| `allergy.view` (ดูประวัติแพ้ยา) | R | R | R | R | R | R | R | - | R | - | R | - |
| `allergy.manage` (บันทึก/ปรับปรุงประวัติแพ้ยา) | CRUD | CRUD | CRUD | CRUD | - | CR | - | - | - | - | - | - |
| **2. ความปลอดภัยทางยา (Medication Safety)** | | | | | | | | | | | | |
| `safety.risk.view` (ดูการแจ้งเตือนความเสี่ยงยา) | R | R | R | R | R | R | R | - | R | - | R | - |
| `safety.rule.configure` (แก้ไขกฎคัดกรองความปลอดภัย) | CRUD | CRUD | U | U | - | - | - | - | - | - | - | - |
| `safety.ham_lasa.manage` (จัดการบัญชี HAM/LASA) | CRUD | CRUD | U | CRUD | R | R | - | - | - | - | R | R |
| `safety.emergency.inspect` (ตรวจเช็คกล่องยาฉุกเฉิน) | CRUD | R | R | CRUD | CRU | CRU | - | - | - | - | R | R |
| **3. บริบาลเภสัชกรรม (Pharmaceutical Care)** | | | | | | | | | | | | |
| `review.create` (สร้าง Medication Review/DRP) | CRUD | CRUD | CRUD | CRUD | - | - | - | - | - | - | - | - |
| `review.approve` (รับรองผลการทบทวนยา) | A | A | A | A | - | - | - | - | - | - | - | - |
| `reconciliation.manage` (จัดทำ Med Reconciliation) | CRUD | CRUD | CRUD | CRUD | - | CRU | - | - | - | - | - | - |
| `pharm_care.soap` (บันทึก SOAP Note และแผนยา) | CRUD | CRUD | CRUD | CRUD | - | - | - | - | - | - | - | - |
| `hmr.record` (บันทึกการเยี่ยมบ้านด้านยา HMR) | CRUD | CRUD | R | CRUD | R | CRU | CRU | - | - | - | R | - |
| `counseling.record` (บันทึกการให้คำปรึกษาการใช้ยา) | CRUD | CRUD | CRUD | CRUD | CRU | CRU | - | - | - | - | - | - |
| `telepharmacy.conduct` (ให้บริการ Telepharmacy) | CRUD | CRUD | CRUD | CRUD | - | - | - | - | - | - | - | - |
| **4. คลังยาและลูกโซ่ความเย็น (Inventory & Storage)** | | | | | | | | | | | | |
| `stock.view` (ดูสต็อกยาและวันหมดอายุ) | R | R | R | R | R | R | - | R | - | R | R | R |
| `stock.adjust` (ปรับปรุงยอดสต็อก/ตัดจ่ายชำรุด) | CRUD | A | - | CRUD | CRU | - | - | - | - | - | - | - |
| `requisition.create` (สร้างใบเบิกยา รพ.สต.) | CRUD | - | - | CRUD | CRUD | CR | - | - | - | - | - | - |
| `requisition.approve` (อนุมัติใบเบิกยาจากแม่ข่าย) | A | A | A | - | - | - | - | - | - | - | - | - |
| `coldchain.log` (บันทึกอุณหภูมิตู้เย็น 2 เวลา) | CRUD | R | R | CRU | CRU | CRU | - | - | - | - | R | R |
| `coldchain.excursion` (บันทึกมาตรการแก้ไขอุณหภูมิ) | CRUD | A | A | CRUD | CRU | CRU | - | - | - | - | R | R |
| **5. อุบัติการณ์และพัฒนาคุณภาพ (Incidents & Quality)** | | | | | | | | | | | | |
| `incident.report` (รายงานข้อผิดพลาดทางยา/Near miss)| C | C | C | C | C | C | C | C | C | C | - | - |
| `incident.rca` (วิเคราะห์สาเหตุและปิดเคส RCA) | CRUD | CRUD | A | CRUD | - | U | - | - | U | - | - | R |
| `quality.assess` (ทำการประเมินตนเองตามมาตรฐาน) | CRUD | CRUD | R | CRUD | - | R | - | U | CRUD | - | R | R |
| `evidence.upload` (อัปโหลดหลักฐานเชิงประจักษ์) | CRUD | CRUD | CRUD | CRUD | CRU | CRU | CRU | CRU | CRUD | - | - | - |
| `evidence.review` (ตรวจสอบและอนุมัติหลักฐาน) | A | A | A | A | - | - | - | - | A | - | R | R |
| `kpi.manage` (กำหนดสูตรและบันทึกผลตัวชี้วัด) | CRUD | CRUD | R | CRUD | - | - | - | - | CRUD | CRUD | R | R |
| **6. รายงานและการบริหารระบบ (Reports & Admin)** | | | | | | | | | | | | |
| `report.export` (ส่งออกรายงาน PDF / Excel / CSV) | X | X | X | X | X | X | X | - | X | X | X | X |
| `user.manage` (จัดการผู้ใช้และรหัสผ่าน) | CRUD | - | - | - | - | - | - | CRUD | - | - | - | - |
| `settings.manage` (ตั้งค่าระบบและตัวแปร) | CRUD | - | - | - | - | - | - | U | - | - | - | - |
| `audit.view` (เปิดดูประวัติการเข้าใช้งาน Audit Log) | R | R | - | R | - | - | - | R | - | - | - | R |

---

## 3. นโยบายการตรวจสอบสิทธิ์ในระดับโค้ด (Authorization Middleware Implementation)

ในระดับโครงสร้างของแอปพลิเคชัน ทุกคำขอจะผ่านฟังก์ชันตรวจสอบสิทธิ์ 2 ระดับ:
1. **Role Check (`hasRole('PCU_PHARM')`):** ใช้สำหรับควบคุมการเข้าถึงเมนูภาพรวม
2. **Permission Check (`can('review.create')`):** ใช้ควบคุมสิทธิ์ในระดับปุ่มกด (Action-level Authorization)
3. **Data Scope Check (`belongsToFacility($facility_id)`):** ป้องกันไม่ให้บุคลากรต่าง รพ.สต. เปิดดูข้อมูลภายในของกันและกัน ยกเว้นผู้ใช้ที่มีขอบเขตระดับอำเภอ (`DISTRICT_PHARM`, `SUPER_ADMIN`)

---
*เอกสารนี้ได้รับการออกแบบเพื่อรองรับข้อกำหนด PDPA และการแบ่งแยกหน้าที่ตามหลัก Good Governance*
