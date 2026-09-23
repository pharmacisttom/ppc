<div class="kpi-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">ตัวชี้วัดคุณภาพทางคลินิกและการบริหารระบบยา (Quality KPIs)</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                ระบบติดตามและประเมินผลตัวชี้วัดคุณภาพบริการเภสัชกรรมปฐมภูมิตามเกณฑ์กระทรวงสาธารณสุข ประจำปีงบประมาณ 2568
            </p>
        </div>
        <a href="/pcc/kpi/rdu" class="btn btn-primary">
            🌱 แดชบอร์ดการใช้ยาอย่างสมเหตุผล (RDU) ➔
        </a>
    </div>

    <!-- KPI Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📈 ตารางสรุปผลตัวชี้วัดคุณภาพ (รอบการประเมินปัจจุบัน)</span>
            </div>
            <span class="badge badge-secondary"><?= count($kpis) ?> ตัวชี้วัดหลัก</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัส KPI</th>
                            <th>ชื่อตัวชี้วัดคุณภาพ</th>
                            <th>หมวดหมู่</th>
                            <th>เกณฑ์เป้าหมาย</th>
                            <th>ผลงานจริง (Actual)</th>
                            <th>ตัวตั้ง / ตัวหาร (A/B)</th>
                            <th>การประเมินผล</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kpis)): ?>
                            <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">ไม่มีข้อมูลตัวชี้วัด</td></tr>
                        <?php else: ?>
                            <?php foreach ($kpis as $k): ?>
                                <?php 
                                    $target = (float)$k['target_value'];
                                    $actual = (float)($k['current_result'] ?? 0);
                                    $op = $k['target_operator'] ?? '>=';
                                    
                                    $passed = false;
                                    if ($op === '>=') $passed = ($actual >= $target);
                                    elseif ($op === '<=') $passed = ($actual <= $target);
                                    elseif ($op === '=') $passed = ($actual == $target);
                                    
                                    $badgeClass = $passed ? 'badge-success' : 'badge-danger';
                                    $badgeText = $passed ? 'ผ่านเกณฑ์เป้าหมาย' : 'ยังไม่บรรลุเป้าหมาย';
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($k['kpi_code']) ?></strong></td>
                                    <td>
                                        <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($k['kpi_name']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                            <?= htmlspecialchars($k['description'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($k['category']) ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($op) ?> <?= $target ?> <?= htmlspecialchars($k['unit'] ?? '%') ?></strong>
                                    </td>
                                    <td>
                                        <strong style="font-size: 15px; color: <?= $passed ? 'var(--success)' : 'var(--danger)' ?>;">
                                            <?= number_format($actual, 2) ?> <?= htmlspecialchars($k['unit'] ?? '%') ?>
                                        </strong>
                                    </td>
                                    <td style="font-size: 13px;">
                                        <?php if ($k['numerator_value'] !== null && $k['denominator_value'] !== null): ?>
                                            <?= number_format((float)$k['numerator_value']) ?> / <?= number_format((float)$k['denominator_value']) ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
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
