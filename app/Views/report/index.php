<div class="report-center-container">
    <!-- Header Notice -->
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 22px; color: #ffffff; margin-bottom: 6px;">📊 ศูนย์รายงานและส่งออกข้อมูล (Reporting & Export Center)</h2>
                    <p style="font-size: 14px; opacity: 0.9; margin: 0; max-width: 700px;">
                        ระบบส่งออกข้อมูลเป็นไฟล์ <strong>Microsoft Excel (.xls)</strong> และ <strong>CSV (UTF-8 BOM)</strong> สอดรับกับมาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570, งานประกันคุณภาพ (QA), และการนิเทศงานของเครือข่าย CUP
                    </p>
                </div>
                <div style="font-size: 48px; opacity: 0.85;">📑</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-widget">
            <div>
                <div class="stat-label">แคตตาล็อกยาในระบบ</div>
                <div class="stat-value"><?= number_format($stats['total_catalog']) ?> <span style="font-size: 13px;">รายการ</span></div>
            </div>
            <div class="stat-icon primary">💊</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">มูลค่าคลังยาคงเหลือ</div>
                <div class="stat-value" style="color: #0f766e;">฿<?= number_format((float)($stockStats['total_value'] ?? 0), 2) ?></div>
            </div>
            <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">💰</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">เคสทบทวนยา / ปัญหา DRP</div>
                <div class="stat-value" style="color: #7c3aed;">
                    <?= number_format($reviewCount) ?> <span style="font-size: 13px;">เคส</span> / <?= number_format($problemCount) ?> <span style="font-size: 13px;">DRP</span>
                </div>
            </div>
            <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">📋</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">เกณฑ์มาตรฐานปฐมภูมิ</div>
                <div class="stat-value" style="color: #2563eb;">10 <span style="font-size: 13px;">ตัวชี้วัด</span></div>
            </div>
            <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">🏅</div>
        </div>
    </div>

    <!-- Available Reports Grid -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        <!-- 1. PCU Drug Formulary Report -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #ccfbf1; padding: 10px; border-radius: 12px; color: #0f766e;">💊</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            รายงานบัญชีรายการยาทั้งหมดของ รพ.สต. (PCU Drug Formulary)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            ส่งออกรายการยาประจำ รพ.สต. ครบทุกฟิลด์: รหัสยา, รหัสมาตรฐาน TMT, ชื่อการค้า, ชื่อสามัญ, หน่วยนับ, ต้นทุน, ราคาเบิกจ่าย, บัญชียาหลัก, หมวดหมู่ความปลอดภัย (HAM/LASA/Cold Chain/CPR), และยอดสต็อก
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/drugs/export-csv" class="btn btn-secondary btn-sm" title="ส่งออกเป็น CSV (UTF-8 BOM)">
                    📑 ส่งออก CSV
                </a>
                <a href="/pcc/drugs/export-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;" title="ส่งออกเป็น Microsoft Excel">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 2. Stock Balance & Expiry FEFO Report -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #ffedd5; padding: 10px; border-radius: 12px; color: #ea580c;">📦</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            รายงานคลังยาและการบริหารวันหมดอายุ (FEFO Stock & Expiry)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            รายงานสถานะสต็อกยาในคลัง รพ.สต. จัดลำดับตามหลัก First-Expire First-Out พร้อมระบุจำนวนวันก่อนหมดอายุ สถานะความเสี่ยง (Expired, ≤30วัน, ≤90วัน) และมูลค่าคงคลัง
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/reports/inventory-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 3. Cold Chain Temperature Compliance -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #e0f2fe; padding: 10px; border-radius: 12px; color: #0284c7;">❄️</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            รายงานการบันทึกอุณหภูมิตู้เย็นยาและวัคซีน (Cold Chain 2-8°C)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            ประวัติการตรวจเช็กอุณหภูมิวันละ 2 รอบ (เช้า-บ่าย) อุณหภูมิ Min-Max การหลุดเกณฑ์ (Excursion) และการบันทึกมาตรการแก้ไขตามเกณฑ์มาตรฐานความปลอดภัยของวัคซีน
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/reports/coldchain-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 4. HAM & LASA Safety Register -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #fee2e2; padding: 10px; border-radius: 12px; color: #dc2626;">⚠️</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            ทะเบียนยาความเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            บัญชีรายชื่อยาเสี่ยงสูง ข้อควรระวังพิเศษ การจัดเก็บแยกจุด และตารางคู่เทียบยา LASA พร้อมชื่อตัวอักษรสูง-ต่ำ (Tall Man Lettering) ตาม Patient Safety Goals
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/reports/ham-lasa-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 5. Medication Review & DRP Problems Report -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #f3e8ff; padding: 10px; border-radius: 12px; color: #7c3aed;">📋</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            รายงานการทบทวนยาและปัญหาจากการใช้ยา (Medication Review & DRP)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            รายงานสรุปปัญหา DRP ที่เภสัชกรปฐมภูมิตรวจพบในกลุ่มผู้ป่วยเรื้อรัง Polypharmacy ข้อเสนอแนะทางคลินิก และการตอบรับคำแนะนำจากแพทย์ผู้สั่งใช้
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/reports/drp-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 6. Primary Care Standards 2568-2570 Self Assessment -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 32px; background: #eff6ff; padding: 10px; border-radius: 12px; color: #2563eb;">🏅</div>
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--text-primary);">
                            รายงานผลการประเมินตนเองตามมาตรฐานปฐมภูมิ พ.ศ. 2568–2570
                        </h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                            สรุปผลคะแนนการประเมินตนเองครบทั้ง 5 มิติคุณภาพ ข้อกำหนดมาตรฐาน น้ำหนักคะแนน สถานะความสอดคล้อง และรายการหลักฐานเชิงประจักษ์ที่ต้องนำส่ง
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="/pcc/reports/quality-excel" class="btn btn-primary btn-sm" style="background: #15803d; border-color: #15803d;">
                    📊 ส่งออก Excel (.xls)
                </a>
            </div>
        </div>
    </div>
</div>
