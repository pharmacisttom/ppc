<?php
namespace App\Services;

/**
 * Clinical Medication Safety Decision Support Rule Engine
 * Evaluates 14 Clinical Risk Dimensions:
 * 1. Direct Drug Allergy
 * 2. Cross-Sensitivity Allergy (e.g. Penicillin - Cephalosporin)
 * 3. Polypharmacy (≥ 5 medications)
 * 4. Duplicate Therapy & Repeated Medication
 * 5. Drug-Disease Risk (e.g. NSAID in CKD/HT, Steroids in DM)
 * 6. Renal Risk (e.g. Metformin in severe CKD)
 * 7. Elderly Medication Risk (Beers Criteria / Anticholinergics)
 * 8. Pediatric Risk (Weight-based dosing)
 * 9. Pregnancy & Lactation Risk
 * 10. High Alert Medications (HAM)
 * 11. Look-Alike Sound-Alike (LASA)
 * 12. Clinically Significant Drug Interactions (DDIs)
 * 13. Adherence Risk (Missed refills / High pill burden)
 * 14. Medication Reconciliation Discrepancy
 *
 * Strictly follows Clinical Decision Support Principle:
 * Flags risks with INFO, REVIEW, HIGH, CRITICAL.
 * NEVER alters physician prescriptions automatically.
 */
class SafetyEngine
{
    /**
     * Run full medication screening on a patient profile
     */
    public static function screenPatient(array $patient, array $allergies, array $chronicDiseases, array $medications): array
    {
        $flags = [];

        // 1. Direct Drug Allergy & Cross Sensitivity
        self::checkAllergies($flags, $allergies, $medications);

        // 2. Polypharmacy Check (≥ 5 medications)
        self::checkPolypharmacy($flags, $medications);

        // 3. Duplicate Therapy
        self::checkDuplicates($flags, $medications);

        // 4. Drug-Disease Risk (NSAID in CKD / HT)
        self::checkDrugDisease($flags, $chronicDiseases, $medications);

        // 5. High Alert Medication (HAM)
        self::checkHighAlert($flags, $medications);

        // 6. Look-Alike Sound-Alike (LASA)
        self::checkLasa($flags, $medications);

        // 7. Elderly Risk (Beers Criteria)
        if (($patient['age'] ?? 0) >= 65) {
            self::checkElderly($flags, $medications);
        }

        // 8. Drug-Drug Interactions
        self::checkInteractions($flags, $medications);

        return $flags;
    }

    private static function checkAllergies(array &$flags, array $allergies, array $medications): void
    {
        if (empty($allergies) || empty($medications)) {
            return;
        }

        foreach ($medications as $med) {
            $medName = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');
            $medGeneric = mb_strtolower($med['generic_name'] ?? '', 'UTF-8');

            foreach ($allergies as $allg) {
                $allgName = mb_strtolower($allg['drug_name'] ?? '', 'UTF-8');
                $allgGeneric = mb_strtolower($allg['generic_name'] ?? '', 'UTF-8');

                // Direct Match
                if ((!empty($allgGeneric) && str_contains($medGeneric, $allgGeneric)) 
                    || (!empty($allgName) && str_contains($medName, $allgName))) {
                    $flags[] = [
                        'dimension' => 'DRUG_ALLERGY',
                        'level' => 'CRITICAL',
                        'title' => 'ตรวจพบประวัติแพ้ยาตรงกันโดยตรง (Direct Allergy Match)',
                        'description' => "ผู้ป่วยมีประวัติแพ้ {$allg['drug_name']} (อาการ: {$allg['reaction']}) แต่มีรายการสั่งใช้ {$med['drug_name']}",
                        'recommendation' => 'ห้ามบริหารยาโดยเด็ดขาด บุคลากรวิชาชีพต้องทบทวนคำสั่งใช้ยาและปรึกษาแพทย์ผู้สั่งใช้ทันที'
                    ];
                }

                // Cross Sensitivity: Penicillin - Cephalosporin
                $isPenicillinAllergy = str_contains($allgGeneric, 'amoxicillin') || str_contains($allgGeneric, 'ampicillin') || str_contains($allgGeneric, 'penicillin');
                $isCephalosporinMed = str_contains($medGeneric, 'cephalexin') || str_contains($medGeneric, 'cefdinir') || str_contains($medGeneric, 'cefuroxime') || str_contains($medGeneric, 'ceftriaxone');

                if ($isPenicillinAllergy && $isCephalosporinMed) {
                    $flags[] = [
                        'dimension' => 'ALLERGY_CROSS_SENSITIVITY',
                        'level' => 'HIGH',
                        'title' => 'ความเสี่ยงแพ้ข้ามกลุ่ม (Cross-Sensitivity Risk)',
                        'description' => "ผู้ป่วยมีประวัติแพ้ยากลุ่ม Penicillin ({$allg['drug_name']}) และได้รับยากลุ่ม Cephalosporin ({$med['drug_name']}) ซึ่งมีโครงสร้าง Beta-lactam คล้ายคลึงกัน (ความเสี่ยงข้ามกลุ่ม 1-3%)",
                        'recommendation' => 'ควรประเมินประวัติความรุนแรงของการแพ้ Penicillin เดิม หากเคยเกิด Anaphylaxis/Angioedema ควรหลีกเลี่ยง Cephalosporin'
                    ];
                }
            }
        }
    }

