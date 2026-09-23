<?php
/**
 * Smart System Settings & Base Configuration View
 */
$fac = $settings['facility'] ?? [];
$inv = $settings['inventory'] ?? [];
$safe = $settings['safety'] ?? [];
$auto = $settings['automation'] ?? [];
$conn = $settings['connection'] ?? [];

$val = function($arr, $key, $default = '') {
    return htmlspecialchars((string)($arr[$key]['typed_value'] ?? $default));
};
$isAuto = function($arr, $key) {
    return !empty($arr[$key]['is_smart_detected']);
};
?>

<div class="settings-container" style="max-width: 1400px; margin: 0 auto; padding-bottom: 50px;">

    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">
                    ⚙️ กำหนดข้อมูลพื้นฐานของระบบ แบบอัจฉริยะ
                </h1>
                <span class="badge badge-primary" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; background: #2563eb; color: #fff;">
                    Smart PCU Config v2.5
                </span>
            </div>
            <p style="margin: 6px 0 0 0; color: var(--text-muted); font-size: 14px;">
                บริหารจัดการข้อมูลสถานบริการ เครือข่าย CUP นโยบายคลังยานอก-ใน กฎความปลอดภัยทางคลินิก (CDS) และการเชื่อมต่อ JHCIS
            </p>
        </div>

        <!-- Quick Smart Action Buttons -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form action="/pcc/settings/auto-sync-jhcis" method="POST" style="margin: 0;" onsubmit="return confirm('ต้องการดึงข้อมูลหน่วยบริการอัตโนมัติจากฐานข้อมูล JHCIS ใช่หรือไม่? ข้อมูล รพ.สต. และพื้นที่เขตบริการจะถูกอัปเดตทันที');">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.25); display: flex; align-items: center; gap: 8px;">
                    <span>🤖 ดึงข้อมูลอัตโนมัติจาก JHCIS</span>
                    <span style="background: rgba(255,255,255,0.2); font-size: 11px; padding: 2px 6px; border-radius: 10px;">One-Click Sync</span>
                </button>
            </form>

            <a href="/pcc/settings/export" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px;">
                <span>💾 สำรองการตั้งค่า (JSON)</span>
            </a>
        </div>
    </div>

    <!-- Smart Diagnostics & Live Health Banner -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- PCU Facility Node Card -->
        <div class="card" style="padding: 16px; border-left: 4px solid #10b981; background: var(--bg-card); display: flex; gap: 14px; align-items: center;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #10b981; flex-shrink: 0;">
                🏥
            </div>
            <div style="overflow: hidden;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #10b981; letter-spacing: 0.5px;">หน่วยบริการปฐมภูมิ</div>
                <div style="font-weight: 700; font-size: 15px; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= $val($fac, 'facility_name', 'รพ.สต.บ้านดอกกราย') ?>">
                    <?= $val($fac, 'facility_name', 'รพ.สต.บ้านดอกกราย') ?>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    รหัส <strong><?= $val($fac, 'facility_code', '01996') ?></strong> | เขตสุขภาพที่ <?= $val($fac, 'health_zone', '06') ?>
                </div>
            </div>
        </div>

        <!-- JHCIS Live Connection Card -->
        <div class="card" style="padding: 16px; border-left: 4px solid #3b82f6; background: var(--bg-card); display: flex; gap: 14px; align-items: center;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #3b82f6; flex-shrink: 0;">
                🔌
            </div>
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #3b82f6;">JHCIS DB (Port 3333)</div>
                    <span class="badge badge-success" style="font-size: 10px;">Connected</span>
                </div>
                <div style="font-weight: 700; font-size: 15px; color: var(--text-main);">
                    <?= number_format((int)($diagnostics['jhcis_db']['stats']['drugs'] ?? 0)) ?> <span style="font-size: 12px; font-weight: 400; color: var(--text-muted);">รายการยา JHCIS</span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Latency: <strong><?= $diagnostics['jhcis_db']['latency_ms'] ?? '0' ?> ms</strong> | <?= number_format((int)($diagnostics['jhcis_db']['stats']['prescriptions'] ?? 0)) ?> ใบสั่งยา
                </div>
            </div>
        </div>

        <!-- App DB & Security Card -->
        <div class="card" style="padding: 16px; border-left: 4px solid #8b5cf6; background: var(--bg-card); display: flex; gap: 14px; align-items: center;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #8b5cf6; flex-shrink: 0;">
                🛡️
            </div>
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #8b5cf6;">App Core & CDS DB</div>
                    <span class="badge badge-success" style="font-size: 10px;">Active</span>
                </div>
                <div style="font-weight: 700; font-size: 15px; color: var(--text-main);">
                    Port 3306 <span style="font-size: 12px; font-weight: 400; color: var(--text-muted);">(pcu_pharmacy)</span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Latency: <strong><?= $diagnostics['app_db']['latency_ms'] ?? '0' ?> ms</strong> | <?= $diagnostics['app_db']['tables_count'] ?? 0 ?> ตาราง
                </div>
            </div>
        </div>

        <!-- System Mode & Status Card -->
        <div class="card" style="padding: 16px; border-left: 4px solid #f59e0b; background: var(--bg-card); display: flex; gap: 14px; align-items: center;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #f59e0b; flex-shrink: 0;">
                ⚙️
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #f59e0b;">สถานะระบบ</div>
                <div style="font-weight: 700; font-size: 15px; color: var(--text-main);">
                    PHP <?= PHP_VERSION ?>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Memory: <?= ini_get('memory_limit') ?> | Opcache: <?= function_exists('opcache_get_status') ? 'ON' : 'OFF' ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Tab Navigation Bar -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;">
        <?php
        $tabs = [
            'facility' => ['icon' => '🏥', 'title' => 'ข้อมูลหน่วยบริการ & เครือข่าย CUP'],
            'inventory' => ['icon' => '📦', 'title' => 'คลังยานอก-ใน & เกณฑ์ รบ. 301'],
            'safety' => ['icon' => '🛡️', 'title' => 'เกณฑ์ความปลอดภัยทางคลินิก (CDS)'],
            'automation' => ['icon' => '🤖', 'title' => 'ระบบอัตโนมัติ & AI Settings'],
            'connection' => ['icon' => '🔌', 'title' => 'การเชื่อมต่อ JHCIS & สำรองข้อมูล']
        ];
        ?>
        <?php foreach ($tabs as $key => $t): ?>
            <a href="/pcc/settings?tab=<?= $key ?>" 
               style="padding: 10px 18px; border-radius: 8px 8px 0 0; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease;
                      <?= $activeTab === $key ? 'color: var(--primary); border-bottom: 3px solid var(--primary); background: rgba(37,99,235,0.06);' : 'color: var(--text-muted); background: transparent;' ?>">
                <span><?= $t['icon'] ?></span>
                <span><?= $t['title'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- SETTINGS FORMS -->
    <form action="/pcc/settings/save" method="POST">
        <input type="hidden" name="active_tab" value="<?= htmlspecialchars($activeTab) ?>">

        <!-- TAB 1: FACILITY PROFILE -->
        <?php if ($activeTab === 'facility'): ?>
            <div class="card" style="padding: 24px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">
                            🏥 ข้อมูลหน่วยบริการสาธารณสุข และ เครือข่าย CUP
                        </h3>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                            ข้อมูลที่ใช้ในการออกรายงานราชการ แบบฟอร์ม รบ. 301 ใบสั่งยา และหัวเอกสารของระบบ
                        </p>
                    </div>
                    <?php if ($isAuto($fac, 'facility_code')): ?>
                        <span class="badge badge-info" style="font-size: 11px;">
                            ✓ ดึงอัตโนมัติจาก JHCIS
                        </span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            รหัสสถานพยาบาล (HOSPCODE 5 หลัก) <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="facility_code" class="form-control" value="<?= $val($fac, 'facility_code', '01996') ?>" required style="font-weight: 600; font-family: monospace;">
                        <small style="color: var(--text-muted); font-size: 11px;">รหัส 5 หลักตามมาตรฐานกระทรวงสาธารณสุข (เช่น 01996)</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            รหัสสถานพยาบาลใหม่ (9 หลัก)
                        </label>
                        <input type="text" name="facility_code_9" class="form-control" value="<?= $val($fac, 'facility_code_9', '000199600') ?>" style="font-family: monospace;">
                        <small style="color: var(--text-muted); font-size: 11px;">รหัส 9 หลักสำหรับส่งออกฐานข้อมูล 43 แฟ้ม</small>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ชื่อหน่วยบริการ (รพ.สต. / ศสม.) <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="facility_name" class="form-control" value="<?= $val($fac, 'facility_name', 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย') ?>" required style="font-weight: 600; font-size: 15px;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            รหัสโรงพยาบาลแม่ข่าย (CUP)
                        </label>
                        <input type="text" name="parent_hospital_code" class="form-control" value="<?= $val($fac, 'parent_hospital_code', '10670') ?>">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ชื่อโรงพยาบาลแม่ข่าย (CUP Parent Hospital)
                        </label>
                        <input type="text" name="parent_hospital_name" class="form-control" value="<?= $val($fac, 'parent_hospital_name', 'โรงพยาบาลระยอง (แม่ข่าย CUP)') ?>">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เขตสุขภาพที่ (Health Zone)
                        </label>
                        <input type="text" name="health_zone" class="form-control" value="<?= $val($fac, 'health_zone', '06') ?>">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            จังหวัด / อำเภอ / ตำบล / หมู่ที่
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" name="province_code" class="form-control" placeholder="จังหวัด (21)" value="<?= $val($fac, 'province_code', '21') ?>" title="รหัสจังหวัด">
                            <input type="text" name="district_code" class="form-control" placeholder="อำเภอ (06)" value="<?= $val($fac, 'district_code', '06') ?>" title="รหัสอำเภอ">
                            <input type="text" name="subdistrict_code" class="form-control" placeholder="ตำบล (04)" value="<?= $val($fac, 'subdistrict_code', '04') ?>" title="รหัสตำบล">
                            <input type="text" name="village_no" class="form-control" placeholder="หมู่ (06)" value="<?= $val($fac, 'village_no', '06') ?>" title="หมู่ที่">
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เบอร์โทรศัพท์ติดต่อ รพ.สต.
                        </label>
                        <input type="text" name="facility_phone" class="form-control" value="<?= $val($fac, 'facility_phone', '038-027123') ?>">
                    </div>
                </div>

                <div style="margin-top: 30px; border-top: 1px dashed var(--border-color); padding-top: 20px;">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 16px;">
                        👥 บุคลากรผู้รับผิดชอบ และผู้ลงนามในรายงานทางการ
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                ผู้อำนวยการ รพ.สต. / หัวหน้าหน่วยบริการ
                            </label>
                            <input type="text" name="director_name" class="form-control" value="<?= $val($fac, 'director_name', 'นายสุขสันต์ มุ่งบริการ') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                ตำแหน่งหัวหน้าหน่วยบริการ
                            </label>
                            <input type="text" name="director_position" class="form-control" value="<?= $val($fac, 'director_position', 'ผู้อำนวยการโรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                เภสัชกรปฐมภูมิผู้ควบคุมคลังยาและบริการ
                            </label>
                            <input type="text" name="lead_pharmacist_name" class="form-control" value="<?= $val($fac, 'lead_pharmacist_name', 'ภก. ธนพงศ์ สุขใจ') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                เลขที่ใบประกอบวิชาชีพเภสัชกรรม
                            </label>
                            <input type="text" name="lead_pharmacist_license" class="form-control" value="<?= $val($fac, 'lead_pharmacist_license', 'ภ.25894') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                เจ้าพนักงานเภสัชกรรมประจำหน่วยบริการ
                            </label>
                            <input type="text" name="pharmacy_technician_name" class="form-control" value="<?= $val($fac, 'pharmacy_technician_name', 'น.ส. นภัสสร เภสัชกรน้อย') ?>">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TAB 2: INVENTORY & รบ. 301 -->
        <?php if ($activeTab === 'inventory'): ?>
            <div class="card" style="padding: 24px; margin-bottom: 20px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">
                        📦 นโยบายบริหารคลังยานอก-ใน และเกณฑ์บัญชีคุมเวชภัณฑ์ (แบบ รบ. 301)
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        กำหนดช่วงวันแจ้งเตือนยาใกล้หมดอายุตามหลัก FEFO สูตรคำนวณอัตราสำรองยา และรายชื่อคณะกรรมการตรวจนับ
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เกณฑ์เตือนยาใกล้หมดอายุระดับวิกฤต (วัน)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="fefo_alert_days_1" class="form-control" value="<?= $val($inv, 'fefo_alert_days_1', '30') ?>" min="1" max="180">
                            <span style="font-size: 13px; color: var(--text-muted);">วัน</span>
                        </div>
                        <small style="color: var(--danger); font-size: 11px;">⚠️ แสดงแถบสีแดง ต้องรีบกระจายยาหรือแลกเปลี่ยนกับ รพ.แม่ข่าย</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เกณฑ์เตือนยาใกล้หมดอายุระดับเฝ้าระวัง (วัน)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="fefo_alert_days_2" class="form-control" value="<?= $val($inv, 'fefo_alert_days_2', '90') ?>" min="30" max="365">
                            <span style="font-size: 13px; color: var(--text-muted);">วัน</span>
                        </div>
                        <small style="color: var(--warning); font-size: 11px;">⚡ แสดงแถบสีส้ม เตรียมแผนเบิกจ่ายก่อน</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เกณฑ์เตือนล่วงหน้า (วัน)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="fefo_alert_days_3" class="form-control" value="<?= $val($inv, 'fefo_alert_days_3', '180') ?>" min="90" max="730">
                            <span style="font-size: 13px; color: var(--text-muted);">วัน</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">แจ้งเตือนในระบบตรวจสอบประจำไตรมาส</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ตัวคูณระดับคงคลังปลอดภัย (Safety Stock Multiplier)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" step="0.1" name="safety_stock_multiplier" class="form-control" value="<?= $val($inv, 'safety_stock_multiplier', '1.5') ?>" min="1.0" max="5.0">
                            <span style="font-size: 13px; color: var(--text-muted);">เท่าของ AMC</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">ใช้คำนวณจุดสั่งซื้อ (Reorder Point) ในแบบ รบ. 301 (ปกติ 1.5 เดือน)</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ตัวคูณระดับคงคลังสูงสุด (Max Stock Multiplier)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" step="0.1" name="max_stock_multiplier" class="form-control" value="<?= $val($inv, 'max_stock_multiplier', '3.0') ?>" min="1.5" max="12.0">
                            <span style="font-size: 13px; color: var(--text-muted);">เท่าของ AMC</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">ปริมาณสูงสุดที่ไม่ควรสั่งสต็อกเกิน (ปกติ 3 เดือน)</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            จุดสั่งเติมยาเข้าคลังยานอก (% ของคลังยาใน)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="dispensary_reorder_pct" class="form-control" value="<?= $val($inv, 'dispensary_reorder_pct', '20') ?>" min="5" max="50">
                            <span style="font-size: 13px; color: var(--text-muted);">%</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">เมื่อยอดคลังยานอกลดลงต่ำกว่าเกณฑ์นี้ ให้เตือนเปิดใบเบิกโอน</small>
                    </div>
                </div>

                <div style="margin-top: 30px; border-top: 1px dashed var(--border-color); padding-top: 20px;">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        ✍️ คณะกรรมการตรวจนับพัสดุและเวชภัณฑ์ (สำหรับพิมพ์ลงท้ายแบบ รบ. 301)
                    </h4>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                        รายชื่อผู้ตรวจนับจะปรากฏที่ส่วนล่างของเอกสารแบบ รบ. 301 ในโหมดสั่งพิมพ์ราชการโดยอัตโนมัติ
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                ประธานกรรมการตรวจนับคนที่ 1
                            </label>
                            <input type="text" name="stock_committee_1" class="form-control" value="<?= $val($inv, 'stock_committee_1', 'ภก. ธนพงศ์ สุขใจ') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                กรรมการตรวจนับคนที่ 2
                            </label>
                            <input type="text" name="stock_committee_2" class="form-control" value="<?= $val($inv, 'stock_committee_2', 'น.ส. นภัสสร เภสัชกรน้อย') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                                กรรมการตรวจนับคนที่ 3
                            </label>
                            <input type="text" name="stock_committee_3" class="form-control" value="<?= $val($inv, 'stock_committee_3', 'นางวรรณา รักษ์สุขภาพ') ?>">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TAB 3: CLINICAL SAFETY & CDS RULES -->
        <?php if ($activeTab === 'safety'): ?>
            <div class="card" style="padding: 24px; margin-bottom: 20px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">
                        🛡️ กฎเกณฑ์ความปลอดภัยทางคลินิก (Clinical Decision Support - CDS)
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        ตั้งค่าระดับการแจ้งเตือนอันตรกิริยาระหว่างยา (Drug Interaction) เกณฑ์การทำงานของไต และความปลอดภัยตู้เย็นเก็บยา
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ระดับความรุนแรง Drug Interaction ขั้นต่ำที่แจ้งเตือน
                        </label>
                        <select name="cds_interaction_alert_level" class="form-control">
                            <option value="major" <?= $val($safe, 'cds_interaction_alert_level') === 'major' ? 'selected' : '' ?>>Major Only (ความรุนแรงระดับสูงเท่านั้น)</option>
                            <option value="moderate" <?= $val($safe, 'cds_interaction_alert_level') === 'moderate' || empty($val($safe, 'cds_interaction_alert_level')) ? 'selected' : '' ?>>Moderate & Major (ระดับปานกลางขึ้นไป - แนะนำ)</option>
                            <option value="minor" <?= $val($safe, 'cds_interaction_alert_level') === 'minor' ? 'selected' : '' ?>>All Levels (แจ้งเตือนทุกระดับรวม Minor)</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 11px;">ป้องกันปัญหา Alert Fatigue ในห้องจ่ายยา</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เกณฑ์ eGFR เตือนปรับขนาดยาผู้ป่วยไตเสื่อม (CKD Alert)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="egfr_alert_threshold" class="form-control" value="<?= $val($safe, 'egfr_alert_threshold', '60') ?>" min="15" max="90">
                            <span style="font-size: 13px; color: var(--text-muted);">ml/min/1.73m²</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">ค่า eGFR ต่ำกว่านี้จะเริ่มแนะนำปรับ dose ยาขับทางไต</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เกณฑ์ eGFR วิกฤต ห้ามใช้ยาเสี่ยง (Critical CKD)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="egfr_critical_threshold" class="form-control" value="<?= $val($safe, 'egfr_critical_threshold', '30') ?>" min="10" max="60">
                            <span style="font-size: 13px; color: var(--text-muted);">ml/min/1.73m²</span>
                        </div>
                        <small style="color: var(--danger); font-size: 11px;">ห้ามใช้ยา เช่น Metformin, NSAIDs ใน eGFR &lt; 30</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            อุณหภูมิปลอดภัยตู้เย็นเก็บยา (°C)
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" step="0.1" name="coldchain_temp_min" class="form-control" value="<?= $val($safe, 'coldchain_temp_min', '2.0') ?>" style="width: 100px;">
                            <span>ถึง</span>
                            <input type="number" step="0.1" name="coldchain_temp_max" class="form-control" value="<?= $val($safe, 'coldchain_temp_max', '8.0') ?>" style="width: 100px;">
                            <span style="font-size: 13px; color: var(--text-muted);">°C</span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">มาตรฐานระบบลูกโซ่ความเย็น (Cold Chain) 2.0 - 8.0 °C</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            การตรวจภาวะพร่องเอนไซม์ G6PD
                        </label>
                        <select name="check_g6pd_enabled" class="form-control">
                            <option value="1" <?= $val($safe, 'check_g6pd_enabled') == '1' ? 'selected' : '' ?>>เปิดใช้งาน (เตือนยาห้ามใช้ใน G6PD อัตโนมัติ)</option>
                            <option value="0" <?= $val($safe, 'check_g6pd_enabled') == '0' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            บังคับตรวจสอบ 2 คนสำหรับยาเสี่ยงสูง (High Alert Drugs)
                        </label>
                        <select name="had_double_check" class="form-control">
                            <option value="1" <?= $val($safe, 'had_double_check') == '1' ? 'selected' : '' ?>>เปิดใช้งาน (Double Check Required)</option>
                            <option value="0" <?= $val($safe, 'had_double_check') == '0' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TAB 4: AUTOMATION & AI -->
        <?php if ($activeTab === 'automation'): ?>
            <div class="card" style="padding: 24px; margin-bottom: 20px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">
                        🤖 ระบบอัตโนมัติ และ โหมดการทำงานอัจฉริยะ (Smart Automation)
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        จัดการระบบตัดล็อตยา FEFO อัตโนมัติ การทำงานของ CDS Engine แบบ Real-time และการสลับโหมดฝึกอบรม
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            โหมดการทำงานของระบบ (Application Mode)
                        </label>
                        <select name="training_mode" class="form-control" style="font-weight: 600; background: #f8fafc;">
                            <option value="0" selected>🚀 โหมดใช้งานจริง (Live Production Mode เชื่อมต่อสด 100%)</option>
                        </select>
                        <small style="color: var(--success); font-size: 11px;">ระบบทำงานในโหมดเชื่อมต่อฐานข้อมูลจริง JHCIS & App DB โดยตรง ปราศจากข้อมูลจำลอง</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            ระบบตัดจ่ายยาตามล็อต FEFO อัตโนมัติ (Smart Auto-FEFO)
                        </label>
                        <select name="auto_fefo_deduct" class="form-control">
                            <option value="1" <?= $val($auto, 'auto_fefo_deduct') == '1' ? 'selected' : '' ?>>เปิดใช้งาน (เลือก Lot ที่จะหมดอายุก่อนให้อัตโนมัติ)</option>
                            <option value="0" <?= $val($auto, 'auto_fefo_deduct') == '0' ? 'selected' : '' ?>>ปิดใช้งาน (ให้ผู้ปฏิบัติงานเลือก Lot ด้วยตนเอง)</option>
                        </select>
                        <small style="color: var(--success); font-size: 11px;">ลดความเสี่ยงยาหมดอายุค้างสต็อกตามเกณฑ์มาตรฐาน รพ.สต.</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            การประมวลผลความปลอดภัยทางยา Real-time
                        </label>
                        <select name="smart_cds_realtime" class="form-control">
                            <option value="1" <?= $val($auto, 'smart_cds_realtime') == '1' ? 'selected' : '' ?>>เปิดใช้งาน (ประมวลผลทันทีเมื่อมีการเลือกยา)</option>
                            <option value="0" <?= $val($auto, 'smart_cds_realtime') == '0' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            เก็บบันทึกประวัติการเปลี่ยนแปลงข้อมูลละเอียด (Audit Trail)
                        </label>
                        <select name="audit_trail_logging" class="form-control">
                            <option value="1" <?= $val($auto, 'audit_trail_logging') == '1' ? 'selected' : '' ?>>เปิดใช้งาน (บันทึก User, IP, Timestamp ทุก Action)</option>
                            <option value="0" <?= $val($auto, 'audit_trail_logging') == '0' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 11px;">ตามข้อกำหนดมาตรฐานความมั่นคงปลอดภัยสารสนเทศกระทรวงสาธารณสุข</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TAB 5: CONNECTION & BACKUP -->
        <?php if ($activeTab === 'connection'): ?>
            <div class="card" style="padding: 24px; margin-bottom: 20px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">
                        🔌 การเชื่อมต่อฐานข้อมูล JHCIS และการสำรองข้อมูลการตั้งค่า
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        ตรวจสอบพารามิเตอร์การเชื่อมต่อ JHCIS MySQL และสำรอง/กู้คืนการตั้งค่าระบบ
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            JHCIS Database Host
                        </label>
                        <input type="text" name="jhcis_db_host" class="form-control" value="<?= $val($conn, 'jhcis_db_host', '127.0.0.1') ?>" style="font-family: monospace;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            JHCIS MySQL Port
                        </label>
                        <input type="number" name="jhcis_db_port" class="form-control" value="<?= $val($conn, 'jhcis_db_port', '3333') ?>" style="font-family: monospace;">
                        <small style="color: var(--text-muted); font-size: 11px;">ค่ามาตรฐานของ JHCIS คือพอร์ต 3333</small>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            JHCIS Database Name
                        </label>
                        <input type="text" name="jhcis_db_name" class="form-control" value="<?= $val($conn, 'jhcis_db_name', 'jhcisdb') ?>" style="font-family: monospace;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                            JHCIS Database User
                        </label>
                        <input type="text" name="jhcis_db_user" class="form-control" value="<?= $val($conn, 'jhcis_db_user', 'root') ?>" style="font-family: monospace;">
                    </div>
                </div>

                <!-- Standalone XAMPP Local MySQL Database & Safe Isolation Box -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <h4 style="font-size: 16px; font-weight: 700; color: #166534; margin: 0 0 4px 0;">
                                🗄️ สถาปัตยกรรมฐานข้อมูลระบบอิสระบน XAMPP MySQL (Port 3306)
                            </h4>
                            <p style="font-size: 13px; color: #15803d; margin: 0;">
                                แยกฐานข้อมูลระบบออกจาก JHCIS DB โดยสิ้นเชิง โดย JHCIS DB (Port 3333) จะอยู่ในโหมด <strong>READ-ONLY 100%</strong> ปลอดภัย ไม่มีการเขียนทับหรือรบกวนข้อมูล รพ.สต.
                            </p>
                        </div>
                        <span class="badge badge-success" style="font-size: 11.5px; padding: 4px 10px;">
                            🛡️ 100% Safe Isolated DB
                        </span>
                    </div>

                    <div style="background: white; border: 1px solid #dcfce7; border-radius: 8px; padding: 14px; margin-bottom: 16px; font-size: 13px; line-height: 1.6; color: #1e293b;">
                        <div>• <strong>ฐานข้อมูลแอพพลิเคชัน (App DB):</strong> <code>pcu_pharmacy</code> บน <code>localhost:3306</code> (38 ตาราง: Users, Formulary Config, Cold Chain, Audit Logs ฯลฯ)</div>
                        <div>• <strong>ฐานข้อมูลโรงพยาบาล (JHCIS DB):</strong> <code>jhcisdb</code> บน <code>localhost:3333</code> (เชื่อมต่อแบบอ่านข้อมูลเท่านั้น <u>READ-ONLY</u> เพื่อความปลอดภัยสูงสุด)</div>
                        <div>• <strong>คำสั่งรันสร้างตารางผ่าน Command Line:</strong> <code>php database/install.php</code></div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <a href="/pcc/settings/download-sql" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #0f766e; border-color: #0f766e; text-decoration: none;">
                            📥 ดาวน์โหลดไฟล์ฐานข้อมูล SQL (pcu_pharmacy_standalone.sql)
                        </a>
                        <form method="POST" action="/pcc/settings/install-db" onsubmit="return confirm('ยืนยันการตรวจสอบและซิงค์โครงสร้างตารางฐานข้อมูล pcu_pharmacy บน XAMPP Localhost MySQL (Port 3306)? ข้อมูล JHCIS DB จะไม่ถูกรบกวนใดๆ');" style="display: inline;">
                            <?= \App\Core\CSRF::field() ?>
                            <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;">
                                🔄 ตรวจสอบ & ซิงค์ตารางใน localhost:3306
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Backup & Restore Box -->
                <div style="background: rgba(37,99,235,0.04); border: 1px solid rgba(37,99,235,0.15); border-radius: 12px; padding: 20px;">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 12px;">
                        📥 นำเข้าการตั้งค่าจากไฟล์สำรอง (Import Configuration JSON)
                    </h4>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">
                        ท่านสามารถนำเข้าไฟล์ JSON ที่เคยสำรองไว้ เพื่อกู้คืนการตั้งค่าระบบทั้งหมด
                    </p>
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <input type="file" id="configFileUpload" accept=".json" class="form-control" style="max-width: 320px;">
                        <button type="button" class="btn btn-secondary" onclick="handleImportConfig()">
                            📤 อัปโหลดและนำเข้า
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Submit Button Bar -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; align-items: center; margin-top: 20px;">
            <a href="/pcc/settings?tab=<?= $activeTab ?>" class="btn btn-secondary">
                ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                💾 บันทึกการตั้งค่าระบบ
            </button>
        </div>

    </form>
</div>

<!-- Upload Script -->
<script>
function handleImportConfig() {
    const input = document.getElementById('configFileUpload');
    if (!input.files || input.files.length === 0) {
        alert('กรุณาเลือกไฟล์ JSON สำหรับนำเข้าก่อน');
        return;
    }

    if (!confirm('ยืนยันการนำเข้าการตั้งค่าระบบจากไฟล์ JSON? การตั้งค่าที่มีอยู่เดิมจะถูกแทนที่ด้วยข้อมูลจากไฟล์')) {
        return;
    }

    const formData = new FormData();
    formData.append('config_file', input.files[0]);

    fetch('/pcc/settings/import', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        window.location.reload();
    })
    .catch(err => {
        alert('เกิดข้อผิดพลาดในการนำเข้า: ' + err);
    });
}
</script>
