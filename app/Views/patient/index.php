<?php
use App\Gateway\JhcisGateway;
$simulated = JhcisGateway::getSimulatedPatients();
?>
<div class="patient-index-container">
    <!-- Search Box Card -->
    <div class="card">
        <div class="card-body" style="padding: 24px;">
            <form action="/hos/patients" method="GET" style="display: flex; gap: 12px; align-items: center;">
                <div style="flex: 1; position: relative;">
                    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" class="form-control" style="font-size: 16px; padding: 12px 16px;" placeholder="พิมพ์ค้นหาด้วย เลขบัตร ปชช. 13 หลัก, ชื่อ-นามสกุล, หรือ รหัส PID (เช่น 101, สมชาย, วารินทร์)..." autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 15px;">
                    🔍 ค้นหาเวชระเบียน
                </button>
                <?php if (!empty($query)): ?>
                    <a href="/hos/patients" class="btn btn-secondary" style="padding: 12px 18px;">ล้างผลค้นหา</a>
                <?php endif; ?>
            </form>

            <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 16px;">
                <span>🔒 <strong>PDPA Protection:</strong> เลขประจำตัวประชาชน 13 หลักจะถูกมาสก์ตามกฎหมายคุ้มครองข้อมูลส่วนบุคคล</span>
                <span>⚡ แหล่งข้อมูล: JHCIS API Gateway (Port 3333 Read-Only)</span>
            </div>
        </div>
    </div>

    <?php if (!empty($patients)): ?>
        <!-- Search Results Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>📋 ผลการค้นหาผู้รับบริการ (พบ <?= count($patients) ?> รายการ)</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส PID</th>
                                <th>เลขบัตรประชาชน (PDPA)</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เพศ/อายุ</th>
                                <th>โรคประจำตัว</th>
                                <th>สิทธิการรักษา</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td><span class="badge badge-secondary">PID <?= (int)$p['pid'] ?></span></td>
                                    <td style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($p['masked_cid'] ?? $p['idcard'] ?? '-') ?></td>
                                    <td><strong><?= htmlspecialchars($p['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['sex'] == 1 ? 'ชาย' : 'หญิง') ?> / <?= (int)($p['age'] ?? 0) ?> ปี</td>
                                    <td>
                                        <?php if (!empty($p['chronic_diseases'])): ?>
                                            <?php foreach ($p['chronic_diseases'] as $cd): ?>
                                                <span class="badge badge-warning" style="margin-right: 4px;"><?= htmlspecialchars($cd) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars($p['right_name'] ?? 'บัตรทอง (UCS)') ?></td>
                                    <td>
                                        <a href="/hos/patients/<?= (int)$p['pid'] ?>" class="btn btn-sm btn-primary">
                                            เปิดแฟ้มยา 💊
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (!empty($query)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 48px;">
                <div style="font-size: 48px; margin-bottom: 12px;">🔍</div>
                <h3>ไม่พบข้อมูลผู้รับบริการที่ตรงกับ "<?= htmlspecialchars($query) ?>"</h3>
                <p style="color: var(--text-muted); margin-top: 6px;">กรุณาตรวจสอบความถูกต้องของชื่อ-นามสกุล หรือเลขประจำตัวประชาชน</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Clinical Training Cohort Grid (Always visible for easy testing) -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title">
                <span>📚 รายชื่อผู้ป่วยฝึกอบรมคลินิก (13 Training Scenarios Cohort)</span>
            </div>
            <span class="badge badge-info">คลิกเลือกเพื่อเปิดแฟ้มยาและตรวจคัดกรองความปลอดภัยทันที</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px;">
                <?php foreach ($simulated as $p): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                <strong style="font-size: 15px; color: var(--text-primary);"><?= htmlspecialchars($p['full_name']) ?></strong>
                                <span class="badge badge-secondary">PID <?= (int)$p['pid'] ?></span>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
                                <?= $p['sex'] == 1 ? 'ชาย' : 'หญิง' ?> / อายุ <?= (int)$p['age'] ?> ปี • <?= htmlspecialchars($p['masked_cid']) ?>
                            </div>
                            <div style="font-size: 12px; padding: 6px 10px; border-radius: 4px; background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; margin-bottom: 12px;">
                                ⚠️ <strong>ประเด็นยา:</strong> <?= htmlspecialchars($p['scenario_note'] ?? '') ?>
                            </div>
                        </div>
                        <a href="/hos/patients/<?= (int)$p['pid'] ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                            เปิดแฟ้มยา & ตรวจสอบความปลอดภัย ➔
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
