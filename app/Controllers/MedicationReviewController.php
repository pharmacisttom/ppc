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

class MedicationReviewController
{
    /**
     * List all reviews
     */
    public function index(): void
    {
        $db = Database::getAppDb();
        $reviews = $db->query("
            SELECT r.*, u.firstname, u.lastname,
                   (SELECT COUNT(*) FROM medication_review_problems p WHERE p.review_id = r.review_id) as problem_count
            FROM medication_reviews r
            JOIN users u ON r.reviewer_id = u.user_id
            ORDER BY r.review_date DESC, r.review_id DESC
        ")->fetchAll();

        View::render('review/index', [
            'pageTitle' => 'การทบทวนวรรณกรรมยาและการค้นหา DRP (Medication Review)',
            'reviews' => $reviews,
            'isTraining' => JhcisGateway::isTrainingMode()
        ]);
    }

    /**
     * Show Create Medication Review Form
     */
    public function create(): void
    {
        $pid = (int)Request::get('pid', 101);
        $patient = JhcisGateway::getPatient($pid);

        if (!$patient) {
            Session::flash('error', 'ไม่พบข้อมูลผู้ป่วย');
            Response::redirect('/hos/patients');
        }

        $allergies = JhcisGateway::getPatientAllergies($pid);
        $chronicConditions = JhcisGateway::getPatientChronicDiseases($pid);
        $medications = $patient['current_medications'] ?? [];

        View::render('review/create', [
            'pageTitle' => 'จัดทำบันทึก Medication Review & DRP: ' . $patient['full_name'],
            'patient' => $patient,
            'allergies' => $allergies,
            'chronicConditions' => $chronicConditions,
            'medications' => $medications
        ]);
    }

    /**
     * Store Review and Problems in Application DB
     */
    public function store(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        $pid = (int)Request::post('patient_pid');
        $reviewType = Request::post('review_type', 'routine');
        $reviewDate = Request::post('review_date', date('Y-m-d'));
        $clinicalSummary = Request::post('clinical_summary', '');
        $status = Request::post('status', 'completed');

        // Problems array from dynamic form
        $drugCodes = Request::post('drug_code', []);
        $drugNames = Request::post('drug_name', []);
        $categories = Request::post('drp_category', []);
        $descriptions = Request::post('problem_description', []);
        $recommendations = Request::post('recommendation', []);
        $prescriberResponses = Request::post('prescriber_response', []);

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO medication_reviews (
                    facility_id, patient_pid, patient_cid_hash, review_date, 
                    reviewer_id, review_type, total_medications, clinical_summary, status
                ) VALUES (
                    :facility_id, :pid, :cid_hash, :review_date, 
                    :reviewer_id, :review_type, :total_meds, :summary, :status
                )
            ");

            $cidHash = hash('sha256', (string)$pid);
            $totalMeds = count($drugNames);

            $stmt->execute([
                ':facility_id' => $user['facility_id'] ?? 1,
                ':pid' => $pid,
                ':cid_hash' => $cidHash,
                ':review_date' => $reviewDate,
                ':reviewer_id' => $user['user_id'] ?? 1,
                ':review_type' => $reviewType,
                ':total_meds' => $totalMeds,
                ':summary' => $clinicalSummary,
                ':status' => $status
            ]);

            $reviewId = (int)$db->lastInsertId();

            // Insert each DRP
            $stmtProb = $db->prepare("
                INSERT INTO medication_review_problems (
                    review_id, drug_code, drug_name, drp_category, 
                    problem_description, assessment, recommendation, 
                    prescriber_response, outcome
                ) VALUES (
                    :review_id, :drug_code, :drug_name, :drp_category, 
                    :problem_desc, :assessment, :recommendation, 
                    :prescriber_response, 'resolved'
                )
            ");

            if (!empty($drugNames) && is_array($drugNames)) {
                for ($i = 0; $i < count($drugNames); $i++) {
                    if (empty($drugNames[$i])) continue;
                    $stmtProb->execute([
                        ':review_id' => $reviewId,
                        ':drug_code' => $drugCodes[$i] ?? null,
                        ':drug_name' => $drugNames[$i],
                        ':drp_category' => $categories[$i] ?? 'other',
                        ':problem_desc' => $descriptions[$i] ?? 'พบปัญหาจากการใช้ยา',
                        ':assessment' => 'ประเมินความเสี่ยงและผลกระทบทางคลินิก',
                        ':recommendation' => $recommendations[$i] ?? 'ทบทวนและปรับแผนการใช้ยา',
                        ':prescriber_response' => $prescriberResponses[$i] ?? 'accepted_fully'
                    ]);
                }
            }

            $db->commit();
            Audit::log('REVIEW_CREATE', 'review', (string)$reviewId, $pid, null, null, 'Created structured medication review with DRPs');

            Session::flash('success', 'บันทึกการทำ Medication Review และปัญหา DRP สำเร็จ');
            Response::redirect('/hos/patients/' . $pid);
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Medication review store error: " . $e->getMessage());
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
            Response::redirect('/hos/reviews/create?pid=' . $pid);
        }
    }

    /**
     * View Review Details
     */
    public function show(string $id): void
    {
        $db = Database::getAppDb();
        $stmt = $db->prepare("
            SELECT r.*, u.firstname, u.lastname, u.license_number
            FROM medication_reviews r
            JOIN users u ON r.reviewer_id = u.user_id
            WHERE r.review_id = :id
        ");
        $stmt->execute([':id' => (int)$id]);
        $review = $stmt->fetch();

        if (!$review) {
            Session::flash('error', 'ไม่พบรายการทบทวนยา');
            Response::redirect('/hos/reviews');
        }

        $stmtProb = $db->prepare("SELECT * FROM medication_review_problems WHERE review_id = :id");
        $stmtProb->execute([':id' => (int)$id]);
        $problems = $stmtProb->fetchAll();

        $patient = JhcisGateway::getPatient((int)$review['patient_pid']);

        View::render('review/show', [
            'pageTitle' => 'รายละเอียดการทบทวนยา #' . $review['review_id'],
            'review' => $review,
            'problems' => $problems,
            'patient' => $patient
        ]);
    }
}
