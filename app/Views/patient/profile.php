<div class="patient-profile-container">
    <!-- Patient Info Header -->
    <div class="card" style="border-left: 5px solid var(--primary); background: #ffffff;">
        <div class="card-body" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; gap: 18px; align-items: center;">
                    <div style="width: 58px; height: 58px; border-radius: 50%; background: var(--primary-light); color: var(--primary-dark); font-size: 26px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        <?= htmlspecialchars(mb_substr($patient['fname'] ?? 'ผ', 0, 1, 'UTF-8')) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">
                                <?= htmlspecialchars($patient['full_name']) ?>
                            </h2>
                            <span class="badge badge-secondary">PID <?= (int)$patient['pid'] ?></span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px; display: flex; gap: 16px; flex-wrap: wrap;">
                            <span><strong>เลข ปชช. (PDPA):</strong> <span style="font-family: monospace;"><?= htmlspecialchars($patient['masked_cid'] ?? $patient['cid_masked'] ?? '-') ?></span></span>
                            <span><strong>เพศ/อายุ:</strong> <?= htmlspecialchars($patient['gender'] ?? ((($patient['sex'] ?? 1) == 1) ? 'ชาย' : 'หญิง')) ?> / <?= (int)($patient['age'] ?? 0) ?> ปี (เกิด: <?= htmlspecialchars($patient['birth_date'] ?? $patient['birth'] ?? '-') ?>)</span>
                            <span><strong>กรุ๊ปเลือด:</strong> <?= htmlspecialchars($patient['blood_group'] ?? $patient['bloodgroup'] ?: '-') ?></span>
                            <span><strong>สิทธิ:</strong> <?= htmlspecialchars($patient['right_name'] ?? $patient['right_code'] ?? 'บัตรทอง') ?></span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="/pcc/reviews/create?pid=<?= (int)$patient['pid'] ?>" class="btn btn-primary">
                        <span>📋</span> จัดทำ Medication Review & DRP
                    </a>
                    <a href="/pcc/patients" class="btn btn-secondary">
                        ย้อนกลับ ➔
                    </a>
                </div>
            </div>

            <!-- Chronic Conditions & Scenario Badge -->
            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <strong style="font-size: 13px; color: var(--text-secondary);">โรคประจำตัว:</strong>
                <?php if (!empty($chronicConditions)): ?>
                    <?php foreach ($chronicConditions as $c): ?>
                        <span class="badge badge-info" style="font-size: 13px; padding: 4px 10px;">
                            🏥 <?= htmlspecialchars($c['group_name'] ?? $c['disease_name'] ?? $c['chronic_code'] ?? $c['code'] ?? '') ?>
                        </span>
                    <?php endforeach; ?>
                <?php elseif (!empty($patient['chronic_diseases'])): ?>
                    <?php 
                        $cds = is_array($patient['chronic_diseases']) ? $patient['chronic_diseases'] : array_filter(array_map('trim', explode(',', (string)$patient['chronic_diseases'])));
                        foreach ($cds as $cd):
                    ?>
                        <span class="badge badge-info" style="font-size: 13px; padding: 4px 10px;">
                            🏥 <?= htmlspecialchars(is_array($cd) ? ($cd['group_name'] ?? $cd['chronic_code'] ?? '') : $cd) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span style="font-size: 13px; color: var(--text-muted);">ไม่มีข้อมูลโรคเรื้อรัง</span>
                <?php endif; ?>

                <?php if (!empty($patient['scenario_note'])): ?>
                    <span class="badge badge-danger" style="margin-left: auto; font-size: 12px; padding: 4px 10px;">
                        ⚠️ คลินิกเคส: <?= htmlspecialchars($patient['scenario_note']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clinical Medication Safety Decision Support Rule Engine Alerts -->
    <?php if (!empty($safetyFlags)): ?>
        <div class="card" style="border: 2px solid #ef4444; background: #fff5f5;">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid #fed7d7;">
                <div class="card-title" style="color: #991b1b;">
                    <span>🛡️ การแจ้งเตือนความปลอดภัยทางยา (Decision Support Safety Engine: พบ <?= count($safetyFlags) ?> ประเด็น)</span>
                </div>
                <span class="badge badge-danger">14 Clinical Risk Dimensions</span>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($safetyFlags as $flag): ?>
                        <?php 
                            $lvl = strtolower($flag['level'] ?? 'info');
                            $bgColor = ($lvl === 'critical') ? '#fee2e2' : (($lvl === 'high') ? '#ffedd5' : '#fef9c3');
                            $borderColor = ($lvl === 'critical') ? '#ef4444' : (($lvl === 'high') ? '#f97316' : '#eab308');
                            $textColor = ($lvl === 'critical') ? '#7f1d1d' : (($lvl === 'high') ? '#7c2d12' : '#713f12');
                        ?>
                        <div style="background: <?= $bgColor ?>; border-left: 5px solid <?= $borderColor ?>; border-radius: var(--radius-sm); padding: 12px 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <strong style="font-size: 14px; color: <?= $textColor ?>;">
                                    ⚠️ <?= htmlspecialchars($flag['title']) ?>
                                </strong>
                                <span class="badge" style="background: <?= $borderColor ?>; color: #ffffff; text-transform: uppercase;">
                                    <?= htmlspecialchars($flag['level']) ?>
                                </span>
                            </div>
                            <p style="font-size: 13px; color: #334155; margin-bottom: 6px;">
                                <?= htmlspecialchars($flag['description'] ?? $flag['message'] ?? '') ?>
                            </p>
                            <?php if (!empty($flag['recommendation'])): ?>
                                <div style="font-size: 12px; font-weight: 600; color: #0f766e; background: rgba(255,255,255,0.7); padding: 6px 10px; border-radius: 4px;">
                                    💡 <strong>ข้อเสนอแนะทางคลินิก:</strong> <?= htmlspecialchars($flag['recommendation']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid-cols-2">
        <!-- Drug Allergies Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>🚫 ประวัติการแพ้ยา (Allergy Screening)</span>
                </div>
                <span class="badge <?= empty($allergies) ? 'badge-success' : 'badge-danger' ?>">
                    <?= empty($allergies) ? 'ไม่พบประวัติแพ้ยา' : 'พบประวัติแพ้ยา ' . count($allergies) . ' รายการ' ?>
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อยาที่แพ้</th>
                                <th>อาการ / อาการแสดง</th>
                                <th>ระดับความรุนแรง</th>
                                <th>วันที่บันทึก</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allergies)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        ✅ ไม่พบประวัติการแพ้ยาในฐานข้อมูล JHCIS
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allergies as $alg): ?>
                                    <tr>
                                        <td><strong style="color: var(--danger);"><?= htmlspecialchars($alg['drug_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($alg['reaction'] ?? $alg['symptom'] ?? 'ไม่ระบุ') ?></td>
                                        <td><span class="badge badge-danger"><?= htmlspecialchars($alg['severity'] ?? 'Moderate') ?></span></td>
                                        <td style="font-size: 12px;"><?= htmlspecialchars($alg['date_recorded'] ?? $alg['report_date'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Current Active Medications Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>💊 รายการยาปัจจุบัน (Active Prescriptions)</span>
                </div>
                <span class="badge badge-secondary"><?= count($currentMedications) ?> รายการ</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อยา</th>
                                <th>วิธีใช้ (Instructions)</th>
                                <th>จำนวน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($currentMedications)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        ไม่มีรายการยาที่ใช้อยู่ในปัจจุบัน
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($currentMedications as $med): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($med['drug_name']) ?></strong>
                                            <?php if (!empty($med['is_ham'])): ?>
                                                <span class="badge badge-danger" style="margin-left: 4px;">HAM</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 13px; color: var(--text-secondary);"><?= htmlspecialchars($med['dose'] ?? $med['instruction'] ?? $med['usage_text'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($med['quantity'] ?? $med['qty'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Longitudinal Medication Timeline (Visits from JHCIS) -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>⏱️ ไทม์ไลน์การรับยาและการมารับบริการย้อนหลัง (Longitudinal Medication Timeline)</span>
            </div>
            <span class="badge badge-info">ดึงจากฐานข้อมูล JHCIS Gateway</span>
        </div>
        <div class="card-body">
            <?php if (empty($timeline)): ?>
                <p style="text-align: center; color: var(--text-muted); padding: 24px;">ไม่พบประวัติการมารับบริการย้อนหลัง</p>
            <?php else: ?>
                <div style="border-left: 2px solid var(--primary-light); margin-left: 12px; padding-left: 20px; display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($timeline as $visit): ?>
                        <div style="position: relative;">
                            <div style="position: absolute; left: -26px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background: var(--primary);"></div>
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                                <strong style="font-size: 15px; color: var(--text-primary);">
                                    วันที่รับบริการ: <?= htmlspecialchars($visit['visit_date']) ?>
                                </strong>
                                <span style="font-size: 12px; color: var(--text-muted);">
                                    แพทย์/ผู้สั่งยา: <?= htmlspecialchars($visit['prescriber'] ?? 'รพ.สต.') ?>
                                </span>
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">
                                <strong>การวินิจฉัย (ICD-10):</strong> <?= htmlspecialchars($visit['diagnosis'] ?? 'ไม่ระบุ') ?>
                            </div>
                            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 10px 14px;">
                                <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">รายการยาที่ได้รับในครั้งนี้:</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($visit['medications'] as $vm): ?>
                                        <span class="badge badge-secondary" style="font-size: 12px; padding: 4px 8px;">
                                            💊 <?= htmlspecialchars($vm['drug_name']) ?> (<?= htmlspecialchars($vm['quantity'] ?? $vm['qty'] ?? '') ?>)
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Past Medication Reviews for this Patient -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📑 ประวัติการทำ Medication Review & DRP ของผู้ป่วยรายนี้</span>
            </div>
            <a href="/pcc/reviews/create?pid=<?= (int)$patient['pid'] ?>" class="btn btn-sm btn-primary">
                ทำทบทวนยาครั้งใหม่ ➕
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>วันที่ทบทวน</th>
                            <th>ประเภท</th>
                            <th>ผู้ทบทวน</th>
                            <th>ปัญหา DRP ที่พบ</th>
                            <th>สถานะ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                    ยังไม่เคยมีการบันทึก Medication Review สำหรับผู้ป่วยรายนี้
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td>#<?= (int)$rev['review_id'] ?></td>
                                    <td><?= htmlspecialchars($rev['review_date']) ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($rev['review_type']) ?></span></td>
                                    <td><?= htmlspecialchars($rev['firstname'] . ' ' . $rev['lastname']) ?></td>
                                    <td>
                                        <span class="badge <?= $rev['problem_count'] > 0 ? 'badge-danger' : 'badge-success' ?>">
                                            <?= (int)$rev['problem_count'] ?> ปัญหา
                                        </span>
                                    </td>
                                    <td><span class="badge badge-success"><?= htmlspecialchars($rev['status']) ?></span></td>
                                    <td>
                                        <a href="/pcc/reviews/<?= (int)$rev['review_id'] ?>" class="btn btn-sm btn-secondary">
                                            เปิดดู ➔
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
