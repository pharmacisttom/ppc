<div class="evidence-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #0f172a;">ศูนย์รวมหลักฐานเชิงประจักษ์ (Evidence Center Repository)</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                คลังจัดเก็บเอกสาร คำสั่ง ภาพถ่าย และหลักฐานเชิงประจักษ์ เชื่อมโยงกับเกณฑ์มาตรฐานปฐมภูมิ พ.ศ. 2568–2570 แบบ Many-to-Many ไม่ซ้ำซ้อน
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/hos/quality" class="btn btn-secondary">
                ⬅ กลับไปหน้าประเมินตนเอง
            </a>
            <button class="btn btn-primary" onclick="alert('ระบบรองรับการอัปโหลดไฟล์ PDF, PNG, JPG, DOCX ขนาดไม่เกิน 20MB พร้อมระบบตรวจสอบความซ้ำซ้อนด้วย SHA-256')">
                📤 เพิ่มหลักฐานใหม่
            </button>
        </div>
    </div>

    <!-- Evidence Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>📁 บัญชีรายการหลักฐานเชิงประจักษ์ที่พร้อมรับการตรวจสอบ</span>
            </div>
            <span class="badge badge-success"><?= count($evidences) ?> เอกสารรับรอง</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสเอกสาร</th>
                            <th>ชื่อหลักฐานเชิงประจักษ์</th>
                            <th>ประเภท</th>
                            <th>เกณฑ์มาตรฐานที่เชื่อมโยง</th>
                            <th>ขนาดไฟล์</th>
                            <th>ผู้อัปโหลด</th>
                            <th>วันที่อัปโหลด</th>
                            <th>สถานะการรับรอง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($evidences)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    ยังไม่มีรายการหลักฐานเชิงประจักษ์
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($evidences as $e): ?>
                                <tr>
                                    <td><strong>#EVD-<?= (int)$e['evidence_id'] ?></strong></td>
                                    <td>
                                        <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($e['title']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                            <?= htmlspecialchars($e['description'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($e['file_type'] ?? 'PDF') ?></span></td>
                                    <td>
                                        <?php if (!empty($e['linked_criteria'])): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($e['linked_criteria']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12px;"><?= number_format(($e['file_size_bytes'] ?? 102400) / 1024, 1) ?> KB</td>
                                    <td style="font-size: 13px;"><?= htmlspecialchars(($e['firstname'] ?? '') . ' ' . ($e['lastname'] ?? '')) ?></td>
                                    <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars(substr($e['created_at'] ?? '', 0, 10)) ?></td>
                                    <td><span class="badge badge-success"><?= htmlspecialchars($e['verification_status'] ?? 'verified') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
