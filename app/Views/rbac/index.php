<?php
use App\Core\Auth;
use App\Core\CSRF;

$currentUser = Auth::user();
?>
<div class="rbac-container">
    <!-- Header Notification / Level Explanation Banner -->
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 22px; color: #ffffff; margin-bottom: 6px;">
                        👥 โครงสร้างระดับสิทธิ์และการเข้าใช้งานระบบ (Role-Based Access Control)
                    </h2>
                    <p style="font-size: 14px; opacity: 0.85; margin: 0; max-width: 780px;">
                        แบ่งการควบคุมการเข้าถึงข้อมูลตาม <strong>5 ระดับความปลอดภัย (Security Levels)</strong> และ <strong>12 บทบาทวิชาชีพ</strong> สอดคล้องตามมาตรฐาน PDPA, พระราชบัญญัติระบบสุขภาพปฐมภูมิ พ.ศ. 2562 และหลักธรรมาภิบาลทางข้อมูล
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="/pcc/audit-logs" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); color: #ffffff;">
                        📜 ดูประวัติการแก้ไขระบบ (Audit Logs)
                    </a>
                    <a href="/pcc/rbac/matrix" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #ffffff;">
                        📑 ดูเมทริกซ์สิทธิ์ละเอียด (Matrix)
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddUserModal()">
                        ➕ เพิ่มผู้ใช้งานใหม่
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Access Levels Architecture Cards -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 24px;">
        <!-- Level 1 -->
        <div class="stat-widget" style="border-top: 4px solid #ef4444; display: block; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="badge badge-danger" style="font-size: 11px;">LEVEL 1</span>
                <span style="font-size: 18px;">🛡️</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">บริหารระบบ & เครือข่าย</div>
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                • SUPER_ADMIN<br>• DISTRICT_PHARM
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                ผู้ใช้ปัจจุบัน: <strong><?= $levelStats[1] ?? 0 ?></strong> คน
            </div>
        </div>

        <!-- Level 2 -->
        <div class="stat-widget" style="border-top: 4px solid #0d9488; display: block; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="badge badge-success" style="font-size: 11px;">LEVEL 2</span>
                <span style="font-size: 18px;">💊</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">บริบาลเภสัชกรรม</div>
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                • PCU_PHARM<br>• HOSPITAL_PHARM
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                ผู้ใช้ปัจจุบัน: <strong><?= $levelStats[2] ?? 0 ?></strong> คน
            </div>
        </div>

        <!-- Level 3 -->
        <div class="stat-widget" style="border-top: 4px solid #3b82f6; display: block; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="badge badge-primary" style="font-size: 11px;">LEVEL 3</span>
                <span style="font-size: 18px;">🩺</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">ปฏิบัติการยา & พยาบาล</div>
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                • PHARM_TECH<br>• NURSE
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                ผู้ใช้ปัจจุบัน: <strong><?= $levelStats[3] ?? 0 ?></strong> คน
            </div>
        </div>

        <!-- Level 4 -->
        <div class="stat-widget" style="border-top: 4px solid #f59e0b; display: block; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="badge badge-warning" style="font-size: 11px;">LEVEL 4</span>
                <span style="font-size: 18px;">🏡</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">ชุมชน (ทีม 3 หมอ)</div>
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                • PUBLIC_HEALTH<br>• FACILITY_ADMIN
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                ผู้ใช้ปัจจุบัน: <strong><?= $levelStats[4] ?? 0 ?></strong> คน
            </div>
        </div>

        <!-- Level 5 -->
        <div class="stat-widget" style="border-top: 4px solid #8b5cf6; display: block; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="badge" style="background: #f3e8ff; color: #7c3aed; font-size: 11px;">LEVEL 5</span>
                <span style="font-size: 18px;">🔍</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">คุณภาพ & ตรวจสอบ</div>
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                • QUALITY / DATA<br>• VIEWER / AUDITOR
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                ผู้ใช้ปัจจุบัน: <strong><?= $levelStats[5] ?? 0 ?></strong> คน
            </div>
        </div>
    </div>

    <!-- Quick Persona Switcher Toolbar (UAT & Testing Feature) -->
    <div class="card" style="margin-bottom: 24px; background: #f8fafc; border: 1px dashed #cbd5e1;">
        <div class="card-body" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🎭</span>
                <div>
                    <strong style="font-size: 14px;">สลับบทบาทจำลอง (Quick Persona Switcher):</strong>
                    <div style="font-size: 12px; color: var(--text-secondary);">ทดสอบมุมมองและขอบเขตการใช้งานตามบทบาทจริงโดยไม่ต้องออกจากระบบ</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="/pcc/rbac/switch?username=admin" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                    🛡️ ผู้ดูแลระบบ (admin)
                </a>
                <a href="/pcc/rbac/switch?username=pcu.pharm" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                    💊 ภญ.กานดา (เภสัชกร รพ.สต.)
                </a>
                <a href="/pcc/rbac/switch?username=nurse.somjai" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                    🩺 พว.สมใจ (พยาบาล)
                </a>
                <a href="/pcc/rbac/switch?username=tech.wirat" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                    📦 นายวิรัช (จพ.เภสัชกรรม)
                </a>
                <a href="/pcc/rbac/switch?username=district.pharm" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                    🏥 ภก.ปรีชา (แม่ข่ายอำเภอ)
                </a>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>👤 ทะเบียนบัญชีผู้ใช้งานและการกำหนดระดับสิทธิ์ (Users & Roles Assignment)</span>
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                จำนวนผู้ใช้ทั้งหมด: <strong><?= count($users) ?></strong> บัญชี
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>ชื่อผู้ใช้งาน (Username)</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>วิชาชีพ</th>
                            <th>สังกัดหน่วยบริการ</th>
                            <th>ระดับการเข้าถึง (Security Level)</th>
                            <th>บทบาทที่ได้รับ (Roles)</th>
                            <th style="width: 110px; text-align: center;">สถานะ</th>
                            <th style="width: 140px; text-align: center;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php
                            $lvl = (int)($u['highest_level'] ?? 3);
                            $levelBadges = [
                                1 => '<span class="badge badge-danger" style="font-size: 11px;">Level 1: บริหารระบบ</span>',
                                2 => '<span class="badge badge-success" style="font-size: 11px;">Level 2: บริบาลเภสัชกรรม</span>',
                                3 => '<span class="badge badge-primary" style="font-size: 11px;">Level 3: ปฏิบัติการยา</span>',
                                4 => '<span class="badge badge-warning" style="font-size: 11px;">Level 4: ทีมชุมชน</span>',
                                5 => '<span class="badge" style="background:#f3e8ff; color:#7c3aed; font-size: 11px;">Level 5: คุณภาพ</span>'
                            ];
                            ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-muted);"><?= $u['user_id'] ?></td>
                                <td>
                                    <strong style="font-family: monospace; color: #0f766e;"><?= htmlspecialchars($u['username']) ?></strong>
                                    <?php if ($u['user_id'] == ($currentUser['user_id'] ?? 0)): ?>
                                        <span class="badge badge-secondary" style="font-size: 10px;">คุณ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(($u['title'] ?? '') . $u['firstname'] . ' ' . $u['lastname']) ?></strong>
                                    <?php if (!empty($u['license_number'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($u['license_number']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 13px; color: #475569;">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $u['profession']))) ?>
                                    </span>
                                </td>
                                <td style="font-size: 13px;">
                                    <?= htmlspecialchars($u['facility_name']) ?>
                                    <div style="font-size: 11px; color: var(--text-muted);">รหัส: <?= htmlspecialchars($u['facility_code']) ?></div>
                                </td>
                                <td>
                                    <?= $levelBadges[$lvl] ?? '<span class="badge badge-secondary">Level ' . $lvl . '</span>' ?>
                                </td>
                                <td>
                                    <?php
                                    $roleArr = explode(', ', $u['display_roles'] ?? '');
                                    foreach ($roleArr as $rname):
                                        if (empty($rname)) continue;
                                    ?>
                                        <span class="badge badge-secondary" style="font-size: 11.5px; margin-right: 3px; margin-bottom: 2px;">
                                            <?= htmlspecialchars($rname) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($u['is_active']): ?>
                                        <span class="badge badge-success" style="font-size: 11px;">ใช้งานปกติ</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size: 11px;">ระงับการใช้</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick='openEditUserModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                style="padding: 3px 8px; font-size: 12px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;" title="แก้ไขข้อมูลพื้นฐานผู้ใช้งาน">
                                            ✏️ แก้ไข
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick="openEditRoleModal(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?>', '<?= htmlspecialchars($u['role_ids'] ?? '') ?>')"
                                                style="padding: 3px 8px; font-size: 12px;" title="ปรับเปลี่ยนบทบาทและสิทธิ์">
                                            🔑 สิทธิ์
                                        </button>
                                        <?php if ($u['user_id'] > 1): ?>
                                            <form method="POST" action="/pcc/rbac/toggle-status" onsubmit="return confirm('ยืนยันการเปลี่ยนสถานะผู้ใช้งานนี้?');" style="display: inline;">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                <input type="hidden" name="status" value="<?= $u['is_active'] ? 0 : 1 ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 12px;" title="<?= $u['is_active'] ? 'ระงับสิทธิ์' : 'เปิดใช้งาน' ?>">
                                                    <?= $u['is_active'] ? '🔒' : '🔓' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Assign Roles -->
<div id="assignRoleModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 580px; box-shadow: var(--shadow-lg);">
        <form method="POST" action="/pcc/rbac/assign-role">
            <?= CSRF::field() ?>
            <input type="hidden" name="user_id" id="assignUserId" value="">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 17px; margin: 0;">✏️ กำหนดบทบาทและระดับสิทธิ์ผู้ใช้</h3>
                <button type="button" onclick="closeEditRoleModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
            </div>
            <div style="padding: 24px;">
                <div style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">ชื่อผู้ใช้งาน:</label>
                    <div id="assignUserName" style="font-size: 15px; font-weight: 600; color: #0f766e;">-</div>
                </div>

                <label class="form-label" style="font-weight: 600; margin-bottom: 8px; display: block;">เลือกบทบาทที่ต้องการมอบหมาย:</label>
                <div style="max-height: 280px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px;">
                    <?php foreach ($roles as $r): ?>
                        <label style="display: flex; align-items: flex-start; gap: 10px; padding: 8px; border-radius: var(--radius-sm); cursor: pointer; border-bottom: 1px solid #f1f5f9;">
                            <input type="checkbox" name="role_ids[]" value="<?= $r['role_id'] ?>" id="roleCheckbox_<?= $r['role_id'] ?>" style="margin-top: 3px;">
                            <div>
                                <div style="font-weight: 600; font-size: 13.5px;">
                                    <?= htmlspecialchars($r['display_name']) ?> (<?= htmlspecialchars($r['role_name']) ?>)
                                    <span class="badge badge-secondary" style="font-size: 10px; font-weight: normal;">Level <?= $r['access_level'] ?></span>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($r['description'] ?? '') ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditRoleModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกการกำหนดบทบาท</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add New User -->
<div id="addUserModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
        <form method="POST" action="/pcc/rbac/create-user">
            <?= CSRF::field() ?>
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 17px; margin: 0;">➕ เพิ่มผู้ใช้งานและกำหนดสิทธิ์เริ่มต้น</h3>
                <button type="button" onclick="closeAddUserModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อผู้ใช้งาน (Username) *</label>
                        <input type="text" name="username" class="form-control" required placeholder="เช่น pharm.somchai">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">รหัสผ่านเริ่มต้น *</label>
                        <input type="text" name="password" class="form-control" value="Password@123" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 80px 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">คำนำหน้า</label>
                        <input type="text" name="title" class="form-control" placeholder="ภก./ภญ./พว.">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อจริง *</label>
                        <input type="text" name="firstname" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">นามสกุล *</label>
                        <input type="text" name="lastname" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สายวิชาชีพ</label>
                        <select name="profession" class="form-control">
                            <option value="pharmacist">เภสัชกร (Pharmacist)</option>
                            <option value="pharmacy_technician">เจ้าพนักงานเภสัชกรรม</option>
                            <option value="nurse">พยาบาลวิชาชีพ</option>
                            <option value="public_health">นักวิชาการสาธารณสุข</option>
                            <option value="physician">แพทย์</option>
                            <option value="officer">เจ้าหน้าที่อื่นๆ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สังกัดหน่วยบริการ</label>
                        <select name="facility_id" class="form-control">
                            <?php foreach ($facilities as $f): ?>
                                <option value="<?= $f['facility_id'] ?>"><?= htmlspecialchars($f['facility_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">บทบาทหลัก (Role & Level)</label>
                    <select name="role_id" class="form-control">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['role_id'] ?>" <?= $r['role_name'] === 'PCU_PHARM' ? 'selected' : '' ?>>
                                [Level <?= $r['access_level'] ?>] <?= htmlspecialchars($r['display_name']) ?> — <?= htmlspecialchars($r['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="text" name="phone" class="form-control" placeholder="08x-xxxxxxx">
                </div>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAddUserModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">➕ สร้างบัญชีผู้ใช้งาน</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User Master & Roles -->
<div id="editUserModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/rbac/update-user">
            <?= CSRF::field() ?>
            <input type="hidden" name="user_id" id="editUserId">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ แก้ไขข้อมูลพื้นฐานผู้ใช้งาน (Edit User Profile & Access)
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditUserModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อผู้ใช้งาน (Username)</label>
                        <input type="text" id="editUsername" class="form-control" readonly style="background: #f1f5f9; color: #475569; font-family: monospace; font-weight: 600;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สถานะการใช้งาน</label>
                        <select name="is_active" id="editIsActive" class="form-control">
                            <option value="1">ใช้งานปกติ (Active)</option>
                            <option value="0">ระงับการใช้งาน (Suspended)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 90px 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">คำนำหน้า</label>
                        <input type="text" name="title" id="editTitle" class="form-control" placeholder="ภก./ภญ./พว.">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อจริง *</label>
                        <input type="text" name="firstname" id="editFirstname" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">นามสกุล *</label>
                        <input type="text" name="lastname" id="editLastname" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สายวิชาชีพ</label>
                        <select name="profession" id="editProfession" class="form-control">
                            <option value="pharmacist">เภสัชกร (Pharmacist)</option>
                            <option value="pharmacy_technician">เจ้าพนักงานเภสัชกรรม</option>
                            <option value="nurse">พยาบาลวิชาชีพ</option>
                            <option value="public_health">นักวิชาการสาธารณสุข</option>
                            <option value="physician">แพทย์</option>
                            <option value="officer">เจ้าหน้าที่อื่นๆ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">เลขที่ใบประกอบวิชาชีพ</label>
                        <input type="text" name="license_number" id="editLicenseNumber" class="form-control" placeholder="เช่น ภ.11223">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สังกัดหน่วยบริการ</label>
                        <select name="facility_id" id="editFacilityId" class="form-control">
                            <?php foreach ($facilities as $f): ?>
                                <option value="<?= $f['facility_id'] ?>"><?= htmlspecialchars($f['facility_name']) ?> (<?= htmlspecialchars($f['facility_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">เบอร์โทรศัพท์ติดต่อ</label>
                        <input type="text" name="phone" id="editPhone" class="form-control" placeholder="08x-xxxxxxx">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">ตั้งรหัสผ่านใหม่ (Reset Password)</label>
                    <input type="password" name="password" class="form-control" placeholder="เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน">
                    <span style="font-size: 11.5px; color: var(--text-muted);">กรอกเฉพาะกรณีที่ต้องการรีเซ็ตรหัสผ่านใหม่ให้กับผู้ใช้งานนี้</span>
                </div>

                <!-- Roles Checkboxes -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 14px;">
                    <label class="form-label" style="font-weight: 700; margin-bottom: 8px; display: block; color: #1e293b;">
                        บทบาทและสิทธิ์ที่ได้รับ (Assigned Roles & Access Levels)
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <?php foreach ($roles as $r): ?>
                            <label style="display: flex; align-items: flex-start; gap: 8px; padding: 6px 8px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-size: 12.5px;">
                                <input type="checkbox" name="role_ids[]" value="<?= $r['role_id'] ?>" id="editRoleCb_<?= $r['role_id'] ?>" style="margin-top: 3px;">
                                <div>
                                    <strong style="color: #0f172a;"><?= htmlspecialchars($r['display_name']) ?></strong>
                                    <span class="badge badge-secondary" style="font-size: 10px; margin-left: 4px;">Level <?= $r['access_level'] ?></span>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($r['description']) ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditUserModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกการแก้ไขข้อมูล & Audit Log</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(user) {
    document.getElementById('editUserId').value = user.user_id || '';
    document.getElementById('editUsername').value = user.username || '';
    document.getElementById('editTitle').value = user.title || '';
    document.getElementById('editFirstname').value = user.firstname || '';
    document.getElementById('editLastname').value = user.lastname || '';
    document.getElementById('editProfession').value = user.profession || 'pharmacist';
    document.getElementById('editLicenseNumber').value = user.license_number || '';
    document.getElementById('editFacilityId').value = user.facility_id || '1';
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editIsActive').value = user.is_active !== undefined ? user.is_active : '1';

    // Reset role checkboxes
    const checkboxes = document.querySelectorAll('#editUserModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);

    // Check roles
    if (user.role_ids) {
        const ids = String(user.role_ids).split(',');
        ids.forEach(id => {
            const cb = document.getElementById('editRoleCb_' + id.trim());
            if (cb) cb.checked = true;
        });
    }

    document.getElementById('editUserModal').style.display = 'flex';
}

function closeEditUserModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

function openEditRoleModal(userId, userName, currentRoleIdsStr) {
    document.getElementById('assignUserId').value = userId;
    document.getElementById('assignUserName').textContent = userName;

    // Clear all checkboxes
    const checkboxes = document.querySelectorAll('#assignRoleModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);

    // Check currently assigned roles
    if (currentRoleIdsStr) {
        const ids = currentRoleIdsStr.split(',');
        ids.forEach(id => {
            const cb = document.getElementById('roleCheckbox_' + id.trim());
            if (cb) cb.checked = true;
        });
    }

    document.getElementById('assignRoleModal').style.display = 'flex';
}

function closeEditRoleModal() {
    document.getElementById('assignRoleModal').style.display = 'none';
}

function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}
</script>

