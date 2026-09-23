# JHCIS API GATEWAY SPECIFICATION & DATA MAPPING
## ข้อกำหนดการเชื่อมต่อและดึงข้อมูลจากระบบ JHCIS ผ่าน API Gateway
**เอกสารเลขที่:** AGY-PCU-API-006  
**ฐานข้อมูลต้นทาง (Source):** JHCIS MySQL 5.6 (`jhcisdb` บน Port 3333 - Read-Only)  
**รูปแบบการให้บริการ:** RESTful JSON API Gateway (Internal Service / Microservice)  
**นโยบายการเข้าถึง:** Least Privilege (SELECT ONLY), In-memory Query Caching, UTF-8 Transcoding, CID Masking  

---

## 1. หลักการทำงานของ JHCIS API Gateway

JHCIS API Gateway ทำหน้าที่เป็น "ฉนวนกั้น (Isolation Barrier)" ระหว่างเว็บแอปพลิเคชันกับฐานข้อมูล JHCIS โดยมีหน้าที่สำคัญ 5 ประการ:
1. **ป้องกัน Database Locking:** จำกัดจำนวน Concurrent Connection ไปยัง JHCIS MySQL 5.6 และป้องกันคำสั่ง Query หนักที่อาจกระทบต่อโปรแกรม JHCIS บนเคาน์เตอร์บริการ
2. **Read-Only Enforcement:** รับคำขอเฉพาะแบบ GET / Read-Only เท่านั้น ปฏิเสธคำสั่งที่เป็นอันตรายโดยสิ้นเชิง
3. **Data Encoding Normalization:** แปลงชุดตัวอักษรภาษาไทยจาก TIS-620 หรือ UTF-8 เดิมของ JHCIS ให้เป็นมาตรฐาน `UTF-8` ที่สมบูรณ์แบบ
4. **Privacy / CID Masking by Default:** ซ่อนเลขบัตรประชาชน 13 หลัก (`1-xxxx-xxxxx-34-1`) ก่อนส่งออกจาก Gateway ยกเว้นกรณีที่มีการ Authenticate Token เฉพาะทางคลินิก
5. **Circuit Breaker:** ตรวจสอบ Health Check ของ MySQL Port 3333 อย่างต่อเนื่อง หากพอร์ตไม่ตอบสนองภายใน 3 วินาที จะตัดสวิตช์ส่ง Fallback Mock Response เพื่อให้ระบบหน้าบ้านยังเปิดทำงานได้

---

## 2. การจับคู่ฟิลด์ฐานข้อมูลจริงของ JHCIS (Data Field Mapping Table)

จากการตรวจสอบ Schema จริงด้วยคำสั่ง `DESCRIBE` ใน Phase 0 ได้ตารางจับคู่ข้อมูลดังนี้:

