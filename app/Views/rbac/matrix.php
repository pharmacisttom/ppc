<div class="rbac-matrix-container">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="/pcc/rbac" class="btn btn-secondary btn-sm">‹ กลับไปหน้าจัดการผู้ใช้งาน</a>
        <div style="font-size: 13px; color: var(--text-muted);">
            อ้างอิงมาตรฐาน: RBAC Matrix Specification (AGY-PCU-RBAC-007)
        </div>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <span>📑 เมทริกซ์สิทธิ์การเข้าถึงระบบยา (System Permission Matrix)</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                12 บทบาทวิชาชีพ × 25 สิทธิ์การดำเนินงานในระบบ
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="font-size: 12px; margin-bottom: 0;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="min-width: 120px;">โมดูลระบบ</th>
                            <th style="min-width: 140px;">รหัสสิทธิ์ (Permission)</th>
                            <th style="min-width: 180px;">คำอธิบายการกระทำ</th>
                            <?php foreach ($roles as $r): ?>
                                <th style="text-align: center; min-width: 75px;">
                                    <div style="font-weight: 700; color: #0f766e;"><?= htmlspecialchars($r['role_name']) ?></div>
                                    <div style="font-size: 10px; color: var(--text-muted);">L<?= $r['access_level'] ?></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentModule = '';
                        foreach ($permissions as $p):
                            $mod = htmlspecialchars($p['module_name']);
                            $isNewMod = ($mod !== $currentModule);
                            if ($isNewMod) $currentModule = $mod;
                        ?>
                            <tr style="<?= $isNewMod ? 'border-top: 2px solid #cbd5e1;' : '' ?>">
                                <td>
                                    <?php if ($isNewMod): ?>
                                        <strong style="color: #0f766e; text-transform: uppercase; font-size: 11px;">
                                            <?= $mod ?>
                                        </strong>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: monospace; font-weight: 600; color: #334155;">
                                    <?= htmlspecialchars($p['permission_code']) ?>
                                </td>
                                <td style="color: var(--text-secondary);">
                                    <?= htmlspecialchars($p['description']) ?>
                                </td>
                                <?php foreach ($roles as $r): ?>
                                    <?php
                                    $hasPerm = !empty($rolePermMap[$r['role_id']][$p['permission_id']]) || $r['role_name'] === 'SUPER_ADMIN';
                                    ?>
                                    <td style="text-align: center; background-color: <?= $hasPerm ? 'rgba(13, 148, 136, 0.04)' : 'transparent' ?>;">
                                        <?php if ($hasPerm): ?>
                                            <span style="color: #0d9488; font-weight: bold; font-size: 14px;" title="อนุญาตให้เข้าถึง">✓</span>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
