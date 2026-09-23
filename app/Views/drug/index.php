<?php
$items = $catalog['items'] ?? [];
$total = $catalog['total'] ?? 0;
$page = $catalog['page'] ?? 1;
$perPage = $catalog['per_page'] ?? 25;
$totalPages = $catalog['total_pages'] ?? 1;

$search = $filters['search'] ?? '';
$category = $filters['category'] ?? 'all';
$nlem = $filters['nlem'] ?? 'all';

$buildQuery = function(array $override = []) use ($filters): string {
    $merged = array_merge($filters ?? [], $override);
    return '?' . http_build_query(array_filter($merged, fn($v) => $v !== null && $v !== ''));
};
?>

<div class="drug-formulary-container">
    <!-- Quick Metric Cards Header -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-widget" style="border-left: 4px solid var(--primary);">
            <div>
                <div class="stat-label">แคตตาล็อกยา JHCIS ทั้งหมด</div>
                <div class="stat-value"><?= number_format($stats['total_catalog']) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">รายการ</span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">บัญชียา รพ.สต.: <strong><?= number_format($stats['pcu_formulary_count']) ?></strong> รายการ</div>
            </div>
            <div class="stat-icon primary">💊</div>
        </div>

        <div class="stat-widget" style="border-left: 4px solid var(--warning);">
            <div>
                <div class="stat-label">ยากลุ่มเสี่ยงสูง & LASA</div>
                <div class="stat-value" style="color: #d97706;">
                    <?= number_format($stats['ham_count']) ?> <span style="font-size: 12px; color: var(--text-muted);">HAM</span> / 
                    <?= number_format($stats['lasa_count']) ?> <span style="font-size: 12px; color: var(--text-muted);">LASA</span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Tall Man Lettering ป้องกันสั่งผิด</div>
            </div>
            <div class="stat-icon warning">⚠️</div>
        </div>

        <div class="stat-widget" style="border-left: 4px solid #8b5cf6;">
            <div>
                <div class="stat-label">ยาปฏิชีวนะ & RDU เฝ้าระวัง</div>
                <div class="stat-value" style="color: #7c3aed;"><?= number_format($stats['antibiotic_count']) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">รายการ</span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">ติดตามเป้าหมาย URI / Diarrhea ≤ 20%</div>
            </div>
            <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">🦠</div>
        </div>

        <div class="stat-widget" style="border-left: 4px solid #0284c7;">
            <div>
                <div class="stat-label">วัคซีน Cold Chain & CPR Kit</div>
                <div class="stat-value" style="color: #0284c7;">
                    <?= number_format($stats['cold_chain_count']) ?> <span style="font-size: 12px; color: var(--text-muted);">วัคซีน</span> / 
                    <?= number_format($stats['emergency_count']) ?> <span style="font-size: 12px; color: var(--text-muted);">CPR</span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">มียอดคงคลังใน รพ.สต.: <strong><?= number_format($stats['in_stock_count']) ?></strong> ชนิด</div>
            </div>
            <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">❄️</div>
        </div>
    </div>

    <!-- Filter & Action Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 20px;">
            <form method="GET" action="/pcc/drugs" id="filterForm">
                <div style="display: grid; grid-template-columns: 2fr 1.3fr 1fr 100px auto; gap: 14px; align-items: flex-end;">
                    <!-- Search Input -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">🔍 ค้นหารายการยา</label>
                        <input type="text" name="q" class="form-control" 
                               placeholder="พิมพ์ชื่อการค้า, ชื่อสามัญ, ภาษาไทย, รหัสยา หรือ TMT..." 
                               value="<?= htmlspecialchars($search) ?>" autofocus>
                    </div>

                    <!-- Category Filter -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">หมวดหมู่ทางเภสัชกรรม</label>
                        <select name="category" class="form-control" onchange="this.form.submit()">
                            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>🌐 แคตตาล็อกยาทั้งหมด (<?= number_format($stats['total_catalog']) ?>)</option>
                            <option value="pcu_formulary" <?= $category === 'pcu_formulary' ? 'selected' : '' ?>>📋 บัญชียา รพ.สต. (<?= number_format($stats['pcu_formulary_count']) ?>)</option>
                            <option value="modern" <?= $category === 'modern' ? 'selected' : '' ?>>💊 ยาแผนปัจจุบัน (<?= number_format($stats['modern_count']) ?>)</option>
                            <option value="herbal" <?= $category === 'herbal' ? 'selected' : '' ?>>🌿 ยาสมุนไพร/ยาไทย (<?= number_format($stats['herbal_count']) ?>)</option>
                            <option value="cold_chain" <?= $category === 'cold_chain' ? 'selected' : '' ?>>❄️ ยาชีววัตถุ/วัคซีน Cold Chain (<?= number_format($stats['cold_chain_count']) ?>)</option>
                            <option value="emergency" <?= $category === 'emergency' ? 'selected' : '' ?>>🚑 ยาช่วยชีวิตฉุกเฉิน CPR Kit (<?= number_format($stats['emergency_count']) ?>)</option>
                            <option value="antibiotic" <?= $category === 'antibiotic' ? 'selected' : '' ?>>🦠 ยาปฏิชีวนะ RDU (<?= number_format($stats['antibiotic_count']) ?>)</option>
                        </select>
                    </div>

                    <!-- NLEM Filter -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">บัญชียาหลักแห่งชาติ</label>
                        <select name="nlem" class="form-control" onchange="this.form.submit()">
                            <option value="all" <?= $nlem === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                            <option value="1" <?= $nlem === '1' ? 'selected' : '' ?>>ในบัญชียาหลัก (EDL)</option>
                            <option value="2" <?= $nlem === '2' ? 'selected' : '' ?>>นอกบัญชียาหลัก</option>
                        </select>
                    </div>

                    <!-- Items per page -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">แถว/หน้า</label>
                        <select name="per_page" class="form-control" onchange="this.form.submit()">
                            <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15</option>
                            <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                            ค้นหา
                        </button>
                        <?php if (!empty($search) || $category !== 'all' || $nlem !== 'all'): ?>
                            <a href="/pcc/drugs" class="btn btn-secondary" title="ล้างตัวกรอง" style="padding: 10px 14px;">✕</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- Export & Print Action Toolbar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <div style="font-size: 13px; color: var(--text-secondary);">
                    แสดงผล <strong><?= number_format(min($total, ($page - 1) * $perPage + 1)) ?></strong> - <strong><?= number_format(min($total, $page * $perPage)) ?></strong> จากทั้งหมด <strong><?= number_format($total) ?></strong> รายการ
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="/pcc/drugs/export-excel<?= $buildQuery() ?>" class="btn btn-secondary btn-sm" style="color: #15803d; border-color: #bbf7d0; background: #f0fdf4;" title="ส่งออกเป็นไฟล์ Microsoft Excel">
                        📊 ส่งออก Excel (.xls)
                    </a>
                    <a href="/pcc/drugs/export-csv<?= $buildQuery() ?>" class="btn btn-secondary btn-sm" style="color: #0369a1; border-color: #bae6fd; background: #f0f9ff;" title="ส่งออกเป็นไฟล์ CSV (UTF-8 BOM)">
                        📑 ส่งออก CSV
                    </a>
                    <a href="/pcc/drugs/print?category=<?= htmlspecialchars($category) ?>" target="_blank" class="btn btn-secondary btn-sm" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff;" title="พิมพ์เอกสารบัญชียาทางการ">
                        🖨️ พิมพ์บัญชียา รพ.สต.
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Drug Catalog Table -->
    <div class="card">
        <div class="card-header" style="background: #fafafa;">
            <div class="card-title">
                <span>💊 บัญชีรายการยาทั้งหมดของ รพ.สต. (PCU Medication Catalog)</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                อ้างอิงฐานข้อมูล JHCIS `cdrug` & `cdrugunitsell` (Port 3333)
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 100px;">รหัสยา</th>
                            <th>ชื่อการค้าและขนาด (Trade Name) / ชื่อสามัญ</th>
                            <th>ชื่อภาษาไทย</th>
                            <th style="width: 110px;">หน่วย / บรรจุ</th>
                            <th style="width: 130px; text-align: right;">ต้นทุน / เบิกจ่าย</th>
                            <th style="width: 100px; text-align: center;">บัญชียาหลัก</th>
                            <th style="width: 180px;">มาตรฐานความปลอดภัย</th>
                            <th style="width: 100px; text-align: center;">คงคลัง รพ.สต.</th>
                            <th style="width: 90px; text-align: center;">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    <div style="font-size: 32px; margin-bottom: 10px;">🔍</div>
                                    <div style="font-size: 16px; font-weight: 500;">ไม่พบข้อมูลรายการยาตามเงื่อนไขที่ค้นหา</div>
                                    <div style="font-size: 13px; margin-top: 5px;">กรุณาลองเปลี่ยนคำค้นหาหรือเลือกหมวดหมู่ยาอื่นๆ</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $d): ?>
                                <tr>
                                    <!-- Drug Code & TMT -->
                                    <td>
                                        <div style="font-family: monospace; font-weight: 600; color: #334155;"><?= htmlspecialchars($d['drugcode']) ?></div>
                                        <?php if (!empty($d['tmtcode'])): ?>
                                            <span class="badge badge-secondary" style="font-size: 10px; padding: 2px 4px;" title="รหัสมาตรฐาน TMT">TMT: <?= htmlspecialchars($d['tmtcode']) ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Drug Trade & Generic Name (Tall Man for LASA) -->
                                    <td>
                                        <div style="font-size: 14.5px; font-weight: 600; color: var(--text-primary);">
                                            <?php if ($d['is_lasa'] && !empty($d['tall_man'])): ?>
                                                <span style="color: #b45309; background: #fef3c7; padding: 1px 6px; border-radius: 4px; font-family: monospace;">
                                                    <?= htmlspecialchars($d['tall_man']) ?>
                                                </span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($d['drugname']) ?>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($d['druggenericname'])): ?>
                                            <div style="font-size: 12px; color: var(--text-secondary); font-style: italic;">
                                                <?= htmlspecialchars($d['druggenericname']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Thai Name -->
                                    <td style="font-size: 13px; color: #475569;">
                                        <?= !empty($d['drugnamethai']) ? htmlspecialchars($d['drugnamethai']) : '<span style="color:#cbd5e1;">-</span>' ?>
                                    </td>

                                    <!-- Packaging & Sell Unit -->
                                    <td style="font-size: 13px;">
                                        <span class="badge badge-secondary" style="font-weight: 500;">
                                            <?= htmlspecialchars($d['unitsell']) ?>
                                        </span>
                                        <?php if (!empty($d['pack']) && $d['pack'] !== '-'): ?>
                                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                                <?= htmlspecialchars($d['pack']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Cost & Sell Price -->
                                    <td style="text-align: right; font-size: 13px;">
                                        <div style="color: #64748b;">฿<?= number_format($d['cost'], 2) ?></div>
                                        <div style="font-weight: 600; color: #0f766e;">฿<?= number_format($d['sell'], 2) ?></div>
                                    </td>

                                    <!-- NLEM Status -->
                                    <td style="text-align: center;">
                                        <?php if ($d['is_nlem']): ?>
                                            <span class="badge badge-success" style="font-size: 11px;">EDL ในบัญชี</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 11px; opacity: 0.7;">นอกบัญชี</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Safety Badges -->
                                    <td>
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                            <?php if ($d['is_ham']): ?>
                                                <span class="badge badge-danger" style="font-size: 10.5px; padding: 2px 6px;" title="ยาความเสี่ยงสูง (High Alert Medication)">
                                                    ⚠️ HAM
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($d['is_lasa']): ?>
                                                <span class="badge badge-warning" style="font-size: 10.5px; padding: 2px 6px;" title="ยาชื่อพ้องมองคล้าย (Look-Alike Sound-Alike)">
                                                    🏷️ LASA
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($d['is_antibiotic']): ?>
                                                <span class="badge" style="background: #f3e8ff; color: #6b21a8; font-size: 10.5px; padding: 2px 6px;" title="ยาปฏิชีวนะ เฝ้าระวังเกณฑ์ RDU Community">
                                                    🦠 ปฏิชีวนะ
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($d['is_cold_chain']): ?>
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10.5px; padding: 2px 6px;" title="เก็บในตู้เย็นควบคุมอุณหภูมิ 2-8 °C">
                                                    ❄️ Cold Chain
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($d['is_emergency']): ?>
                                                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 10.5px; padding: 2px 6px;" title="ยาช่วยชีวิตฉุกเฉิน CPR Kit">
                                                    🚑 CPR Kit
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($d['is_herbal']): ?>
                                                <span class="badge" style="background: #ecfdf5; color: #047857; font-size: 10.5px; padding: 2px 6px;" title="ยาสมุนไพรและแพทย์แผนไทย">
                                                    🌿 สมุนไพร
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Stock Balance in PCU -->
                                    <td style="text-align: center;">
                                        <?php if ($d['stock_balance'] > 0): ?>
                                            <span class="badge badge-success" style="font-size: 12px; font-weight: 600;">
                                                <?= number_format($d['stock_balance']) ?>
                                            </span>
                                            <?php if ($d['lots_count'] > 0): ?>
                                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;"><?= $d['lots_count'] ?> Lot FEFO</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: #94a3b8;">0</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Action Button -->
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-secondary btn-sm" 
                                                    onclick='openEditDrugModal(<?= json_encode($d, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                                                    title="แก้ไขข้อมูลพื้นฐานรายการยาและค่าความปลอดภัย"
                                                    style="padding: 3px 8px; font-size: 11.5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;">
                                                ✏️ แก้ไข
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" 
                                                    onclick="openDrugModal('<?= htmlspecialchars($d['drugcode']) ?>')" 
                                                    title="ดูแฟ้มข้อมูลยา & ข้อควรระวัง"
                                                    style="padding: 3px 8px; font-size: 11.5px;">
                                                📋 แฟ้มยา
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: #fafafa; border-top: 1px solid var(--border-color);">
                <div style="font-size: 13px; color: var(--text-secondary);">
                    หน้า <strong><?= $page ?></strong> จากทั้งหมด <strong><?= $totalPages ?></strong> หน้า
                </div>

                <div style="display: flex; gap: 6px;">
                    <?php if ($page > 1): ?>
                        <a href="/pcc/drugs<?= $buildQuery(['page' => 1]) ?>" class="btn btn-secondary btn-sm" title="หน้าแรก">« หน้าแรก</a>
                        <a href="/pcc/drugs<?= $buildQuery(['page' => $page - 1]) ?>" class="btn btn-secondary btn-sm">‹ ก่อนหน้า</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <a href="/pcc/drugs<?= $buildQuery(['page' => $p]) ?>" 
                           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"
                           style="min-width: 34px; text-align: center;">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="/pcc/drugs<?= $buildQuery(['page' => $page + 1]) ?>" class="btn btn-secondary btn-sm">ถัดไป ›</a>
                        <a href="/pcc/drugs<?= $buildQuery(['page' => $totalPages]) ?>" class="btn btn-secondary btn-sm" title="หน้าสุดท้าย">หน้าสุดท้าย »</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Drug Detail Modal (AJAX Loaded) -->
<div id="drugDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 780px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg); animation: modalIn 0.2s ease;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">💊</span>
                <div>
                    <h3 id="modalDrugName" style="font-size: 17px; margin-bottom: 2px;">รายละเอียดข้อมูลยา</h3>
                    <div id="modalDrugGeneric" style="font-size: 12.5px; color: var(--text-secondary); font-style: italic;">Generic Name</div>
                </div>
            </div>
            <button type="button" onclick="closeDrugModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
        </div>

        <div id="modalLoading" style="padding: 50px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 32px; animation: spin 1s infinite linear;">⏳</div>
            <div style="margin-top: 10px;">กำลังโหลดข้อมูลทางเภสัชกรรมและความปลอดภัย...</div>
        </div>

        <div id="modalContent" style="display: none; padding: 24px;">
            <!-- Safety Alert Banner if HAM/LASA/Precaution -->
            <div id="modalSafetyBanner" style="display: none; margin-bottom: 20px; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13.5px; line-height: 1.5;"></div>

            <!-- Basic Identification Grid -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">รหัสยา JHCIS:</div>
                    <div id="modalCode" style="font-weight: 600; font-family: monospace;">-</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">รหัสมาตรฐาน TMT:</div>
                    <div id="modalTmt" style="font-weight: 600; font-family: monospace;">-</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">ชื่อภาษาไทย:</div>
                    <div id="modalThaiName" style="font-weight: 600;">-</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">หน่วยนับ / ขนาดบรรจุ:</div>
                    <div id="modalUnitPack" style="font-weight: 600;">-</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">ราคาต้นทุน / ราคาเบิกจ่าย:</div>
                    <div id="modalPrice" style="font-weight: 600; color: #0f766e;">-</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">สถานะบัญชียาหลัก (NLEM):</div>
                    <div id="modalNlem" style="font-weight: 600;">-</div>
                </div>
            </div>

            <!-- Standard Dosage Instructions (sysdrugdose) -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 14px; margin-bottom: 10px; color: #334155; display: flex; align-items: center; gap: 6px;">
                    <span>📝</span> วิธีใช้ยามาตรฐานในระบบ JHCIS (`sysdrugdose`)
                </h4>
                <div id="modalDosesList" style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 16px; font-size: 13.5px;">
                    -
                </div>
            </div>

            <!-- Clinical Caution & Contraindications -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 14px; margin-bottom: 10px; color: #991b1b; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span> คำเตือน ข้อห้ามใช้ และข้อควรระวังพิเศษ (`drugcaution`)
                </h4>
                <div id="modalCautionText" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 12px 16px; font-size: 13.5px; color: #991b1b;">
                    -
                </div>
            </div>

            <!-- Stock & Active Lots in PCU -->
            <div>
                <h4 style="font-size: 14px; margin-bottom: 10px; color: #334155; display: flex; align-items: center; gap: 6px;">
                    <span>📦</span> สต็อกคงคลังและล็อตยา รพ.สต. (FEFO Active Lots)
                </h4>
                <div id="modalStockLots" style="font-size: 13px;">
                    -
                </div>
            </div>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeDrugModal()">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<script>
function openDrugModal(code) {
    const modal = document.getElementById('drugDetailModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    
    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    fetch('/pcc/api/drugs/' + encodeURIComponent(code))
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                const d = res.data;
                document.getElementById('modalDrugName').textContent = d.drugname;
                document.getElementById('modalDrugGeneric').textContent = d.druggenericname || d.drugnamethai || 'ไม่มีชื่อสามัญ';
                document.getElementById('modalCode').textContent = d.drugcode;
                document.getElementById('modalTmt').textContent = d.tmtcode || '-';
                document.getElementById('modalThaiName').textContent = d.drugnamethai || '-';
                document.getElementById('modalUnitPack').textContent = d.unitsell + ' (บรรจุ: ' + d.pack + ')';
                document.getElementById('modalPrice').textContent = '฿' + d.cost.toFixed(2) + ' / ฿' + d.sell.toFixed(2);
                document.getElementById('modalNlem').textContent = d.is_nlem ? 'ในบัญชียาหลักแห่งชาติ' : 'นอกบัญชียาหลัก';

                // Safety Banner
                const banner = document.getElementById('modalSafetyBanner');
                if (d.is_ham) {
                    banner.style.display = 'block';
                    banner.style.background = '#fef2f2';
                    banner.style.border = '1px solid #f87171';
                    banner.style.color = '#991b1b';
                    banner.innerHTML = '<strong>⚠️ ยากลุ่มเสี่ยงสูง (High Alert Medication):</strong> ต้องมีระบบ Double-check ก่อนบริหารยา ' + (d.ham_info ? '<br><small>' + d.ham_info.precautions + '</small>' : '');
                } else if (d.is_lasa) {
                    banner.style.display = 'block';
                    banner.style.background = '#fffbeb';
                    banner.style.border = '1px solid #fde68a';
                    banner.style.color = '#92400e';
                    banner.innerHTML = '<strong>🏷️ ยาชื่อพ้องมองคล้าย (LASA):</strong> ระวังการหยิบจ่ายสลับกับ ' + (d.lasa_info ? d.lasa_info.pair_name : 'ยาคู่เทียบ') + ' (ใช้ Tall Man: ' + (d.lasa_info ? d.lasa_info.tall_man : '') + ')';
                } else if (d.is_antibiotic) {
                    banner.style.display = 'block';
                    banner.style.background = '#f5f3ff';
                    banner.style.border = '1px solid #ddd6fe';
                    banner.style.color = '#5b21b6';
                    banner.innerHTML = '<strong>🦠 ยาปฏิชีวนะ (Antibiotic Stewardship):</strong> เฝ้าระวังการใช้ยาตามเกณฑ์ RDU Community ในโรคหวังผล URI และ Acute Diarrhea';
                } else {
                    banner.style.display = 'none';
                }

                // Doses List
                const dosesContainer = document.getElementById('modalDosesList');
                if (d.doses && d.doses.length > 0) {
                    let html = '<ul style="margin: 0; padding-left: 20px;">';
                    d.doses.forEach(dose => {
                        html += '<li style="margin-bottom: 6px;">' + (dose.dosedescription || '-') + '</li>';
                    });
                    html += '</ul>';
                    dosesContainer.innerHTML = html;
                } else {
                    dosesContainer.innerHTML = '<span style="color: #94a3b8;">ไม่พบวิธีใช้ยามาตรฐานที่ผูกไว้ในระบบ JHCIS</span>';
                }

                // Caution Text
                const cautionContainer = document.getElementById('modalCautionText');
                if (d.drugcaution && d.drugcaution.trim() !== '') {
                    cautionContainer.textContent = d.drugcaution;
                    cautionContainer.parentElement.style.display = 'block';
                } else {
                    cautionContainer.textContent = 'ไม่มีข้อควรระวังพิเศษที่บันทึกไว้ใน JHCIS';
                }

                // Stock Lots Table
                const stockContainer = document.getElementById('modalStockLots');
                if (d.lots && d.lots.length > 0) {
                    let lotHtml = '<table class="table" style="font-size: 12px; margin-bottom: 0;"><thead><tr><th>Lot No.</th><th>สถานที่เก็บ</th><th>วันหมดอายุ</th><th>คงเหลือ</th><th>สถานะ</th></tr></thead><tbody>';
                    d.lots.forEach(lot => {
                        lotHtml += '<tr><td style="font-family: monospace;">' + lot.lot_number + '</td><td>' + lot.location_name + '</td><td>' + lot.expiry_date + ' (เหลือ ' + lot.days_to_expire + ' วัน)</td><td><strong>' + lot.quantity_balance + '</strong></td><td><span class="badge badge-success">พร้อมจ่าย</span></td></tr>';
                    });
                    lotHtml += '</tbody></table>';
                    stockContainer.innerHTML = lotHtml;
                } else {
                    stockContainer.innerHTML = '<div style="color: #64748b; padding: 10px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed #cbd5e1;">ไม่มียอดคงคลังยา Lot Active ใน รพ.สต. ปัจจุบัน</div>';
                }

                loading.style.display = 'none';
                content.style.display = 'block';
            }
        })
        .catch(err => {
            loading.innerHTML = '<div style="color: var(--danger);">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
}

function closeDrugModal() {
    document.getElementById('drugDetailModal').style.display = 'none';
}

function openEditDrugModal(drug) {
    document.getElementById('editDrugCode').value = drug.drugcode || '';
    document.getElementById('editDrugCodeDisp').value = drug.drugcode || '';
    document.getElementById('editDrugNameEng').value = drug.drugname || '';
    document.getElementById('editDrugNameThai').value = drug.drugnamethai || '';
    document.getElementById('editDrugGeneric').value = drug.druggenericname || '';
    
    document.getElementById('editIsNlem').checked = !!drug.is_nlem;
    document.getElementById('editIsHam').checked = !!drug.is_ham;
    document.getElementById('editIsLasa').checked = !!drug.is_lasa;
    document.getElementById('editIsAntibiotic').checked = !!drug.is_antibiotic;
    document.getElementById('editIsColdChain').checked = !!drug.is_cold_chain;
    document.getElementById('editIsEmergency').checked = !!drug.is_emergency;
    document.getElementById('editIsHerbal').checked = !!drug.is_herbal;

    document.getElementById('editMinStock').value = drug.min_stock || 0;
    document.getElementById('editMaxStock').value = drug.max_stock || 0;
    document.getElementById('editCaution').value = drug.caution || drug.drugcaution || '';

    document.getElementById('editDrugModal').style.display = 'flex';
}

function closeEditDrugModal() {
    document.getElementById('editDrugModal').style.display = 'none';
}

window.onclick = function(e) {
    const detailModal = document.getElementById('drugDetailModal');
    const editModal = document.getElementById('editDrugModal');
    if (e.target === detailModal) {
        closeDrugModal();
    }
    if (e.target === editModal) {
        closeEditDrugModal();
    }
}
</script>

<!-- Modal Edit Drug Master Config -->
<div id="editDrugModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 720px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/drugs/update-master">
            <?= CSRF::field() ?>
            <input type="hidden" name="drugcode" id="editDrugCode">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/pcc/drugs') ?>">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ แก้ไขข้อมูลพื้นฐานรายการยา (Master Drug Configuration)
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditDrugModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">รหัสยา JHCIS</label>
                        <input type="text" id="editDrugCodeDisp" class="form-control" readonly style="background: #f1f5f9; font-family: monospace; font-weight: 600;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อการค้าและขนาดภาษาอังกฤษ (JHCIS Trade Name)</label>
                        <input type="text" id="editDrugNameEng" class="form-control" readonly style="background: #f8fafc; color: #475569;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อภาษาไทย (Thai Name)</label>
                        <input type="text" name="drugnamethai" id="editDrugNameThai" class="form-control" placeholder="เช่น พาราเซตามอล 500 มก.">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ชื่อสามัญทางยา (Generic Name)</label>
                        <input type="text" name="druggenericname" id="editDrugGeneric" class="form-control" placeholder="เช่น Paracetamol">
                    </div>
                </div>

                <!-- Safety & Clinical Categorization Checkboxes -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 700; margin-bottom: 8px; display: block; color: #1e293b;">
                        การจัดหมวดหมู่ความปลอดภัยและเกณฑ์มาตรฐาน (Safety Standards)
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_nlem" value="1" id="editIsNlem">
                            <span>📘 บัญชียาหลักแห่งชาติ (EDL / NLEM)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #fee2e2; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_ham" value="1" id="editIsHam">
                            <span style="color: #b91c1c; font-weight: 600;">⚠️ ยาความเสี่ยงสูง (High Alert Drug)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #fef3c7; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_lasa" value="1" id="editIsLasa">
                            <span style="color: #b45309; font-weight: 600;">🏷️ ยาชื่อพ้องมองคล้าย (LASA)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #f3e8ff; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_antibiotic" value="1" id="editIsAntibiotic">
                            <span style="color: #7c3aed; font-weight: 600;">🦠 ยาปฏิชีวนะ เฝ้าระวัง RDU</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #e0f2fe; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_cold_chain" value="1" id="editIsColdChain">
                            <span style="color: #0369a1; font-weight: 600;">❄️ ยาควบคุมความเย็น (Cold Chain 2-8°C)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #fee2e2; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_emergency" value="1" id="editIsEmergency">
                            <span style="color: #dc2626; font-weight: 600;">🚑 ยาช่วยชีวิตฉุกเฉิน (CPR Kit)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border: 1px solid #ecfdf5; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_herbal" value="1" id="editIsHerbal">
                            <span style="color: #047857; font-weight: 600;">🌿 ยาสมุนไพรและแพทย์แผนไทย</span>
                        </label>
                    </div>
                </div>

                <!-- Stock Levels Threshold -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">เกณฑ์สำรองยาขั้นต่ำ (Min Stock Alert)</label>
                        <input type="number" name="min_stock" id="editMinStock" class="form-control" min="0" placeholder="0 = ไม่กำหนด">
                        <span style="font-size: 11.5px; color: var(--text-muted);">ระบบจะแจ้งเตือนเมื่อยอดคงคลังลดลงต่ำกว่าเกณฑ์นี้</span>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">เกณฑ์สำรองยาสูงสุด (Max Stock Threshold)</label>
                        <input type="number" name="max_stock" id="editMaxStock" class="form-control" min="0" placeholder="0 = ไม่กำหนด">
                        <span style="font-size: 11.5px; color: var(--text-muted);">ป้องกันการสั่งซื้อยาเกินความจำเป็น (Overstock)</span>
                    </div>
                </div>

                <!-- Clinical Caution -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">ข้อควรระวังทางคลินิกและคำเตือนพิเศษ (Clinical Caution / DRP Note)</label>
                    <textarea name="caution" id="editCaution" class="form-control" rows="3" placeholder="ระบุข้อควรระวัง เช่น ห้ามใช้ในหญิงมีครรภ์, ต้องตรวจติดตามการทำงานของไต eGFR, หรือปรับขนาดยา..."></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditDrugModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกข้อมูลพื้นฐาน & Audit Log</button>
            </div>
        </form>
    </div>
</div>

