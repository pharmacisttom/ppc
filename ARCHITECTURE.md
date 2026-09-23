# SYSTEM ARCHITECTURE & SECURITY SPECIFICATION
## สถาปัตยกรรมระบบ ความปลอดภัย และการคุ้มครองข้อมูลส่วนบุคคล (PDPA)
**เอกสารเลขที่:** AGY-PCU-ARCH-004  
**มาตรฐานอ้างอิง:** Enterprise Healthcare Information Architecture, NIST Cybersecurity Framework, พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)  

---

## 1. สถาปัตยกรรมภาพรวมแบบแยกชั้น (Multi-Tier Isolated Healthcare Architecture)

ระบบ **PCU Smart Pharmacy** ปฏิบัติตามหลักความปลอดภัยระดับ Enterprise ทางการแพทย์อย่างเคร่งครัด โดยแบ่งโครงสร้างออกเป็น 4 เลเยอร์หลักเพื่อตัดขาดช่องโหว่ความปลอดภัย:

```
+-------------------------------------------------------------------------------+
|                            PRESENTATION LAYER (UI/UX)                         |
|  - Modern Thai Healthcare UI (Responsive: Desktop, Tablet, Mobile)            |
|  - Strictly Communicates with PCU Smart Pharmacy Server via HTTPS             |
|  - [STRICT RULE] Web Browser NEVER connects to JHCIS MySQL directly           |
+---------------------------------------+---------------------------------------+
                                        | HTTPS / SameSite Strict / CSRF Token
                                        v
+-------------------------------------------------------------------------------+
|                      PCU SMART PHARMACY APPLICATION LAYER                     |
|  - PHP 8.2 Modern Modular MVC Architecture                                     |
|  - Authentication & RBAC Middleware                                           |
|  - Medication Safety Decision Support Rule Engine                             |
|  - Quality Standard Engine & Audit Trail Logger                               |
|  - [STRICT RULE] Never uses JHCIS root account                                |
+-------------------+-----------------------------------+-----------------------+
                    |                                   |
    Internal HTTP / Service Call               PDO Prepared Statement (Read/Write)
                    v                                   v
+---------------------------------------+   +-----------------------------------+
|           JHCIS API GATEWAY           |   |       APPLICATION DATABASE        |
|  - Dedicated Read-Only Gateway        |   |   (MySQL Port 3306: pcu_pharmacy) |
|  - Least Privilege (SELECT ONLY)      |   |  - Clinical Reviews & Notes       |
|  - UTF-8 Normalizer & CID Masking     |   |  - Medication Reconciliation      |
|  - Query Caching & Circuit Breaker    |   |  - Stock Lots & FEFO Movements    |
+-------------------+-------------------+   |  - Cold Chain, HAM, LASA Logs     |
                    |                       |  - Quality Standards & Evidence   |
      Port 3333     | Read-Only Query       |  - User Credentials & RBAC        |
                    v                       +-----------------------------------+
+---------------------------------------+
|        JHCIS DATABASE (Port 3333)     |
|  (MySQL 5.6.x - jhcisdb: READ-ONLY)   |
|   person, visit, visitdrug, cdrug,    |
|   personalergic, personchronic        |
+---------------------------------------+
```

---

## 2. นโยบายการแยกฐานข้อมูลและการป้องกันฐานข้อมูลเดิม (Data Isolation Policy)

1. **การอ่านข้อมูลจาก JHCIS (Read-Only Policy):**
   * ระบบ JHCIS ดั้งเดิมที่กำลังปฏิบัติงานใน รพ.สต. มีความสำคัญยิ่งยวดต่อการให้บริการผู้ป่วยประจำวัน
   * การเข้าถึงตาราง JHCIS ทำได้เฉพาะการ `SELECT` ผ่าน **JHCIS API Gateway** เท่านั้น
   * **ห้ามมีคำสั่ง `INSERT`, `UPDATE`, `DELETE`, `ALTER` หรือ `DROP` ใดๆ ไปยังฐานข้อมูล `jhcisdb` เป็นอันขาด** เพื่อป้องกันความเสี่ยงต่อความสมบูรณ์ของฐานข้อมูลทางการแพทย์ของรัฐ
