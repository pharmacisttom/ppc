<div class="ham-lasa-container">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">การจัดการยากลุ่มเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA)</h2>
        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
            ระบบควบคุมความปลอดภัยด้านยาตามมาตรฐานสากล: การกำหนดสัญลักษณ์เตือน ป้ายเตือน Tall Man Lettering และการแยกตำแหน่งจัดเก็บ
        </p>
    </div>

    <!-- High Alert Medications (HAM) Card -->
    <div class="card" style="border-top: 4px solid var(--danger);">
        <div class="card-header">
            <div class="card-title" style="color: #991b1b;">
                <span>⚠️ บัญชียากลุ่มเสี่ยงสูงระดับ รพ.สต. (High Alert Medications: HAM)</span>
            </div>
            <span class="badge badge-danger">Double Check Protocol</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่อสามัญทางยา (Generic Name)</th>
                            <th>กลุ่มยา (Drug Class)</th>
                            <th>ระดับความเสี่ยง</th>
                            <th>ข้อควรระวังสำคัญและจุดอันตราย (Clinical Precautions)</th>
                            <th>ขนาดยาสูงสุด / ติดตาม</th>
                            <th>ยาต้านพิษ (Antidote)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hams)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    ไม่พบรายการ HAM
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hams as $h): ?>
                                <tr>
                                    <td><strong style="color: #991b1b;"><?= htmlspecialchars($h['generic_name']) ?></strong></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($h['drug_class'] ?? '-') ?></span></td>
                                    <td>
                                        <span class="badge badge-danger"><?= htmlspecialchars($h['risk_level'] ?? 'High') ?></span>
                                    </td>
                                    <td style="font-size: 13px; color: #334155;"><?= htmlspecialchars($h['precautions'] ?? '-') ?></td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars($h['max_daily_dose'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($h['antidote'])): ?>
                                            <span class="badge badge-success"><?= htmlspecialchars($h['antidote']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
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

    <!-- Look-Alike Sound-Alike (LASA) Card -->
    <div class="card" style="border-top: 4px solid var(--warning);">
        <div class="card-header">
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
                            <th>ประเภทยาสับสน</th>
                            <th>รายการยาที่ 1 (Tall Man 1)</th>
                            <th>รายการยาที่ 2 (Tall Man 2)</th>
                            <th>สาเหตุความสับสน</th>
                            <th>มาตรการป้องกันความผิดพลาด</th>
                            <th>การแยกตำแหน่งจัดเก็บ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lasas)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    ไม่พบรายการ LASA
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lasas as $l): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $l['lasa_type'] === 'look_alike' ? 'badge-info' : 'badge-warning' ?>">
                                            <?= $l['lasa_type'] === 'look_alike' ? 'มองคล้าย (Look-Alike)' : 'ชื่อพ้อง (Sound-Alike)' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="font-size: 14px;"><?= htmlspecialchars($l['tall_man_1'] ?? $l['drug_name_1']) ?></strong>
                                    </td>
                                    <td>
                                        <strong style="font-size: 14px;"><?= htmlspecialchars($l['tall_man_2'] ?? $l['drug_name_2']) ?></strong>
                                    </td>
                                    <td style="font-size: 13px; color: var(--text-secondary);"><?= htmlspecialchars($l['similarity_reason'] ?? '-') ?></td>
                                    <td style="font-size: 13px; color: #0f766e; font-weight: 500;"><?= htmlspecialchars($l['preventive_action'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($l['is_separated_storage'])): ?>
                                            <span class="badge badge-success">✅ แยกช่องเก็บเด็ดขาด</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">⚠️ ต้องตรวจสอบช่องเก็บ</span>
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
