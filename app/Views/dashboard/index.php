<div class="dashboard-container">
    <!-- Stat Widgets -->
    <div class="grid-cols-4">
        <div class="stat-widget">
            <div>
                <div class="stat-label">มูลค่าคลังยาคงคลัง (FEFO)</div>
                <div class="stat-value">฿<?= number_format((float)($stockStats['total_stock_value'] ?? 0), 2) ?></div>
            </div>
            <div class="stat-icon primary">📦</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">ยาใกล้หมดอายุ (≤30 วัน)</div>
                <div class="stat-value" style="color: var(--danger);"><?= (int)($stockStats['exp_30_count'] ?? 0) ?> <span style="font-size: 14px; color: var(--text-muted);">รายการ</span></div>
            </div>
            <div class="stat-icon danger">⏳</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">การทบทวนยา & DRP</div>
                <div class="stat-value"><?= (int)$reviewCount ?> <span style="font-size: 14px; color: var(--text-muted);">ครั้ง</span></div>
            </div>
            <div class="stat-icon success">📋</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">ความพร้อมมาตรฐาน 2568–70</div>
                <div class="stat-value"><?= (int)($qualStats['ready_count'] ?? 0) ?>/<?= (int)($qualStats['total_criteria'] ?? 10) ?></div>
            </div>
            <div class="stat-icon warning">🏅</div>
        </div>
    </div>

    <!-- Quick Clinical Simulation Scenarios (Training Mode) -->
    <div class="card" style="border: 2px solid #0d9488; background: #f0fdfa;">
        <div class="card-header" style="background: transparent; border-bottom: 1px solid rgba(13,148,136,0.15);">
            <div class="card-title" style="color: var(--primary-dark);">
                <span>⚡ เคสผู้ป่วยจำลองทางคลินิก (13 Clinical Training Cohort Scenarios)</span>
            </div>
            <span class="badge badge-success">ระบบตัดสินใจความปลอดภัยทางยา (Decision Support Engine 14 มิติ)</span>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: #334155; margin-bottom: 14px;">
                คลิกที่ชื่อผู้ป่วยเพื่อทดสอบระบบคัดกรองความปลอดภัยทางยา, Drug Interaction, Cross-Allergy, DRP, และการทำ Medication Review แบบเรียลไทม์:
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                <?php foreach ($scenarios as $s): ?>
                    <a href="/hos/patients/<?= (int)$s['pid'] ?>" class="btn btn-secondary" style="justify-content: flex-start; text-align: left; padding: 10px 14px; background: #ffffff; border-radius: var(--radius-sm); border: 1px solid #ccfbf1;">
                        <span style="font-size: 18px;">👤</span>
                        <div style="overflow: hidden;">
                            <div style="font-weight: 600; color: #0f172a; font-size: 13px;"><?= htmlspecialchars($s['full_name']) ?> (<?= (int)$s['age'] ?> ปี)</div>
                            <div style="font-size: 11px; color: #0f766e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ⚠️ <?= htmlspecialchars($s['scenario_note'] ?? 'เคสคลินิก') ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid-cols-2">
        <!-- Cold Chain Today Monitoring -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>❄️ ตรวจสอบอุณหภูมิตู้เย็นยาประจำวัน (2.0 – 8.0 °C)</span>
                </div>
                <a href="/hos/cold-chain" class="btn btn-sm btn-secondary">บันทึกอุณหภูมิ ➕</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัสตู้ / ตำแหน่ง</th>
                                <th>เกณฑ์มาตรฐาน</th>
                                <th>รอบเช้า (08:30)</th>
                                <th>รอบบ่าย (14:30)</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coldChainUnits)): ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">ไม่พบข้อมูลตู้เย็น</td></tr>
                            <?php else: ?>
                                <?php foreach ($coldChainUnits as $u): ?>
                                    <?php 
                                        $m = $u['morning_temp'];
                                        $a = $u['afternoon_temp'];
                                        $mOk = ($m !== null && $m >= 2.0 && $m <= 8.0);
                                        $aOk = ($a !== null && $a >= 2.0 && $a <= 8.0);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($u['unit_name']) ?></strong>
                                            <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($u['unit_code']) ?></div>
                                        </td>
                                        <td><?= (float)$u['min_temp'] ?> – <?= (float)$u['max_temp'] ?> °C</td>
                                        <td>
                                            <?php if ($m !== null): ?>
                                                <span class="badge <?= $mOk ? 'badge-success' : 'badge-danger' ?>"><?= (float)$m ?> °C</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">ยังไม่บันทึก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($a !== null): ?>
                                                <span class="badge <?= $aOk ? 'badge-success' : 'badge-danger' ?>"><?= (float)$a ?> °C</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">ยังไม่บันทึก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mOk && ($aOk || $a === null)): ?>
                                                <span class="badge badge-success">ปกติ</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">ผิดปกติ/หลุดเกณฑ์</span>
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

        <!-- Recent Audit Trails (PDPA Compliance) -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>🛡️ ประวัติการเข้าถึงข้อมูลตามมาตรฐาน PDPA (Audit Logs)</span>
                </div>
                <span class="badge badge-info">ความปลอดภัยระดับสากล</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>วัน-เวลา</th>
                                <th>กิจกรรม (Action)</th>
                                <th>โมดูล</th>
                                <th>PID ผู้ป่วย</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">ไม่มีรายการบันทึก</td></tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($log['created_at']) ?></td>
                                        <td><strong><?= htmlspecialchars($log['action_type']) ?></strong></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($log['module_name']) ?></span></td>
                                        <td><?= $log['patient_pid'] ? 'PID ' . (int)$log['patient_pid'] : '-' ?></td>
                                        <td style="font-size: 12px; font-family: monospace;"><?= htmlspecialchars($log['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