2. **การจัดเก็บข้อมูลใหม่ (Application Database):**
   * ข้อมูลที่ระบบสร้างขึ้นมาใหม่ทั้งหมด เช่น การทบทวนวรรณกรรมยา (Medication Review), การประสานรายการยา (Medication Reconciliation), บันทึกการเยี่ยมบ้าน HMR, ทะเบียนสต็อก Lot/FEFO, ประวัติอุณหภูมิตู้เย็น, การประเมินตนเองตามมาตรฐาน และหลักฐานเชิงประจักษ์ **จะถูกจัดเก็บลงในฐานข้อมูล `pcu_pharmacy` (Port 3306) อย่างเป็นเอกเทศ 100%**
3. **การจัดการข้อผิดพลาดและการทำงานต่อเนื่อง (Circuit Breaker & Fallback):**
   * หากเซอร์วิส JHCIS หรือพอร์ต 3333 เกิดการขัดข้องหรือไม่ตอบสนองภายในระยะเวลาที่กำหนด (Timeout 3,000 ms) ระบบ JHCIS API Gateway จะเข้าสู่สภาวะ Fallback พร้อมแจ้งเตือนผู้ใช้งานอย่างสุภาพ โดยไม่ทำให้ระบบหลักของ PCU Smart Pharmacy หยุดทำงาน

---

## 3. มาตรการความมั่นคงปลอดภัยสารสนเทศ (Cybersecurity Hardening)

ระบบได้รับการออกแบบตามมาตรฐาน OWASP Top 10 และมาตรการความปลอดภัยระดับสูง:

1. **Prepared Statements & Parameterized Queries:**
   * ทุกการติดต่อฐานข้อมูลใช้ PDO ร่วมกับ Prepared Statements 100% ห้ามต่อสตริงคำสั่ง SQL โดยเด็ดขาด ป้องกัน SQL Injection 100%
