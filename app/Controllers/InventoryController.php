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
            'expiryStats' => $expiryStats,
            'isTraining' => JhcisGateway::isTrainingMode()
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
}
