<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัตรแพ้ยา — <?= htmlspecialchars($patient['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .toolbar {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn-print {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #b91c1c;
        }
        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        /* Standard ISO/IEC 7810 ID-1 Card Size (85.6mm × 53.98mm) scaled for High Quality Print */
        .cards-wrapper {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .allergy-card {
            width: 95mm;
            min-height: 58mm;
            background: #ffffff;
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 10px 14px;
            position: relative;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Top Banner */
        .card-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff;
            margin: -10px -14px 8px -14px;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #b91c1c;
        }
        .card-logo-area {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-symbol {
            width: 26px;
            height: 26px;
            background: #ffffff;
            color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 16px;
        }
        .card-title-th {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .card-title-en {
            font-size: 9px;
            font-weight: 700;
            color: #fecaca;
            letter-spacing: 0.8px;
        }
        .card-no-badge {
            font-family: 'Outfit', sans-serif;
            font-size: 10px;
            font-weight: 700;
            background: rgba(0,0,0,0.25);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        /* Patient Details */
        .patient-box {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 4px 10px;
            font-size: 11px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 6px;
        }
        .p-label {
            color: #64748b;
            font-weight: 600;
        }
        .p-val {
            font-weight: 700;
            color: #0f172a;
        }
        /* Allergic Drug Highlight */
        .drugs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 6px;
        }
        .drugs-table th {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 6px;
            text-align: left;
            font-weight: 700;
            border: 1px solid #fecaca;
        }
        .drugs-table td {
            padding: 4px 6px;
            border: 1px solid #fecaca;
            vertical-align: top;
        }
        .drug-alert-name {
            font-size: 11px;
            font-weight: 800;
            color: #b91c1c;
        }
        .drug-reaction {
            color: #7f1d1d;
            font-weight: 600;
        }
        /* Card Footer */
        .card-footer {
            font-size: 8px;
            color: #475569;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .notice-th {
            font-weight: 700;
            color: #dc2626;
        }
        .notice-en {
            color: #64748b;
        }
        /* Back Card */
        .allergy-card.back-card {
            border-color: #475569;
        }
        .back-header {
            background: #334155;
            color: #ffffff;
            margin: -10px -14px 8px -14px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
        }
        .signatures-area {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            text-align: center;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .toolbar {
                display: none;
            }
            .cards-wrapper {
                flex-direction: row;
                gap: 15mm;
            }
            .allergy-card {
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">
            <span>🖨️</span> พิมพ์บัตรแพ้ยามาตรฐาน (Print Card)
        </button>
        <a href="/pcc/safety/allergies" class="btn-back">
            ย้อนกลับสู่ทะเบียนแพ้ยา
        </a>
    </div>

    <div class="cards-wrapper">
        <!-- FRONT OF CARD -->
        <div class="allergy-card">
            <div>
                <div class="card-header">
                    <div class="card-logo-area">
                        <div class="card-symbol">!</div>
                        <div>
                            <div class="card-title-th">บัตรแพ้ยา (DRUG ALLERGY CARD)</div>
                            <div class="card-title-en">MINISTRY OF PUBLIC HEALTH, THAILAND</div>
                        </div>
                    </div>
                    <div class="card-no-badge">
                        <?= htmlspecialchars($cardNo) ?>
                    </div>
                </div>

                <div class="patient-box">
                    <span class="p-label">ชื่อ-นามสกุล:</span>
                    <span class="p-val" style="font-size: 12.5px;"><?= htmlspecialchars($patient['full_name']) ?></span>

                    <span class="p-label">เลข ปชช.:</span>
                    <span class="p-val" style="font-family: monospace; letter-spacing: 0.5px;"><?= htmlspecialchars($patient['cid'] ?: $patient['cid_masked']) ?></span>

                    <span class="p-label">HN / PID:</span>
                    <span class="p-val"><?= (int)$patient['pid'] ?> (อายุ <?= (int)$patient['age'] ?> ปี / เลือด <?= htmlspecialchars($patient['blood_group'] ?: 'ไม่ระบุ') ?>)</span>
                </div>

                <table class="drugs-table">
                    <thead>
                        <tr>
                            <th>ชื่อยาที่แพ้ (Allergic Drug)</th>
                            <th>อาการแพ้ (Reaction)</th>
                            <th style="width: 70px; text-align: center;">ความรุนแรง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allergies)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #94a3b8; padding: 10px;">ไม่พบรายการแพ้ยา</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allergies as $alg): ?>
                                <tr>
                                    <td>
                                        <div class="drug-alert-name"><?= htmlspecialchars($alg['drug_name'] ?? $alg['drugcode'] ?? '') ?></div>
                                        <div style="font-size: 8px; color: #64748b;">รหัสยา: <?= htmlspecialchars($alg['drug_code'] ?? $alg['drugcode'] ?? '') ?></div>
                                    </td>
                                    <td class="drug-reaction">
                                        <?= htmlspecialchars($alg['reaction'] ?? $alg['symptom'] ?? 'มีอาการแพ้') ?>
                                    </td>
                                    <td style="text-align: center; font-size: 8.5px; font-weight: 700; color: #dc2626;">
                                        <?= htmlspecialchars($alg['severity'] ?? 'ระดับ 2') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div>
                    <div class="notice-th">⚠️ โปรดแสดงบัตรนี้แก่แพทย์หรือเภสัชกรทุกครั้งก่อนรับยา</div>
                    <div class="notice-en">Always present this card to physicians or pharmacists before taking any medications.</div>
                </div>
                <div style="text-align: right; white-space: nowrap;">
                    <div><strong>รพ.สต.บ้านดอกกราย (01996)</strong></div>
                    <div>CUP รพ.ปลวกแดง จ.ระยอง</div>
                </div>
            </div>
        </div>

        <!-- BACK OF CARD -->
        <div class="allergy-card back-card">
            <div>
                <div class="back-header">
                    <span>ข้อปฏิบัติและคำเตือนสำคัญสำหรับผู้ถือบัตร</span>
                    <span>ออกบัตร: <?= date('d/m/Y') ?></span>
                </div>

                <div style="font-size: 9.5px; line-height: 1.45; color: #334155; margin-bottom: 8px;">
                    <p style="margin-bottom: 4px;">1. ผู้ป่วยต้องพกบัตรแพ้ยานี้ติดตัวตลอดเวลาในกระเป๋าสตางค์หรือโทรศัพท์มือถือ</p>
                    <p style="margin-bottom: 4px;">2. ห้ามรับประทานยาที่ระบุในบัตรนี้ และยาในกลุ่มโครงสร้างเดียวกันโดยเด็ดขาด</p>
                    <p style="margin-bottom: 4px;">3. หากมีอาการผิดปกติ เช่น ผื่นคัน แน่นหน้าอก หายใจไม่ออก หนังตาบวม ให้หยุดยาทันทีและรีบพบแพทย์</p>
                    <p>4. บัตรนี้ออกโดยระบบเภสัชกรรมปฐมภูมิ เชื่อมโยงฐานข้อมูล JHCIS เครือข่าย รพ.แม่ข่าย (CUP)</p>
                </div>
            </div>

            <div>
                <div class="signatures-area">
                    <div style="width: 48%;">
                        <div style="height: 18px; border-bottom: 1px dotted #94a3b8; margin-bottom: 3px;"></div>
                        <div>ลงชื่อผู้ป่วย / ญาติ</div>
                    </div>
                    <div style="width: 48%;">
                        <div style="height: 18px; border-bottom: 1px dotted #94a3b8; margin-bottom: 3px;">
                            <span style="font-size: 9px; font-weight: 700; color: #0d9488;"><?= htmlspecialchars($user['firstname'] ?? 'ภญ.กานดา') ?></span>
                        </div>
                        <div>เภสัชกร / แพทย์ผู้ประเมินและออกบัตร</div>
                    </div>
                </div>

                <div style="margin-top: 6px; padding-top: 4px; border-top: 1px solid #cbd5e1; font-size: 8px; color: #64748b; display: flex; justify-content: space-between;">
                    <span>หน่วยบริการ: รพ.สต.บ้านดอกกราย อ.ปลวกแดง จ.ระยอง โทร. 038-xxx-xxx</span>
                    <span>รหัสบัตร: <?= htmlspecialchars($cardNo) ?></span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