2. **Cross-Site Scripting (XSS) & Input Validation:**
   * ข้อมูลนำเข้าทั้งหมดต้องผ่านการ Validate Type, Length, Format
   * ข้อมูลที่แสดงผลบน Web UI ต้องผ่านการ Encode ด้วย `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
3. **Cross-Site Request Forgery (CSRF) Protection:**
   * ทุกฟอร์มและการส่งข้อมูลแบบ POST, PUT, DELETE ต้องแนบ CSRF Token แบบสุ่มความยาวไม่น้อยกว่า 64 ตัวอักษร (Cryptographically Secure Pseudo-Random Bytes)
4. **Session Security Management:**
   * Cookie ติดตั้งแฟล็ก `HttpOnly = true` (ป้องกันการขโมย Cookie ผ่าน JavaScript)
   * `SameSite = Strict` หรือ `Lax` เพื่อป้องกัน Cross-site Request
   * ทำ `session_regenerate_id(true)` ทุกครั้งเมื่อมีการล็อกอินสำเร็จ เพื่อป้องกัน Session Fixation
5. **Security Headers:**
   * ส่งออก Headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `X-XSS-Protection: 1; mode=block`, `Content-Security-Policy`
6. **Environment Variables & Secrets:**
   * แยกการตั้งค่าและรหัสผ่านไว้ในไฟล์ `.env` โดยไม่บันทึก Secret ใดๆ ลงใน Git Repository
   * เมื่อทำงานบน Production: กำหนด `APP_DEBUG = false` เพื่อไม่ให้แสดง Stack Trace หรือข้อมูลภายในระบบหากเกิด Error

---

## 4. มาตรการคุ้มครองข้อมูลส่วนบุคคล (PDPA Compliance & Privacy by Design)

ตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 ข้อมูลประวัติการรักษาและประวัติด้านยาถือเป็น **"ข้อมูลส่วนบุคคลที่มีความอ่อนไหวเป็นพิเศษ (Sensitive Personal Data ตามมาตรา 26)"** ระบบจึงกำหนดมาตรการดังนี้:

1. **การบังคับใช้หลัก Minimum Necessary (ความจำเป็นขั้นต่ำ):**
   * เรียกดูและแสดงผลเฉพาะข้อมูลที่จำเป็นต่อการบริบาลทางเภสัชกรรมและความปลอดภัยของผู้ป่วยเท่านั้น
2. **การปกปิดและพรางข้อมูลเลขประจำตัวประชาชน (CID Masking):**
   * บนหน้าจอแสดงผลทั่วไป เลขประจำตัวประชาชน 13 หลักจะถูก Mask เสมอ เช่น `1-1002-xxxxx-34-1`
   * เฉพาะผู้มีบทบาทที่ได้รับอนุญาตและกดปุ่มยืนยันเพื่อการระบุตัวตนเท่านั้นที่จะสามารถปลดล็อกดูข้อมูลเต็มได้ พร้อมบันทึกลงใน Audit Log
3. **การเก็บบันทึกประวัติการเข้าดูเวชระเบียน (Access Audit Trail):**
   * ทุกครั้งที่มีการเปิดดู Patient Pharmaceutical Profile ระบบจะบันทึก:
     * `user_id` (ผู้เข้าดู)
     * `patient_pid` (ผู้ป่วยที่ถูกเปิดดู)
     * `action` (`PATIENT_VIEW`, `MEDICATION_VIEW`, `ALLERGY_VIEW`)
     * `ip_address`
     * `timestamp`
     * `reason` (วัตถุประสงค์ในการเข้าดู เช่น เพื่อการสั่งจ่ายยา / เพื่อการเยี่ยมบ้าน)
4. **Data Isolation by Facility & Scope:**
   * ข้อมูลผู้ป่วยถูกจำกัดการเข้าถึงตามสังกัดหน่วยบริการ (Facility Scoping) ยกเว้นบทบาทระดับเครือข่ายอำเภอ (District Pharmacist) ที่ได้รับมอบหมายให้กำกับดูแลข้าม รพ.สต. ใน CUP

---

## 5. ระบบโหมดการฝึกอบรม (Training & Simulation Mode)

ระบบรองรับสวิตช์โหมดการทำงานผ่านตัวแปรสภาพแวดล้อม:
```ini
APP_MODE=training
```

### กลไกการทำงานเมื่อเข้าสู่ Training Mode:
1. **Visual Indicator:** แสดงแถบสีส้มสะท้อนแสงเด่นชัดด้านบนสุดของทุกหน้าจอ:
   > ⚠️ **TRAINING MODE (ระบบจำลองเพื่อการฝึกอบรม) — ข้อมูลทั้งหมดเป็นสถานการณ์จำลอง ห้ามใช้ตัดสินใจในการรักษาผู้ป่วยจริง**
2. **Data Guarding:**
   * ระบบจะตัดการเชื่อมต่อไปยังข้อมูลประชากรจริง และใช้ฐานข้อมูลตัวอย่างจำลอง (Simulated Cohort)
   * ป้องกันการรั่วไหลของข้อมูลผู้ป่วยจริงในระหว่างการจัดอบรมบุคลากร
3. **Pre-seeded Clinical Scenarios (สถานการณ์จำลองทางคลินิก 13 รูปแบบ):**
   * Scenario 1: ผู้ป่วยเบาหวาน (DM) ได้รับยา Metformin ร่วมกับไตเสื่อม (CKD stage 4)
   * Scenario 2: ผู้ป่วยความดันโลหิตสูง (HT) เกิดข้อห้ามใช้ยากลุ่ม NSAID
   * Scenario 3: ผู้ป่วยมีประวัติแพ้ยา Amoxicillin แบบ Anaphylaxis และแพทย์ทดลองสั่ง Cephalexin (Cross-sensitivity Screening)
   * Scenario 4: ผู้ป่วยสูงอายุได้รับยา Polypharmacy มากกว่า 10 รายการ และมีปัญหา Non-Adherence
   * Scenario 5: การทำ Medication Reconciliation ในผู้ป่วยส่งกลับจาก รพ.แม่ข่าย มียาตกหล่น
   * Scenario 6: การตรวจสอบคลังยาพบยาล็อตใกล้หมดอายุภายใน 30 วัน และยาสต็อกขาดคราว (Stockout)
   * Scenario 7: เหตุการณ์อุณหภูมิตู้เย็นเก็บวัคซีนพุ่งสูงเกินเกณฑ์ (Cold Chain Excursion 12°C)
   * Scenario 8: การจัดเก็บยา Look-Alike Sound-Alike (LASA) คู่เสี่ยงสูง
   * Scenario 9: การรายงาน Medication Incident ชนิด Near Miss ในขั้นตอน Dispensing

---
*เอกสารนี้กำหนดข้อบังคับด้านสถาปัตยกรรมที่วิศวกรทุกคนต้องปฏิบัติตามอย่างเคร่งครัด*
