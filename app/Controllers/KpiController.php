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
        // Simulated or Live RDU stats
        $rduData = [
            'uri_rate' => 12.5, // Target <= 20% (Passed Green)
            'uri_antibiotic_visits' => 25,
            'uri_total_visits' => 200,

            'diarrhea_rate' => 8.3, // Target <= 20% (Passed Green)
            'diarrhea_antibiotic_visits' => 10,
            'diarrhea_total_visits' => 120,

            'wound_rate' => 28.0, // Target <= 40% (Passed Green)
            'wound_antibiotic_visits' => 14,
            'wound_total_visits' => 50,

            'top_antibiotics' => [
                ['name' => 'Amoxicillin 500 mg cap', 'prescriptions' => 38, 'percentage' => 77.5],
                ['name' => 'Ciprofloxacin 500 mg tab', 'prescriptions' => 7, 'percentage' => 14.3],
                ['name' => 'Norfloxacin 400 mg tab', 'prescriptions' => 4, 'percentage' => 8.2]
            ]
        ];

        View::render('kpi/rdu', [
            'pageTitle' => 'การใช้ยาอย่างสมเหตุผลและการจัดการยาปฏิชีวนะ (RDU & Antibiotic Stewardship)',
            'rdu' => $rduData,
            'isTraining' => JhcisGateway::isTrainingMode()
        ]);
    }
}
