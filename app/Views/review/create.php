<?php
use App\Core\CSRF;
?>
<div class="review-create-container">
    <!-- Patient Context Banner -->
    <div class="card" style="border-left: 4px solid var(--primary); background: #f0fdfa;">
        <div class="card-body" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="font-size: 16px; color: var(--primary-dark);">
                    👤 ผู้รับบริการ: <?= htmlspecialchars($patient['full_name']) ?> (PID: <?= (int)$patient['pid'] ?>)
                </strong>
                <div style="font-size: 13px; color: var(--text-secondary); margin-top: 3px;">
                    อายุ <?= (int)$patient['age'] ?> ปี • เลขบัตร: <?= htmlspecialchars($patient['masked_cid']) ?>
                </div>
            </div>
            <div>
                <a href="/pcc/patients/<?= (int)$patient['pid'] ?>" class="btn btn-secondary btn-sm">
                    เปิดดูแฟ้มยาแบบเต็ม ➔
                </a>
            </div>
        </div>
    </div>

    <form action="/pcc/reviews/store" method="POST">
        <?= CSRF::field() ?>
        <input type="hidden" name="patient_pid" value="<?= (int)$patient['pid'] ?>">

        <!-- Review Metadata -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>📝 ข้อมูลการทบทวนยา (Medication Review Metadata)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="grid-cols-3">
                    <div class="form-group">
                        <label class="form-label">วันที่ทบทวน</label>
                        <input type="date" name="review_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ประเภทการทบทวน</label>
                        <select name="review_type" class="form-control" required>
                            <option value="routine">Routine Review (การทบทวนประจำ)</option>
                            <option value="polypharmacy" selected>Polypharmacy (ผู้ใช้ยา ≥ 5 รายการ)</option>
                            <option value="high_risk">High Risk Patients (ผู้ป่วยความเสี่ยงสูง)</option>
                            <option value="transition">Transition of Care / Reconciliation (ส่งต่อจาก รพ.)</option>
                            <option value="adr_drp">Follow-up ADR / DRP (ติดตามปัญหาเดิม)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">สถานะการทบทวน</label>
                        <select name="status" class="form-control">
                            <option value="completed" selected>Completed (ดำเนินการเรียบร้อย)</option>
                            <option value="in_progress">In Progress (อยู่ระหว่างประสานแพทย์)</option>
                            <option value="followup_required">Follow-up Required (ต้องติดตามต่อ)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">บทสรุปทางคลินิกและแผนการจัดการ (Clinical Summary & Plan)</label>
                    <textarea name="clinical_summary" class="form-control" rows="3" placeholder="ระบุการประเมินภาพรวม การประสานแพทย์ผู้สั่งยา หรือคำแนะนำเพิ่มเติมแก่ผู้ป่วย..."></textarea>
                </div>
            </div>
        </div>

        <!-- Drug-Related Problems (DRP) Builder -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>⚠️ ปัญหาจากการใช้ยาที่ตรวจพบ (Drug-Related Problems - PCNE Classification)</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addProblemRow()">
                    ➕ เพิ่มปัญหา DRP
                </button>
            </div>
            <div class="card-body">
                <div id="problemsContainer" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Pre-seed first row from existing patient meds or blank -->
                    <?php 
                        $firstMed = !empty($medications) ? $medications[0]['drug_name'] : '';
                    ?>
                    <div class="problem-row" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px;">
                        <div class="grid-cols-2">
                            <div class="form-group">
                                <label class="form-label">ชื่อยาที่เกี่ยวข้อง (Drug Name)</label>
                                <input type="text" name="drug_name[]" class="form-control" value="<?= htmlspecialchars($firstMed) ?>" placeholder="เช่น Warfarin 3 mg, Ibuprofen 400 mg" required>
                                <input type="hidden" name="drug_code[]" value="">
                            </div>
                            <div class="form-group">
                                <label class="form-label">หมวดหมู่ปัญหา (DRP Category - PCNE)</label>
                                <select name="drp_category[]" class="form-control" required>
                                    <option value="drug_interaction">C1: อันตรกิริยาระหว่างยา (Drug-Drug Interaction)</option>
                                    <option value="inappropriate_drug">C2: ยาไม่เหมาะสมกับสภาวะโรค/ผู้สูงอายุ (Inappropriate Drug)</option>
                                    <option value="inappropriate_dose">C3: ขนาดยาไม่เหมาะสม (Inappropriate Dosage)</option>
                                    <option value="duplicate_therapy">C4: การใช้ยาซ้ำซ้อน (Duplicate Therapy)</option>
                                    <option value="adverse_drug_event">C5: อาการไม่พึงประสงค์จากการใช้ยา (Adverse Drug Event)</option>
                                    <option value="non_adherence">C6: ปัญหาความร่วมมือในการใช้ยา (Non-Adherence)</option>
                                    <option value="untreated_indication">C7: มีข้อบ่งใช้แต่ไม่ได้รับยา (Untreated Indication)</option>
                                    <option value="other">C8: อื่นๆ (Other Problem)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">รายละเอียดปัญหาที่พบ (Problem Description)</label>
                            <input type="text" name="problem_description[]" class="form-control" placeholder="เช่น พบการใช้ยาแก้ปวด NSAID ร่วมกับ Warfarin เสี่ยงเลือดออกในทางเดินอาหาร" required>
                        </div>

                        <div class="grid-cols-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">ข้อเสนอแนะของเภสัชกร (Pharmacist Recommendation)</label>
                                <input type="text" name="recommendation[]" class="form-control" placeholder="เช่น แนะนำเปลี่ยนยาเป็น Paracetamol หรือปรับแผนการรักษา" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">การตอบรับของแพทย์ผู้สั่งยา (Prescriber Response)</label>
                                <select name="prescriber_response[]" class="form-control">
                                    <option value="accepted_fully" selected>Accepted Fully (ยอมรับและปรับเปลี่ยนตามข้อเสนอแนะ)</option>
                                    <option value="accepted_partially">Accepted Partially (ยอมรับบางส่วน)</option>
                                    <option value="not_accepted">Not Accepted (ไม่ยอมรับด้วยเหตุผลทางคลินิก)</option>
                                    <option value="pending">Pending (รอแพทย์พิจารณา)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
            <a href="/pcc/patients/<?= (int)$patient['pid'] ?>" class="btn btn-secondary">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-size: 15px;">
                💾 บันทึกผลการทบทวนยาและ DRP
            </button>
        </div>
    </form>
