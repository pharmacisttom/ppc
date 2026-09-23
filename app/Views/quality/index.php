<?php
use App\Core\CSRF;
?>
<div class="quality-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">
                <?= htmlspecialchars($standard['title'] ?? 'มาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570') ?>
            </h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                ระบบประเมินตนเอง (Self-Assessment) และวิเคราะห์ช่องว่างการพัฒนา (Gap Analysis) ด้านระบบยาและเภสัชกรรมปฐมภูมิ
            </p>
        </div>
        <a href="/hos/quality/evidence" class="btn btn-primary">
            📁 ศูนย์รวมหลักฐานเชิงประจักษ์ (Evidence Center) ➔
        </a>
    </div>

    <!-- Readiness Progress Bar & Widgets -->
    <div class="grid-cols-4">
        <div class="stat-widget">
            <div>
                <div class="stat-label">เกณฑ์ทั้งหมด</div>
                <div class="stat-value"><?= (int)$readiness['total'] ?> <span style="font-size: 13px;">เกณฑ์</span></div>
            </div>
            <div class="stat-icon primary">📋</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">ผ่านเกณฑ์พร้อมตรวจ (Ready)</div>
                <div class="stat-value" style="color: var(--success);"><?= (int)$readiness['ready'] ?> <span style="font-size: 13px;">เกณฑ์</span></div>
            </div>
            <div class="stat-icon success">✅</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">อยู่ระหว่างดำเนินการ</div>
                <div class="stat-value" style="color: #ca8a04;"><?= (int)$readiness['in_progress'] ?> <span style="font-size: 13px;">เกณฑ์</span></div>
            </div>
            <div class="stat-icon warning">⚙️</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">ขาดหลักฐานเชิงประจักษ์</div>
                <div class="stat-value" style="color: var(--danger);"><?= (int)$readiness['evidence_missing'] ?> <span style="font-size: 13px;">เกณฑ์</span></div>
            </div>
            <div class="stat-icon danger">⚠️</div>
        </div>
    </div>

    <!-- Criteria Breakdown by Category -->
    <?php foreach ($categories as $cat): ?>
        <?php 
            $catId = $cat['category_id'];
            $criteriaList = $groupedCriteria[$catId] ?? [];
        ?>
        <div class="card">
            <div class="card-header" style="background: #f8fafc;">
                <div class="card-title">
                    <span class="badge badge-secondary"><?= htmlspecialchars($cat['category_code']) ?></span>
                    <span><?= htmlspecialchars($cat['category_name']) ?></span>
                </div>
                <span style="font-size: 13px; color: var(--text-muted);">
                    (<?= count($criteriaList) ?> ข้อประเมิน)
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">รหัสเกณฑ์</th>
                                <th>ข้อกำหนดและรายละเอียดตามมาตรฐาน</th>
                                <th style="width: 120px;">หลักฐานเชื่อมโยง</th>
                                <th style="width: 160px;">สถานะการประเมิน</th>
                                <th style="width: 180px;">ช่องว่าง / แผนพัฒนา (CAPA)</th>
                                <th style="width: 120px;">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($criteriaList)): ?>
                                <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 16px;">ไม่มีเกณฑ์ในหมวดนี้</td></tr>
                            <?php else: ?>
                                <?php foreach ($criteriaList as $cr): ?>
                                    <?php 
                                        $st = $cr['assessment_status'] ?? 'not_assessed';
                                        if ($st === 'ready') $stBadge = 'badge-success';
                                        elseif ($st === 'in_progress') $stBadge = 'badge-warning';
                                        elseif ($st === 'evidence_missing') $stBadge = 'badge-danger';
                                        else $stBadge = 'badge-secondary';
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cr['criterion_code']) ?></strong></td>
                                        <td>
                                            <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($cr['criterion_name']) ?></div>
                                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 3px;">
                                                <?= htmlspecialchars($cr['description'] ?? '') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="/hos/quality/evidence" class="badge badge-info">
                                                📁 <?= (int)$cr['evidence_count'] ?> ไฟล์หลักฐาน
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stBadge ?>">
                                                <?= htmlspecialchars($st) ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-secondary);">
                                            <?= htmlspecialchars($cr['gap_identified'] ?: '-') ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="openAssessModal(<?= htmlspecialchars(json_encode($cr), ENT_QUOTES, 'UTF-8') ?>)">
                                                ✏️ ประเมิน
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Assessment Update Modal -->
<div class="modal-backdrop" id="assessModal">
    <div class="modal-content">
        <form action="/hos/quality/assess" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="criterion_id" id="modalCriterionId">
            <div class="modal-header">
                <h3 style="font-size: 16px; font-weight: 700;" id="modalCriterionTitle">ประเมินข้อกำหนด</h3>
                <button type="button" onclick="closeModal('assessModal')" style="border:none; background:transparent; font-size:20px; cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">สถานะความพร้อมตามเกณฑ์ (Assessment Status)</label>
                    <select name="status" id="modalStatus" class="form-control" required>
                        <option value="ready">Ready (ผ่านเกณฑ์และมีหลักฐานครบถ้วน)</option>
                        <option value="in_progress">In Progress (อยู่ระหว่างดำเนินการจัดทำ)</option>
                        <option value="evidence_missing">Evidence Missing (มีกระบวนการแต่ขาดหลักฐานเชิงประจักษ์)</option>
                        <option value="need_improvement">Need Improvement (ต้องปรับปรุงเชิงระบบ)</option>
                        <option value="not_assessed">Not Assessed (ยังไม่ประเมิน)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">คะแนนประเมิน (0 - 100%)</label>
                    <input type="number" step="1" name="score_achieved" id="modalScore" class="form-control" value="100" min="0" max="100">
                </div>

                <div class="form-group">
                    <label class="form-label">ช่องว่างการพัฒนาที่พบ (Gap Identified)</label>
                    <textarea name="gap_identified" id="modalGap" class="form-control" rows="2" placeholder="ระบุสิ่งที่ยังขาด เช่น ยังไม่ได้ทบทวน SOP ประจำปี, ขาดภาพถ่ายหลักฐาน..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">แผนพัฒนาและมาตรการแก้ไข (Corrective Action Plan / CAPA)</label>
                    <textarea name="corrective_action_plan" id="modalCapa" class="form-control" rows="2" placeholder="ระบุกิจกรรมแก้ไข ผู้รับผิดชอบ และกำหนดแล้วเสร็จ..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('assessModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">💾 บันทึกผลการประเมิน</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssessModal(cr) {
    document.getElementById('modalCriterionId').value = cr.criterion_id;
    document.getElementById('modalCriterionTitle').innerText = 'ประเมิน: ' + cr.criterion_code + ' - ' + cr.criterion_name;
    document.getElementById('modalStatus').value = cr.assessment_status || 'in_progress';
    document.getElementById('modalScore').value = cr.score_achieved !== null ? cr.score_achieved : 100;
    document.getElementById('modalGap').value = cr.gap_identified || '';
    document.getElementById('modalCapa').value = cr.corrective_action_plan || '';
    openModal('assessModal');
}
</script>
