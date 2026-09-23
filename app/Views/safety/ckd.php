<div class="safety-ckd-container">
    <!-- Top Stats -->
    <div class="grid-cols-4" style="margin-bottom: 20px;">
        <div class="stat-card" style="border-top: 4px solid #0d9488;">
            <div class="stat-label">ผู้ป่วยโรคไตเรื้อรัง (CKD) ทั้งหมด</div>
            <div class="stat-value" style="color: #0d9488;"><?= (int)($stats['total'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">รหัส ICD-10: N18 และ E11.2</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #10b981;">
            <div class="stat-label">ระยะเริ่มต้น (Stage 1 – 2)</div>
            <div class="stat-value" style="color: #10b981;">
                <?= (int)(($stats['stage_counts']['Stage 1'] ?? 0) + ($stats['stage_counts']['Stage 2'] ?? 0)) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span>
            </div>
            <div class="stat-subtext">eGFR &ge; 60 mL/min (ชะลอไตเสื่อม)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #f59e0b;">
            <div class="stat-label">ระยะปานกลาง (Stage 3a – 3b)</div>
            <div class="stat-value" style="color: #f59e0b;">
                <?= (int)(($stats['stage_counts']['Stage 3a'] ?? 0) + ($stats['stage_counts']['Stage 3b'] ?? 0)) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span>
            </div>
            <div class="stat-subtext">eGFR 30–59 (ปรับขนาดยา)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #ef4444;">
            <div class="stat-label">ระยะรุนแรง / ล้างไต (Stage 4 – 5)</div>
            <div class="stat-value" style="color: #ef4444;">
                <?= (int)(($stats['stage_counts']['Stage 4'] ?? 0) + ($stats['stage_counts']['Stage 5'] ?? 0)) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span>
            </div>
            <div class="stat-subtext">eGFR &lt; 30 (ห้าม Metformin / NSAIDs)</div>
        </div>
    </div>

    <!-- Search & Guidance Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <form action="/pcc/safety/ckd" method="GET" style="display: flex; gap: 12px; align-items: center; flex: 1; max-width: 600px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาด้วยชื่อผู้ป่วย, PID, ระยะไต (Stage 3, 4, 5)..." class="form-control" style="flex: 1; padding: 10px 14px; font-size: 14px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        🔍 ค้นหา
                    </button>
                    <?php if (!empty($query)): ?>
                        <a href="/pcc/safety/ckd" class="btn btn-secondary">ล้างค้นหา</a>
                    <?php endif; ?>
                </form>
                <div style="display: flex; gap: 8px;">
                    <a href="/pcc/exchange/file/CHRONIC" class="btn btn-secondary">
                        📥 แฟ้ม CHRONIC.txt
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="openModal('ckdGuideModal')">
                        ℹ️ เกณฑ์การปรับขนาดยาในผู้ป่วยไต
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CKD Patients Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🫘 คลินิกผู้ป่วยโรคไตเรื้อรังและความปลอดภัยในการใช้ยา (CKD & Renal Medication Safety)</span>
            </div>
            <span class="badge badge-success">คลินิกชะลอไตเสื่อม รพ.สต.</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th>PID</th>
                            <th>ชื่อ-นามสกุล ผู้ป่วย</th>
                            <th style="text-align: center;">ระยะไต (CKD Stage)</th>
                            <th style="text-align: center;">eGFR (mL/min)</th>
                            <th style="text-align: center;">Serum Cr (mg/dL)</th>
                            <th>วันที่ตรวจแล็บ</th>
                            <th style="text-align: center;">สถานะการบำบัดทดแทนไต</th>
                            <th>ข้อเตือนความปลอดภัยทางยา (Nephrotoxic Alert)</th>
                            <th style="text-align: center;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    ไม่พบข้อมูลผู้ป่วยโรคไตเรื้อรัง
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $idx => $p): ?>
                                <?php
                                    $st = $p['stage'];
                                    $stClass = 'badge-info';
                                    if ($st === 'Stage 1' || $st === 'Stage 2') $stClass = 'badge-success';
                                    elseif ($st === 'Stage 3a' || $st === 'Stage 3b') $stClass = 'badge-warning';
                                    elseif ($st === 'Stage 4' || $st === 'Stage 5') $stClass = 'badge-danger';
                                ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                    <td><span class="badge badge-secondary">PID <?= (int)$p['pid'] ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['patient']['full_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= htmlspecialchars($p['patient']['gender']) ?> / อายุ <?= htmlspecialchars($p['patient']['age']) ?> ปี • สิทธิ <?= htmlspecialchars($p['patient']['right_name'] ?? 'บัตรทอง') ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $stClass ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($p['stage']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong style="font-family: 'Outfit', sans-serif; font-size: 15px; color: <?= ($p['egfr'] < 30) ? '#dc2626' : (($p['egfr'] < 60) ? '#d97706' : '#059669') ?>;">
                                            <?= (float)$p['egfr'] ?>
                                        </strong>
                                    </td>
                                    <td style="text-align: center; font-family: 'Outfit', sans-serif;">
                                        <?= (float)$p['cr'] ?>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary); white-space: nowrap;">
                                        <?= htmlspecialchars($p['lab_date'] ?: '-') ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($p['dialysis'] === 'hemodialysis'): ?>
                                            <span class="badge badge-danger">ฟอกเลือด (HD)</span>
                                        <?php elseif ($p['dialysis'] === 'peritoneal'): ?>
                                            <span class="badge badge-warning">ล้างไตทางช่องท้อง (CAPD)</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">รักษาประคับประคอง</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 12.5px; color: #b91c1c; font-weight: 600;">
                                            ⚠️ <?= htmlspecialchars($p['alerts'] ?: 'ระมัดระวังการใช้ยาที่มีพิษต่อไต') ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-secondary btn-sm" 
                                                    onclick='openEditCkdModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                    style="padding: 3px 8px; font-size: 11.5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;">
                                                ✏️ แก้ไข
                                            </button>
                                            <a href="/pcc/patients/<?= (int)$p['pid'] ?>" class="btn btn-sm btn-primary" style="font-size: 11.5px; padding: 3px 8px;">
                                                เปิดแฟ้ม ➔
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

<!-- CKD Drug Safety Modal -->
<div id="ckdGuideModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 12px; width: 650px; max-width: 90%; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 17px; color: #0f766e;">🫘 แนวทางความปลอดภัยด้านยาในผู้ป่วยโรคไตเรื้อรัง (Renal Safety)</h3>
            <button type="button" onclick="closeModal('ckdGuideModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div style="font-size: 13.5px; line-height: 1.5; color: #334155;">
            <h4 style="color: #dc2626; margin-bottom: 6px;">1. ยาที่ห้ามใช้เด็ดขาด (Contraindicated):</h4>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li><strong>NSAIDs (Ibuprofen, Diclofenac, Naproxen):</strong> ห้ามใช้ใน CKD Stage 3–5 เนื่องจากลดเลือดไปเลี้ยงไต ทำให้ไตวายเฉียบพลัน</li>
                <li><strong>Metformin:</strong> ห้ามใช้เมื่อ eGFR &lt; 30 mL/min เสี่ยงต่อภาวะ Lactic Acidosis รุนแรงถึงแก่ชีวิต</li>
            </ul>

            <h4 style="color: #0f172a; margin-bottom: 6px;">2. ยาที่ต้องปรับลดขนาดตามค่า eGFR (Dose Adjustment):</h4>
            <ul style="padding-left: 20px; margin-bottom: 12px;">
                <li><strong>Metformin:</strong> หาก eGFR 30–44 ปรับขนาดไม่เกิน 1,000 mg/วัน</li>
                <li><strong>Atenolol:</strong> ปรับลดขนาดยา 50% เมื่อ eGFR &lt; 35</li>
                <li><strong>Allopurinol:</strong> เริ่มต้นขนาดต่ำ 50–100 mg/วัน และปรับตาม eGFR ป้องกัน SJS</li>
                <li><strong>ACEI / ARB (Enalapril, Losartan):</strong> ตรวจติดตามค่า Serum K+ และ Creatinine ภายใน 2 สัปดาห์หลังเริ่มยา</li>
            </ul>
        </div>
        <div style="text-align: right; margin-top: 18px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('ckdGuideModal')">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- Modal Edit CKD Patient Clinical Data -->
<div id="editCkdModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/safety/ckd/update">
            <?= \App\Core\CSRF::field() ?>
            <input type="hidden" name="id" id="editCkdId">
            <input type="hidden" name="pid" id="editCkdPid">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ ปรับปรุงข้อมูลคลินิกโรคไตเรื้อรัง (CKD Registry Edit)
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditCkdModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;" id="editCkdPatientName"></div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;" id="editCkdPatientMeta"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">CKD Stage *</label>
                        <select name="ckd_stage" id="editCkdStage" class="form-control">
                            <option value="Stage 1">Stage 1 (eGFR ≥ 90)</option>
                            <option value="Stage 2">Stage 2 (eGFR 60–89)</option>
                            <option value="Stage 3a">Stage 3a (eGFR 45–59)</option>
                            <option value="Stage 3b">Stage 3b (eGFR 30–44)</option>
                            <option value="Stage 4">Stage 4 (eGFR 15–29)</option>
                            <option value="Stage 5">Stage 5 (eGFR &lt; 15 ไตวายระยะสุดท้าย)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">สถานะการบำบัดทดแทนไต (RRT)</label>
                        <select name="dialysis_status" id="editCkdDialysis" class="form-control">
                            <option value="none">ยังไม่ฟอกไต (Conservative Care)</option>
                            <option value="hemodialysis">ฟอกเลือดด้วยเครื่องไตเทียม (HD)</option>
                            <option value="peritoneal">ล้างไตทางช่องท้อง (CAPD)</option>
                            <option value="transplant">ปลูกถ่ายไต (Kidney Transplant)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ค่า eGFR ล่าสุด (mL/min/1.73m²)</label>
                        <input type="number" step="0.01" name="latest_egfr" id="editCkdEgfr" class="form-control" placeholder="เช่น 42.50">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ค่า Serum Creatinine ล่าสุด (mg/dL)</label>
                        <input type="number" step="0.01" name="latest_cr" id="editCkdCr" class="form-control" placeholder="เช่น 1.45">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">การแจ้งเตือนความเสี่ยงยา (Nephrotoxic Alerts)</label>
                    <input type="text" name="nephrotoxic_alerts" id="editCkdAlerts" class="form-control" placeholder="เช่น ห้ามจ่าย NSAIDs เด็ดขาด, เฝ้าระวัง Metformin">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">บันทึกการบริบาลเภสัชกรรม / หมายเหตุทางคลินิก (Pharmacist Notes)</label>
                    <textarea name="notes" id="editCkdNotes" class="form-control" rows="3" placeholder="ระบุการให้คำแนะนำผู้ป่วย การติดตามผลตรวจแล็บ หรือแผนการปรับขนาดยา..."></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditCkdModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกข้อมูล & Audit Log</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCkdModal(p) {
    document.getElementById('editCkdId').value = p.id || '';
    document.getElementById('editCkdPid').value = p.pid || '';
    document.getElementById('editCkdPatientName').textContent = p.patient ? p.patient.full_name : ('PID: ' + p.pid);
    document.getElementById('editCkdPatientMeta').textContent = `PID: ${p.pid} | อายุ: ${p.patient ? p.patient.age : '-'} ปี | สิทธิ: ${p.patient ? (p.patient.right_name || 'บัตรทอง') : '-'}`;
    
    document.getElementById('editCkdStage').value = p.stage || 'Stage 3a';
    document.getElementById('editCkdDialysis').value = p.dialysis || 'none';
    document.getElementById('editCkdEgfr').value = p.egfr !== undefined ? p.egfr : '';
    document.getElementById('editCkdCr').value = p.cr !== undefined ? p.cr : '';
    document.getElementById('editCkdAlerts').value = p.alerts || '';
    document.getElementById('editCkdNotes').value = p.notes || '';

    document.getElementById('editCkdModal').style.display = 'flex';
}

function closeEditCkdModal() {
    document.getElementById('editCkdModal').style.display = 'none';
}
</script>

