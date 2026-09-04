<?php
/**
 * =====================================================
 * صفحة تسجيل الدخول - Login Page
 * =====================================================
 */

$pageTitle = 'تسجيل الدخول';
require_once __DIR__ . '/../config.php';

// إذا كان المستخدم مسجل الدخول بالفعل، يتم توجيهه للوحة التحكم
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit();
}

$error = '';

// معالجة نموذج تسجيل الدخول عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف المدخلات
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور.';
    } else {
        try {
            // البحث عن المستخدم في قاعدة البيانات
            $stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // التحقق من وجود المستخدم وصحة كلمة المرور
            if ($user && password_verify($password, $user['password'])) {
                // حفظ بيانات المستخدم في الجلسة (Session)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                // رسالة ترحيبية وتوجيه للوحة التحكم
                $_SESSION['success'] = 'مرحباً بك مجدداً يا ' . $user['full_name'];
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit();
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ في النظام. الرجاء المحاولة لاحقاً.';
            // يمكن تسجيل الخطأ في ملف السجل للتطوير: error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | <?= SITE_NAME ?></title>
    
    <!-- خطوط جوجل (Tajawal) وأيقونات Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- التنسيق الرئيسي -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-card">
            <!-- رأس نموذج الدخول -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-book-reader"></i>
                </div>
                <h1><?= SITE_NAME ?></h1>
                <p>الرجاء تسجيل الدخول للوصول إلى لوحة التحكم</p>
            </div>
            
            <!-- عرض رسائل الخطأ -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✗</span>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            <?php showAlert(); // لعرض أي رسالة خطأ أو نجاح قادمة من صفحات أخرى ?>
            
            <!-- نموذج تسجيل الدخول -->
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">اسم المستخدم <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="أدخل اسم المستخدم" required 
                               value="<?= htmlspecialchars($username ?? '') ?>" autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="أدخل كلمة المرور" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </button>
            </form>
        </div>
    </div>

    <!-- 스كريبت إخفاء التنبيهات وغيرها -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
