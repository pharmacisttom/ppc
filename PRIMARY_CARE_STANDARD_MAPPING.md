# PRIMARY CARE STANDARD MAPPING SPECIFICATION
## การจัดทำความสอดคล้องตามมาตรฐานหน่วยบริการปฐมภูมิ (พ.ศ. 2568–2570)
**เอกสารเลขที่:** AGY-PCU-STD-002  
**มาตรฐานอ้างอิงหลัก:** มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570 (พ.ร.บ. ระบบสุขภาพปฐมภูมิ พ.ศ. 2562)  
**มาตรฐานอ้างอิงร่วม:** แนวทางการจัดการด้านยาในระบบบริการปฐมภูมิ (สปสช./กรมสนับสนุนบริการสุขภาพ/สภาเภสัชกรรม), RDU Community, กฎหมายคุ้มครองข้อมูลส่วนบุคคล (PDPA พ.ศ. 2562)  

---

## 1. หลักการสำคัญและข้อกำหนดการเปลี่ยนผ่าน (Governance & Policy)

### 1.1 นโยบายการใช้มาตรฐานปัจจุบัน
1. **เกณฑ์มาตรฐานปัจจุบัน:** ระบบต้องยึดถือ **"มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570"** และ **"มาตรฐานวิชาชีพเภสัชกรรมปฐมภูมิ"** เป็นเกณฑ์หลักในการประเมินตนเองและการตรวจประเมิน
2. **ข้อห้ามเด็ดขาด (Strict Prohibition):** **ห้ามนำเกณฑ์ "รพ.สต.ติดดาว" รุ่นเก่ามาใช้แทนมาตรฐานปัจจุบันโดยอัตโนมัติ** เนื่องจากโครงสร้างหมวด ตัวชี้วัด และน้ำหนักคะแนนมีความแตกต่างกันตามกฎหมายระบบสุขภาพปฐมภูมิฉบับใหม่
3. **การเชื่อมโยงข้อมูลในอดีต (Historical Crosswalk):** เกณฑ์ รพ.สต.ติดดาว รุ่นเก่าจะอนุญาตให้ใช้เฉพาะในรูปแบบ "Historical Mapping / Crosswalk Table" เพื่อให้หน่วยบริการสามารถเปรียบเทียบผลงานย้อนหลังหรือตรวจเทียบข้อมูลเดิมได้เท่านั้น

---

## 2. การแมป 5 มิติคุณภาพของระบบบริการปฐมภูมิ (5 Dimensions Mapping)

ระบบ PCU Smart Pharmacy ถูกออกแบบโครงสร้างรองรับ 5 มิติคุณภาพ ดังนี้:

```
                  +-------------------------------------------------------------+
                  |         5 มิติการพัฒนาคุณภาพหน่วยบริการปฐมภูมิ (2568-2570)        |
                  +-------------------------------------------------------------+
                                                 |
         +-------------------+-------------------+-------------------+-------------------+
         |                   |                   |                   |                   |
         v                   v                   v                   v                   v
+-----------------+ +-----------------+ +-----------------+ +-----------------+ +-----------------+
|     มิติที่ 1     | |     มิติที่ 2     | |     มิติที่ 3     | |     มิติที่ 4     | |     มิติที่ 5     |
| การนำองค์กรและ    | | ประชากรเป้าหมาย   | |    ด้านบุคลากร    | |   ระบบบริการสุขภาพ  | | ด้านผลลัพธ์และตัว |
|  การบริหารจัดการ  | | ชุมชน/ผู้มีส่วนได้| | (Workforce &    | | (Service Delivery | |  ชี้วัดคุณภาพ     |
| (Governance &   | | (Community &    | |  Competency)    | |  & Safety)      | |  (Outcomes &    |
|  Leadership)    | |  Stakeholders)  | |                 | |                 | |   KPIs)         |
+-----------------+ +-----------------+ +-----------------+ +-----------------+ +-----------------+
```

### รายละเอียดการแมปแต่ละมิติสู่โมดูลของระบบ:

