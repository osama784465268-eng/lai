<?php
/**
 * =====================================================
 * صفحة حول النظام - About Us
 * =====================================================
 */
$pageTitle = 'حول النظام';
require_once __DIR__ . '/config.php';
requireLogin();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1><i class="fas fa-book-reader"></i> نظام إدارة المكتبة الإلكترونية</h1>
    <p>نظام متكامل لإدارة الكتب والمستعيرين وحركات الاستعارة بكل سهولة واحترافية</p>
</div>

<div class="info-grid">
    <div class="info-card">
        <div class="info-icon">
            <i class="fas fa-book"></i>
        </div>
        <h3>إدارة الكتب بفاعلية</h3>
        <p>تحكم كامل بمخزون المكتبة، إضافة وتعديل الكتب مع دعم تصنيفات متعددة وإدارة تلقائية للكميات المتوفرة.</p>
    </div>
    
    <div class="info-card">
        <div class="info-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>سجل المستعيرين</h3>
        <p>تسجيل بيانات المستعيرين بشكل آمن مع منع التكرار، وربط دقيق بين المستعير والكتب المؤجرة له.</p>
    </div>
    
    <div class="info-card">
        <div class="info-icon">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <h3>نظام الاستعارة</h3>
        <p>تتبع دقيق لعمليات الاستعارة، تنبيهات التأخير، وإدارة مواعيد الإرجاع لضمان حقوق المكتبة والمستعير.</p>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2><i class="fas fa-code"></i> عن المطور والمشروع</h2>
    </div>
    <div class="card-body">
        <p style="font-size: 16px; line-height: 1.8; color: var(--text-secondary);">
            تم بناء هذا النظام كتطبيق عملي شامل باستخدام لغة <strong>PHP</strong> المعاصرة وقواعد بيانات <strong>MySQL</strong> بواجهة برمجة التطبيقات (PDO) لضمان أقصى درجات الأمان. 
            <br><br>
            يعتمد التصميم على <strong>HTML5 & CSS3</strong> نقي مع دعم كامل للغة العربية (RTL) واستجابة تامة لجميع مقاسات الشاشات (Responsive Design).
            <br><br>
            <strong>أهم التقنيات المستخدمة:</strong>
        </p>
        <ul style="margin-top: 15px; margin-right: 20px; color: var(--text-secondary); line-height: 1.8;">
            <li>PHP 8+ (PDO, Sessions, Password Hashing)</li>
            <li>MySQL (Relational Database, Foreign Keys, Cascade)</li>
            <li>Pure CSS (Flexbox, Grid, CSS Variables)</li>
            <li>Vanilla JavaScript (DOM Manipulation)</li>
            <li>Font Awesome & Google Fonts (Tajawal)</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
