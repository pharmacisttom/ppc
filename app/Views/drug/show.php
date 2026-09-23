<div class="drug-show-container">
    <div style="margin-bottom: 20px;">
        <a href="/pcc/drugs" class="btn btn-secondary btn-sm">‹ กลับไปหน้ารายการยา</a>
    </div>

    <!-- Main Card Header -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 32px;">💊</span>
                        <div>
                            <h2 style="font-size: 22px; color: var(--text-primary); margin-bottom: 4px;">
                                <?= htmlspecialchars($drug['drugname']) ?>
                            </h2>
                            <div style="font-size: 15px; color: var(--text-secondary); font-style: italic;">
                                <?= htmlspecialchars($drug['druggenericname'] ?: $drug['drugnamethai'] ?: '-') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-family: monospace; font-size: 14px; color: var(--text-muted);">
                        รหัสยา JHCIS: <strong><?= htmlspecialchars($drug['drugcode']) ?></strong>
                    </div>
                    <?php if (!empty($drug['tmtcode'])): ?>
                        <div style="font-family: monospace; font-size: 13px; color: #0284c7; margin-top: 4px;">
                            รหัส TMT: <strong><?= htmlspecialchars($drug['tmtcode']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Safety Alert Ribbon -->
            <?php if ($drug['is_ham']): ?>
                <div style="margin-top: 20px; padding: 14px 18px; background: #fef2f2; border: 1px solid #f87171; border-radius: var(--radius-md); color: #991b1b;">
                    <strong>⚠️ ยาความเสี่ยงสูง (High Alert Medication):</strong>
                    <?= htmlspecialchars($drug['ham_info']['precautions'] ?? 'ระมัดระวังเป็นพิเศษในการสั่งใช้และบริหารยา บังคับทำ Independent Double Check') ?>
                </div>
            <?php endif; ?>

            <?php if ($drug['is_lasa']): ?>
                <div style="margin-top: 14px; padding: 14px 18px; background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); color: #92400e;">
                    <strong>🏷️ ยาชื่อพ้องมองคล้าย (LASA):</strong>
                    ระวังการหยิบจ่ายสลับกับ <?= htmlspecialchars($drug['lasa_info']['pair_name'] ?? 'ยาคู่เทียบ') ?>
                    <?php if (!empty($drug['lasa_info']['tall_man'])): ?>
                        (ใช้ Tall Man: <strong><?= htmlspecialchars($drug['lasa_info']['tall_man']) ?></strong>)
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2 Column Details -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Left: Pharmacology & Dosing -->
        <div>
            <!-- Basic Data -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-title">📋 ข้อมูลทั่วไปและราคา</div>
                </div>
                <div class="card-body">
                    <table class="table" style="font-size: 14px; margin-bottom: 0;">
                        <tr>
                            <td style="width: 40%; color: var(--text-muted);">ชื่อภาษาไทย:</td>
                            <td><strong><?= htmlspecialchars($drug['drugnamethai'] ?: '-') ?></strong></td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);">หน่วยนับ / รูปแบบ:</td>
                            <td><?= htmlspecialchars($drug['unitsell']) ?> (บรรจุ: <?= htmlspecialchars($drug['pack']) ?>)</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);">บัญชียาหลักแห่งชาติ:</td>
                            <td>
                                <?= $drug['is_nlem'] ? '<span class="badge badge-success">ในบัญชียาหลัก (EDL)</span>' : '<span class="badge badge-secondary">นอกบัญชียาหลัก</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);">ราคาต้นทุน:</td>
                            <td>฿<?= number_format($drug['cost'], 2) ?></td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted);">ราคาเบิกจ่าย/ขาย:</td>
                            <td><strong style="color: #0f766e;">฿<?= number_format($drug['sell'], 2) ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Standard Dosing -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📝 วิธีใช้ยามาตรฐานใน JHCIS (`sysdrugdose`)</div>
                </div>
                <div class="card-body">
                    <?php if (!empty($drug['doses'])): ?>
                        <ul style="padding-left: 20px; margin: 0; font-size: 14px;">
                            <?php foreach ($drug['doses'] as $dose): ?>
                                <li style="margin-bottom: 8px;">
                                    <strong>แบบที่ <?= $dose['doseno'] ?>:</strong> <?= htmlspecialchars($dose['dosedescription']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div style="color: var(--text-muted); font-size: 13px;">ไม่พบวิธีใช้ยามาตรฐานที่ระบุไว้ในระบบ JHCIS</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Safety, Caution & Lots -->
        <div>
            <!-- Caution & Warnings -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="background: #fff5f5;">
                    <div class="card-title" style="color: #991b1b;">⚠️ ข้อควรระวังและคำเตือน (`drugcaution`)</div>
                </div>
                <div class="card-body">
                    <?php if (!empty($drug['drugcaution'])): ?>
                        <div style="padding: 12px 16px; background: #fef2f2; border-radius: var(--radius-md); color: #991b1b; font-size: 14px; line-height: 1.5;">
                            <?= htmlspecialchars($drug['drugcaution']) ?>
                        </div>
                    <?php else: ?>
                        <div style="color: var(--text-muted); font-size: 13px;">ไม่มีข้อควรระวังพิเศษระบุไว้</div>
                    <?php endif; ?>

                    <?php if (!empty($drug['conflicting_diseases'])): ?>
                        <div style="margin-top: 14px;">
                            <strong style="font-size: 13px; color: #991b1b;">โรคที่มีข้อห้ามใช้ (ICD-10):</strong>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                                <?php foreach ($drug['conflicting_diseases'] as $cd): ?>
                                    <span class="badge badge-danger"><?= htmlspecialchars($cd) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Active Lots & Stock in PCU -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📦 ยอดคงคลังและล็อตยา รพ.สต. (FEFO)</div>
                    <span class="badge badge-success" style="font-size: 13px;">รวม: <?= number_format($drug['stock_balance']) ?> <?= htmlspecialchars($drug['unitsell']) ?></span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (!empty($drug['lots'])): ?>
                        <table class="table" style="font-size: 13px; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Lot No.</th>
                                    <th>คลังจัดเก็บ</th>
                                    <th>วันหมดอายุ</th>
                                    <th>คงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drug['lots'] as $lot): ?>
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($lot['lot_number']) ?></td>
                                        <td><?= htmlspecialchars($lot['location_name']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($lot['expiry_date']) ?>
                                            <div style="font-size: 11px; color: <?= $lot['days_to_expire'] <= 90 ? 'var(--danger)' : 'var(--text-muted)' ?>;">
                                                เหลือ <?= $lot['days_to_expire'] ?> วัน
                                            </div>
                                        </td>
                                        <td><strong><?= number_format($lot['quantity_balance']) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                            ไม่มียอดคงคลังใน รพ.สต. ขณะนี้
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
