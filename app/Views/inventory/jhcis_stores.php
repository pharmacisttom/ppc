<?php
/**
 * JHCIS Dual-Store Inventory Dashboard View
 * คลังยานอก vs คลังยาใน JHCIS
 */
$main = $summary['main_store'] ?? ['items' => 0, 'qty' => 0, 'value' => 0];
$disp = $summary['dispensary'] ?? ['items' => 0, 'qty' => 0, 'value' => 0];
?>

<div class="dual-store-container" style="max-width: 1440px; margin: 0 auto; padding-bottom: 50px;">

    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">
                    🏢 แดชบอร์ดบริหารจัดการคลังยานอก-คลังยาใน JHCIS
                </h1>
                <span class="badge badge-info" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                    JHCIS Dual-Store Architecture
                </span>
            </div>
            <p style="margin: 6px 0 0 0; color: var(--text-muted); font-size: 14px;">
                ติดตามและเปรียบเทียบยอดคงเหลือยา 2 ระดับ: คลังใหญ่/คลังยาใน (Main Store) และ คลังย่อย/คลังยานอก (Dispensary) พร้อมตรวจสอบการเบิกโอนและวันหมดอายุตามหลัก FEFO
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="/pcc/inventory/rb301" class="btn btn-secondary" style="display: flex; align-items: center; gap: 6px; border-color: #2563eb; color: #2563eb;">
                <span>📋 บัญชีคุมเวชภัณฑ์ (แบบ รบ. 301)</span>
            </a>
            <a href="/pcc/inventory/jhcis-stores/export?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px; background: #10b981; border-color: #10b981;">
                <span>📥 ส่งออกรายงาน Excel</span>
            </a>
        </div>
    </div>

    <!-- Visual Inventory Pipeline Diagram -->
    <div class="card" style="padding: 20px; margin-bottom: 24px; background: linear-gradient(135deg, rgba(37,99,235,0.03), rgba(16,185,129,0.03)); border: 1px solid rgba(37,99,235,0.12);">
        <div style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <span>🔄 ผังจำลองการหมุนเวียนเวชภัณฑ์ใน รพ.สต. (Drug Flow Architecture)</span>
        </div>
        
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; text-align: center;">
            
            <!-- Step 1: CUP -->
            <div style="flex: 1; min-width: 180px; background: var(--bg-card); padding: 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                <div style="font-size: 24px; margin-bottom: 4px;">🏥</div>
                <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">รพ.แม่ข่าย (CUP)</div>
                <div style="font-size: 11px; color: var(--text-muted);">จัดสรรและส่งมอบยา</div>
            </div>

            <div style="color: var(--primary); font-size: 20px; font-weight: 700;">➔</div>

            <!-- Step 2: Main Store -->
            <div style="flex: 1.2; min-width: 220px; background: var(--bg-card); padding: 14px; border-radius: 12px; border: 2px solid #2563eb; box-shadow: 0 4px 12px rgba(37,99,235,0.08);">
                <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span style="font-size: 22px;">🏢</span>
                    <span style="font-weight: 700; font-size: 15px; color: #2563eb;">คลังยาใน (Main Store)</span>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-main);">
                    <?= number_format($main['items']) ?> รายการ | <?= number_format($main['qty']) ?> หน่วย
                </div>
                <div style="font-size: 11px; color: var(--text-muted);">
                    มูลค่า: <strong><?= number_format($main['value'], 2) ?></strong> บาท
                </div>
            </div>

            <div style="color: var(--primary); font-size: 20px; font-weight: 700;">
                <div style="font-size: 11px; color: var(--text-muted); font-weight: 400;">ใบเบิกโอนยา</div>
                ➔
            </div>

            <!-- Step 3: Dispensary -->
            <div style="flex: 1.2; min-width: 220px; background: var(--bg-card); padding: 14px; border-radius: 12px; border: 2px solid #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.08);">
                <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span style="font-size: 22px;">💊</span>
                    <span style="font-weight: 700; font-size: 15px; color: #10b981;">คลังยานอก (Dispensary)</span>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-main);">
                    <?= number_format($disp['items']) ?> รายการ | <?= number_format($disp['qty']) ?> หน่วย
                </div>
                <div style="font-size: 11px; color: var(--text-muted);">
                    มูลค่า: <strong><?= number_format($disp['value'], 2) ?></strong> บาท
                </div>
            </div>

            <div style="color: #10b981; font-size: 20px; font-weight: 700;">➔</div>

            <!-- Step 4: Patient OPD -->
            <div style="flex: 1; min-width: 180px; background: var(--bg-card); padding: 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                <div style="font-size: 24px; margin-bottom: 4px;">👥</div>
                <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">ผู้ป่วยนอก (OPD)</div>
                <div style="font-size: 11px; color: var(--text-muted);">จ่ายยาตามใบสั่งแพทย์</div>
            </div>

        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="card" style="padding: 18px; border-left: 4px solid #2563eb;">
            <div style="font-size: 12px; font-weight: 600; color: #2563eb; text-transform: uppercase;">มูลค่าคลังยาใน (Main Store)</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 6px 0 2px 0;">
                ฿<?= number_format($main['value'], 2) ?>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                <?= number_format($main['items']) ?> รายการ | <?= number_format($main['qty']) ?> เม็ด/หน่วย
            </div>
        </div>

        <div class="card" style="padding: 18px; border-left: 4px solid #10b981;">
            <div style="font-size: 12px; font-weight: 600; color: #10b981; text-transform: uppercase;">มูลค่าคลังยานอก (Dispensary)</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 6px 0 2px 0;">
                ฿<?= number_format($disp['value'], 2) ?>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                <?= number_format($disp['items']) ?> รายการพร้อมจ่ายหน้าห้องตรวจ
            </div>
        </div>

        <div class="card" style="padding: 18px; border-left: 4px solid #8b5cf6;">
            <div style="font-size: 12px; font-weight: 600; color: #8b5cf6; text-transform: uppercase;">มูลค่าคงคลังรวมทั้งสิ้น</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 6px 0 2px 0;">
                ฿<?= number_format($summary['total_value'] ?? 0, 2) ?>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                รวมคลังยาใน + คลังยานอก รพ.สต.
            </div>
        </div>

        <div class="card" style="padding: 18px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 12px; font-weight: 600; color: #f59e0b; text-transform: uppercase;">ยอดเบิกโอนยา (90 วัน)</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 6px 0 2px 0;">
                <?= number_format($summary['transfers_month'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500;">ใบเบิก</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                มูลค่าเบิกโอน: ฿<?= number_format($summary['transfers_value'] ?? 0, 2) ?>
            </div>
        </div>

        <div class="card" style="padding: 18px; border-left: 4px solid #ef4444;">
            <div style="font-size: 12px; font-weight: 600; color: #ef4444; text-transform: uppercase;">ยาใกล้หมดอายุ (&lt; 90 วัน)</div>
            <div style="font-size: 22px; font-weight: 800; color: #ef4444; margin: 6px 0 2px 0;">
                <?= number_format($summary['near_expiry_count'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500;">รายการ</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                ต้องกระจายยาด่วนตามหลัก FEFO
            </div>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form action="/pcc/inventory/jhcis-stores" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            
            <!-- Search input -->
            <div style="flex: 1; min-width: 260px;">
                <input type="text" name="q" class="form-control" placeholder="🔍 ค้นหาด้วยรหัสยา, ชื่อยาภาษาไทย/อังกฤษ, รหัส TMT..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <!-- Filter buttons -->
            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=all" 
                   class="btn btn-sm <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">
                    ทั้งหมด (<?= number_format($total) ?>)
                </a>
                <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=dispensary_empty" 
                   class="btn btn-sm <?= $status === 'dispensary_empty' ? 'btn-warning' : 'btn-secondary' ?>"
                   style="<?= $status === 'dispensary_empty' ? 'background: #f59e0b; border-color: #f59e0b; color: #fff;' : '' ?>">
                    ⚠️ คลังนอกหมดแต่คลังในมี
                </a>
                <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=expiring" 
                   class="btn btn-sm <?= $status === 'expiring' ? 'btn-danger' : 'btn-secondary' ?>"
                   style="<?= $status === 'expiring' ? 'background: #ef4444; border-color: #ef4444; color: #fff;' : '' ?>">
                    ⏳ ใกล้หมดอายุ (FEFO)
                </a>
                <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=stockout" 
                   class="btn btn-sm <?= $status === 'stockout' ? 'btn-danger' : 'btn-secondary' ?>">
                    ❌ ยาขาดคราว (Stockout)
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                ค้นหา
            </button>
            <?php if (!empty($search) || $status !== 'all'): ?>
                <a href="/pcc/inventory/jhcis-stores" class="btn btn-secondary btn-sm">
                    รีเซ็ต
                </a>
            <?php endif; ?>

        </form>
    </div>

    <!-- Comparative Inventory Table -->
    <div class="card" style="padding: 0; margin-bottom: 24px; overflow: hidden;">
        <div style="padding: 16px 20px; background: rgba(37,99,235,0.03); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">
                    📊 ตารางเปรียบเทียบยอดคงเหลือยา (คลังใน vs คลังนอก)
                </h3>
                <span style="font-size: 12px; color: var(--text-muted);">
                    แสดงรายการที่ <?= ($page - 1) * 25 + 1 ?> ถึง <?= min($page * 25, $total) ?> จากทั้งหมด <?= number_format($total) ?> รายการ
                </span>
            </div>
            
            <div style="display: flex; gap: 12px; font-size: 12px; align-items: center;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 2px; background: #2563eb; display: inline-block;"></span> คลังยาใน (Main)
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 10px; height: 10px; border-radius: 2px; background: #10b981; display: inline-block;"></span> คลังยานอก (Dispensary)
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" style="margin: 0; font-size: 13px;">
                <thead>
                    <tr style="background: var(--bg-hover);">
                        <th style="width: 100px;">รหัสยา</th>
                        <th>ชื่อยาและขนาด (Drug Description)</th>
                        <th style="text-align: right; width: 80px;">ต้นทุน</th>
                        <th style="width: 140px; text-align: center;">สัดส่วนคงคลัง</th>
                        <th style="text-align: right; width: 110px; color: #2563eb;">🏢 คลังใน</th>
                        <th style="text-align: right; width: 110px; color: #10b981;">💊 คลังนอก</th>
                        <th style="text-align: right; width: 110px;">รวมคงเหลือ</th>
                        <th style="text-align: right; width: 120px;">มูลค่ารวม (บาท)</th>
                        <th style="width: 130px;">Lot / วันหมดอายุ</th>
                        <th style="width: 150px;">สถานะ</th>
                        <th style="width: 90px; text-align: center;">รบ. 301</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stocks)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                ไม่พบรายการยาที่ตรงกับเงื่อนไขการค้นหา
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stocks as $item): ?>
                            <?php
                                $mainR = (int)$item['main_store_remain'];
                                $dispR = (int)$item['dispensary_remain'];
                                $totR = (int)$item['total_remain'];
                                $mainPct = $totR > 0 ? round(($mainR / $totR) * 100) : 0;
                                $dispPct = $totR > 0 ? (100 - $mainPct) : 0;
                            ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
                                    <?= htmlspecialchars($item['drugcode']) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);">
                                        <?= htmlspecialchars($item['drugname']) ?>
                                    </div>
                                    <?php if (!empty($item['drugnamethai'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= htmlspecialchars($item['drugnamethai']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; color: var(--text-muted);">
                                    ฿<?= number_format((float)$item['unit_cost'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($totR > 0): ?>
                                        <div style="display: flex; height: 8px; border-radius: 4px; overflow: hidden; background: #e2e8f0;" title="คลังใน <?= $mainPct ?>% | คลังนอก <?= $dispPct ?>%">
                                            <div style="width: <?= $mainPct ?>%; background: #2563eb;"></div>
                                            <div style="width: <?= $dispPct ?>%; background: #10b981;"></div>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                                            <span><?= $mainPct ?>%</span>
                                            <span><?= $dispPct ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: var(--text-muted); text-align: center; display: block;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #2563eb;">
                                    <?= number_format($mainR) ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #10b981;">
                                    <?= number_format($dispR) ?>
                                </td>
                                <td style="text-align: right; font-weight: 800;">
                                    <?= number_format($totR) ?>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    ฿<?= number_format((float)$item['total_value'], 2) ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['dateexpire']) && $item['dateexpire'] !== '0000-00-00'): ?>
                                        <div style="font-size: 11px; font-weight: 600; color: <?= $item['days_to_expire'] <= 90 ? 'var(--danger)' : 'var(--text-main)' ?>;">
                                            <?= htmlspecialchars($item['dateexpire']) ?>
                                        </div>
                                        <div style="font-size: 10px; color: var(--text-muted);">
                                            Lot: <?= htmlspecialchars($item['lotno'] ?: '-') ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $item['status_badge'] ?>" style="font-size: 10px; padding: 3px 6px;">
                                        <?= htmlspecialchars($item['stock_status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="/pcc/inventory/rb301?drugcode=<?= urlencode($item['drugcode']) ?>" 
                                       class="btn btn-secondary btn-sm" 
                                       style="padding: 3px 8px; font-size: 11px; border-color: #2563eb; color: #2563eb; display: inline-flex; align-items: center; gap: 3px;"
                                       title="เปิดดูบัญชีคุมเวชภัณฑ์ รบ. 301">
                                        <span>📋</span> รบ. 301
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($pages > 1): ?>
            <div style="padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); background: var(--bg-card); flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 13px; color: var(--text-muted);">
                    หน้า <?= $page ?> จากทั้งหมด <?= $pages ?> หน้า (รวม <?= number_format($total) ?> รายการ)
                </div>
                <div style="display: flex; gap: 6px;">
                    <?php if ($page > 1): ?>
                        <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=1" class="btn btn-secondary btn-sm">« หน้าแรก</a>
                        <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">‹ ก่อนหน้า</a>
                    <?php endif; ?>

                    <span class="btn btn-primary btn-sm" style="cursor: default;"><?= $page ?></span>

                    <?php if ($page < $pages): ?>
                        <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page + 1 ?>" class="btn btn-secondary btn-sm">ถัดไป ›</a>
                        <a href="/pcc/inventory/jhcis-stores?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $pages ?>" class="btn btn-secondary btn-sm">หน้าสุดท้าย »</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Recent Transfer History Section -->
    <div class="card" style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <span>📑 ประวัติการเบิกยาโอนเข้าคลังยานอกล่าสุด (Transfer History)</span>
                </h3>
                <span style="font-size: 12px; color: var(--text-muted);">
                    บันทึกการตัดจ่ายจากคลังยาในส่งมอบให้ห้องจ่ายยาหน้าห้องตรวจ (ตาราง drugrepositoryout &amp; detail)
                </span>
            </div>
            <span class="badge badge-info" style="font-size: 11px;">
                15 รายการล่าสุด
            </span>
        </div>

        <div class="table-responsive">
            <table class="table" style="font-size: 13px; margin: 0;">
                <thead>
                    <tr style="background: var(--bg-hover);">
                        <th>วันที่เบิก</th>
                        <th>เลขที่ใบเบิก</th>
                        <th>ปลายทาง / วัตถุประสงค์</th>
                        <th>รหัสยา</th>
                        <th>ชื่อยา</th>
                        <th style="text-align: right;">จำนวนที่โอน</th>
                        <th>หน่วย</th>
                        <th>Lot No.</th>
                        <th>วันหมดอายุ</th>
                        <th style="text-align: right;">คงเหลือคลังในหลังโอน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transfers)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 25px;">
                                ไม่พบบันทึกการเบิกโอนยาเข้าคลังยานอก
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transfers as $t): ?>
                            <tr>
                                <td style="color: var(--text-muted);"><?= htmlspecialchars($t['outdate']) ?></td>
                                <td style="font-weight: 600; font-family: monospace;"><?= htmlspecialchars($t['realno'] ?: $t['outno']) ?></td>
                                <td>
                                    <span class="badge badge-primary" style="font-size: 10px; background: rgba(37,99,235,0.1); color: #2563eb; border: 1px solid rgba(37,99,235,0.2);">
                                        <?= htmlspecialchars($t['companyname'] ?: 'คลังยานอก') ?>
                                    </span>
                                </td>
                                <td style="font-family: monospace;"><?= htmlspecialchars($t['drugcode']) ?></td>
                                <td><strong><?= htmlspecialchars($t['drugname']) ?></strong></td>
                                <td style="text-align: right; font-weight: 700; color: #10b981;">
                                    +<?= number_format((int)$t['total_units']) ?>
                                </td>
                                <td><?= htmlspecialchars($t['unitsell'] ?: 'หน่วย') ?></td>
                                <td style="font-family: monospace; font-size: 11px;"><?= htmlspecialchars($t['lotno'] ?: '-') ?></td>
                                <td style="font-size: 11px;"><?= htmlspecialchars($t['dateexpire'] ?: '-') ?></td>
                                <td style="text-align: right; font-weight: 600; color: #2563eb;">
                                    <?= number_format((int)$t['main_remain_after']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
