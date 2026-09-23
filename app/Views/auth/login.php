<?php
use App\Core\CSRF;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'เข้าสู่ระบบ') ?> — PCU Smart Pharmacy</title>
    <link rel="stylesheet" href="/pcc/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: var(--primary-gradient);
            padding: 32px 28px 24px;
            color: #ffffff;
            text-align: center;
        }
        .login-logo {
            font-size: 42px;
            margin-bottom: 12px;
            display: inline-block;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
        }
        .login-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }
        .login-header p {
            font-size: 13px;
            color: var(--primary-light);
            font-weight: 400;
        }
        .login-body {
            padding: 28px;
        }
        .prod-security-notice {
            margin-top: 24px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }
        .prod-security-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #0f766e;
            font-weight: 700;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">💊</div>
        <h1>PCU Smart Pharmacy</h1>
        <p>ระบบบริหารจัดการด้านยาและบริบาลเภสัชกรรมปฐมภูมิ</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 16px; font-size: 13px; padding: 10px 14px;">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 16px; font-size: 13px; padding: 10px 14px;">
                <span>✅</span>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form action="/pcc/login" method="POST" id="loginForm">
            <?= CSRF::field() ?>
            <div class="form-group">
                <label class="form-label" for="username">ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="เช่น pcu.pharm หรือ admin" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password">รหัสผ่าน (Password)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-size: 15px; font-weight: 600;">
                เข้าสู่ระบบ 🚀
            </button>
        </form>

        <div class="prod-security-notice">
            <div class="prod-security-badge">
                <span>🛡️</span>
                <span>ระบบใช้งานจริง (Live Production Environment)</span>
            </div>
            <div>
                เชื่อมต่อฐานข้อมูล JHCIS แบบ <strong>Read-Only 100%</strong> เพื่อความปลอดภัยสูงสุดของข้อมูลผู้รับบริการ และบันทึกประวัติการเข้าใช้งานตามมาตรฐานความมั่นคงปลอดภัยสารสนเทศ สธ.
            </div>
        </div>

        <p style="text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 18px;">
            🔒 รองรับการคุ้มครองข้อมูลส่วนบุคคลตามมาตรฐาน PDPA & JHCIS Isolation Gateway
        </p>
    </div>
</div>

</body>
</html>
