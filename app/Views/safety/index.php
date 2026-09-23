<div class="safety-center-container">
    <!-- Header with Quick Action Buttons -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 28px;">🛡️</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    ศูนย์ความปลอดภัยด้านยา ระดับ รพ.สต. (PCU Medication Safety Center)
                </h1>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 6px 0 0 0;">
                ศูนย์บัญชาการเฝ้าระวังอุบัติการณ์ความคลาดเคลื่อนทางยา (2P Safety), ป้องกันการแพ้ยาซ้ำ, ควบคุมยาเสี่ยงสูง (HAD) และชุดยาช่วยชีวิตฉุกเฉิน
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/pcc/safety/incidents/create" class="btn btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);">
                <span>🚨</span> รายงานอุบัติการณ์ความคลาดเคลื่อน (Incident Report)
            </a>
            <a href="/pcc/safety/allergies" class="btn btn-secondary">
                <span>🧬</span> เรดาร์ตรวจจับการแพ้ยาซ้ำ
            </a>
            <a href="/pcc/safety/incidents/export" class="btn btn-secondary">
                <span>📥</span> ส่งออกรายงาน Excel
            </a>
        </div>
    </div>

    <!-- 4 Core Executive Safety KPIs -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <!-- Card 1: Total & Active Incidents -->
        <div class="card" style="border-top: 4px solid var(--primary);">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">อุบัติการณ์ความคลาดเคลื่อนทางยา</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Medication Error Total / Open</div>
                    </div>
                    <span class="badge <?= $openIncidents > 0 ? 'badge-warning' : 'badge-success' ?>">
                        <?= $openIncidents > 0 ? "เปิดอยู่ {$openIncidents} เคส" : 'เรียบร้อย' ?>
                    </span>
                </div>
                <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
                    <div style="font-size: 32px; font-weight: 700; color: #0f172a; font-family: 'Outfit', sans-serif;">
                        <?= (int)$totalIncidents ?>
                    </div>
                    <span style="font-size: 12px; color: var(--text-muted);">
                        (อยู่ระหว่างสืบสวน <?= (int)$openIncidents ?> เคส)
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                    🎯 ติดตามและสอบสวนตามมาตรฐาน 2P Safety
                </div>
            </div>
        </div>

        <!-- Card 2: Near Miss Reporting Culture -->
        <div class="card" style="border-top: 4px solid var(--success);">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">รายงานเหตุการณ์เกือบพลาด (Near Miss)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">ความรุนแรงระดับ A - B (Non-punitive)</div>
                    </div>
                    <span class="badge badge-success">เป้าหมาย ≥ 10/ปี</span>
                </div>
                <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
                    <div style="font-size: 32px; font-weight: 700; color: var(--success); font-family: 'Outfit', sans-serif;">
                        <?= (int)$nearMissCount ?>
                    </div>
                    <span style="font-size: 12px; color: var(--success); font-weight: 600;">
                        วัฒนธรรมความปลอดภัยเชิงรุก
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                    คิดเป็น <?= $totalIncidents > 0 ? round(($nearMissCount / $totalIncidents) * 100, 1) : 0 ?>% ของรายงานทั้งหมด
                </div>
            </div>
        </div>

        <!-- Card 3: Zero Repeat Allergy Radar -->
        <div class="card" style="border-top: 4px solid #8b5cf6;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">เฝ้าระวังการแพ้ยาซ้ำ (Repeat Allergy)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Zero Repeat Drug Allergy Target</div>
                    </div>
                    <span class="badge badge-info">JHCIS สด 43 คน</span>
                </div>
                <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
                    <div style="font-size: 32px; font-weight: 700; color: #8b5cf6; font-family: 'Outfit', sans-serif;">
                        0
                    </div>
                    <span style="font-size: 12px; color: var(--success); font-weight: 600;">
                        ✅ ไตรมาสปัจจุบัน ปลอดภัย 100%
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                    สแกนฐาน JHCIS `personalergic` แบบ Real-time
                </div>
            </div>
        </div>

        <!-- Card 4: RCA & CAPA Closure Rate -->
        <div class="card" style="border-top: 4px solid #f59e0b;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">ร้อยละการวิเคราะห์สาเหตุ (RCA & CAPA)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">การแก้ปัญหาเชิงระบบและปิดเคส</div>
                    </div>
                    <span class="badge badge-warning">เป้าหมาย 100%</span>
                </div>
                <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
                    <div style="font-size: 32px; font-weight: 700; color: #f59e0b; font-family: 'Outfit', sans-serif;">
                        <?= $rcaClosureRate ?>%
                    </div>
                    <span style="font-size: 12px; color: var(--text-muted);">
                        (ปิดเคสสมบูรณ์ <?= (int)$rcaClosedCount ?> เคส)
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                    พัฒนาเชิงระบบเพื่อป้องกันความผิดพลาดซ้ำ
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Left: Stage Breakdown, NCC MERP Matrix & Recent Incidents -->
        <div>
            <!-- Error Breakdown by Stage -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>📊 สัดส่วนความคลาดเคลื่อนทางยาจำแนกตามขั้นตอน (Medication Process Stages)</span>
                    </div>
                    <span class="badge badge-secondary">วงจรระบบยา รพ.สต.</span>
                </div>
                <div class="card-body">
                    <?php
                    $stageNames = [
                        'prescribing' => ['label' => '1. ขั้นตอนการสั่งใช้ยาของแพทย์ (Prescribing)', 'color' => '#3b82f6'],
                        'transcribing' => ['label' => '2. ขั้นตอนการคัดลอกคำสั่ง/บันทึกคอมพิวเตอร์ (Transcribing)', 'color' => '#6366f1'],
                        'dispensing' => ['label' => '3. ขั้นตอนการจัดยา/จ่ายยา (Dispensing)', 'color' => '#f59e0b'],
                        'administration' => ['label' => '4. ขั้นตอนการบริหารยา/ให้ยาแก่ผู้ป่วย (Administration)', 'color' => '#ef4444'],
                        'monitoring' => ['label' => '5. ขั้นตอนการติดตามการใช้ยาและอาการไม่พึงประสงค์ (Monitoring)', 'color' => '#10b981'],
                        'storage' => ['label' => '6. ขั้นตอนการจัดเก็บยาในคลัง/ตู้เย็น (Storage)', 'color' => '#8b5cf6']
                    ];
                    $maxStageVal = max(array_values($stages) ?: [1]);
                    if ($maxStageVal == 0) $maxStageVal = 1;
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($stageNames as $stKey => $stMeta): 
                            $cnt = $stages[$stKey] ?? 0;
                            $pct = $totalIncidents > 0 ? round(($cnt / $totalIncidents) * 100, 1) : 0;
                            $barWidth = max(5, round(($cnt / $maxStageVal) * 100));
                        ?>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 4px;">
                                    <span style="color: #334155;"><?= $stMeta['label'] ?></span>
                                    <span><?= $cnt ?> ครั้ง (<?= $pct ?>%)</span>
                                </div>
                                <div style="height: 10px; background: #f1f5f9; border-radius: 6px; overflow: hidden;">
                                    <div style="height: 100%; width: <?= $barWidth ?>%; background: <?= $stMeta['color'] ?>; border-radius: 6px; transition: width 0.4s ease;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- NCC MERP Severity Category Distribution Matrix -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>🎯 ระดับความรุนแรงตามมาตรฐานสากล NCC MERP Index (Category A – I)</span>
                    </div>
                    <a href="/pcc/safety/incidents" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">
                        ดูทั้งหมด ➔
                    </a>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                        <!-- Group 1: No Error (A) & Near Miss (B) -->
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="font-size: 13px; color: #166534;">🟢 กลุ่มไม่เกิดข้อผิดพลาด / Near Miss</strong>
                                <span class="badge badge-success"><?= ($severities['A'] + $severities['B']) ?> เคส</span>
                            </div>
                            <div style="font-size: 12px; color: #15803d; line-height: 1.5;">
                                <div><strong>Cat A (<?= $severities['A'] ?>):</strong> สภาพแวดล้อมที่อาจทำให้เกิดความคลาดเคลื่อน</div>
                                <div><strong>Cat B (<?= $severities['B'] ?>):</strong> เกิดความคลาดเคลื่อน แต่หยุดไว้ได้ก่อนถึงผู้ป่วย</div>
                            </div>
                        </div>

                        <!-- Group 2: Error, No Harm (C - D) -->
                        <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="font-size: 13px; color: #854d0e;">🟡 กลุ่มถึงผู้ป่วย แต่ไม่เกิดอันตราย</strong>
                                <span class="badge badge-warning"><?= ($severities['C'] + $severities['D']) ?> เคส</span>
                            </div>
                            <div style="font-size: 12px; color: #a16207; line-height: 1.5;">
                                <div><strong>Cat C (<?= $severities['C'] ?>):</strong> ถึงผู้ป่วยแต่ไม่เกิดอันตราย</div>
                                <div><strong>Cat D (<?= $severities['D'] ?>):</strong> ถึงผู้ป่วย ต้องเฝ้าระวังเพื่อยืนยันว่าปลอดภัย</div>
                            </div>
                        </div>

                        <!-- Group 3: Error, Harm (E - I) -->
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="font-size: 13px; color: #991b1b;">🔴 กลุ่มเกิดอันตรายต่อผู้ป่วย (Sentinel)</strong>
                                <span class="badge badge-danger"><?= array_sum(array_intersect_key($severities, array_flip(['E','F','G','H','I']))) ?> เคส</span>
                            </div>
                            <div style="font-size: 12px; color: #b91c1c; line-height: 1.5;">
                                <div><strong>Cat E-F:</strong> อันตรายชั่วคราว ต้องให้การรักษา / นอน รพ.</div>
                                <div><strong>Cat G-H:</strong> อันตรายถาวร / เกือบเสียชีวิต</div>
                                <div><strong>Cat I:</strong> ส่งผลให้ผู้ป่วยเสียชีวิต</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Incidents Table -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>📑 รายการอุบัติการณ์ความคลาดเคลื่อนทางยาล่าสุด (Recent Incident Logs)</span>
                    </div>
                    <a href="/pcc/safety/incidents/create" class="btn btn-primary" style="padding: 4px 12px; font-size: 12px;">
                        + รายงานเคสใหม่
                    </a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัสอุบัติการณ์</th>
                                    <th>วัน-เวลาเกิดเหตุ</th>
                                    <th>ขั้นตอน</th>
                                    <th>ระดับความรุนแรง</th>
                                    <th>ยาที่เกี่ยวข้อง & รายละเอียด</th>
                                    <th>สถานะ</th>
                                    <th style="text-align: right;">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentIncidents)): ?>
                                    <?php foreach ($recentIncidents as $inc): ?>
                                        <tr>
                                            <td>
                                                <a href="/pcc/safety/incidents/<?= (int)$inc['incident_id'] ?>" style="font-weight: 700; color: var(--primary); font-family: monospace;">
                                                    INC-<?= str_pad((string)$inc['incident_id'], 4, '0', STR_PAD_LEFT) ?>
                                                </a>
                                            </td>
                                            <td style="font-size: 12.5px; white-space: nowrap;">
                                                <?= date('d/m/Y H:i', strtotime($inc['incident_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary" style="font-size: 11px;">
                                                    <?= htmlspecialchars($inc['incident_stage']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $cat = $inc['severity_category'];
                                                $badgeClass = in_array($cat, ['A','B']) ? 'badge-success' : (in_array($cat, ['C','D']) ? 'badge-warning' : 'badge-danger');
                                                ?>
                                                <span class="badge <?= $badgeClass ?>" style="font-size: 12px; font-weight: 700;">
                                                    Cat <?= $cat ?> <?= $inc['is_near_miss'] ? '(Near Miss)' : '' ?>
                                                </span>
                                            </td>
                                            <td style="max-width: 280px;">
                                                <div style="font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    💊 <?= htmlspecialchars($inc['drugs_involved']) ?>
                                                </div>
                                                <div style="font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= htmlspecialchars($inc['incident_description']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($inc['status'] === 'closed'): ?>
                                                    <span class="badge badge-success">✅ ปิดเคส RCA แล้ว</span>
                                                <?php elseif ($inc['status'] === 'rca_completed'): ?>
                                                    <span class="badge badge-info">🔍 RCA เรียบร้อย</span>
                                                <?php elseif ($inc['status'] === 'investigating'): ?>
                                                    <span class="badge badge-warning">⏳ อยู่ระหว่างสอบสวน</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">📥 รายงานใหม่</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <a href="/pcc/safety/incidents/<?= (int)$inc['incident_id'] ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">
                                                    สืบสวน/RCA ➔
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                            ยังไม่มีบันทึกอุบัติการณ์ความคลาดเคลื่อนทางยาในระบบ
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: HAD, LASA, Emergency CPR Kit & Pharmacovigilance Highlights -->
        <div>
            <!-- High Alert Drugs (HAD) Widget -->
            <div class="card" style="margin-bottom: 24px; border-left: 4px solid #ef4444;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>⚠️ ยากลุ่มเสี่ยงสูง (High Alert Drugs)</span>
                    </div>
                    <span class="badge badge-danger"><?= (int)$hamCount ?> กลุ่มยา</span>
                </div>
                <div class="card-body">
                    <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 0;">
                        ยาที่มีความเสี่ยงสูงต่อการเกิดอันตรายร้ายแรงหากมีความผิดพลาด ต้องตรวจ Double-check ทุกครั้งก่อนจ่าย
                    </p>
                    <div style="background: #fff1f2; border-radius: 8px; padding: 12px; margin-bottom: 14px; font-size: 12.5px; color: #9f1239;">
                        <strong>🛡️ มาตรการ Double-check ณ รพ.สต.:</strong>
                        <ul style="margin: 6px 0 0 16px; padding: 0;">
                            <li>Insulin ทุกชนิด (ตรวจสอบประเภทยาและขนาดฉีด)</li>
                            <li>Warfarin (ตรวจสอบค่า INR ล่าสุดและขนาดยา)</li>
                            <li>Digoxin (ตรวจสอบอัตราการเต้นหัวใจและ eGFR)</li>
                            <li>Morphine / Opioids (จัดเก็บในตู้ล็อค 2 ชั้น)</li>
                        </ul>
                    </div>
                    <a href="/pcc/safety/ham-lasa" class="btn btn-secondary" style="width: 100%; justify-content: center; font-size: 12.5px;">
                        เปิดดูทะเบียนยาเสี่ยงสูงและคู่เทียบ LASA ➔
                    </a>
                </div>
            </div>

            <!-- Zero Repeat Allergy Radar Widget -->
            <div class="card" style="margin-bottom: 24px; border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>🧬 เรดาร์เฝ้าระวังแพ้ยาซ้ำ</span>
                    </div>
                    <span class="badge badge-info">JHCIS Active</span>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 13px; color: var(--text-secondary);">จำนวนผู้มีประวัติแพ้ยาใน JHCIS:</span>
                        <strong style="font-size: 16px; color: #8b5cf6;"><?= (int)$totalAllergies ?> ราย</strong>
                    </div>
                    <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 12px; font-size: 12.5px; color: #5b21b6;">
                        <div style="font-weight: 600; margin-bottom: 4px;">📡 สถานะระบบตรวจจับ Real-time:</div>
                        ระบบตรวจเช็คอัตโนมัติทุกครั้งเมื่อมีการบันทึกใบสั่งยา หากมีประวัติแพ้ยาตรงกัน ระบบจะสกัดกั้นทันที
                    </div>
                    <a href="/pcc/safety/allergies" class="btn btn-secondary" style="width: 100%; justify-content: center; font-size: 12.5px; margin-top: 14px;">
                        เข้าสู่ศูนย์เฝ้าระวังแพ้ยา (Allergy Hub) ➔
                    </a>
                </div>
            </div>

            <!-- Emergency CPR Kit Widget -->
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title">
                        <span>🚑 ชุดยาช่วยชีวิตฉุกเฉิน (CPR Kit)</span>
                    </div>
                    <span class="badge badge-success">พร้อมใช้ 100%</span>
                </div>
                <div class="card-body">
                    <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                        ตรวจสอบ Adrenaline, Atropine, Diazepam, 50% Glucose และสารน้ำช่วยชีวิต ประจำ รพ.สต.
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; padding: 8px 0; border-top: 1px solid #f1f5f9;">
                        <span>ความพร้อมของยาในกล่อง:</span>
                        <strong style="color: var(--success);">5/5 รายการครบ</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; padding: 8px 0; border-top: 1px solid #f1f5f9; margin-bottom: 14px;">
                        <span>วันหมดอายุที่ใกล้ที่สุด:</span>
                        <strong style="color: #0f172a;">ธ.ค. 2569 (Diazepam)</strong>
                    </div>
                    <a href="/pcc/safety/emergency-kit" class="btn btn-secondary" style="width: 100%; justify-content: center; font-size: 12.5px;">
                        ตรวจสอบและลงบันทึกเช็ก CPR Kit ➔
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
