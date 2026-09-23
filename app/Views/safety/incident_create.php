<?php
use App\Core\CSRF;
?>
<div class="incident-create-container" style="max-width: 900px; margin: 0 auto;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 26px;">🚨</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    แบบรายงานอุบัติการณ์ความคลาดเคลื่อนทางยา (Medication Incident Report)
                </h1>
            </div>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 4px 0 0 0;">
                ระบบรายงานเพื่อการเรียนรู้และพัฒนาเชิงระบบ (Non-punitive Reporting System) ตามมาตรฐาน 2P Safety
            </p>
        </div>
        <a href="/pcc/safety/incidents" class="btn btn-secondary">
            ⬅ ย้อนกลับไปทะเบียนอุบัติการณ์
        </a>
    </div>

    <!-- Incident Form Card -->
    <div class="card" style="border-top: 4px solid #ef4444;">
        <div class="card-body" style="padding: 28px;">
            <form method="POST" action="/pcc/safety/incidents">
                <?= CSRF::field() ?>

                <!-- Section 1: When & Where -->
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                    <span>🕒</span> ข้อมูลวันเวลา และ ขั้นตอนที่เกิดเหตุ
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            วัน-เวลาที่เกิดเหตุ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="datetime-local" name="incident_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            ขั้นตอนที่เกิดเหตุ (Process Stage) <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="incident_stage" class="form-control" required style="font-size: 13.5px;">
                            <option value="prescribing">1. สั่งใช้ยาของแพทย์ (Prescribing)</option>
                            <option value="transcribing">2. คัดลอกคำสั่ง / คีย์คอมพิวเตอร์ (Transcribing)</option>
                            <option value="dispensing" selected>3. จัดยา / จ่ายยา (Dispensing)</option>
                            <option value="administration">4. การบริหารยา / ให้ยาแก่ผู้ป่วย (Administration)</option>
                            <option value="monitoring">5. การติดตามผลการใช้ยา (Monitoring)</option>
                            <option value="storage">6. การจัดเก็บยาในคลัง / ตู้เย็น (Storage)</option>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Severity (NCC MERP Category A-I) -->
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 24px 0 16px 0; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                    <span>🎯</span> ระดับความรุนแรงตามมาตรฐานสากล NCC MERP Index (Category A – I)
                </h3>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">
                        เลือกระดับความรุนแรง <span style="color: #ef4444;">*</span>
                    </label>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                        <!-- Category A -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="A" style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #166534;">Category A</strong>
                                <div style="font-size: 11.5px; color: #15803d;">สภาพแวดล้อมที่อาจก่อให้เกิดความคลาดเคลื่อน (Potential Error)</div>
                            </div>
                        </label>

                        <!-- Category B -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #f0fdf4; border: 2px solid #22c55e; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="B" checked style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #166534;">Category B (Near Miss)</strong>
                                <div style="font-size: 11.5px; color: #15803d;">เกิดความคลาดเคลื่อน แต่ตรวจพบและสกัดได้ก่อนถึงผู้ป่วย</div>
                            </div>
                        </label>

                        <!-- Category C -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="C" style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #854d0e;">Category C</strong>
                                <div style="font-size: 11.5px; color: #a16207;">ความคลาดเคลื่อนถึงผู้ป่วย แต่ไม่ทำให้เกิดอันตราย</div>
                            </div>
                        </label>

                        <!-- Category D -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #fefce8; border: 1px solid #fde047; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="D" style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #854d0e;">Category D</strong>
                                <div style="font-size: 11.5px; color: #a16207;">ถึงผู้ป่วย และจำเป็นต้องเฝ้าระวังเพื่อยืนยันว่าปลอดภัย</div>
                            </div>
                        </label>

                        <!-- Category E -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="E" style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #991b1b;">Category E</strong>
                                <div style="font-size: 11.5px; color: #b91c1c;">เกิดอันตรายชั่วคราว และต้องได้รับการบำบัดรักษา</div>
                            </div>
                        </label>

                        <!-- Category F-I -->
                        <label style="display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; background: #fef2f2; border: 1px solid #f87171; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="severity_category" value="F" style="margin-top: 3px;">
                            <div>
                                <strong style="font-size: 13px; color: #991b1b;">Category F - I (รุนแรงสูง)</strong>
                                <div style="font-size: 11.5px; color: #b91c1c;">ต้องนอน รพ., เกิดอันตรายถาวร หรือเสียชีวิต</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" id="is_near_miss" name="is_near_miss" value="1" checked style="width: 18px; height: 18px;">
                    <label for="is_near_miss" style="font-size: 13px; font-weight: 600; color: #1e293b; cursor: pointer;">
                        🎯 จัดเป็นเหตุการณ์เกือบพลาด (Near Miss: ระดับ A หรือ B ที่สกัดกั้นได้ทันก่อนส่งมอบถึงมือผู้ป่วย)
                    </label>
                </div>

                <!-- Section 3: Medication & Incident Details -->
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 24px 0 16px 0; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                    <span>💊</span> รายการยาและรายละเอียดอุบัติการณ์
                </h3>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        รายการยาที่เกี่ยวข้อง (ระบุชื่อยา ความแรง หรือคู่เทียบยา LASA) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="drugs_involved" list="recentDrugsList" class="form-control" placeholder="เช่น Amlodipine 5 mg tab vs Amitriptyline 10 mg tab หรือ Insulin Regular" required style="font-size: 13.5px;">
                    <datalist id="recentDrugsList">
                        <?php foreach ($recentDrugs as $d): ?>
                            <option value="<?= htmlspecialchars($d['drugname']) ?>"><?= htmlspecialchars($d['drugcode']) ?></option>
                        <?php endforeach; ?>
                    </datalist>
                    <small style="color: var(--text-muted); font-size: 11.5px;">พิมพ์ชื่อยาเพื่อค้นหาจากคลังยา JHCIS ได้อัตโนมัติ</small>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        รายละเอียดของอุบัติการณ์ที่เกิดขึ้น <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="incident_description" rows="4" class="form-control" placeholder="อธิบายลำดับเหตุการณ์ สิ่งที่ตรวจพบ และจุดที่เกิดความผิดพลาด..." required style="font-size: 13.5px; line-height: 1.5;"></textarea>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        การแก้ไขและช่วยเหลือเฉพาะหน้าทันที (Immediate Action Taken)
                    </label>
                    <textarea name="immediate_action_taken" rows="2" class="form-control" placeholder="เช่น เปลี่ยนยาให้ถูกต้องทันทีก่อนส่งมอบ, ประสานแพทย์แก้ไขคำสั่ง, หรือโทรติดตามผู้ป่วย..." style="font-size: 13.5px;"></textarea>
                </div>

                <!-- Section 4: Non-punitive & Reporter Policy -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <strong style="font-size: 13.5px; color: #1e40af;">🛡️ นโยบายความปลอดภัยที่ไม่มุ่งโทษ (Non-Punitive Safety Culture)</strong>
                            <div style="font-size: 12px; color: #3b82f6; margin-top: 2px;">
                                ข้อมูลนี้ใช้เพื่อการปรับปรุงระบบงานและป้องกันความผิดพลาดซ้ำ ไม่นำมาประเมินความดีความชอบ
                            </div>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #1e3a8a; cursor: pointer;">
                            <input type="checkbox" name="is_anonymous" value="1" style="width: 16px; height: 16px;">
                            <span>รายงานแบบไม่ประสงค์ออกนาม (Anonymous)</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="/pcc/safety/incidents" class="btn btn-secondary">
                        ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none; padding: 10px 24px; font-size: 14px; font-weight: 600;">
                        💾 บันทึกรายงานอุบัติการณ์
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
