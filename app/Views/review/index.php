<div class="review-index-container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📋 รายการทบทวนวรรณกรรมยาและการจัดการปัญหาจากการใช้ยา (Medication Reviews & DRP)</span>
            </div>
            <a href="/pcc/patients" class="btn btn-primary">
                ➕ เริ่มการทบทวนยาใหม่ (เลือกผู้ป่วย)
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสทบทวน</th>
                            <th>วันที่ทบทวน</th>
                            <th>รหัส PID</th>
                            <th>ประเภทการทบทวน</th>
                            <th>เภสัชกรผู้ทบทวน</th>
                            <th>จำนวนยา</th>
                            <th>ปัญหา DRP ที่พบ</th>
                            <th>สถานะ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 36px;">
                                    ยังไม่มีรายการทบทวนยาในระบบ คลิกปุ่ม "เริ่มการทบทวนยาใหม่" เพื่อเลือกผู้รับบริการ
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td><strong>#<?= (int)$r['review_id'] ?></strong></td>
                                    <td><?= htmlspecialchars($r['review_date']) ?></td>
                                    <td>
                                        <a href="/pcc/patients/<?= (int)$r['patient_pid'] ?>" style="font-weight: 600;">
                                            PID <?= (int)$r['patient_pid'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($r['review_type']) ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? '')) ?>
                                    </td>
                                    <td><?= (int)$r['total_medications'] ?> รายการ</td>
                                    <td>
                                        <?php $pCount = (int)$r['problem_count']; ?>
                                        <span class="badge <?= $pCount > 0 ? 'badge-danger' : 'badge-success' ?>">
                                            <?= $pCount ?> ปัญหา (PCNE)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?= htmlspecialchars($r['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="/pcc/reviews/<?= (int)$r['review_id'] ?>" class="btn btn-sm btn-secondary">
                                            📄 ดูรายละเอียด
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
