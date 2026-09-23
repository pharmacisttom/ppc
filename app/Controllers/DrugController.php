<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Gateway\JhcisGateway;
use PDO;

class DrugController
{
    /**
     * Drug Formulary & Catalog Overview
     */
    public function index(): void
    {
        $search = Request::get('q', '');
        $category = Request::get('category', 'all');
        $nlem = Request::get('nlem', 'all');
        $page = max(1, (int)Request::get('page', 1));
        $perPage = max(10, min(100, (int)Request::get('per_page', 25)));
        $orderBy = Request::get('order_by', 'drugname');
        $orderDir = Request::get('order_dir', 'ASC');

        $filters = [
            'search' => $search,
            'category' => $category,
            'nlem' => $nlem,
            'order_by' => $orderBy,
            'order_dir' => $orderDir,
            'page' => $page,
            'per_page' => $perPage
        ];

        $catalog = JhcisGateway::getDrugCatalog($filters, $page, $perPage);
        $stats = JhcisGateway::getFormularyStats();

        if (!empty($search)) {
            Audit::log('DRUG_SEARCH', 'drug', null, null, null, null, "Searched drugs: {$search}");
        }

        View::render('drug/index', [
            'pageTitle' => 'บัญชีรายการยาทั้งหมดของ รพ.สต. (PCU Smart Drug Formulary)',
            'catalog' => $catalog,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }

    /**
     * Single Drug Clinical & Safety Detail Factsheet
     */
    public function show(string $code): void
    {
        $drug = JhcisGateway::getDrugDetail($code);
        if (!$drug) {
            Session::flash('error', 'ไม่พบข้อมูลรายการยารหัส: ' . htmlspecialchars($code));
            Response::redirect('/pcc/drugs');
        }

        Audit::log('DRUG_VIEW', 'drug', $code, null, null, null, "Viewed drug detail: {$drug['drugname']}");

        View::render('drug/show', [
            'pageTitle' => 'แฟ้มข้อมูลยาและความปลอดภัย: ' . $drug['drugname'],
            'drug' => $drug
        ]);
    }

    /**
     * Official Printable PCU Formulary Document (ตามมาตรฐาน รพ.สต. 2568-2570)
     */
    public function printFormulary(): void
    {
        $category = Request::get('category', 'pcu_formulary');
        $catalog = JhcisGateway::getDrugCatalog([
            'category' => $category,
            'per_page' => 500,
            'order_by' => 'drugname'
        ]);

        $categoryNames = [
            'all' => 'รายการยาทั้งหมดในแคตตาล็อก',
            'pcu_formulary' => 'บัญชีรายการยาประจำโรงพยาบาลส่งเสริมสุขภาพตำบล',
            'modern' => 'บัญชียาแผนปัจจุบัน',
            'herbal' => 'บัญชียาสมุนไพรและแพทย์แผนไทย',
            'cold_chain' => 'บัญชียาชีววัตถุและวัคซีนควบคุมความเย็น (Cold Chain)',
            'emergency' => 'บัญชียาช่วยชีวิตฉุกเฉิน (CPR Kit)',
            'antibiotic' => 'บัญชียาปฏิชีวนะและการเฝ้าระวัง RDU',
            'ham' => 'บัญชียาความเสี่ยงสูง (High Alert Drugs)'
        ];

        View::render('drug/print', [
            'pageTitle' => 'พิมพ์บัญชียา รพ.สต. — ' . ($categoryNames[$category] ?? 'PCU Drug Formulary'),
            'categoryTitle' => $categoryNames[$category] ?? 'บัญชียา รพ.สต.',
            'category' => $category,
            'drugs' => $catalog['items'] ?? [],
            'currentUser' => Auth::user()
        ]);
    }

    /**
     * Export Current Filtered Drug List to Microsoft Excel (.xls)
     */
    public function exportExcel(): void
    {
        $search = Request::get('q', '');
        $category = Request::get('category', 'all');
        $nlem = Request::get('nlem', 'all');

        $catalog = JhcisGateway::getDrugCatalog([
            'search' => $search,
            'category' => $category,
            'nlem' => $nlem,
            'per_page' => 2000,
            'order_by' => 'drugname'
        ]);

        $rows = [];
        $i = 1;
        foreach ($catalog['items'] as $d) {
            $safetyTags = [];
            if ($d['is_ham']) $safetyTags[] = 'HAM (ยาเสี่ยงสูง)';
            if ($d['is_lasa']) $safetyTags[] = 'LASA (' . ($d['tall_man'] ?? 'พ้องมองคล้าย') . ')';
            if ($d['is_antibiotic']) $safetyTags[] = 'ปฏิชีวนะ (RDU)';
            if ($d['is_cold_chain']) $safetyTags[] = 'Cold Chain (2-8°C)';
            if ($d['is_emergency']) $safetyTags[] = 'ยาช่วยชีวิต (CPR)';
            if ($d['is_herbal']) $safetyTags[] = 'ยาสมุนไพร';

            $rows[] = [
                'no' => $i++,
                'drugcode' => $d['drugcode'],
                'tmtcode' => !empty($d['tmtcode']) ? $d['tmtcode'] : '-',
                'drugname' => $d['drugname'],
                'drugnamethai' => !empty($d['drugnamethai']) ? $d['drugnamethai'] : '-',
                'druggenericname' => !empty($d['druggenericname']) ? $d['druggenericname'] : '-',
                'unitsell' => $d['unitsell'],
                'pack' => $d['pack'],
                'cost' => number_format($d['cost'], 2),
                'sell' => number_format($d['sell'], 2),
                'nlem' => $d['is_nlem'] ? 'ในบัญชียาหลัก' : 'นอกบัญชียาหลัก',
                'safety' => !empty($safetyTags) ? implode(', ', $safetyTags) : 'ยาแผนปัจจุบันทั่วไป',
                'stock' => (int)$d['stock_balance'],
                'caution' => !empty($d['drugcaution']) ? $d['drugcaution'] : '-'
            ];
        }

        $headers = [
            'ลำดับ',
            'รหัสยา (JHCIS)',
            'รหัสมาตรฐาน TMT',
            'ชื่อการค้าและขนาด (Trade Name)',
            'ชื่อยาภาษาไทย',
            'ชื่อสามัญทางยา (Generic)',
            'หน่วยนับ',
            'ขนาดบรรจุ',
            'ราคาต้นทุน (บาท)',
            'ราคาเบิกจ่าย (บาท)',
            'บัญชียาหลัก',
            'หมวดหมู่ความปลอดภัย',
            'คงคลัง รพ.สต.',
            'คำเตือนและข้อควรระวัง'
        ];

        $currentUser = Auth::user();
        $facilityName = $currentUser['facility_name'] ?? 'โรงพยาบาลส่งเสริมสุขภาพตำบล';

        $filename = 'PCU_Drug_Formulary_' . date('Ymd_His') . '.xls';
        $title = "บัญชีรายการยาประจำ {$facilityName}";
        $meta = [
            'หน่วยบริการ' => $facilityName,
            'วันที่ออกรายงาน' => date('d/m/Y H:i:s'),
            'ผู้ส่งออกรายงาน' => ($currentUser['firstname'] ?? '') . ' ' . ($currentUser['lastname'] ?? ''),
            'จำนวนรายการทั้งหมด' => count($rows) . ' รายการ'
        ];

        Audit::log('REPORT_EXPORT', 'drug', null, null, null, null, "Exported Excel Drug Formulary: " . count($rows) . " items");

        Response::downloadExcel($filename, $title, $headers, $rows, $meta);
    }

    /**
     * Export Current Filtered Drug List to CSV (with UTF-8 BOM)
     */
    public function exportCsv(): void
    {
        $search = Request::get('q', '');
        $category = Request::get('category', 'all');
        $nlem = Request::get('nlem', 'all');

        $catalog = JhcisGateway::getDrugCatalog([
            'search' => $search,
            'category' => $category,
            'nlem' => $nlem,
            'per_page' => 2000,
            'order_by' => 'drugname'
        ]);

        $rows = [];
        $i = 1;
        foreach ($catalog['items'] as $d) {
            $rows[] = [
                'no' => $i++,
                'drugcode' => $d['drugcode'],
                'tmtcode' => $d['tmtcode'],
                'drugname' => $d['drugname'],
                'drugnamethai' => $d['drugnamethai'],
                'druggenericname' => $d['druggenericname'],
                'unitsell' => $d['unitsell'],
                'pack' => $d['pack'],
                'cost' => $d['cost'],
                'sell' => $d['sell'],
                'nlem' => $d['is_nlem'] ? 'ในบัญชียาหลัก' : 'นอกบัญชียาหลัก',
                'stock' => $d['stock_balance']
            ];
        }

        $headers = [
            'ลำดับ',
            'รหัสยา',
            'รหัส TMT',
            'ชื่อการค้า',
            'ชื่อภาษาไทย',
            'ชื่อสามัญ',
            'หน่วยนับ',
            'ขนาดบรรจุ',
            'ราคาต้นทุน',
            'ราคาขาย',
            'บัญชียาหลัก',
            'ยอดคงคลัง'
        ];

        $filename = 'PCU_Drugs_' . date('Ymd_His') . '.csv';
        Audit::log('REPORT_EXPORT', 'drug', null, null, null, null, "Exported CSV Drug Formulary: " . count($rows) . " items");
        Response::downloadCsv($filename, $headers, $rows);
    }

    /**
     * RESTful API: Live Drug Catalog Search
     */
    public function apiCatalog(): void
    {
        $q = Request::get('q', '');
        $category = Request::get('category', 'all');
        $limit = max(5, min(100, (int)Request::get('limit', 20)));

        $catalog = JhcisGateway::getDrugCatalog([
            'search' => $q,
            'category' => $category,
            'per_page' => $limit
        ]);

        Response::json([
            'status' => 'success',
            'query' => $q,
            'count' => count($catalog['items']),
            'total' => $catalog['total'],
            'data' => $catalog['items']
        ]);
    }

    /**
     * RESTful API: Drug Detail & Safety Factsheet
     */
    public function apiDetail(string $code): void
    {
        $drug = JhcisGateway::getDrugDetail($code);
        if (!$drug) {
            Response::json(['error' => 'Drug not found'], 404);
            return;
        }

        Response::json([
            'status' => 'success',
            'data' => $drug
        ]);
    }

    /**
     * Update Drug Master Information and Record Audit Trail Log
     */
    public function updateMaster(): void
    {
        $drugcode = trim(Request::post('drugcode', ''));
        if (empty($drugcode)) {
            Session::flash('error', 'ไม่พบรหัสยาที่ต้องการแก้ไข');
            Response::redirect('/pcc/drugs');
            return;
        }

        $db = Database::getAppDb();
        $user = Auth::user();
        $userId = $user['user_id'] ?? Session::get('user_id');

        // Fetch previous state
        $stmtBefore = $db->prepare("SELECT * FROM drug_master_configs WHERE drugcode = :c");
        $stmtBefore->execute([':c' => $drugcode]);
        $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $thaiName = trim(Request::post('drugnamethai', ''));
        $genericName = trim(Request::post('druggenericname', ''));
        $isNlem = (int)Request::post('is_nlem', 0);
        $isHam = (int)Request::post('is_ham', 0);
        $isLasa = (int)Request::post('is_lasa', 0);
        $isAntibiotic = (int)Request::post('is_antibiotic', 0);
        $isColdChain = (int)Request::post('is_cold_chain', 0);
        $isEmergency = (int)Request::post('is_emergency', 0);
        $isHerbal = (int)Request::post('is_herbal', 0);
        $minStock = max(0, (int)Request::post('min_stock', 0));
        $maxStock = max(0, (int)Request::post('max_stock', 0));
        $caution = trim(Request::post('caution', ''));

        try {
            $stmt = $db->prepare("
                INSERT INTO drug_master_configs (
                    drugcode, drugnamethai, druggenericname, is_nlem, is_ham, is_lasa,
                    is_antibiotic, is_cold_chain, is_emergency, is_herbal, min_stock, max_stock, caution, updated_by
                ) VALUES (
                    :c, :tname, :gname, :nlem, :ham, :lasa,
                    :atb, :cc, :emg, :hrb, :min, :max, :caution, :uid
                )
                ON DUPLICATE KEY UPDATE
                    drugnamethai = VALUES(drugnamethai),
                    druggenericname = VALUES(druggenericname),
                    is_nlem = VALUES(is_nlem),
                    is_ham = VALUES(is_ham),
                    is_lasa = VALUES(is_lasa),
                    is_antibiotic = VALUES(is_antibiotic),
                    is_cold_chain = VALUES(is_cold_chain),
                    is_emergency = VALUES(is_emergency),
                    is_herbal = VALUES(is_herbal),
                    min_stock = VALUES(min_stock),
                    max_stock = VALUES(max_stock),
                    caution = VALUES(caution),
                    updated_by = VALUES(updated_by),
                    updated_at = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                ':c' => $drugcode,
                ':tname' => $thaiName,
                ':gname' => $genericName,
                ':nlem' => $isNlem,
                ':ham' => $isHam,
                ':lasa' => $isLasa,
                ':atb' => $isAntibiotic,
                ':cc' => $isColdChain,
                ':emg' => $isEmergency,
                ':hrb' => $isHerbal,
                ':min' => $minStock,
                ':max' => $maxStock,
                ':caution' => $caution,
                ':uid' => $userId
            ]);

            // Fetch after state
            $stmtAfter = $db->prepare("SELECT * FROM drug_master_configs WHERE drugcode = :c");
            $stmtAfter->execute([':c' => $drugcode]);
            $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);

            // Record into Audit Logs
            Audit::log(
                'DRUG_MASTER_UPDATED',
                'drugs',
                $drugcode,
                null,
                $before ?: ['status' => 'new_master_config', 'drugcode' => $drugcode],
                $after,
                "แก้ไขข้อมูลพื้นฐานรายการยา: {$drugcode} ({$thaiName})"
            );

            Session::flash('success', "บันทึกข้อมูลพื้นฐานรายการยา [{$drugcode}] และบันทึกประวัติ Log เรียบร้อยแล้ว");
        } catch (\Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }

        $returnUrl = Request::post('return_url', '/pcc/drugs');
        Response::redirect($returnUrl);
    }
}
