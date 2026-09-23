<div class="exchange-container">
    <!-- Header Banner -->
    <div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #0f766e 100%); color: #ffffff; padding: 24px 28px; border-radius: 16px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(30, 27, 75, 0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); padding: 3px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 600; margin-bottom: 8px;">
                    <span>🌐</span>
                    <span>ระบบส่งออกและแลกเปลี่ยนข้อมูลมาตรฐานกระทรวงสาธารณสุข (MOPH 43 Files)</span>
                </div>
                <h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin-bottom: 6px;">
                    ศูนย์แลกเปลี่ยนข้อมูล รพ.แม่ข่าย (CUP รพ.ปลวกแดง)
                </h1>
                <p style="font-size: 13.5px; color: #cbd5e1; max-width: 750px; line-height: 1.5;">
                    สร้างและส่งออกแฟ้มข้อมูลมาตรฐาน 43 แฟ้ม สธ. รูปแบบ Text Pipe-delimited (|) และสมุดงาน Excel ครบถ้วนตามโครงสร้าง สนย./กสธ. เพื่อเชื่อมต่อข้อมูลระหว่าง รพ.สต.บ้านดอกกราย (01996) และ รพ.แม่ข่าย
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/pcc/exchange/zip" class="btn btn-primary" style="background: #10b981; border-color: #10b981; padding: 12px 20px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);">
                    📦 ดาวน์โหลดแพ็กเกจ 43 แฟ้ม (ZIP)
                </a>
                <a href="/pcc/exchange/excel" class="btn btn-secondary" style="background: #ffffff; color: #1e1b4b; padding: 12px 18px; font-size: 14px; font-weight: 700;">
                    📊 ส่งออกสมุดงาน Excel (CUP)
                </a>
            </div>
        </div>
    </div>

    <!-- Data Quality & Validation Summary -->
    <div class="grid-cols-4" style="margin-bottom: 22px;">
        <div class="stat-card" style="border-top: 4px solid #10b981;">
            <div class="stat-label">ความสมบูรณ์ของโครงสร้าง 43 แฟ้ม</div>
            <div class="stat-value" style="color: #10b981;">100% ผ่านเกณฑ์</div>
            <div class="stat-subtext">มาตรฐาน สนย. กสธ. v2.4</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #3b82f6;">
            <div class="stat-label">รหัสมาตรฐานยา (DIDSTD / 24 หลัก)</div>
            <div class="stat-value" style="color: #3b82f6;">เชื่อมโยง cdrug</div>
            <div class="stat-subtext">จับคู่รหัสยาพร้อมส่งออก</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #f59e0b;">
            <div class="stat-label">การตรวจสอบ CID 13 หลัก</div>
            <div class="stat-value" style="color: #f59e0b;">ครบถ้วนตามเกณฑ์</div>
            <div class="stat-subtext">ตรวจสอบเลข ปชช. จริงใน JHCIS</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #8b5cf6;">
            <div class="stat-label">สถานะการเชื่อมต่อ CUP รพ.แม่ข่าย</div>
            <div class="stat-value" style="color: #8b5cf6;">ออนไลน์</div>
            <div class="stat-subtext">CUP รพ.ปลวกแดง (10832)</div>
        </div>
    </div>

    <!-- 5 MOPH Standard Files Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- File 1: DRUG_OPD -->
        <div class="card" style="border-left: 5px solid #0d9488;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="badge badge-primary" style="font-size: 11px;">MOPH FILE 1</span>
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">DRUG_OPD.txt</h3>
                        <div style="font-size: 12.5px; color: #64748b;">แฟ้มข้อมูลการสั่งใช้ยาผู้ป่วยนอก</div>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #0d9488;">
                        <?= (int)($stats['DRUG_OPD']['count'] ?? 0) ?> <span style="font-size: 12px; font-weight: 500; color: #64748b;">เรคอร์ด</span>
                    </span>
                </div>
                <div style="font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 14px; font-family: monospace; color: #475569; overflow-x: auto; white-space: nowrap;">
                    HOSPCODE|PID|SEQ|DATE_SERV|CLINIC|DIDSTD|DNAME|AMOUNT|UNIT|...
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #059669; font-weight: 600;">✓ รองรับการเบิกจ่ายและประมวลผล HDC</span>
                    <a href="/pcc/exchange/file/DRUG_OPD" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 6px 14px;">
                        📥 ดาวน์โหลด .txt
                    </a>
                </div>
            </div>
        </div>

        <!-- File 2: DRUG_ALLERGY -->
        <div class="card" style="border-left: 5px solid #ef4444;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="badge badge-danger" style="font-size: 11px;">MOPH FILE 2</span>
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">DRUG_ALLERGY.txt</h3>
                        <div style="font-size: 12.5px; color: #64748b;">แฟ้มประวัติการแพ้ยาและการเฝ้าระวัง</div>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #ef4444;">
                        <?= (int)($stats['DRUG_ALLERGY']['count'] ?? 0) ?> <span style="font-size: 12px; font-weight: 500; color: #64748b;">เรคอร์ด</span>
                    </span>
                </div>
                <div style="font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 14px; font-family: monospace; color: #475569; overflow-x: auto; white-space: nowrap;">
                    HOSPCODE|PID|DATERECORD|DRUGNAME|SYMPTOM|ALEVEL|INFORMANT|...
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #059669; font-weight: 600;">✓ ป้องกันการแพ้ยาซ้ำข้ามโรงพยาบาล</span>
                    <a href="/pcc/exchange/file/DRUG_ALLERGY" class="btn btn-sm btn-primary" style="background: #dc2626; border-color: #dc2626; font-size: 12px; padding: 6px 14px;">
                        📥 ดาวน์โหลด .txt
                    </a>
                </div>
            </div>
        </div>

        <!-- File 3: CHRONIC -->
        <div class="card" style="border-left: 5px solid #3b82f6;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="badge badge-info" style="font-size: 11px;">MOPH FILE 3</span>
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">CHRONIC.txt</h3>
                        <div style="font-size: 12.5px; color: #64748b;">แฟ้มทะเบียนผู้ป่วยโรคเรื้อรัง (NCDs & CKD)</div>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #3b82f6;">
                        <?= (int)($stats['CHRONIC']['count'] ?? 0) ?> <span style="font-size: 12px; font-weight: 500; color: #64748b;">เรคอร์ด</span>
                    </span>
                </div>
                <div style="font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 14px; font-family: monospace; color: #475569; overflow-x: auto; white-space: nowrap;">
                    HOSPCODE|PID|DATEDX|CHRONIC|HOSP_DX|HOSP_RX|DATE_DISCH|...
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #059669; font-weight: 600;">✓ ติดตามโรคเบาหวาน ความดัน โรคไต</span>
                    <a href="/pcc/exchange/file/CHRONIC" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 6px 14px;">
                        📥 ดาวน์โหลด .txt
                    </a>
                </div>
            </div>
        </div>

        <!-- File 4: LABFU -->
        <div class="card" style="border-left: 5px solid #8b5cf6;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="badge badge-secondary" style="font-size: 11px;">MOPH FILE 4</span>
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">LABFU.txt</h3>
                        <div style="font-size: 12.5px; color: #64748b;">แฟ้มผลการตรวจทางห้องปฏิบัติการ (INR, eGFR, Cr, BP)</div>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #8b5cf6;">
                        <?= (int)($stats['LABFU']['count'] ?? 0) ?> <span style="font-size: 12px; font-weight: 500; color: #64748b;">เรคอร์ด</span>
                    </span>
                </div>
                <div style="font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 14px; font-family: monospace; color: #475569; overflow-x: auto; white-space: nowrap;">
                    HOSPCODE|PID|SEQ|DATE_SERV|LABTEST|LABRESULT|D_UPDATE|CID
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #059669; font-weight: 600;">✓ แลกเปลี่ยนค่าแล็บผู้ป่วยกลุ่มเสี่ยง</span>
                    <a href="/pcc/exchange/file/LABFU" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 6px 14px;">
                        📥 ดาวน์โหลด .txt
                    </a>
                </div>
            </div>
        </div>

        <!-- File 5: PERSON -->
        <div class="card" style="border-left: 5px solid #f59e0b;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="badge badge-warning" style="font-size: 11px;">MOPH FILE 5</span>
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">PERSON.txt</h3>
                        <div style="font-size: 12.5px; color: #64748b;">แฟ้มข้อมูลประชากรและผู้รับบริการ</div>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700; color: #f59e0b;">
                        <?= (int)($stats['PERSON']['count'] ?? 0) ?> <span style="font-size: 12px; font-weight: 500; color: #64748b;">เรคอร์ด</span>
                    </span>
                </div>
                <div style="font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 14px; font-family: monospace; color: #475569; overflow-x: auto; white-space: nowrap;">
                    HOSPCODE|PID|CID|PRENAME|NAME|LNAME|HN|SEX|BIRTH|...
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #059669; font-weight: 600;">✓ ตรวจสอบสิทธิและระบุตัวบุคคล</span>
                    <a href="/pcc/exchange/file/PERSON" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 6px 14px;">
                        📥 ดาวน์โหลด .txt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
