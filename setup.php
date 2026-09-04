<?php
/**
 * =====================================================
 * ملف التنصيب التلقائي لقاعدة البيانات (Setup)
 * =====================================================
 * يقوم الـ Script هذا بقراءة ملف SQL واستيراده في MySQL بنقرة واحدة
 */

$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'mOMCCLFCXnBMBlHejlEtQWdmwyxCMnkH'));

$db_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($db_url) {
    $parsed_url = parse_url($db_url);
    if ($parsed_url) {
        $host = $parsed_url['host'] ?? $host;
        $port = $parsed_url['port'] ?? $port;
        $username = $parsed_url['user'] ?? $username;
        $password = $parsed_url['pass'] ?? $password;
    }
}

// دالة لتنفيذ الأوامر من ملف SQL وفصلها إلى مصفوفة
function executeSQLFile($pdo, $sqlFile) {
    $script = file_get_contents($sqlFile);
    if (!$script) return false;
    
    try {
        $pdo->exec($script);
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

$message = '';
$isSuccess = false;

if (isset($_POST['install'])) {
    try {
        // الاتصال بخادم MySQL العام (دون تحديد اسم Database لتتمكن من إنشائها)
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sqlPath = __DIR__ . '/database/library_db.sql';
        
        if (!file_exists($sqlPath)) {
            $message = 'ملف SQL غير موجود في المسار: ' . $sqlPath;
        } else {
            $result = executeSQLFile($pdo, $sqlPath);
            if ($result === true) {
                $isSuccess = true;
                $message = 'تم إنشاء قاعدة البيانات والجداول واستيراد البيانات بنجاح!';
            } else {
                $message = 'حدث خطأ أثناء الاستيراد: ' . $result;
            }
        }
    } catch (PDOException $e) {
        $message = 'فشل الاتصال بالخادم: ' . $e->getMessage() . '<br>تأكد من تشغيل MySQL في XAMPP.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معالج تنصيب قاعدة البيانات</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 50px 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1 { color: #1f2937; margin-top: 0; }
        p { color: #4b5563; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
        .btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background-color: #4338ca; transform: translateY(-2px); }
        .btn-success { background-color: #10b981; }
        .btn-success:hover { background-color: #059669; }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: bold;
        }
        .alert-error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background-color: #d1fae5; color: #047857; border: 1px solid #6ee7b7; }
    </style>
</head>
<body>

    <div class="container">
        <h1>🛠️ تنصيب قاعدة البيانات</h1>
        
        <?php if ($message && !$isSuccess): ?>
            <div class="alert alert-error"><?= $message ?></div>
        <?php elseif ($isSuccess): ?>
            <div class="alert alert-success">✅ <?= $message ?></div>
            <p>لقد تم إعداد النظام بالكامل. يمكنك الآن تسجيل الدخول وحذف ملف <code>setup.php</code> للأمان.</p>
            <a href="index.php" class="btn btn-success">الانتقال لتسجيل الدخول</a>
        <?php endif; ?>

        <?php if (!$isSuccess): ?>
            <p>
                يبدو أنه لم يتم إنشاء قاعدة البيانات <b>library_db</b> في الخادم المحلي.<br>
                سيقوم هذا المعالج باستيراد ملف الـ SQL وإنشاء الجداول وإدراج بيانات التجربة (بما فيها حساب الأدمن) تلقائياً.
            </p>
            <form method="POST">
                <button type="submit" name="install" class="btn">ثبّت قاعدة البيانات الآن</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
