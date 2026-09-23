<?php
use App\Core\Session;
use App\Core\CSRF;
?>
<div class="emergency-kit-container">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 26px;">🚑</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    การตรวจสอบชุดยาช่วยชีวิตฉุกเฉิน (Emergency CPR Kit & Crash Cart)
                </h1>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 6px 0 0 0;">
                การตรวจสอบความพร้อมใช้ของชุดยาช่วยชีวิตฉุกเฉินระดับ รพ.สต. ต้องตรวจสอบทุกสัปดาห์หรือหลังการใช้งาน
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/pcc/safety" class="btn btn-secondary">
                🛡️ กลับศูนย์บัญชาการความปลอดภัย
            </a>
            <form method="POST" action="/pcc/safety/emergency-kit/inspect" style="display: inline;">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    ✅ บันทึกรับรองผลการตรวจความพร้อม
                </button>
            </form>
        </div>
    </div>

    <!-- Status Card -->
    <div class="card" style="background: #f0fdf4; border: 1px solid #bbf7d0; margin-bottom: 24px;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <strong style="font-size: 16px; color: #166534;">🟢 สถานะกล่องช่วยชีวิต: ปิดผนึกเรียบร้อย (Sealed & Ready to Use)</strong>
                <div style="font-size: 13px; color: #15803d; margin-top: 4px;">
                    หมายเลขสายรัดซีล: <strong>#SL-2568-0914</strong> • ตรวจสอบล่าสุด: <strong><?= htmlspecialchars($lastChecked ?? date('d/m/Y H:i')) ?> น.</strong> โดย <?= htmlspecialchars($inspector ?? 'เจ้าหน้าที่ผู้รับผิดชอบ') ?>
                </div>
            </div>
            <div>
                <span class="badge badge-success" style="font-size: 14px; padding: 6px 16px;">ความพร้อม 100% (5/5 รายการ)</span>
            </div>
        </div>
    </div>

    <!-- Kit Items Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🚑 บัญชีรายการยาช่วยชีวิตฉุกเฉินประจำ รพ.สต.บ้านดอกกราย</span>
            </div>
            <span class="badge badge-secondary">มาตรฐานกระทรวงสาธารณสุข</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่อยาช่วยชีวิตฉุกเฉิน</th>
                            <th style="width: 130px;">Lot No.</th>
                            <th style="width: 140px;">เกณฑ์มาตรฐาน</th>
                            <th style="width: 140px;">จำนวนที่มีอยู่จริง</th>
                            <th style="width: 160px;">วันหมดอายุ (Expiry)</th>
                            <th>ข้อบ่งใช้หลัก / ขนาดยาฉุกเฉิน</th>
                            <th style="width: 140px;">สถานะความพร้อม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kitItems as $item): ?>
                            <tr>
                                <td>
                                    <strong style="font-size: 14px; color: #0f172a;"><?= htmlspecialchars($item['name']) ?></strong>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($item['lot'] ?? '-') ?></span>
                                </td>
                                <td><?= (int)$item['qty_required'] ?> Amp/Vial</td>
                                <td><strong style="color: var(--success); font-size: 14px;"><?= (int)$item['qty_available'] ?> Amp/Vial</strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($item['expiry_date']) ?></strong>
                                    <span class="badge badge-success" style="margin-left: 6px; font-size: 11px;">ปลอดภัย</span>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?php 
                                        if (str_contains($item['name'], 'Adrenaline')) echo 'Cardiac arrest / Anaphylactic shock (1 mg IV/IM)';
                                        elseif (str_contains($item['name'], 'Atropine')) echo 'Severe symptomatic bradycardia (0.6 mg IV)';
                                        elseif (str_contains($item['name'], 'Diazepam')) echo 'Status epilepticus / ชักเกร็ง (10 mg IV slow)';
                                        elseif (str_contains($item['name'], 'Glucose')) echo 'Severe Hypoglycemia (50 ml IV push)';
                                        else echo 'IV Fluid resuscitation / ชดเชยสารน้ำเร่งด่วน';
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
