<?php
/**
 * =====================================================
 * صفحة تسجيل الخروج - Logout
 * =====================================================
 * تقوم بتدمير الجلسة (Session) وتوجيه المستخدم لصفحة الدخول
 */

require_once __DIR__ . '/../config.php';

// إفراغ المصفوفة الخاصة بمتغيرات الجلسة
$_SESSION = array();

// حذف الكوكي الخاص بالجلسة إذا تم ضبطه (زيادة أمان)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة بالكامل
session_destroy();

// بدء جلسة جديدة فقط لغرض إرسال رسالة توضيحية لصفحة تسجيل الدخول
session_start();
$_SESSION['success'] = 'تم تسجيل الخروج بنجاح.';

// التوجيه إلى صفحة تسجيل الدخول
header('Location: ' . BASE_URL . '/auth/login.php');
exit();
?>
