<div class="safety-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 24px;">🧬</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    ทะเบียนผู้ป่วยภาวะพร่องเอนไซม์ G6PD (G6PD Deficiency Registry)
                </h1>
                <span class="badge badge-danger" style="font-size: 12px;">ห้ามใช้ยาเสี่ยงสูง</span>
            </div>
            <p style="font-size: 13.5px; color: #64748b; margin: 0;">
                ระบบเฝ้าระวังผู้ป่วยขาดเอนไซม์ Glucose-6-Phosphate Dehydrogenase เพื่อป้องกันภาวะเม็ดเลือดแดงแตกเฉียบพลัน (Acute Hemolytic Anemia)
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/pcc/dashboard" class="btn btn-secondary" style="font-size: 13px;">
                <span>📊</span> แดชบอร์ดสถิติ
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()" style="font-size: 13px;">
                <span>🖨️</span> พิมพ์ทะเบียน
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid-cols-4" style="margin-bottom: 22px;">
        <div class="card" style="padding: 16px; border-left: 4px solid #ef4444;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">ผู้ป่วย G6PD ในระบบ</div>
            <div style="font-size: 26px; font-weight: 800; color: #b91c1c; margin-top: 4px;">
                <?= (int)($stats['total'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div style="font-size: 11.5px; color: #991b1b; margin-top: 4px;">
                มีประวัติความเสี่ยงในพื้นที่ รพ.สต.
            </div>
        </div>

        <div class="card" style="padding: 16px; border-left: 4px solid #dc2626;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">รุนแรงสูง (WHO Class II)</div>
            <div style="font-size: 26px; font-weight: 800; color: #dc2626; margin-top: 4px;">
                <?= (int)($stats['severe_class2'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                ระดับเอนไซม์ < 10% เม็ดเลือดแดงแตกง่าย
            </div>
        </div>

        <div class="card" style="padding: 16px; border-left: 4px solid #10b981;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">ออกบัตรประจำตัว G6PD แล้ว</div>
            <div style="font-size: 26px; font-weight: 800; color: #047857; margin-top: 4px;">
                <?= (int)($stats['card_issued'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">คน</span>
            </div>
            <div style="font-size: 11.5px; color: #059669; margin-top: 4px;">
                พกบัตรเตือนแพทย์/เภสัชกร
            </div>
        </div>

        <div class="card" style="padding: 16px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">ยาเสี่ยงสูงห้ามจ่ายเด็ดขาด</div>
            <div style="font-size: 18px; font-weight: 800; color: #d97706; margin-top: 4px;">
                6 กลุ่มยาหลัก
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                Cotrimoxazole, Dapsone, Primaquine ฯลฯ
            </div>
        </div>
    </div>

    <!-- Clinical Warning Banner for High Risk Meds in G6PD -->
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #991b1b; font-size: 14px;">
            <span>⚠️</span>
            <span>ข้อห้ามใช้ยาเด็ดขาดในผู้ป่วย G6PD (High-Risk Drug Contraindications)</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-top: 10px; font-size: 12.5px; color: #7f1d1d;">
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>1. Cotrimoxazole (Bactrim)</strong>: ยาฆ่าเชื้อทางเดินปัสสาวะ/ทางเดินหายใจ
            </div>
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>2. Dapsone</strong>: ยารักษาโรคเรื้อนและโรคผิวหนัง
            </div>
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>3. Primaquine</strong>: ยารักษาและป้องกันมาลาเรีย
            </div>
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>4. Nitrofurantoin</strong>: ยาปฏิชีวนะกระเพาะปัสสาวะอักเสบ
            </div>
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>5. Methylene Blue / Rasburicase</strong>: ยาแก้พิษและยาลดกรดยูริกเฉียบพลัน
            </div>
            <div style="background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;">
                <strong>6. อื่นๆ</strong>: ถั่วปากอ้า (Fava beans), ลูกเหม็น (Naphthalene)
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="card" style="padding: 14px 18px; margin-bottom: 20px;">
        <form action="/pcc/safety/g6pd" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาชื่อผู้ป่วย, เลขบัตร ปชช. 13 หลัก, PID หรือกลุ่มยาเสี่ยง..." class="form-control" style="flex: 1;">
            <button type="submit" class="btn btn-primary">
                🔍 ค้นหา
            </button>
            <?php if (!empty($query)): ?>
                <a href="/pcc/safety/g6pd" class="btn btn-secondary">ล้างการค้นหา</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Patients Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>📋 รายชื่อผู้ป่วยในทะเบียน G6PD (พบ <?= count($patients) ?> ราย)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>PID</th>
                            <th>ชื่อ-นามสกุล / เลขบัตร ปชช.</th>
                            <th>อายุ/เพศ</th>
                            <th>ที่อยู่ (JHCIS)</th>
                            <th>ระดับความรุนแรง (WHO Class)</th>
                            <th>ประวัติเม็ดเลือดแดงแตก</th>
                            <th>รายการยาที่ต้องระวัง</th>
                            <th>บัตรประจำตัว</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🧬</div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 14px;">ไม่พบประวัติผู้ป่วยภาวะพร่องเอนไซม์ G6PD ในฐานข้อมูล JHCIS (รหัสวินิจฉัย D55.0)</div>
                                    <div style="font-size: 12.5px; color: #64748b; margin-top: 4px;">หน่วยบริการยังไม่มีการบันทึกผู้ป่วย G6PD ในระบบ JHCIS สด หากตรวจพบในคลินิกสามารถลงบันทึกหรือประสาน รพ.แม่ข่ายเพื่อบันทึกประวัติ</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td>
                                        <a href="/pcc/patients/<?= (int)$p['pid'] ?>" style="font-weight: 700; color: #4a154b;">
                                            #<?= (int)$p['pid'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($p['patient_name']) ?></div>
                                        <div style="font-size: 11.5px; color: #64748b;"><?= htmlspecialchars($p['idcard']) ?></div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['age']) ?> ปี (<?= htmlspecialchars($p['gender']) ?>)
                                    </td>
                                    <td style="font-size: 12px; color: #475569;">
                                        <?= htmlspecialchars($p['address']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= str_contains($p['who_class'], 'Class II') ? 'badge-danger' : 'badge-warning' ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($p['who_class']) ?>
                                        </span>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            <?= htmlspecialchars($p['enzyme_activity']) ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; color: #b91c1c; max-width: 200px;">
                                        <?= htmlspecialchars($p['hemolysis_history'] ?: '-') ?>
                                    </td>
                                    <td style="font-size: 11.5px; color: #991b1b; max-width: 220px;">
                                        <strong><?= htmlspecialchars($p['high_risk_drugs']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($p['g6pd_card_status'] === 'issued'): ?>
                                            <span class="badge badge-success" style="font-size: 11px;">ออกบัตรแล้ว</span>
                                            <div style="font-size: 10.5px; color: #64748b;"><?= htmlspecialchars($p['g6pd_card_no'] ?? '') ?></div>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 11px;">รอดำเนินการ</span>
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