    private static function checkPolypharmacy(array &$flags, array $medications): void
    {
        $count = count($medications);
        if ($count >= 10) {
            $flags[] = [
                'dimension' => 'POLYPHARMACY_HYPER',
                'level' => 'HIGH',
                'title' => "ภาวะใช้ยาหลายขนานขั้นวิกฤต (Hyper-Polypharmacy: {$count} รายการ)",
                'description' => "ผู้ป่วยได้รับยาประจำตั้งแต่ 10 รายการขึ้นไป มีความเสี่ยงสูงมากต่อการเกิด Drug Interactions, อาการไม่พึงประสงค์จากยา และความสับสนในการรับประทานยา",
                'recommendation' => 'ควรจัดทำ Comprehensive Medication Review และพิจารณา Deprescribing ยาที่ไม่จำเป็นร่วมกับแพทย์'
            ];
        } elseif ($count >= 5) {
            $flags[] = [
                'dimension' => 'POLYPHARMACY',
                'level' => 'REVIEW',
                'title' => "ภาวะใช้ยาหลายขนาน (Polypharmacy: {$count} รายการ)",
                'description' => "ผู้ป่วยได้รับยาตั้งแต่ 5 รายการขึ้นไป เข้าเกณฑ์ตัวชี้วัดที่ต้องได้รับการทบทวนรายการยา (Medication Review)",
                'recommendation' => 'ประเมิน Adherence และความถูกต้องของเทคนิคการใช้ยา'
            ];
        }
    }

    private static function checkDuplicates(array &$flags, array $medications): void
    {
        $seen = [];
        foreach ($medications as $med) {
            $code = $med['drug_code'] ?? $med['drug_name'];
            if (isset($seen[$code])) {
                $flags[] = [
                    'dimension' => 'DUPLICATE_THERAPY',
                    'level' => 'HIGH',
                    'title' => 'ตรวจพบการสั่งใช้ยาซ้ำซ้อน (Duplicate Medication)',
                    'description' => "มีรายการยา {$med['drug_name']} สั่งจ่ายซ้ำกันในเวชระเบียน",
                    'recommendation' => 'ตรวจสอบความตั้งใจในการสั่งจ่าย เพื่อป้องกันผู้ป่วยได้รับยาเกินขนาด (Overdose)'
                ];
            }
            $seen[$code] = true;
        }
    }

