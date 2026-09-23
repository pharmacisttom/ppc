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

class InventoryController
{
    /**
     * Inventory Stock & FEFO Overview
     */
    public function index(): void
    {
        $db = Database::getAppDb();

        $lots = $db->query("
            SELECT l.*, sl.location_name,
                   DATEDIFF(l.expiry_date, CURRENT_DATE()) as days_to_expire
            FROM stock_lots l
            JOIN stock_locations sl ON l.location_id = sl.location_id
            WHERE l.status = 'active'
            ORDER BY l.expiry_date ASC, l.drug_name ASC
        ")->fetchAll();

        // Expiry Buckets
        $expiryStats = [
            'expired' => 0,
            'exp_30' => 0,
            'exp_90' => 0,
            'exp_180' => 0,
            'safe' => 0,
            'total_value' => 0.00
        ];

        foreach ($lots as $lot) {
            $days = (int)$lot['days_to_expire'];
            $val = $lot['quantity_balance'] * $lot['unit_cost'];
            $expiryStats['total_value'] += $val;

            if ($days <= 0) $expiryStats['expired']++;
            elseif ($days <= 30) $expiryStats['exp_30']++;
            elseif ($days <= 90) $expiryStats['exp_90']++;
            elseif ($days <= 180) $expiryStats['exp_180']++;
            else $expiryStats['safe']++;
        }

        View::render('inventory/index', [
            'pageTitle' => 'การจัดการคลังยาตามหลัก FEFO และวันหมดอายุ',
            'lots' => $lots,
            'expiryStats' => $expiryStats
        ]);
    }

    /**
     * Stock Movement & Stock Card
     */
    public function movements(): void
    {
        $db = Database::getAppDb();
        $movements = $db->query("
            SELECT m.*, l.drug_name, l.lot_number, u.firstname, u.lastname
            FROM stock_movements m
            JOIN stock_lots l ON m.lot_id = l.lot_id
            JOIN users u ON m.operator_id = u.user_id
            ORDER BY m.created_at DESC
            LIMIT 100
        ")->fetchAll();

        View::render('inventory/movements', [
            'pageTitle' => 'ประวัติการเคลื่อนไหวคลังยา (Electronic Stock Card)',
            'movements' => $movements
        ]);
    }

    /**
     * Expiry Management Dashboard
     */
    public function expiryDashboard(): void
    {
        $this->index();
    }

    /**
     * JHCIS Dual-Store Inventory Dashboard (คลังยาใน vs คลังยานอก)
     */
    public function jhcisStoreDashboard(): void
    {
        $search = Request::get('q', '');
        $status = Request::get('status', 'all');
        $page = (int)Request::get('page', 1);
        $perPage = 25;

        $summary = JhcisGateway::getJhcisDualStoreSummary();
        $stockData = JhcisGateway::getJhcisDualStockList([
            'search' => $search,
            'status' => $status
        ], $page, $perPage);
        $transfers = JhcisGateway::getJhcisTransferHistory(15);

        View::render('inventory/jhcis_stores', [
            'pageTitle' => 'แดชบอร์ดบริหารจัดการคลังยานอก-คลังยาใน JHCIS (Dual-Store Inventory)',
            'summary' => $summary,
            'stocks' => $stockData['items'],
            'total' => $stockData['total'],
            'page' => $stockData['page'],
            'pages' => $stockData['pages'],
            'search' => $search,
            'status' => $status,
            'transfers' => $transfers
        ]);
    }

    /**
     * Export Dual-Store Inventory Comparative Report to Excel
     */
    public function exportDualStoreExcel(): void
    {
        $search = Request::get('q', '');
        $status = Request::get('status', 'all');
        $stockData = JhcisGateway::getJhcisDualStockList([
            'search' => $search,
            'status' => $status
        ], 1, 1000);

        $headers = [
            'รหัสยา (Drug Code)',
            'ชื่อยาและขนาด (Drug Name)',
            'ชื่อการค้า / ชื่อไทย',
            'หน่วยนับ',
            'ราคาต้นทุน (บาท)',
            'ยอดคงคลังใน (Main Store)',
            'มูลค่าคลังใน (บาท)',
            'ยอดคงคลังนอก (Dispensary)',
            'มูลค่าคลังนอก (บาท)',
            'ยอดคงคลังรวม (Total)',
            'มูลค่าคงคลังรวม (บาท)',
            'Lot ล่าสุด',
            'วันหมดอายุ',
            'สถานะสต็อก'
        ];

        $rows = [];
        foreach ($stockData['items'] as $item) {
            $rows[] = [
                $item['drugcode'],
                $item['drugname'],
                $item['drugnamethai'] ?: '-',
                $item['unitsell'] ?: 'หน่วย',
                number_format((float)$item['unit_cost'], 2),
                number_format((float)$item['main_store_remain']),
                number_format((float)$item['main_value'], 2),
                number_format((float)$item['dispensary_remain']),
                number_format((float)$item['disp_value'], 2),
                number_format((float)$item['total_remain']),
                number_format((float)$item['total_value'], 2),
                $item['lotno'] ?: '-',
                $item['dateexpire'] ?: '-',
                $item['stock_status']
            ];
        }

        $filename = 'jhcis_dual_store_inventory_' . date('Ymd_His') . '.xls';
        $reportTitle = 'รายงานเปรียบเทียบยอดคงคลังยานอก-คลังยาใน JHCIS';
        Response::downloadExcel($filename, $reportTitle, $headers, $rows);
    }

    /**
     * MOPH Form รบ. 301 Stock Card Ledger
     */
    public function rb301(): void
    {
        $drugCode = Request::get('drugcode', '1CET');
        $storeType = Request::get('store', 'all');
        $dateStart = Request::get('start', date('Y-m-d', strtotime('-6 months')));
        $dateEnd = Request::get('end', date('Y-m-d'));

        // Load active drug list for dropdown
        $drugs = JhcisGateway::getDrugCatalog(['active_only' => true], 1, 300);

        $stockCard = JhcisGateway::getRb301StockCard($drugCode, $storeType, $dateStart, $dateEnd);

        View::render('inventory/rb301', [
            'pageTitle' => 'บัญชีคุมเวชภัณฑ์ (แบบ รบ. 301) - MOPH Stock Card',
            'stockCard' => $stockCard,
            'drugCode' => $drugCode,
            'storeType' => $storeType,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'drugs' => $drugs['drugs'] ?? []
        ]);
    }

    /**
     * Export MOPH Form รบ. 301 to Excel
     */
    public function exportRb301Excel(): void
    {
        $drugCode = Request::get('drugcode', '1CET');
        $storeType = Request::get('store', 'all');
        $dateStart = Request::get('start', date('Y-m-d', strtotime('-6 months')));
        $dateEnd = Request::get('end', date('Y-m-d'));

        $sc = JhcisGateway::getRb301StockCard($drugCode, $storeType, $dateStart, $dateEnd);
        $drug = $sc['drug'] ?? [];

        $storeTitle = match($storeType) {
            'main' => 'คลังยาใน (Main Store)',
            'dispensary' => 'คลังยานอก (Dispensary)',
            default => 'คลังรวม (Integrated)'
        };

        $headers = [
            'วัน เดือน ปี',
            'เลขที่เอกสาร',
            'รายการ / รับจาก - จ่ายให้',
            'คลัง',
            'ราคาต่อหน่วย (บาท)',
            'รุ่นที่ผลิต (Lot No.)',
            'วันหมดอายุ',
            'จำนวนรับ',
            'จำนวนจ่าย',
            'จำนวนคงเหลือ',
            'หมายเหตุ'
        ];

        $rows = [];
        // Opening balance row
        $rows[] = [
            $dateStart,
            '-',
            'ยอดยกมา (Opening Balance)',
            $storeTitle,
            number_format((float)($drug['cost'] ?? 0), 2),
            '-',
            '-',
            '-',
            '-',
            number_format((float)$sc['opening_balance']),
            'ยอดยกมาก่อนวันที่ ' . $dateStart
        ];

        foreach ($sc['movements'] as $m) {
            $rows[] = [
                $m['tx_date'],
                $m['doc_no'],
                $m['party'],
                $m['store_name'],
                number_format((float)$m['unit_price'], 2),
                $m['lotno'] ?: '-',
                $m['expiredate'] ?: '-',
                (int)$m['qty_in'] > 0 ? number_format((int)$m['qty_in']) : '-',
                (int)$m['qty_out'] > 0 ? number_format((int)$m['qty_out']) : '-',
                number_format((int)$m['balance']),
                $m['remark'] ?: '-'
            ];
        }

        $filename = "rb301_{$drugCode}_" . date('Ymd_His') . ".xls";
        $reportTitle = "บัญชีคุมเวชภัณฑ์ (แบบ รบ. 301) - " . ($drug['drugname'] ?? $drugCode) . " [{$storeTitle}]";

        Response::downloadExcel($filename, $reportTitle, $headers, $rows);
    }
}
