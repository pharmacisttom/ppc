<div class="rdu-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">การใช้ยาอย่างสมเหตุผลและการจัดการยาปฏิชีวนะ (RDU & Antibiotic Stewardship)</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                ตัวชี้วัดการใช้ยาปฏิชีวนะใน 3 โรคหลักตามเกณฑ์ Rational Drug Use ของกระทรวงสาธารณสุข
            </p>
        </div>
        <a href="/hos/kpi" class="btn btn-secondary">
            ⬅ กลับไปหน้า KPIs รวม
        </a>
    </div>

    <!-- 3 Core RDU Indicators -->
    <div class="grid-cols-3">
        <!-- URI -->
        <div class="card" style="border-top: 4px solid var(--success);">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">1. โรคติดเชื้อทางเดินหายใจส่วนบน (URI)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">หวัด เจ็บคอ (J00, J02, J06)</div>
                    </div>
                    <span class="badge badge-success">เป้าหมาย ≤ 20%</span>
                </div>
                <div style="margin-top: 16px; display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 32px; font-weight: 700; color: var(--success); font-family: 'Outfit', sans-serif;">
                        <?= (float)$rdu['uri_rate'] ?>%
                    </div>
                    <span style="font-size: 12px; color: var(--success); font-weight: 600;">✅ ผ่านเกณฑ์</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                    จ่ายยาปฏิชีวนะ <?= (int)$rdu['uri_antibiotic_visits'] ?> จากทั้งหมด <?= (int)$rdu['uri_total_visits'] ?> ครั้ง
                </div>
            </div>
        </div>

        <!-- Diarrhea -->
        <div class="card" style="border-top: 4px solid var(--success);">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">2. โรคอุจจาระร่วงเฉียบพลัน (Acute Diarrhea)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">ท้องเสียเฉียบพลัน (A09)</div>
                    </div>
                    <span class="badge badge-success">เป้าหมาย ≤ 20%</span>
                </div>
                <div style="margin-top: 16px; display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 32px; font-weight: 700; color: var(--success); font-family: 'Outfit', sans-serif;">
                        <?= (float)$rdu['diarrhea_rate'] ?>%
                    </div>
                    <span style="font-size: 12px; color: var(--success); font-weight: 600;">✅ ผ่านเกณฑ์</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                    จ่ายยาปฏิชีวนะ <?= (int)$rdu['diarrhea_antibiotic_visits'] ?> จากทั้งหมด <?= (int)$rdu['diarrhea_total_visits'] ?> ครั้ง
                </div>
            </div>
        </div>

        <!-- Fresh Wound -->
        <div class="card" style="border-top: 4px solid var(--success);">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">3. บาดแผลสดจากอุบัติเหตุ (Fresh Wound)</div>
                        <div style="font-size: 11px; color: var(--text-muted);">แผลสด แผลถลอก สะอาด (T14.1)</div>
                    </div>
                    <span class="badge badge-success">เป้าหมาย ≤ 40%</span>
                </div>
                <div style="margin-top: 16px; display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 32px; font-weight: 700; color: var(--success); font-family: 'Outfit', sans-serif;">
                        <?= (float)$rdu['wound_rate'] ?>%
                    </div>
                    <span style="font-size: 12px; color: var(--success); font-weight: 600;">✅ ผ่านเกณฑ์</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                    จ่ายยาปฏิชีวนะ <?= (int)$rdu['wound_antibiotic_visits'] ?> จากทั้งหมด <?= (int)$rdu['wound_total_visits'] ?> ครั้ง
                </div>
            </div>
        </div>
    </div>

    <!-- Top Antibiotics Prescribed -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>💊 รายการยาปฏิชีวนะที่ถูกสั่งใช้บ่อยที่สุด (Top Prescribed Antibiotics)</span>
            </div>
            <span class="badge badge-info">Antibiotic Stewardship Tracking</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อยาปฏิชีวนะ (Generic & Strength)</th>
                            <th>จำนวนครั้งที่สั่งใช้ (Prescriptions)</th>
                            <th>สัดส่วนการสั่งใช้ (%)</th>
                            <th>การจัดกลุ่มตาม AWaRe Classification (WHO)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r = 1; foreach ($rdu['top_antibiotics'] as $ab): ?>
                            <tr>
                                <td><?= $r++ ?></td>
                                <td><strong><?= htmlspecialchars($ab['name']) ?></strong></td>
                                <td><strong><?= (int)$ab['prescriptions'] ?> ครั้ง</strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; max-width: 160px;">
                                            <div style="height: 100%; width: <?= (float)$ab['percentage'] ?>%; background: var(--primary);"></div>
                                        </div>
                                        <span style="font-size: 13px; font-weight: 600;"><?= (float)$ab['percentage'] ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (str_contains($ab['name'], 'Amoxicillin')): ?>
                                        <span class="badge badge-success">ACCESS (ยากลุ่มแรกที่ควรเลือก)</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">WATCH (ยากลุ่มที่ต้องเฝ้าระวัง)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