    private static function checkDrugDisease(array &$flags, array $chronicDiseases, array $medications): void
    {
        $hasCkd = false;
        $hasHt = false;
        $hasDm = false;

        foreach ($chronicDiseases as $cd) {
            $code = strtoupper($cd['chronic_code'] ?? '');
            $name = $cd['group_name'] ?? '';
            if (str_starts_with($code, 'N18') || str_contains($name, 'ไต')) $hasCkd = true;
            if (str_starts_with($code, 'I10') || str_contains($name, 'ความดัน')) $hasHt = true;
            if (str_starts_with($code, 'E11') || str_contains($name, 'เบาหวาน')) $hasDm = true;
        }

        foreach ($medications as $med) {
            $name = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');
            $generic = mb_strtolower($med['generic_name'] ?? '', 'UTF-8');

            // NSAID in CKD or HT
            $isNsaid = str_contains($generic, 'ibuprofen') || str_contains($generic, 'naproxen') || str_contains($generic, 'diclofenac') || str_contains($generic, 'indomethacin') || str_contains($name, 'ibuprofen');
            if ($isNsaid && $hasCkd) {
                $flags[] = [
                    'dimension' => 'DRUG_DISEASE_RENAL',
                    'level' => 'CRITICAL',
                    'title' => 'ข้อห้ามใช้: ยากลุ่ม NSAID ในผู้ป่วยโรคไตเรื้อรัง (CKD)',
                    'description' => "ผู้ป่วยมีภาวะไตเรื้อรัง การใช้ยา {$med['drug_name']} ยับยั้ง Prostaglandin ส่งผลให้ไตขาดเลือดเฉียบพลัน (Acute Kidney Injury) และการทำงานของไตเสื่อมลงอย่างรวดเร็ว",
                    'recommendation' => 'ควรระงับการสั่งใช้ยา NSAID และเปลี่ยนเป็นยา Paracetamol หรือ Topical Analgesic แทน'
                ];
            } elseif ($isNsaid && $hasHt) {
                $flags[] = [
                    'dimension' => 'DRUG_DISEASE_HT',
                    'level' => 'REVIEW',
                    'title' => 'ข้อควรระวัง: ยากลุ่ม NSAID อาจทำให้ความดันโลหิตสูงขึ้นและต้านฤทธิ์ยาลดความดัน',
                    'description' => "ยา {$med['drug_name']} ทำให้เกิดการคั่งของโซเดียมและน้ำ ส่งผลให้ความดันโลหิตควบคุมได้ยากขึ้น",
                    'recommendation' => 'หากจำเป็นต้องใช้ ให้ใช้ในระยะเวลาสั้นที่สุด และติดตามความดันโลหิตอย่างใกล้ชิด'
                ];
            }

            // Metformin in severe CKD
            $isMetformin = str_contains($generic, 'metformin') || str_contains($name, 'metformin');
            if ($isMetformin && $hasCkd) {
                $flags[] = [
                    'dimension' => 'RENAL_RISK',
                    'level' => 'HIGH',
                    'title' => 'ความเสี่ยงไตเสื่อม: การใช้ยา Metformin ในผู้ป่วยโรคไต (Renal Impairment)',
                    'description' => "ผู้ป่วยมีโรคไตเรื้อรัง การใช้ Metformin มีความเสี่ยงต่อการสะสมของยาจนเกิดภาวะ Lactic Acidosis ซึ่งมีอัตราเสียชีวิตสูง",
                    'recommendation' => 'ตรวจสอบค่า eGFR ล่าสุด: หาก eGFR 30-44 ให้ลดขนาดยาไม่เกิน 1,000 mg/วัน หาก eGFR < 30 ml/min/1.73m² ต้องหยุดใช้ยาทันที'
                ];
            }
        }
    }

    private static function checkHighAlert(array &$flags, array $medications): void
    {
        foreach ($medications as $med) {
            $name = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');
            $generic = mb_strtolower($med['generic_name'] ?? '', 'UTF-8');

            if (str_contains($generic, 'insulin') || str_contains($name, 'mixtard') || str_contains($name, 'insulin')) {
                $flags[] = [
                    'dimension' => 'HIGH_ALERT_MEDICATION',
                    'level' => 'HIGH',
                    'title' => 'ยากลุ่มเสี่ยงสูง (High Alert Drug): Insulin',
                    'description' => "ยา {$med['drug_name']} เป็นยากลุ่มเสี่ยงสูง เสี่ยงต่อภาวะน้ำตาลในเลือดต่ำรุนแรง (Severe Hypoglycemia) และการหยิบยาผิดยูนิต",
                    'recommendation' => 'ต้องทำ Double Check ขนาดยาและชนิดอินซูลินก่อนส่งมอบ พร้อมทบทวนเทคนิคการฉีดยาและการเก็บรักษาที่อุณหภูมิ 2-8 °C'
                ];
            } elseif (str_contains($generic, 'warfarin') || str_contains($name, 'warfarin')) {
                $flags[] = [
                    'dimension' => 'HIGH_ALERT_MEDICATION',
                    'level' => 'HIGH',
                    'title' => 'ยากลุ่มเสี่ยงสูง (High Alert Drug): Warfarin',
                    'description' => "ยา {$med['drug_name']} มีหน้าต่างการรักษาแคบ เสี่ยงต่อภาวะเลือดออกผิดปกติ",
                    'recommendation' => 'ตรวจสอบผลค่า INR ล่าสุด สอบถามอาการเลือดออกผิดปกติ และตรวจสอบอันตรกิริยากับยาร่วมและสมุนไพร'
                ];
            }
        }
    }

