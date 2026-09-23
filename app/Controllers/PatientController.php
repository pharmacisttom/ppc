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
use App\Services\SafetyEngine;

class PatientController
{
    /**
     * Patient Search View
     */
    public function index(): void
    {
        $q = Request::get('q', '');
        $patients = [];
        if (!empty($q)) {
            $patients = JhcisGateway::searchPatients($q);
            Audit::log('PATIENT_SEARCH', 'patient', null, null, null, null, "Searched query: {$q}");
        }

        View::render('patient/index', [
            'pageTitle' => 'ค้นหาผู้รับบริการ — PCU Smart Pharmacy',
            'query' => $q,
            'patients' => $patients,
            'isTraining' => JhcisGateway::isTrainingMode()
        ]);
    }

    /**
     * Patient Pharmaceutical Profile
     */
    public function profile(string $pid): void
    {
        $pidInt = (int)$pid;
        $patient = JhcisGateway::getPatient($pidInt);

        if (!$patient) {
            Session::flash('error', 'ไม่พบข้อมูลผู้รับบริการรหัส PID: ' . htmlspecialchars($pid));
            Response::redirect('/hos/patients');
        }

        // Audit view for PDPA compliance
        Audit::log('PATIENT_VIEW', 'patient', (string)$pidInt, $pidInt, null, null, 'Viewed Patient Pharmaceutical Profile');

        // Fetch clinical data via Gateway
        $allergies = JhcisGateway::getPatientAllergies($pidInt);
        $chronicConditions = JhcisGateway::getPatientChronicDiseases($pidInt);
        $timeline = JhcisGateway::getPatientMedicationTimeline($pidInt);

        // Get latest active medications
        $currentMedications = $patient['current_medications'] ?? [];
        if (empty($currentMedications) && !empty($timeline)) {
            $currentMedications = $timeline[0]['medications'] ?? [];
        }

        // Run Medication Safety Decision Support Screening Engine
        $safetyFlags = SafetyEngine::screenPatient($patient, $allergies, $chronicConditions, $currentMedications);

        // Fetch past Medication Reviews from Application Database
        $db = Database::getAppDb();
        $stmtRev = $db->prepare("
            SELECT r.*, u.firstname, u.lastname,
                   (SELECT COUNT(*) FROM medication_review_problems p WHERE p.review_id = r.review_id) as problem_count
            FROM medication_reviews r
            JOIN users u ON r.reviewer_id = u.user_id
            WHERE r.patient_pid = :pid
            ORDER BY r.review_date DESC
        ");
        $stmtRev->execute([':pid' => $pidInt]);
        $reviews = $stmtRev->fetchAll();

        // Fetch past HMRs
        $stmtHmr = $db->prepare("
            SELECT h.*, u.firstname, u.lastname
            FROM home_medication_reviews h
            JOIN users u ON h.visitor_id = u.user_id
            WHERE h.patient_pid = :pid
            ORDER BY h.visit_date DESC
        ");
        $stmtHmr->execute([':pid' => $pidInt]);
        $hmrs = $stmtHmr->fetchAll();

        View::render('patient/profile', [
            'pageTitle' => 'Pharmaceutical Profile: ' . $patient['full_name'],
            'patient' => $patient,
            'allergies' => $allergies,
            'chronicConditions' => $chronicConditions,
            'timeline' => $timeline,
            'currentMedications' => $currentMedications,
            'safetyFlags' => $safetyFlags,
            'reviews' => $reviews,
            'hmrs' => $hmrs,
            'isTraining' => JhcisGateway::isTrainingMode()
        ]);
    }
}