| โมเดลข้อมูลระบบใหม่ (API DTO) | ตาราง JHCIS ต้นทาง | ฟิลด์ใน JHCIS (Actual Schema) | ชนิดข้อมูล | หมายเหตุการแปลง (Transformation / Business Logic) |
|---|---|---|---|---|
| **Patient Profile** | `person` | `pcucodeperson`<br>`pid`<br>`idcard`<br>`prename`<br>`fname`<br>`lname`<br>`birth`<br>`sex`<br>`bloodgroup`<br>`telephoneperson`<br>`mobile`<br>`hnomoi`, `mumoi` | char(5)<br>int(11)<br>varchar(13)<br>varchar(20)<br>varchar(25)<br>varchar(35)<br>date<br>varchar(1)<br>varchar(2)<br>varchar(35)<br>varchar(15)<br>varchar | - รวม `prename` + `fname` + `lname` เป็น `full_name`<br>- คำนวณ `age` จาก `birth` เปรียบเทียบกับวันปัจจุบัน<br>- ปกปิด `idcard` เหลือ 4 ตัวท้าย ยกเว้นร้องขอแบบ Authen<br>- `sex`: '1' = ชาย, '2' = หญิง |
| **Chronic Diseases** | `personchronic`<br>JOIN `cchronic` | `chroniccode`<br>`datefirstdiag`<br>`chronicclinic`<br>`cchronic.groupname` | char(7)<br>date<br>char(5)<br>varchar | - แปลงรหัส ICD-10 หรือรหัสกลุ่มโรค 17 กลุ่มเป็นชื่อโรคภาษาไทย (เช่น เบาหวาน, ความดันโลหิตสูง, ไตเรื้อรัง) |
| **Drug Allergy** | `personalergic`<br>LEFT JOIN `cdrug`<br>LEFT JOIN `cdrugallergysymtom` | `personalergic.drugcode`<br>`cdrug.drugname`<br>`cdrug.druggenericname`<br>`personalergic.daterecord`<br>`personalergic.levelalergic`<br>`personalergic.symptom`<br>`personalergic.allergicsymtomps`<br>`personalergic.remark` | char(24)<br>varchar(255)<br>varchar(220)<br>date<br>char(1)<br>char(2)<br>varchar(300)<br>varchar(300) | - `levelalergic`: แปลงเป็นรหัสความรุนแรง (1=สงสัย, 2=แพ้ปานกลาง, 3=รุนแรงมาก/Anaphylaxis)<br>- หากไม่มีข้อมูล ให้ส่งสถานะ `"UNKNOWN_NO_RECORD"` พร้อมข้อความ *"ไม่พบข้อมูลประวัติแพ้ยาในแหล่งข้อมูลที่เชื่อมต่อ"* (ห้ามระบุว่าไม่แพ้) |
| **Patient Visit** | `visit` | `pcucode`<br>`visitno`<br>`visitdate`<br>`timestart`<br>`symptoms`<br>`vitalcheck`<br>`weight`, `height`<br>`pressure`<br>`pulse`, `respri`<br>`o2satuation`<br>`refertohos` | char(5)<br>int(11)<br>date<br>time<br>varchar(500)<br>varchar(500)<br>decimal<br>varchar(7)<br>int(11)<br>tinyint(4)<br>varchar(5) | - คำนวณ BMI อัตโนมัติ: `weight / ((height/100)^2)`<br>- แยกค่า SBP / DBP จากฟิลด์ `pressure` (เช่น `120/80` -> SBP: 120, DBP: 80)<br>- ตรวจสอบว่ามีการส่งต่อไปยัง รพ.แม่ข่าย หรือไม่ |
| **Visit Diagnosis** | `visitdiag` | `diagcode`<br>`dxtype`<br>`doctordiag`<br>`appointdate` | char(7)<br>varchar(2)<br>varchar(35)<br>date | - `dxtype`: 1 = Principal Diag, 2 = Comorbidity, 3 = Complication, 4 = Other, 5 = External Cause<br>- เชื่อมโยงรหัส ICD-10 เพื่อตรวจสอบ RDU (เช่น URI: J00, J02, J06 / Diarrhea: A09) |
| **Visit Prescription** | `visitdrug`<br>JOIN `cdrug`<br>LEFT JOIN `sysdrugdose` | `visitdrug.drugcode`<br>`cdrug.drugname`<br>`cdrug.druggenericname`<br>`cdrug.tmtcode`<br>`cdrug.antibio`<br>`visitdrug.unit`<br>`visitdrug.dose`<br>`visitdrug.realprice` | char(24)<br>varchar(255)<br>varchar(220)<br>varchar(55)<br>char(1)<br>int(11)<br>varchar(100)<br>decimal(11,2) | - `antibio`: '1' = ยาปฏิชีวนะ (ใช้สำหรับคำนวณ Antibiotic Stewardship)<br>- ดึงวิธีใช้ยาจาก `visitdrug.dose` หรือ Fallback ไปยัง `sysdrugdose.dosedescription` |
| **Drug Master Catalog** | `cdrug` | `drugcode`<br>`drugname`<br>`druggenericname`<br>`unitsell`<br>`pack`<br>`cost`, `sell`<br>`tmtcode`<br>`antibio`<br>`drugcaution` | char(24)<br>varchar(255)<br>varchar(220)<br>varchar(15)<br>varchar(255)<br>decimal<br>varchar(55)<br>char(1)<br>varchar(255) | - ดึงรายการยา 6,712 รายการมาเป็น Master Catalog ในระบบ<br>- นำไปแมปกับตาราง HAM และ LASA ในระบบใหม่ |

