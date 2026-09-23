<div class="ham-lasa-container">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 26px;">⚠️</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    การจัดการยากลุ่มเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA)
                </h1>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 6px 0 0 0;">
                ระบบควบคุมความปลอดภัยด้านยาตามมาตรฐานสากล: การกำหนดสัญลักษณ์เตือน ป้ายเตือน Tall Man Lettering และการแยกตำแหน่งจัดเก็บ
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/pcc/safety" class="btn btn-secondary">
                🛡️ กลับศูนย์บัญชาการความปลอดภัย
            </a>
            <a href="/pcc/reports/ham-lasa/export" class="btn btn-secondary">
                📥 ส่งออกทะเบียนเป็น Excel
            </a>
        </div>
    </div>

    <!-- High Alert Medications (HAM) Card -->
    <div class="card" style="border-top: 4px solid #ef4444; margin-bottom: 24px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title" style="color: #991b1b;">
                <span>⚠️ บัญชียากลุ่มเสี่ยงสูงระดับ รพ.สต. (High Alert Medications: HAM)</span>
            </div>
            <span class="badge badge-danger">Double Check Protocol Required</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 110px;">รหัสยา</th>
                            <th style="width: 260px;">ชื่อสามัญทางยา (Generic Name)</th>
                            <th style="width: 170px;">หมวดหมู่ความเสี่ยง (Risk Category)</th>
                            <th>ข้อควรระวังสำคัญและจุดอันตราย (Clinical Precautions)</th>
                            <th style="width: 140px;">การ Double Check</th>
                            <th style="width: 180px;">คำแนะนำการจัดเก็บ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hams)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    ไม่พบรายการยากลุ่มเสี่ยงสูง
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hams as $h): ?>
                                <tr>
                                    <td><span style="font-family: monospace; font-weight: 700; color: #991b1b;"><?= htmlspecialchars($h['drug_code']) ?></span></td>
                                    <td><strong style="color: #0f172a; font-size: 13.5px;"><?= htmlspecialchars($h['generic_name']) ?></strong></td>
                                    <td><span class="badge badge-danger"><?= htmlspecialchars($h['risk_category'] ?? 'High Alert') ?></span></td>
                                    <td style="font-size: 13px; color: #334155; line-height: 1.5;"><?= htmlspecialchars($h['precautions'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($h['double_check_required'])): ?>
                                            <span class="badge badge-warning" style="font-weight: 700;">✓ ต้องตรวจ 2 คน</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">ตรวจตามมาตรฐาน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12.5px; color: var(--text-secondary);"><?= htmlspecialchars($h['storage_instructions'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Look-Alike Sound-Alike (LASA) Card -->
    <div class="card" style="border-top: 4px solid #f59e0b;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title" style="color: #92400e;">
                <span>🔤 บัญชียาชื่อพ้องมองคล้าย (Look-Alike Sound-Alike: LASA & Tall Man Lettering)</span>
            </div>
            <span class="badge badge-warning">Physical Separation Required</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">ประเภทความคล้าย</th>
                            <th style="width: 220px;">รายการยาที่ 1 (Tall Man 1)</th>
                            <th style="width: 220px;">รายการยาที่ 2 (Tall Man 2)</th>
                            <th>ข้อควรระวังและคำเตือน (Warning Note)</th>
                            <th style="width: 160px;">การแยกตำแหน่งจัดเก็บ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lasas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    ไม่พบรายการยาชื่อพ้องมองคล้าย
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lasas as $l): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $l['lasa_type'] === 'look_alike' ? 'badge-info' : ($l['lasa_type'] === 'sound_alike' ? 'badge-warning' : 'badge-danger') ?>">
                                            <?php
                                            if ($l['lasa_type'] === 'look_alike') echo '👁️ มองคล้าย (Look-Alike)';
                                            elseif ($l['lasa_type'] === 'sound_alike') echo '👂 ชื่อพ้อง (Sound-Alike)';
                                            else echo '⚠️ ทั้งชื่อและรูป (Both)';
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; background: #fefce8; padding: 6px 10px; border-radius: 6px; border: 1px solid #fef08a;">
                                            <?= htmlspecialchars($l['tall_man_1'] ?: $l['drug_name_1']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; background: #fefce8; padding: 6px 10px; border-radius: 6px; border: 1px solid #fef08a;">
                                            <?= htmlspecialchars($l['tall_man_2'] ?: $l['drug_name_2']) ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 13px; color: #334155; line-height: 1.4;">
                                        <?= htmlspecialchars($l['warning_note'] ?? 'ให้ตรวจสอบข้อบ่งใช้และขนาดยาก่อนจ่ายยาทุกครั้ง') ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($l['storage_separation_required'])): ?>
                                            <span class="badge badge-success">✅ แยกช่องเก็บเด็ดขาด</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">⚠️ ต้องตรวจช่องเก็บ</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
