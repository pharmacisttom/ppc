<div class="safety-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 24px;">👥</span>
                <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">
                    ทำเนียบอาสาสมัครสาธารณสุขประจำหมู่บ้าน (อสม. ในระบบ JHCIS)
                </h1>
                <span class="badge badge-success" style="font-size: 12px;">เครือข่ายปฐมภูมิ</span>
            </div>
            <p style="font-size: 13.5px; color: #64748b; margin: 0;">
                ข้อมูล อสม. ผู้รับผิดชอบหลังคาเรือน (pidvola) จากฐานข้อมูล JHCIS รพ.สต.บ้านดอกกราย ดูแลประชาชนและการใช้ยาในชุมชน
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/pcc/dashboard" class="btn btn-secondary" style="font-size: 13px;">
                <span>📊</span> แดชบอร์ดสถิติ
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()" style="font-size: 13px;">
                <span>🖨️</span> พิมพ์ทำเนียบ อสม.
            </button>
        </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="grid-cols-3" style="margin-bottom: 22px;">
        <div class="card" style="padding: 16px; border-left: 4px solid #10b981;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">จำนวน อสม. ทั้งหมดในระบบ</div>
            <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 4px;">
                <?= (int)($stats['total_vhv'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">ท่าน</span>
            </div>
            <div style="font-size: 11.5px; color: #059669; margin-top: 4px;">
                บันทึกเชื่อมโยงในฐานข้อมูล JHCIS
            </div>
        </div>

        <div class="card" style="padding: 16px; border-left: 4px solid #3b82f6;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">หลังคาเรือนในความรับผิดชอบ</div>
            <div style="font-size: 28px; font-weight: 800; color: #1d4ed8; margin-top: 4px;">
                <?= (int)($stats['total_houses'] ?? 0) ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">หลังคาเรือน</span>
            </div>
            <div style="font-size: 11.5px; color: #2563eb; margin-top: 4px;">
                เฉลี่ยประมาณ <?= round(($stats['total_houses'] ?? 1) / max(1, ($stats['total_vhv'] ?? 1)), 1) ?> หลังคาเรือน/ท่าน
            </div>
        </div>

        <div class="card" style="padding: 16px; border-left: 4px solid #8b5cf6;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">บทบาทด้านความปลอดภัยยาชุมชน</div>
            <div style="font-size: 18px; font-weight: 800; color: #6d28d9; margin-top: 4px;">
                คัดกรอง & ติดตามยา NCD
            </div>
            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                ติดตามการกินยาต่อเนื่อง และแจ้งประวัติแพ้ยา
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="card" style="padding: 14px 18px; margin-bottom: 20px;">
        <form action="/pcc/vhv" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="ค้นหาชื่อ อสม., เลขบัตร ปชช., หรือหมู่บ้าน..." class="form-control" style="flex: 1;">
            <button type="submit" class="btn btn-primary">
                🔍 ค้นหา
            </button>
            <?php if (!empty($query)): ?>
                <a href="/pcc/vhv" class="btn btn-secondary">ล้างการค้นหา</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- VHV Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <span>📋 รายชื่อ อสม. ในระบบ JHCIS (พบ <?= count($vhvs) ?> ท่าน)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ลำดับ</th>
                            <th>PID (JHCIS)</th>
                            <th>ชื่อ-นามสกุล อสม.</th>
                            <th>อายุ/เพศ</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>หมู่บ้านที่รับผิดชอบ</th>
                            <th style="text-align: center;">หลังคาเรือนที่ดูแล</th>
                            <th>ตัวอย่างบ้านเลขที่ในความดูแล</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vhvs)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                    ไม่พบข้อมูล อสม. ตามเงื่อนไขการค้นหา
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($vhvs as $v): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <a href="/pcc/patients/<?= (int)$v['pidvola'] ?>" style="font-weight: 700; color: #4a154b;">
                                            #<?= (int)$v['pidvola'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($v['full_name']) ?></div>
                                        <div style="font-size: 11.5px; color: #64748b;">CID: <?= htmlspecialchars($v['idcard'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($v['age']) ?> ปี (<?= htmlspecialchars($v['gender']) ?>)
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($v['telephoneperson'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <strong>หมู่ <?= (int)$v['villno'] ?></strong> <?= htmlspecialchars($v['villname'] ?: '-') ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success" style="font-size: 13px; font-weight: 700;">
                                            <?= (int)$v['house_count'] ?> หลัง
                                        </span>
                                    </td>
                                    <td style="font-size: 11.5px; color: #475569; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($v['sample_houses'] ?? '') ?>">
                                        <?= htmlspecialchars($v['sample_houses'] ?? '-') ?>
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