---

## 3. เอกสารข้อกำหนดของ API Endpoints (Gateway Contract)

### 3.1 การค้นหาผู้ป่วย (Patient Search)
* **Endpoint:** `GET /api/v1/jhcis/patients/search`
* **Query Parameters:**
  * `q` (string, required): คำค้นหา (เลขบัตร ปชช., ชื่อ, สกุล หรือ HN/PID)
  * `type` (string, optional): `auto` (default), `cid`, `name`, `pid`
  * `limit` (int, optional): ค่าเริ่มต้น 20 รายการ
* **Response Example (200 OK):**
```json
{
  "status": "success",
  "data_source": "JHCIS_LIVE",
  "count": 1,
  "patients": [
    {
      "pcu_code": "05432",
      "pid": 10452,
      "cid_masked": "1-1002-xxxxx-34-1",
      "full_name": "นายสมชาย ใจดี",
      "birth_date": "1965-04-12",
      "age": 61,
      "gender": "male",
      "rights_title": "สิทธิบัตรทอง (UC)",
      "chronic_summary": ["ความดันโลหิตสูง (I10)", "เบาหวานชนิดที่ 2 (E11.9)"],
      "allergy_count": 1
    }
  ]
}
```

---

### 3.2 เวชระเบียนภาพรวมด้านยาของผู้ป่วย (Patient Pharmaceutical Profile)
* **Endpoint:** `GET /api/v1/jhcis/patients/{pid}/profile`
* **Response Structure:**
```json
{
  "status": "success",
  "patient_demographic": {
    "pid": 10452,
    "pcu_code": "05432",
    "name": "นายสมชาย ใจดี",
    "cid_masked": "1-1002-xxxxx-34-1",
    "age": 61,
    "gender": "male",
    "blood_group": "O+",
    "weight_kg": 68.5,
    "height_cm": 165.0,
    "bmi": 25.16,
    "latest_bp": "138/84"
  },
  "allergies": [
    {
      "drug_code": "000000000000000000010411",
      "drug_name": "Amoxicillin 500 mg capsule",
      "generic_name": "amoxicillin trihydrate",
      "reaction": "ผื่นลมพิษ แน่นหน้าอก",
      "severity_level": "3 (Severe)",
      "date_recorded": "2024-03-15",
      "reported_by_hosp": "รพ.ระยอง"
    }
  ],
  "chronic_conditions": [
    { "code": "I10", "name": "Essential (primary) hypertension", "first_diagnosed": "2018-06-10" },
    { "code": "E11.9", "name": "Type 2 diabetes mellitus without complications", "first_diagnosed": "2020-01-22" }
  ],
  "current_medications": [
    {
      "drug_code": "1000021",
      "drug_name": "Amlodipine 5 mg tablet",
      "quantity": 90,
      "dose_instruction": "รับประทานครั้งละ 1 เม็ด วันละ 1 ครั้ง หลังอาหารเช้า",
      "last_dispensed_date": "2026-08-15",
      "prescriber": "พญ.วิภาวรรณ"
    },
    {
      "drug_code": "1000055",
      "drug_name": "Metformin 500 mg tablet",
      "quantity": 180,
      "dose_instruction": "รับประทานครั้งละ 1 เม็ด วันละ 2 ครั้ง หลังอาหารเช้า เย็น",
      "last_dispensed_date": "2026-08-15",
      "prescriber": "พญ.วิภาวรรณ"
    }
  ],
  "safety_screening_flags": [
    {
      "risk_code": "ALLERGY_CROSS_RISK",
      "severity": "REVIEW",
      "title": "ประวัติแพ้ยาในกลุ่ม Penicillins",
      "description": "ผู้ป่วยมีประวัติแพ้ Amoxicillin ควรระมัดระวังการสั่งจ่ายยากลุ่ม Cephalosporins"
    }
  ]
}
```

