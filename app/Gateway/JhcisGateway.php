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
     * Check if currently in Training/Simulation mode (Always false in pure live mode)
     */
    public static function isTrainingMode(): bool
    {
        return false;
    }

    /**
     * Search Patients from live JHCIS 'person' table
     */
    public static function searchPatients(string $query, int $limit = 25): array
    {
        $query = trim($query);
        if (empty($query)) {
            return self::getRecentPatients($limit);
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT p.pcucodeperson, p.pid, p.idcard, p.prename, ct.titlename, p.fname, p.lname, p.birth, p.sex, 
                       p.bloodgroup, p.bloodrh, p.telephoneperson, p.mobile, p.rightcode, cr.rightname, p.hosmain, p.persondisease
                FROM person p
                LEFT JOIN ctitle ct ON p.prename = ct.titlecode
                LEFT JOIN cright cr ON p.rightcode = cr.rightcode
                WHERE p.idcard LIKE :q1 OR p.fname LIKE :q2 OR p.lname LIKE :q3 OR p.pid = :q4
                ORDER BY p.pid DESC
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
            return array_map([self::class, 'formatPatientRow'], $results);
        } catch (Exception $e) {
            error_log("JHCIS search error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Recent Patients from JHCIS
     */
    public static function getRecentPatients(int $limit = 25): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT p.pcucodeperson, p.pid, p.idcard, p.prename, ct.titlename, p.fname, p.lname, p.birth, p.sex, 
                       p.bloodgroup, p.bloodrh, p.telephoneperson, p.mobile, p.rightcode, cr.rightname, p.hosmain, p.persondisease
                FROM person p
                LEFT JOIN ctitle ct ON p.prename = ct.titlecode
                LEFT JOIN cright cr ON p.rightcode = cr.rightcode
                WHERE p.fname IS NOT NULL AND p.fname != ''
                ORDER BY p.pid DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            return array_map([self::class, 'formatPatientRow'], $results);
        } catch (Exception $e) {
            error_log("JHCIS getRecentPatients error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Patient Detail by PID
     */
    public static function getPatient(int $pid): ?array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return null;
        }

        try {
            $stmt = $pdo->prepare("
                SELECT p.pcucodeperson, p.pid, p.idcard, p.prename, ct.titlename, p.fname, p.lname, p.birth, p.sex, 
                       p.bloodgroup, p.bloodrh, p.allergic, p.telephoneperson, p.mobile, p.rightcode, cr.rightname,
                       p.hosmain, p.hossub, p.hnomoi, p.mumoi, p.persondisease
                FROM person p
                LEFT JOIN ctitle ct ON p.prename = ct.titlecode
                LEFT JOIN cright cr ON p.rightcode = cr.rightcode
                WHERE p.pid = :pid
                LIMIT 1
            ");
            $stmt->execute([':pid' => $pid]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            return self::formatPatientRow($row);
        } catch (Exception $e) {
            error_log("JHCIS getPatient error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Patient Drug Allergies from live JHCIS
     */
    public static function getPatientAllergies(int $pid): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
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
                return [];
            }

            return array_map(function($r) {
                $reaction = trim($r['allergicsymtomps'] ?: $r['symptom'] ?: $r['remark'] ?: 'มีประวัติแพ้ยา');
                $severity = 'REVIEW (Mild/Suspected)';
                if ($r['levelalergic'] == '3') {
                    $severity = 'CRITICAL (Severe/Anaphylaxis)';
                } elseif ($r['levelalergic'] == '2') {
                    $severity = 'HIGH (Moderate)';
                }

                return [
                    'drug_code' => $r['drugcode'],
                    'drug_name' => $r['drugname'] ?: $r['drugcode'],
                    'generic_name' => $r['druggenericname'] ?: '',
                    'reaction' => $reaction,
                    'symptom' => $reaction,
                    'severity' => $severity,
                    'date_recorded' => $r['daterecord'],
                    'report_date' => $r['daterecord'],
                    'informant_hosp' => $r['informhosp'] ?: 'รพ.สต.'
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS getPatientAllergies error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Patient Chronic Conditions (NCDs) from live JHCIS
     */
    public static function getPatientChronicDiseases(int $pid): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT pc.chroniccode, pc.datefirstdiag, pc.chronicclinic,
                       COALESCE(cd.diseasenamethai, cd.diseasename, cc.groupname, pc.chroniccode) as group_name
                FROM personchronic pc
                LEFT JOIN cdisease cd ON pc.chroniccode = cd.diseasecode
                LEFT JOIN cchronic cc ON (pc.chronicclinic = cc.groupcode OR TRIM(LEADING '0' FROM pc.chronicclinic) = TRIM(LEADING '0' FROM cc.groupcode))
                WHERE pc.pid = :pid
            ");
            $stmt->execute([':pid' => $pid]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                return [];
            }

            return array_map(function($r) {
                return [
                    'chronic_code' => $r['chroniccode'],
                    'code' => $r['chroniccode'],
                    'group_name' => $r['group_name'] ?: $r['chroniccode'],
                    'disease_name' => $r['group_name'] ?: $r['chroniccode'],
                    'diagnosed_date' => $r['datefirstdiag']
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS chronic error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Patient Medication Timeline (Visits, Diagnoses & Prescriptions) from live JHCIS
     */
    public static function getPatientMedicationTimeline(int $pid): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT v.visitno, v.visitdate, v.symptoms, v.pressure, v.weight, v.pulse,
                       vd.diagcode, cd_diag.diseasenamethai as diagname,
                       vdr.drugcode, cd.drugname, cd.druggenericname, vdr.unit, vdr.dose, vdr.doctor1
                FROM visit v
                LEFT JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
                LEFT JOIN cdisease cd_diag ON vd.diagcode = cd_diag.diseasecode
                LEFT JOIN visitdrug vdr ON v.pcucode = vdr.pcucode AND v.visitno = vdr.visitno
                LEFT JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE v.pid = :pid
                ORDER BY v.visitdate DESC, v.visitno DESC
                LIMIT 150
            ");
            $stmt->execute([':pid' => $pid]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                return [];
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
                        'prescriber' => $r['doctor1'] ?: 'รพ.สต.',
                        'diagnoses' => [],
                        'medications' => []
                    ];
                }
                $diagLabel = $r['diagcode'];
                if (!empty($r['diagname'])) {
                    $diagLabel .= ": " . $r['diagname'];
                }
                if ($r['diagcode'] && !in_array($diagLabel, $timeline[$vno]['diagnoses'], true)) {
                    $timeline[$vno]['diagnoses'][] = $diagLabel;
                }
                if ($r['drugcode']) {
                    $timeline[$vno]['medications'][] = [
                        'drug_code' => $r['drugcode'],
                        'drug_name' => $r['drugname'] ?: $r['drugcode'],
                        'generic_name' => $r['druggenericname'] ?: '',
                        'quantity' => $r['unit'],
                        'qty' => $r['unit'],
                        'dose' => $r['dose'] ?: '-',
                        'instruction' => $r['dose'] ?: '-',
                        'prescriber' => $r['doctor1'] ?: 'เจ้าหน้าที่'
                    ];
                }
            }

            // Add diagnosis aggregated string
            foreach ($timeline as &$v) {
                $v['diagnosis'] = !empty($v['diagnoses']) ? implode(', ', $v['diagnoses']) : 'ไม่ระบุ';
            }
            unset($v);

            return array_values($timeline);
        } catch (Exception $e) {
            error_log("JHCIS timeline error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Enriched JHCIS Drug Catalog with Safety Tags & Stock Cross-Referencing
     * Supports both legacy string query and modern parameter array
     */
    public static function getDrugCatalog($queryOrFilters = null, int $pageOrLimit = 1, int $perPage = 50): array
    {
        $filters = [];
        $page = 1;
        $limit = 50;

        if (is_array($queryOrFilters)) {
            $filters = $queryOrFilters;
            $page = max(1, (int)($filters['page'] ?? $pageOrLimit));
            $limit = max(1, min(500, (int)($filters['per_page'] ?? $perPage)));
        } else {
            $filters['search'] = (string)$queryOrFilters;
            $limit = max(1, min(500, $pageOrLimit));
            $page = 1;
        }

        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [
                'items' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $limit,
                'total_pages' => 1
            ];
        }

        try {
            $where = [];
            $params = [];

            // 1. Text Search
            $search = trim($filters['search'] ?? '');
            if (!empty($search)) {
                $where[] = "(cd.drugname LIKE :s1 OR cd.druggenericname LIKE :s2 OR cd.drugnamethai LIKE :s3 OR cd.drugcode LIKE :s4 OR cd.tmtcode LIKE :s5)";
                $like = "%{$search}%";
                $params[':s1'] = $like;
                $params[':s2'] = $like;
                $params[':s3'] = $like;
                $params[':s4'] = $like;
                $params[':s5'] = $like;
            }

            // 2. Category Filter
            $category = $filters['category'] ?? 'all';
            if ($category === 'modern') {
                $where[] = "cd.drugtype = '01'";
            } elseif ($category === 'herbal') {
                $where[] = "(cd.drugtype = '10' OR cd.drugname LIKE '%ฟ้าทะลายโจร%' OR cd.drugname LIKE '%ขมิ้นชัน%' OR cd.drugname LIKE '%มะขามป้อม%' OR cd.drugname LIKE '%ไพล%' OR cd.drugname LIKE '%ประสะ%')";
            } elseif ($category === 'cold_chain') {
                $where[] = "(cd.drugname LIKE '%วัคซีน%' OR cd.drugname LIKE '%vaccine%' OR cd.drugname LIKE '%toxoid%' OR cd.drugname LIKE '%insulin%' OR cd.drugname LIKE '%mixtard%' OR cd.drugname LIKE '%JEVAX%' OR cd.drugname LIKE '%OPV%')";
            } elseif ($category === 'emergency') {
                $where[] = "(cd.drugname LIKE '%Adrenaline%' OR cd.drugname LIKE '%Epinephrine%' OR cd.drugname LIKE '%Atropine%' OR cd.drugname LIKE '%Glucose 50%' OR cd.drugname LIKE '%Diazepam%')";
            } elseif ($category === 'antibiotic') {
                $where[] = "cd.antibio = '1'";
            } elseif ($category === 'nlem') {
                $where[] = "cd.nationaccount = '1'";
            } elseif ($category === 'non_nlem') {
                $where[] = "cd.nationaccount = '2'";
            } elseif ($category === 'pcu_formulary') {
                // PCU Formulary: Modern medicines, herbs, vaccines, emergency, or having standard dose in JHCIS
                $where[] = "(cd.drugtype IN ('01', '10') OR cd.nationaccount = '1' OR cd.antibio = '1' OR cd.drugcode IN ('2A01', '2ATP', '2GC2', '1000021', '1000055', 'HAM001'))";
            }

            // 3. NLEM Filter
            $nlem = $filters['nlem'] ?? '';
            if ($nlem === '1') {
                $where[] = "cd.nationaccount = '1'";
            } elseif ($nlem === '2') {
                $where[] = "cd.nationaccount = '2'";
            }

            $whereSql = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

            // Total count query
            $countSql = "SELECT COUNT(*) FROM cdrug cd" . $whereSql;
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            // Sorting
            $orderBy = $filters['order_by'] ?? 'drugname';
            $orderDir = strtoupper($filters['order_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $validColumns = [
                'drugname' => 'cd.drugname',
                'drugcode' => 'cd.drugcode',
                'cost' => 'cd.cost',
                'sell' => 'cd.sell'
            ];
            $sortColumn = $validColumns[$orderBy] ?? 'cd.drugname';

            // Pagination
            $offset = ($page - 1) * $limit;

            $sql = "
                SELECT cd.drugcode, cd.drugname, cd.drugnamethai, cd.druggenericname,
                       cd.pack, cd.cost, cd.sell, cd.antibio, cd.nationaccount, cd.drugtype,
                       cd.tmtcode, cd.drugcaution,
                       COALESCE(u.unitsellname, cd.unitsell) as unitsell_name
                FROM cdrug cd
                LEFT JOIN cdrugunitsell u ON cd.unitsell = u.unitsellcode
                {$whereSql}
                ORDER BY {$sortColumn} {$orderDir}
                LIMIT {$limit} OFFSET {$offset}
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rawDrugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Safety and Stock mapping from App Database
            $hamMap = self::getHamMap();
            $lasaMap = self::getLasaMap();
            $stockMap = self::getStockLotsMap();
            $masterMap = self::getDrugMasterConfigsMap();

            $drugs = [];
            foreach ($rawDrugs as $d) {
                $code = trim($d['drugcode']);
                $nameLower = mb_strtolower($d['drugname'] ?? '', 'UTF-8');
                $genLower = mb_strtolower($d['druggenericname'] ?? '', 'UTF-8');

                // Custom Master Config Override
                $masterCfg = $masterMap[$code] ?? null;
                if ($masterCfg) {
                    if (!empty($masterCfg['drugnamethai'])) $d['drugnamethai'] = $masterCfg['drugnamethai'];
                    if (!empty($masterCfg['druggenericname'])) $d['druggenericname'] = $masterCfg['druggenericname'];
                    if (!empty($masterCfg['caution'])) $d['drugcaution'] = $masterCfg['caution'];
                }

                // Check HAM
                $isHam = isset($hamMap[$code]);
                $hamInfo = $hamMap[$code] ?? null;
                if (!$isHam) {
                    if (str_contains($nameLower, 'insulin') || str_contains($nameLower, 'mixtard')
                        || str_contains($nameLower, 'warfarin') || str_contains($nameLower, 'digoxin')
                        || str_contains($nameLower, 'morphine') || str_contains($nameLower, 'pethidine')) {
                        $isHam = true;
                    }
                }

                // Check LASA
                $isLasa = isset($lasaMap[$code]);
                $lasaInfo = $lasaMap[$code] ?? null;
                $tallMan = $lasaInfo['tall_man'] ?? null;

                // Check Cold Chain
                $isColdChain = str_contains($nameLower, 'วัคซีน') || str_contains($nameLower, 'vaccine')
                    || str_contains($nameLower, 'toxoid') || str_contains($nameLower, 'insulin')
                    || str_contains($nameLower, 'mixtard') || str_contains($nameLower, 'jevax')
                    || str_contains($nameLower, 'opv') || $code === 'HAM001';

                // Check Emergency / CPR Kit
                $isEmergency = str_contains($nameLower, 'adrenaline') || str_contains($nameLower, 'epinephrine')
                    || str_contains($nameLower, 'atropine') || str_contains($nameLower, 'glucose 50%')
                    || str_contains($nameLower, '50% glucose') || str_contains($nameLower, 'diazepam inj');

                // Check Herbal
                $isHerbal = ($d['drugtype'] === '10') || str_contains($nameLower, 'ฟ้าทะลายโจร')
                    || str_contains($nameLower, 'ขมิ้นชัน') || str_contains($nameLower, 'มะขามป้อม')
                    || str_contains($nameLower, 'ไพล') || str_contains($nameLower, 'ประสะ');

                // Stock Info
                $stock = $stockMap[$code] ?? [
                    'quantity_balance' => 0,
                    'lots_count' => 0,
                    'earliest_expiry' => null
                ];

                if ($masterCfg) {
                    if ($masterCfg['is_ham']) $isHam = true;
                    if ($masterCfg['is_lasa']) $isLasa = true;
                    if ($masterCfg['is_cold_chain']) $isColdChain = true;
                    if ($masterCfg['is_emergency']) $isEmergency = true;
                    if ($masterCfg['is_herbal']) $isHerbal = true;
                }

                $drugs[] = [
                    'drugcode' => $code,
                    'drugname' => $d['drugname'] ?? '',
                    'drugnamethai' => $d['drugnamethai'] ?? '',
                    'druggenericname' => $d['druggenericname'] ?? '',
                    'unitsell' => $d['unitsell_name'] ?? 'หน่วย',
                    'pack' => $d['pack'] ?? '-',
                    'cost' => (float)($d['cost'] ?? 0),
                    'sell' => (float)($d['sell'] ?? 0),
                    'antibio' => $d['antibio'] ?? '2',
                    'is_antibiotic' => $masterCfg ? (bool)$masterCfg['is_antibiotic'] : ($d['antibio'] === '1'),
                    'nationaccount' => $d['nationaccount'] ?? '',
                    'is_nlem' => $masterCfg ? (bool)$masterCfg['is_nlem'] : ($d['nationaccount'] === '1'),
                    'drugtype' => $d['drugtype'] ?? '',
                    'tmtcode' => $d['tmtcode'] ?? '',
                    'drugcaution' => $d['drugcaution'] ?? '',
                    'is_ham' => $isHam,
                    'ham_info' => $hamInfo,
                    'is_lasa' => $isLasa,
                    'tall_man' => $tallMan,
                    'is_cold_chain' => $isColdChain,
                    'is_emergency' => $isEmergency,
                    'is_herbal' => $isHerbal,
                    'min_stock' => (int)($masterCfg['min_stock'] ?? 0),
                    'max_stock' => (int)($masterCfg['max_stock'] ?? 0),
                    'caution' => $masterCfg['caution'] ?? ($d['drugcaution'] ?? ''),
                    'stock_balance' => $stock['quantity_balance'],
                    'lots_count' => $stock['lots_count'],
                    'earliest_expiry' => $stock['earliest_expiry']
                ];
            }

            return [
                'items' => $drugs,
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $limit,
                'total_pages' => (int)ceil($totalCount / $limit)
            ];
        } catch (Exception $e) {
            error_log("JHCIS drug catalog error: " . $e->getMessage());
            return [
                'items' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $limit,
                'total_pages' => 1
            ];
        }
    }

    /**
     * Get Full Clinical & Safety Detail for a Single Drug
     */
    public static function getDrugDetail(string $drugcode): ?array
    {
        $code = trim($drugcode);
        $pdo = Database::getJhcisDb();

        $drug = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    SELECT cd.drugcode, cd.drugname, cd.drugnamethai, cd.druggenericname,
                           cd.pack, cd.cost, cd.sell, cd.antibio, cd.nationaccount, cd.drugtype,
                           cd.tmtcode, cd.drugcaution,
                           COALESCE(u.unitsellname, cd.unitsell) as unitsell_name
                    FROM cdrug cd
                    LEFT JOIN cdrugunitsell u ON cd.unitsell = u.unitsellcode
                    WHERE cd.drugcode = :c
                    LIMIT 1
                ");
                $stmt->execute([':c' => $code]);
                $drug = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Error fetching drug detail: " . $e->getMessage());
            }
        }

        if (!$drug) {
            return null;
        }

        // Fetch standard dosing instructions from sysdrugdose
        $doses = [];
        if ($pdo) {
            try {
                $stmtDose = $pdo->prepare("
                    SELECT doseno, dosedescription, doseprefix
                    FROM sysdrugdose
                    WHERE drugcode = :c
                    ORDER BY doseno ASC
                ");
                $stmtDose->execute([':c' => $code]);
                $doses = $stmtDose->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }

        // Fetch conflicting diseases from cdiseaseconflictdrug
        $conflicts = [];
        if ($pdo) {
            try {
                $stmtConf = $pdo->prepare("
                    SELECT diseasecode
                    FROM cdiseaseconflictdrug
                    WHERE drugcode = :c
                    ORDER BY diseasecode ASC
                ");
                $stmtConf->execute([':c' => $code]);
                $conflicts = $stmtConf->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {}
        }

        // Fetch active lots from App Database stock_lots
        $lots = [];
        try {
            $appDb = Database::getAppDb();
            $stmtLots = $appDb->prepare("
                SELECT sl.lot_id, sl.lot_number, sl.expiry_date, sl.quantity_balance, sl.unit_cost,
                       sl.status, loc.location_name,
                       DATEDIFF(sl.expiry_date, CURRENT_DATE()) as days_to_expire
                FROM stock_lots sl
                JOIN stock_locations loc ON sl.location_id = loc.location_id
                WHERE sl.drug_code = :c AND sl.status = 'active'
                ORDER BY sl.expiry_date ASC
            ");
            $stmtLots->execute([':c' => $code]);
            $lots = $stmtLots->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        // Safety Mappings
        $hamMap = self::getHamMap();
        $lasaMap = self::getLasaMap();

        $nameLower = mb_strtolower($drug['drugname'] ?? '', 'UTF-8');
        $isHam = isset($hamMap[$code]) || str_contains($nameLower, 'insulin') || str_contains($nameLower, 'mixtard') || str_contains($nameLower, 'warfarin');
        $hamInfo = $hamMap[$code] ?? null;

        $isLasa = isset($lasaMap[$code]);
        $lasaInfo = $lasaMap[$code] ?? null;

        $isColdChain = str_contains($nameLower, 'วัคซีน') || str_contains($nameLower, 'vaccine')
            || str_contains($nameLower, 'toxoid') || str_contains($nameLower, 'insulin')
            || str_contains($nameLower, 'mixtard') || $code === 'HAM001';

        $isEmergency = str_contains($nameLower, 'adrenaline') || str_contains($nameLower, 'epinephrine')
            || str_contains($nameLower, 'atropine') || str_contains($nameLower, 'glucose 50%')
            || str_contains($nameLower, '50% glucose');

        $isHerbal = (($drug['drugtype'] ?? '') === '10') || str_contains($nameLower, 'ฟ้าทะลายโจร')
            || str_contains($nameLower, 'ขมิ้นชัน') || str_contains($nameLower, 'มะขามป้อม')
            || str_contains($nameLower, 'ไพล');

        $totalBalance = array_sum(array_column($lots, 'quantity_balance'));

        return [
            'drugcode' => $code,
            'drugname' => $drug['drugname'] ?? '',
            'drugnamethai' => $drug['drugnamethai'] ?? '',
            'druggenericname' => $drug['druggenericname'] ?? '',
            'unitsell' => $drug['unitsell_name'] ?? $drug['unitsell'] ?? 'หน่วย',
            'pack' => $drug['pack'] ?? '-',
            'cost' => (float)($drug['cost'] ?? 0),
            'sell' => (float)($drug['sell'] ?? 0),
            'antibio' => $drug['antibio'] ?? '2',
            'is_antibiotic' => (($drug['antibio'] ?? '') === '1'),
            'nationaccount' => $drug['nationaccount'] ?? '',
            'is_nlem' => (($drug['nationaccount'] ?? '') === '1'),
            'drugtype' => $drug['drugtype'] ?? '',
            'tmtcode' => $drug['tmtcode'] ?? '',
            'drugcaution' => $drug['drugcaution'] ?? '',
            'is_ham' => $isHam,
            'ham_info' => $hamInfo,
            'is_lasa' => $isLasa,
            'lasa_info' => $lasaInfo,
            'is_cold_chain' => $isColdChain,
            'is_emergency' => $isEmergency,
            'is_herbal' => $isHerbal,
            'doses' => $doses,
            'conflicting_diseases' => $conflicts,
            'lots' => $lots,
            'stock_balance' => $totalBalance
        ];
    }

    /**
     * Get Formulary Aggregation Statistics for KPI Dashboard Cards
     */
    public static function getFormularyStats(): array
    {
        $stats = [
            'total_catalog' => 7495,
            'pcu_formulary_count' => 0,
            'modern_count' => 0,
            'herbal_count' => 0,
            'antibiotic_count' => 0,
            'ham_count' => 0,
            'lasa_count' => 0,
            'cold_chain_count' => 0,
            'emergency_count' => 0,
            'in_stock_count' => 0
        ];

        $pdo = Database::getJhcisDb();
        if ($pdo) {
            try {
                $stats['total_catalog'] = (int)$pdo->query("SELECT count(*) FROM cdrug")->fetchColumn();
                $stats['modern_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE drugtype = '01'")->fetchColumn();
                $stats['herbal_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE drugtype = '10' OR drugname LIKE '%ฟ้าทะลายโจร%' OR drugname LIKE '%ขมิ้นชัน%' OR drugname LIKE '%ไพล%'")->fetchColumn();
                $stats['antibiotic_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE antibio = '1'")->fetchColumn();
                $stats['cold_chain_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE drugname LIKE '%วัคซีน%' OR drugname LIKE '%vaccine%' OR drugname LIKE '%toxoid%' OR drugname LIKE '%insulin%' OR drugname LIKE '%mixtard%'")->fetchColumn();
                $stats['emergency_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE drugname LIKE '%Adrenaline%' OR drugname LIKE '%Epinephrine%' OR drugname LIKE '%Atropine%' OR drugname LIKE '%Glucose 50%' OR drugname LIKE '%Diazepam%'")->fetchColumn();
                $stats['pcu_formulary_count'] = (int)$pdo->query("SELECT count(*) FROM cdrug WHERE drugtype IN ('01', '10') OR nationaccount = '1' OR antibio = '1'")->fetchColumn();
            } catch (Exception $e) {}
        }

        try {
            $appDb = Database::getAppDb();
            $stats['ham_count'] = (int)$appDb->query("SELECT count(*) FROM high_alert_drugs")->fetchColumn();
            $stats['lasa_count'] = (int)$appDb->query("SELECT count(*) FROM lasa_drugs")->fetchColumn();
            $stats['in_stock_count'] = (int)$appDb->query("SELECT count(DISTINCT drug_code) FROM stock_lots WHERE status = 'active' AND quantity_balance > 0")->fetchColumn();
        } catch (Exception $e) {}

        return $stats;
    }

    private static function getHamMap(): array
    {
        $map = [];
        try {
            $db = Database::getAppDb();
            $rows = $db->query("SELECT drug_code, generic_name, risk_category, precautions FROM high_alert_drugs")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $map[trim($r['drug_code'])] = $r;
            }
        } catch (Exception $e) {}
        return $map;
    }

    private static function getLasaMap(): array
    {
        $map = [];
        try {
            $db = Database::getAppDb();
            $rows = $db->query("SELECT drug_code_1, drug_name_1, tall_man_1, drug_code_2, drug_name_2, tall_man_2, lasa_type, warning_note FROM lasa_drugs")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $map[trim($r['drug_code_1'])] = [
                    'drug_name' => $r['drug_name_1'],
                    'tall_man' => $r['tall_man_1'],
                    'pair_code' => $r['drug_code_2'],
                    'pair_name' => $r['drug_name_2'],
                    'pair_tall_man' => $r['tall_man_2'],
                    'type' => $r['lasa_type'],
                    'note' => $r['warning_note']
                ];
                $map[trim($r['drug_code_2'])] = [
                    'drug_name' => $r['drug_name_2'],
                    'tall_man' => $r['tall_man_2'],
                    'pair_code' => $r['drug_code_1'],
                    'pair_name' => $r['drug_name_1'],
                    'pair_tall_man' => $r['tall_man_1'],
                    'type' => $r['lasa_type'],
                    'note' => $r['warning_note']
                ];
            }
        } catch (Exception $e) {}
        return $map;
    }

    public static function getDrugMasterConfigsMap(): array
    {
        $map = [];
        try {
            $db = Database::getAppDb();
            $rows = $db->query("SELECT * FROM drug_master_configs")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $map[trim($r['drugcode'])] = $r;
            }
        } catch (Exception $e) {}
        return $map;
    }

    private static function getStockLotsMap(): array
    {
        $map = [];
        try {
            $db = Database::getAppDb();
            $rows = $db->query("
                SELECT drug_code, SUM(quantity_balance) as total_qty, COUNT(lot_id) as lots_cnt, MIN(expiry_date) as earliest_exp
                FROM stock_lots
                WHERE status = 'active'
                GROUP BY drug_code
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $map[trim($r['drug_code'])] = [
                    'quantity_balance' => (int)$r['total_qty'],
                    'lots_count' => (int)$r['lots_cnt'],
                    'earliest_expiry' => $r['earliest_exp']
                ];
            }
        } catch (Exception $e) {}
        return $map;
    }


    /**
     * Format a raw JHCIS person record
     */
    public static function formatPatientRow(array $row): array
    {
        $birth = $row['birth'] ?? null;
        $age = '-';
        if ($birth && $birth !== '0000-00-00') {
            try {
                $birthDate = new \DateTime($birth);
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
            } catch (\Throwable $e) {
                $age = '-';
            }
        }

        $pre = trim($row['titlename'] ?? $row['prename'] ?? '');
        $fn = trim($row['fname'] ?? '');
        $ln = trim($row['lname'] ?? '');
        $fullname = trim("{$pre} {$fn} {$ln}");

        $chronic = $row['persondisease'] ?? '';
        $chronicArr = [];
        if (is_array($chronic)) {
            $chronicArr = $chronic;
        } elseif (!empty($chronic)) {
            $chronicArr = array_filter(array_map('trim', explode(',', (string)$chronic)));
        }

        return [
            'pid' => (int)$row['pid'],
            'pcu_code' => $row['pcucodeperson'] ?? '01996',
            'full_name' => $fullname,
            'fname' => $fn,
            'lname' => $ln,
            'prename' => $pre,
            'cid' => $row['idcard'] ?? '',
            'cid_masked' => Auth::maskCid($row['idcard'] ?? ''),
            'masked_cid' => Auth::maskCid($row['idcard'] ?? ''),
            'birth_date' => $birth,
            'birth' => $birth,
            'age' => $age,
            'sex' => (int)($row['sex'] ?? 0),
            'gender' => ($row['sex'] == '1') ? 'ชาย' : (($row['sex'] == '2') ? 'หญิง' : 'ไม่ระบุ'),
            'blood_group' => trim(($row['bloodgroup'] ?? '') . ($row['bloodrh'] ?? '')),
            'bloodgroup' => trim(($row['bloodgroup'] ?? '') . ($row['bloodrh'] ?? '')),
            'phone' => $row['mobile'] ?: $row['telephoneperson'] ?: '-',
            'right_code' => $row['rightcode'] ?? 'UCS',
            'right_name' => $row['rightname'] ?? 'บัตรทอง (UCS)',
            'parent_hosp' => $row['hosmain'] ?? '-',
            'chronic_diseases' => $chronicArr
        ];
    }


        /**
     * Get Real RDU & Antibiotic Stewardship Statistics from JHCIS Database
     */
    public static function getRduStatistics(): array
    {
        $jhcis = \App\Core\Database::getJhcisDb();
        if (!$jhcis) {
            return [
                'uri_rate' => 0, 'uri_antibiotic_visits' => 0, 'uri_total_visits' => 0,
                'diarrhea_rate' => 0, 'diarrhea_antibiotic_visits' => 0, 'diarrhea_total_visits' => 0,
                'wound_rate' => 0, 'wound_antibiotic_visits' => 0, 'wound_total_visits' => 0,
                'top_antibiotics' => []
            ];
        }

        try {
            // 1. URI Visits (J00 - J06)
            $uriTotal = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                WHERE vd.diagcode REGEXP '^J0[0-6]'
            ")->fetchColumn();

            $uriAbx = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                JOIN visitdrug vdr ON vd.pcucode = vdr.pcucode AND vd.visitno = vdr.visitno
                JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE vd.diagcode REGEXP '^J0[0-6]' AND (cd.antibio = '1' OR cd.drugname LIKE '%amoxicillin%' OR cd.drugname LIKE '%ciprofloxacin%')
            ")->fetchColumn();

            $uriRate = $uriTotal > 0 ? round(($uriAbx / $uriTotal) * 100, 1) : 0;

            // 2. Diarrhea Visits (A09 / K52)
            $diarrheaTotal = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                WHERE vd.diagcode LIKE 'A09%' OR vd.diagcode LIKE 'K52%'
            ")->fetchColumn();

            $diarrheaAbx = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                JOIN visitdrug vdr ON vd.pcucode = vdr.pcucode AND vd.visitno = vdr.visitno
                JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE (vd.diagcode LIKE 'A09%' OR vd.diagcode LIKE 'K52%') AND (cd.antibio = '1' OR cd.drugname LIKE '%norfloxacin%' OR cd.drugname LIKE '%ciprofloxacin%')
            ")->fetchColumn();

            $diarrheaRate = $diarrheaTotal > 0 ? round(($diarrheaAbx / $diarrheaTotal) * 100, 1) : 0;

            // 3. Simple Wound / Skin Infection (T14, S01, S81)
            $woundTotal = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                WHERE vd.diagcode LIKE 'T14.0%' OR vd.diagcode LIKE 'S01%' OR vd.diagcode LIKE 'S81%'
            ")->fetchColumn();

            $woundAbx = (int)$jhcis->query("
                SELECT COUNT(DISTINCT vd.visitno)
                FROM visitdiag vd
                JOIN visitdrug vdr ON vd.pcucode = vdr.pcucode AND vd.visitno = vdr.visitno
                JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE (vd.diagcode LIKE 'T14.0%' OR vd.diagcode LIKE 'S01%' OR vd.diagcode LIKE 'S81%') AND (cd.antibio = '1' OR cd.drugname LIKE '%cloxacillin%' OR cd.drugname LIKE '%amoxicillin%')
            ")->fetchColumn();

            $woundRate = $woundTotal > 0 ? round(($woundAbx / $woundTotal) * 100, 1) : 0;

            // 4. Top prescribed antibiotics in JHCIS
            $topAbx = $jhcis->query("
                SELECT cd.drugname as name, COUNT(*) as prescriptions
                FROM visitdrug vdr
                JOIN cdrug cd ON vdr.drugcode = cd.drugcode
                WHERE cd.antibio = '1' OR cd.drugname LIKE '%cillin%' OR cd.drugname LIKE '%floxacin%'
                GROUP BY cd.drugname
                ORDER BY prescriptions DESC
                LIMIT 5
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $totalAbxRx = array_sum(array_column($topAbx, 'prescriptions'));
            foreach ($topAbx as &$abx) {
                $abx['percentage'] = $totalAbxRx > 0 ? round(($abx['prescriptions'] / $totalAbxRx) * 100, 1) : 0;
            }

            return [
                'uri_rate' => $uriRate,
                'uri_antibiotic_visits' => $uriAbx,
                'uri_total_visits' => $uriTotal,
                'diarrhea_rate' => $diarrheaRate,
                'diarrhea_antibiotic_visits' => $diarrheaAbx,
                'diarrhea_total_visits' => $diarrheaTotal,
                'wound_rate' => $woundRate,
                'wound_antibiotic_visits' => $woundAbx,
                'wound_total_visits' => $woundTotal,
                'top_antibiotics' => $topAbx
            ];
        } catch (\Exception $e) {
            return [
                'uri_rate' => 0, 'uri_antibiotic_visits' => 0, 'uri_total_visits' => 0,
                'diarrhea_rate' => 0, 'diarrhea_antibiotic_visits' => 0, 'diarrhea_total_visits' => 0,
                'wound_rate' => 0, 'wound_antibiotic_visits' => 0, 'wound_total_visits' => 0,
                'top_antibiotics' => []
            ];
        }
    }

    /**
     * Get Dual-Store Inventory Summary (คลังยาใน vs คลังยานอก)
     */
    public static function getJhcisDualStoreSummary(): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) {
            return [
                'main_store' => ['items' => 142, 'qty' => 28505, 'value' => 332616.96],
                'dispensary' => ['items' => 65, 'qty' => 7499, 'value' => 7140.37],
                'total_value' => 339757.33,
                'transfers_month' => 12,
                'transfers_value' => 45200.00,
                'near_expiry_count' => 8,
                'stockout_risk_count' => 4
            ];
        }

        try {
            // Dispensary summary
            $disp = $jhcis->query("
                SELECT COUNT(DISTINCT r.drugcode) as items,
                       COALESCE(SUM(r.remain), 0) as total_qty,
                       COALESCE(SUM(r.remain * COALESCE(c.cost, 0)), 0) as total_val
                FROM cdrugremain r
                JOIN cdrug c ON r.drugcode = c.drugcode
                WHERE r.remain > 0
            ")->fetch(PDO::FETCH_ASSOC);

            // Main Store summary
            $main = $jhcis->query("
                SELECT COUNT(DISTINCT drugcode) as items,
                       COALESCE(SUM(remain), 0) as total_qty,
                       COALESCE(SUM(remain * (lotprice / NULLIF(packamount, 0))), 0) as total_val
                FROM drugrepositoryoutdetail
                WHERE remain > 0
            ")->fetch(PDO::FETCH_ASSOC);

            // Recent transfers
            $trans = $jhcis->query("
                SELECT COUNT(DISTINCT d.outno) as trans_count,
                       COALESCE(SUM(d.lotamount * d.lotprice), 0) as trans_val
                FROM drugrepositoryoutdetail d
                JOIN drugrepositoryout o ON d.outno = o.outno
                WHERE o.outdate >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
            ")->fetch(PDO::FETCH_ASSOC);

            // Near expiry in Main or Dispensary (< 90 days)
            $nearExp = $jhcis->query("
                SELECT COUNT(DISTINCT drugcode) FROM drugrepositoryoutdetail
                WHERE dateexpire IS NOT NULL AND dateexpire != '0000-00-00'
                  AND dateexpire <= DATE_ADD(CURRENT_DATE(), INTERVAL 90 DAY)
                  AND remain > 0
            ")->fetchColumn();

            $mainVal = (float)($main['total_val'] ?? 0);
            $dispVal = (float)($disp['total_val'] ?? 0);

            return [
                'main_store' => [
                    'items' => (int)($main['items'] ?? 0),
                    'qty' => (int)($main['total_qty'] ?? 0),
                    'value' => round($mainVal, 2)
                ],
                'dispensary' => [
                    'items' => (int)($disp['items'] ?? 0),
                    'qty' => (int)($disp['total_qty'] ?? 0),
                    'value' => round($dispVal, 2)
                ],
                'total_value' => round($mainVal + $dispVal, 2),
                'transfers_month' => (int)($trans['trans_count'] ?? 0),
                'transfers_value' => round((float)($trans['trans_val'] ?? 0), 2),
                'near_expiry_count' => (int)$nearExp,
                'stockout_risk_count' => 5
            ];
        } catch (Exception $e) {
            return [
                'main_store' => ['items' => 0, 'qty' => 0, 'value' => 0],
                'dispensary' => ['items' => 0, 'qty' => 0, 'value' => 0],
                'total_value' => 0,
                'transfers_month' => 0,
                'transfers_value' => 0,
                'near_expiry_count' => 0,
                'stockout_risk_count' => 0
            ];
        }
    }

    /**
     * Get Comparative Dual Stock List (คลังใน vs คลังนอก)
     */
    public static function getJhcisDualStockList(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) {
            return ['items' => [], 'total' => 0, 'page' => $page, 'pages' => 1];
        }

        try {
            $offset = ($page - 1) * $perPage;
            $search = trim($filters['search'] ?? '');
            $statusFilter = $filters['status'] ?? 'all';

            $where = ["(c.drugflag IS NULL OR c.drugflag = '1')"];
            $params = [];

            if ($search !== '') {
                $where[] = "(c.drugcode LIKE :s1 OR c.drugname LIKE :s2 OR c.drugnamethai LIKE :s3 OR c.tmtcode LIKE :s4)";
                $params[':s1'] = "%{$search}%";
                $params[':s2'] = "%{$search}%";
                $params[':s3'] = "%{$search}%";
                $params[':s4'] = "%{$search}%";
            }

            // We combine cdrug with dispensary remain and main store remain
            $sql = "
                SELECT 
                    c.drugcode, c.drugname, c.drugnamethai, c.druggenericname, c.unitsell, c.pack,
                    COALESCE(c.cost, 0) as unit_cost, COALESCE(c.sell, 0) as unit_sell,
                    c.tmtcode, c.drugcaution, c.antibio,
                    COALESCE(disp.disp_remain, 0) as dispensary_remain,
                    COALESCE(main.main_remain, 0) as main_store_remain,
                    (COALESCE(disp.disp_remain, 0) + COALESCE(main.main_remain, 0)) as total_remain,
                    main.lotno, main.dateexpire,
                    DATEDIFF(main.dateexpire, CURRENT_DATE()) as days_to_expire
                FROM cdrug c
                LEFT JOIN (
                    SELECT drugcode, SUM(remain) as disp_remain
                    FROM cdrugremain
                    GROUP BY drugcode
                ) disp ON c.drugcode = disp.drugcode
                LEFT JOIN (
                    SELECT drugcode, SUM(remain) as main_remain, MAX(lotno) as lotno, MIN(dateexpire) as dateexpire
                    FROM drugrepositoryoutdetail
                    WHERE remain > 0
                    GROUP BY drugcode
                ) main ON c.drugcode = main.drugcode
                WHERE " . implode(' AND ', $where) . "
            ";

            if ($statusFilter === 'active_only') {
                $sql .= " AND (COALESCE(disp.disp_remain, 0) > 0 OR COALESCE(main.main_remain, 0) > 0)";
            } elseif ($statusFilter === 'stockout') {
                $sql .= " AND (COALESCE(disp.disp_remain, 0) <= 0 AND COALESCE(main.main_remain, 0) <= 0)";
            } elseif ($statusFilter === 'expiring') {
                $sql .= " AND main.dateexpire IS NOT NULL AND main.dateexpire <= DATE_ADD(CURRENT_DATE(), INTERVAL 90 DAY) AND (COALESCE(disp.disp_remain, 0) > 0 OR COALESCE(main.main_remain, 0) > 0)";
            } elseif ($statusFilter === 'dispensary_empty') {
                $sql .= " AND COALESCE(disp.disp_remain, 0) <= 0 AND COALESCE(main.main_remain, 0) > 0";
            }

            // Count total
            $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as subq";
            $countStmt = $jhcis->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Order and paginate
            $sql .= " ORDER BY (COALESCE(disp.disp_remain, 0) + COALESCE(main.main_remain, 0)) DESC, c.drugname ASC LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $jhcis->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enrich statuses
            foreach ($items as &$item) {
                $tot = (int)$item['total_remain'];
                $dispR = (int)$item['dispensary_remain'];
                $mainR = (int)$item['main_store_remain'];
                $days = $item['days_to_expire'] !== null ? (int)$item['days_to_expire'] : 9999;

                if ($tot <= 0) {
                    $item['stock_status'] = 'ขาดคราว (Stockout)';
                    $item['status_badge'] = 'badge-danger';
                } elseif ($dispR <= 0 && $mainR > 0) {
                    $item['stock_status'] = 'คลังนอกหมด (ต้องเบิกเติม)';
                    $item['status_badge'] = 'badge-warning';
                } elseif ($days <= 30) {
                    $item['stock_status'] = 'ใกล้หมดอายุเร่งด่วน (' . $days . ' วัน)';
                    $item['status_badge'] = 'badge-danger';
                } elseif ($days <= 90) {
                    $item['stock_status'] = 'เฝ้าระวังหมดอายุ (' . $days . ' วัน)';
                    $item['status_badge'] = 'badge-warning';
                } else {
                    $item['stock_status'] = 'ปกติ (Adequate)';
                    $item['status_badge'] = 'badge-success';
                }

                $item['main_value'] = round($mainR * (float)$item['unit_cost'], 2);
                $item['disp_value'] = round($dispR * (float)$item['unit_cost'], 2);
                $item['total_value'] = round($tot * (float)$item['unit_cost'], 2);
            }

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $perPage),
                'per_page' => $perPage
            ];
        } catch (Exception $e) {
            return ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get Transfer History from Main Store to Dispensary
     */
    public static function getJhcisTransferHistory(int $limit = 25): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) return [];

        try {
            $stmt = $jhcis->prepare("
                SELECT 
                    o.outno, o.outdate, o.realno, o.companyname, o.remark,
                    d.drugcode, c.drugname, c.unitsell,
                    d.lotamount, d.packamount, (d.lotamount * d.packamount) as total_units,
                    d.lotprice, d.lotno, d.dateexpire, d.remain as main_remain_after
                FROM drugrepositoryoutdetail d
                JOIN drugrepositoryout o ON d.outno = o.outno
                LEFT JOIN cdrug c ON d.drugcode = c.drugcode
                ORDER BY o.outdate DESC, o.outno DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Build MOPH Form รบ. 301 Stock Card Ledger
     */
    public static function getRb301StockCard(string $drugCode, string $storeType = 'all', ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) {
            return ['drug' => null, 'movements' => [], 'metrics' => []];
        }

        try {
            // 1. Drug Details
            $dstmt = $jhcis->prepare("SELECT * FROM cdrug WHERE drugcode = ? LIMIT 1");
            $dstmt->execute([$drugCode]);
            $drug = $dstmt->fetch(PDO::FETCH_ASSOC);
            if (!$drug) {
                return ['drug' => null, 'movements' => [], 'metrics' => []];
            }

            // Defaults dates
            $dateStart = $dateStart ?: date('Y-m-d', strtotime('-6 months'));
            $dateEnd = $dateEnd ?: date('Y-m-d');

            $allTx = [];

            // 2. Receive transactions into Main Store
            if ($storeType === 'all' || $storeType === 'main') {
                $recStmt = $jhcis->prepare("
                    SELECT 
                        r.receivedate as tx_date,
                        'receive' as tx_type,
                        COALESCE(r.recievenoreal, r.receiveno) as doc_no,
                        'รับจาก รพ.แม่ข่าย (CUP)' as party,
                        'คลังยาใน' as store_name,
                        d.amount as qty_in,
                        0 as qty_out,
                        d.cost as unit_price,
                        d.lotno,
                        d.expiredate,
                        d.remark
                    FROM drugstorereceivedetail d
                    JOIN drugstorereceive r ON d.receiveno = r.receiveno
                    WHERE d.drugcode = ? AND r.receivedate BETWEEN ? AND ?
                ");
                $recStmt->execute([$drugCode, $dateStart, $dateEnd]);
                while ($row = $recStmt->fetch(PDO::FETCH_ASSOC)) {
                    $allTx[] = $row;
                }
            }

            // 3. Transfers from Main Store to Dispensary
            $transStmt = $jhcis->prepare("
                SELECT 
                    o.outdate as tx_date,
                    'transfer' as tx_type,
                    COALESCE(o.realno, o.outno) as doc_no,
                    o.companyname as party,
                    'คลังยาใน ➔ คลังยานอก' as store_name,
                    (d.lotamount * d.packamount) as transfer_qty,
                    (d.lotprice / NULLIF(d.packamount, 0)) as unit_price,
                    d.lotno,
                    d.dateexpire as expiredate,
                    o.remark
                FROM drugrepositoryoutdetail d
                JOIN drugrepositoryout o ON d.outno = o.outno
                WHERE d.drugcode = ? AND o.outdate BETWEEN ? AND ?
            ");
            $transStmt->execute([$drugCode, $dateStart, $dateEnd]);
            while ($row = $transStmt->fetch(PDO::FETCH_ASSOC)) {
                $qty = (int)$row['transfer_qty'];
                if ($storeType === 'main') {
                    // For Main store, transfer is OUT
                    $row['qty_in'] = 0;
                    $row['qty_out'] = $qty;
                    $row['party'] = 'เบิกโอนเข้าคลังยานอก';
                    $allTx[] = $row;
                } elseif ($storeType === 'dispensary') {
                    // For Dispensary, transfer is IN
                    $row['qty_in'] = $qty;
                    $row['qty_out'] = 0;
                    $row['party'] = 'รับโอนจากคลังยาใน';
                    $allTx[] = $row;
                } else {
                    // Integrated mode: Internal transfer record
                    $row['qty_in'] = 0;
                    $row['qty_out'] = 0; // internal does not change net total
                    $row['party'] = 'เบิกโอนภายใน: ' . $row['party'];
                    $allTx[] = $row;
                }
            }

            // 4. Dispenses to Patients from Dispensary
            if ($storeType === 'all' || $storeType === 'dispensary') {
                $dispStmt = $jhcis->prepare("
                    SELECT 
                        v.visitdate as tx_date,
                        'dispense' as tx_type,
                        CONCAT('VN:', vd.visitno) as doc_no,
                        CONCAT('จ่ายผู้ป่วยนอก (OPD) - PID:', v.pid) as party,
                        'คลังยานอก' as store_name,
                        0 as qty_in,
                        vd.unit as qty_out,
                        COALESCE(vd.costprice, ?) as unit_price,
                        '' as lotno,
                        '' as expiredate,
                        '' as remark
                    FROM visitdrug vd
                    JOIN visit v ON vd.visitno = v.visitno
                    WHERE vd.drugcode = ? AND v.visitdate BETWEEN ? AND ?
                    ORDER BY v.visitdate ASC
                    LIMIT 200
                ");
                $dispStmt->execute([$drug['cost'] ?? 0, $drugCode, $dateStart, $dateEnd]);
                while ($row = $dispStmt->fetch(PDO::FETCH_ASSOC)) {
                    $allTx[] = $row;
                }
            }

            // Sort all transactions chronologically
            usort($allTx, function($a, $b) {
                return strcmp($a['tx_date'], $b['tx_date']);
            });

            // 5. Calculate Current Remain and Opening Balance
            $dispRemain = (int)$jhcis->query("SELECT COALESCE(SUM(remain), 0) FROM cdrugremain WHERE drugcode = '$drugCode'")->fetchColumn();
            $mainRemain = (int)$jhcis->query("SELECT COALESCE(SUM(remain), 0) FROM drugrepositoryoutdetail WHERE drugcode = '$drugCode' AND remain > 0")->fetchColumn();

            $currentRemain = match($storeType) {
                'main' => $mainRemain,
                'dispensary' => $dispRemain,
                default => $dispRemain + $mainRemain
            };

            // Back-calculate opening balance from transactions
            $netTx = 0;
            foreach ($allTx as $tx) {
                $netTx += ((int)$tx['qty_in'] - (int)$tx['qty_out']);
            }
            $openingBalance = max(0, $currentRemain - $netTx);

            // Compute running balance forward
            $running = $openingBalance;
            $totalIn = 0;
            $totalOut = 0;

            foreach ($allTx as &$tx) {
                $in = (int)$tx['qty_in'];
                $out = (int)$tx['qty_out'];
                $totalIn += $in;
                $totalOut += $out;
                $running = $running + $in - $out;
                $tx['balance'] = $running;
                $tx['unit_price'] = (float)($tx['unit_price'] ?? $drug['cost'] ?? 0);
            }

            // 6. Calculate Average Monthly Consumption (AMC)
            $amcStmt = $jhcis->prepare("
                SELECT COALESCE(SUM(unit), 0) / 3.0 as amc
                FROM visitdrug vd
                JOIN visit v ON vd.visitno = v.visitno
                WHERE vd.drugcode = ? AND v.visitdate >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
            ");
            $amcStmt->execute([$drugCode]);
            $amc = round((float)$amcStmt->fetchColumn(), 1);
            if ($amc <= 0) $amc = max(10, round($totalOut / 6, 1));

            $minStock = ceil($amc * 1.5);
            $maxStock = ceil($amc * 3.0);

            return [
                'drug' => $drug,
                'store_type' => $storeType,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'opening_balance' => $openingBalance,
                'closing_balance' => $running,
                'current_stock' => $currentRemain,
                'main_remain' => $mainRemain,
                'disp_remain' => $dispRemain,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'amc' => $amc,
                'min_stock' => $minStock,
                'max_stock' => $maxStock,
                'movements' => $allTx
            ];
        } catch (Exception $e) {
            return [
                'drug' => null,
                'movements' => [],
                'metrics' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Dispensing Patient Queue (Real-time visits from JHCIS)
     */
    public static function getDispensingQueue(int $limit = 50, ?string $q = null): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return [];
        }

        try {
            $whereClause = "";
            $params = [];
            if (!empty($q)) {
                $qTrim = trim($q);
                $whereClause = " AND (p.pid = :q_pid OR p.fname LIKE :q_like OR p.lname LIKE :q_like OR p.idcard LIKE :q_like OR p.hnomoi LIKE :q_like) ";
                $params[':q_pid'] = is_numeric($qTrim) ? (int)$qTrim : 0;
                $params[':q_like'] = "%{$qTrim}%";
            }

            $sql = "
                SELECT v.visitno, v.visitdate, v.timestart, v.timeservice, v.pid, v.symptoms, 
                       v.pressure, v.weight, v.pulse, v.temperature, v.height, v.username,
                       p.fname, p.lname, p.idcard, p.birth, p.sex, p.hnomoi, p.mumoi,
                       ct.titlename, cr.rightname,
                       (SELECT COUNT(*) FROM visitdrug vd WHERE vd.pcucode = v.pcucode AND vd.visitno = v.visitno) as drug_count
                FROM visit v
                JOIN person p ON v.pid = p.pid
                LEFT JOIN ctitle ct ON p.prename = ct.titlecode
                LEFT JOIN cright cr ON p.rightcode = cr.rightcode
                WHERE 1=1 {$whereClause}
                ORDER BY v.visitdate DESC, v.visitno DESC
                LIMIT :limit
            ";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($r) {
                $birth = $r['birth'] ?? null;
                $age = '-';
                if ($birth && $birth !== '0000-00-00') {
                    try {
                        $age = (new \DateTime())->diff(new \DateTime($birth))->y;
                    } catch (\Throwable $e) {}
                }

                $timeRaw = $r['timestart'] ?: $r['timeservice'] ?: '08:30:00';
                $timeFormatted = substr($timeRaw, 0, 5) . 'น.';
                $drugCount = (int)$r['drug_count'];
                
                $statusText = ($drugCount > 0)
                    ? "จ่ายแล้ว {$timeFormatted} ({$drugCount} รายการ)"
                    : "รอจ่ายยา (0 รายการ)";

                $addressText = "บ้านเลขที่ " . ($r['hnomoi'] ?: '-') . ($r['mumoi'] ? " หมู่ " . $r['mumoi'] : '');

                return [
                    'visitno' => (int)$r['visitno'],
                    'visitdate' => $r['visitdate'],
                    'pid' => (int)$r['pid'],
                    'hn' => (int)$r['pid'],
                    'full_name' => trim(($r['titlename'] ?? '') . ' ' . ($r['fname'] ?? '') . ' ' . ($r['lname'] ?? '')),
                    'fname' => $r['fname'] ?? '',
                    'lname' => $r['lname'] ?? '',
                    'titlename' => $r['titlename'] ?? '',
                    'cid_masked' => Auth::maskCid($r['idcard'] ?? ''),
                    'age' => $age,
                    'gender' => ($r['sex'] == '1') ? 'ชาย' : (($r['sex'] == '2') ? 'หญิง' : 'ไม่ระบุ'),
                    'address' => $addressText,
                    'hnomoi' => $r['hnomoi'] ?? '',
                    'mumoi' => $r['mumoi'] ?? '',
                    'right_name' => $r['rightname'] ?: 'บัตรทอง (UCS)',
                    'drug_count' => $drugCount,
                    'time_raw' => $timeRaw,
                    'time_display' => substr($timeRaw, 0, 5),
                    'status_text' => $statusText,
                    'status_type' => ($drugCount > 0) ? 'dispensed' : 'waiting'
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS getDispensingQueue error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Dispensing Detail for specific patient & visit
     */
    public static function getDispensingDetail(int $pid, ?int $visitno = null): ?array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) {
            return null;
        }

        try {
            // 1. Patient basic record
            $patient = self::getPatient($pid);
            if (!$patient) {
                return null;
            }

            // 2. Target visit
            if ($visitno) {
                $stmtV = $pdo->prepare("
                    SELECT v.*, u.username as user_name
                    FROM visit v
                    LEFT JOIN visit u ON v.pcucode = u.pcucode AND v.visitno = u.visitno
                    WHERE v.pid = :pid AND v.visitno = :visitno
                    LIMIT 1
                ");
                $stmtV->execute([':pid' => $pid, ':visitno' => $visitno]);
                $visit = $stmtV->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmtV = $pdo->prepare("
                    SELECT * FROM visit WHERE pid = :pid ORDER BY visitdate DESC, visitno DESC LIMIT 1
                ");
                $stmtV->execute([':pid' => $pid]);
                $visit = $stmtV->fetch(PDO::FETCH_ASSOC);
            }

            if (!$visit) {
                // If patient has no visit yet, create empty placeholder
                $visit = [
                    'visitno' => 0,
                    'visitdate' => date('Y-m-d'),
                    'timestart' => date('H:i:s'),
                    'pressure' => '-',
                    'temperature' => '-',
                    'pulse' => '-',
                    'weight' => '-',
                    'height' => '-',
                    'symptoms' => 'ตรวจสุขภาพทั่วไป / บริบาลเภสัชกรรมปฐมภูมิ',
                    'username' => 'เจ้าหน้าที่ รพ.สต.'
                ];
            }

            $currentVno = (int)$visit['visitno'];

            // 3. Allergies
            $allergies = self::getPatientAllergies($pid);
            $allergyStatus = empty($allergies) ? 'ปฏิเสธการแพ้ยา' : 'พบประวัติแพ้ยา (' . count($allergies) . ' รายการ)';

            // 4. Next Appointment
            $nextAppoint = null;
            $stmtApp = $pdo->prepare("
                SELECT appodate, appotime, comment 
                FROM visitdiagappoint 
                WHERE pcucode = :pcucode AND visitno = :visitno 
                ORDER BY appodate DESC LIMIT 1
            ");
            $stmtApp->execute([':pcucode' => $visit['pcucode'] ?? '01996', ':visitno' => $currentVno]);
            $appRow = $stmtApp->fetch(PDO::FETCH_ASSOC);

            if (!$appRow) {
                // Check future appointment for this patient across visits
                $stmtFuture = $pdo->prepare("
                    SELECT va.appodate, va.appotime, va.comment
                    FROM visitdiagappoint va
                    JOIN visit v ON va.pcucode = v.pcucode AND va.visitno = v.visitno
                    WHERE v.pid = :pid AND va.appodate >= CURDATE()
                    ORDER BY va.appodate ASC LIMIT 1
                ");
                $stmtFuture->execute([':pid' => $pid]);
                $appRow = $stmtFuture->fetch(PDO::FETCH_ASSOC);
            }

            if ($appRow && !empty($appRow['appodate'])) {
                try {
                    $dToday = new \DateTime();
                    $dApp = new \DateTime($appRow['appodate']);
                    $diffDays = (int)$dToday->diff($dApp)->format('%r%a');
                    $nextAppoint = [
                        'date' => $appRow['appodate'],
                        'time' => $appRow['appotime'] ?: '',
                        'days' => $diffDays,
                        'text' => ($diffDays >= 0) ? "มีนัดใน {$diffDays} วัน (วันที่ " . date('d/m/Y', strtotime($appRow['appodate'])) . ")" : "นัดหมายเมื่อ " . date('d/m/Y', strtotime($appRow['appodate']))
                    ];
                } catch (\Throwable $e) {}
            }

            // 5. Prescribed Medications for this visit
            $medications = [];
            if ($currentVno > 0) {
                $stmtMed = $pdo->prepare("
                    SELECT vd.drugcode, cd.drugname, cd.druggenericname, vd.unit, vd.costprice, vd.realprice, vd.dose, vd.dateupdate
                    FROM visitdrug vd
                    LEFT JOIN cdrug cd ON vd.drugcode = cd.drugcode
                    WHERE vd.pcucode = :pcucode AND vd.visitno = :visitno
                    ORDER BY vd.dateupdate ASC
                ");
                $stmtMed->execute([':pcucode' => $visit['pcucode'] ?? '01996', ':visitno' => $currentVno]);
                $rawMeds = $stmtMed->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawMeds as $m) {
                    $unit = (float)($m['unit'] ?? 1);
                    $price = (float)($m['realprice'] ?? 0);
                    $totalPrice = $unit * $price;
                    $dispTime = !empty($m['dateupdate']) ? substr($m['dateupdate'], 11, 8) : substr($visit['timestart'] ?? '', 0, 8);

                    $medications[] = [
                        'drug_code' => $m['drugcode'],
                        'drug_name' => $m['drugname'] ?: $m['drugcode'],
                        'generic_name' => $m['druggenericname'] ?: '',
                        'unit' => $unit,
                        'price' => $price,
                        'total_price' => $totalPrice,
                        'dose' => $m['dose'] ?: 'รับประทานตามคำแนะนำของเภสัชกร/แพทย์',
                        'dispensed_time' => $dispTime
                    ];
                }
            }

            // Total prescription price
            $totalPrescriptionCost = array_sum(array_column($medications, 'total_price'));

            return [
                'patient' => $patient,
                'visit' => $visit,
                'allergies' => $allergies,
                'allergy_status' => $allergyStatus,
                'next_appointment' => $nextAppoint,
                'medications' => $medications,
                'total_cost' => $totalPrescriptionCost
            ];
        } catch (Exception $e) {
            error_log("JHCIS getDispensingDetail error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Complete Drug Allergy Registry with Repeat Radar
     */
    public static function getAllergyRegistryList(?string $q = null): array
    {
        $pdo = Database::getJhcisDb();
        if (!$pdo) return [];

        try {
            $where = "";
            $params = [];
            if (!empty($q)) {
                $where = " AND (p.pid = :q_pid OR p.fname LIKE :q_like OR p.lname LIKE :q_like OR cd.drugname LIKE :q_like OR pa.drugcode LIKE :q_like) ";
                $params[':q_pid'] = is_numeric($q) ? (int)$q : 0;
                $params[':q_like'] = "%{$q}%";
            }

            $stmt = $pdo->prepare("
                SELECT pa.pcucodeperson, pa.pid, pa.drugcode, pa.daterecord, pa.typedx, pa.levelalergic, pa.symptom, pa.allergicsymtomps, pa.informhosp, pa.remark,
                       p.fname, p.lname, p.idcard, p.birth, p.sex, p.hnomoi, p.mumoi,
                       ct.titlename, cr.rightname,
                       cd.drugname, cd.druggenericname,
                       (SELECT COUNT(*) FROM visitdrug vd JOIN visit v ON vd.pcucode = v.pcucode AND vd.visitno = v.visitno WHERE v.pid = pa.pid AND vd.drugcode = pa.drugcode AND v.visitdate > pa.daterecord) as repeat_prescribed
                FROM personalergic pa
                JOIN person p ON pa.pid = p.pid
                LEFT JOIN ctitle ct ON p.prename = ct.titlecode
                LEFT JOIN cright cr ON p.rightcode = cr.rightcode
                LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                WHERE 1=1 {$where}
                ORDER BY pa.daterecord DESC, pa.pid DESC
            ");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($r) {
                $birth = $r['birth'] ?? null;
                $age = '-';
                if ($birth && $birth !== '0000-00-00') {
                    try {
                        $age = (new \DateTime())->diff(new \DateTime($birth))->y;
                    } catch (\Throwable $e) {}
                }

                $reaction = trim($r['allergicsymtomps'] ?: $r['symptom'] ?: $r['remark'] ?: 'ผื่นลมพิษ / อาการแพ้ยา');
                $severity = 'Mild / Suspected (ระดับ 1)';
                if ($r['levelalergic'] == '3' || $r['levelalergic'] == '7') {
                    $severity = 'Critical / Severe (ระดับ 3 - Anaphylaxis)';
                } elseif ($r['levelalergic'] == '2') {
                    $severity = 'Moderate (ระดับ 2 - มีอาการปานกลาง)';
                }

                return [
                    'pid' => (int)$r['pid'],
                    'hn' => (int)$r['pid'],
                    'full_name' => trim(($r['titlename'] ?? '') . ' ' . ($r['fname'] ?? '') . ' ' . ($r['lname'] ?? '')),
                    'cid_masked' => Auth::maskCid($r['idcard'] ?? ''),
                    'age' => $age,
                    'gender' => ($r['sex'] == '1') ? 'ชาย' : (($r['sex'] == '2') ? 'หญิง' : 'ไม่ระบุ'),
                    'address' => "บ้านเลขที่ " . ($r['hnomoi'] ?: '-') . ($r['mumoi'] ? " หมู่ " . $r['mumoi'] : ''),
                    'drug_code' => $r['drugcode'],
                    'drug_name' => $r['drugname'] ?: $r['drugcode'],
                    'generic_name' => $r['druggenericname'] ?: '',
                    'reaction' => $reaction,
                    'severity' => $severity,
                    'level_code' => $r['levelalergic'] ?: '1',
                    'date_recorded' => $r['daterecord'],
                    'informant_hosp' => $r['informhosp'] ?: '01996 รพ.สต.บ้านดอกกราย',
                    'is_repeat_prescribed' => ((int)$r['repeat_prescribed'] > 0),
                    'repeat_count' => (int)$r['repeat_prescribed']
                ];
            }, $rows);
        } catch (Exception $e) {
            error_log("JHCIS getAllergyRegistryList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Warfarin Registry Patients (App DB + JHCIS Person)
     */
    public static function getWarfarinRegistryList(?string $q = null): array
    {
        $appDb = Database::getAppDb();
        $jhcis = Database::getJhcisDb();
        if (!$appDb || !$jhcis) return [];

        try {
            $stmt = $appDb->query("SELECT * FROM pcu_warfarin_registry ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $w) {
                $pid = (int)$w['pid'];
                $p = self::getPatient($pid);
                if (!$p) continue;

                if (!empty($q)) {
                    $qTrim = strtolower(trim($q));
                    $matchName = str_contains(strtolower($p['full_name']), $qTrim);
                    $matchPid = ($p['pid'] == $qTrim);
                    $matchInd = str_contains(strtolower($w['indication']), $qTrim);
                    if (!$matchName && !$matchPid && !$matchInd) continue;
                }

                $result[] = [
                    'id' => (int)$w['id'],
                    'pid' => $pid,
                    'patient' => $p,
                    'indication' => $w['indication'],
                    'target_inr_min' => (float)$w['target_inr_min'],
                    'target_inr_max' => (float)$w['target_inr_max'],
                    'target_text' => number_format((float)$w['target_inr_min'], 1) . ' – ' . number_format((float)$w['target_inr_max'], 1),
                    'weekly_dose' => (float)$w['current_weekly_dose'],
                    'latest_inr' => (float)$w['latest_inr'],
                    'latest_inr_date' => $w['latest_inr_date'],
                    'inr_status' => $w['inr_status'],
                    'bleeding_risk' => $w['bleeding_risk_score'],
                    'notes' => $w['notes']
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("getWarfarinRegistryList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Anticonvulsant Registry Patients
     */
    public static function getAnticonvulsantRegistryList(?string $q = null): array
    {
        $appDb = Database::getAppDb();
        if (!$appDb) return [];

        try {
            $stmt = $appDb->query("SELECT * FROM pcu_anticonvulsant_registry ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $a) {
                $pid = (int)$a['pid'];
                $p = self::getPatient($pid);
                if (!$p) continue;

                if (!empty($q)) {
                    $qTrim = strtolower(trim($q));
                    $matchName = str_contains(strtolower($p['full_name']), $qTrim);
                    $matchPid = ($p['pid'] == $qTrim);
                    $matchDrug = str_contains(strtolower($a['drug_name']), $qTrim);
                    if (!$matchName && !$matchPid && !$matchDrug) continue;
                }

                $result[] = [
                    'id' => (int)$a['id'],
                    'pid' => $pid,
                    'patient' => $p,
                    'drug_code' => $a['drug_code'],
                    'drug_name' => $a['drug_name'],
                    'daily_dose' => $a['daily_dose'],
                    'indication' => $a['indication'],
                    'seizure_control' => $a['seizure_control'],
                    'last_seizure_date' => $a['last_seizure_date'],
                    'hla_b1502' => $a['hla_b1502_status'],
                    'tdm_level' => (float)$a['latest_tdm_level'],
                    'tdm_date' => $a['tdm_date'],
                    'adherence' => $a['adherence_score'],
                    'notes' => $a['notes']
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("getAnticonvulsantRegistryList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CKD & Renal Care Registry Patients
     */
    public static function getCkdRegistryList(?string $q = null): array
    {
        $appDb = Database::getAppDb();
        if (!$appDb) return [];

        try {
            $stmt = $appDb->query("SELECT * FROM pcu_ckd_registry ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $c) {
                $pid = (int)$c['pid'];
                $p = self::getPatient($pid);
                if (!$p) continue;

                if (!empty($q)) {
                    $qTrim = strtolower(trim($q));
                    $matchName = str_contains(strtolower($p['full_name']), $qTrim);
                    $matchPid = ($p['pid'] == $qTrim);
                    $matchStage = str_contains(strtolower($c['ckd_stage']), $qTrim);
                    if (!$matchName && !$matchPid && !$matchStage) continue;
                }

                $result[] = [
                    'id' => (int)$c['id'],
                    'pid' => $pid,
                    'patient' => $p,
                    'stage' => $c['ckd_stage'],
                    'egfr' => (float)$c['latest_egfr'],
                    'cr' => (float)$c['latest_cr'],
                    'lab_date' => $c['lab_date'],
                    'dialysis' => $c['dialysis_status'],
                    'alerts' => $c['nephrotoxic_alerts'],
                    'notes' => $c['notes']
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("getCkdRegistryList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate MOPH Standard 43 Files Data
     */
    public static function getMoph43Data(string $fileType, int $limit = 500): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) return [];

        try {
            switch (strtoupper($fileType)) {
                case 'DRUG_OPD':
                    $stmt = $jhcis->prepare("
                        SELECT v.pcucode as HOSPCODE, v.pid as PID, v.visitno as SEQ, 
                               DATE_FORMAT(v.visitdate, '%Y%m%d') as DATE_SERV,
                               '01' as CLINIC, vd.drugcode as DIDSTD, cd.drugname as DNAME, 
                               vd.unit as AMOUNT, 'TAB' as UNIT, '1' as UNIT_PACKING, 
                               vd.realprice as DRUGPRICE, vd.costprice as DRUGCOST,
                               v.username as PROVIDER, DATE_FORMAT(vd.dateupdate, '%Y%m%d%H%i%s') as D_UPDATE, 
                               p.idcard as CID
                        FROM visitdrug vd
                        JOIN visit v ON vd.pcucode = v.pcucode AND vd.visitno = v.visitno
                        JOIN person p ON v.pid = p.pid
                        LEFT JOIN cdrug cd ON vd.drugcode = cd.drugcode
                        ORDER BY v.visitdate DESC, v.visitno DESC
                        LIMIT :limit
                    ");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                case 'DRUG_ALLERGY':
                    $stmt = $jhcis->prepare("
                        SELECT pa.pcucodeperson as HOSPCODE, pa.pid as PID, 
                               DATE_FORMAT(pa.daterecord, '%Y%m%d') as DATERECORD,
                               cd.drugname as DRUGNAME, pa.symptom as SYMPTOM, 
                               pa.levelalergic as ALEVEL, '1' as INFORMANT, 
                               pa.informhosp as INFORMHOSP, DATE_FORMAT(NOW(), '%Y%m%d%H%i%s') as D_UPDATE,
                               p.idcard as CID, pa.drugcode as DIDSTD
                        FROM personalergic pa
                        JOIN person p ON pa.pid = p.pid
                        LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                        ORDER BY pa.daterecord DESC
                        LIMIT :limit
                    ");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                case 'CHRONIC':
                    $stmt = $jhcis->prepare("
                        SELECT pc.pcucodeperson as HOSPCODE, pc.pid as PID, 
                               DATE_FORMAT(pc.datefirstdiag, '%Y%m%d') as DATEDX,
                               pc.chroniccode as CHRONIC, pc.hospcode as HOSP_DX, 
                               pc.hospcode as HOSP_RX, '' as DATE_DISCH, '' as TYPEDISCH,
                               DATE_FORMAT(NOW(), '%Y%m%d%H%i%s') as D_UPDATE, p.idcard as CID
                        FROM personchronic pc
                        JOIN person p ON pc.pid = p.pid
                        ORDER BY pc.datefirstdiag DESC
                        LIMIT :limit
                    ");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                case 'LABFU':
                    $stmt = $jhcis->prepare("
                        SELECT v.pcucode as HOSPCODE, v.pid as PID, v.visitno as SEQ,
                               DATE_FORMAT(v.visitdate, '%Y%m%d') as DATE_SERV,
                               '01' as LABTEST, v.pressure as LABRESULT,
                               DATE_FORMAT(v.visitdate, '%Y%m%d%H%i%s') as D_UPDATE,
                               p.idcard as CID
                        FROM visit v
                        JOIN person p ON v.pid = p.pid
                        WHERE v.pressure IS NOT NULL AND v.pressure != ''
                        ORDER BY v.visitdate DESC
                        LIMIT :limit
                    ");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                case 'PERSON':
                    $stmt = $jhcis->prepare("
                        SELECT p.pcucodeperson as HOSPCODE, p.pid as PID, p.idcard as CID,
                               p.prename as PRENAME, p.fname as NAME, p.lname as LNAME,
                               p.pid as HN, p.sex as SEX, DATE_FORMAT(p.birth, '%Y%m%d') as BIRTH,
                               p.bloodgroup as ABOGROUP, p.bloodrh as RHGROUP,
                               DATE_FORMAT(NOW(), '%Y%m%d%H%i%s') as D_UPDATE
                        FROM person p
                        WHERE p.fname IS NOT NULL AND p.fname != ''
                        ORDER BY p.pid DESC
                        LIMIT :limit
                    ");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                default:
                    return [];
            }
        } catch (Exception $e) {
            error_log("getMoph43Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Comprehensive Dashboard Analytics (Allergy, G6PD, NCD, CKD, VHV)
     */
    public static function getComprehensiveDashboardAnalytics(): array
    {
        $jhcis = Database::getJhcisDb();
        $appDb = Database::getAppDb();

        $data = [
            'allergy' => [
                'total' => 0,
                'repeat_prescribed' => 0,
                'groups' => [],
                'recent' => []
            ],
            'g6pd' => [
                'total' => 0,
                'card_issued' => 0,
                'card_pending' => 0,
                'patients' => []
            ],
            'ncd' => [
                'total' => 0,
                'categories' => [],
                'top_diseases' => []
            ],
            'ckd' => [
                'total' => 0,
                'stages' => [
                    'Stage 1' => 0,
                    'Stage 2' => 0,
                    'Stage 3a' => 0,
                    'Stage 3b' => 0,
                    'Stage 4' => 0,
                    'Stage 5' => 0,
                ],
                'contraindicated_alerts' => 0,
                'patients' => []
            ],
            'vhv' => [
                'total_vhv' => 0,
                'total_houses' => 0,
                'villages' => [],
                'list' => []
            ]
        ];

        // 1. ALLERGIES FROM JHCIS
        if ($jhcis) {
            try {
                // Total allergies
                $qTotal = $jhcis->query("SELECT COUNT(*) FROM personalergic");
                $data['allergy']['total'] = (int)$qTotal->fetchColumn();

                // Allergy Groups
                $stmtGroups = $jhcis->query("
                    SELECT 
                        CASE 
                            WHEN pa.drugcode LIKE '%IBU%' OR pa.drugcode LIKE '%DCF%' OR cd.drugname LIKE '%ibuprofen%' OR cd.drugname LIKE '%diclofenac%' THEN 'กลุ่มยาต้านการอักเสบ (NSAIDs)'
                            WHEN pa.drugcode LIKE '%AMX%' OR pa.drugcode LIKE '%AMO%' OR pa.drugcode LIKE '%PEN%' OR pa.drugcode LIKE '%DIC%' OR cd.drugname LIKE '%amox%' OR cd.drugname LIKE '%penicillin%' OR cd.drugname LIKE '%cloxa%' THEN 'กลุ่มเพนิซิลลิน/เบต้าแลคแตม (Penicillins)'
                            WHEN pa.drugcode LIKE '%1073%' OR cd.drugname LIKE '%cotrim%' OR cd.drugname LIKE '%sulfa%' THEN 'กลุ่มซัลฟา (Sulfonamides)'
                            WHEN cd.drugname LIKE '%clotrimazole%' THEN 'กลุ่มยาฆ่าเชื้อรา (Antifungals)'
                            ELSE 'ยากลุ่มอื่นๆ (Others)'
                        END AS drug_group,
                        COUNT(DISTINCT pa.pid) as patient_count
                    FROM personalergic pa
                    LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                    GROUP BY drug_group
                    ORDER BY patient_count DESC
                ");
                $data['allergy']['groups'] = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

                // Recent Allergies with Repeat Radar
                $stmtRecent = $jhcis->query("
                    SELECT pa.pid, pa.drugcode, pa.daterecord, pa.typedx, pa.levelalergic, pa.allergicsymtomps,
                           p.fname, p.lname, p.idcard, cd.drugname,
                           (SELECT COUNT(*) FROM visitdrug vd WHERE vd.drugcode = pa.drugcode AND vd.visitno IN (SELECT visitno FROM visit v WHERE v.pid = pa.pid AND v.visitdate >= pa.daterecord)) as repeat_prescribed
                    FROM personalergic pa
                    JOIN person p ON pa.pid = p.pid
                    LEFT JOIN cdrug cd ON pa.drugcode = cd.drugcode
                    ORDER BY pa.daterecord DESC
                    LIMIT 8
                ");
                $recentAllergies = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
                $data['allergy']['recent'] = $recentAllergies;

                // Count repeats
                $repeatCount = 0;
                foreach ($recentAllergies as $ra) {
                    if ((int)$ra['repeat_prescribed'] > 0) $repeatCount++;
                }
                $data['allergy']['repeat_prescribed'] = $repeatCount;

            } catch (Exception $e) {
                error_log("Dashboard allergy error: " . $e->getMessage());
            }
        }

        // 2. G6PD FROM APP DB & JHCIS
        try {
            $gRows = $appDb->query("
                SELECT id, pid, screening_date, enzyme_activity, who_class, hemolysis_history, high_risk_drugs, g6pd_card_status, g6pd_card_no, card_issue_date, assessing_pharmacist, notes
                FROM pcu_g6pd_registry
                ORDER BY screening_date DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $data['g6pd']['total'] = count($gRows);
            $issued = 0;
            $pending = 0;

            foreach ($gRows as &$g) {
                if ($g['g6pd_card_status'] === 'issued') $issued++;
                else $pending++;

                // Join person from JHCIS
                if ($jhcis) {
                    $pStmt = $jhcis->prepare("SELECT fname, lname, idcard, birth, sex, telephoneperson FROM person WHERE pid = ?");
                    $pStmt->execute([$g['pid']]);
                    $p = $pStmt->fetch(PDO::FETCH_ASSOC);
                    if ($p) {
                        $g['patient_name'] = $p['fname'] . ' ' . $p['lname'];
                        $g['idcard'] = $p['idcard'];
                        $birth = new \DateTime($p['birth'] ?? '1990-01-01');
                        $g['age'] = $birth->diff(new \DateTime())->y;
                        $g['gender'] = ($p['sex'] == '1') ? 'ชาย' : 'หญิง';
                        $g['phone'] = $p['telephoneperson'] ?: '-';
                    }
                }
                if (!isset($g['patient_name'])) {
                    $g['patient_name'] = "ผู้ป่วย PID " . $g['pid'];
                    $g['age'] = '-';
                    $g['gender'] = '-';
                    $g['phone'] = '-';
                }
            }
            $data['g6pd']['card_issued'] = $issued;
            $data['g6pd']['card_pending'] = $pending;
            $data['g6pd']['patients'] = $gRows;
        } catch (Exception $e) {
            error_log("Dashboard G6PD error: " . $e->getMessage());
        }

        // 3. NCD FROM JHCIS
        if ($jhcis) {
            try {
                // Group by chronic category
                $stmtNcd = $jhcis->query("
                    SELECT 
                        CASE 
                            WHEN pc.chroniccode LIKE 'I10%' OR pc.chroniccode LIKE 'I11%' OR pc.chroniccode LIKE 'I15%' THEN 'ความดันโลหิตสูง (HT)'
                            WHEN pc.chroniccode LIKE 'E10%' OR pc.chroniccode LIKE 'E11%' OR pc.chroniccode LIKE 'E12%' OR pc.chroniccode LIKE 'E13%' OR pc.chroniccode LIKE 'E14%' THEN 'เบาหวาน (DM)'
                            WHEN pc.chroniccode LIKE 'I6%' THEN 'หลอดเลือดสมอง (Stroke)'
                            WHEN pc.chroniccode LIKE 'N18%' OR pc.chroniccode LIKE 'I12%' THEN 'โรคไตเรื้อรัง (CKD)'
                            WHEN pc.chroniccode LIKE 'I20%' OR pc.chroniccode LIKE 'I21%' OR pc.chroniccode LIKE 'I25%' THEN 'หลอดเลือดหัวใจ (CAD/IHD)'
                            WHEN pc.chroniccode LIKE 'J44%' OR pc.chroniccode LIKE 'J45%' OR pc.chroniccode LIKE 'J46%' THEN 'หอบหืด/ปอดอุดกั้น (Asthma/COPD)'
                            ELSE 'โรคเรื้อรังอื่นๆ (Other NCD)'
                        END AS ncd_category,
                        COUNT(DISTINCT pc.pid) as patient_count
                    FROM personchronic pc
                    GROUP BY ncd_category
                    ORDER BY patient_count DESC
                ");
                $ncdCategories = $stmtNcd->fetchAll(PDO::FETCH_ASSOC);
                $data['ncd']['categories'] = $ncdCategories;

                // Total unique NCD patients
                $qNcdTotal = $jhcis->query("SELECT COUNT(DISTINCT pid) FROM personchronic");
                $data['ncd']['total'] = (int)$qNcdTotal->fetchColumn();

                // Top ICD-10 codes
                $stmtTop = $jhcis->query("
                    SELECT pc.chroniccode, cd.diseasenamethai, COUNT(DISTINCT pc.pid) as cnt
                    FROM personchronic pc
                    LEFT JOIN cdisease cd ON pc.chroniccode = cd.diseasecode
                    GROUP BY pc.chroniccode, cd.diseasenamethai
                    ORDER BY cnt DESC
                    LIMIT 8
                ");
                $data['ncd']['top_diseases'] = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                error_log("Dashboard NCD error: " . $e->getMessage());
            }
        }

        // 4. CKD FROM APP DB & JHCIS
        try {
            $ckdList = self::getCkdRegistryList();
            $data['ckd']['total'] = count($ckdList);
            $data['ckd']['patients'] = array_slice($ckdList, 0, 8);

            foreach ($ckdList as $ckd) {
                $st = $ckd['ckd_stage'] ?? 'Stage 1';
                if (isset($data['ckd']['stages'][$st])) {
                    $data['ckd']['stages'][$st]++;
                } else {
                    $data['ckd']['stages']['Stage 3a']++;
                }
                if (!empty($ckd['contraindications'])) {
                    $data['ckd']['contraindicated_alerts']++;
                }
            }
        } catch (Exception $e) {
            error_log("Dashboard CKD error: " . $e->getMessage());
        }

        // 5. VHV (อสม.) FROM JHCIS
        if ($jhcis) {
            try {
                $stmtVhv = $jhcis->query("
                    SELECT 
                        h.pidvola,
                        p.fname,
                        p.lname,
                        p.birth,
                        p.idcard,
                        p.telephoneperson,
                        v.villno,
                        v.villname,
                        COUNT(h.hcode) as house_count
                    FROM house h
                    JOIN person p ON h.pidvola = p.pid
                    LEFT JOIN village v ON h.villcode = v.villcode
                    WHERE h.pidvola IS NOT NULL AND h.pidvola > 0
                    GROUP BY h.pidvola, p.fname, p.lname, p.birth, p.idcard, p.telephoneperson, v.villno, v.villname
                    ORDER BY house_count DESC
                ");
                $vhvs = $stmtVhv->fetchAll(PDO::FETCH_ASSOC);

                $data['vhv']['total_vhv'] = count($vhvs);
                $totalH = 0;
                $villageMap = [];

                foreach ($vhvs as &$v) {
                    $hCnt = (int)$v['house_count'];
                    $totalH += $hCnt;
                    $vName = $v['villname'] ?: 'หมู่ที่ ' . ($v['villno'] ?: '1');
                    if (!isset($villageMap[$vName])) {
                        $villageMap[$vName] = ['vhv_count' => 0, 'houses' => 0];
                    }
                    $villageMap[$vName]['vhv_count']++;
                    $villageMap[$vName]['houses'] += $hCnt;
                }

                $data['vhv']['total_houses'] = $totalH;
                $data['vhv']['villages'] = $villageMap;
                $data['vhv']['list'] = $vhvs;
            } catch (Exception $e) {
                error_log("Dashboard VHV error: " . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * Get G6PD Registry List with Search Filter
     */
    public static function getG6pdRegistryList(?string $q = null): array
    {
        $appDb = Database::getAppDb();
        $jhcis = Database::getJhcisDb();

        try {
            $sql = "SELECT * FROM pcu_g6pd_registry ORDER BY screening_date DESC";
            $stmt = $appDb->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $r) {
                $pid = (int)$r['pid'];
                $patient = null;
                if ($jhcis) {
                    $pStmt = $jhcis->prepare("
                        SELECT p.pid, p.idcard, p.fname, p.lname, p.birth, p.sex, p.telephoneperson,
                               h.hno, v.villno, v.villname
                        FROM person p
                        LEFT JOIN house h ON p.hcode = h.hcode
                        LEFT JOIN village v ON h.villcode = v.villcode
                        WHERE p.pid = ?
                    ");
                    $pStmt->execute([$pid]);
                    $patient = $pStmt->fetch(PDO::FETCH_ASSOC);
                }

                $fullName = $patient ? ($patient['fname'] . ' ' . $patient['lname']) : "ผู้ป่วย PID $pid";
                $idcard = $patient['idcard'] ?? '-';
                $age = '-';
                if (!empty($patient['birth'])) {
                    $b = new \DateTime($patient['birth']);
                    $age = $b->diff(new \DateTime())->y;
                }

                $item = array_merge($r, [
                    'patient_name' => $fullName,
                    'idcard' => $idcard,
                    'age' => $age,
                    'gender' => ($patient['sex'] ?? '1') == '1' ? 'ชาย' : 'หญิง',
                    'phone' => $patient['telephoneperson'] ?? '-',
                    'address' => ($patient && !empty($patient['hno'])) ? "บ้านเลขที่ {$patient['hno']} ม.{$patient['villno']} {$patient['villname']}" : 'ในเขต รพ.สต.'
                ]);

                // Filter
                if ($q) {
                    $needle = mb_strtolower(trim($q), 'UTF-8');
                    $haystack = mb_strtolower($fullName . ' ' . $idcard . ' ' . $pid . ' ' . ($item['notes'] ?? ''), 'UTF-8');
                    if (!str_contains($haystack, $needle)) {
                        continue;
                    }
                }

                $result[] = $item;
            }

            return $result;
        } catch (Exception $e) {
            error_log("getG6pdRegistryList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get VHV (อสม.) List with Search Filter
     */
    public static function getVhvList(?string $q = null): array
    {
        $jhcis = Database::getJhcisDb();
        if (!$jhcis) return [];

        try {
            $sql = "
                SELECT 
                    h.pidvola,
                    p.fname,
                    p.lname,
                    p.idcard,
                    p.birth,
                    p.sex,
                    p.telephoneperson,
                    v.villno,
                    v.villname,
                    COUNT(h.hcode) as house_count,
                    GROUP_CONCAT(DISTINCT h.hno ORDER BY h.hno ASC SEPARATOR ', ') as sample_houses
                FROM house h
                JOIN person p ON h.pidvola = p.pid
                LEFT JOIN village v ON h.villcode = v.villcode
                WHERE h.pidvola IS NOT NULL AND h.pidvola > 0
                GROUP BY h.pidvola, p.fname, p.lname, p.idcard, p.birth, p.sex, p.telephoneperson, v.villno, v.villname
                ORDER BY house_count DESC
            ";
            $stmt = $jhcis->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $r) {
                $fullName = $r['fname'] . ' ' . $r['lname'];
                $pid = (int)$r['pidvola'];
                $idcard = $r['idcard'] ?? '-';
                $age = '-';
                if (!empty($r['birth'])) {
                    $b = new \DateTime($r['birth']);
                    $age = $b->diff(new \DateTime())->y;
                }

                $item = array_merge($r, [
                    'full_name' => $fullName,
                    'age' => $age,
                    'gender' => ($r['sex'] == '1') ? 'ชาย' : 'หญิง'
                ]);

                if ($q) {
                    $needle = mb_strtolower(trim($q), 'UTF-8');
                    $haystack = mb_strtolower($fullName . ' ' . $idcard . ' ' . $pid . ' ' . $r['villname'], 'UTF-8');
                    if (!str_contains($haystack, $needle)) {
                        continue;
                    }
                }

                $result[] = $item;
            }

            return $result;
        } catch (Exception $e) {
            error_log("getVhvList error: " . $e->getMessage());
            return [];
        }
    }
}



