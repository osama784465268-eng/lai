<?php
/**
 * =====================================================
 * صفحة إرجاع كتاب (ليس لها واجهة مرئية، تقوم بالمعالجة فقط)
 * =====================================================
 */
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = (int)($_POST['id'] ?? 0);
    
    if ($borrow_id > 0) {
        try {
            // بدء معاملة (Transaction) لضمان اتساق البيانات
            $pdo->beginTransaction();
            
            // 1. جلب بيانات الاستعارة للتحقق وجلب رقم الكتاب
            $stmt = $pdo->prepare("SELECT book_id, status FROM borrowings WHERE id = ? FOR UPDATE");
            $stmt->execute([$borrow_id]);
            $borrow = $stmt->fetch();
            
            if ($borrow) {
                if ($borrow['status'] === 'returned') {
                    $_SESSION['error'] = 'هذا الكتاب تم إرجاعه مسبقاً.';
                } else {
                    $book_id = $borrow['book_id'];
                    
                    // 2. تحديث حالة الاستعارة لتصبح 'returned' مع تاريخ اليوم
                    $updateBorrow = $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = NOW() WHERE id = ?");
                    $updateBorrow->execute([$borrow_id]);
                    
                    // 3. زيادة عدد النسخ المتوفرة من هذا الكتاب بمقدار 1
                    $updateBook = $pdo->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?");
                    $updateBook->execute([$book_id]);
                    
                    // تأكيد العملية
                    $pdo->commit();
                    $_SESSION['success'] = 'تم استلام الكتاب وإرجاعه للمكتبة بنجاح!';
                }
            } else {
                $pdo->rollBack();
                $_SESSION['error'] = 'سجل الاستعارة غير موجود.';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'حدث خطأ أثناء إجراء عملية الإرجاع: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'طلب غير صحيح.';
    }
} else {
    $_SESSION['error'] = 'غير مسموح بهذا الإجراء المباشر.';
}

header('Location: ' . BASE_URL . '/borrow/index.php');
exit();
?>
