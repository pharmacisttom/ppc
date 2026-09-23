<?php
use App\Gateway\JhcisGateway;
?>
<div class="patient-index-container">
    <!-- Search Box Card -->
    <div class="card">
        <div class="card-body" style="padding: 24px;">
            <form action="/pcc/patients" method="GET" style="display: flex; gap: 12px; align-items: center;">
                <div style="flex: 1; position: relative;">
                    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" class="form-control" style="font-size: 16px; padding: 12px 16px;" placeholder="พิมพ์ค้นหาด้วย เลขบัตร ปชช. 13 หลัก, ชื่อ-นามสกุล, หรือ รหัส PID (เช่น 101, สมชาย, วารินทร์)..." autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 15px;">
                    🔍 ค้นหาเวชระเบียน
                </button>
                <?php if (!empty($query)): ?>
                    <a href="/pcc/patients" class="btn btn-secondary" style="padding: 12px 18px;">ล้างผลค้นหา</a>
                <?php endif; ?>
            </form>

            <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 16px;">
                <span>🔒 <strong>PDPA Protection:</strong> เลขประจำตัวประชาชน 13 หลักจะถูกมาสก์ตามกฎหมายคุ้มครองข้อมูลส่วนบุคคล</span>
                <span>⚡ แหล่งข้อมูล: JHCIS API Gateway (Port 3333 Read-Only)</span>
            </div>
        </div>
    </div>

    <?php if (!empty($patients)): ?>
        <!-- Patients Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <?php if (!empty($query)): ?>
                        <span>📋 ผลการค้นหาผู้รับบริการสำหรับ "<?= htmlspecialchars($query) ?>" (พบ <?= count($patients) ?> รายการ)</span>
                    <?php else: ?>
                        <span>📋 รายชื่อผู้รับบริการจากฐานข้อมูล JHCIS (Real-time Port 3333: <?= count($patients) ?> รายการล่าสุด)</span>
                    <?php endif; ?>
                </div>
                <span class="badge badge-success">⚡ JHCIS LIVE PORT 3333</span>
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
                                    <td style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($p['masked_cid'] ?? $p['cid_masked'] ?? '-') ?></td>
                                    <td><strong><?= htmlspecialchars($p['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['gender'] ?? (($p['sex'] == 1) ? 'ชาย' : 'หญิง')) ?> / <?= (int)($p['age'] ?? 0) ?> ปี</td>
                                    <td>
                                        <?php 
                                            $rawChronic = $p['chronic_diseases'] ?? [];
                                            $cds = is_array($rawChronic) ? $rawChronic : array_filter(array_map('trim', explode(',', (string)$rawChronic)));
                                        ?>
                                        <?php if (!empty($cds)): ?>
                                            <?php foreach ($cds as $cd): ?>
                                                <span class="badge badge-warning" style="margin-right: 4px; font-size: 11px;">
                                                    <?= htmlspecialchars(is_array($cd) ? ($cd['group_name'] ?? $cd['chronic_code'] ?? '') : $cd) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars($p['right_name'] ?? 'บัตรทอง (UCS)') ?></td>
                                    <td>
                                        <a href="/pcc/patients/<?= (int)$p['pid'] ?>" class="btn btn-sm btn-primary">
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
                <p style="color: var(--text-muted); margin-top: 6px;">กรุณาตรวจสอบความถูกต้องของชื่อ-นามสกุล หรือเลขประจำตัวประชาชน (ค้นหาจากฐาน JHCIS จริง)</p>
            </div>
        </div>
    <?php endif; ?>
</div>