</div>

<script>
function addProblemRow() {
    const container = document.getElementById('problemsContainer');
    const row = document.createElement('div');
    row.className = 'problem-row';
    row.style.cssText = 'background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px; margin-top: 10px; position: relative;';
    row.innerHTML = `
        <button type="button" onclick="this.parentElement.remove()" style="position: absolute; right: 12px; top: 12px; border: none; background: transparent; color: #ef4444; font-size: 16px; cursor: pointer;">✕ ลบแถวนี้</button>
        <div class="grid-cols-2">
            <div class="form-group">
                <label class="form-label">ชื่อยาที่เกี่ยวข้อง (Drug Name)</label>
                <input type="text" name="drug_name[]" class="form-control" placeholder="เช่น Metformin, Enalapril" required>
                <input type="hidden" name="drug_code[]" value="">
            </div>
            <div class="form-group">
                <label class="form-label">หมวดหมู่ปัญหา (DRP Category - PCNE)</label>
                <select name="drp_category[]" class="form-control" required>
                    <option value="drug_interaction">C1: อันตรกิริยาระหว่างยา (Drug-Drug Interaction)</option>
                    <option value="inappropriate_drug">C2: ยาไม่เหมาะสมกับสภาวะโรค/ผู้สูงอายุ (Inappropriate Drug)</option>
                    <option value="inappropriate_dose">C3: ขนาดยาไม่เหมาะสม (Inappropriate Dosage)</option>
                    <option value="duplicate_therapy">C4: การใช้ยาซ้ำซ้อน (Duplicate Therapy)</option>
                    <option value="adverse_drug_event">C5: อาการไม่พึงประสงค์จากการใช้ยา (Adverse Drug Event)</option>
                    <option value="non_adherence">C6: ปัญหาความร่วมมือในการใช้ยา (Non-Adherence)</option>
                    <option value="untreated_indication">C7: มีข้อบ่งใช้แต่ไม่ได้รับยา (Untreated Indication)</option>
                    <option value="other">C8: อื่นๆ (Other Problem)</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">รายละเอียดปัญหาที่พบ (Problem Description)</label>
            <input type="text" name="problem_description[]" class="form-control" placeholder="ระบุปัญหาที่ประเมินพบ..." required>
        </div>
        <div class="grid-cols-2">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">ข้อเสนอแนะของเภสัชกร (Pharmacist Recommendation)</label>
                <input type="text" name="recommendation[]" class="form-control" placeholder="ระบุคำแนะนำการแก้ไข..." required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">การตอบรับของแพทย์ผู้สั่งยา (Prescriber Response)</label>
                <select name="prescriber_response[]" class="form-control">
                    <option value="accepted_fully" selected>Accepted Fully (ยอมรับและปรับเปลี่ยน)</option>
                    <option value="accepted_partially">Accepted Partially (ยอมรับบางส่วน)</option>
                    <option value="not_accepted">Not Accepted (ไม่ยอมรับ)</option>
                    <option value="pending">Pending (รอพิจารณา)</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(row);
}
</script>
