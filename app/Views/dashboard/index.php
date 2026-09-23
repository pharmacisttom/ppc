<style>
/* Modern Clinical Dashboard Styles */
.dashboard-hero {
    background: linear-gradient(135deg, #4a154b 0%, #310c33 50%, #0f766e 100%);
    border-radius: 16px;
    padding: 22px 28px;
    color: #ffffff;
    margin-bottom: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(74, 21, 75, 0.2);
}
.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.hero-title {
    font-size: 23px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 4px;
}
.hero-subtitle {
    font-size: 13.5px;
    color: #f1f5f9;
    max-width: 720px;
    line-height: 1.45;
}
.telemetry-chip {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid #10b981;
    color: #ffffff;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.kpi-row-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
@media (max-width: 1200px) {
    .kpi-row-5 {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .kpi-row-5 {
        grid-template-columns: 1fr;
    }
}
.kpi-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
.kpi-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}
.kpi-stat-card.allergy::before { background: #ef4444; }
.kpi-stat-card.g6pd::before { background: #dc2626; }
.kpi-stat-card.ncd::before { background: #3b82f6; }
.kpi-stat-card.ckd::before { background: #8b5cf6; }
.kpi-stat-card.vhv::before { background: #10b981; }

.kpi-stat-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kpi-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 6px 0 2px 0;
}
.kpi-stat-desc {
    font-size: 11.5px;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.chart-container-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    margin-bottom: 22px;
}
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 10px;
}
.chart-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<div class="dashboard-container">
    <!-- Executive Header -->
    <div class="dashboard-hero">
        <div>
            <div class="hero-tag">
                <span>🏥</span>
                <span><?= htmlspecialchars($user['facility_name'] ?? 'รพ.สต.บ้านดอกกราย') ?> (รหัส: <?= htmlspecialchars($user['facility_code'] ?? '01996') ?>) • CUP รพ.ปลวกแดง</span>
            </div>
            <h1 class="hero-title">
                แดชบอร์ดสารสนเทศสาธารณสุข & ศูนย์ความปลอดภัยด้านยา
            </h1>
            <p class="hero-subtitle">
                รายงานสถิติสถานการณ์ผู้ป่วยแพ้ยา, ภาวะพร่องเอนไซม์ G6PD, โรคไม่ติดต่อเรื้อรัง (NCDs), ผู้ป่วยโรคไตเรื้อรัง (CKD), และเครือข่าย อสม. ในระบบ JHCIS สด
            </p>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
            <div class="telemetry-chip">
                <span class="status-dot"></span>
                <span>JHCIS Port 3333: เชื่อมต่อสด</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/pcc/exchange" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px; color: #fff; border-color: rgba(255,255,255,0.3);">
                    🌐 ส่งออก 43 แฟ้ม สธ.
                </a>
                <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px; color: #fff; border-color: rgba(255,255,255,0.3);">
                    🖨️ พิมพ์รายงาน
                </button>
            </div>
        </div>
    </div>

    <!-- 5 Key KPI Metric Cards -->
    <div class="kpi-row-5">
        <!-- 1. ผู้ป่วยแพ้ยา -->
        <div class="kpi-stat-card allergy">
            <div class="kpi-stat-title">
                <span>🚨 ผู้ป่วยแพ้ยา (Allergy)</span>
                <span class="badge badge-danger">JHCIS สด</span>
            </div>
            <div class="kpi-stat-value" style="color: #b91c1c;">
                <?= (int)($analytics['allergy']['total'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div class="kpi-stat-desc">
                <span>มีประวัติรับยาซ้ำ: <strong style="color: #dc2626;"><?= (int)($analytics['allergy']['repeat_prescribed'] ?? 0) ?> เคส</strong></span>
                <a href="/pcc/safety/allergies" style="font-weight: 700; color: #b91c1c;">ออกบัตร สธ. ➔</a>
            </div>
        </div>

        <!-- 2. ผู้ป่วย G6PD -->
        <div class="kpi-stat-card g6pd">
            <div class="kpi-stat-title">
                <span>🧬 ผู้ป่วย G6PD</span>
                <span class="badge badge-info">JHCIS สด</span>
            </div>
            <div class="kpi-stat-value" style="color: #dc2626;">
                <?= (int)($analytics['g6pd']['total'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div class="kpi-stat-desc">
                <?php if ((int)($analytics['g6pd']['total'] ?? 0) > 0): ?>
                    <span>ออกบัตรแล้ว: <strong><?= (int)($analytics['g6pd']['card_issued'] ?? 0) ?> คน</strong></span>
                <?php else: ?>
                    <span style="color: #64748b;">ไม่พบประวัติใน JHCIS</span>
                <?php endif; ?>
                <a href="/pcc/safety/g6pd" style="font-weight: 700; color: #dc2626;">เช็กยาห้าม ➔</a>
            </div>
        </div>

        <!-- 3. ผู้ป่วยโรค NCD -->
        <div class="kpi-stat-card ncd">
            <div class="kpi-stat-title">
                <span>🫀 ผู้ป่วยโรคเรื้อรัง (NCDs)</span>
                <span class="badge badge-info">ความดัน/เบาหวาน</span>
            </div>
            <div class="kpi-stat-value" style="color: #1d4ed8;">
                <?= (int)($analytics['ncd']['total'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div class="kpi-stat-desc">
                <?php
                $ncdLabels = [];
                foreach (array_slice($analytics['ncd']['categories'] ?? [], 0, 3) as $nc) {
                    preg_match('/\(([^)]+)\)/', $nc['ncd_category'], $mCat);
                    $abbr = $mCat[1] ?? $nc['ncd_category'];
                    $ncdLabels[] = $abbr . ': ' . number_format((int)$nc['patient_count']);
                }
                ?>
                <span><?= !empty($ncdLabels) ? implode(' | ', $ncdLabels) : 'ไม่มีข้อมูล' ?></span>
                <a href="#ncd-report-section" style="font-weight: 700; color: #1d4ed8;">ดูสัดส่วน ➔</a>
            </div>
        </div>

        <!-- 4. ผู้ป่วยโรคไตเรื้อรัง CKD -->
        <div class="kpi-stat-card ckd">
            <div class="kpi-stat-title">
                <span>🫘 ผู้ป่วยโรคไต (CKD)</span>
                <span class="badge badge-purple" style="background: #f3e8ff; color: #6b21a8;">Stage 1–5</span>
            </div>
            <div class="kpi-stat-value" style="color: #7c3aed;">
                <?= (int)($analytics['ckd']['total'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div class="kpi-stat-desc">
                <span>เตือนยาห้ามใช้ไต: <strong><?= (int)($analytics['ckd']['contraindicated_alerts'] ?? 0) ?> เคส</strong></span>
                <a href="/pcc/safety/ckd" style="font-weight: 700; color: #7c3aed;">ดูแลไต ➔</a>
            </div>
        </div>

        <!-- 5. อสม. ในระบบทั้งหมด -->
        <div class="kpi-stat-card vhv">
            <div class="kpi-stat-title">
                <span>👥 อสม. ในระบบ (VHV)</span>
                <span class="badge badge-success">เครือข่ายชุมชน</span>
            </div>
            <div class="kpi-stat-value" style="color: #059669;">
                <?= (int)($analytics['vhv']['total_vhv'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">ท่าน</span>
            </div>
            <div class="kpi-stat-desc">
                <span>ดูแล: <strong><?= (int)($analytics['vhv']['total_houses'] ?? 0) ?> หลังคาเรือน</strong></span>
                <a href="/pcc/vhv" style="font-weight: 700; color: #059669;">ดูทำเนียบ ➔</a>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: NCDs Distribution & Drug Allergy Categories -->
    <div class="grid-cols-2" style="margin-bottom: 22px;">
        <!-- Chart 1: NCDs Distribution -->
        <div class="chart-container-card">
            <div class="chart-header">
                <div class="chart-title">
                    <span>🫀</span>
                    <span>สัดส่วนผู้ป่วยโรคไม่ติดต่อเรื้อรัง (NCDs) ในเขต รพ.สต.</span>
                </div>
                <span class="badge badge-info"><?= (int)($analytics['ncd']['total'] ?? 0) ?> เคส</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="ncdChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Drug Allergies Grouping -->
        <div class="chart-container-card">
            <div class="chart-header">
                <div class="chart-title">
                    <span>🚨</span>
                    <span>กลุ่มยาที่พบประวัติการแพ้สูงสุด (Allergy by Drug Family)</span>
                </div>
                <span class="badge badge-danger"><?= (int)($analytics['allergy']['total'] ?? 0) ?> รายการ</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="allergyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: CKD Staging & VHV Households -->
    <div class="grid-cols-2" style="margin-bottom: 22px;">
        <!-- Chart 3: CKD Staging -->
        <div class="chart-container-card">
            <div class="chart-header">
                <div class="chart-title">
                    <span>🫘</span>
                    <span>การจำแนกระยะผู้ป่วยโรคไตเรื้อรัง (CKD Staging & eGFR)</span>
                </div>
                <span class="badge badge-purple" style="background: #f3e8ff; color: #6b21a8;"><?= (int)($analytics['ckd']['total'] ?? 0) ?> คน</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="ckdChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: VHV Distribution -->
        <div class="chart-container-card">
            <div class="chart-header">
                <div class="chart-title">
                    <span>👥</span>
                    <span>การกระจายตัวของ อสม. และหลังคาเรือนที่ดูแล (VHV Coverage)</span>
                </div>
                <span class="badge badge-success"><?= (int)($analytics['vhv']['total_vhv'] ?? 0) ?> อสม.</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="vhvChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Section 1 & 2: Allergies & G6PD Tables -->
    <div class="grid-cols-2" style="margin-bottom: 22px;">
        <!-- Recent Allergies with Repeat Radar -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <span>🚨 ทะเบียนแพ้ยาล่าสุด & สแกนสั่งจ่ายซ้ำ (Repeat Radar)</span>
                </div>
                <a href="/pcc/safety/allergies" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px;">
                    ดูทั้งหมด (<?= (int)$analytics['allergy']['total'] ?>) ➔
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ผู้ป่วย</th>
                                <th>ยาที่แพ้</th>
                                <th>อาการแพ้</th>
                                <th>เรดาร์สั่งซ้ำ</th>
                                <th>บัตรแพ้ยา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($analytics['allergy']['recent'])): ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">ไม่พบข้อมูลแพ้ยา</td></tr>
                            <?php else: ?>
                                <?php foreach ($analytics['allergy']['recent'] as $a): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($a['fname'] . ' ' . $a['lname']) ?></div>
                                            <div style="font-size: 11px; color: #64748b;">PID: <?= (int)$a['pid'] ?></div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 700; color: #b91c1c;"><?= htmlspecialchars($a['drugname'] ?: $a['drugcode']) ?></span>
                                        </td>
                                        <td style="font-size: 11.5px; color: #475569;">
                                            <?= htmlspecialchars($a['allergicsymtomps'] ?: 'มีอาการแพ้ยา') ?>
                                        </td>
                                        <td>
                                            <?php if ((int)$a['repeat_prescribed'] > 0): ?>
                                                <span class="badge badge-danger" style="font-size: 10.5px;">🚨 เคยจ่ายซ้ำ!</span>
                                            <?php else: ?>
                                                <span class="badge badge-success" style="font-size: 10.5px;">ปลอดภัย</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/pcc/safety/allergies/card/<?= (int)$a['pid'] ?>" target="_blank" class="btn btn-secondary" style="font-size: 11px; padding: 3px 8px; color: #b91c1c;">
                                                🖨️ ออกบัตร สธ.
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- G6PD Patients & High Risk Med Alerts -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <span>🧬 ผู้ป่วยพร่องเอนไซม์ G6PD ในระบบ (G6PD Patients)</span>
                </div>
                <a href="/pcc/safety/g6pd" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px;">
                    ดูทะเบียน G6PD ➔
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ผู้ป่วย</th>
                                <th>ระดับความรุนแรง</th>
                                <th>ยาอันตรายที่ห้ามใช้</th>
                                <th>สถานะบัตร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($analytics['g6pd']['patients'])): ?>
                                <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 28px;">
                                    <div style="font-size: 24px; margin-bottom: 6px;">🧬</div>
                                    <div style="font-weight: 700; color: #1e293b;">ไม่พบประวัติผู้ป่วย G6PD ในฐานข้อมูล JHCIS</div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">ไม่มีการบันทึกรหัสวินิจฉัย D55.0 หรือประวัติใน JHCIS สด</div>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($analytics['g6pd']['patients'] as $g): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($g['patient_name']) ?></div>
                                            <div style="font-size: 11px; color: #64748b;">PID: <?= (int)$g['pid'] ?> (<?= htmlspecialchars($g['gender'] ?? '-') ?>)</div>
                                        </td>
                                        <td>
                                            <span class="badge <?= str_contains($g['who_class'], 'Class II') ? 'badge-danger' : 'badge-warning' ?>" style="font-size: 11px;">
                                                <?= htmlspecialchars($g['who_class']) ?>
                                            </span>
                                            <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                                                <?= htmlspecialchars($g['enzyme_activity']) ?>
                                            </div>
                                        </td>
                                        <td style="font-size: 11.5px; color: #991b1b; max-width: 180px;">
                                            <strong><?= htmlspecialchars($g['high_risk_drugs']) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($g['g6pd_card_status'] === 'issued'): ?>
                                                <span class="badge badge-success" style="font-size: 10.5px;">ออกบัตรแล้ว</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary" style="font-size: 10.5px;">รอดำเนินการ</span>
                                            <?php endif; ?>
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

    <!-- Section 3 & 4: Top NCD Diseases & Top VHV List -->
    <div class="grid-cols-2" id="ncd-report-section" style="margin-bottom: 22px;">
        <!-- Top NCD Diseases in Area -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <span>🫀 8 อันดับโรคไม่ติดต่อเรื้อรัง (NCDs) สูงสุดในพื้นที่ รพ.สต.</span>
                </div>
                <span class="badge badge-info">ฐานข้อมูล JHCIS</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส ICD-10</th>
                                <th>ชื่อโรค (วินิจฉัย)</th>
                                <th style="text-align: right;">จำนวนผู้ป่วย</th>
                                <th>สัดส่วน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $maxNcd = 337; 
                                foreach ($analytics['ncd']['top_diseases'] as $top): 
                                    $pct = round(($top['cnt'] / max(1, $analytics['ncd']['total'])) * 100, 1);
                                    $barWidth = round(($top['cnt'] / $maxNcd) * 100);
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary" style="font-family: monospace; font-weight: 700;">
                                            <?= htmlspecialchars($top['chroniccode']) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; color: #1e293b;">
                                        <?= htmlspecialchars($top['diseasenamethai'] ?: $top['chroniccode']) ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                        <?= number_format((int)$top['cnt']) ?> คน
                                    </td>
                                    <td style="width: 140px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                                <div style="width: <?= $barWidth ?>%; height: 100%; background: #3b82f6; border-radius: 3px;"></div>
                                            </div>
                                            <span style="font-size: 11px; color: #64748b; width: 35px;"><?= $pct ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top VHV List -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <span>👥 รายชื่อ อสม. และหลังคาเรือนในความรับผิดชอบ (Top 8)</span>
                </div>
                <a href="/pcc/vhv" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px;">
                    ดูทั้งหมด 25 ท่าน ➔
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อ-นามสกุล อสม.</th>
                                <th>หมู่บ้าน</th>
                                <th>เบอร์โทร</th>
                                <th style="text-align: center;">หลังคาเรือนที่ดูแล</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $topVhvs = array_slice($analytics['vhv']['list'] ?? [], 0, 8);
                                foreach ($topVhvs as $v):
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($v['fname'] . ' ' . $v['lname']) ?></div>
                                        <div style="font-size: 11px; color: #64748b;">PID: <?= (int)$v['pidvola'] ?></div>
                                    </td>
                                    <td>
                                        หมู่ <?= (int)$v['villno'] ?> <?= htmlspecialchars($v['villname'] ?: '-') ?>
                                    </td>
                                    <td style="font-size: 12px; color: #64748b;">
                                        <?= htmlspecialchars($v['telephoneperson'] ?: '-') ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success" style="font-size: 12px; font-weight: 700;">
                                            <?= (int)$v['house_count'] ?> หลัง
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Local Chart.js -->
<script src="/pcc/assets/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart NCDs (Doughnut)
    const ncdCtx = document.getElementById('ncdChart');
    if (ncdCtx && typeof Chart !== 'undefined') {
        const ncdLabels = <?= json_encode(array_column($analytics['ncd']['categories'] ?? [], 'ncd_category')) ?>;
        const ncdCounts = <?= json_encode(array_map('intval', array_column($analytics['ncd']['categories'] ?? [], 'patient_count'))) ?>;

        new Chart(ncdCtx, {
            type: 'doughnut',
            data: {
                labels: ncdLabels,
                datasets: [{
                    data: ncdCounts,
                    backgroundColor: [
                        '#3b82f6', // HT Blue
                        '#10b981', // DM Green
                        '#f59e0b', // Stroke Amber
                        '#8b5cf6', // CKD Purple
                        '#ec4899', // CAD Pink
                        '#06b6d4', // Asthma Cyan
                        '#94a3b8'  // Others Slate
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11.5, family: "'Prompt', sans-serif" },
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.raw;
                                const pct = Math.round((val / total) * 100);
                                return ` ${context.label}: ${val} คน (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '62%'
            }
        });
    }

    // 2. Chart Drug Allergies (Horizontal Bar)
    const allergyCtx = document.getElementById('allergyChart');
    if (allergyCtx && typeof Chart !== 'undefined') {
        const allergyLabels = <?= json_encode(array_column($analytics['allergy']['groups'] ?? [], 'drug_group')) ?>;
        const allergyCounts = <?= json_encode(array_map('intval', array_column($analytics['allergy']['groups'] ?? [], 'patient_count'))) ?>;

        new Chart(allergyCtx, {
            type: 'bar',
            data: {
                labels: allergyLabels,
                datasets: [{
                    label: 'จำนวนผู้ป่วยแพ้ยา (คน)',
                    data: allergyCounts,
                    backgroundColor: [
                        '#ef4444',
                        '#f97316',
                        '#06b6d4',
                        '#8b5cf6',
                        '#64748b'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 2 }
                    }
                }
            }
        });
    }

    // 3. Chart CKD Staging (Bar)
    const ckdCtx = document.getElementById('ckdChart');
    if (ckdCtx && typeof Chart !== 'undefined') {
        const ckdStages = <?= json_encode(array_keys($analytics['ckd']['stages'] ?? [])) ?>;
        const ckdValues = <?= json_encode(array_values($analytics['ckd']['stages'] ?? [])) ?>;

        new Chart(ckdCtx, {
            type: 'bar',
            data: {
                labels: ckdStages,
                datasets: [{
                    label: 'จำนวนผู้ป่วย (คน)',
                    data: ckdValues,
                    backgroundColor: [
                        '#10b981', // Stage 1 Green
                        '#84cc16', // Stage 2 Light green
                        '#f59e0b', // Stage 3a Amber
                        '#ea580c', // Stage 3b Orange
                        '#dc2626', // Stage 4 Red
                        '#7f1d1d'  // Stage 5 Dark Red
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 }
                    }
                }
            }
        });
    }

    // 4. Chart VHV Coverage (Top 8 VHV)
    const vhvCtx = document.getElementById('vhvChart');
    if (vhvCtx && typeof Chart !== 'undefined') {
        <?php 
            $topVhvChart = array_slice($analytics['vhv']['list'] ?? [], 0, 7);
            $vNames = array_map(fn($v) => $v['fname'] . ' (ม.' . $v['villno'] . ')', $topVhvChart);
            $vHouses = array_map(fn($v) => (int)$v['house_count'], $topVhvChart);
        ?>
        const vhvNames = <?= json_encode($vNames) ?>;
        const vhvHouses = <?= json_encode($vHouses) ?>;

        new Chart(vhvCtx, {
            type: 'bar',
            data: {
                labels: vhvNames,
                datasets: [{
                    label: 'หลังคาเรือนที่ดูแล (หลัง)',
                    data: vhvHouses,
                    backgroundColor: '#10b981',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 }
                    }
                }
            }
        });
    }
});
</script>
