<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ฉลากซองยา — <?= htmlspecialchars($detail['patient']['full_name'] ?? 'ผู้รับบริการ') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #4a154b;
            color: #fff;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .stickers-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        /* Standard 8.0cm x 5.0cm Thermal / Drug Envelope Sticker */
        .sticker-card {
            width: 80mm;
            min-height: 50mm;
            background: #fff;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 10px 12px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sticker-header {
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .sticker-hosp {
            font-size: 11px;
            font-weight: 700;
        }
        .sticker-date {
            font-size: 10px;
        }
        .patient-row {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }
        .drug-name {
            font-size: 14px;
            font-weight: 700;
            color: #000;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        .drug-instruction {
            font-size: 12.5px;
            line-height: 1.35;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 4px;
        }
        .sticker-footer {
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 9.5px;
            display: flex;
            justify-content: space-between;
            color: #334155;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .sticker-card {
                border: 1px solid #ccc;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ พิมพ์ฉลากยา (Print All Stickers)</button>
        <button class="btn-print" style="background: #64748b; margin-left: 10px;" onclick="window.close()">ปิดหน้านี้</button>
    </div>

    <div class="stickers-container">
        <?php foreach ($detail['medications'] as $med): ?>
            <div class="sticker-card">
                <div>
                    <div class="sticker-header">
                        <div class="sticker-hosp">
                            🏥 <?= htmlspecialchars($user['facility_name'] ?? 'รพ.สต.บ้านดอกกราย') ?>
                        </div>
                        <div class="sticker-date">
                            <?= date('d/m/Y') ?>
                        </div>
                    </div>
                    <div class="patient-row">
                        <span><?= htmlspecialchars($detail['patient']['full_name']) ?></span>
                        <span>PID: <?= (int)$detail['patient']['pid'] ?></span>
                    </div>
                    <div class="drug-name">
                        💊 <?= htmlspecialchars($med['drug_name']) ?>
                    </div>
                    <div class="drug-instruction">
                        <strong>วิธีใช้:</strong> <?= htmlspecialchars($med['dose']) ?>
                    </div>
                </div>
                <div class="sticker-footer">
                    <span>จำนวน: <strong><?= (float)$med['unit'] ?></strong> หน่วย</span>
                    <span>ผู้จ่าย: <?= htmlspecialchars($user['firstname'] ?? 'เภสัชกร') ?></span>
                    <span>ยาใช้เฉพาะบุคคล</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