---

### 3.3 ประวัติไทม์ไลน์การใช้ยาย้อนหลัง (Medication Timeline & Visits)
* **Endpoint:** `GET /api/v1/jhcis/patients/{pid}/medication-timeline`
* **Query Parameters:**
  * `months` (int, optional): ค่าเริ่มต้น 12 เดือน
  * `drug_group` (string, optional): กรองกลุ่มยา เช่น `antibiotic`, `ncd`, `nsaid`
* **Response Content:** ส่งออกรายการทุก Visit ที่มีการจ่ายยา พร้อมรายละเอียดการวินิจฉัยโรค (ICD-10) ผู้สั่งจ่ายยา และขนาดรับประทาน

---

### 3.4 รายการยาปฏิชีวนะเพื่อการประเมิน RDU (Antibiotic Prescription Surveillance)
* **Endpoint:** `GET /api/v1/jhcis/surveillance/antibiotic-visits`
* **Query Parameters:**
  * `start_date` (date, required)
  * `end_date` (date, required)
  * `diagnosis_group` (string): `URI`, `DIARRHEA`, `WOUND`, `ALL`
* **SQL Query Logic หลัง Gateway:**
```sql
SELECT 
    v.pcucode, v.visitno, v.visitdate, v.pid,
    vd.diagcode,
    vd.dxtype,
    vd.doctordiag,
    vdr.drugcode,
    cd.drugname,
    cd.druggenericname,
    vdr.unit,
    vdr.dose
FROM visit v
INNER JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
INNER JOIN visitdrug vdr ON v.pcucode = vdr.pcucode AND v.visitno = vdr.visitno
INNER JOIN cdrug cd ON vdr.drugcode = cd.drugcode
WHERE v.visitdate BETWEEN :start_date AND :end_date
  AND (cd.antibio = '1' OR cd.druggenericname IN ('amoxicillin', 'ampicillin', 'ciprofloxacin', 'norfloxacin', 'erythromycin', 'azithromycin', 'doxycycline'))
ORDER BY v.visitdate DESC, v.visitno;
```

---

## 4. นโยบายการสำรองและการทนต่อความผิดพลาด (Failure Handling & Fallback Strategy)

1. **JHCIS Connection Timeout:**
   * Timeout การต่อฐานข้อมูลถูกตั้งไว้ที่ 3.0 วินาที หากเกินกำหนด จะส่ง Error Code `JHCIS_CONNECTION_TIMEOUT`
2. **Circuit Breaker Policy:**
   * หาก Gateway ล้มเหลวติดต่อกัน 3 ครั้ง ระบบจะสลับไปดึงข้อมูลจาก Local Mock Provider ชั่วคราว (Fallback Mode) พร้อมแสดงสถานะการเชื่อมต่อสีส้มบนหน้าจอ
3. **Encoding Safe Fallback:**
   * สตริงภาษาไทยทั้งหมดจะผ่านฟังก์ชัน `mb_convert_encoding($str, 'UTF-8', 'TIS-620, UTF-8')` เพื่อป้องกันปัญหาข้อความภาษาไทยเพี้ยนหรือกลายเป็นเครื่องหมายคำถาม (`???`)

---
*เอกสารนี้ได้รับการตรวจสอบและทดสอบกับพอร์ตจริง 3333 เรียบร้อยแล้ว*
