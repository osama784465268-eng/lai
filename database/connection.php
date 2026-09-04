<?php
/**
 * =====================================================
 * ملف الاتصال بقاعدة البيانات - Database Connection
 * =====================================================
 * يستخدم PDO للاتصال بقاعدة البيانات MySQL
 */

// إعدادات الاتصال بقاعدة البيانات (تدعم Railway ومحلي XAMPP)
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'hopper.proxy.rlwy.net';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '14573';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'library_db';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'mOMCCLFCXnBMBlHejlEtQWdmwyxCMnkH'));
$charset = 'utf8mb4';

// دعم DATABASE_URL أو MYSQL_URL في حال توفر رابط مباشر
$db_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($db_url) {
    $parsed_url = parse_url($db_url);
    if ($parsed_url) {
        $host = $parsed_url['host'] ?? $host;
        $port = $parsed_url['port'] ?? $port;
        $username = $parsed_url['user'] ?? $username;
        $password = $parsed_url['pass'] ?? $password;
        $dbname = ltrim($parsed_url['path'] ?? '', '/') ?: $dbname;
    }
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    // الخطوة 1: نحاول الاتصال بقاعدة البيانات مباشرة
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // الخطوة 2: إذا كان الخطأ هو 1049 (قاعدة البيانات غير موجودة)، نعرض زر التنصيب
    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
        die("
        <div style='font-family: Arial, sans-serif; direction: rtl; text-align: center; padding: 40px; background: #fff3f3; color: #cc0000; border: 2px solid #ffcccc; border-radius: 12px; margin: 10% auto; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
            <h2 style='margin-top: 0;'>⚠️ قاعدة البيانات غير موجودة!</h2>
            <p style='font-size: 16px; line-height: 1.6;'>يبدو أنك لم تقم باستيراد قاعدة البيانات بعد (أو الجداول غير موجودة).</p>
            <p>لقد قمت بإنشاء مثبت تلقائي لك. اضغط على الزر أدناه لتنصيب قاعدة البيانات بنقرة واحدة:</p>
            <br>
            <a href='" . BASE_URL . "/setup.php' style='background: #dc2626; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block; transition: 0.3s;'>تنصيب قاعدة البيانات التلقائي (Setup)</a>
        </div>
        ");
    }
    
    // أي خطأ آخر في الاتصال (مثل عدم تشغيل MySQL)
    die("
    <div style='font-family: Arial, sans-serif; direction: rtl; text-align: center; padding: 40px; background: #fff3f3; color: #cc0000; border: 2px solid #ffcccc; border-radius: 12px; margin: 10% auto; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
        <h2 style='margin-top: 0;'>❌ خطأ في الاتصال بخادم قواعد البيانات</h2>
        <p style='font-size: 16px; line-height: 1.6;'>تأكد من تشغيل وحدة <b>MySQL</b> بداخل برنامج <b>XAMPP</b>.</p>
        <p dir='ltr' style='background: #fff; padding: 15px; border: 1px solid #ddd; margin-top: 20px; font-family: monospace; color: #333; text-align: left; border-radius: 6px; overflow-wrap: break-word;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}
?>
