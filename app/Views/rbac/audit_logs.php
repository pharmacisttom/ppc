<?php
// Audit Logs Viewer View
?>
<div class="container-fluid" style="padding: 24px; max-width: 1400px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 24px;">📜</span>
                <h1 style="font-size: 22px; font-weight: 700; margin: 0; color: #0f172a;">
                    บันทึกประวัติการแก้ไขและตรวจสอบระบบ (System Audit Trail Logs)
                </h1>
            </div>
            <p style="margin: 0; color: var(--text-muted); font-size: 13.5px;">
                บันทึกค่า Log อัตโนมัติทุกครั้งเมื่อมีการแก้ไขข้อมูลพื้นฐานในทุกตาราง (Users, Drug Formulary, Cold Chain, Clinical Registries, Settings) ตามมาตรฐาน PDPA และ Healthcare Security
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/pcc/rbac" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                <span>👥</span> จัดการบัญชีผู้ใช้ (RBAC)
            </a>
            <a href="/pcc/settings" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                <span>⚙️</span> การตั้งค่าระบบ
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 20px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="/pcc/audit-logs" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 220px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control" placeholder="ค้นหาเหตุผล, รหัสข้อมูล หรือชื่อผู้ใช้งาน...">
                </div>
                <div style="min-width: 180px;">
                    <select name="module" class="form-control" onchange="this.form.submit()">
                        <option value="">-- ทุกโมดูล (All Modules) --</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= ($filterModule ?? '') === $m ? 'selected' : '' ?>>
                                <?= htmlspecialchars(strtoupper($m)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 7px 16px;">
                    🔍 ค้นหา Log
                </button>
                <?php if (!empty($search) || !empty($filterModule)): ?>
                    <a href="/pcc/audit-logs" class="btn btn-secondary btn-sm" style="padding: 7px 14px;">
                        ล้างตัวกรอง
                    </a>
                <?php endif; ?>
                <div style="margin-left: auto; font-size: 13px; color: var(--text-muted);">
                    พบประวัติการแก้ไข <strong><?= number_format($totalCount) ?></strong> รายการล่าสุด
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th style="width: 70px;">Log ID</th>
                        <th style="width: 150px;">วัน-เวลา</th>
                        <th style="width: 160px;">ผู้ดำเนินการ</th>
                        <th style="width: 130px;">โมดูล / ตาราง</th>
                        <th style="width: 170px;">ประเภทการกระทำ</th>
                        <th style="width: 110px;">รหัสข้อมูล</th>
                        <th>รายละเอียดเหตุผลการแก้ไข</th>
                        <th style="width: 120px; text-align: center;">ข้อมูลการแก้ไข</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                📂 ไม่พบประวัติการแก้ไขข้อมูลตามเงื่อนไขที่ระบุ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-muted); font-size: 12px;">
                                    #<?= $log['log_id'] ?>
                                </td>
                                <td style="font-size: 12px; white-space: nowrap; color: #475569;">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px; color: #0f172a;">
                                        <?= htmlspecialchars($log['username'] ?? 'System') ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        <?= htmlspecialchars(($log['firstname'] ?? '') . ' ' . ($log['lastname'] ?? '')) ?>
                                        <?= !empty($log['profession']) ? ' (' . htmlspecialchars($log['profession']) . ')' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary" style="font-size: 11px; font-family: monospace;">
                                        <?= htmlspecialchars(strtoupper($log['module_name'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $actionClass = 'badge-primary';
                                    if (str_contains($log['action_type'], 'UPDATE')) $actionClass = 'badge-warning';
                                    if (str_contains($log['action_type'], 'DELETE')) $actionClass = 'badge-danger';
                                    if (str_contains($log['action_type'], 'CREATE')) $actionClass = 'badge-success';
                                    ?>
                                    <span class="badge <?= $actionClass ?>" style="font-size: 11px; font-family: monospace;">
                                        <?= htmlspecialchars($log['action_type']) ?>
                                    </span>
                                </td>
                                <td style="font-family: monospace; font-size: 12px; color: #0f766e;">
                                    <?= htmlspecialchars($log['record_id'] ?? ($log['patient_pid'] ? 'PID:' . $log['patient_pid'] : '-')) ?>
                                </td>
                                <td style="font-size: 13px; color: #1e293b;">
                                    <?= htmlspecialchars($log['reason'] ?? '-') ?>
                                    <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                                        IP: <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($log['payload_before']) || !empty($log['payload_after'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick='viewPayload(<?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                style="padding: 3px 8px; font-size: 11.5px; background: #f1f5f9;">
                                            🔍 ดู Diff
                                        </button>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: var(--text-muted);">-</span>
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

<!-- Payload Diff Modal -->
<div id="payloadModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 800px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: var(--shadow-xl); margin: 20px;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;" id="payloadModalTitle">
                รายละเอียดค่า Log การเปลี่ยนแปลง (Audit Payload Diff)
            </h3>
            <button type="button" onclick="closePayloadModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            <div style="margin-bottom: 14px; font-size: 13px;" id="payloadModalMeta"></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #dc2626;">
                        🔴 ข้อมูลก่อนแก้ไข (Payload Before)
                    </div>
                    <pre id="payloadBeforeBox" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 11.5px; font-family: monospace; max-height: 340px; overflow: auto; white-space: pre-wrap;"></pre>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #16a34a;">
                        🟢 ข้อมูลหลังแก้ไข (Payload After)
                    </div>
                    <pre id="payloadAfterBox" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 8px; font-size: 11.5px; font-family: monospace; max-height: 340px; overflow: auto; white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
        <div style="padding: 14px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); text-align: right;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closePayloadModal()">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<script>
function viewPayload(log) {
    document.getElementById('payloadModalTitle').textContent = `Log #${log.log_id}: ${log.action_type} (${log.module_name})`;
    document.getElementById('payloadModalMeta').innerHTML = `
        <strong>ผู้แก้ไข:</strong> ${log.username || 'System'} | 
        <strong>เวลา:</strong> ${log.created_at} | 
        <strong>รหัสข้อมูล:</strong> ${log.record_id || log.patient_pid || '-'} <br>
        <strong>เหตุผล/สรุป:</strong> ${log.reason || '-'}
    `;

    try {
        const b = log.payload_before ? JSON.parse(log.payload_before) : null;
        document.getElementById('payloadBeforeBox').textContent = b ? JSON.stringify(b, null, 2) : '(ไม่มีข้อมูลก่อนหน้า หรือเป็นการสร้างใหม่)';
    } catch(e) {
        document.getElementById('payloadBeforeBox').textContent = log.payload_before || '-';
    }

    try {
        const a = log.payload_after ? JSON.parse(log.payload_after) : null;
        document.getElementById('payloadAfterBox').textContent = a ? JSON.stringify(a, null, 2) : '(ไม่มีข้อมูลหลังแก้ไข)';
    } catch(e) {
        document.getElementById('payloadAfterBox').textContent = log.payload_after || '-';
    }

    document.getElementById('payloadModal').style.display = 'flex';
}

function closePayloadModal() {
    document.getElementById('payloadModal').style.display = 'none';
}
</script>
