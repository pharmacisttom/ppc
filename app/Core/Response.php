<?php
namespace App\Core;

/**
 * HTTP Response Helper
 */
class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: {$url}");
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json([
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Download CSV with UTF-8 BOM for perfect Thai font support in Excel
     */
    public static function downloadCsv(string $filename, array $headers, array $rows): void
    {
        // Clean any buffered output
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        foreach ($rows as $row) {
            fputcsv($output, array_values($row));
        }

        fclose($output);
        exit;
    }

    /**
     * Download Microsoft Excel (.xls) with Rich Styling and Thai Font Support
     */
    public static function downloadExcel(string $filename, string $title, array $headers, array $rows, array $meta = []): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        ?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name><?= htmlspecialchars(mb_substr($title, 0, 31)) ?></x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        body { font-family: 'TH Sarabun New', 'Sarabun', Tahoma, sans-serif; font-size: 14pt; }
        .report-header { font-size: 18pt; font-weight: bold; text-align: center; color: #0f766e; height: 40px; }
        .report-subheader { font-size: 13pt; text-align: center; color: #475569; height: 25px; }
        .meta-table { margin-bottom: 15px; font-size: 12pt; }
        .meta-label { font-weight: bold; color: #334155; }
        table.data-table { border-collapse: collapse; width: 100%; }
        table.data-table th { background-color: #0f766e; color: #ffffff; font-weight: bold; border: 1px solid #0d9488; padding: 10px 8px; text-align: center; font-size: 13pt; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px 10px; font-size: 12pt; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-ham { background-color: #fee2e2; color: #991b1b; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .badge-lasa { background-color: #fef3c7; color: #92400e; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .badge-anti { background-color: #f3e8ff; color: #6b21a8; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .badge-cold { background-color: #e0f2fe; color: #075985; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .badge-safe { background-color: #dcfce7; color: #166534; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .num-format { mso-number-format:"\#\,\#\#0\.00"; }
        .text-format { mso-number-format:"\@"; }
    </style>
</head>
<body>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="<?= max(count($headers), 5) ?>" class="report-header">
                <?= htmlspecialchars($title) ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= max(count($headers), 5) ?>" class="report-subheader">
                ระบบบริหารจัดการด้านยาและเภสัชกรรมปฐมภูมิ (PCU Smart Pharmacy) — ตามมาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570
            </td>
        </tr>
        <?php if (!empty($meta)): ?>
            <?php foreach ($meta as $k => $v): ?>
                <tr>
                    <td colspan="2" class="meta-label"><?= htmlspecialchars($k) ?>:</td>
                    <td colspan="<?= max(count($headers) - 2, 3) ?>"><?= htmlspecialchars((string)$v) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr>
            <td colspan="<?= max(count($headers), 5) ?>" style="height: 15px;"></td>
        </tr>
    </table>

    <table class="data-table" border="1">
        <thead>
            <tr>
                <?php foreach ($headers as $h): ?>
                    <th><?= htmlspecialchars($h) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="<?= count($headers) ?>" class="text-center" style="padding: 20px; color: #64748b;">
                        ไม่พบข้อมูลตามเงื่อนไขที่ระบุ
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $idx => $row): ?>
                    <tr style="background-color: <?= $idx % 2 === 0 ? '#ffffff' : '#f8fafc' ?>;">
                        <?php foreach ($row as $cell): ?>
                            <?php
                            $val = (string)($cell ?? '-');
                            $align = '';
                            $cls = '';
                            if (is_numeric($cell) && !str_starts_with((string)$cell, '0') && strlen((string)$cell) <= 10) {
                                $align = 'class="text-right num-format"';
                            } elseif (is_numeric($cell) || preg_match('/^\d{2,24}$/', (string)$cell)) {
                                $align = 'class="text-center text-format"';
                            } elseif (str_contains($val, 'HAM')) {
                                $cls = 'badge-ham';
                            } elseif (str_contains($val, 'LASA')) {
                                $cls = 'badge-lasa';
                            } elseif (str_contains($val, 'ปฏิชีวนะ')) {
                                $cls = 'badge-anti';
                            } elseif (str_contains($val, 'Cold Chain')) {
                                $cls = 'badge-cold';
                            }
                            ?>
                            <td <?= $align ?>><span class="<?= $cls ?>"><?= htmlspecialchars($val) ?></span></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
        <?php
        exit;
    }
}

