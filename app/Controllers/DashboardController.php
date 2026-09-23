<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Gateway\JhcisGateway;
use PDO;

class DashboardController
{
    public function index(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        // 1. Check JHCIS Status
        $jhcisConnected = Database::isJhcisConnected();
        $isTraining = JhcisGateway::isTrainingMode();

        // 2. Fetch Stock Expiry Counters from App DB
        $stmtExp = $db->query("
            SELECT 
                SUM(CASE WHEN expiry_date <= CURRENT_DATE() THEN 1 ELSE 0 END) AS expired_count,
                SUM(CASE WHEN expiry_date > CURRENT_DATE() AND expiry_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS exp_30_count,
                SUM(CASE WHEN expiry_date > DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY) AND expiry_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) AS exp_90_count,
                SUM(quantity_balance * unit_cost) AS total_stock_value
            FROM stock_lots
            WHERE status = 'active'
        ");
        $stockStats = $stmtExp->fetch();

        // 3. Fetch Cold Chain Today Status
        $stmtCold = $db->query("
            SELECT u.unit_code, u.unit_name, u.min_temp, u.max_temp,
                   (SELECT current_temp FROM cold_chain_temperature_logs l WHERE l.unit_id = u.unit_id AND l.record_date = CURRENT_DATE() AND l.session = 'morning' LIMIT 1) as morning_temp,
                   (SELECT current_temp FROM cold_chain_temperature_logs l WHERE l.unit_id = u.unit_id AND l.record_date = CURRENT_DATE() AND l.session = 'afternoon' LIMIT 1) as afternoon_temp
            FROM cold_chain_units u
            WHERE u.is_active = 1
        ");
        $coldChainUnits = $stmtCold->fetchAll();

        // 4. Fetch Quality Standards Readiness
        $stmtQual = $db->query("
            SELECT 
                COUNT(*) as total_criteria,
                SUM(CASE WHEN a.status = 'ready' OR a.status = 'passed_internal_review' THEN 1 ELSE 0 END) as ready_count,
                SUM(CASE WHEN a.status = 'evidence_missing' THEN 1 ELSE 0 END) as missing_evidence_count,
                SUM(CASE WHEN a.status IS NULL OR a.status = 'not_assessed' THEN 1 ELSE 0 END) as not_assessed_count
            FROM quality_criteria c
            LEFT JOIN quality_assessments a ON c.criterion_id = a.criterion_id
            WHERE c.is_active = 1
        ");
        $qualStats = $stmtQual->fetch();

        // 5. Total Reviews & Incidents
        $revCount = $db->query("SELECT COUNT(*) FROM medication_reviews")->fetchColumn();
        $incCount = $db->query("SELECT COUNT(*) FROM medication_incidents WHERE status != 'closed'")->fetchColumn();

        // 6. Recent Audit Logs
        $auditLogs = $db->query("
            SELECT action_type, module_name, patient_pid, created_at, ip_address 
            FROM audit_logs 
            ORDER BY log_id DESC 
            LIMIT 5
        ")->fetchAll();

        // 7. Training Scenario Shortcuts
        $scenarios = JhcisGateway::getSimulatedPatients();

        View::render('dashboard/index', [
            'pageTitle' => 'แดชบอร์ดบริหารจัดการด้านยาและเภสัชกรรมปฐมภูมิ',
            'user' => $user,
            'jhcisConnected' => $jhcisConnected,
            'isTraining' => $isTraining,
            'stockStats' => $stockStats,
            'coldChainUnits' => $coldChainUnits,
            'qualStats' => $qualStats,
            'reviewCount' => $revCount,
            'openIncidents' => $incCount,
            'auditLogs' => $auditLogs,
            'scenarios' => $scenarios
        ]);
    }
}
