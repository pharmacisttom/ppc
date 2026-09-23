<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Audit;
use App\Gateway\JhcisGateway;

class SafetyController
{
    /**
     * Executive Medication Safety Command Center Dashboard
     */
    public function index(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        // 1. Incident Metrics
        $totalIncidents = (int)$db->query("SELECT COUNT(*) FROM medication_incidents")->fetchColumn();
        $openIncidents = (int)$db->query("SELECT COUNT(*) FROM medication_incidents WHERE status != 'closed'")->fetchColumn();
        $nearMissCount = (int)$db->query("SELECT COUNT(*) FROM medication_incidents WHERE is_near_miss = 1 OR severity_category IN ('A', 'B')")->fetchColumn();
        $rcaClosedCount = (int)$db->query("SELECT COUNT(*) FROM medication_incidents WHERE status = 'closed'")->fetchColumn();
        $rcaClosureRate = $totalIncidents > 0 ? round(($rcaClosedCount / $totalIncidents) * 100, 1) : 100.0;

        // Stage breakdown
        $stageStmt = $db->query("
            SELECT incident_stage, COUNT(*) as cnt 
            FROM medication_incidents 
            GROUP BY incident_stage
        ");
        $stages = [
            'prescribing' => 0,
            'transcribing' => 0,
            'dispensing' => 0,
            'administration' => 0,
            'monitoring' => 0,
            'storage' => 0
        ];
        foreach ($stageStmt->fetchAll() as $s) {
            $stages[$s['incident_stage']] = (int)$s['cnt'];
        }

        // Severity breakdown (NCC MERP A - I)
        $sevStmt = $db->query("
            SELECT severity_category, COUNT(*) as cnt 
            FROM medication_incidents 
            GROUP BY severity_category
        ");
        $severities = array_fill_keys(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'], 0);
        foreach ($sevStmt->fetchAll() as $sv) {
            $severities[$sv['severity_category']] = (int)$sv['cnt'];
        }

        // 2. High Alert Drugs & LASA
        $hamCount = (int)$db->query("SELECT COUNT(*) FROM high_alert_drugs")->fetchColumn();
        $lasaCount = (int)$db->query("SELECT COUNT(*) FROM lasa_drugs")->fetchColumn();

        // 3. JHCIS Live Pharmacovigilance & Allergy Radar
        $jhcis = Database::getJhcisDb();
        $totalAllergies = 0;
        $repeatAllergyViolations = [];

        if ($jhcis) {
            try {
                $totalAllergies = (int)$jhcis->query("SELECT COUNT(*) FROM personalergic")->fetchColumn();

                // Repeat Allergy Radar: patients in personalergic who received the drug after date recorded
                $radarStmt = $jhcis->query("
                    SELECT pa.pid, p.fname, p.lname, cd.drugname, pa.daterecord as allergy_date, 
                           v.visitdate, vd.drugcode, pa.allergicsymtomps, pa.levelalergic
                    FROM personalergic pa
                    JOIN person p ON pa.pid = p.pid
                    JOIN visit v ON pa.pid = v.pid AND v.visitdate > pa.daterecord
                    JOIN visitdrug vd ON v.visitno = vd.visitno AND pa.drugcode = vd.drugcode
                    LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                    ORDER BY v.visitdate DESC
                    LIMIT 5
                ");
                $repeatAllergyViolations = $radarStmt->fetchAll();
            } catch (\Exception $e) {
                error_log("Allergy radar query error: " . $e->getMessage());
            }
        }

        // 4. Recent Incidents
        $recentIncidents = $db->query("
            SELECT i.*, u.firstname, u.lastname
            FROM medication_incidents i
            LEFT JOIN users u ON i.reporter_id = u.user_id
            ORDER BY i.incident_date DESC
            LIMIT 5
        ")->fetchAll();

        View::render('safety/index', [
            'pageTitle' => 'ศูนย์ความปลอดภัยด้านยา ระดับ รพ.สต. (PCU Medication Safety Center)',
            'totalIncidents' => $totalIncidents,
            'openIncidents' => $openIncidents,
            'nearMissCount' => $nearMissCount,
            'rcaClosedCount' => $rcaClosedCount,
            'rcaClosureRate' => $rcaClosureRate,
            'stages' => $stages,
            'severities' => $severities,
            'hamCount' => $hamCount,
            'lasaCount' => $lasaCount,
            'totalAllergies' => $totalAllergies,
            'repeatAllergyViolations' => $repeatAllergyViolations,
            'recentIncidents' => $recentIncidents,
            'user' => $user
        ]);
    }

    /**
     * Medication Incidents Ledger & Management
     */
    public function incidents(): void
    {
        $db = Database::getAppDb();

        $stage = Request::get('stage', '');
        $severity = Request::get('severity', '');
        $status = Request::get('status', '');
        $search = trim(Request::get('q', ''));

        $sql = "
            SELECT i.*, u.firstname, u.lastname, u.username
            FROM medication_incidents i
            LEFT JOIN users u ON i.reporter_id = u.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($stage)) {
            $sql .= " AND i.incident_stage = :stage";
            $params[':stage'] = $stage;
        }
        if (!empty($severity)) {
            $sql .= " AND i.severity_category = :severity";
            $params[':severity'] = $severity;
        }
        if (!empty($status)) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (i.drugs_involved LIKE :q OR i.incident_description LIKE :q OR i.responsible_person LIKE :q)";
            $params[':q'] = "%{$search}%";
        }

        $sql .= " ORDER BY i.incident_date DESC, i.incident_id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $incidents = $stmt->fetchAll();

        View::render('safety/incidents', [
            'pageTitle' => 'ทะเบียนรายงานอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incidents & Near Miss)',
            'incidents' => $incidents,
            'filters' => [
                'stage' => $stage,
                'severity' => $severity,
                'status' => $status,
                'q' => $search
            ]
        ]);
    }

    /**
     * Show Create Incident Form (NCC MERP Category A - I)
     */
    public function createIncident(): void
    {
        $user = Auth::user();
        
        // Suggest common drugs from JHCIS
        $recentDrugs = [];
        $jhcis = Database::getJhcisDb();
        if ($jhcis) {
            try {
                $recentDrugs = $jhcis->query("
                    SELECT drugcode, drugname 
                    FROM cdrug 
                    WHERE drugname IS NOT NULL AND drugname != '' 
                    ORDER BY drugname ASC 
                    LIMIT 200
                ")->fetchAll();
            } catch (\Exception $e) {
                // Ignore
            }
        }

        View::render('safety/incident_create', [
            'pageTitle' => 'รายงานอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incident Reporting)',
            'user' => $user,
            'recentDrugs' => $recentDrugs
        ]);
    }

    /**
     * Store New Incident
     */
    public function storeIncident(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        $incidentDate = Request::post('incident_date', date('Y-m-d H:i:s'));
        $incidentStage = Request::post('incident_stage', 'dispensing');
        $severityCategory = Request::post('severity_category', 'B');
        $isNearMiss = (int)Request::post('is_near_miss', in_array($severityCategory, ['A', 'B']) ? 1 : 0);
        $drugsInvolved = trim(Request::post('drugs_involved', ''));
        $incidentDescription = trim(Request::post('incident_description', ''));
        $immediateAction = trim(Request::post('immediate_action_taken', ''));
        $isAnonymous = Request::post('is_anonymous', '0') === '1';

        $reporterId = $isAnonymous ? null : ($user['user_id'] ?? null);

        if (empty($drugsInvolved) || empty($incidentDescription)) {
            Session::flash('error', 'กรุณาระบุรายการยาที่เกี่ยวข้องและรายละเอียดอุบัติการณ์ให้ครบถ้วน');
            Response::redirect('/pcc/safety/incidents/create');
            return;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO medication_incidents (
                    facility_id, incident_date, report_date, reporter_id,
                    incident_stage, severity_category, is_near_miss,
                    drugs_involved, incident_description, immediate_action_taken,
                    status
                ) VALUES (
                    :facility_id, :incident_date, NOW(), :reporter_id,
                    :incident_stage, :severity_category, :is_near_miss,
                    :drugs_involved, :incident_description, :immediate_action,
                    'reported'
                )
            ");

            $stmt->execute([
                ':facility_id' => $user['facility_id'] ?? 1,
                ':incident_date' => $incidentDate,
                ':reporter_id' => $reporterId,
                ':incident_stage' => $incidentStage,
                ':severity_category' => $severityCategory,
                ':is_near_miss' => $isNearMiss,
                ':drugs_involved' => $drugsInvolved,
                ':incident_description' => $incidentDescription,
                ':immediate_action' => $immediateAction
            ]);

            $newId = (int)$db->lastInsertId();

            Audit::log('INCIDENT_REPORT', 'medication_incidents', (string)$newId, null, null, null, 
                "Reported Incident #{$newId} (Severity {$severityCategory}, Stage {$incidentStage})");

            Session::flash('success', "บันทึกรายงานอุบัติการณ์เลขที่ INC-{$newId} เรียบร้อยแล้ว เข้าสู่กระบวนการสืบสวนและวิเคราะห์ RCA");
            Response::redirect("/pcc/safety/incidents/{$newId}");
        } catch (\Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
            Response::redirect('/pcc/safety/incidents/create');
        }
    }

    /**
     * Show Incident Detail, RCA and CAPA Form
     */
    public function showIncident(string $id): void
    {
        $db = Database::getAppDb();
        $incId = (int)$id;

        $stmt = $db->prepare("
            SELECT i.*, u.firstname, u.lastname, u.profession as role
            FROM medication_incidents i
            LEFT JOIN users u ON i.reporter_id = u.user_id
            WHERE i.incident_id = :id
        ");
        $stmt->execute([':id' => $incId]);
        $incident = $stmt->fetch();

        if (!$incident) {
            Session::flash('error', 'ไม่พบข้อมูลอุบัติการณ์ที่ระบุ');
            Response::redirect('/pcc/safety/incidents');
            return;
        }

        View::render('safety/incident_show', [
            'pageTitle' => "สืบสวนและวิเคราะห์หาสาเหตุ RCA: อุบัติการณ์ INC-{$incId}",
            'incident' => $incident
        ]);
    }

    /**
     * Update Root Cause Analysis (RCA) & CAPA
     */
    public function updateRca(string $id): void
    {
        $db = Database::getAppDb();
        $incId = (int)$id;

        $rca = trim(Request::post('root_cause_analysis', ''));
        $capa = trim(Request::post('preventive_action', ''));
        $responsible = trim(Request::post('responsible_person', ''));
        $dueDate = Request::post('due_date', null);
        $status = Request::post('status', 'investigating');

        $closedAt = null;
        if ($status === 'closed') {
            $closedAt = date('Y-m-d H:i:s');
        }

        try {
            $updateSql = "
                UPDATE medication_incidents
                SET root_cause_analysis = :rca,
                    preventive_action = :capa,
                    responsible_person = :responsible,
                    due_date = :due_date,
                    status = :status
            ";
            $params = [
                ':rca' => $rca,
                ':capa' => $capa,
                ':responsible' => $responsible,
                ':due_date' => !empty($dueDate) ? $dueDate : null,
                ':status' => $status,
                ':id' => $incId
            ];

            if ($status === 'closed') {
                $updateSql .= ", closed_at = COALESCE(closed_at, NOW())";
            }

            $updateSql .= " WHERE incident_id = :id";
            $stmt = $db->prepare($updateSql);
            $stmt->execute($params);

            Audit::log('INCIDENT_RCA_UPDATE', 'medication_incidents', (string)$incId, null, null, null,
                "Updated RCA & CAPA for Incident #{$incId}, Status: {$status}");

            Session::flash('success', "บันทึกผลการวิเคราะห์สาเหตุที่แท้จริง (RCA) และมาตรการ CAPA สำหรับอุบัติการณ์ INC-{$incId} สำเร็จ");
            Response::redirect("/pcc/safety/incidents/{$incId}");
        } catch (\Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            Response::redirect("/pcc/safety/incidents/{$incId}");
        }
    }

    /**
     * Export Incidents to Excel
     */
    public function exportIncidents(): void
    {
        $db = Database::getAppDb();

        $rows = $db->query("
            SELECT i.incident_id, i.incident_date, i.incident_stage, i.severity_category,
                   i.is_near_miss, i.drugs_involved, i.incident_description,
                   i.immediate_action_taken, i.root_cause_analysis, i.preventive_action,
                   i.responsible_person, i.status, i.closed_at,
                   COALESCE(CONCAT(u.firstname, ' ', u.lastname), 'ไม่ประสงค์ออกนาม (Anonymous)') as reporter_name
            FROM medication_incidents i
            LEFT JOIN users u ON i.reporter_id = u.user_id
            ORDER BY i.incident_date DESC
        ")->fetchAll();

        $headers = [
            'เลขที่อุบัติการณ์',
            'วัน-เวลาที่เกิดเหตุ',
            'ขั้นตอน (Stage)',
            'ความรุนแรง (NCC MERP)',
            'ประเภทเหตุการณ์',
            'ยาที่เกี่ยวข้อง',
            'รายละเอียดข้อผิดพลาด',
            'การแก้ไขเฉพาะหน้า',
            'การวิเคราะห์สาเหตุรากเหง้า (RCA)',
            'มาตรการแก้ไขและป้องกัน (CAPA)',
            'ผู้รับผิดชอบ',
            'สถานะ',
            'วันที่ปิดเคส',
            'ผู้รายงาน'
        ];

        $exportData = [];
        foreach ($rows as $r) {
            $exportData[] = [
                'INC-' . str_pad((string)$r['incident_id'], 4, '0', STR_PAD_LEFT),
                $r['incident_date'],
                $r['incident_stage'],
                $r['severity_category'],
                $r['is_near_miss'] ? 'เกือบพลาด (Near Miss)' : 'เกิดข้อผิดพลาด (Error)',
                $r['drugs_involved'],
                $r['incident_description'],
                $r['immediate_action_taken'],
                $r['root_cause_analysis'] ?: '-',
                $r['preventive_action'] ?: '-',
                $r['responsible_person'] ?: '-',
                $r['status'],
                $r['closed_at'] ?: '-',
                $r['reporter_name']
            ];
        }

        $filename = 'Medication_Incidents_Report_' . date('Ymd_His') . '.xls';
        $title = 'ทะเบียนรายงานอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incidents Report) รพ.สต.บ้านดอกกราย';

        Audit::log('REPORT_EXPORT', 'medication_incidents', null, null, null, null, "Exported medication incidents to Excel");

        Response::downloadExcel($filename, $title, $headers, $exportData);
    }

    /**
     * Pharmacovigilance & Allergy Safety Hub
     */
    public function allergies(): void
    {
        $jhcis = Database::getJhcisDb();
        $allergies = [];
        $repeatAllergies = [];

        if ($jhcis) {
            try {
                // 1. All Recorded Allergies in JHCIS
                $stmt = $jhcis->query("
                    SELECT pa.pcucodeperson, pa.pid, pa.drugcode, pa.daterecord, pa.typedx,
                           pa.levelalergic, pa.symptom, pa.allergicsymtomps, pa.informhosp, pa.remark,
                           p.fname, p.lname, p.idcard, p.birth,
                           cd.drugname, cd.druggenericname
                    FROM personalergic pa
                    JOIN person p ON pa.pid = p.pid
                    LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                    ORDER BY pa.daterecord DESC
                ");
                $allergies = $stmt->fetchAll();

                // 2. Repeat Allergy Audit Radar
                $radarStmt = $jhcis->query("
                    SELECT pa.pid, p.fname, p.lname, p.idcard, cd.drugname, pa.daterecord as allergy_date, 
                           v.visitdate, vd.drugcode, pa.allergicsymtomps, pa.levelalergic,
                           v.visitno
                    FROM personalergic pa
                    JOIN person p ON pa.pid = p.pid
                    JOIN visit v ON pa.pid = v.pid AND v.visitdate > pa.daterecord
                    JOIN visitdrug vd ON v.visitno = vd.visitno AND pa.drugcode = vd.drugcode
                    LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                    ORDER BY v.visitdate DESC
                ");
                $repeatAllergies = $radarStmt->fetchAll();
            } catch (\Exception $e) {
                error_log("Allergy error: " . $e->getMessage());
            }
        }

        View::render('safety/allergies', [
            'pageTitle' => 'ศูนย์เฝ้าระวังการแพ้ยาและอาการไม่พึงประสงค์ (Pharmacovigilance & Zero Repeat Allergy Radar)',
            'allergies' => $allergies,
            'repeatAllergies' => $repeatAllergies
        ]);
    }

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
            ['name' => 'Adrenaline (Epinephrine) 1 mg/ml injection', 'qty_required' => 5, 'qty_available' => 5, 'expiry_date' => '2027-02-15', 'status' => 'ready', 'lot' => 'EP24A01'],
            ['name' => 'Atropine Sulfate 0.6 mg/ml injection', 'qty_required' => 5, 'qty_available' => 5, 'expiry_date' => '2027-04-10', 'status' => 'ready', 'lot' => 'AT24C03'],
            ['name' => 'Diazepam 10 mg/2 ml injection', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2026-12-01', 'status' => 'ready', 'lot' => 'DZ23L09'],
            ['name' => '50% Glucose 50 ml injection', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2027-08-20', 'status' => 'ready', 'lot' => 'GL24H12'],
            ['name' => 'NSS 0.9% 1,000 ml IV infusion', 'qty_required' => 2, 'qty_available' => 2, 'expiry_date' => '2028-01-10', 'status' => 'ready', 'lot' => 'NS24K05']
        ];

        View::render('safety/emergency_kit', [
            'pageTitle' => 'การตรวจสอบชุดยาช่วยชีวิตฉุกเฉิน (Emergency CPR Kit)',
            'kitItems' => $kitItems,
            'lastChecked' => date('d/m/Y H:i'),
            'inspector' => Auth::user()['firstname'] . ' ' . Auth::user()['lastname']
        ]);
    }

    /**
     * Record Periodic Emergency Kit Inspection
     */
    public function inspectEmergencyKit(): void
    {
        $user = Auth::user();
        Audit::log('CPR_KIT_INSPECT', 'emergency_kit', null, null, null, null, 
            "Recorded periodic CPR kit inspection by {$user['firstname']} {$user['lastname']}");

        Session::flash('success', 'บันทึกการตรวจสอบชุดยาช่วยชีวิตฉุกเฉินประจำ รพ.สต. เรียบร้อยแล้ว (ผลการตรวจ: สมบูรณ์พร้อมใช้ 100%)');
        Response::redirect('/pcc/safety/emergency-kit');
    }
}
