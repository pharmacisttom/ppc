<div class="inventory-container">
    <!-- Expiry Buckets Widgets -->
    <div class="grid-cols-4">
        <div class="stat-widget">
            <div>
                <div class="stat-label">มูลค่าคลังยารวม</div>
                <div class="stat-value">฿<?= number_format((float)$expiryStats['total_value'], 2) ?></div>
            </div>
            <div class="stat-icon primary">💰</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">หมดอายุแล้ว (Expired)</div>
                <div class="stat-value" style="color: var(--danger);"><?= (int)$expiryStats['expired'] ?> <span style="font-size: 13px;">รายการ</span></div>
            </div>
            <div class="stat-icon danger">🛑</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">เสี่ยงหมดอายุใน ≤ 30 วัน</div>
                <div class="stat-value" style="color: #ea580c;"><?= (int)$expiryStats['exp_30'] ?> <span style="font-size: 13px;">รายการ</span></div>
            </div>
            <div class="stat-icon warning">⚠️</div>
        </div>

        <div class="stat-widget">
            <div>
                <div class="stat-label">หมดอายุใน 31–90 วัน</div>
                <div class="stat-value" style="color: #ca8a04;"><?= (int)$expiryStats['exp_90'] ?> <span style="font-size: 13px;">รายการ</span></div>
            </div>
            <div class="stat-icon warning">⏳</div>
        </div>
    </div>

    <!-- Inventory Lots Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📦 คลังยา รพ.สต. และการจัดลำดับจ่ายยาตามวันหมดอายุ (FEFO Engine: First-Expire, First-Out)</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/pcc/inventory/movements" class="btn btn-secondary btn-sm">
                    📜 ตรวจสอบ Stock Card เคลื่อนไหว
                </a>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ลำดับ FEFO</th>
                            <th>รหัสยา / ชื่อยา</th>
                            <th>หมายเลข Lot</th>
                            <th>ตำแหน่งจัดเก็บ</th>
                            <th>วันหมดอายุ (Expiry)</th>
                            <th>จำนวนคงเหลือ</th>
                            <th>ราคา/หน่วย</th>
                            <th>มูลค่าคงคลัง</th>
                            <th>สถานะความเสี่ยงวันหมดอายุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lots)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    ไม่พบรายการยาในคลัง
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $rank = 1; foreach ($lots as $lot): ?>
                                <?php 
                                    $days = (int)$lot['days_to_expire'];
                                    $val = $lot['quantity_balance'] * $lot['unit_cost'];
                                    if ($days <= 0) {
                                        $badgeClass = 'badge-danger';
                                        $statusText = 'หมดอายุแล้ว (' . abs($days) . ' วันที่แล้ว)';
                                    } elseif ($days <= 30) {
                                        $badgeClass = 'badge-danger';
                                        $statusText = 'วิกฤต (เหลือ ' . $days . ' วัน)';
                                    } elseif ($days <= 90) {
                                        $badgeClass = 'badge-warning';
                                        $statusText = 'เฝ้าระวัง (เหลือ ' . $days . ' วัน)';
                                    } elseif ($days <= 180) {
                                        $badgeClass = 'badge-info';
                                        $statusText = 'ปกติ (เหลือ ' . $days . ' วัน)';
                                    } else {
                                        $badgeClass = 'badge-success';
                                        $statusText = 'ปลอดภัย (> 6 เดือน)';
                                    }
                                ?>
                                <tr>
                                    <td><span class="badge badge-secondary"><?= $rank++ ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($lot['drug_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($lot['drug_code'] ?? '-') ?></div>
                                    </td>
                                    <td style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($lot['lot_number']) ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($lot['location_name']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($lot['expiry_date']) ?></strong></td>
                                    <td><strong><?= number_format((float)$lot['quantity_balance']) ?></strong></td>
                                    <td>฿<?= number_format((float)$lot['unit_cost'], 2) ?></td>
                                    <td>฿<?= number_format((float)$val, 2) ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
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
