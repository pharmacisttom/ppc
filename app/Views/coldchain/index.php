<?php
use App\Core\CSRF;
?>
<div class="coldchain-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">ระบบบริหารจัดการลูกโซ่ความเย็น (Cold Chain Management 2.0 – 8.0 °C)</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                การควบคุมอุณหภูมิตู้เย็นเก็บวัคซีนและยาชีววัตถุตามมาตรฐานงานเภสัชกรรมปฐมภูมิ บันทึกวันละ 2 รอบ (เช้า 08:30 น. และ บ่าย 14:30 น.)
            </p>
        </div>
        <button class="btn btn-primary" onclick="openModal('recordTempModal')">
            🌡️ บันทึกอุณหภูมิใหม่
        </button>
    </div>

    <!-- Refrigerator Units Cards -->
    <div class="grid-cols-2">
        <?php foreach ($units as $u): ?>
            <div class="card" style="border-top: 4px solid var(--info);">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h3 style="font-size: 17px; font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($u['unit_name']) ?></h3>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                รหัสครุภัณฑ์: <?= htmlspecialchars($u['unit_code']) ?> • ยี่ห้อ/รุ่น: <?= htmlspecialchars($u['model'] ?? 'มาตรฐานชีววัตถุ') ?>
                            </div>
                        </div>
                        <span class="badge <?= (int)$u['total_excursions'] > 0 ? 'badge-danger' : 'badge-success' ?>">
                            <?= (int)$u['total_excursions'] ?> หลุดเกณฑ์สะสม
                        </span>
                    </div>

                    <div style="margin-top: 18px; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: var(--radius-sm); padding: 14px; display: flex; justify-content: space-around; align-items: center; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">ช่วงเกณฑ์มาตรฐาน</div>
                            <div style="font-size: 18px; font-weight: 700; color: #0f766e; font-family: 'Outfit', sans-serif;">
                                <?= (float)$u['min_temp'] ?>°C – <?= (float)$u['max_temp'] ?>°C
                            </div>
                        </div>
                        <div style="width: 1px; height: 35px; background: #99f6e4;"></div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">ระบบตรวจวัด</div>
                            <div style="font-size: 14px; font-weight: 600; color: var(--text-primary);">Data Logger + Digital</div>
                        </div>
                        <div style="width: 1px; height: 35px; background: #99f6e4;"></div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">มาตรการไฟสำรอง</div>
                            <div style="font-size: 14px; font-weight: 600; color: var(--success);">UPS สำรองไฟ 4 ชม.</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Temperature Logs Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📈 ประวัติการบันทึกอุณหภูมิตู้เย็นยาย้อนหลัง (30 วันล่าสุด)</span>
            </div>
            <span class="badge badge-secondary"><?= count($logs) ?> รายการล่าสุด</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>วันที่บันทึก</th>
                            <th>รอบการตรวจ</th>
                            <th>ตู้เย็น</th>
                            <th>อุณหภูมิปัจจุบัน (°C)</th>
                            <th>Min – Max (°C)</th>
                            <th>สถานะการประเมิน</th>
                            <th>มาตรการแก้ไขกรณีหลุดเกณฑ์ (CAPA)</th>
                            <th>ผู้บันทึก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    ยังไม่มีรายการบันทึกอุณหภูมิ
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                                <?php 
                                    $isEx = (int)$l['is_excursion'] === 1;
                                    $temp = (float)$l['current_temp'];
                                ?>
                                <tr style="<?= $isEx ? 'background: #fff5f5;' : '' ?>">
                                    <td><strong><?= htmlspecialchars($l['record_date']) ?></strong></td>
                                    <td>
                                        <span class="badge <?= $l['session'] === 'morning' ? 'badge-info' : 'badge-secondary' ?>">
                                            <?= $l['session'] === 'morning' ? '☀️ เช้า (08:30)' : '🌤️ บ่าย (14:30)' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($l['unit_name']) ?></td>
                                    <td>
                                        <strong style="font-size: 15px; color: <?= $isEx ? 'var(--danger)' : 'var(--primary-dark)' ?>;">
                                            <?= $temp ?> °C
                                        </strong>
                                    </td>
                                    <td><?= (float)$l['min_recorded'] ?> – <?= (float)$l['max_recorded'] ?> °C</td>
                                    <td>
                                        <span class="badge <?= $isEx ? 'badge-danger' : 'badge-success' ?>">
                                            <?= $isEx ? '⚠️ หลุดเกณฑ์ (Excursion)' : '✅ ปกติ (ในเกณฑ์)' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isEx && !empty($l['corrective_action'])): ?>
                                            <div style="font-size: 12px; color: #991b1b; max-width: 250px;">
                                                <?= htmlspecialchars($l['corrective_action']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars(($l['firstname'] ?? '') . ' ' . ($l['lastname'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Temperature -->
<div class="modal-backdrop" id="recordTempModal">
    <div class="modal-content">
        <form action="/hos/cold-chain/store" method="POST">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h3 style="font-size: 17px; font-weight: 700;">🌡️ บันทึกอุณหภูมิตู้เย็นยา Cold Chain</h3>
                <button type="button" onclick="closeModal('recordTempModal')" style="border:none; background:transparent; font-size:20px; cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">เลือกตู้เย็นยา</label>
                    <select name="unit_id" class="form-control" required>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= (int)$u['unit_id'] ?>"><?= htmlspecialchars($u['unit_name']) ?> (<?= htmlspecialchars($u['unit_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">วันที่ตรวจวัด</label>
                        <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">รอบเวลา</label>
                        <select name="session" class="form-control" required>
                            <option value="morning">☀️ รอบเช้า (08:30 น.)</option>
                            <option value="afternoon">🌤️ รอบบ่าย (14:30 น.)</option>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-3">
                    <div class="form-group">
                        <label class="form-label">อุณหภูมิปัจจุบัน (°C)</label>
                        <input type="number" step="0.1" name="current_temp" id="inputCurrentTemp" class="form-control" placeholder="เช่น 4.5" required oninput="checkExcursion(this.value)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min (°C)</label>
                        <input type="number" step="0.1" name="min_recorded" id="inputMinTemp" class="form-control" placeholder="เช่น 3.8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max (°C)</label>
                        <input type="number" step="0.1" name="max_recorded" id="inputMaxTemp" class="form-control" placeholder="เช่น 5.2">
                    </div>
                </div>

                <div id="excursionAlert" style="display: none; background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 12px; margin-bottom: 14px;">
                    <div style="font-weight: 700; color: #991b1b; font-size: 13px;">⚠️ คำเตือน: อุณหภูมิหลุดเกณฑ์ 2.0 – 8.0 °C!</div>
                    <div style="font-size: 12px; color: #7f1d1d; margin-top: 4px;">
                        ระบบกำหนดให้ต้องบันทึกมาตรการแก้ไข (Corrective Action / CAPA) ก่อนบันทึกข้อมูล
                    </div>
                </div>

                <div class="form-group" id="actionGroup" style="display: none; margin-bottom: 0;">
                    <label class="form-label" style="color: #991b1b;">มาตรการแก้ไขกรณีอุณหภูมิหลุดเกณฑ์ (Corrective Action) *</label>
                    <textarea name="corrective_action" id="inputAction" class="form-control" rows="2" placeholder="ระบุสาเหตุและการแก้ไข เช่น ปรับลูกบิดทำความเย็น, ย้ายวัคซีนไปยังกระติกน้ำแข็งสำรอง พร้อม Ice pack, แจ้งช่างซ่อม..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('recordTempModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูลอุณหภูมิ</button>
            </div>
        </form>
    </div>
</div>

<script>
function checkExcursion(val) {
    const t = parseFloat(val);
    const alertBox = document.getElementById('excursionAlert');
    const actionGroup = document.getElementById('actionGroup');
    const actionInput = document.getElementById('inputAction');

    if (!isNaN(t) && (t < 2.0 || t > 8.0)) {
        alertBox.style.display = 'block';
        actionGroup.style.display = 'block';
        actionInput.required = true;
    } else {
        alertBox.style.display = 'none';
        actionGroup.style.display = 'none';
        actionInput.required = false;
    }
}
</script>
