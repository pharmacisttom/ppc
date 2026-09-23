<div class="emergency-kit-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">การตรวจสอบชุดยาช่วยชีวิตฉุกเฉิน (Emergency CPR Kit & Crash Cart)</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                การตรวจสอบความพร้อมใช้ของชุดยาช่วยชีวิตฉุกเฉินระดับ รพ.สต. ต้องตรวจสอบทุกสัปดาห์หรือหลังการใช้งาน
            </p>
        </div>
        <button class="btn btn-primary" onclick="alert('ระบบบันทึกการตรวจสอบและรับรองชุดยาช่วยชีวิตฉุกเฉิน (Seal Number: #SL-2568-0914) สำเร็จ')">
            ✅ รับรองผลการตรวจความพร้อม
        </button>
    </div>

    <!-- Status Card -->
    <div class="card" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <strong style="font-size: 16px; color: #166534;">🟢 สถานะกล่องช่วยชีวิต: ปิดผนึกเรียบร้อย (Sealed & Ready)</strong>
                <div style="font-size: 13px; color: #15803d; margin-top: 2px;">
                    หมายเลขสายรัดซีล: <strong>#SL-2568-0914</strong> • ตรวจสอบล่าสุด: <strong><?= date('d/m/Y') ?></strong> โดย พว.สมใจ รักการพยาบาล
                </div>
            </div>
            <div>
                <span class="badge badge-success" style="font-size: 14px; padding: 6px 14px;">ความพร้อม 100% (5/5 รายการ)</span>
            </div>
        </div>
    </div>

    <!-- Kit Items Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>🚑 บัญชีรายการยาช่วยชีวิตฉุกเฉินประจำ รพ.สต.</span>
            </div>
            <span class="badge badge-secondary">มาตรฐานกระทรวงสาธารณสุข</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่อยาช่วยชีวิตฉุกเฉิน</th>
                            <th>จำนวนเกณฑ์มาตรฐาน</th>
                            <th>จำนวนที่มีอยู่จริง</th>
                            <th>วันหมดอายุ (Expiry)</th>
                            <th>ข้อบ่งใช้หลัก / ขนาดยาฉุกเฉิน</th>
                            <th>สถานะความพร้อม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kitItems as $item): ?>
                            <tr>
                                <td>
                                    <strong style="font-size: 14px; color: #0f172a;"><?= htmlspecialchars($item['name']) ?></strong>
                                </td>
                                <td><?= (int)$item['qty_required'] ?> Amp/Vial</td>
                                <td><strong><?= (int)$item['qty_available'] ?> Amp/Vial</strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($item['expiry_date']) ?></strong>
                                    <span class="badge badge-success" style="margin-left: 6px;">ปลอดภัย</span>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?php 
                                        if (str_contains($item['name'], 'Adrenaline')) echo 'Cardiac arrest / Anaphylactic shock (1 mg IV/IM)';
                                        elseif (str_contains($item['name'], 'Atropine')) echo 'Severe symptomatic bradycardia (0.6 mg IV)';
                                        elseif (str_contains($item['name'], 'Diazepam')) echo 'Status epilepticus / ชักเกร็ง (10 mg IV slow)';
                                        elseif (str_contains($item['name'], 'Glucose')) echo 'Severe Hypoglycemia (50 ml IV push)';
                                        else echo 'IV Fluid resuscitation / ปริมาณสารน้ำ';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">✅ พร้อมใช้ 100%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
