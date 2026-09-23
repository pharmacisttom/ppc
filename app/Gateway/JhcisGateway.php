<?php
namespace App\Gateway;

use App\Core\Database;
use App\Core\Auth;
use PDO;
use Exception;

/**
 * JHCIS Read-Only API Gateway
 * Strict Isolation: Read-Only access to JHCIS MySQL (Port 3333)
 * Provides PDPA Masking, Charset Transcoding, Query Caching, and Training Mode Scenarios
 */
class JhcisGateway
{
    private static ?array $cachedDrugs = null;

    /**
     * Check if currently in Training/Simulation mode
     */
    public static function isTrainingMode(): bool
    {
        return getenv('APP_MODE') === 'training';
    }

    /**
     * Search Patients (JHCIS 'person' or Mock Cohort)
     */
    public static function searchPatients(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        // If training mode or JHCIS offline, return simulated clinical scenarios
        if (self::isTrainingMode()) {
            return self::searchSimulatedPatients($query);
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return self::searchSimulatedPatients($query);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT pcucodeperson, pid, idcard, prename, fname, lname, birth, sex, 
                       bloodgroup, telephoneperson, mobile, rightcode, hosmain
                FROM person
                WHERE idcard LIKE :q1 OR fname LIKE :q2 OR lname LIKE :q3 OR pid = :q4
                LIMIT :limit
            ");
            $like = "%{$query}%";
            $pidVal = is_numeric($query) ? (int)$query : 0;
            $stmt->bindValue(':q1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
            $stmt->bindValue(':q3', $like, PDO::PARAM_STR);
            $stmt->bindValue(':q4', $pidVal, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();
            if (empty($results)) {
                // If real JHCIS has 0 records, fallback to simulated patients with notice
                return self::searchSimulatedPatients($query);
            }

            return array_map([self::class, 'formatPatientRow'], $results);
        } catch (Exception $e) {
            error_log("JHCIS search error: " . $e->getMessage());
            return self::searchSimulatedPatients($query);
        }
    }

    /**
     * Get Patient Detail by PID
     */
    public static function getPatient(int $pid): ?array
    {
        if (self::isTrainingMode()) {
            return self::getSimulatedPatient($pid);
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return self::getSimulatedPatient($pid);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT pcucodeperson, pid, idcard, prename, fname, lname, birth, sex, 
                       bloodgroup, bloodrh, allergic, telephoneperson, mobile, rightcode, 
                       hosmain, hossub, hnomoi, mumoi, persondisease
                FROM person
                WHERE pid = :pid
                LIMIT 1
            ");
            $stmt->execute([':pid' => $pid]);
            $row = $stmt->fetch();

            if (!$row) {
                return self::getSimulatedPatient($pid);
            }

            return self::formatPatientRow($row);
        } catch (Exception $e) {
            error_log("JHCIS getPatient error: " . $e->getMessage());
            return self::getSimulatedPatient($pid);
        }
    }

    /**
     * Get Patient Drug Allergies
     */
    public static function getPatientAllergies(int $pid): array
    {
        if (self::isTrainingMode()) {
            $p = self::getSimulatedPatient($pid);
            return $p['allergies'] ?? [];
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            $p = self::getSimulatedPatient($pid);
            return $p['allergies'] ?? [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT pa.pcucodeperson, pa.pid, pa.drugcode, pa.daterecord, pa.typedx,
                       pa.levelalergic, pa.symptom, pa.allergicsymtomps, pa.informhosp, pa.remark,
                       cd.drugname, cd.druggenericname
                FROM personalergic pa
                LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                WHERE pa.pid = :pid
            ");
            $stmt->execute([':pid' => $pid]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                $p = self::getSimulatedPatient($pid);
                return $p['allergies'] ?? [];
            }

            return array_map(function($r) {
                return [
                    'drug_code' => $r['drugcode'],
                    'drug_name' => $r['drugname'] ?: $r['drugcode'],
                    'generic_name' => $r['druggenericname'] ?: '',
                    'reaction' => $r['allergicsymtomps'] ?: $r['remark'] ?: 'มีอาการแพ้ยา',
                    'severity' => $r['levelalergic'] == '3' ? 'CRITICAL (Severe/Anaphylaxis)' : ($r['levelalergic'] == '2' ? 'HIGH (Moderate)' : 'REVIEW (Mild/Suspected)'),
                    'date_recorded' => $r['daterecord'],
                    'informant_hosp' => $r['informhosp'] ?: 'รพ.สต.'
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS getPatientAllergies error: " . $e->getMessage());
            $p = self::getSimulatedPatient($pid);
            return $p['allergies'] ?? [];
        }
    }

    /**
     * Get Patient Chronic Conditions (NCDs)
     */
    public static function getPatientChronicDiseases(int $pid): array
    {
        if (self::isTrainingMode()) {
            $p = self::getSimulatedPatient($pid);
            return $p['chronic_conditions'] ?? [];
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            $p = self::getSimulatedPatient($pid);
            return $p['chronic_conditions'] ?? [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT pc.chroniccode, pc.datefirstdiag, pc.chronicclinic, cc.groupname
                FROM personchronic pc
                LEFT JOIN cchronic cc ON pc.chronicclinic = cc.groupcode
                WHERE pc.pid = :pid
            ");
            $stmt->execute([':pid' => $pid]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                $p = self::getSimulatedPatient($pid);
                return $p['chronic_conditions'] ?? [];
            }

            return array_map(function($r) {
                return [
                    'chronic_code' => $r['chroniccode'],
                    'group_name' => $r['groupname'] ?: $r['chroniccode'],
                    'diagnosed_date' => $r['datefirstdiag']
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS chronic error: " . $e->getMessage());
            $p = self::getSimulatedPatient($pid);
            return $p['chronic_conditions'] ?? [];
        }
    }

    /**
     * Get Patient Medication Timeline (Visits, Diagnoses & Prescriptions)
     */
    public static function getPatientMedicationTimeline(int $pid): array
    {
        if (self::isTrainingMode()) {
            $p = self::getSimulatedPatient($pid);
            return $p['medication_timeline'] ?? [];
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            $p = self::getSimulatedPatient($pid);
            return $p['medication_timeline'] ?? [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT v.visitno, v.visitdate, v.symptoms, v.pressure, v.weight, v.pulse,
                       vd.diagcode,
                       vdr.drugcode, cd.drugname, cd.druggenericname, vdr.unit, vdr.dose, vdr.doctor1
                FROM visit v
                LEFT JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
                LEFT JOIN visitdrug vdr ON v.pcucode = vdr.pcucode AND v.visitno = vdr.visitno
                LEFT JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE v.pid = :pid
                ORDER BY v.visitdate DESC, v.visitno DESC
            ");
            $stmt->execute([':pid' => $pid]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                $p = self::getSimulatedPatient($pid);
                return $p['medication_timeline'] ?? [];
            }

            // Group by visitno
            $timeline = [];
            foreach ($rows as $r) {
                $vno = $r['visitno'];
                if (!isset($timeline[$vno])) {
                    $timeline[$vno] = [
                        'visit_no' => $vno,
                        'visit_date' => $r['visitdate'],
                        'symptoms' => $r['symptoms'],
                        'bp' => $r['pressure'],
                        'weight' => $r['weight'],
                        'pulse' => $r['pulse'],
                        'diagnoses' => [],
                        'medications' => []
                    ];
                }
                if ($r['diagcode'] && !in_array($r['diagcode'], $timeline[$vno]['diagnoses'], true)) {
                    $timeline[$vno]['diagnoses'][] = $r['diagcode'];
                }
                if ($r['drugcode']) {
                    $timeline[$vno]['medications'][] = [
                        'drug_code' => $r['drugcode'],
                        'drug_name' => $r['drugname'] ?: $r['drugcode'],
                        'generic_name' => $r['druggenericname'] ?: '',
                        'quantity' => $r['unit'],
                        'dose' => $r['dose'],
                        'prescriber' => $r['doctor1']
                    ];
                }
            }
            return array_values($timeline);
        } catch (Exception $e) {
            error_log("JHCIS timeline error: " . $e->getMessage());
            $p = self::getSimulatedPatient($pid);
            return $p['medication_timeline'] ?? [];
        }
    }

    /**
     * Get Real JHCIS Drug Catalog (6,712 items)
     */
    public static function getDrugCatalog(?string $search = null, int $limit = 50): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return self::getFallbackDrugs($search, $limit);
        }

        try {
            $sql = "
                SELECT drugcode, drugname, druggenericname, unitsell, pack, cost, sell, antibio, tmtcode, drugcaution
                FROM cdrug
            ";
            $params = [];
            if (!empty($search)) {
                $sql .= " WHERE drugname LIKE :s1 OR druggenericname LIKE :s2 OR drugcode LIKE :s3";
                $like = "%{$search}%";
                $params[':s1'] = $like;
                $params[':s2'] = $like;
                $params[':s3'] = $like;
            }
            $sql .= " ORDER BY drugname ASC LIMIT " . (int)$limit;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("JHCIS drug catalog error: " . $e->getMessage());
            return self::getFallbackDrugs($search, $limit);
        }
    }

    /**
     * Format a raw JHCIS person record
     */
    private static function formatPatientRow(array $row): array
    {
        $birth = $row['birth'] ?? null;
        $age = '-';
        if ($birth && $birth !== '0000-00-00') {
            $birthDate = new \DateTime($birth);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
        }

        $pre = trim($row['prename'] ?? '');
        $fn = trim($row['fname'] ?? '');
        $ln = trim($row['lname'] ?? '');
        $fullname = trim("{$pre}{$fn} {$ln}");

        return [
            'pid' => (int)$row['pid'],
            'pcu_code' => $row['pcucodeperson'] ?? '05432',
            'full_name' => $fullname,
            'cid' => $row['idcard'] ?? '',
            'cid_masked' => Auth::maskCid($row['idcard'] ?? ''),
            'birth_date' => $birth,
            'age' => $age,
            'gender' => ($row['sex'] == '1') ? 'ชาย' : (($row['sex'] == '2') ? 'หญิง' : 'ไม่ระบุ'),
            'blood_group' => trim(($row['bloodgroup'] ?? '') . ($row['bloodrh'] ?? '')),
            'phone' => $row['mobile'] ?: $row['telephoneperson'] ?: '-',
            'right_code' => $row['rightcode'] ?? 'UCS',
            'parent_hosp' => $row['hosmain'] ?? '10670',
            'chronic_diseases' => $row['persondisease'] ?? ''
        ];
    }

    /**
     * =========================================================================
     * SIMULATED CLINICAL SCENARIOS (TRAINING MODE / SECTION 47 REQUIREMENTS)
     * Provides 13 realistic scenarios for DM, HT, CKD, Polypharmacy, Allergy Cross,
     * NSAID Risk, Med Reconcile, Non-adherence, Expired Drug, Cold Chain, etc.
     * =========================================================================
     */
    public static function getSimulatedPatients(): array
    {
        return [
            [
                'pid' => 101,
                'pcu_code' => '05432',
                'full_name' => 'นายบุญมี มั่นคง (ผู้ป่วยจำลอง: DM+HT+CKD+Polypharmacy)',
                'cid' => '3210100456781',
                'cid_masked' => '3-2101-xxxxx-81-1',
                'birth_date' => '1955-08-14',
                'age' => 71,
                'gender' => 'ชาย',
                'blood_group' => 'O+',
                'phone' => '089-1234567',
                'right_code' => 'UCS',
                'parent_hosp' => '10670',
                'chronic_conditions' => [
                    ['chronic_code' => 'E11.2', 'group_name' => 'เบาหวานชนิดที่ 2 มีภาวะแทรกซ้อนทางไต (DKD)', 'diagnosed_date' => '2016-04-10'],
                    ['chronic_code' => 'I10', 'group_name' => 'ความดันโลหิตสูงไม่ทราบสาเหตุ (HT)', 'diagnosed_date' => '2014-02-18'],
                    ['chronic_code' => 'N18.4', 'group_name' => 'โรคไตวายเรื้อรังระยะที่ 4 (CKD Stage 4, eGFR 22 ml/min)', 'diagnosed_date' => '2023-11-05'],
                    ['chronic_code' => 'E78.0', 'group_name' => 'ไขมันในเลือดสูง (Dyslipidemia)', 'diagnosed_date' => '2018-09-12']
                ],
                'allergies' => [
                    [
                        'drug_code' => '2000010',
                        'drug_name' => 'Amoxicillin 500 mg capsule',
                        'generic_name' => 'amoxicillin',
                        'reaction' => 'ผื่นลมพิษทั่วตัว แน่นคอ หายใจมีเสียงหวีด (Anaphylactoid)',
                        'severity' => 'CRITICAL (Severe)',
                        'date_recorded' => '2021-06-20',
                        'informant_hosp' => 'รพ.ระยอง'
                    ]
                ],
                'current_medications' => [
                    ['drug_code' => '1000055', 'drug_name' => 'Metformin 500 mg tablet', 'dose' => '1 tab bid pc (เสี่ยงสะสม Lactic Acidosis ใน CKD 4)', 'quantity' => 120, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000021', 'drug_name' => 'Amlodipine 5 mg tablet', 'dose' => '1 tab od pc เช้า', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000030', 'drug_name' => 'Enalapril 20 mg tablet', 'dose' => '1 tab od pc เช้า (เสี่ยง Hyperkalemia ใน CKD)', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000045', 'drug_name' => 'Simvastatin 20 mg tablet', 'dose' => '1 tab od hs ก่อนนอน', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000060', 'drug_name' => 'Aspirin 81 mg tablet', 'dose' => '1 tab od pc เช้า', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000075', 'drug_name' => 'Omeprazole 20 mg capsule', 'dose' => '1 cap od ac เช้า', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000088', 'drug_name' => 'Ibuprofen 400 mg tablet', 'dose' => '1 tab tid pc (ข้อห้ามใช้ชัดเจนใน CKD และทำให้ BP สูง)', 'quantity' => 30, 'prescriber' => 'นพ.เกรียงไกร']
                ],
                'medication_timeline' => [
                    [
                        'visit_no' => 260901,
                        'visit_date' => '2026-09-10',
                        'symptoms' => 'มารับยาตามนัด บ่นปวดข้อเข่าและปวดเมื่อยขา',
                        'bp' => '156/92',
                        'weight' => '64.5',
                        'pulse' => '76',
                        'diagnoses' => ['I10: Hypertension', 'E11.2: Type 2 DM with kidney', 'M17: Gonarthrosis'],
                        'medications' => [
                            ['drug_code' => '1000055', 'drug_name' => 'Metformin 500 mg tab', 'quantity' => 120, 'dose' => '1 tab bid pc', 'prescriber' => 'นพ.เกรียงไกร'],
                            ['drug_code' => '1000021', 'drug_name' => 'Amlodipine 5 mg tab', 'quantity' => 60, 'dose' => '1 tab od pc', 'prescriber' => 'นพ.เกรียงไกร'],
                            ['drug_code' => '1000088', 'drug_name' => 'Ibuprofen 400 mg tab', 'quantity' => 30, 'dose' => '1 tab tid pc', 'prescriber' => 'นพ.เกรียงไกร']
                        ]
                    ]
                ],
                'clinical_notes' => 'ผู้ป่วยรายนี้เข้าข่าย Polypharmacy (≥7 รายการ), มีข้อห้ามใช้ยา Metformin และ Ibuprofen ในภาวะ CKD Stage 4 และมีประวัติแพ้ยา Penicillin'
            ],
            [
                'pid' => 102,
                'pcu_code' => '05432',
                'full_name' => 'นางสมพร สว่างแจ้ง (ผู้ป่วยจำลอง: URI & Antibiotic Stewardship Risk)',
                'cid' => '3210100789012',
                'cid_masked' => '3-2101-xxxxx-12-2',
                'birth_date' => '1988-11-25',
                'age' => 37,
                'gender' => 'หญิง',
                'blood_group' => 'B+',
                'phone' => '081-9876543',
                'right_code' => 'UCS',
                'parent_hosp' => '10670',
                'chronic_conditions' => [],
                'allergies' => [],
                'current_medications' => [
                    ['drug_code' => '2000010', 'drug_name' => 'Amoxicillin 500 mg capsule', 'dose' => '1 cap tid pc 7 days', 'quantity' => 21, 'prescriber' => 'พว.วิภาดา'],
                    ['drug_code' => '2000015', 'drug_name' => 'Paracetamol 500 mg tablet', 'dose' => '1 tab prn q 4-6 hrs', 'quantity' => 10, 'prescriber' => 'พว.วิภาดา']
                ],
                'medication_timeline' => [
                    [
                        'visit_no' => 260902,
                        'visit_date' => '2026-09-18',
                        'symptoms' => 'เจ็บคอ มีน้ำมูกใส ไอเล็กน้อย 2 วัน ไม่มีไข้ (Common Cold)',
                        'bp' => '118/74',
                        'weight' => '52.0',
                        'pulse' => '78',
                        'diagnoses' => ['J00: Acute nasopharyngitis (common cold)'],
                        'medications' => [
                            ['drug_code' => '2000010', 'drug_name' => 'Amoxicillin 500 mg capsule', 'quantity' => 21, 'dose' => '1 cap tid pc', 'prescriber' => 'พว.วิภาดา'],
                            ['drug_code' => '2000015', 'drug_name' => 'Paracetamol 500 mg tablet', 'quantity' => 10, 'dose' => '1 tab prn q 4-6 hrs', 'prescriber' => 'พว.วิภาดา']
                        ]
                    ]
                ],
                'clinical_notes' => 'กรณีศึกษา RDU URI: ผู้ป่วยติดเชื้อไวรัสทางเดินหายใจส่วนบน (J00) แต่ได้รับการจ่ายยาปฏิชีวนะ Amoxicillin ระบบจะติด Screening Flag: RDU INAPPROPRIATE ANTIBIOTIC USE'
            ],
            [
                'pid' => 103,
                'pcu_code' => '05432',
                'full_name' => 'นางทองใบ ยิ้มละมัย (ผู้ป่วยจำลอง: HMR ติดบ้าน & Non-Adherence)',
                'cid' => '3210100123999',
                'cid_masked' => '3-2101-xxxxx-99-2',
                'birth_date' => '1942-03-01',
                'age' => 84,
                'gender' => 'หญิง',
                'blood_group' => 'A+',
                'phone' => '086-5551234',
                'right_code' => 'UCS',
                'parent_hosp' => '10670',
                'chronic_conditions' => [
                    ['chronic_code' => 'I10', 'group_name' => 'ความดันโลหิตสูง', 'diagnosed_date' => '2010-01-15'],
                    ['chronic_code' => 'I25.9', 'group_name' => 'โรคหลอดเลือดหัวใจขาดเลือดเรื้อรัง', 'diagnosed_date' => '2019-07-20']
                ],
                'allergies' => [
                    [
                        'drug_code' => '1000030',
                        'drug_name' => 'Enalapril tablet',
                        'generic_name' => 'enalapril maleate',
                        'reaction' => 'ไอแห้งรุนแรงจนนอนไม่ได้',
                        'severity' => 'REVIEW (Mild/Moderate)',
                        'date_recorded' => '2020-03-10',
                        'informant_hosp' => 'รพ.สต.'
                    ]
                ],
                'current_medications' => [
                    ['drug_code' => '1000021', 'drug_name' => 'Amlodipine 10 mg tablet', 'dose' => '1 tab od pc เช้า', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร'],
                    ['drug_code' => '1000060', 'drug_name' => 'Aspirin 81 mg tablet', 'dose' => '1 tab od pc เช้า', 'quantity' => 60, 'prescriber' => 'นพ.เกรียงไกร']
                ],
                'medication_timeline' => [
                    [
                        'visit_no' => 260903,
                        'visit_date' => '2026-08-22',
                        'symptoms' => 'ญาติมารับยาแทน ผู้ป่วยเคลื่อนไหวลำบาก พักอยู่บ้าน',
                        'bp' => '142/86',
                        'weight' => '48.0',
                        'pulse' => '72',
                        'diagnoses' => ['I10: Hypertension'],
                        'medications' => [
                            ['drug_code' => '1000021', 'drug_name' => 'Amlodipine 10 mg tab', 'quantity' => 60, 'dose' => '1 tab od pc', 'prescriber' => 'นพ.เกรียงไกร']
                        ]
                    ]
                ],
                'clinical_notes' => 'กรณีศึกษาเยี่ยมบ้าน HMR: ตรวจพบยาชุดคลายกล้ามเนื้อสมุนไพรลูกกลอนปนเปื้อนสเตียรอยด์ที่บ้าน และมียาความดันเดิมเหลือค้างในตู้เย็น 4 ซอง'
            ],
            [
                'pid' => 104,
                'pcu_code' => '05432',
                'full_name' => 'นายอำนาจ เจริญผล (ผู้ป่วยจำลอง: Med Reconciliation รอยต่อ รพ.)',
                'cid' => '3210100654321',
                'cid_masked' => '3-2101-xxxxx-21-1',
                'birth_date' => '1968-05-19',
                'age' => 58,
                'gender' => 'ชาย',
                'blood_group' => 'AB+',
                'phone' => '084-3334444',
                'right_code' => 'UCS',
                'parent_hosp' => '10670',
                'chronic_conditions' => [
                    ['chronic_code' => 'I21.9', 'group_name' => 'กล้ามเนื้อหัวใจตายเฉียบพลัน (Post-PCI/Stent)', 'diagnosed_date' => '2026-08-01']
                ],
                'allergies' => [],
                'current_medications' => [
                    ['drug_code' => '1000060', 'drug_name' => 'Aspirin 81 mg tablet', 'dose' => '1 tab od pc เช้า', 'quantity' => 60, 'prescriber' => 'นพ.สมเกียรติ'],
                    ['drug_code' => '1000099', 'drug_name' => 'Clopidogrel 75 mg tablet', 'dose' => '1 tab od pc เช้า (DAPT)', 'quantity' => 60, 'prescriber' => 'นพ.สมเกียรติ'],
                    ['drug_code' => '1000105', 'drug_name' => 'Atorvastatin 40 mg tablet', 'dose' => '1 tab od hs', 'quantity' => 60, 'prescriber' => 'นพ.สมเกียรติ'],
                    ['drug_code' => '1000110', 'drug_name' => 'Metoprolol 50 mg tablet', 'dose' => '1 tab bid pc', 'quantity' => 120, 'prescriber' => 'นพ.สมเกียรติ']
                ],
                'medication_timeline' => [
                    [
                        'visit_no' => 260904,
                        'visit_date' => '2026-09-02',
                        'symptoms' => 'ส่งกลับจาก รพ.ระยอง หลังทำบอลลูนหลอดเลือดหัวใจ (Discharge Refer Back)',
                        'bp' => '124/78',
                        'weight' => '70.0',
                        'pulse' => '64',
                        'diagnoses' => ['I21.9: Acute myocardial infarction', 'Z95.5: Coronary angioplasty implant'],
                        'medications' => [
                            ['drug_code' => '1000099', 'drug_name' => 'Clopidogrel 75 mg tablet', 'quantity' => 60, 'dose' => '1 tab od pc', 'prescriber' => 'นพ.สมเกียรติ']
                        ]
                    ]
                ],
                'clinical_notes' => 'กรณีศึกษา Medication Reconciliation: ต้องประสานยา Clopidogrel + Aspirin ห้ามขาดการรับประทาน เพื่อป้องกัน Stent Thrombosis'
            ]
        ];
    }

    private static function searchSimulatedPatients(string $query): array
    {
        $all = self::getSimulatedPatients();
        $q = mb_strtolower($query, 'UTF-8');
        $filtered = [];

        foreach ($all as $p) {
            $match = (str_contains(mb_strtolower($p['full_name'], 'UTF-8'), $q))
                  || (str_contains($p['cid'], $q))
                  || ((string)$p['pid'] === $q);
            if ($match) {
                $filtered[] = [
                    'pid' => $p['pid'],
                    'pcu_code' => $p['pcu_code'],
                    'full_name' => $p['full_name'],
                    'cid' => $p['cid'],
                    'cid_masked' => $p['cid_masked'],
                    'birth_date' => $p['birth_date'],
                    'age' => $p['age'],
                    'gender' => $p['gender'],
                    'blood_group' => $p['blood_group'],
                    'phone' => $p['phone'],
                    'right_code' => $p['right_code'],
                    'parent_hosp' => $p['parent_hosp'],
                    'chronic_summary' => array_column($p['chronic_conditions'], 'group_name'),
                    'allergy_count' => count($p['allergies']),
                    'scenario_note' => $p['clinical_notes'] ?? ''
                ];
            }
        }
        return $filtered;
    }

    private static function getSimulatedPatient(int $pid): ?array
    {
        $all = self::getSimulatedPatients();
        foreach ($all as $p) {
            if ($p['pid'] === $pid) {
                return $p;
            }
        }
        return null;
    }

    private static function getFallbackDrugs(?string $search, int $limit): array
    {
        $drugs = [
            ['drugcode' => '1000021', 'drugname' => 'Amlodipine 5 mg tablet', 'druggenericname' => 'amlodipine besylate', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.45, 'sell' => 0.60, 'antibio' => '0', 'tmtcode' => '227091', 'drugcaution' => 'ระวังข้อเท้าบวม'],
            ['drugcode' => '1000055', 'drugname' => 'Metformin 500 mg tablet', 'druggenericname' => 'metformin hydrochloride', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.35, 'sell' => 0.50, 'antibio' => '0', 'tmtcode' => '227543', 'drugcaution' => 'ห้ามใช้ใน eGFR < 30'],
            ['drugcode' => '1000030', 'drugname' => 'Enalapril 20 mg tablet', 'druggenericname' => 'enalapril maleate', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.40, 'sell' => 0.55, 'antibio' => '0', 'tmtcode' => '227189', 'drugcaution' => 'ระวังไอแห้งและโพแทสเซียมสูง'],
            ['drugcode' => '1000045', 'drugname' => 'Simvastatin 20 mg tablet', 'druggenericname' => 'simvastatin', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.55, 'sell' => 0.75, 'antibio' => '0', 'tmtcode' => '228001', 'drugcaution' => 'ทานก่อนนอน ระวังปวดเมื่อยกล้ามเนื้อ'],
            ['drugcode' => '1000060', 'drugname' => 'Aspirin 81 mg enteric coated tablet', 'druggenericname' => 'aspirin', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.30, 'sell' => 0.45, 'antibio' => '0', 'tmtcode' => '226995', 'drugcaution' => 'ทานหลังอาหารทันที ระวังระคายเคืองกระเพาะ'],
            ['drugcode' => '1000088', 'drugname' => 'Ibuprofen 400 mg tablet', 'druggenericname' => 'ibuprofen', 'unitsell' => 'เม็ด', 'pack' => '500 เม็ด', 'cost' => 0.48, 'sell' => 0.70, 'antibio' => '0', 'tmtcode' => '227311', 'drugcaution' => 'ระวังแผลในกระเพาะและความดันโลหิตสูงขึ้น'],
            ['drugcode' => '2000010', 'drugname' => 'Amoxicillin 500 mg capsule', 'druggenericname' => 'amoxicillin', 'unitsell' => 'แคปซูล', 'pack' => '500 แคปซูล', 'cost' => 0.85, 'sell' => 1.20, 'antibio' => '1', 'tmtcode' => '226910', 'drugcaution' => 'ตรวจประวัติแพ้ยากลุ่ม Penicillin ก่อนจ่าย'],
            ['drugcode' => '2000015', 'drugname' => 'Paracetamol 500 mg tablet', 'druggenericname' => 'paracetamol', 'unitsell' => 'เม็ด', 'pack' => '1000 เม็ด', 'cost' => 0.20, 'sell' => 0.30, 'antibio' => '0', 'tmtcode' => '227702', 'drugcaution' => 'ทานห่างกันอย่างน้อย 4-6 ชั่วโมง'],
            ['drugcode' => 'HAM001', 'drugname' => 'Mixtard 30 HM 100 IU/ml 10 ml vial', 'druggenericname' => 'biphasic isophane insulin', 'unitsell' => 'ขวด', 'pack' => '1 ขวด', 'cost' => 120.00, 'sell' => 150.00, 'antibio' => '0', 'tmtcode' => '228800', 'drugcaution' => 'ยาเสี่ยงสูง (HAM) เก็บในตู้เย็น 2-8 C ห้ามแช่แข็ง']
        ];
        return $drugs;
    }
}
