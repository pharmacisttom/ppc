<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class SafetyController
{
    /**
     * High Alert Medications (HAM) & LASA Management
     */
    public function hamLasa(): void
    {
        $db = Database::getAppDb();

        $hams = $db->query("SELECT * FROM high_alert_drugs ORDER BY generic_name ASC")->fetchAll();
        $lasas = $db->query("SELECT * FROM lasa_drugs ORDER BY drug_name_1 ASC")->fetchAll();

        View::render('safety/ham_lasa', [
            'pageTitle' => 'การจัดการยากลุ่มเสี่ยงสูง (HAM) และยาชื่อพ้องมองคล้าย (LASA)',
            'hams' => $hams,
            'lasas' => $lasas
        ]);
    }

    /**
     * Emergency Medication Sets (CPR Kit) & Inspection Checklist
     */
    public function emergencyKit(): void
    {
        $db = Database::getAppDb();

        $kitItems = [
            ['name' => 'Adrenaline (Epinephrine) 1 mg/ml injection', 'qty_required' => 5, 'qty_available' => 5, 'expiry_date' => '2027-02-15', 'status' => 'ready'],
            ['name' => 'Atropine Sulfate 0.6 mg/ml injection', 'qty_required' => 5, 'qty_available' => 5, 'expiry_date' => '2027-04-10', 'status' => 'ready'],
            ['name' => 'Diazepam 10 mg/2 ml injection', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2026-12-01', 'status' => 'ready'],
            ['name' => '50% Glucose 50 ml injection', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2027-08-20', 'status' => 'ready'],
            ['name' => 'NSS 0.9% 1,000 ml IV infusion', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2028-01-10', 'status' => 'ready']
        ];

        View::render('safety/emergency_kit', [
            'pageTitle' => 'การตรวจสอบชุดยาช่วยชีวิตฉุกเฉิน (Emergency CPR Kit)',
            'kitItems' => $kitItems
        ]);
    }
}
