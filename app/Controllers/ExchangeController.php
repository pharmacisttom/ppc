<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Gateway\JhcisGateway;
use ZipArchive;

class ExchangeController
{
    /**
     * MOPH 43 Files & CUP Hospital Data Exchange Center
     */
    public function index(): void
    {
        $fileTypes = ['DRUG_OPD', 'DRUG_ALLERGY', 'CHRONIC', 'LABFU', 'PERSON'];
        $stats = [];

        foreach ($fileTypes as $ft) {
            $data = JhcisGateway::getMoph43Data($ft, 1000);
            $stats[$ft] = [
                'count' => count($data),
                'sample' => $data[0] ?? null
            ];
        }

        View::render('exchange/index', [
            'pageTitle' => 'ระบบไฟล์มาตรฐานแลกเปลี่ยนข้อมูล รพ.แม่ข่าย (MOPH 43 Files & CUP Exchange)',
            'stats' => $stats,
            'hospCode' => '01996',
            'hospName' => 'รพ.สต.บ้านดอกกราย เครือข่าย CUP รพ.ปลวกแดง'
        ]);
    }

    /**
     * Download Individual Standard MOPH Text File (.txt pipe-delimited)
     */
    public function downloadFile(string $type): void
    {
        $ft = strtoupper($type);
        $data = JhcisGateway::getMoph43Data($ft, 2000);

        Audit::log('MOPH43_EXPORT', 'exchange', $ft, null, null, null, "Exported MOPH file {$ft}.txt");

        $filename = "{$ft}_01996_" . date('Ymd_His') . ".txt";

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // UTF-8 BOM
        echo "\xEF\xBB\xBF";

        if (!empty($data)) {
            // Header line
            echo implode('|', array_keys($data[0])) . "\r\n";
            foreach ($data as $row) {
                echo implode('|', array_values($row)) . "\r\n";
            }
        }
        exit;
    }

    /**
     * One-Click Complete 43 Files ZIP Package Export
     */
    public function downloadZip(): void
    {
        $fileTypes = ['DRUG_OPD', 'DRUG_ALLERGY', 'CHRONIC', 'LABFU', 'PERSON'];
        $zipFilename = "MOPH43_01996_" . date('Ymd_His') . ".zip";
        $tempZipPath = sys_get_temp_dir() . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Session::flash('error', 'ไม่สามารถสร้างไฟล์ ZIP ได้');
            Response::redirect('/pcc/exchange');
            return;
        }

        foreach ($fileTypes as $ft) {
            $data = JhcisGateway::getMoph43Data($ft, 1000);
            $content = "\xEF\xBB\xBF";
            if (!empty($data)) {
                $content .= implode('|', array_keys($data[0])) . "\r\n";
                foreach ($data as $row) {
                    $content .= implode('|', array_values($row)) . "\r\n";
                }
            }
            $zip->addFromString("{$ft}.txt", $content);
        }

        // Add Readme Metadata
        $meta = "MOPH 43 Files Data Exchange Package\r\n";
        $meta .= "Facility: 01996 รพ.สต.บ้านดอกกราย\r\n";
        $meta .= "CUP Hospital: 10832 รพ.ปลวกแดง จ.ระยอง\r\n";
        $meta .= "Export Date: " . date('Y-m-d H:i:s') . "\r\n";
        $meta .= "Standard: Bureau of Policy and Strategy (สนย./กสธ. 43 แฟ้ม)\r\n";
        $zip->addFromString("METADATA.txt", $meta);

        $zip->close();