| มิติคุณภาพ (Standard Dimension) | ข้อกำหนดตามมาตรฐาน (Core Requirements) | โมดูลระบบที่รองรับ (System Modules) | หลักฐานเชิงประจักษ์ (Evidence Type) | ผู้รับผิดชอบ (Responsible Role) |
|---|---|---|---|---|
| **มิติที่ 1: การนำองค์กรและการจัดการ** | 1.1 มีนโยบายด้านระบบยาและ RDU ของหน่วยบริการ<br>1.2 มีคณะกรรมการเภสัชกรรมและการบำบัด (PTC) ระดับปฐมภูมิ/เครือข่าย CUP<br>1.3 มีระบบควบคุมเอกสารและ SOP ด้านยา<br>1.4 มีระบบบริหารจัดการความเสี่ยงและ Medication Incident | - SOP / Document Control Module<br>- Incident & Error Management<br>- CQI & PDCA Module<br>- Policy & PTC Evidence Center | - เอกสารคำสั่งแต่งตั้งคณะกรรมการ PTC/นโยบายยา<br>- ทะเบียน SOP ควบคุม 100%<br>- รายงานการประชุมทบทวนอุบัติการณ์ทางยา (RCA) | ผอ.รพ.สต. / เภสัชกรประจำเครือข่าย / ผู้รับผิดชอบงานคุณภาพ |
| **มิติที่ 2: ประชากรเป้าหมาย ชุมชน และผู้มีส่วนได้ส่วนเสีย** | 2.1 การจัดการยาสำหรับกลุ่มเปราะบาง/ผู้ป่วยโรคเรื้อรัง (NCDs)<br>2.2 ระบบบริบาลเภสัชกรรมที่บ้าน (HMR) ร่วมกับทีม 3 หมอ/อสม.<br>2.3 ชุมชนใช้ยาอย่างสมเหตุผล (RDU Community) และการจัดการยาเหลือใช้ในชุมชน | - Chronic Disease Care Module<br>- Home Medication Review (HMR)<br>- Community RDU & Drug Return<br>- Patient Counseling Module | - บันทึกการเยี่ยมบ้าน HMR พร้อมระบุพิกัด/ภาพถ่ายยาเดิม<br>- บันทึกการให้คำปรึกษาการใช้ยาเทคนิคพิเศษ<br>- ข้อมูลการส่งคืนยาและทำลายยาเหลือใช้ในชุมชน | เภสัชกรปฐมภูมิ / พยาบาลวิชาชีพ / นักวิชาการสาธารณสุข |
| **มิติที่ 3: บุคลากร** | 3.1 บุคลากรผู้ปฏิบัติงานด้านยาได้รับการอบรมตามเกณฑ์<br>3.2 มีการประเมินสมรรถนะ (Competency) การจ่ายยาและคัดกรองความปลอดภัย<br>3.3 บุคลากรผ่านการอบรมเรื่อง High Alert Drugs และ CPR/Emergency drug | - Personnel Competency Module<br>- Training & Certification Tracker<br>- Role-Based Access Control (RBAC) | - ใบประกาศนียบัตร/บันทึกการฝึกอบรมบุคลากร<br>- แบบประเมินสมรรถนะรายบุคคลประจำปี<br>- รายชื่อผู้มีสิทธิ์เข้าถึงระบบและสั่งจ่ายยา | ผู้ดูแลระบบ (Facility Admin) / เภสัชกรพี่เลี้ยง |
| **มิติที่ 4: ระบบบริการ** | 4.1 คัดกรองและบริหารจัดการประวัติแพ้ยาอย่างปลอดภัย (Drug Allergy Safety)<br>4.2 การทบทวนวรรณกรรมยาและแก้ปัญหาจากการใช้ยา (Medication Review & DRP)<br>4.3 การประสานรายการยาเมื่อส่งต่อ (Medication Reconciliation)<br>4.4 การจัดการคลังยาตามหลัก FEFO และควบคุมลูกโซ่ความเย็น (Cold Chain)<br>4.5 การจัดการยากลุ่มเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA) | - Patient Pharmaceutical Profile<br>- Drug Allergy Safety Engine<br>- Medication Review (DRP PCNE)<br>- Medication Reconciliation<br>- Inventory & FEFO Engine<br>- Cold Chain Monitoring Log<br>- HAM & LASA Safety Tools | - บันทึกประวัติแพ้ยาและ Screening Alert Logs<br>- Reconciled Medication List ในใบ Refer<br>- Temp Log ตู้เย็นเก็บวัคซีน/ยา 2 ครั้ง/วัน<br>- Checklist ตรวจสอบชุดยาฉุกเฉินและ High Alert | เภสัชกร / พยาบาล / เจ้าพนักงานเภสัชกรรม |
| **มิติที่ 5: ผลลัพธ์** | 5.1 อัตราการใช้ยาปฏิชีวนะในโรคติดเชื้อทางเดินหายใจส่วนบน (URI) และอุจจาระร่วงเฉียบพลัน (Acute Diarrhea)<br>5.2 อัตราการเกิดความคลาดเคลื่อนทางยา (Medication Errors) ระดับ E ขึ้นไปเป็นศูนย์<br>5.3 ร้อยละผู้ป่วยโรคเรื้อรังที่ได้รับการประเมิน Adherence<br>5.4 อัตราความพร้อมตามมาตรฐานหน่วยบริการปฐมภูมิด้านระบบยา | - Quality KPI Engine<br>- Antibiotic Stewardship Dashboard<br>- Medication Safety Dashboard<br>- Executive & District Dashboard<br>- Quality Readiness & Self-Assessment | - ข้อมูลกราฟแสดงแนวโน้ม KPI รายเดือน<br>- รายงานตัวชี้วัด RDU ส่งออกสปสช./HDC<br>- ผลคะแนนประเมินตนเองพร้อมหลักฐานแนบ | คณะกรรมการบริหาร รพ.สต. / เภสัชกรอำเภอ |

---

## 3. สถาปัตยกรรมเครื่องมือขับเคลื่อนมาตรฐาน (Quality Standard Engine)

