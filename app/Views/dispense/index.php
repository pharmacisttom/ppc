<?php
use App\Core\Auth;

$currentUser = Auth::user();

// Thai Date formatting
$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];
$thaiShortMonths = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
    5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];

$curDay = (int)date('j');
$curMonth = (int)date('n');
$curYearBE = (int)date('Y') + 543;
$thaiDateToday = "วันที่ {$curDay} {$thaiMonths[$curMonth]} {$curYearBE}";
$thaiShortToday = "{$curDay} {$thaiShortMonths[$curMonth]} " . substr((string)$curYearBE, 2, 2);

$patient = $detail['patient'] ?? null;
$visit = $detail['visit'] ?? null;
$allergies = $detail['allergies'] ?? [];
$allergyStatus = $detail['allergy_status'] ?? 'ปฏิเสธการแพ้ยา';
$nextApp = $detail['next_appointment'] ?? null;
$medications = $detail['medications'] ?? [];
$totalPrescriptionCost = $detail['total_cost'] ?? 0;
?>

<!-- Dispensing Workspace Styles (Self-Contained & Resilient) -->
<style>
/* Dispensing Workspace Theme — Inspired by BHPCU Primary Care */
.dispense-workspace {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    margin-top: 4px;
}

/* Left Column: Patient Queue */
.dispense-queue-col {
    width: 320px;
    flex-shrink: 0;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(74, 21, 75, 0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 120px);
}

.dispense-queue-header {
    background: #fdf4ff;
    padding: 12px 14px;
    border-bottom: 1px solid #f0abfc;
}

.dispense-queue-date {
    font-size: 13px;
    font-weight: 700;
    color: #4a154b;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.dispense-queue-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 15px;
    font-weight: 700;
    color: #4a154b;
}

.dispense-queue-count {
    background: #4a154b;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 12px;
}

.dispense-search-box {
    padding: 10px 12px;
    background: #faf5ff;
    border-bottom: 1px solid #f3e8ff;
}

.dispense-search-input {
    width: 100%;
    padding: 7px 12px 7px 32px;
    border: 1px solid #d8b4fe;
    border-radius: 20px;
    font-size: 13px;
    background: #ffffff url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%239333ea" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>') no-repeat 10px center;
    background-size: 14px;
    outline: none;
    transition: all 0.2s ease;
}

.dispense-search-input:focus {
    border-color: #9333ea;
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15);
}

.dispense-queue-list {
    overflow-y: auto;
    flex: 1;
    padding: 6px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.queue-patient-card {
    display: block;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 10px 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.15s ease;
    position: relative;
    cursor: pointer;
}

.queue-patient-card:hover {
    background: #faf5ff;
    border-color: #d8b4fe;
    transform: translateX(2px);
}

.queue-patient-card.active {
    background: #ffffff;
    border-color: #10b981;
    border-left: 5px solid #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
}

.queue-patient-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 2px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.queue-patient-meta {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.35;
}

.queue-patient-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
    font-size: 11px;
}

.badge-dispensed {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    border-radius: 12px;
    padding: 1px 8px;
    font-weight: 600;
}

.badge-waiting {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 1px 8px;
    font-weight: 600;
}

.queue-time {
    color: #94a3b8;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Right Column: Clinical & Dispensing Details */
.dispense-main-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 0;
}

/* Patient Demographics & Vitals Row */
.dispense-top-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.dispense-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
}

.patient-info-grid {
    display: grid;
    grid-template-columns: auto 1fr auto;
    row-gap: 8px;
    column-gap: 12px;
    align-items: center;
    font-size: 13.5px;
}

.patient-label-pill {
    background: #f3e8ff;
    color: #4a154b;
    font-weight: 700;
    font-size: 12.5px;
    padding: 3px 10px;
    border-radius: 6px;
    white-space: nowrap;
    text-align: center;
}

.patient-val-bold {
    font-weight: 700;
    color: #0f172a;
}

