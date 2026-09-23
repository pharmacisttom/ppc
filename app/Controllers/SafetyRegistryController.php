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

class SafetyRegistryController
{
    /**
     * Drug Allergy Registry & Repeat Radar
     */
    public function allergies(): void
    {
        $q = Request::get('q', '');
        $allergies = JhcisGateway::getAllergyRegistryList($q ?: null);

        $repeatRadarCount = 0;
        foreach ($allergies as $a) {
            if (!empty($a['is_repeat_prescribed'])) {
                $repeatRadarCount++;
            }
        }

        View::render('safety/allergies', [
            'pageTitle' => 'ทะเบียนผู้ป่วยแพ้ยา (Drug Allergy Registry & Radar)',
            'allergies' => $allergies,
            'query' => $q,
            'repeatCount' => $repeatRadarCount,
            'totalCount' => count($allergies)
        ]);
    }

    /**
     * Official Thai MOPH Standard Drug Allergy Card (บัตรแพ้ยามาตรฐาน สธ.)
     */
    public function printAllergyCard(string $pid): void
    {
        $pidInt = (int)$pid;
        $patient = JhcisGateway::getPatient($pidInt);
        if (!$patient) {
            Session::flash('error', 'ไม่พบข้อมูลผู้รับบริการ');
            Response::redirect('/pcc/safety/allergies');
            return;
        }

        $allergies = JhcisGateway::getPatientAllergies($pidInt);
        if (empty($allergies)) {
            // Also check personalergic directly
            $allAllergies = JhcisGateway::getAllergyRegistryList((string)$pidInt);
            $allergies = array_filter($allAllergies, fn($a) => $a['pid'] === $pidInt);
        }

        $user = Auth::user();
        $cardNo = 'ALC-' . date('Y') . '-' . str_pad((string)$pidInt, 6, '0', STR_PAD_LEFT);

        // Record in pcu_allergy_cards for official audit trail
        $db = Database::getAppDb();
        try {
            $stmt = $db->prepare("
                INSERT INTO pcu_allergy_cards (card_no, pid, issue_date, hospital_code, hospital_name, issuer_name, allergies_snapshot, print_count)
                VALUES (:cno, :pid, CURDATE(), '01996', 'รพ.สต.บ้านดอกกราย เครือข่าย CUP รพ.ปลวกแดง', :issuer, :snap, 1)
                ON DUPLICATE KEY UPDATE print_count = print_count + 1, updated_at = NOW()
            ");
            $stmt->execute([
                ':cno' => $cardNo,
                ':pid' => $pidInt,
                ':issuer' => ($user['title'] ?? '') . ($user['firstname'] ?? 'เภสัชกร') . ' ' . ($user['lastname'] ?? ''),
                ':snap' => json_encode($allergies, JSON_UNESCAPED_UNICODE)
            ]);
        } catch (\Throwable $e) {}

        Audit::log('PRINT_ALLERGY_CARD', 'safety', (string)$pidInt, $pidInt, null, null, "Issued official drug allergy card {$cardNo}");

        View::render('safety/allergy_card', [
            'pageTitle' => 'บัตรแพ้ยา — ' . $patient['full_name'],
            'patient' => $patient,
            'allergies' => $allergies,
            'cardNo' => $cardNo,
            'user' => $user
        ], null); // Render standalone printable card
    }

    /**
     * Warfarin Clinic Registry
     */
    public function warfarin(): void
    {
        $q = Request::get('q', '');
        $patients = JhcisGateway::getWarfarinRegistryList($q ?: null);

        $inRange = 0;
        $below = 0;
        $above = 0;
        $critical = 0;

        foreach ($patients as $p) {
            if ($p['inr_status'] === 'in_range') $inRange++;
            elseif ($p['inr_status'] === 'below_target') $below++;
            elseif ($p['inr_status'] === 'above_target') $above++;
            elseif ($p['inr_status'] === 'critical') $critical++;
        }

        View::render('safety/warfarin', [
            'pageTitle' => 'คลินิกผู้ป่วยรับยา Warfarin (High Alert Anticoagulant)',
            'patients' => $patients,
            'query' => $q,
            'stats' => [
                'total' => count($patients),
                'in_range' => $inRange,
                'below' => $below,
                'above' => $above,
                'critical' => $critical
            ]
        ]);
    }

    /**
     * Anticonvulsant Registry
     */
    public function anticonvulsant(): void
    {
        $q = Request::get('q', '');
        $patients = JhcisGateway::getAnticonvulsantRegistryList($q ?: null);

        $freeSeizure = 0;
        $hlaTested = 0;
        $highAdherence = 0;

        foreach ($patients as $p) {
            if ($p['seizure_control'] === 'free_gt6m') $freeSeizure++;
            if ($p['hla_b1502'] === 'negative' || $p['hla_b1502'] === 'positive') $hlaTested++;
            if ($p['adherence'] === 'high') $highAdherence++;
        }

        View::render('safety/anticonvulsant', [
            'pageTitle' => 'ทะเบียนผู้รับยากันชัก (Anticonvulsant & TDM Registry)',
            'patients' => $patients,
            'query' => $q,
            'stats' => [
                'total' => count($patients),
                'free_seizure' => $freeSeizure,
                'hla_tested' => $hlaTested,
                'high_adherence' => $highAdherence
            ]
        ]);
    }

    /**
     * CKD & Renal Care Registry
     */
    public function ckd(): void
    {
        $q = Request::get('q', '');
        $patients = JhcisGateway::getCkdRegistryList($q ?: null);

        $stageCounts = ['Stage 1' => 0, 'Stage 2' => 0, 'Stage 3a' => 0, 'Stage 3b' => 0, 'Stage 4' => 0, 'Stage 5' => 0];
        $dialysisCount = 0;

        foreach ($patients as $p) {
            $st = $p['stage'] ?? 'Stage 3a';
            if (isset($stageCounts[$st])) $stageCounts[$st]++;
            if ($p['dialysis'] !== 'none') $dialysisCount++;
        }

        View::render('safety/ckd', [
            'pageTitle' => 'คลินิกผู้ป่วยโรคไตเรื้อรัง (CKD Care & Renal Safety)',
            'patients' => $patients,
            'query' => $q,
            'stats' => [
                'total' => count($patients),
                'stage_counts' => $stageCounts,
                'dialysis_count' => $dialysisCount
            ]
        ]);
    }

    /**
     * G6PD Deficiency Registry & High-Risk Med Alert
     */
    public function g6pd(): void
    {
        $q = Request::get('q', '');
        $patients = JhcisGateway::getG6pdRegistryList($q ?: null);

        $issuedCount = 0;
        $severeCount = 0;
        foreach ($patients as $p) {
            if ($p['g6pd_card_status'] === 'issued') $issuedCount++;
            if (str_contains($p['who_class'], 'Class II')) $severeCount++;
        }

        View::render('safety/g6pd', [
            'pageTitle' => 'ทะเบียนผู้ป่วยภาวะพร่องเอนไซม์ G6PD (G6PD Deficiency Registry)',
            'patients' => $patients,
            'query' => $q,
            'stats' => [
                'total' => count($patients),
                'card_issued' => $issuedCount,
                'severe_class2' => $severeCount
            ]
        ]);
    }

    /**
     * Village Health Volunteers (อสม.) Registry
     */
    public function vhv(): void
    {
        $q = Request::get('q', '');
        $vhvs = JhcisGateway::getVhvList($q ?: null);

        $totalHouses = 0;
        foreach ($vhvs as $v) {
            $totalHouses += (int)$v['house_count'];
        }

        View::render('safety/vhv', [
            'pageTitle' => 'ทำเนียบอาสาสมัครสาธารณสุขประจำหมู่บ้าน (อสม. ในระบบ JHCIS)',
            'vhvs' => $vhvs,
            'query' => $q,
            'stats' => [
                'total_vhv' => count($vhvs),
                'total_houses' => $totalHouses
            ]
        ]);
    }

    /**
     * Update CKD Registry Master/Clinical Data & Record Audit Log
     */
    public function updateCkd(): void
    {
        $id = (int)Request::post('id', 0);
        $pid = (int)Request::post('pid', 0);
        $ckdStage = trim(Request::post('ckd_stage', 'Stage 3a'));
        $egfr = (float)Request::post('latest_egfr', 0);
        $cr = (float)Request::post('latest_cr', 0);
        $dialysis = trim(Request::post('dialysis_status', 'none'));
        $alerts = trim(Request::post('nephrotoxic_alerts', ''));
        $notes = trim(Request::post('notes', ''));

        if ($id <= 0 && $pid <= 0) {
            Session::flash('error', 'ไม่พบข้อมูลผู้ป่วยโรคไตที่ต้องการแก้ไข');
            Response::redirect('/pcc/safety/ckd');
            return;
        }

        $db = Database::getAppDb();
        try {
            // Fetch before state
            $stmtBefore = $db->prepare("SELECT * FROM pcu_ckd_registry WHERE id = :id OR pid = :pid LIMIT 1");
            $stmtBefore->execute([':id' => $id, ':pid' => $pid]);
            $before = $stmtBefore->fetch(\PDO::FETCH_ASSOC);

            if ($before) {
                $actualId = (int)$before['id'];
                $actualPid = (int)$before['pid'];

                $stmt = $db->prepare("
                    UPDATE pcu_ckd_registry SET
                        ckd_stage = :st,
                        latest_egfr = :egfr,
                        latest_cr = :cr,
                        dialysis_status = :dial,
                        nephrotoxic_alerts = :alerts,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':st' => $ckdStage,
                    ':egfr' => $egfr,
                    ':cr' => $cr,
                    ':dial' => $dialysis,
                    ':alerts' => $alerts,
                    ':notes' => $notes,
                    ':id' => $actualId
                ]);

                // Fetch after state
                $stmtAfter = $db->prepare("SELECT * FROM pcu_ckd_registry WHERE id = :id");
                $stmtAfter->execute([':id' => $actualId]);
                $after = $stmtAfter->fetch(\PDO::FETCH_ASSOC);

                Audit::log(
                    'CKD_REGISTRY_UPDATED',
                    'pcu_ckd_registry',
                    (string)$actualId,
                    $actualPid,
                    $before,
                    $after,
                    "ปรับปรุงข้อมูลคลินิกโรคไตเรื้อรัง PID: {$actualPid} ({$ckdStage}, eGFR: {$egfr})"
                );

                Session::flash('success', "บันทึกข้อมูลคลินิกโรคไต PID [{$actualPid}] และบันทึกประวัติ Log เรียบร้อยแล้ว");
            } else {
                Session::flash('error', 'ไม่พบรายการในทะเบียนโรคไต');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' . $e->getMessage());
        }

        Response::redirect('/pcc/safety/ckd');
    }

    /**
     * Update Anticonvulsant Registry Data & Record Audit Log
     */
    public function updateAnticonvulsant(): void
    {
        $id = (int)Request::post('id', 0);
        $pid = (int)Request::post('pid', 0);
        $dailyDose = trim(Request::post('daily_dose', ''));
        $indication = trim(Request::post('indication', ''));
        $seizureControl = trim(Request::post('seizure_control', 'free_gt6m'));
        $hla = trim(Request::post('hla_b1502_status', 'not_tested'));
        $tdm = (float)Request::post('latest_tdm_level', 0);
        $adherence = trim(Request::post('adherence_score', 'high'));
        $notes = trim(Request::post('notes', ''));

        if ($id <= 0 && $pid <= 0) {
            Session::flash('error', 'ไม่พบข้อมูลผู้รับยากันชักที่ต้องการแก้ไข');
            Response::redirect('/pcc/safety/anticonvulsant');
            return;
        }

        $db = Database::getAppDb();
        try {
            $stmtBefore = $db->prepare("SELECT * FROM pcu_anticonvulsant_registry WHERE id = :id OR pid = :pid LIMIT 1");
            $stmtBefore->execute([':id' => $id, ':pid' => $pid]);
            $before = $stmtBefore->fetch(\PDO::FETCH_ASSOC);

            if ($before) {
                $actualId = (int)$before['id'];
                $actualPid = (int)$before['pid'];

                $stmt = $db->prepare("
                    UPDATE pcu_anticonvulsant_registry SET
                        daily_dose = :dose,
                        indication = :ind,
                        seizure_control = :ctrl,
                        hla_b1502_status = :hla,
                        latest_tdm_level = :tdm,
                        adherence_score = :adh,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':dose' => $dailyDose,
                    ':ind' => $indication,
                    ':ctrl' => $seizureControl,
                    ':hla' => $hla,
                    ':tdm' => $tdm,
                    ':adh' => $adherence,
                    ':notes' => $notes,
                    ':id' => $actualId
                ]);

                $stmtAfter = $db->prepare("SELECT * FROM pcu_anticonvulsant_registry WHERE id = :id");
                $stmtAfter->execute([':id' => $actualId]);
                $after = $stmtAfter->fetch(\PDO::FETCH_ASSOC);

                Audit::log(
                    'ANTICONVULSANT_REGISTRY_UPDATED',
                    'pcu_anticonvulsant_registry',
                    (string)$actualId,
                    $actualPid,
                    $before,
                    $after,
                    "ปรับปรุงข้อมูลผู้รับยากันชัก PID: {$actualPid} (Seizure Control: {$seizureControl}, TDM: {$tdm})"
                );

                Session::flash('success', "บันทึกข้อมูลคลินิกยากันชัก PID [{$actualPid}] และบันทึกประวัติ Log เรียบร้อยแล้ว");
            } else {
                Session::flash('error', 'ไม่พบรายการในทะเบียนยากันชัก');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' . $e->getMessage());
        }

        Response::redirect('/pcc/safety/anticonvulsant');
    }

    /**
     * Update Allergy Clinical Safety Note & Record Audit Log
     */
    public function updateAllergyNote(): void
    {
        $pid = (int)Request::post('pid', 0);
        $drugcode = trim(Request::post('drugcode', ''));
        $clinicalNote = trim(Request::post('clinical_note', ''));
        $severity = trim(Request::post('levelalergic', '1'));

        if ($pid <= 0) {
            Session::flash('error', 'ไม่พบรหัสผู้รับบริการ');
            Response::redirect('/pcc/safety/allergies');
            return;
        }

        Audit::log(
            'ALLERGY_NOTE_UPDATED',
            'personalergic',
            $drugcode,
            $pid,
            null,
            ['drugcode' => $drugcode, 'level' => $severity, 'note' => $clinicalNote],
            "บันทึก/ปรับปรุงข้อมูลเฝ้าระวังความปลอดภัยการแพ้ยา PID: {$pid} (รหัสยา: {$drugcode})"
        );

        Session::flash('success', "บันทึกข้อมูลเฝ้าระวังการแพ้ยา PID [{$pid}] และบันทึกประวัติ Log เรียบร้อยแล้ว");
        Response::redirect('/pcc/safety/allergies');
    }
}

