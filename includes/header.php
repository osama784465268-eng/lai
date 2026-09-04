<?php
/**
 * =====================================================
 * الهيدر العلوي - Header
 * =====================================================
 * يتم تضمينه في أعلى كل صفحة
 * يحتوي على: عنوان الصفحة، ربط ملفات CSS، شريط التنقل العلوي
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="نظام إدارة المكتبة الإلكترونية - Library Management System">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Google Fonts - خط Tajawal العربي -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome - الأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- ملف التنسيق الرئيسي -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- =====================================================
         القائمة الجانبية - Sidebar Navigation
         =====================================================  -->
    <?php if (isLoggedIn()): ?>
    <aside class="sidebar" id="sidebar">
        <!-- شعار واسم النظام -->
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h2><?= SITE_NAME ?></h2>
        </div>

        <!-- قائمة التنقل -->
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>لوحة التحكم</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/books/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'books') !== false ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>إدارة الكتب</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/borrowers/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'borrowers') !== false ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>إدارة المستعيرين</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/borrow/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'borrow') !== false ? 'active' : '' ?>">
                        <i class="fas fa-exchange-alt"></i>
                        <span>عمليات الاستعارة</span>
                    </a>
                </li>
                <li class="nav-divider"></li>
                <li>
                    <a href="<?= BASE_URL ?>/about.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'about') !== false ? 'active' : '' ?>">
                        <i class="fas fa-info-circle"></i>
                        <span>حول النظام</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/contact.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'contact') !== false ? 'active' : '' ?>">
                        <i class="fas fa-envelope"></i>
                        <span>اتصل بنا</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- معلومات المستخدم وزر الخروج -->
        <div class="sidebar-footer">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= $_SESSION['full_name'] ?? 'مدير النظام' ?></span>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>تسجيل الخروج</span>
            </a>
        </div>
    </aside>
    <?php endif; ?>

    <!-- =====================================================
         المحتوى الرئيسي - Main Content Area
         =====================================================  -->
    <main class="main-content <?= isLoggedIn() ? 'with-sidebar' : 'full-width' ?>">
        <!-- شريط التنقل العلوي -->
        <?php if (isLoggedIn()): ?>
        <header class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?= $pageTitle ?? 'لوحة التحكم' ?></h1>
            <div class="top-bar-actions">
                <span class="current-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('Y/m/d') ?>
                </span>
            </div>
        </header>
        <?php endif; ?>

        <!-- منطقة عرض المحتوى -->
        <div class="content-wrapper">
            <?php showAlert(); ?>