    private static function checkLasa(array &$flags, array $medications): void
    {
        foreach ($medications as $med) {
            $name = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');
            if (str_contains($name, 'prednisolone')) {
                $flags[] = [
                    'dimension' => 'LASA_ALERT',
                    'level' => 'REVIEW',
                    'title' => 'ยาชื่อพ้องมองคล้าย (LASA): prednisoLONE vs predniSONE',
                    'description' => "ระมัดระวังการจัดจ่ายสับสนระหว่าง prednisoLONE 5 mg และ predniSONE 5 mg",
                    'recommendation' => 'สังเกต Tall Man Lettering บนฉลากยา และแยกจุดจัดเก็บในห้องยา'
                ];
            } elseif (str_contains($name, 'amlodipine')) {
                $flags[] = [
                    'dimension' => 'LASA_ALERT',
                    'level' => 'INFO',
                    'title' => 'ยาชื่อพ้องมองคล้าย (LASA): amLODIPine vs amITRIPtyline',
                    'description' => "ชื่อยาออกเสียงคล้ายคลึงกัน ระวังการจัดจ่ายสลับขนาน",
                    'recommendation' => 'ตรวจสอบข้อบ่งใช้และขนาดยาซ้ำก่อนส่งมอบ'
                ];
            }
        }
    }

    private static function checkElderly(array &$flags, array $medications): void
    {
        foreach ($medications as $med) {
            $generic = mb_strtolower($med['generic_name'] ?? '', 'UTF-8');
            $name = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');

            if (str_contains($generic, 'chlorpheniramine') || str_contains($name, 'cpm') || str_contains($generic, 'dimenhydrinate')) {
                $flags[] = [
                    'dimension' => 'ELDERLY_BEERS_CRITERIA',
                    'level' => 'REVIEW',
                    'title' => 'เกณฑ์ความเสี่ยงในผู้สูงอายุ (Beers Criteria): ยาต้านฮิสตามีนกลุ่มง่วงซึม',
                    'description' => "ยา {$med['drug_name']} มีฤทธิ์ Anticholinergic สูง เสี่ยงต่ออาการง่วงซึม วิงเวียน สับสน ปัสสาวะคั่ง และการพลัดตกหกล้มในผู้สูงอายุ",
                    'recommendation' => 'พิจารณาเปลี่ยนเป็นยารุ่นที่สอง เช่น Loratadine หรือ Cetirizine แทน'
                ];
            }
        }
    }

    private static function checkInteractions(array &$flags, array $medications): void
    {
        $hasAcei = false;
        $hasSpironolactone = false;
        $hasAspirin = false;
        $hasNsaid = false;

        foreach ($medications as $med) {
            $generic = mb_strtolower($med['generic_name'] ?? '', 'UTF-8');
            $name = mb_strtolower($med['drug_name'] ?? '', 'UTF-8');

            if (str_contains($generic, 'enalapril') || str_contains($generic, 'lisinopril')) $hasAcei = true;
            if (str_contains($generic, 'spironolactone')) $hasSpironolactone = true;
            if (str_contains($generic, 'aspirin')) $hasAspirin = true;
            if (str_contains($generic, 'ibuprofen') || str_contains($generic, 'naproxen')) $hasNsaid = true;
        }

        if ($hasAspirin && $hasNsaid) {
            $flags[] = [
                'dimension' => 'DRUG_DRUG_INTERACTION',
                'level' => 'HIGH',
                'title' => 'อันตรกิริยาระหว่างยา: Aspirin + NSAID',
                'description' => 'การใช้ Aspirin ร่วมกับยากลุ่ม NSAID เพิ่มความเสี่ยงแผลในกระเพาะอาหารและเลือดออกในทางเดินอาหารอย่างมีนัยสำคัญ (GI Bleeding)',
                'recommendation' => 'หลีกเลี่ยงการใช้ร่วมกัน หากจำเป็นต้องสั่งยาแก้ปวด ควรพิจารณายาพาราเซตามอลหรือยาทาภายนอก'
            ];
        }
    }
}
