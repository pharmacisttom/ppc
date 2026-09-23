# PCU Smart Pharmacy (ระบบบริหารจัดการระบบยาปฐมภูมิ รพ.สต.)

ระบบบริหารจัดการด้านยาและเภสัชกรรมปฐมภูมิสำหรับโรงพยาบาลส่งเสริมสุขภาพตำบล (รพ.สต.) และศูนย์บริการสาธารณสุข
ตามแนวคิด: **"One Patient – One Pharmaceutical Profile – One Medication Plan – One Primary Care Network"**

---

## 🌟 คุณสมบัติเด่น (Key Features)

1. **Dashboard & Summary Analytics**
   - แสดงสถิติและตัวชี้วัดสำคัญ (ผู้ป่วยกลุ่มเสี่ยง, ปัญหาการใช้ยา DRPs, ยาเสี่ยงสูง HAM/LASA, คุณภาพวัคซีนและห่วงโซ่ความเย็น)
2. **Clinical Safety & Alert Engine**
   - แจ้งเตือน Drug-Drug Interaction (ระดับรุนแรง/ปานกลาง/น้อย)
   - แจ้งเตือน Duplicate Therapy (ยาซ้ำซ้อนในกลุ่มบำบัดเดียวกัน)
   - แจ้งเตือน Renal Dosing Alert (ปรับขนาดยาตามค่า eGFR / ไตวาย)
   - ตรวจจับ High-Alert Medications (HAM) และ Look-Alike Sound-Alike (LASA)
3. **Primary Care Medication Review & Medication Reconciliation**
   - บันทึกการประเมินการใช้ยาของผู้ป่วยและการเยี่ยมบ้าน (Home Ward / HHC)
   - คัดกรองและประเมินผู้ป่วยกลุ่มเสี่ยง (ผู้สูงอายุ Polypharmacy, NCDs, CKD)
   - บันทึกปัญหาการใช้ยา (Drug Related Problems - DRPs) และแนวทางแก้ไข
4. **Emergency Kit & Safety Inspections**
   - ตรวจสอบกล่องยาฉุกเฉินประจำ รพ.สต. และรถพยาบาล
   - ควบคุมวันหมดอายุและสถานะความพร้อมใช้
5. **Cold Chain & Vaccine Management**
   - บันทึกอุณหภูมิตู้เย็นเก็บวัคซีน 2 รอบ/วัน (เช้า - เย็น) ตามมาตรฐาน 2°C - 8°C
   - ระบบแจ้งเตือนเมื่ออุณหภูมิหลุดช่วงมาตรฐาน (Out-of-range alert)
6. **Smart Inventory & Stock Card**
   - คลังเวชภัณฑ์ยา รพ.สต. ตรวจสอบสต็อกคงเหลือ บัญชีคุมยา (Stock Card)
   - แจ้งเตือนจุดสั่งซื้อ (Reorder Point) และยาวันใกล้หมดอายุ (FEFO)
7. **Quality & Standard Evaluation (รพ.สต. ติดดาว / เกณฑ์มาตรฐานบริการเภสัชกรรม)**
   - ระบบประเมินตนเองตามมาตรฐานบริการเภสัชกรรมปฐมภูมิ 5 ด้าน
   - รายการตรวจสอบพร้อมหลักฐานอ้างอิง (Evidence Checklist)
8. **JHCIS Integration Gateway**
   - เชื่อมโยงฐานข้อมูล JHCIS แบบ Read-Only Gateway เพื่อดึงประวัติผู้ป่วยและการสั่งจ่ายยาโดยไม่กระทบฐานข้อมูลหลัก

---

## 🛠 เทคโนโลยีที่ใช้ (Tech Stack)

- **Language:** PHP 8.x (Vanilla Native MVC Architecture)
- **Database:** MySQL 8.x / MariaDB
- **Web Server:** Apache (XAMPP / Linux LAMP)
- **Frontend:** Responsive UI with Bootstrap Icons, Google Fonts (Prompt & Sarabun) & Vanilla CSS
- **Security:** CSRF Protection, PDO Prepared Statements, RBAC (Role-Based Access Control), Audit Trail Logging

---

## 🚀 การติดตั้งและตั้งค่า (Installation & Setup)

1. **โคลนคลังโค้ด (Clone Repository):**
   ```bash
   git clone https://github.com/pharmacisttom/ppc.git hos
   ```
   นำโฟลเดอร์ไปไว้ที่ `c:/xampp/htdocs/hos` หรือ web root directory

2. **สร้างฐานข้อมูล (Database Setup):**
   - สร้างฐานข้อมูล MySQL ชื่อ `pcu_pharmacy` (utf8mb4_general_ci)
   - นำเข้าไฟล์สคริปต์ในโฟลเดอร์ `database/`:
     - `database/migrations/001_initial_schema.sql`
     - `database/seeds/001_master_seeds.sql`

3. **ตั้งค่าไฟล์สภาพแวดล้อม (Environment Configuration):**
   - คัดลอก `.env.example` เป็น `.env`
   - แก้ไขการตั้งค่าเชื่อมต่อฐานข้อมูล:
     ```env
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=pcu_pharmacy
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. **เริ่มใช้งาน:**
   - เปิดบราวเซอร์ไปที่: `http://localhost/pcc`
   - บัญชีผู้ใช้เริ่มต้น (Default Seed Users):
     - **Pharmacist (เภสัชกร):** `pharmacist` / `password123`
     - **Nurse (พยาบาลวิชาชีพ):** `nurse` / `password123`
     - **Public Health Officer (นวก.สาธารณสุข):** `officer` / `password123`
     - **Administrator (ผู้ดูแลระบบ):** `admin` / `password123`

---

## 📄 License & Credits
พัฒนาเพื่อยกระดับงานบริการเภสัชกรรมปฐมภูมิ และความปลอดภัยด้านยาในชุมชน
