<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Gateway\JhcisGateway;

class ReportController
{
    /**
     * Reports Center Dashboard
     */
    public function index(): void
    {
        $stats = JhcisGateway::getFormularyStats();
        $db = Database::getAppDb();

        $stockStats = $db->query("
            SELECT 
                COUNT(lot_id) as total_lots,
                SUM(quantity_balance * unit_cost) as total_value,
                SUM(CASE WHEN DATEDIFF(expiry_date, CURRENT_DATE()) <= 0 THEN 1 ELSE 0 END) as expired_cnt,
                SUM(CASE WHEN DATEDIFF(expiry_date, CURRENT_DATE()) BETWEEN 1 AND 90 THEN 1 ELSE 0 END) as exp_90_cnt
            FROM stock_lots WHERE status = 'active'
        ")->fetch();

        $reviewCount = (int)$db->query("SELECT COUNT(*) FROM medication_reviews")->fetchColumn();
        $problemCount = (int)$db->query("SELECT COUNT(*) FROM medication_review_problems")->fetchColumn();
        $incidentCount = (int)$db->query("SELECT COUNT(*) FROM medication_incidents")->fetchColumn();

        View::render('report/index', [
            'pageTitle' => 'ศูนย์รายงานและส่งออกข้อมูล (Reporting & Excel Export Center)',
            'stats' => $stats,
            'stockStats' => $stockStats,
            'reviewCount' => $reviewCount,
            'problemCount' => $problemCount,
            'incidentCount' => $incidentCount,
            'currentUser' => Auth::user()
        ]);
    }

    /**
     * Export Stock & Expiry FEFO Report
     */
    public function exportInventory(): void
    {
        $db = Database::getAppDb();
        $lots = $db->query("
            SELECT sl.*, loc.location_name,
                   DATEDIFF(sl.expiry_date, CURRENT_DATE()) as days_to_expire
            FROM stock_lots sl
            JOIN stock_locations loc ON sl.location_id = loc.location_id
            WHERE sl.status = 'active'
            ORDER BY sl.expiry_date ASC, sl.drug_name ASC
        ")->fetchAll();

        $rows = [];
        $i = 1;
        foreach ($lots as $lot) {
            $days = (int)$lot['days_to_expire'];
            $fefoStatus = 'ปกติ (Safe)';
            if ($days <= 0) $fefoStatus = 'หมดอายุแล้ว (Expired)';
            elseif ($days <= 30) $fefoStatus = 'วิกฤต (≤ 30 วัน)';
            elseif ($days <= 90) $fefoStatus = 'เฝ้าระวัง (≤ 90 วัน)';
            elseif ($days <= 180) $fefoStatus = 'ใกล้หมดอายุ (≤ 180 วัน)';

            $rows[] = [
                'no' => $i++,
                'drug_code' => $lot['drug_code'],
                'drug_name' => $lot['drug_name'],
                'lot_number' => $lot['lot_number'],
                'location' => $lot['location_name'],
                'expiry_date' => $lot['expiry_date'],
                'days_left' => $days,
                'fefo_status' => $fefoStatus,
                'qty_received' => (int)$lot['quantity_received'],
                'qty_balance' => (int)$lot['quantity_balance'],
                'unit_cost' => number_format($lot['unit_cost'], 2),
                'total_value' => number_format($lot['quantity_balance'] * $lot['unit_cost'], 2),
                'supplier' => $lot['supplier'] ?? 'องค์การเภสัชกรรม (GPO)'
            ];
        }

        $headers = [
            'ลำดับ',
            'รหัสยา',
            'ชื่อยา',
            'หมายเลข Lot',
            'สถานที่จัดเก็บ',
            'วันหมดอายุ (Expiry Date)',
            'จำนวนวันคงเหลือ',
            'สถานะความเสี่ยง FEFO',
            'ยอดรับเข้า',
            'ยอดคงคลังปัจจุบัน',
            'ราคาต่อหน่วย (บาท)',
            'มูลค่าคงคลังรวม (บาท)',
            'ผู้ผลิต / แหล่งเบิก'
        ];

        $user = Auth::user();
        $filename = 'PCU_Stock_FEFO_' . date('Ymd_His') . '.xls';
        $title = 'รายงานคลังยาและการบริหารวันหมดอายุตามหลัก FEFO';
        $meta = [
            'หน่วยบริการ' => $user['facility_name'] ?? 'รพ.สต.บ้านหนองบัว',
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s'),
            'ผู้จัดทำ' => ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')
        ];

        Audit::log('REPORT_EXPORT', 'inventory', null, null, null, null, "Exported Inventory Stock FEFO Report");
        Response::downloadExcel($filename, $title, $headers, $rows, $meta);
    }

    /**
     * Export Cold Chain Temperature Logs
     */
    public function exportColdChain(): void
    {
        $db = Database::getAppDb();
        $logs = $db->query("
            SELECT l.*, u.unit_code, u.unit_name, usr.firstname, usr.lastname
            FROM cold_chain_temperature_logs l
            JOIN cold_chain_units u ON l.unit_id = u.unit_id
            JOIN users usr ON l.recorded_by = usr.user_id
            ORDER BY l.record_date DESC, l.session ASC
            LIMIT 500
        ")->fetchAll();

        $rows = [];
        $i = 1;
        foreach ($logs as $log) {
            $rows[] = [
                'no' => $i++,
                'record_date' => $log['record_date'],
                'session' => $log['session'] === 'morning' ? 'เช้า (08:30-09:00)' : 'บ่าย (15:30-16:00)',
                'unit_name' => $log['unit_name'],
                'current_temp' => number_format((float)$log['current_temp'], 1) . ' °C',
                'min_temp' => number_format((float)$log['min_recorded'], 1) . ' °C',
                'max_temp' => number_format((float)$log['max_recorded'], 1) . ' °C',
                'status' => $log['is_excursion'] ? 'หลุดเกณฑ์ (Excursion)' : 'ปกติ (2.0 - 8.0 °C)',
                'action_taken' => !empty($log['action_taken']) ? $log['action_taken'] : '-',
                'recorded_by' => $log['firstname'] . ' ' . $log['lastname']
            ];
        }

        $headers = [
            'ลำดับ',
            'วันที่บันทึก',
            'รอบบันทึก',
            'ชื่อตู้เย็นจัดเก็บ',
            'อุณหภูมิปัจจุบัน',
            'อุณหภูมิต่ำสุด (Min)',
            'อุณหภูมิสูงสุด (Max)',
            'สถานะมาตรฐานลูกโซ่ความเย็น',
            'มาตรการแก้ไขกรณีหลุดเกณฑ์',
            'ผู้รับผิดชอบบันทึก'
        ];

        $user = Auth::user();
        $filename = 'PCU_ColdChain_Log_' . date('Ymd_His') . '.xls';
        Audit::log('REPORT_EXPORT', 'coldchain', null, null, null, null, "Exported Cold Chain Temperature Report");
        Response::downloadExcel($filename, 'รายงานการบันทึกอุณหภูมิตู้เย็นยาและวัคซีน 2-8 °C', $headers, $rows, [
            'หน่วยบริการ' => $user['facility_name'] ?? 'รพ.สต.บ้านหนองบัว',
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s')
        ]);
    }

    /**
     * Export HAM & LASA Safety Register
     */
    public function exportHamLasa(): void
    {
        $db = Database::getAppDb();
        $hams = $db->query("SELECT * FROM high_alert_drugs ORDER BY drug_code ASC")->fetchAll();
        $lasas = $db->query("SELECT * FROM lasa_drugs ORDER BY drug_code_1 ASC")->fetchAll();

        $rows = [];
        $i = 1;
        foreach ($hams as $ham) {
            $rows[] = [
                'no' => $i++,
                'type' => 'HAM (ยาความเสี่ยงสูง)',
                'code' => $ham['drug_code'],
                'name' => $ham['generic_name'],
                'risk_category' => $ham['risk_category'],
                'precautions' => $ham['precautions'],
                'storage' => $ham['storage_instructions'],
                'double_check' => $ham['double_check_required'] ? 'บังคับตรวจสอบซ้ำอิสระ (Independent Double Check)' : '-'
            ];
        }

        foreach ($lasas as $lasa) {
            $rows[] = [
                'no' => $i++,
                'type' => 'LASA (' . ($lasa['lasa_type'] === 'look_alike' ? 'มองคล้าย' : ($lasa['lasa_type'] === 'sound_alike' ? 'เสียงพ้อง' : 'ทั้งพ้องและคล้าย')) . ')',
                'code' => $lasa['drug_code_1'] . ' ↔ ' . $lasa['drug_code_2'],
                'name' => $lasa['tall_man_1'] . ' (' . $lasa['drug_name_1'] . ') กับ ' . $lasa['tall_man_2'] . ' (' . $lasa['drug_name_2'] . ')',
                'risk_category' => 'Look-Alike Sound-Alike Risk',
                'precautions' => $lasa['warning_note'],
                'storage' => $lasa['storage_separation_required'] ? 'บังคับจัดเก็บแยกจุด / ป้ายเตือน Tall Man' : 'ป้ายเตือน',
                'double_check' => 'Double-check ชื่อยาและขนาดยาก่อนจัด'
            ];
        }

        $headers = [
            'ลำดับ',
            'ประเภทความปลอดภัย',
            'รหัสยา',
            'ชื่อยา / Tall Man Lettering',
            'ระดับความเสี่ยง',
            'ข้อควรระวังทางคลินิก (Precautions)',
            'แนวทางการจัดเก็บรักษา (Storage)',
            'มาตรการป้องกันความคลาดเคลื่อน'
        ];

        $user = Auth::user();
        $filename = 'PCU_HAM_LASA_Register_' . date('Ymd_His') . '.xls';
        Audit::log('REPORT_EXPORT', 'safety', null, null, null, null, "Exported HAM & LASA Safety Register");
        Response::downloadExcel($filename, 'ทะเบียนยาความเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA)', $headers, $rows, [
            'หน่วยบริการ' => $user['facility_name'] ?? 'รพ.สต.บ้านหนองบัว',
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s')
        ]);
    }

    /**
     * Export Medication Review & DRP Problems Report
     */
    public function exportDrp(): void
    {
        $db = Database::getAppDb();
        $drps = $db->query("
            SELECT p.*, r.review_date, r.patient_pid, r.review_type,
                   u.firstname, u.lastname
            FROM medication_review_problems p
            JOIN medication_reviews r ON p.review_id = r.review_id
            JOIN users u ON r.reviewer_id = u.user_id
            ORDER BY r.review_date DESC
        ")->fetchAll();

        $rows = [];
        $i = 1;
        $drpCategories = [
            'unnecessary_medication' => 'ยาเกินความจำเป็น',
            'need_additional_therapy' => 'ต้องการยาเพิ่มเติม',
            'ineffective_medication' => 'ยาไม่ได้ผลการรักษา',
            'dose_too_low' => 'ขนาดยาต่ำเกินไป',
            'dose_too_high' => 'ขนาดยาสูงเกินไป',
            'adverse_drug_reaction' => 'อาการไม่พึงประสงค์จากยา (ADR)',
            'drug_interaction' => 'อันตรกิริยาระหว่างยา (DDI)',
            'non_adherence' => 'ความไม่ร่วมมือในการใช้ยา',
            'duplicate_therapy' => 'การสั่งยาซ้ำซ้อน',
            'administration_problem' => 'ปัญหาเทคนิคการใช้ยา'
        ];

        foreach ($drps as $drp) {
            $rows[] = [
                'no' => $i++,
                'date' => $drp['review_date'],
                'pid' => $drp['patient_pid'],
                'drug' => $drp['drug_name'],
                'category' => $drpCategories[$drp['drp_category']] ?? $drp['drp_category'],
                'description' => $drp['problem_description'],
                'recommendation' => $drp['recommendation'],
                'prescriber_response' => $drp['prescriber_response'],
                'outcome' => $drp['outcome'],
                'reviewer' => $drp['firstname'] . ' ' . $drp['lastname']
            ];
        }

        $headers = [
            'ลำดับ',
            'วันที่ทบทวน',
            'รหัสผู้รับบริการ (PID)',
            'รายการยาที่เกี่ยวข้อง',
            'ประเภทปัญหาการใช้ยา (DRP)',
            'รายละเอียดปัญหา',
            'ข้อเสนอแนะทางเภสัชกรรม',
            'การตอบรับจากแพทย์/ผู้สั่งใช้',
            'ผลลัพธ์การแก้ไข',
            'เภสัชกรผู้ทบทวน'
        ];

        $user = Auth::user();
        $filename = 'PCU_Medication_Review_DRP_' . date('Ymd_His') . '.xls';
        Audit::log('REPORT_EXPORT', 'review', null, null, null, null, "Exported Medication Review DRP Report");
        Response::downloadExcel($filename, 'รายงานการทบทวนยาและปัญหาจากการใช้ยา (DRP Report)', $headers, $rows, [
            'หน่วยบริการ' => $user['facility_name'] ?? 'รพ.สต.บ้านหนองบัว',
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s')
        ]);
    }

    /**
     * Export Quality Standards Assessment (พ.ศ. 2568-2570)
     */
    public function exportQuality(): void
    {
        $db = Database::getAppDb();
        $criteria = $db->query("
            SELECT c.*, cat.category_name, cat.category_code,
                   qa.compliance_status, qa.score, qa.assessed_date
            FROM quality_criteria c
            JOIN quality_categories cat ON c.category_id = cat.category_id
            LEFT JOIN quality_assessments qa ON c.criterion_id = qa.criterion_id
            ORDER BY cat.ordering ASC, c.criterion_code ASC
        ")->fetchAll();

        $rows = [];
        $i = 1;
        $statusLabels = [
            'compliant' => 'ผ่านเกณฑ์สมบูรณ์ (100%)',
            'partial' => 'ผ่านเกณฑ์บางส่วน (50%)',
            'non_compliant' => 'ยังไม่ผ่านเกณฑ์ (0%)',
            'not_applicable' => 'ไม่เข้าเกณฑ์'
        ];

        foreach ($criteria as $c) {
            $rows[] = [
                'no' => $i++,
                'dim' => $c['category_name'],
                'code' => $c['criterion_code'],
                'name' => $c['criterion_name'],
                'sub' => $c['subcriterion'],
                'requirement' => $c['requirement'],
                'weight' => $c['weight'],
                'target' => $c['target'],
                'status' => $statusLabels[$c['compliance_status'] ?? ''] ?? 'รอการประเมิน',
                'score' => number_format((float)($c['score'] ?? 0), 1),
                'evidence' => $c['evidence_required'],
                'responsible' => $c['responsible_role']
            ];
        }

        $headers = [
            'ลำดับ',
            'มิติคุณภาพปฐมภูมิ',
            'รหัสเกณฑ์',
            'ชื่อเกณฑ์ประเมิน',
            'เกณฑ์ย่อย',
            'ข้อกำหนดมาตรฐาน 2568–2570',
            'น้ำหนักคะแนน',
            'เป้าหมาย (%)',
            'สถานะการปฏิบัติตามเกณฑ์',
            'คะแนนที่ได้',
            'หลักฐานเชิงประจักษ์ที่ต้องการ',
            'ผู้รับผิดชอบหลัก'
        ];

        $user = Auth::user();
        $filename = 'PCU_PrimaryCare_Standard_Assessment_' . date('Ymd_His') . '.xls';
        Audit::log('REPORT_EXPORT', 'quality', null, null, null, null, "Exported Primary Care Standards Assessment Report");
        Response::downloadExcel($filename, 'รายงานผลการประเมินตนเองตามมาตรฐานหน่วยบริการปฐมภูมิ พ.ศ. 2568–2570', $headers, $rows, [
            'หน่วยบริการ' => $user['facility_name'] ?? 'รพ.สต.บ้านหนองบัว',
            'รอบประเมิน' => 'ปีงบประมาณ 2569 (เกณฑ์ 2568–2570)',
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s')
        ]);
    }
}