เพื่อป้องกันปัญหาข้อจำกัดเดิมที่ระบบมัก Hard-code เกณฑ์การประเมิน ซึ่งทำให้ระบบใช้งานไม่ได้เมื่อกระทรวงสาธารณสุขปรับปรุงเกณฑ์ในแต่ละปีงบประมาณ ระบบ PCU Smart Pharmacy จึงออกแบบ **Quality Standard Engine** แบบ Dynamic Configuration

### โครงสร้างตารางมาตรฐานในระดับ Database:
```sql
CREATE TABLE quality_standards (
    standard_id INT AUTO_INCREMENT PRIMARY KEY,
    standard_year INT NOT NULL COMMENT 'ปี พ.ศ. ที่ประกาศใช้ เช่น 2568, 2569, 2570',
    standard_version VARCHAR(20) NOT NULL COMMENT 'เวอร์ชันเกณฑ์ เช่น 1.0, 2.0',
    title VARCHAR(255) NOT NULL COMMENT 'ชื่อมาตรฐาน เช่น มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568-2570',
    effective_date DATE NOT NULL,
    expiry_date DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quality_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    standard_id INT NOT NULL,
    category_code VARCHAR(20) NOT NULL COMMENT 'เช่น DIM-1, DIM-2, DIM-4',
    category_name VARCHAR(255) NOT NULL COMMENT 'ชื่อมิติ เช่น ระบบบริการสุขภาพและระบบยา',
    ordering INT DEFAULT 1,
    FOREIGN KEY (standard_id) REFERENCES quality_standards(standard_id)
);

CREATE TABLE quality_criteria (
    criterion_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    criterion_code VARCHAR(30) NOT NULL COMMENT 'เช่น 4.3.1',
    criterion_name VARCHAR(255) NOT NULL,
    subcriterion VARCHAR(100) NULL,
    requirement TEXT NOT NULL COMMENT 'ข้อกำหนดมาตรฐานฉบับสมบูรณ์',
    guidance TEXT NULL COMMENT 'คำอธิบายแนวทางปฏิบัติและการตรวจประเมิน',
    evidence_required TEXT NOT NULL COMMENT 'หลักฐานเชิงประจักษ์ที่จำเป็นต้องอัปโหลด',
    weight DECIMAL(5,2) DEFAULT 1.00 COMMENT 'ค่าน้ำหนักคะแนน',
    target DECIMAL(5,2) DEFAULT 100.00 COMMENT 'เป้าหมายความสำเร็จ',
    responsible_role VARCHAR(50) DEFAULT 'PCU Pharmacist',
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES quality_categories(category_id)
);
```

---

## 4. ตารางเปรียบเทียบมาตรฐานเก่า-ใหม่ (Historical Crosswalk: รพ.สต.ติดดาว vs มาตรฐาน 2568–2570)

> **คำเตือนความปลอดภัยของระบบ:** ตารางด้านล่างนี้มีไว้เพื่ออำนวยความสะดวกในการเทียบเคียงเอกสารเดิมเท่านั้น (Historical Crosswalk) มิใช่การนำเกณฑ์เก่ามาตัดสินผลตามมาตรฐานปัจจุบัน

| รหัสเกณฑ์ รพ.สต.ติดดาว (เดิม) | ประเด็นระบบยาเดิม | รหัสเกณฑ์มาตรฐานปฐมภูมิ 2568–2570 | การยกระดับในระบบ PCU Smart Pharmacy |
|---|---|---|---|
| หมวด 2 ข้อ 2.3 (ระบบยา รพ.สต.ติดดาว) | การจัดหายาและการเก็บรักษายา | มิติที่ 4 ข้อ 4.3 (ระบบบริหารจัดการยาและเวชภัณฑ์) | เพิ่มระบบ FEFO Automation, Temperature Excursion Alerts และ Real-time Stock Card |
| หมวด 2 ข้อ 2.4 | ความปลอดภัยในการใช้ยาและการแพ้ยา | มิติที่ 4 ข้อ 4.4 (Medication Safety & High Alert Drugs) | เพิ่ม Screening Rule Engine อัตโนมัติ (LASA, HAM, Renal Risk, Elderly Risk) |
| หมวด 3 ข้อ 3.2 | การเยี่ยมบ้านและดูแลผู้ป่วยที่บ้าน | มิติที่ 2 ข้อ 2.2 (การบริบาลเภสัชกรรมชุมชน HMR) | เพิ่มระบบบันทึก Drug-Related Problems (DRP) เชิงโครงสร้างตามมาตรฐาน PCNE |
| หมวด 4 ข้อ 4.1 | การใช้ยาปฏิชีวนะอย่างสมเหตุผล | มิติที่ 4 และ 5 (RDU Service & Antibiotic Stewardship) | ระบบคำนวณอัตรา RDU อัตโนมัติจากฐานข้อมูล JHCIS พร้อมตัดเกณฑ์ข้อยกเว้นทางคลินิก |

---
*เอกสารนี้กำหนดเป็นบรรทัดฐานสำหรับการพัฒนาโมดูล Self-Assessment และ Evidence Center ในระยะถัดไป*
