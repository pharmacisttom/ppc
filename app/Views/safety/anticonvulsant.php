<div class="safety-anticonvulsant-container">
    <!-- Top Stats -->
    <div class="grid-cols-4" style="margin-bottom: 20px;">
        <div class="stat-card" style="border-top: 4px solid #6366f1;">
            <div class="stat-label">ผู้รับยากันชักทั้งหมดในพื้นที่</div>
            <div class="stat-value" style="color: #6366f1;"><?= (int)($stats['total'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">Phenobarbital, Phenytoin, Valproate</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #10b981;">
            <div class="stat-label">ปลอดอาการชัก &gt; 6 เดือน</div>
            <div class="stat-value" style="color: #10b981;"><?= (int)($stats['free_seizure'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">Seizure Free (ควบคุมอาการได้ดี)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #3b82f6;">
            <div class="stat-label">ตรวจยีนแพ้ยา HLA-B*1502</div>
            <div class="stat-value" style="color: #3b82f6;"><?= (int)($stats['hla_tested'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">ป้องกันผื่นแพ้ยารุนแรง (SJS/TEN)</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #8b5cf6;">
            <div class="stat-label">กินยาสม่ำเสมอ (High Adherence)</div>
            <div class="stat-value" style="color: #8b5cf6;"><?= (int)($stats['high_adherence'] ?? 0) ?> <span style="font-size: 15px; color: var(--text-muted);">ราย</span></div>
            <div class="stat-subtext">ประเมินโดยเภสัชกรปฐมภูมิ</div>
        </div>
    </div>

    <!-- Search & Guidance Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <form action="/pcc/safety/anticonvulsant" method="GET" style="display: flex; gap: 12px; align-items: center; flex: 1; max-width: 600px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาด้วยชื่อผู้ป่วย, PID, ชื่อยากันชัก..." class="form-control" style="flex: 1; padding: 10px 14px; font-size: 14px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        🔍 ค้นหา
                    </button>
                    <?php if (!empty($query)): ?>
                        <a href="/pcc/safety/anticonvulsant" class="btn btn-secondary">ล้างค้นหา</a>
                    <?php endif; ?>
                </form>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="openModal('anticonvGuideModal')">
                        ℹ️ เกณฑ์ระดับยาในเลือด (TDM Target)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Anticonvulsant Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>🧠 ทะเบียนผู้ป่วยรับยากันชักและการตรวจติดตามระดับยา (Anticonvulsant & TDM Surveillance)</span>
            </div>
            <span class="badge badge-primary">รพ.สต.บ้านดอกกราย (01996)</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th>PID</th>
                            <th>ชื่อ-นามสกุล ผู้ป่วย</th>
                            <th>ชื่อยาที่ได้รับ</th>
                            <th>ขนาดและวิธีรับประทาน</th>
                            <th>ข้อบ่งใช้</th>
                            <th style="text-align: center;">การควบคุมอาการชัก</th>
                            <th style="text-align: center;">ยีน HLA-B*1502</th>
                            <th style="text-align: center;">ระดับยาในเลือด (TDM)</th>
                            <th style="text-align: center;">ความร่วมมือในการกินยา</th>
                            <th style="text-align: center;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                    ไม่พบข้อมูลผู้ป่วยในทะเบียนยากันชัก
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $idx => $p): ?>
                                <?php
                                    $sc = $p['seizure_control'];
                                    $scClass = ($sc === 'free_gt6m') ? 'badge-success' : (($sc === 'occasional') ? 'badge-warning' : 'badge-danger');
                                    $scText = ($sc === 'free_gt6m') ? '✓ ปลอดอาการ >6ด.' : (($sc === 'occasional') ? 'ชักเป็นครั้งคราว' : '⚠️ ยังคุมไม่ได้');
                                    
                                    $hla = $p['hla_b1502'];
                                    $hlaClass = ($hla === 'negative') ? 'badge-success' : (($hla === 'positive') ? 'badge-danger' : 'badge-secondary');
                                    $hlaText = ($hla === 'negative') ? 'Negative (ไม่พบยีนแพ้)' : (($hla === 'positive') ? '⚠️ POSITIVE (ห้ามใช้ CBZ)' : 'ยังไม่ตรวจ');
                                ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                    <td><span class="badge badge-secondary">PID <?= (int)$p['pid'] ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['patient']['full_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= htmlspecialchars($p['patient']['gender']) ?> / อายุ <?= htmlspecialchars($p['patient']['age']) ?> ปี
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: #4338ca;">💊 <?= htmlspecialchars($p['drug_name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);">รหัส: <?= htmlspecialchars($p['drug_code']) ?></div>
                                    </td>
                                    <td style="font-size: 12.5px; color: var(--text-secondary);">
                                        <?= htmlspecialchars($p['daily_dose']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['indication']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $scClass ?>">
                                            <?= $scText ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $hlaClass ?>">
                                            <?= $hlaText ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($p['tdm_level']): ?>
                                            <strong style="font-family: 'Outfit', sans-serif; color: #0d9488;">
                                                <?= (float)$p['tdm_level'] ?> mcg/mL
                                            </strong>
                                            <div style="font-size: 10px; color: var(--text-muted);"><?= htmlspecialchars($p['tdm_date'] ?: '') ?></div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= ($p['adherence'] === 'high') ? 'badge-success' : 'badge-warning' ?>">
                                            <?= ($p['adherence'] === 'high') ? 'กินยาสม่ำเสมอ' : 'มีลืมกินยา' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-secondary btn-sm" 
                                                    onclick='openEditAnticonvModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
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

<!-- Anticonvulsant Guidance Modal -->
<div id="anticonvGuideModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 12px; width: 600px; max-width: 90%; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 17px; color: #4338ca;">🧠 ค่าเป้าหมายระดับยากันชักในเลือด (Therapeutic Range)</h3>
            <button type="button" onclick="closeModal('anticonvGuideModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div style="font-size: 13.5px; line-height: 1.5; color: #334155;">
            <table class="table" style="margin-bottom: 14px;">
                <thead>
                    <tr><th>ชื่อยา</th><th>ช่วงเป้าหมายการรักษา</th><th>อาการเป็นพิษจากยา (Toxicity)</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Phenobarbital</strong></td><td>15 – 40 mcg/mL</td><td>ง่วงซึมมาก, สับสน, เดินเซ (Ataxia)</td></tr>
                    <tr><td><strong>Phenytoin</strong></td><td>10 – 20 mcg/mL</td><td>ตากระตุก (Nystagmus), เหงือกบวมโต, เดินเซ</td></tr>
                    <tr><td><strong>Sodium Valproate</strong></td><td>50 – 100 mcg/mL</td><td>ตับอักเสบ, มือสั่น (Tremor), ผมร่วง</td></tr>
                    <tr><td><strong>Carbamazepine</strong></td><td>4 – 12 mcg/mL</td><td>มองเห็นภาพซ้อน, คลื่นไส้, ผื่นแพ้รุนแรง</td></tr>
                </tbody>
            </table>
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 12px; border-radius: 4px; font-size: 12.5px;">
                💡 <strong>คำแนะนำปฐมภูมิ:</strong> ผู้ป่วยโรคลมชักไม่ควรหยุดยาเองอย่างกะทันหัน เพราะอาจกระตุ้นให้เกิดภาวะชักต่อเนื่อง (Status Epilepticus) ได้
            </div>
        </div>
        <div style="text-align: right; margin-top: 18px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('anticonvGuideModal')">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- Modal Edit Anticonvulsant Patient Clinical Data -->
<div id="editAnticonvModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: var(--radius-lg); width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-xl); margin: 20px;">
        <form method="POST" action="/pcc/safety/anticonvulsant/update">
            <?= \App\Core\CSRF::field() ?>
            <input type="hidden" name="id" id="editAnticonvId">
            <input type="hidden" name="pid" id="editAnticonvPid">

            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        ✏️ ปรับปรุงข้อมูลคลินิกผู้รับยากันชัก (Anticonvulsant Registry Edit)
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        🛡️ ทุกการแก้ไขข้อมูลจะถูกบันทึกค่า Log ลงใน audit_logs พร้อม Snapshot อัตโนมัติ
                    </div>
                </div>
                <button type="button" onclick="closeEditAnticonvModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div style="padding: 24px;">
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;" id="editAnticonvPatientName"></div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;" id="editAnticonvPatientMeta"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ขนาดยาต่อวัน (Daily Dose)</label>
                        <input type="text" name="daily_dose" id="editAnticonvDose" class="form-control" placeholder="เช่น 60 mg hs">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ข้อบ่งใช้ทางคลินิก (Indication)</label>
                        <input type="text" name="indication" id="editAnticonvIndication" class="form-control" placeholder="เช่น Generalized tonic-clonic seizure">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">การคุมอาการชัก (Seizure Control)</label>
                        <select name="seizure_control" id="editAnticonvControl" class="form-control">
                            <option value="free_gt6m">สงบดี &gt; 6 เดือน (Seizure Free)</option>
                            <option value="occasional">มีชักเป็นครั้งคราว (Occasional)</option>
                            <option value="uncontrolled">ยังคุมอาการไม่ได้ (Uncontrolled)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ผลตรวจยีน HLA-B*1502</label>
                        <select name="hla_b1502_status" id="editAnticonvHla" class="form-control">
                            <option value="not_tested">ยังไม่ตรวจ (Not Tested)</option>
                            <option value="negative">Negative (ไม่พบยีนแพ้ยา ปลอดภัย)</option>
                            <option value="positive">Positive (⚠️ พบยีนแพ้ยารุนแรง ห้ามใช้ CBZ)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ระดับยาในเลือดล่าสุด (TDM Level mcg/mL)</label>
                        <input type="number" step="0.01" name="latest_tdm_level" id="editAnticonvTdm" class="form-control" placeholder="เช่น 22.40">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600;">ความร่วมมือในการใช้ยา (Adherence)</label>
                        <select name="adherence_score" id="editAnticonvAdherence" class="form-control">
                            <option value="high">รับประทานสม่ำเสมอ (High Adherence)</option>
                            <option value="medium">มีลืมรับประทานเป็นครั้งคราว (Medium)</option>
                            <option value="low">ขาดยาบ่อย/ไม่ร่วมมือ (Low Adherence)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">บันทึกการบริบาลเภสัชกรรม / อาการข้างเคียง (Pharmacist Notes)</label>
                    <textarea name="notes" id="editAnticonvNotes" class="form-control" rows="3" placeholder="ระบุอาการง่วงซึม เดินเซ หรือคำแนะนำการใช้ยา..."></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditAnticonvModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 บันทึกข้อมูล & Audit Log</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAnticonvModal(p) {
    document.getElementById('editAnticonvId').value = p.id || '';
    document.getElementById('editAnticonvPid').value = p.pid || '';
    document.getElementById('editAnticonvPatientName').textContent = (p.patient ? p.patient.full_name : ('PID: ' + p.pid)) + ' — ' + (p.drug_name || '');
    document.getElementById('editAnticonvPatientMeta').textContent = `PID: ${p.pid} | อายุ: ${p.patient ? p.patient.age : '-'} ปี | ยา: ${p.drug_name || '-'}`;
    
    document.getElementById('editAnticonvDose').value = p.daily_dose || '';
    document.getElementById('editAnticonvIndication').value = p.indication || '';
    document.getElementById('editAnticonvControl').value = p.seizure_control || 'free_gt6m';
    document.getElementById('editAnticonvHla').value = p.hla_b1502 || p.hla_b1502_status || 'not_tested';
    document.getElementById('editAnticonvTdm').value = p.tdm_level !== undefined ? p.tdm_level : '';
    document.getElementById('editAnticonvAdherence').value = p.adherence || p.adherence_score || 'high';
    document.getElementById('editAnticonvNotes').value = p.notes || '';

    document.getElementById('editAnticonvModal').style.display = 'flex';
}

function closeEditAnticonvModal() {
    document.getElementById('editAnticonvModal').style.display = 'none';
}
</script>

