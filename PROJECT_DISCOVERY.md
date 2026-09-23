# PROJECT DISCOVERY & ENVIRONMENT ASSESSMENT REPORT
## ระบบบริหารจัดการด้านยาและเภสัชกรรมปฐมภูมิสำหรับโรงพยาบาลส่งเสริมสุขภาพตำบล (PCU Smart Pharmacy)
**วันและเวลาที่ทำการสำรวจ:** 23 กันยายน 2569  
**สถานะการประเมิน:** PHASE 0 — DISCOVERY COMPLETE  
**เอกสารเลขที่:** AGY-PCU-DISCOVERY-001  

---

## 1. บทนำและวัตถุประสงค์ (Executive Summary)
การสำรวจสถาปัตยกรรมและสภาพแวดล้อมการทำงานของระบบ (Environment Discovery) จัดทำขึ้นเพื่อเป็นฐานข้อมูลตั้งต้นสำหรับการออกแบบและพัฒนา **PCU Smart Pharmacy & Medication Management System** ซึ่งมีแนวคิดหลักคือ:
> **"One Patient – One Pharmaceutical Profile – One Medication Plan – One Primary Care Network"**

เป้าหมายสูงสุดคือการยกระดับ รพ.สต. และหน่วยบริการปฐมภูมิให้มีระบบสารสนเทศด้านยาที่ปลอดภัย ได้มาตรฐานวิชาชีพเภสัชกรรมปฐมภูมิ สอดรับกับมาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570 และเกณฑ์ RDU Community โดยเชื่อมโยงกับฐานข้อมูลสารสนเทศสุขภาพชุมชนระดับปฐมภูมิ (JHCIS) เดิมอย่างปลอดภัยสูงสุดตามหลัก PDPA และ Cybersecurity

---

## 2. สรุปผลการตรวจสอบสภาพแวดล้อมการทำงานจริง (Runtime & Hardware Environment)

จากการรันคำสั่งตรวจสอบระบบบนเครื่อง Host ปรากฏผลดังนี้:

| รายการทรัพยากร | รายละเอียดทางเทคนิคที่ตรวจพบ | สถานะ / ความพร้อม |
|---|---|---|
| **ระบบปฏิบัติการ (OS)** | Microsoft Windows (x64) | พร้อมใช้งาน |
| **Web Server / Stack** | Apache 2.4.x ติดตั้งผ่าน XAMPP (`C:\xampp`) | พร้อมใช้งาน |
| **PHP Runtime** | PHP 8.2.12 (cli / ZTS Visual C++ 2019 x64) | สมบูรณ์มาก รองรับ Type Safety, Enums, Attributes, Readonly Properties |
| **Node.js Runtime** | Node.js v20.17.0 (LTS) | รองรับ Build Tools, Vite, Next.js หรือ Tooling |
| **Dependency Manager** | Composer 2.7.6 | พร้อมสำหรับจัดการ PHP Libraries |
| **MySQL Instance 1 (JHCIS)** | Service: `MySQL56_JHCIS`<br>Path: `C:\Program Files\JHCIS\MySQL5.6\bin\mysqld`<br>Host: `127.0.0.1`<br>Port: `3333`<br>Database: `jhcisdb`<br>Authentication: Verified (Port 3333 Active) | **ตรวจพบฐานข้อมูล JHCIS ของจริง กำลังรันอยู่บนพอร์ต 3333** |
| **MySQL Instance 2 (Application)** | XAMPP MariaDB/MySQL 10.4/8.0<br>Host: `127.0.0.1`<br>Port: `3306`<br>Database ที่จะสร้างใหม่: `pcu_pharmacy` | พร้อมใช้งานสำหรับการสร้าง Application Database แยกต่างหาก |
| **โครงสร้างโฟลเดอร์โครงการ** | `c:\xampp\htdocs\hos` | พร้อมสำหรับการวางโครงสร้างระบบ |

---

## 3. ผลการตรวจสอบ Schema ฐานข้อมูล JHCIS จริง (JHCIS Schema Discovery)

จากการเชื่อมต่อไปยัง `MySQL56_JHCIS` (Port 3333, DB `jhcisdb`) และใช้คำสั่ง `SHOW TABLES` และ `DESCRIBE` ตามข้อกำหนด **"ห้ามเดาชื่อตารางหรือ Column"** สรุปข้อมูลจริงได้ดังนี้:

### 3.1 ตารางเวชระเบียนและผู้ป่วย (Patient & Demographic)
* **`person` (ตารางหลักของผู้รับบริการ):**
  * คีย์หลัก: `pcucodeperson` (char 5) + `pid` (int 11)
  * ข้อมูลจำเพาะ: `idcard` (เลขประจำตัวประชาชน 13 หลัก), `prename`, `fname`, `lname`, `birth` (วันเกิด), `sex` (เพศ), `bloodgroup`, `bloodrh`, `allergic` (ข้อความสรุปแพ้ยา), `hcode` (รหัสบ้าน), `rightcode` (สิทธิการรักษา), `hosmain` (รพ.แม่ข่าย), `hossub`, `telephoneperson`, `mobile`
* **`personchronic` (ตารางผู้ป่วยโรคเรื้อรัง NCDs):**
  * คีย์: `pcucodeperson`, `pid`, `chroniccode` (รหัสโรคเรื้อรัง/ICD-10)
  * ข้อมูล: `datefirstdiag`, `typedischart`, `chronicclinic`
* **`cchronic` (ตารางกลุ่มโรคเรื้อรังมาตรฐาน JHCIS):**
  * ตรวจพบ 17 กลุ่มโรคหลัก (เช่น 01=ความดันโลหิตสูง, 02=เบาหวาน, 03=หัวใจขาดเลือด, 04=อัมพาต, 06=ไต, 10=หอบหืด, 11=ถุงลมโป่งพอง ฯลฯ)

### 3.2 ตารางการรับบริการและการวินิจฉัย (Visit & Diagnosis)
* **`visit` (ตารางการเข้ารับบริการ):**
  * คีย์หลัก: `pcucode` (char 5) + `visitno` (int 11)
  * ข้อมูลผู้รับบริการ: `pcucodeperson`, `pid`, `visitdate` (วันที่รับบริการ), `timestart`, `timeend`
  * สัญญาณชีพ (Vital Signs): `weight`, `height`, `waist`, `pressure` (ความดันโลหิต), `temperature`, `pulse`, `respri` (อัตราหายใจ), `o2satuation` (SpO2)
  * อาการและบริการ: `symptoms` (อาการสำคัญ), `vitalcheck`, `refer` (การส่งต่อ), `refertohos`, `rightcode`
* **`visitdiag` (ตารางการวินิจฉัยโรคในแต่ละ Visit):**
  * คีย์: `pcucode`, `visitno`, `diagcode` (ICD-10)
  * ข้อมูล: `dxtype` (1=Principal Diag, 2=Comorbidity, 3=Complication, 4=Other, 5=External Cause), `doctordiag`, `appointdate`

### 3.3 ตารางยาและการสั่งใช้ยา (Medication & Dispensing)
* **`cdrug` (ตารางบัญชียาหลักของ JHCIS):**
  * ตรวจพบจำนวนรายการยาในระบบ: **6,712 รายการ**
  * คีย์: `drugcode` (char 24)
  * รายละเอียด: `drugname` (ชื่อการค้าและขนาด), `drugnamethai`, `druggenericname` (ชื่อสามัญทางยา), `pack`, `unitsell`, `unitusage`, `cost`, `sell`, `antibio` (Flag ยาปฏิชีวนะ), `lotno`, `dateexpire`, `drugcaution`, `tmtcode` (รหัสมาตรฐาน TMT), `drugforspecialdisease`
* **`visitdrug` (ตารางการจ่ายยาในแต่ละ Visit):**
  * คีย์: `pcucode`, `visitno`, `drugcode`
  * ข้อมูล: `unit` (จำนวนที่จ่าย), `dose` (วิธีใช้ยา/ขนาดการบริหารยา), `costprice`, `realprice`, `doctor1`
* **`sysdrugdose` (ตารางวิธีใช้ยามาตรฐาน):**
  * คีย์: `pcucode`, `drugcode`, `doseno`
  * รายละเอียด: `dosedescription`, `doseprefix`

### 3.4 ตารางการแพ้ยาและข้อควรระวัง (Drug Allergy & Warnings)
* **`personalergic` (ตารางประวัติแพ้ยาของผู้ป่วย):**
  * คีย์: `pcucodeperson`, `pid`, `drugcode`
  * ข้อมูล: `daterecord` (วันที่บันทึก), `typedx` (ประเภทการวินิจฉัยการแพ้), `levelalergic` (ระดับความรุนแรง), `symptom` (รหัสอาการแพ้), `allergicsymtomps` (ข้อความอาการแพ้), `informant` (ผู้ให้ข้อมูล), `informhosp` (รพ.ที่รายงาน), `remark`