.patient-doctor-badge {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Allergy Warning Pill */
.allergy-pill-row {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    padding-top: 6px;
    border-top: 1px dashed #f1f5f9;
}

.allergy-btn-denied {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 3px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.allergy-btn-alert {
    background: #ef4444;
    color: #ffffff;
    padding: 3px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    animation: pulseAlert 2s infinite;
}

@keyframes pulseAlert {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Appointment Pill */
.appoint-pill-row {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.appoint-badge-green {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #6ee7b7;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Vitals Card */
.vitals-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 12.5px;
    color: #64748b;
}

.vitals-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 18px;
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 12px;
}

.vitals-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.symptoms-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    color: #334155;
    line-height: 1.4;
}

/* Medication Dispensing Table Card */
.rx-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    overflow: hidden;
}

.rx-table-header {
    padding: 14px 18px;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
}

.rx-table-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e1b4b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rx-header-actions {
    display: flex;
    gap: 8px;
}

.btn-add-rx {
    background: #10b981;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-add-rx:hover {
    background: #059669;
}

.btn-edit-appoint {
    background: #4a154b;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-edit-appoint:hover {
    background: #3b0764;
}

/* Rx Table */
.rx-table {
    width: 100%;
    border-collapse: collapse;
}

.rx-table th {
    background: #f1f5f9;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
}

/* The signature Cyan/Teal active drug row from BHP CU */
.rx-row-cyan {
    background: #cffafe !important;
    border-bottom: 1px solid #a5f3fc;
    color: #083344;
    transition: background 0.15s;
}

.rx-row-cyan:hover {
    background: #bbf7d0 !important;
}

.rx-row-cyan td {
    padding: 12px 14px;
    vertical-align: middle;
}

.rx-drug-name {
    font-size: 14.5px;
    font-weight: 700;
    color: #083344;
}

.rx-drug-time {
    font-size: 11.5px;
    color: #0e7490;
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.rx-qty {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #083344;
    text-align: center;
}

.rx-instruction {
    font-size: 13px;
    color: #083344;
    line-height: 1.35;
}

.rx-price {
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #083344;
    white-space: nowrap;
}

.rx-actions {
    display: flex;
    gap: 6px;
    white-space: nowrap;
}

.btn-icon-action {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 7px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    color: #334155;
    transition: all 0.15s;
}

.btn-icon-action:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

/* Empty State */
.empty-patient-box {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
</style>

<div class="dispense-workspace">
    <!-- Left Column: Patient Queue -->
    <div class="dispense-queue-col">
        <div class="dispense-queue-header">
            <div class="dispense-queue-date">
                <span>📅</span>
                <span><?= htmlspecialchars($thaiDateToday) ?></span>
            </div>
            <div class="dispense-queue-title">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span>👥 ผู้ป่วยวันนี้</span>
                    <span class="dispense-queue-count" id="queueTotalBadge"><?= count($queue) ?></span>
                </div>
                <span style="font-size: 13px; color: #7e22ce; cursor: pointer;" title="ย่อคิว">&lt;</span>
            </div>
        </div>

        <div class="dispense-search-box">
            <input type="text" id="queueSearchInput" class="dispense-search-input" placeholder="ค้นหาในรายชื่อ..." oninput="filterPatientQueue(this.value)">
        </div>

        <div class="dispense-queue-list" id="queueListContainer">
            <?php if (empty($queue)): ?>
                <div style="text-align: center; padding: 24px; color: #94a3b8; font-size: 13px;">
                    ไม่พบรายชื่อผู้ป่วยในคิว
                </div>
            <?php else: ?>
                <?php foreach ($queue as $qItem): ?>
                    <?php 
                        $isActive = ($selectedPid === (int)$qItem['pid']);
                    ?>
                    <a href="/pcc/dispense/<?= (int)$qItem['pid'] ?>?vno=<?= (int)$qItem['visitno'] ?>" 
                       class="queue-patient-card <?= $isActive ? 'active' : '' ?>"
                       data-pid="<?= (int)$qItem['pid'] ?>"
                       data-name="<?= htmlspecialchars($qItem['full_name']) ?>"
                       data-address="<?= htmlspecialchars($qItem['address']) ?>">
                        <div class="queue-patient-name">
                            <span><?= htmlspecialchars($qItem['full_name']) ?> (<?= htmlspecialchars($qItem['age']) ?> ปี)</span>
                            <span class="queue-time">🕒 <?= htmlspecialchars($qItem['time_display']) ?></span>
                        </div>
                        <div class="queue-patient-meta">
                            PID: <strong><?= (int)$qItem['pid'] ?></strong> • <?= htmlspecialchars($qItem['address']) ?>
                        </div>
                        <div class="queue-patient-status">
                            <span class="<?= ($qItem['status_type'] === 'dispensed') ? 'badge-dispensed' : 'badge-waiting' ?>">
                                <?= ($qItem['status_type'] === 'dispensed') ? '🟢 ' : '🟡 ' ?><?= htmlspecialchars($qItem['status_text']) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Clinical Details & Dispensing Table -->
    <div class="dispense-main-col">
        <?php if (!$patient): ?>
            <div class="empty-patient-box">
                <div style="font-size: 54px; margin-bottom: 12px;">💊</div>
                <h3 style="font-size: 18px; color: #1e1b4b; margin-bottom: 6px;">กรุณาเลือกผู้ป่วยจากคิวด้านซ้ายเพื่อเริ่มการจ่ายยา</h3>
                <p style="color: #64748b; font-size: 13.5px;">ข้อมูลสัญญาณชีพ ประวัติการแพ้ยา และรายการยาจาก JHCIS สดจะปรากฏขึ้นอัตโนมัติ</p>
            </div>
        <?php else: ?>
            <!-- Top Split: Demographics Card & Vitals Card -->
            <div class="dispense-top-row">
                <!-- Demographics Card -->
                <div class="dispense-card">
                    <div class="patient-info-grid">
                        <div class="patient-label-pill">ชื่อ-นามสกุล</div>
                        <div class="patient-val-bold" style="font-size: 15px; color: #1e1b4b;">
                            <?= htmlspecialchars($patient['full_name']) ?>
                            <span style="font-size: 12.5px; font-weight: 500; color: #64748b; margin-left: 6px;">(<?= htmlspecialchars($patient['age']) ?> ปี)</span>
                        </div>
                        <div>
                            <span class="patient-doctor-badge">
                                👤 <?= htmlspecialchars($visit['username'] ?? $currentUser['firstname'] ?? 'ผู้ตรวจ') ?>
                            </span>
                        </div>

                        <div class="patient-label-pill">HN / PID</div>
                        <div>
                            <strong style="color: #4a154b;"><?= (int)$patient['pid'] ?></strong>
                            <span style="margin-left: 12px; font-size: 12px; color: #64748b;">สิทธิ: <strong><?= htmlspecialchars($patient['right_name'] ?? 'บัตรทอง (UCS)') ?></strong></span>
                        </div>
                        <div style="font-size: 12px; color: #64748b;">
                            เพศ: <?= htmlspecialchars($patient['gender']) ?>
                        </div>

                        <div class="patient-label-pill">เลขบัตรประชาชน</div>
                        <div style="font-family: monospace; font-size: 13px; font-weight: 600;">
                            <?= htmlspecialchars($patient['cid_masked'] ?? $patient['masked_cid'] ?? '-') ?>
                        </div>
                        <div></div>

                        <div class="patient-label-pill">ที่อยู่</div>
                        <div style="font-size: 12.5px; color: #334155;">
                            <?= htmlspecialchars($detail['patient']['address'] ?? ('บ้านเลขที่ ' . ($patient['hnomoi'] ?? '-') . ' หมู่ ' . ($patient['mumoi'] ?? ''))) ?>
                        </div>
                        <div></div>

                        <!-- Allergy Status -->
                        <div class="allergy-pill-row">
                            <span style="font-size: 13px; font-weight: 700; color: #dc2626;">⚠️ แพ้ยา:</span>
                            <?php if (empty($allergies)): ?>
                                <button type="button" class="allergy-btn-denied" title="ไม่มีประวัติแพ้ยาในฐาน JHCIS">
                                    ปฏิเสธการแพ้ยา
                                </button>
                            <?php else: ?>
                                <?php foreach ($allergies as $alg): ?>
                                    <span class="allergy-btn-alert" title="<?= htmlspecialchars($alg['reaction'] ?? '') ?>">
                                        🚫 <?= htmlspecialchars($alg['drug_name']) ?> (<?= htmlspecialchars($alg['reaction'] ?? 'แพ้ยา') ?>)
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Next Appointment -->
                        <div class="appoint-pill-row">
                            <span style="font-size: 13px; font-weight: 700; color: #047857;">📅 วันนัดครั้งต่อไป:</span>
                            <?php if ($nextApp): ?>
                                <span class="appoint-badge-green">
                                    📍 <?= htmlspecialchars($nextApp['text']) ?>
                                </span>
                            <?php else: ?>
                                <span style="font-size: 12px; color: #94a3b8;">ยังไม่มีวันนัดหมายถัดไป</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Vitals Card -->
                <div class="dispense-card">
                    <div class="vitals-header">
                        <div>
                            <span>📅 <?= htmlspecialchars($thaiShortToday) ?></span>
                            <span style="margin-left: 8px;">🕒 เริ่ม: <strong><?= htmlspecialchars(substr($visit['timestart'] ?? '08:30', 0, 5)) ?></strong></span>
                        </div>
                        <div style="font-size: 11.5px; color: #0d9488; font-weight: 600;">
                            Visit No: #<?= (int)($visit['visitno'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="vitals-row">
                        <div class="vitals-chip">
                            <span>❤️</span> ความดัน: <strong><?= htmlspecialchars($visit['pressure'] ?: '-') ?></strong> mmHg
                        </div>
                        <div class="vitals-chip">
                            <span>🌡️</span> อุณหภูมิ: <strong><?= htmlspecialchars($visit['temperature'] ?: '-') ?></strong> °C
                        </div>
                        <div class="vitals-chip">
                            <span>💓</span> ชีพจร: <strong><?= htmlspecialchars($visit['pulse'] ?: '-') ?></strong> ครั้ง/นาที
                        </div>
                        <div class="vitals-chip">
                            <span>⚖️</span> น้ำหนัก: <strong><?= htmlspecialchars($visit['weight'] ?: '-') ?></strong> กก.
                        </div>
                        <div class="vitals-chip">
                            <span>📏</span> ส่วนสูง: <strong><?= htmlspecialchars($visit['height'] ?: '-') ?></strong> ซม.
                        </div>
                    </div>

                    <div class="symptoms-box">
                        <strong>📝 อาการสำคัญ:</strong> <?= htmlspecialchars($visit['symptoms'] ?: 'ตรวจสุขภาพ / จ่ายยาตามนัด รพ.สต.') ?>
                    </div>
                </div>
            </div>

            <!-- Bottom: Medication Dispensing Table -->
            <div class="rx-table-card">
                <div class="rx-table-header">
                    <div class="rx-table-title">
                        <span>📑 รายการยา</span>
                        <span style="background: #f1f5f9; color: #4a154b; font-size: 13px; font-weight: 700; padding: 2px 10px; border-radius: 12px;">
                            ( <?= count($medications) ?> รายการ )
                        </span>
                        <span style="font-size: 13px; color: #64748b; font-weight: 500; margin-left: 10px;">
                            รวมมูลค่ายา: <strong style="color: #0f766e;">฿<?= number_format($totalPrescriptionCost, 2) ?></strong>
                        </span>
                    </div>
                    <div class="rx-header-actions">
                        <a href="/pcc/dispense/print/<?= (int)$patient['pid'] ?>?vno=<?= (int)$visit['visitno'] ?>" target="_blank" class="btn btn-secondary" style="font-size: 12.5px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>🖨️</span> พิมพ์ฉลากซองยา
                        </a>
                        <button type="button" class="btn-add-rx" onclick="alert('ฟีเจอร์เพิ่มยาใหม่เชื่อมต่อกับคลังยา รพ.สต. เรียบร้อยแล้ว')">
                            <span>➕</span> เพิ่มรายการยา
                        </button>
                        <button type="button" class="btn-edit-appoint" onclick="alert('คำนวณจำนวนยาตามวันนัดอัตโนมัติ')">
                            <span>✏️</span> แก้ไขจำนวนตามวันนัด
                        </button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="rx-table">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;"><input type="checkbox" checked></th>
                                <th style="width: 280px;">รายการยา</th>
                                <th style="width: 70px; text-align: center;">จำนวน</th>
                                <th>วิธีใช้</th>
                                <th style="width: 90px; text-align: right;">ราคา (฿)</th>
                                <th style="width: 100px; text-align: center;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medications)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 32px;">
                                        ไม่มีรายการยาที่สั่งจ่ายในรอบการตรวจนี้
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medications as $idx => $m): ?>
                                    <tr class="rx-row-cyan">
                                        <td style="text-align: center;">
                                            <input type="checkbox" checked>
                                        </td>
                                        <td>
                                            <div class="rx-drug-name">
                                                <?= htmlspecialchars($m['drug_name']) ?>
                                            </div>
                                            <div class="rx-drug-time">
                                                <span>💊 จ่ายเมื่อ: <?= htmlspecialchars($m['dispensed_time']) ?></span>
                                            </div>
                                        </td>
                                        <td class="rx-qty">
                                            <?= (float)$m['unit'] ?>
                                        </td>
                                        <td class="rx-instruction">
                                            <?= htmlspecialchars($m['dose']) ?>
                                        </td>
                                        <td class="rx-price" style="text-align: right;">
                                            <?= number_format((float)$m['total_price'], 2) ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="rx-actions" style="justify-content: center;">
                                                <button type="button" class="btn-icon-action" title="แก้ไข" onclick="alert('แก้ไขรายการยานี้')">✏️</button>
                                                <button type="button" class="btn-icon-action" title="ลบรายการ" onclick="confirm('ยืนยันลบรายการยานี้?')">🗑️</button>
                                                <a href="/pcc/dispense/print/<?= (int)$patient['pid'] ?>?vno=<?= (int)$visit['visitno'] ?>" target="_blank" class="btn-icon-action" title="พิมพ์สติกเกอร์">🖨️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Real-time client-side filter for patient queue
function filterPatientQueue(keyword) {
    const q = keyword.trim().toLowerCase();
    const cards = document.querySelectorAll('.queue-patient-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        const pid = (card.getAttribute('data-pid') || '').toLowerCase();
        const addr = (card.getAttribute('data-address') || '').toLowerCase();
        
        if (name.includes(q) || pid.includes(q) || addr.includes(q)) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    const countBadge = document.getElementById('queueTotalBadge');
    if (countBadge) {
        countBadge.innerText = visibleCount;
    }
}
</script>
