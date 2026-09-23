<?php
use App\Core\Session;
use App\Core\CSRF;
$incIdStr = 'INC-' . str_pad((string)$incident['incident_id'], 4, '0', STR_PAD_LEFT);
$cat = $incident['severity_category'];
$badgeClass = in_array($cat, ['A','B']) ? 'badge-success' : (in_array($cat, ['C','D']) ? 'badge-warning' : 'badge-danger');

$stageLabels = [
    'prescribing' => 'สั่งใช้ยา (Prescribing)',
    'transcribing' => 'คัดลอก/คีย์คำสั่ง (Transcribing)',
    'dispensing' => 'จัดยา/จ่ายยา (Dispensing)',
    'administration' => 'บริหารยา/ให้ยา (Administration)',
    'monitoring' => 'ติดตามผลยา (Monitoring)',
    'storage' => 'การจัดเก็บยา (Storage)'
];
?>
<div class="incident-show-container" style="max-width: 1000px; margin: 0 auto;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px; font-weight: 700; color: var(--primary); font-family: monospace;"><?= $incIdStr ?></span>
                <span class="badge <?= $badgeClass ?>" style="font-size: 13px; font-weight: 700;">
                    NCC MERP Category <?= $cat ?> <?= $incident['is_near_miss'] ? '(Near Miss)' : '' ?>
                </span>
                <?php if ($incident['status'] === 'closed'): ?>
                    <span class="badge badge-success">✅ ปิดเคส RCA สมบูรณ์</span>
                <?php elseif ($incident['status'] === 'rca_completed'): ?>
                    <span class="badge badge-info">🔍 วิเคราะห์ RCA แล้ว</span>
                <?php elseif ($incident['status'] === 'investigating'): ?>
                    <span class="badge badge-warning">⏳ อยู่ระหว่างสืบสวน</span>
                <?php else: ?>
                    <span class="badge badge-secondary">📥 รายงานใหม่</span>
                <?php endif; ?>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 6px 0 0 0;">
                บันทึกการสืบสวนอุบัติการณ์และการวิเคราะห์สาเหตุที่แท้จริง (Root Cause Analysis & Corrective Actions)
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/pcc/safety/incidents" class="btn btn-secondary">
                ⬅ กลับไปทะเบียนอุบัติการณ์
            </a>
            <a href="/pcc/safety" class="btn btn-secondary">
                🛡️ ศูนย์บัญชาการ
            </a>
        </div>
    </div>

    <!-- Incident Summary Card -->
    <div class="card" style="margin-bottom: 24px; border-left: 5px solid #ef4444;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>📋 รายละเอียดเหตุการณ์ที่รายงาน (Incident Information)</span>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);">
                รายงานเมื่อ: <?= date('d/m/Y H:i', strtotime($incident['report_date'])) ?>
            </span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">วัน-เวลาที่เกิดเหตุ:</div>
                    <strong style="font-size: 14px; color: #0f172a;"><?= date('d/m/Y H:i', strtotime($incident['incident_date'])) ?> น.</strong>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">ขั้นตอนที่เกิดเหตุ (Stage):</div>
                    <strong style="font-size: 14px; color: #0f172a;"><?= $stageLabels[$incident['incident_stage']] ?? $incident['incident_stage'] ?></strong>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">ประเภทเหตุการณ์:</div>
                    <strong style="font-size: 14px; color: <?= $incident['is_near_miss'] ? 'var(--success)' : '#ef4444' ?>;">
                        <?= $incident['is_near_miss'] ? '🎯 เกือบพลาด (Near Miss สกัดได้ทัน)' : '⚠️ เกิดข้อผิดพลาด (Error reached patient)' ?>
                    </strong>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">ผู้รายงาน:</div>
                    <strong style="font-size: 14px; color: #0f172a;">
                        <?= !empty($incident['firstname']) ? htmlspecialchars($incident['firstname'] . ' ' . $incident['lastname']) : 'ไม่ประสงค์ออกนาม (Anonymous)' ?>
                    </strong>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">💊 รายการยาที่เกี่ยวข้อง:</div>
                <div style="font-size: 15px; font-weight: 700; color: #1e293b; background: #f8fafc; padding: 10px 14px; border-radius: 6px; border-left: 3px solid var(--primary);">
                    <?= htmlspecialchars($incident['drugs_involved']) ?>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">📝 รายละเอียดเหตุการณ์:</div>
                <div style="font-size: 13.5px; color: #334155; line-height: 1.6; background: #ffffff; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <?= nl2br(htmlspecialchars($incident['incident_description'])) ?>
                </div>
            </div>

            <?php if (!empty($incident['immediate_action_taken'])): ?>
                <div>
                    <div style="font-size: 12px; color: #15803d; margin-bottom: 4px; font-weight: 600;">⚡ การแก้ไขและช่วยเหลือเฉพาะหน้าทันที:</div>
                    <div style="font-size: 13.5px; color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 14px; border-radius: 6px;">
                        <?= nl2br(htmlspecialchars($incident['immediate_action_taken'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RCA & CAPA Investigation Section -->
    <div class="card" style="border-top: 4px solid var(--primary);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🔍 การวิเคราะห์หาสาเหตุที่แท้จริง (Root Cause Analysis - RCA) & มาตรการป้องกัน (CAPA)</span>
            </div>
            <span class="badge badge-info">กรอบวิเคราะห์ 4-M / Systemic Factors</span>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form method="POST" action="/pcc/safety/incidents/<?= (int)$incident['incident_id'] ?>/rca">
                <?= CSRF::field() ?>

                <!-- RCA Guidance Box -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; font-size: 12.5px; color: #475569; line-height: 1.5;">
                    <strong>💡 แนวทางการวิเคราะห์สาเหตุเชิงระบบ (Systemic Root Causes):</strong>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 6px;">
                        <div>• <strong>ด้านบุคคล (Man):</strong> ความล้า, ประสบการณ์, การสื่อสาร</div>
                        <div>• <strong>ด้านวิธีปฏิบัติ (Method):</strong> ขาด SOP, ข้ามขั้นตอน Double Check</div>
                        <div>• <strong>ด้านยา/อุปกรณ์ (Material):</strong> ฉลากไม่ชัดเจน, บรรจุภัณฑ์คล้าย (LASA)</div>
                        <div>• <strong>ด้านสิ่งแวดล้อม (Milieu):</strong> แสงสว่าง, เสียงรบกวน, ภาระงานเร่งด่วน</div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        1. ผลการวิเคราะห์สาเหตุที่แท้จริง (Root Cause Analysis - RCA) <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="root_cause_analysis" rows="4" class="form-control" placeholder="ระบุปัจจัยเชิงระบบที่แท้จริงที่ทำให้เกิดความผิดพลาด..." required style="font-size: 13.5px; line-height: 1.5;"><?= htmlspecialchars($incident['root_cause_analysis'] ?? '') ?></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        2. มาตรการแก้ไขเชิงระบบและป้องกันการเกิดซ้ำ (Corrective & Preventive Action - CAPA) <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="preventive_action" rows="4" class="form-control" placeholder="เช่น ปรับปรุงตำแหน่งจัดเก็บยา LASA, ติดป้าย Tall Man, กำหนดระบบเตือนในคอมพิวเตอร์, ซักซ้อม SOP..." required style="font-size: 13.5px; line-height: 1.5;"><?= htmlspecialchars($incident['preventive_action'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            ผู้รับผิดชอบการดำเนินการ
                        </label>
                        <input type="text" name="responsible_person" class="form-control" value="<?= htmlspecialchars($incident['responsible_person'] ?? '') ?>" placeholder="เช่น ภญ.วิภาวดี, ทีมนำทางคลินิก">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            กำหนดแล้วเสร็จ (Due Date)
                        </label>
                        <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($incident['due_date'] ?? '') ?>">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            สถานะการจัดการ (Status)
                        </label>
                        <select name="status" class="form-control" style="font-size: 13px; font-weight: 600;">
                            <option value="investigating" <?= ($incident['status'] ?? '') === 'investigating' ? 'selected' : '' ?>>⏳ อยู่ระหว่างสืบสวน (Investigating)</option>
                            <option value="rca_completed" <?= ($incident['status'] ?? '') === 'rca_completed' ? 'selected' : '' ?>>🔍 วางแผน CAPA เรียบร้อย (RCA Done)</option>
                            <option value="closed" <?= ($incident['status'] ?? '') === 'closed' ? 'selected' : '' ?>>✅ ปิดเคสสมบูรณ์ (Case Closed)</option>
                        </select>
                    </div>
                </div>

                <?php if (!empty($incident['closed_at'])): ?>
                    <div style="font-size: 12px; color: var(--success); margin-bottom: 16px; font-weight: 600;">
                        ✓ เคสนี้ปิดสมบูรณ์แล้วเมื่อ: <?= date('d/m/Y H:i', strtotime($incident['closed_at'])) ?> น.
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="/pcc/safety/incidents" class="btn btn-secondary">
                        ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 600;">
                        💾 บันทึกผลการวิเคราะห์ RCA & CAPA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
