<?php
/**
 * MOPH Form รบ. 301: บัญชีคุมเวชภัณฑ์ (Stock Card)
 */
use App\Services\SystemSettingService;

$drug = $stockCard['drug'] ?? null;
$movements = $stockCard['movements'] ?? [];

$facilityName = SystemSettingService::get('facility_name', 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย');
$leadPharmacist = SystemSettingService::get('lead_pharmacist_name', 'ภก. ธนพงศ์ สุขใจ');
$comm1 = SystemSettingService::get('stock_committee_1', 'ภก. ธนพงศ์ สุขใจ');
$comm2 = SystemSettingService::get('stock_committee_2', 'น.ส. นภัสสร เภสัชกรน้อย');
$comm3 = SystemSettingService::get('stock_committee_3', 'นางวรรณา รักษ์สุขภาพ');
$director = SystemSettingService::get('director_name', 'นายสุขสันต์ มุ่งบริการ');

$storeLabel = match($storeType) {
    'main' => 'คลังยาใน (Main Store)',
    'dispensary' => 'คลังยานอก (Dispensary)',
    default => 'คลังรวม (Integrated Stores)'
};
?>

<div class="rb301-container" style="max-width: 1400px; margin: 0 auto; padding-bottom: 60px;">

    <!-- Non-print Action & Filter Header -->
    <div class="no-print" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">
                        📋 บัญชีคุมเวชภัณฑ์ (แบบ รบ. 301)
                    </h1>
                    <span class="badge badge-primary" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                        MOPH Stock Card Standard
                    </span>
                </div>
                <p style="margin: 6px 0 0 0; color: var(--text-muted); font-size: 14px;">
                    แบบฟอร์มบัญชีคุมยาและเวชภัณฑ์มาตรฐานกระทรวงสาธารณสุข สำหรับตรวจนับพัสดุ ควบคุม FEFO และรับการตรวจประเมิน
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="/pcc/inventory/jhcis-stores" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px;">
                    <span>⬅ คลังยานอก-ใน JHCIS</span>
                </a>
                <button type="button" onclick="window.print()" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px;">
                    <span>🖨️ พิมพ์แบบ รบ. 301</span>
                </button>
                <a href="/pcc/inventory/rb301/export?drugcode=<?= urlencode($drugCode) ?>&store=<?= urlencode($storeType) ?>&start=<?= urlencode($dateStart) ?>&end=<?= urlencode($dateEnd) ?>" 
                   class="btn btn-primary" style="display: flex; align-items: center; gap: 6px; background: #10b981; border-color: #10b981;">
                    <span>📥 ส่งออก Excel (รบ. 301)</span>
                </a>
            </div>
        </div>

        <!-- Filter Form Bar -->
        <div class="card" style="padding: 18px 24px; background: var(--bg-card);">
            <form action="/pcc/inventory/rb301" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) 100px; gap: 14px; align-items: flex-end;">
                
                <!-- Drug Select -->
                <div style="min-width: 260px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        💊 เลือกรายการยา / เวชภัณฑ์
                    </label>
                    <select name="drugcode" class="form-control" style="font-weight: 600;" onchange="this.form.submit()">
                        <?php foreach ($drugs as $d): ?>
                            <option value="<?= htmlspecialchars($d['drugcode']) ?>" <?= $d['drugcode'] === $drugCode ? 'selected' : '' ?>>
                                [<?= htmlspecialchars($d['drugcode']) ?>] <?= htmlspecialchars($d['drugname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Store Type -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        🏢 เลือกคลังที่ควบคุม
                    </label>
                    <select name="store" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?= $storeType === 'all' ? 'selected' : '' ?>>คลังรวม (Integrated PCU)</option>
                        <option value="main" <?= $storeType === 'main' ? 'selected' : '' ?>>คลังยาใน (Main Store / คลังใหญ่)</option>
                        <option value="dispensary" <?= $storeType === 'dispensary' ? 'selected' : '' ?>>คลังยานอก (Dispensary / ห้องจ่ายยา)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        📅 ตั้งแต่วันที่
                    </label>
                    <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($dateStart) ?>">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        📅 ถึงวันที่
                    </label>
                    <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($dateEnd) ?>">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px;">
                        แสดง
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- OFFICIAL MOPH FORM รบ. 301 SHEET (Styled for Screen & Print) -->
    <div class="card printable-sheet" style="padding: 30px; background: #fff; border: 1px solid #cbd5e1; box-shadow: 0 4px 16px rgba(0,0,0,0.06); color: #000; font-family: 'Sarabun', Tahoma, sans-serif;">
        
        <!-- Official Document Header -->
        <div style="border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">แบบ รบ. 301</div>
                    <div style="font-size: 20px; font-weight: bold; margin-top: 2px;">บัญชีคุมเวชภัณฑ์ (Stock Card)</div>
                    <div style="font-size: 14px; margin-top: 2px;">
                        สถานบริการ: <strong><?= htmlspecialchars($facilityName) ?></strong>
                    </div>
                </div>
                <div style="text-align: right; font-size: 12px;">
                    <div>คลังที่บันทึก: <strong><?= htmlspecialchars($storeLabel) ?></strong></div>
                    <div>ช่วงเวลา: <?= htmlspecialchars($dateStart) ?> ถึง <?= htmlspecialchars($dateEnd) ?></div>
                    <div>วันที่พิมพ์: <?= date('d/m/Y H:i') ?> น.</div>
                </div>
            </div>
        </div>

        <!-- Drug Specification & Control Parameters Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">ชื่อเวชภัณฑ์ / ขนาดความแรง</span>
                <strong style="font-size: 15px; color: #0f172a;"><?= htmlspecialchars($drug['drugname'] ?? '-') ?></strong>
                <?php if (!empty($drug['drugnamethai'])): ?>
                    <span style="color: #475569; font-size: 12px;">(<?= htmlspecialchars($drug['drugnamethai']) ?>)</span>
                <?php endif; ?>
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">รหัสเวชภัณฑ์ / TMT</span>
                <strong><?= htmlspecialchars($drug['drugcode'] ?? '-') ?></strong>
                <?php if (!empty($drug['tmtcode'])): ?>
                    <div style="font-size: 11px; color: #475569;">TMT: <?= htmlspecialchars($drug['tmtcode']) ?></div>
                <?php endif; ?>
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">ขนาดบรรจุ / หน่วยนับ</span>
                <strong><?= htmlspecialchars($drug['pack'] ?: '1 หน่วย') ?> / <?= htmlspecialchars($drug['unitsell'] ?: 'เม็ด') ?></strong>
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">ราคาต่อหน่วย (ต้นทุน)</span>
                <strong style="font-size: 14px; color: #0284c7;">฿<?= number_format((float)($drug['cost'] ?? 0), 2) ?></strong>
            </div>

            <!-- Second Row: Inventory Control Limits -->
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">อัตราการใช้เฉลี่ยต่อเดือน (AMC)</span>
                <strong><?= number_format((float)($stockCard['amc'] ?? 0), 1) ?></strong> <?= htmlspecialchars($drug['unitsell'] ?: 'หน่วย') ?>/เดือน
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">จุดสั่งซื้อต่ำสุด (Min / Reorder Point)</span>
                <strong style="color: #dc2626;"><?= number_format((int)($stockCard['min_stock'] ?? 0)) ?></strong> <?= htmlspecialchars($drug['unitsell'] ?: 'หน่วย') ?> (1.5 เดือน)
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">ระดับคงคลังสูงสุด (Max Level)</span>
                <strong style="color: #16a34a;"><?= number_format((int)($stockCard['max_stock'] ?? 0)) ?></strong> <?= htmlspecialchars($drug['unitsell'] ?: 'หน่วย') ?> (3 เดือน)
            </div>
            <div>
                <span style="color: #64748b; font-size: 11px; display: block;">ยอดคงคลังปัจจุบัน</span>
                <strong style="font-size: 16px; color: #2563eb;"><?= number_format((int)($stockCard['current_stock'] ?? 0)) ?></strong> <?= htmlspecialchars($drug['unitsell'] ?: 'หน่วย') ?>
            </div>
        </div>

        <!-- Ledger Movements Table (ตารางบันทึกการเคลื่อนไหว) -->
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 24px;" border="1" cellpadding="6" cellspacing="0" bordercolor="#cbd5e1">
                <thead>
                    <tr style="background: #f1f5f9; text-align: center; font-weight: bold;">
                        <th style="width: 85px;">วัน เดือน ปี</th>
                        <th style="width: 90px;">เลขที่เอกสาร</th>
                        <th>รายการ / รับจาก - จ่ายให้</th>
                        <th style="width: 85px;">คลัง</th>
                        <th style="width: 70px;">ราคา/หน่วย</th>
                        <th style="width: 75px;">Lot No.</th>
                        <th style="width: 80px;">วันหมดอายุ</th>
                        <th style="width: 70px; background: #ecfdf5; color: #065f46;">รับ (In)</th>
                        <th style="width: 70px; background: #fef2f2; color: #991b1b;">จ่าย (Out)</th>
                        <th style="width: 80px; background: #eff6ff; color: #1e40af;">คงเหลือ</th>
                        <th style="width: 80px;">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance Row -->
                    <tr style="background: #f8fafc; font-weight: bold;">
                        <td style="text-align: center;"><?= htmlspecialchars($dateStart) ?></td>
                        <td style="text-align: center;">-</td>
                        <td colspan="5">ยอดยกมา (Opening Balance ก่อนวันที่ <?= htmlspecialchars($dateStart) ?>)</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right; color: #1e40af; font-size: 13px;">
                            <?= number_format((int)$stockCard['opening_balance']) ?>
                        </td>
                        <td style="text-align: center; font-size: 11px; color: #64748b;">ยอดยกมา</td>
                    </tr>

                    <!-- Transaction Rows -->
                    <?php if (empty($movements)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; color: #64748b; padding: 24px;">
                                ไม่พบรายการรับ-จ่ายยาในช่วงเวลาที่ระบุ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td style="text-align: center;"><?= htmlspecialchars($m['tx_date']) ?></td>
                                <td style="text-align: center; font-family: monospace; font-size: 11px;"><?= htmlspecialchars($m['doc_no']) ?></td>
                                <td><?= htmlspecialchars($m['party']) ?></td>
                                <td style="text-align: center; font-size: 11px; color: #475569;"><?= htmlspecialchars($m['store_name']) ?></td>
                                <td style="text-align: right;"><?= number_format((float)$m['unit_price'], 2) ?></td>
                                <td style="text-align: center; font-family: monospace; font-size: 11px;"><?= htmlspecialchars($m['lotno'] ?: '-') ?></td>
                                <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($m['expiredate'] ?: '-') ?></td>
                                <td style="text-align: right; font-weight: bold; color: #16a34a;">
                                    <?= (int)$m['qty_in'] > 0 ? number_format((int)$m['qty_in']) : '-' ?>
                                </td>
                                <td style="text-align: right; font-weight: bold; color: #dc2626;">
                                    <?= (int)$m['qty_out'] > 0 ? number_format((int)$m['qty_out']) : '-' ?>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #1e40af; font-size: 13px;">
                                    <?= number_format((int)$m['balance']) ?>
                                </td>
                                <td style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($m['remark'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Total Summary Row -->
                    <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #94a3b8;">
                        <td colspan="7" style="text-align: right; padding-right: 12px;">รวมจำนวนการเคลื่อนไหวในช่วงเวลา:</td>
                        <td style="text-align: right; color: #16a34a; font-size: 13px;">
                            +<?= number_format((int)$stockCard['total_in']) ?>
                        </td>
                        <td style="text-align: right; color: #dc2626; font-size: 13px;">
                            -<?= number_format((int)$stockCard['total_out']) ?>
                        </td>
                        <td style="text-align: right; color: #1e40af; font-size: 14px;">
                            <?= number_format((int)$stockCard['closing_balance']) ?>
                        </td>
                        <td style="text-align: center; font-size: 11px;">ยอดคงเหลือยกไป</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Official Signatures Block (สำหรับ Print View / ตรวจสอบพัสดุราชการ) -->
        <div style="margin-top: 30px; page-break-inside: avoid;">
            <div style="font-size: 12px; font-weight: bold; margin-bottom: 16px;">
                การรับรองและตรวจนับยอดเวชภัณฑ์คงเหลือ:
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; font-size: 12px;">
                
                <!-- Sign 1: Committee / Warehouse Officer -->
                <div>
                    <div style="height: 45px;"></div>
                    <div>ลงชื่อ ................................................................</div>
                    <div style="margin-top: 4px;">( <?= htmlspecialchars($comm1) ?> )</div>
                    <div style="color: #64748b; font-size: 11px;">ประธานกรรมการตรวจนับพัสดุ</div>
                    <div style="color: #64748b; font-size: 11px;">วันที่ ......../......../............</div>
                </div>

                <!-- Sign 2: Member Committee -->
                <div>
                    <div style="height: 45px;"></div>
                    <div>ลงชื่อ ................................................................</div>
                    <div style="margin-top: 4px;">( <?= htmlspecialchars($comm2) ?> )</div>
                    <div style="color: #64748b; font-size: 11px;">กรรมการตรวจนับพัสดุ</div>
                    <div style="color: #64748b; font-size: 11px;">วันที่ ......../......../............</div>
                </div>

                <!-- Sign 3: Pharmacist / Director -->
                <div>
                    <div style="height: 45px;"></div>
                    <div>ลงชื่อ ................................................................</div>
                    <div style="margin-top: 4px;">( <?= htmlspecialchars($director) ?> )</div>
                    <div style="color: #64748b; font-size: 11px;">ผู้อำนวยการหน่วยบริการ / หัวหน้า รพ.สต.</div>
                    <div style="color: #64748b; font-size: 11px;">วันที่ ......../......../............</div>
                </div>

            </div>
        </div>

    </div>

</div>

<!-- Print Stylesheet -->
<style>
@media print {
    .no-print, header, aside, .sidebar, nav, .btn, form, footer {
        display: none !important;
    }
    body, .main-content, .content-area {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .rb301-container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .printable-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-footer-group;
    }
}
</style>
