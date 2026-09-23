<div class="movements-container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📑 บัญชีคุมยาอิเล็กทรอนิกส์และประวัติการเคลื่อนไหว (Electronic Stock Card)</span>
            </div>
            <a href="/pcc/inventory" class="btn btn-secondary btn-sm">
                ⬅ กลับไปหน้าคลังยา FEFO
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>วัน-เวลา</th>
                            <th>ชื่อยา</th>
                            <th>Lot Number</th>
                            <th>ประเภทการเคลื่อนไหว</th>
                            <th>จำนวน</th>
                            <th>ยอดยกมา</th>
                            <th>ยอดคงเหลือใหม่</th>
                            <th>เอกสารอ้างอิง</th>
                            <th>ผู้ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    ยังไม่มีประวัติการเคลื่อนไหวคลังยา
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $m): ?>
                                <?php 
                                    $type = $m['movement_type'];
                                    $isAdd = in_array($type, ['receive', 'return']);
                                    $typeBadge = ($type === 'receive') ? 'badge-success' : (($type === 'dispense') ? 'badge-info' : 'badge-danger');
                                ?>
                                <tr>
                                    <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($m['created_at']) ?></td>
                                    <td><strong><?= htmlspecialchars($m['drug_name']) ?></strong></td>
                                    <td style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($m['lot_number']) ?></td>
                                    <td><span class="badge <?= $typeBadge ?>"><?= htmlspecialchars($type) ?></span></td>
                                    <td>
                                        <strong style="color: <?= $isAdd ? 'var(--success)' : 'var(--danger)' ?>;">
                                            <?= $isAdd ? '+' : '-' ?><?= number_format((float)$m['quantity']) ?>
                                        </strong>
                                    </td>
                                    <td><?= number_format((float)$m['balance_before']) ?></td>
                                    <td><strong><?= number_format((float)$m['balance_after']) ?></strong></td>
                                    <td style="font-size: 12px;"><?= htmlspecialchars($m['reference_doc'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(($m['firstname'] ?? '') . ' ' . ($m['lastname'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
