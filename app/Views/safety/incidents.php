<div class="safety-incidents-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="/pcc/safety" style="text-decoration: none; font-size: 20px;">🛡️</a>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    ทะเบียนรายงานอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incidents & Near Miss)
                </h1>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 6px 0 0 0;">
                ระบบบันทึก สืบสวน และวิเคราะห์สาเหตุที่แท้จริง (Root Cause Analysis - RCA & CAPA) ตามมาตรฐานกระทรวงสาธารณสุขและ NCC MERP
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/pcc/safety/incidents/create" class="btn btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none;">
                <span>+</span> รายงานอุบัติการณ์ใหม่
            </a>
            <a href="/pcc/safety/incidents/export" class="btn btn-secondary">
                <span>📥</span> ส่งออก Excel (.xls)
            </a>
            <a href="/pcc/safety" class="btn btn-secondary">
                ⬅ กลับศูนย์บัญชาการ
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="/pcc/safety/incidents" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
                <!-- Filter: Stage -->
                <div style="flex: 1; min-width: 170px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        ขั้นตอนที่เกิดเหตุ (Stage)
                    </label>
                    <select name="stage" class="form-control" style="font-size: 13px;">
                        <option value="">-- ทุกขั้นตอน --</option>
                        <option value="prescribing" <?= ($filters['stage'] ?? '') === 'prescribing' ? 'selected' : '' ?>>1. สั่งใช้ยา (Prescribing)</option>
                        <option value="transcribing" <?= ($filters['stage'] ?? '') === 'transcribing' ? 'selected' : '' ?>>2. คัดลอก/คีย์คำสั่ง (Transcribing)</option>
                        <option value="dispensing" <?= ($filters['stage'] ?? '') === 'dispensing' ? 'selected' : '' ?>>3. จัด/จ่ายยา (Dispensing)</option>
                        <option value="administration" <?= ($filters['stage'] ?? '') === 'administration' ? 'selected' : '' ?>>4. บริหารยา/ให้ยา (Administration)</option>
                        <option value="monitoring" <?= ($filters['stage'] ?? '') === 'monitoring' ? 'selected' : '' ?>>5. ติดตามผลยา (Monitoring)</option>
                        <option value="storage" <?= ($filters['stage'] ?? '') === 'storage' ? 'selected' : '' ?>>6. การจัดเก็บในคลัง (Storage)</option>
                    </select>
                </div>

                <!-- Filter: Severity -->
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        ความรุนแรง (NCC MERP)
                    </label>
                    <select name="severity" class="form-control" style="font-size: 13px;">
                        <option value="">-- ทุกระดับ (A-I) --</option>
                        <option value="A" <?= ($filters['severity'] ?? '') === 'A' ? 'selected' : '' ?>>Cat A (แวดล้อมเสี่ยง)</option>
                        <option value="B" <?= ($filters['severity'] ?? '') === 'B' ? 'selected' : '' ?>>Cat B (Near Miss สกัดทัน)</option>
                        <option value="C" <?= ($filters['severity'] ?? '') === 'C' ? 'selected' : '' ?>>Cat C (ถึงผู้ป่วย ไม่เป็นไร)</option>
                        <option value="D" <?= ($filters['severity'] ?? '') === 'D' ? 'selected' : '' ?>>Cat D (ถึงผู้ป่วย ต้องเฝ้าระวัง)</option>
                        <option value="E" <?= ($filters['severity'] ?? '') === 'E' ? 'selected' : '' ?>>Cat E (เกิดอันตรายชั่วคราว)</option>
                        <option value="F" <?= ($filters['severity'] ?? '') === 'F' ? 'selected' : '' ?>>Cat F (อันตรายต้องนอน รพ.)</option>
                    </select>
                </div>

                <!-- Filter: Status -->
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        สถานะการจัดการ (Status)
                    </label>
                    <select name="status" class="form-control" style="font-size: 13px;">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="reported" <?= ($filters['status'] ?? '') === 'reported' ? 'selected' : '' ?>>รายงานใหม่ (Reported)</option>
                        <option value="investigating" <?= ($filters['status'] ?? '') === 'investigating' ? 'selected' : '' ?>>กำลังสืบสวน (Investigating)</option>
                        <option value="rca_completed" <?= ($filters['status'] ?? '') === 'rca_completed' ? 'selected' : '' ?>>วิเคราะห์ RCA แล้ว</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>ปิดเคสแล้ว (Closed)</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div style="flex: 2; min-width: 200px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        คำค้นหา (ชื่อยา / รายละเอียด / ผู้รับผิดชอบ)
                    </label>
                    <input type="text" name="q" class="form-control" placeholder="พิมพ์ชื่อยา หรือ คำค้น..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" style="font-size: 13px;">
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                        🔍 กรองข้อมูล
                    </button>
                    <a href="/pcc/safety/incidents" class="btn btn-secondary" style="font-size: 13px; padding: 8px 12px;">
                        ล้างค่า
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Incidents Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>📑 รายการอุบัติการณ์ความคลาดเคลื่อนทางยา (พบทั้งหมด <?= count($incidents) ?> เคส)</span>
            </div>
            <span class="badge badge-secondary">มาตรฐานความปลอดภัย 2P Safety / NRLS</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">รหัสอุบัติการณ์</th>
                            <th style="width: 120px;">วัน-เวลาเกิดเหตุ</th>
                            <th style="width: 110px;">ขั้นตอน (Stage)</th>
                            <th style="width: 140px;">ระดับความรุนแรง</th>
                            <th>รายการยาที่เกี่ยวข้อง & สรุปเหตุการณ์</th>
                            <th style="width: 150px;">ผู้รับผิดชอบ RCA</th>
                            <th style="width: 120px;">สถานะ</th>
                            <th style="width: 110px; text-align: right;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($incidents)): ?>
                            <?php foreach ($incidents as $inc): ?>
                                <tr>
                                    <td>
                                        <a href="/pcc/safety/incidents/<?= (int)$inc['incident_id'] ?>" style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 13.5px;">
                                            INC-<?= str_pad((string)$inc['incident_id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td style="font-size: 12.5px; white-space: nowrap;">
                                        <?= date('d/m/Y H:i', strtotime($inc['incident_date'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $stageLabels = [
                                            'prescribing' => 'สั่งยา',
                                            'transcribing' => 'คัดลอก/คีย์',
                                            'dispensing' => 'จัด/จ่ายยา',
                                            'administration' => 'บริหารยา',
                                            'monitoring' => 'ติดตามยา',
                                            'storage' => 'จัดเก็บยา'
                                        ];
                                        ?>
                                        <span class="badge badge-secondary" style="font-size: 11.5px;">
                                            <?= $stageLabels[$inc['incident_stage']] ?? $inc['incident_stage'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $cat = $inc['severity_category'];
                                        $badgeClass = in_array($cat, ['A','B']) ? 'badge-success' : (in_array($cat, ['C','D']) ? 'badge-warning' : 'badge-danger');
                                        ?>
                                        <span class="badge <?= $badgeClass ?>" style="font-size: 12px; font-weight: 700;">
                                            Category <?= $cat ?>
                                        </span>
                                        <?php if ($inc['is_near_miss']): ?>
                                            <span style="font-size: 11px; color: var(--success); font-weight: 600; display: block; margin-top: 2px;">
                                                🎯 Near Miss
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #0f172a; margin-bottom: 2px;">
                                            💊 <?= htmlspecialchars($inc['drugs_involved']) ?>
                                        </div>
                                        <div style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.4;">
                                            <?= htmlspecialchars(mb_strimwidth($inc['incident_description'], 0, 110, '...')) ?>
                                        </div>
                                        <?php if (!empty($inc['immediate_action_taken'])): ?>
                                            <div style="font-size: 11.5px; color: #166534; margin-top: 2px;">
                                                ⚡ <strong>แก้ไขเบื้องต้น:</strong> <?= htmlspecialchars(mb_strimwidth($inc['immediate_action_taken'], 0, 90, '...')) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12.5px;">
                                        <?= htmlspecialchars($inc['responsible_person'] ?: '-') ?>
                                        <?php if (!empty($inc['due_date'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                กำหนด: <?= date('d/m/Y', strtotime($inc['due_date'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($inc['status'] === 'closed'): ?>
                                            <span class="badge badge-success">✅ ปิดเคสแล้ว</span>
                                        <?php elseif ($inc['status'] === 'rca_completed'): ?>
                                            <span class="badge badge-info">🔍 วาง CAPA แล้ว</span>
                                        <?php elseif ($inc['status'] === 'investigating'): ?>
                                            <span class="badge badge-warning">⏳ สืบสวน</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">📥 รายงานใหม่</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <a href="/pcc/safety/incidents/<?= (int)$inc['incident_id'] ?>" class="btn btn-secondary" style="padding: 5px 12px; font-size: 12px;">
                                            สืบสวน/RCA ➔
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    ไม่พบรายการอุบัติการณ์ที่ตรงกับเงื่อนไขการค้นหา
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