* **`cdrugallergysymtom` (ตารางอาการแพ้ยามาตรฐาน):**
  * มีข้อมูล 39 กลุ่มอาการแพ้ (เช่น ผื่นลมพิษ, Angioedema, Anaphylaxis, SJS/TEN ฯลฯ)
* **`cdrugconflictdrug` & `cdiseaseconflictdrug`:**
  * ตาราง Master สำหรับบันทึกข้อห้ามใช้ระหว่างยากับยา และยากับโรค

---

## 4. ข้อค้นพบสำคัญ (Key Findings & Architecture Implications)

1. **JHCIS Data Security & Isolation Requirement:**
   * สถาปัตยกรรมต้องแยก JHCIS Database ออกจาก Web Application ชัดเจน โดยห้าม Web Browser หรือโค้ดหน้าบ้านเรียกตรงไปยัง JHCIS MySQL (Port 3333)
   * ต้องสร้าง **JHCIS Read-Only API Gateway** เป็นด่านหน้า (Abstraction Layer) เพื่อป้องกัน Database Locking และการแทรกแซงข้อมูลเดิมของหน่วยบริการ
   * สิทธิ์การต่อ JHCIS Database ในระดับ Gateway ต้องจำกัดเป็น `SELECT` เท่านั้น และไม่อนุญาตให้ใช้ root account ในการ Query ประจำวัน
2. **Character Set Encoding Management:**
   * ระบบ JHCIS ดั้งเดิมใช้ MySQL 5.6 ร่วมกับ UTF-8 / TIS-620 ในบาง Table จึงต้องมี Data Normalization Layer ใน API Gateway เพื่อให้มั่นใจว่าข้อมูลภาษาไทยทั้งหมดจะถูกแปลงเป็น UTF-8 สม่ำเสมอ
3. **Training & Simulation Mode (`APP_MODE=training`):**
   * ในเครื่องทดสอบ ข้อมูลประชากรและ Visit ใน `jhcisdb` มีจำนวน 0 รายการ (แต่มีแคตตาล็อกยา `cdrug` ครบ 6,712 รายการ)
   * แสดงให้เห็นถึงความจำเป็นเร่งด่วนของ **Training Mode / Seed Clinical Scenarios Generator** เพื่อให้บุคลากรสามารถฝึกอบรม, ทดสอบระบบ, ตรวจสอบ Clinical Decision Support และเตรียม UAT ได้โดยไม่ต้องแตะต้องข้อมูลผู้ป่วยจริงตามกฎหมาย PDPA

---

## 5. แผนผังการกระจายความรับผิดชอบของระบบ (System Landscape)

```
+-------------------------------------------------------------------------------+
|                             CLIENT BROWSER (UI/UX)                            |
|        PCU Smart Pharmacy Web Interface (Thai UI, Responsive, Role-based)     |
+---------------------------------------+---------------------------------------+
                                        | HTTPS / REST / Session
                                        v
+-------------------------------------------------------------------------------+
|                       PCU SMART PHARMACY CORE ENGINE                          |
|  - Patient Profile & Timeline          - Medication Reconciliation Engine     |
|  - Medication Safety Decision Support  - Chronic Disease & HMR Module         |
|  - Medication Review & DRP             - Inventory / FEFO / Cold Chain / HAM  |
|  - RDU & Antibiotic Stewardship        - Quality Standard Engine & Audit Log  |
+-------------------+-----------------------------------+-----------------------+
                    |                                   |
         Read-Only API Calls                     SQL CRUD Operations
                    v                                   v
+---------------------------------------+   +-----------------------------------+
|          JHCIS API GATEWAY            |   |       APPLICATION DATABASE        |
|  (Microservice / Secure Gateway)      |   |   (MySQL Port 3306: pcu_pharmacy) |
|  - Prepared Statement Read-Only       |   |  - Clinical Notes / DRP / Plans   |
|  - CID Masking & PDPA Filtering       |   |  - Quality Self-Assessments       |
|  - In-memory Query Caching            |   |  - Evidence Files Metadata        |
+-------------------+-------------------+   |  - Inventory & Cold Chain Logs    |
                    |                       |  - Incident & Safety Logs         |
          Port 3333 | Read-Only             |  - Configurable Rules & KPIs      |
                    v                       +-----------------------------------+
+---------------------------------------+
|          JHCIS DATABASE (Port 3333)   |
|  (Existing EHR: person, visit, cdrug, |
|   visitdrug, personalergic, chronic)  |
+---------------------------------------+
```

---
*เอกสารนี้ได้รับการตรวจสอบความถูกต้องร่วมกับผลสำรวจเครื่องแม่ข่าย ณ วันที่จัดทำ*
