<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class QualityController
{
    /**
     * Standard Self-Assessment & Readiness Overview
     */
    public function index(): void
    {
        $db = Database::getAppDb();

        // 1. Fetch Active Standard (พ.ศ. 2568-2570)
        $standard = $db->query("SELECT * FROM quality_standards WHERE is_active = 1 LIMIT 1")->fetch();

        // 2. Fetch Categories with their Criteria and Assessment Status
        $stmtCats = $db->prepare("
            SELECT qc.* 
            FROM quality_categories qc 
            WHERE qc.standard_id = :sid 
            ORDER BY qc.ordering ASC
        ");
        $stmtCats->execute([':sid' => $standard['standard_id'] ?? 1]);
        $categories = $stmtCats->fetchAll();

        $stmtCriteria = $db->prepare("
            SELECT cr.*, 
                   qa.status as assessment_status, 
                   qa.score_achieved, 
                   qa.gap_identified,
                   qa.corrective_action_plan,
                   (SELECT COUNT(*) FROM evidence_criterion_mapping ecm WHERE ecm.criterion_id = cr.criterion_id) as evidence_count
            FROM quality_criteria cr
            LEFT JOIN quality_assessments qa ON cr.criterion_id = qa.criterion_id
            WHERE cr.is_active = 1
            ORDER BY cr.category_id ASC, cr.criterion_code ASC
        ");
        $stmtCriteria->execute();
        $allCriteria = $stmtCriteria->fetchAll();

        // Group criteria by category
        $grouped = [];
        $readiness = [
            'total' => count($allCriteria),
            'ready' => 0,
            'evidence_missing' => 0,
            'in_progress' => 0,
            'not_assessed' => 0,
            'need_improvement' => 0
        ];

        foreach ($allCriteria as $c) {
            $catId = $c['category_id'];
            $grouped[$catId][] = $c;

            $status = $c['assessment_status'] ?? 'not_assessed';
            if ($status === 'ready' || $status === 'passed_internal_review') $readiness['ready']++;
            elseif ($status === 'evidence_missing') $readiness['evidence_missing']++;
            elseif ($status === 'in_progress') $readiness['in_progress']++;
            elseif ($status === 'need_improvement') $readiness['need_improvement']++;
            else $readiness['not_assessed']++;
        }

        View::render('quality/index', [
            'pageTitle' => 'การประเมินตนเองตามมาตรฐานหน่วยบริการปฐมภูมิ (พ.ศ. 2568–2570)',
            'standard' => $standard,
            'categories' => $categories,
            'groupedCriteria' => $grouped,
            'readiness' => $readiness
        ]);
    }

    /**
     * Update Criterion Assessment Status & Gap
     */
    public function updateAssessment(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        $criterionId = (int)Request::post('criterion_id');
        $status = Request::post('status', 'in_progress');
        $score = (float)Request::post('score_achieved', 100);
        $gap = Request::post('gap_identified', '');
        $capa = Request::post('corrective_action_plan', '');

        try {
            $stmt = $db->prepare("
                INSERT INTO quality_assessments (
                    facility_id, criterion_id, assessment_year, status, 
                    score_achieved, gap_identified, corrective_action_plan, assessed_by, assessed_at
                ) VALUES (
                    :facility_id, :criterion_id, 2568, :status, 
                    :score, :gap, :capa, :assessed_by, NOW()
                )
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    score_achieved = VALUES(score_achieved),
                    gap_identified = VALUES(gap_identified),
                    corrective_action_plan = VALUES(corrective_action_plan),
                    assessed_by = VALUES(assessed_by),
                    assessed_at = NOW()
            ");

            $stmt->execute([
                ':facility_id' => $user['facility_id'] ?? 1,
                ':criterion_id' => $criterionId,
                ':status' => $status,
                ':score' => $score,
                ':gap' => $gap,
                ':capa' => $capa,
                ':assessed_by' => $user['user_id'] ?? 1
            ]);

            Audit::log('QUALITY_ASSESS', 'quality', (string)$criterionId, null, null, null, "Updated criterion {$criterionId} to status: {$status}");

            Session::flash('success', 'บันทึกการประเมินความพร้อมตามมาตรฐานเรียบร้อยแล้ว');
            Response::redirect('/hos/quality');
        } catch (\Exception $e) {
            error_log("Assessment update error: " . $e->getMessage());
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
            Response::redirect('/hos/quality');
        }
    }

    /**
     * Evidence Center Repository
     */
    public function evidence(): void
    {
        $db = Database::getAppDb();

        $evidences = $db->query("
            SELECT e.*, u.firstname, u.lastname,
                   GROUP_CONCAT(c.criterion_code SEPARATOR ', ') as linked_criteria
            FROM quality_evidence e
            JOIN users u ON e.uploaded_by = u.user_id
            LEFT JOIN evidence_criterion_mapping ecm ON e.evidence_id = ecm.evidence_id
            LEFT JOIN quality_criteria c ON ecm.criterion_id = c.criterion_id
            GROUP BY e.evidence_id
            ORDER BY e.created_at DESC
        ")->fetchAll();

        $criteria = $db->query("SELECT criterion_id, criterion_code, criterion_name FROM quality_criteria ORDER BY criterion_code")->fetchAll();

        View::render('quality/evidence', [
            'pageTitle' => 'ศูนย์รวมหลักฐานเชิงประจักษ์ (Evidence Center)',
            'evidences' => $evidences,
            'criteria' => $criteria
        ]);
    }
}
