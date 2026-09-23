<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Gateway\JhcisGateway;

class KpiController
{
    /**
     * Quality KPIs & Clinical Indicators
     */
    public function index(): void
    {
        $db = Database::getAppDb();

        $kpis = $db->query("
            SELECT k.*,
                   COALESCE(r.result_value, 0.00) as current_result,
                   r.numerator_value, r.denominator_value, r.remarks
            FROM kpis k
            LEFT JOIN kpi_results r ON k.kpi_id = r.kpi_id AND r.period_year = 2568 AND r.period_month = MONTH(CURRENT_DATE())
            WHERE k.is_active = 1
            ORDER BY k.category ASC, k.kpi_code ASC
        ")->fetchAll();

        View::render('kpi/index', [
            'pageTitle' => 'ตัวชี้วัดคุณภาพทางคลินิกและการบริหารระบบยา (Quality KPIs)',
            'kpis' => $kpis
        ]);
    }

    /**
     * Rational Drug Use (RDU) & Antibiotic Stewardship Dashboard
     */
    public function rduDashboard(): void
    {
        $rduData = JhcisGateway::getRduStatistics();

        View::render('kpi/rdu', [
            'pageTitle' => 'การใช้ยาอย่างสมเหตุผลและการจัดการยาปฏิชีวนะ (RDU & Antibiotic Stewardship)',
            'rdu' => $rduData
        ]);
    }
}
