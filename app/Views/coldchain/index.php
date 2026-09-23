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
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button type="button" class="btn btn-secondary btn-sm" 
                                    onclick='openEditUnitModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                    style="padding: 3px 8px; font-size: 11.5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;">
                                ✏️ แก้ไขข้อมูลตู้
                            </button>
                            <span class="badge <?= (int)$u['total_excursions'] > 0 ? 'badge-danger' : 'badge-success' ?>">
                                <?= (int)$u['total_excursions'] ?> หลุดเกณฑ์สะสม
                            </span>
                        </div>
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
        <form action="/pcc/cold-chain/store" method="POST">
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

function openEditUnitModal(u) {
    document.getElementById('editUnitId').value = u.unit_id || '';
    document.getElementById('editUnitCode').value = u.unit_code || '';
    document.getElementById('editUnitName').value = u.unit_name || '';
    document.getElementById('editMinTemp').value = u.min_temp !== undefined ? u.min_temp : '2.0';
    document.getElementById('editMaxTemp').value = u.max_temp !== undefined ? u.max_temp : '8.0';
    document.getElementById('editModelInfo').value = u.model_info || u.model || '';
    document.getElementById('editUnitActive').value = u.is_active !== undefined ? u.is_active : '1';

    document.getElementById('editUnitModal').style.display = 'flex';
}

function closeEditUnitModal() {
    document.getElementById('editUnitModal').style.display = 'none';
}
</script>

<!-- Modal Edit Cold Chain Unit -->
<div id="editUnitModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/cold-chain/update-unit">
            <?= CSRF::field() ?>
            <input type="hidden" name="unit_id" id="editUnitId">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ แก้ไขข้อมูลตู้เย็น/อุปกรณ์ควบคุมความเย็น
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditUnitModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">รหัสครุภัณฑ์ / อุปกรณ์ *</label>
                        <input type="text" name="unit_code" id="editUnitCode" class="form-control" required placeholder="เช่น CC-01">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สถานะการใช้งาน</label>
                        <select name="is_active" id="editUnitActive" class="form-control">
                            <option value="1">เปิดใช้งานปกติ (Active)</option>
                            <option value="0">ระงับการใช้/ส่งซ่อม (Inactive)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">ชื่อตู้เย็น / ตำแหน่งติดตั้ง *</label>
                    <input type="text" name="unit_name" id="editUnitName" class="form-control" required placeholder="เช่น ตู้เย็นเก็บวัคซีนหลัก (ห้องยา)">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">อุณหภูมิต่ำสุดที่ยอมรับ (°C)</label>
                        <input type="number" step="0.1" name="min_temp" id="editMinTemp" class="form-control" value="2.0" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">อุณหภูมิสูงสุดที่ยอมรับ (°C)</label>
                        <input type="number" step="0.1" name="max_temp" id="editMaxTemp" class="form-control" value="8.0" required>
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">ยี่ห้อ / รุ่น / ข้อมูลทางเทคนิค</label>
                    <input type="text" name="model_info" id="editModelInfo" class="form-control" placeholder="เช่น Panasonic MPR-414F, Dometic TCX 21">
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditUnitModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกข้อมูล & Audit Log</button>
            </div>
        </form>
    </div>
</div>

