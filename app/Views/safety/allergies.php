<div class="safety-allergies-container">
    <!-- Top Stats -->
    <div class="grid-cols-4" style="margin-bottom: 20px;">
        <div class="stat-card" style="border-top: 4px solid #ef4444;">
            <div class="stat-label">ผู้ป่วยแพ้ยาทั้งหมดในพื้นที่</div>
            <div class="stat-value" style="color: #ef4444;"><?= (int)$totalCount ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">จากฐาน JHCIS `personalergic`</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #10b981;">
            <div class="stat-label">ป้องกันการสั่งจ่ายซ้ำ (Repeat Radar)</div>
            <div class="stat-value" style="color: #10b981;">
                <?= (int)$repeatCount === 0 ? '100%' : (int)$repeatCount . ' เคส' ?>
            </div>
            <div class="stat-subtext">
                <?= (int)$repeatCount === 0 ? 'Zero Repeat Allergy (ปลอดภัย)' : 'ตรวจพบการสั่งจ่ายยาซ้ำ' ?>
            </div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #3b82f6;">
            <div class="stat-label">มาตรฐานบัตรแพ้ยา สธ.</div>
            <div class="stat-value" style="color: #3b82f6;">พร้อมออกบัตร</div>
            <div class="stat-subtext">พิมพ์บัตรกระเป๋าสตางค์ 2 ด้าน</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #8b5cf6;">
            <div class="stat-label">เชื่อมโยง CUP รพ.ปลวกแดง</div>
            <div class="stat-value" style="color: #8b5cf6;">Port 3333</div>
            <div class="stat-subtext">แลกเปลี่ยนแฟ้ม DRUG_ALLERGY</div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form action="/pcc/safety/allergies" method="GET" style="display: flex; gap: 12px; align-items: center;">
                <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาด้วยชื่อผู้ป่วย, PID, ชื่อยาที่แพ้ (เช่น Penicillin, Amoxicillin, Ibuprofen)..." class="form-control" style="flex: 1; padding: 10px 14px; font-size: 14px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 14px;">
                    🔍 ค้นหาทะเบียน
                </button>
                <?php if (!empty($query)): ?>
                    <a href="/pcc/safety/allergies" class="btn btn-secondary">ล้างการค้นหา</a>
                <?php endif; ?>
                <a href="/pcc/exchange/file/DRUG_ALLERGY" class="btn btn-secondary" style="margin-left: auto;">
                    📥 ดาวน์โหลดแฟ้ม DRUG_ALLERGY.txt
                </a>
            </form>
        </div>
    </div>

    <!-- Allergy Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🚫 ทะเบียนประวัติการแพ้ยาและเฝ้าระวังแพ้ยาซ้ำ (Drug Allergy Registry & Repeat Radar)</span>
            </div>
            <span class="badge badge-danger">
                <?= count($allergies) ?> รายการ
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th>PID</th>
                            <th>ชื่อ-นามสกุล ผู้ป่วย</th>
                            <th>ชื่อยาที่แพ้ (Allergic Drug)</th>
                            <th>อาการแพ้ที่แสดงออก</th>
                            <th>ระดับความรุนแรง</th>
                            <th>วันที่บันทึก</th>
                            <th>หน่วยงานที่รายงาน</th>
                            <th style="text-align: center;">ตรวจจับแพ้ยาซ้ำ (Radar)</th>
                            <th style="text-align: center; width: 170px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allergies)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    ไม่พบข้อมูลประวัติแพ้ยาในเงื่อนไขการค้นหา
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allergies as $idx => $a): ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                    <td><span class="badge badge-secondary">PID <?= (int)$a['pid'] ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($a['full_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= htmlspecialchars($a['gender']) ?> / อายุ <?= htmlspecialchars($a['age']) ?> ปี • <?= htmlspecialchars($a['address']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: #dc2626; font-size: 14px;">
                                            💊 <?= htmlspecialchars($a['drug_name']) ?>
                                        </strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            รหัส: <?= htmlspecialchars($a['drug_code']) ?>
                                        </div>
                                    </td>
                                    <td style="color: #991b1b; font-weight: 500;">
                                        <?= htmlspecialchars($a['reaction']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger" style="font-size: 11px;">
                                            <?= htmlspecialchars($a['severity']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary); white-space: nowrap;">
                                        <?= htmlspecialchars($a['date_recorded']) ?>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary);">
                                        <?= htmlspecialchars($a['informant_hosp']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($a['is_repeat_prescribed']): ?>
                                            <span class="badge badge-danger" style="animation: pulseAlert 1.5s infinite;">
                                                ⚠️ พบจ่ายซ้ำ <?= (int)$a['repeat_count'] ?> ครั้ง!
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success">
                                                ✓ ไม่พบจ่ายซ้ำ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    onclick='openEditAllergyModal(<?= json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                    style="padding: 3px 8px; font-size: 11.5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;" title="บันทึกเฝ้าระวัง/แก้ไขข้อมูลทางคลินิก">
                                                ✏️ เฝ้าระวัง
                                            </button>
                                            <a href="/pcc/safety/allergies/card/<?= (int)$a['pid'] ?>" target="_blank" class="btn btn-sm btn-primary" style="background: #dc2626; border-color: #dc2626; font-size: 11.5px; padding: 3px 8px;" title="พิมพ์บัตรแพ้ยามาตรฐาน สธ.">
                                                🖨️ บัตรแพ้ยา
                                            </a>
                                            <a href="/pcc/patients/<?= (int)$a['pid'] ?>" class="btn btn-sm btn-secondary" style="font-size: 11.5px; padding: 3px 8px;" title="ดูแฟ้มผู้ป่วย">
                                                แฟ้ม ➔
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Allergy Safety Note -->
<div id="editAllergyModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/safety/allergies/update-note">
            <?= \App\Core\CSRF::field() ?>
            <input type="hidden" name="pid" id="editAllergyPid">
            <input type="hidden" name="drugcode" id="editAllergyDrugCode">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ บันทึกการเฝ้าระวังและข้อมูลทางคลินิก (Allergy Safety Note)
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditAllergyModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 14px; font-weight: 700; color: #991b1b;" id="editAllergyPatientName"></div>
                    <div style="font-size: 12px; color: #b91c1c; margin-top: 2px;" id="editAllergyDrugName"></div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">ระดับความรุนแรงของการแพ้ยา (Severity Level)</label>
                    <select name="levelalergic" id="editAllergyLevel" class="form-control">
                        <option value="1">ระดับ 1: ไม่ร้ายแรง (Non-serious) เช่น ผื่นคัน ลมพิษ</option>
                        <option value="2">ระดับ 2: ร้ายแรงปานกลาง (Moderate)</option>
                        <option value="3">ระดับ 3: ร้ายแรงมาก (Severe) เช่น Angioedema, หายใจไม่ออก</option>
                        <option value="4">ระดับ 4: อันตรายถึงชีวิต (Life-threatening: Anaphylaxis / SJS / TEN)</option>
                        <option value="5">ระดับ 5: เสียชีวิต (Fatal)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">ลักษณะอาการที่เกิดขึ้น (Allergic Symptoms)</label>
                    <input type="text" name="symptom" id="editAllergySymptom" class="form-control" placeholder="เช่น Maculopapular rash, Urticaria, Facial edema">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">บันทึกการเฝ้าระวังของเภสัชกร / คำเตือนความปลอดภัย (Pharmacist Safety Radar Note)</label>
                    <textarea name="clinical_note" id="editAllergyNote" class="form-control" rows="3" placeholder="ระบุการให้คำแนะนำผู้ป่วย การประสานงานแพทย์ หรือข้อควรระวังในการจ่ายยาข้ามกลุ่ม..."></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditAllergyModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกข้อมูล & Audit Log</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAllergyModal(a) {
    document.getElementById('editAllergyPid').value = a.pid || '';
    document.getElementById('editAllergyDrugCode').value = a.drugcode || '';
    document.getElementById('editAllergyPatientName').textContent = (a.patient ? a.patient.full_name : ('PID: ' + a.pid));
    document.getElementById('editAllergyDrugName').textContent = `ยาที่แพ้: ${a.drugname || a.drugcode} (รหัส: ${a.drugcode})`;
    
    document.getElementById('editAllergyLevel').value = a.levelalergic || '1';
    document.getElementById('editAllergySymptom').value = a.symptom || a.allergicsymtomps || '';
    document.getElementById('editAllergyNote').value = a.remark || '';

    document.getElementById('editAllergyModal').style.display = 'flex';
}

function closeEditAllergyModal() {
    document.getElementById('editAllergyModal').style.display = 'none';
}
</script>

