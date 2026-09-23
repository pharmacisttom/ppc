<div class="safety-warfarin-container">
    <!-- Top Stats -->
    <div class="grid-cols-4" style="margin-bottom: 20px;">
        <div class="stat-card" style="border-top: 4px solid #4a154b;">
            <div class="stat-label">ผู้ป่วย Warfarin ในพื้นที่ รพ.สต.</div>
            <div class="stat-value" style="color: #4a154b;"><?= (int)($stats['total'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">High Alert Anticoagulant (HAM)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #10b981;">
            <div class="stat-label">INR ในเกณฑ์เป้าหมาย (In Range)</div>
            <div class="stat-value" style="color: #10b981;"><?= (int)($stats['in_range'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">Target INR 2.0 – 3.0 (ปลอดภัย)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #f59e0b;">
            <div class="stat-label">INR ต่ำกว่าเป้าหมาย (&lt; 2.0)</div>
            <div class="stat-value" style="color: #f59e0b;"><?= (int)($stats['below'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">เสี่ยงต่อภาวะลิ่มเลือดอุดตัน (Thrombosis)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #ef4444;">
            <div class="stat-label">INR เกินเป้าหมาย / วิกฤต (&gt; 3.0)</div>
            <div class="stat-value" style="color: #ef4444;"><?= (int)(($stats['above'] ?? 0) + ($stats['critical'] ?? 0)) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">เสี่ยงต่อเลือดออกรุนแรง (Bleeding)</div>
        </div>
    </div>

    <!-- Search & Guidance Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <form action="/pcc/safety/warfarin" method="GET" style="display: flex; gap: 12px; align-items: center; flex: 1; max-width: 600px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาด้วยชื่อผู้ป่วย, PID, ข้อบ่งใช้ (AF, DVT, Valve)..." class="form-control" style="flex: 1; padding: 10px 14px; font-size: 14px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        🔍 ค้นหา
                    </button>
                    <?php if (!empty($query)): ?>
                        <a href="/pcc/safety/warfarin" class="btn btn-secondary">ล้างค้นหา</a>
                    <?php endif; ?>
                </form>
                <div style="display: flex; gap: 8px;">
                    <a href="/pcc/exchange/file/LABFU" class="btn btn-secondary">
                        📥 แฟ้มผลแล็บ LABFU.txt
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="openModal('warfarinGuideModal')">
                        ℹ️ คู่มือสีเม็ดยา & DDIs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Warfarin Registry Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🩸 ทะเบียนผู้ป่วยคลินิกยาวาร์ฟาริน (Warfarin Clinic Monitoring & Safety)</span>
            </div>
            <span class="badge badge-info">เครือข่าย CUP รพ.ปลวกแดง</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th>PID</th>
                            <th>ชื่อ-นามสกุล ผู้ป่วย</th>
                            <th>ข้อบ่งใช้ (Indication)</th>
                            <th style="text-align: center;">เป้าหมาย (Target INR)</th>
                            <th style="text-align: right;">ขนาดยา/สัปดาห์</th>
                            <th style="text-align: center;">ค่า INR ล่าสุด</th>
                            <th>วันที่เจาะเลือด</th>
                            <th style="text-align: center;">สถานะการควบคุม</th>
                            <th>ความเสี่ยงเลือดออก</th>
                            <th style="text-align: center;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 36px;">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🩸</div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 14px;">ไม่พบประวัติผู้ป่วยรับยา Warfarin ในฐานข้อมูล JHCIS ระดับ รพ.สต.</div>
                                    <div style="font-size: 12.5px; color: #64748b; margin-top: 4px;">ผู้ป่วยรับยาวาร์ฟารินส่วนใหญ่ได้รับการรักษา ติดตามค่า INR และปรับขนาดยาที่ รพ.แม่ข่าย (CUP รพ.ปลวกแดง)</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $idx => $p): ?>
                                <?php
                                    $st = $p['inr_status'];
                                    $stClass = 'badge-success';
                                    $stText = '🟢 In Range';
                                    if ($st === 'below_target') {
                                        $stClass = 'badge-warning';
                                        $stText = '🟡 Below Target (< 2.0)';
                                    } elseif ($st === 'above_target') {
                                        $stClass = 'badge-danger';
                                        $stText = '🟠 Above Target (> 3.0)';
                                    } elseif ($st === 'critical') {
                                        $stClass = 'badge-danger';
                                        $stText = '🔴 Critical Bleeding Risk (> 4.5)';
                                    }
                                ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                    <td><span class="badge badge-secondary">PID <?= (int)$p['pid'] ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['patient']['full_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= htmlspecialchars($p['patient']['gender']) ?> / อายุ <?= htmlspecialchars($p['patient']['age']) ?> ปี • <?= htmlspecialchars($p['patient']['phone'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['indication']) ?></strong>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: #0d9488;">
                                        <?= htmlspecialchars($p['target_text']) ?>
                                    </td>
                                    <td style="text-align: right; font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;">
                                        <?= (float)$p['weekly_dose'] ?> mg
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 700; color: <?= ($st === 'in_range') ? '#059669' : '#dc2626' ?>;">
                                            <?= (float)$p['latest_inr'] ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary); white-space: nowrap;">
                                        <?= htmlspecialchars($p['latest_inr_date']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $stClass ?>" style="font-size: 11px;">
                                            <?= $stText ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary);">
                                        <?= htmlspecialchars($p['bleeding_risk']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="/pcc/patients/<?= (int)$p['pid'] ?>" class="btn btn-sm btn-primary" style="font-size: 12px;">
                                            เปิดแฟ้มยา ➔
                                        </a>
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

<!-- Warfarin Clinical Guidance Modal -->
<div id="warfarinGuideModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 12px; width: 650px; max-width: 90%; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 17px; color: #4a154b;">🩸 คู่มือการดูแลผู้ป่วยยา Warfarin ปฐมภูมิ</h3>
            <button type="button" onclick="closeModal('warfarinGuideModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div style="font-size: 13.5px; line-height: 1.5; color: #334155;">
            <h4 style="color: #0f172a; margin-bottom: 6px;">1. สีและขนาดยาเม็ดมาตรฐาน:</h4>
            <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                <div style="background: #f3e8ff; border: 1px solid #d8b4fe; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center;">
                    <div style="font-weight: 700; color: #6b21a8;">2 mg (สีม่วงลาเวนเดอร์)</div>
                </div>
                <div style="background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center;">
                    <div style="font-weight: 700; color: #0369a1;">3 mg (สีฟ้า/น้ำตาล)</div>
                </div>
                <div style="background: #fce7f3; border: 1px solid #fbcfe8; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center;">
                    <div style="font-weight: 700; color: #be185d;">5 mg (สีชมพู)</div>
                </div>
            </div>

            <h4 style="color: #0f172a; margin-bottom: 6px;">2. อันตรกิริยาระหว่างยาที่ต้องระวังสูง (DDIs Alert):</h4>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li><strong>ยาที่เพิ่มฤทธิ์ Warfarin (เสี่ยงเลือดออก):</strong> Cotrimoxazole, Fluconazole, Metronidazole, Amiodarone, Paracetamol ขนาดสูง (> 2 g/วัน ต่อเนื่อง)</li>
                <li><strong>ยาที่ลดฤทธิ์ Warfarin (เสี่ยงลิ่มเลือดอุดตัน):</strong> Rifampicin, Carbamazepine, Phenobarbital, Phenytoin</li>
                <li><strong>ยาต้านการอักเสบที่ไม่ใช่สเตียรอยด์ (NSAIDs):</strong> ห้ามใช้ร่วมกัน เนื่องจากระคายเคืองกระเพาะอาหารและยับยั้งเกล็ดเลือด</li>
            </ul>

            <h4 style="color: #0f172a; margin-bottom: 6px;">3. สัญญาณเตือนภาวะเลือดออกผิดปกติ (Bleeding Signs):</h4>
            <p style="color: #dc2626; font-weight: 600;">
                เลือดกำเดาไหลไม่หยุด, เลือดออกตามไรฟันมากผิดปกติ, ปัสสาวะสีน้ำตาลแดง, อุจจาระสีดำเหมือนยางมะตอย, รอยจ้ำเลือดขนาดใหญ่ตามผิวหนัง — หากพบให้หยุดยาและส่ง รพ.ปลวกแดง ทันที
            </p>
        </div>
        <div style="text-align: right; margin-top: 18px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('warfarinGuideModal')">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>
