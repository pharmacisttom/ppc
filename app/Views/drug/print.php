<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'พิมพ์บัญชียา รพ.สต.') ?></title>
    <style>
        body {
            font-family: 'TH Sarabun New', 'Sarabun', Tahoma, sans-serif;
            font-size: 14pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 20mm 15mm;
        }

        .header-section {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
        }

        .garuda-logo {
            font-size: 28pt;
            margin-bottom: 4px;
        }

        .title {
            font-size: 18pt;
            font-weight: bold;
            margin: 2px 0;
        }

        .subtitle {
            font-size: 14pt;
            margin: 2px 0;
        }

        .meta-bar {
            display: flex;
            justify-content: space-between;
            font-size: 12pt;
            margin-top: 10px;
        }

        table.print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11pt;
        }

        table.print-table th, table.print-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.print-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .tag-ham { font-weight: bold; color: #b91c1c; }
        .tag-lasa { font-weight: bold; color: #b45309; }
        .tag-anti { font-weight: bold; color: #6b21a8; }
        .tag-cold { font-weight: bold; color: #0284c7; }

        .signature-section {
            margin-top: 35px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            text-align: center;
            page-break-inside: avoid;
        }

        .signature-box {
            padding: 10px;
        }

        .signature-line {
            margin-top: 50px;
            border-bottom: 1px dotted #333;
            width: 70%;
            display: inline-block;
        }

        @media print {
            body { margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>คำแนะนำการพิมพ์:</strong> เอกสารฉบับนี้จัดรูปแบบสำหรับพิมพ์ลงกระดาษ A4 หรือบันทึกเป็น PDF เพื่อแนบเป็นหลักฐานการประเมินคุณภาพ
        </div>
        <button onclick="window.print()" style="padding: 8px 18px; font-size: 14px; background: #0d9488; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            🖨️ สั่งพิมพ์เอกสาร (Print / PDF)
        </button>
    </div>

    <div class="header-section">
        <div class="garuda-logo">🏥</div>
        <div class="title">บัญชีรายการยาประจำโรงพยาบาลส่งเสริมสุขภาพตำบล (PCU Drug Formulary)</div>
        <div class="subtitle"><?= htmlspecialchars($currentUser['facility_name'] ?? 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านหนองบัว') ?> (รหัส: <?= htmlspecialchars($currentUser['facility_code'] ?? '05432') ?>)</div>
        <div class="subtitle">ตามมาตรฐานระบบบริการปฐมภูมิ พ.ศ. 2568–2570 และเกณฑ์ RDU Community</div>
        <div class="meta-bar">
            <span><strong>หมวดหมู่:</strong> <?= htmlspecialchars($categoryTitle) ?></span>
            <span><strong>จำนวนรายการ:</strong> <?= count($drugs) ?> รายการ</span>
            <span><strong>วันที่จัดพิมพ์:</strong> <?= date('d/m/Y') ?></span>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 35px;">ลำดับ</th>
                <th style="width: 80px;">รหัสยา</th>
                <th>ชื่อการค้าและขนาด (Trade Name) / ชื่อสามัญ (Generic)</th>
                <th style="width: 130px;">ชื่อภาษาไทย</th>
                <th style="width: 60px;">หน่วยนับ</th>
                <th style="width: 70px;">บัญชียา</th>
                <th style="width: 140px;">มาตรฐานความปลอดภัย</th>
                <th style="width: 65px;">ราคาเบิก</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($drugs)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">ไม่พบรายการยาในหมวดหมู่นี้</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($drugs as $d): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td class="text-center" style="font-family: monospace; font-size: 10pt;"><?= htmlspecialchars($d['drugcode']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($d['drugname']) ?></strong>
                            <?php if (!empty($d['druggenericname'])): ?>
                                <div style="font-size: 9.5pt; color: #444; font-style: italic;"><?= htmlspecialchars($d['druggenericname']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['drugnamethai'] ?: '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($d['unitsell']) ?></td>
                        <td class="text-center"><?= $d['is_nlem'] ? 'ในบัญชี' : 'นอกบัญชี' ?></td>
                        <td>
                            <?php
                            $tags = [];
                            if ($d['is_ham']) $tags[] = '<span class="tag-ham">HAM (เสี่ยงสูง)</span>';
                            if ($d['is_lasa']) $tags[] = '<span class="tag-lasa">LASA</span>';
                            if ($d['is_antibiotic']) $tags[] = '<span class="tag-anti">ยาปฏิชีวนะ</span>';
                            if ($d['is_cold_chain']) $tags[] = '<span class="tag-cold">Cold Chain (2-8°C)</span>';
                            if ($d['is_emergency']) $tags[] = '<strong>CPR Kit</strong>';
                            if ($d['is_herbal']) $tags[] = 'ยาสมุนไพร';
                            echo !empty($tags) ? implode(', ', $tags) : '-';
                            ?>
                        </td>
                        <td class="text-right">฿<?= number_format($d['sell'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div>ผู้จัดทำ / เภสัชกรผู้รับผิดชอบ</div>
            <div class="signature-line"></div>
            <div style="margin-top: 8px;">( ............................................................ )</div>
            <div style="font-size: 11pt; color: #444; margin-top: 2px;">เภสัชกรปฐมภูมิ / ผู้รับผิดชอบระบบยา</div>
            <div style="font-size: 10pt; color: #666;">วันที่ ...... / ...... / ............</div>
        </div>

        <div class="signature-box">
            <div>ผู้อนุมัติบัญชียา / ผู้อำนวยการ รพ.สต.</div>
            <div class="signature-line"></div>
            <div style="margin-top: 8px;">( ............................................................ )</div>
            <div style="font-size: 11pt; color: #444; margin-top: 2px;">ผู้อำนวยการโรงพยาบาลส่งเสริมสุขภาพตำบล</div>
            <div style="font-size: 10pt; color: #666;">วันที่ ...... / ...... / ............</div>
        </div>
    </div>
</body>
</html>