        Audit::log('MOPH43_ZIP_EXPORT', 'exchange', null, null, null, null, "Exported complete MOPH 43 files ZIP package");

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($tempZipPath));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempZipPath);
        @unlink($tempZipPath);
        exit;
    }

    /**
     * Export Formatted CUP Hospital Excel for Pharmacy Team
     */
    public function exportCupExcel(): void
    {
        $allergies = JhcisGateway::getAllergyRegistryList();
        $warfarin = JhcisGateway::getWarfarinRegistryList();
        $anticonv = JhcisGateway::getAnticonvulsantRegistryList();
        $ckd = JhcisGateway::getCkdRegistryList();

        Audit::log('CUP_EXCEL_EXPORT', 'exchange', null, null, null, null, "Exported CUP clinical registries Excel");

        $filename = "CUP_CLINICAL_SAFETY_01996_" . date('Ymd') . ".xls";

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        ?>
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                th { background-color: #4a154b; color: #ffffff; font-family: Sarabun, sans-serif; font-size: 13px; font-weight: bold; border: 1px solid #000; text-align: center; }
                td { font-family: Sarabun, sans-serif; font-size: 12px; border: 1px solid #ccc; vertical-align: middle; }
                .title { font-size: 16px; font-weight: bold; color: #4a154b; }
            </style>
        </head>
        <body>
            <div class="title">รายงานข้อมูลผู้ป่วยกลุ่มเสี่ยงด้านยา รพ.สต.บ้านดอกกราย (01996) — เครือข่าย CUP รพ.ปลวกแดง</div>
            <div>ส่งออกเมื่อ: <?= date('d/m/Y H:i:s') ?> | มาตรฐานแฟ้มข้อมูลกระทรวงสาธารณสุข</div>
            <br/>

            <h3>1. ทะเบียนผู้ป่วยแพ้ยา (Drug Allergy Registry)</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>PID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>รหัสยา</th>
                        <th>ชื่อยาที่แพ้</th>
                        <th>อาการแพ้</th>
                        <th>ระดับความรุนแรง</th>
                        <th>วันที่บันทึก</th>
                        <th>สถานพยาบาลที่รายงาน</th>
                        <th>ตรวจพบการสั่งจ่ายซ้ำ (Repeat Radar)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allergies as $i => $a): ?>
                        <tr>
                            <td align="center"><?= $i + 1 ?></td>
                            <td align="center"><?= $a['pid'] ?></td>
                            <td><?= htmlspecialchars($a['full_name']) ?></td>
                            <td align="center"><?= htmlspecialchars($a['drug_code']) ?></td>
                            <td><strong><?= htmlspecialchars($a['drug_name']) ?></strong></td>
                            <td><?= htmlspecialchars($a['reaction']) ?></td>
                            <td align="center"><?= htmlspecialchars($a['severity']) ?></td>
                            <td align="center"><?= htmlspecialchars($a['date_recorded']) ?></td>
                            <td><?= htmlspecialchars($a['informant_hosp']) ?></td>
                            <td align="center" style="<?= $a['is_repeat_prescribed'] ? 'background:#fee2e2;color:#991b1b;font-weight:bold;' : 'color:#059669;' ?>">
                                <?= $a['is_repeat_prescribed'] ? '⚠️ สั่งจ่ายซ้ำ ' . $a['repeat_count'] . ' ครั้ง' : 'ปลอดภัย (0 ครั้ง)' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br/>

            <h3>2. ทะเบียนผู้ป่วยรับยา Warfarin</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>PID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ข้อบ่งใช้ (Indication)</th>
                        <th>Target INR</th>
                        <th>ขนาดยา/สัปดาห์ (mg)</th>
                        <th>INR ล่าสุด</th>
                        <th>วันที่ตรวจ</th>
                        <th>สถานะ INR</th>
                        <th>ความเสี่ยงเลือดออก (HAS-BLED)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warfarin as $i => $w): ?>
                        <tr>
                            <td align="center"><?= $i + 1 ?></td>
                            <td align="center"><?= $w['pid'] ?></td>
                            <td><?= htmlspecialchars($w['patient']['full_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($w['indication']) ?></td>
                            <td align="center"><?= $w['target_text'] ?></td>
                            <td align="right"><?= $w['weekly_dose'] ?></td>
                            <td align="center"><strong><?= $w['latest_inr'] ?></strong></td>
                            <td align="center"><?= $w['latest_inr_date'] ?></td>
                            <td align="center"><?= strtoupper($w['inr_status']) ?></td>
                            <td><?= htmlspecialchars($w['bleeding_risk']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br/>

            <h3>3. ทะเบียนผู้ป่วยรับยากันชัก (Anticonvulsants)</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>PID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ชื่อยา</th>
                        <th>ขนาดรับประทาน</th>
                        <th>ข้อบ่งใช้</th>
                        <th>การควบคุมอาการชัก</th>
                        <th>ผลตรวจยีน HLA-B*1502</th>
                        <th>ระดับยาในเลือด (TDM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anticonv as $i => $ac): ?>
                        <tr>
                            <td align="center"><?= $i + 1 ?></td>
                            <td align="center"><?= $ac['pid'] ?></td>
                            <td><?= htmlspecialchars($ac['patient']['full_name'] ?? '') ?></td>
                            <td><strong><?= htmlspecialchars($ac['drug_name']) ?></strong></td>
                            <td><?= htmlspecialchars($ac['daily_dose']) ?></td>
                            <td><?= htmlspecialchars($ac['indication']) ?></td>
                            <td align="center"><?= htmlspecialchars($ac['seizure_control']) ?></td>
                            <td align="center"><?= strtoupper($ac['hla_b1502']) ?></td>
                            <td align="center"><?= $ac['tdm_level'] ? $ac['tdm_level'] . ' mcg/mL' : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br/>

            <h3>4. ทะเบียนผู้ป่วยโรคไตเรื้อรัง (CKD Care)</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>PID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>CKD Stage</th>
                        <th>eGFR (mL/min/1.73m²)</th>
                        <th>Serum Cr (mg/dL)</th>
                        <th>วันที่ตรวจแล็บ</th>
                        <th>สถานะการฟอกไต</th>
                        <th>ข้อเตือนการใช้ยา (Contraindications)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ckd as $i => $c): ?>
                        <tr>
                            <td align="center"><?= $i + 1 ?></td>
                            <td align="center"><?= $c['pid'] ?></td>
                            <td><?= htmlspecialchars($c['patient']['full_name'] ?? '') ?></td>
                            <td align="center"><strong><?= htmlspecialchars($c['stage']) ?></strong></td>
                            <td align="center"><?= $c['egfr'] ?></td>
                            <td align="center"><?= $c['cr'] ?></td>
                            <td align="center"><?= $c['lab_date'] ?></td>
                            <td align="center"><?= strtoupper($c['dialysis']) ?></td>
                            <td><strong style="color: #dc2626;"><?= htmlspecialchars($c['alerts']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        exit;
    }
}
