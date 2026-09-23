<div class="review-show-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;" class="no-print">
        <a href="/hos/reviews" class="btn btn-secondary">
            ⬅ ย้อนกลับไปหน้ารายการ
        </a>
        <div style="display: flex; gap: 10px;">
            <a href="/hos/patients/<?= (int)$review['patient_pid'] ?>" class="btn btn-secondary">
                👤 เปิดแฟ้มยาผู้ป่วย
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ พิมพ์เอกสารบันทึกการทบทวนยา
            </button>
        </div>
    </div>

    <!-- Review Document Card -->
    <div class="card" style="padding: 10px;">
        <div class="card-body">
            <!-- Header for Print -->
            <div style="text-align: center; border-bottom: 2px solid var(--border-color); padding-bottom: 16px; margin-bottom: 20px;">
                <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">แบบบันทึกการทบทวนการใช้ยาและการจัดการปัญหาจากการใช้ยา (Medication Review Report)</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                    หน่วยบริการ: โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านหนองบัว • เครือข่ายบริการปฐมภูมิ (CUP รพ.ระยอง)
                </p>
            </div>

            <!-- Review Summary Grid -->
            <div class="grid-cols-2" style="background: #f8fafc; border-radius: var(--radius-sm); padding: 16px; margin-bottom: 20px;">
                <div>
                    <div><strong>เลขที่เอกสาร:</strong> #REV-<?= str_pad((string)$review['review_id'], 5, '0', STR_PAD_LEFT) ?></div>
                    <div style="margin-top: 4px;"><strong>ผู้รับบริการ:</strong> <?= htmlspecialchars($patient['full_name'] ?? 'ผู้รับบริการ') ?> (PID: <?= (int)$review['patient_pid'] ?>)</div>
                    <div style="margin-top: 4px;"><strong>เลขประจำตัว ปชช.:</strong> <?= htmlspecialchars($patient['masked_cid'] ?? '-') ?></div>
                </div>
                <div>
                    <div><strong>วันที่ดำเนินการ:</strong> <?= htmlspecialchars($review['review_date']) ?></div>
                    <div style="margin-top: 4px;"><strong>ประเภทการทบทวน:</strong> <span class="badge badge-info"><?= htmlspecialchars($review['review_type']) ?></span></div>
                    <div style="margin-top: 4px;"><strong>เภสัชกรผู้ทบทวน:</strong> <?= htmlspecialchars(($review['firstname'] ?? '') . ' ' . ($review['lastname'] ?? '')) ?> (เลขที่ใบประกอบวิชาชีพ: <?= htmlspecialchars($review['license_number'] ?? '-') ?>)</div>
                </div>
            </div>

            <!-- Clinical Assessment & DRP Table -->
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: var(--primary-dark);">
                    ⚠️ ปัญหาจากการใช้ยาและการจัดการ (Identified Drug-Related Problems & Interventions)
                </h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ลำดับ</th>
                                <th>ชื่อยาที่เกี่ยวข้อง</th>
                                <th>หมวดหมู่ปัญหา (PCNE)</th>
                                <th>รายละเอียดปัญหาที่พบ</th>
                                <th>ข้อเสนอแนะของเภสัชกร</th>
                                <th>การตอบรับของแพทย์</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($problems)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        ✅ ไม่พบปัญหาจากการใช้ยา (No Drug-Related Problems Identified)
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($problems as $p): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><strong><?= htmlspecialchars($p['drug_name']) ?></strong></td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($p['drp_category']) ?></span></td>
                                        <td><?= htmlspecialchars($p['problem_description']) ?></td>
                                        <td style="color: #0f766e; font-weight: 500;"><?= htmlspecialchars($p['recommendation']) ?></td>
                                        <td>
                                            <?php 
                                                $resp = $p['prescriber_response'];
                                                $badgeClass = ($resp === 'accepted_fully') ? 'badge-success' : (($resp === 'accepted_partially') ? 'badge-warning' : 'badge-danger');
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($resp) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clinical Summary -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 30px;">
                <h4 style="font-size: 14px; margin-bottom: 6px;">บทสรุปทางคลินิกและแผนการบริบาลต่อเนื่อง:</h4>
                <p style="font-size: 14px; color: #334155; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($review['clinical_summary'] ?: 'ไม่มีบันทึกเพิ่มเติม')) ?>
                </p>
            </div>

            <!-- Signatures -->
            <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                <div style="text-align: center; width: 260px;">
                    <div style="border-bottom: 1px dotted #94a3b8; height: 35px; margin-bottom: 8px;"></div>
                    <div style="font-size: 14px; font-weight: 600;">(<?= htmlspecialchars(($review['firstname'] ?? '') . ' ' . ($review['lastname'] ?? '')) ?>)</div>
                    <div style="font-size: 12px; color: var(--text-muted);">เภสัชกรผู้ประเมินและทบทวนยา</div>
                    <div style="font-size: 11px; color: var(--text-muted);">ใบอนุญาตฯ: <?= htmlspecialchars($review['license_number'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
