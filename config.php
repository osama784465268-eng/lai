<?php
/**
 * =====================================================
 * ملف الإعدادات العامة - Configuration
 * =====================================================
 * يحتوي على الثوابت والإعدادات المستخدمة في جميع صفحات المشروع
 */

// بدء الجلسة إذا لم تكن قد بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// المسار الأساسي للمشروع (يدعم النشر المحالي وعلى Railway)
if (!defined('BASE_URL')) {
    $env_url = getenv('BASE_URL');
    if ($env_url !== false) {
        define('BASE_URL', $env_url);
    } else {
        $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        define('BASE_URL', (strpos($_SERVER['REQUEST_URI'] ?? '', '/library-management-system') !== false) ? '/library-management-system' : '');
    }
}

// اسم المشروع
define('SITE_NAME', 'نظام إدارة المكتبة');

// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/database/connection.php';

/**
 * دالة للتحقق من تسجيل الدخول
 * تُعيد true إذا كان المستخدم مسجل الدخول
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * دالة لحماية الصفحات - تمنع الوصول بدون تسجيل دخول
 * إذا لم يكن المستخدم مسجل الدخول، يتم توجيهه لصفحة تسجيل الدخول
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit();
    }
}

/**
 * دالة لعرض رسائل النجاح والخطأ
 * تقرأ الرسائل من الجلسة وتعرضها ثم تحذفها
 */
function showAlert() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">';
        echo '<span class="alert-icon">✓</span>';
        echo '<span>' . $_SESSION['success'] . '</span>';
        echo '<button class="alert-close" onclick="this.parentElement.remove()">×</button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">';
        echo '<span class="alert-icon">✗</span>';
        echo '<span>' . $_SESSION['error'] . '</span>';
        echo '<button class="alert-close" onclick="this.parentElement.remove()">×</button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}

/**
 * دالة لتنظيف المدخلات من الأكواد الضارة
 * تمنع هجمات XSS
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>
