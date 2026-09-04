<?php
/**
 * =====================================================
 * صفحة تواصل معنا - Contact Us
 * =====================================================
 */
$pageTitle = 'اتصل بنا';
require_once __DIR__ . '/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = 'يرجى تعبئة جميع الحقول المطلوبة.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'صيغة البريد الإلكتروني غير صحيحة.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $_SESSION['success'] = 'تم إرسال رسالتك بنجاح، سيتم الرد عليها قريباً.';
            
            // إعادة توجيه لنفس الصفحة لتفريغ الحقول
            header('Location: ' . BASE_URL . '/contact.php');
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, #0f172a, #334155);">
    <h1><i class="fas fa-envelope-open-text"></i> تواصل مع الدعم الفني</h1>
    <p>نحن هنا لمساعدتك والإجابة على أي استفسار يخص النظام</p>
</div>

<div class="form-grid" style="gap: 20px;">
    <!-- معلومات الاتصال -->
    <div class="card" style="align-self: start;">
        <div class="card-header">
            <h2><i class="fas fa-map-marker-alt"></i> معلومات التواصل</h2>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 4px; color: var(--text-primary);">الدعم الفني</h4>
                    <span style="color: var(--text-secondary);">+966 50 123 4567</span>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 4px; color: var(--text-primary);">البريد الإلكتروني</h4>
                    <span style="color: var(--text-secondary);">support@library-system.com</span>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 4px; color: var(--text-primary);">أوقات العمل</h4>
                    <span style="color: var(--text-secondary);">الأحد - الخميس (8 ص - 4 م)</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نموذج المراسلة -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-paper-plane"></i> أرسل استفسارك</h2>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="name">الاسم الكامل <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $_SESSION['full_name'] ?? '') ?>" placeholder="أدخل اسمك">
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@email.com">
                </div>
                
                <div class="form-group">
                    <label for="subject">الموضوع</label>
                    <input type="text" id="subject" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="عنوان رسالتك">
                </div>
                
                <div class="form-group">
                    <label for="message">الرسالة <span class="required">*</span></label>
                    <textarea id="message" name="message" class="form-control" required rows="5" placeholder="اكتب تفاصيل استفسارك أو مشكلتك هنا..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 16px; padding: 12px;">
                    <i class="fas fa-paper-plane"></i> إرسال الرسالة
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
