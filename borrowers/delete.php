<?php
/**
 * =====================================================
 * ملف حذف مستعير
 * =====================================================
 */
require_once __DIR__ . '/../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // 1. التحقق من إمكانية الحذف - هل المستعير يمتلك كتباً لم يقم بإرجاعها؟
        $checkBorrowStmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE borrower_id = ? AND status = 'borrowed'");
        $checkBorrowStmt->execute([$id]);
        $activeBorrowings = $checkBorrowStmt->fetchColumn();
        
        if ($activeBorrowings > 0) {
            $_SESSION['error'] = 'لا يمكن حذف حساب هذا المستعير! لديه كتب مستعارة حالياً ويجب إرجاعها أولاً، أو قم بحذف الاستعارات المتعلقة به.';
        } else {
            // 2. جلب اسم المستعير لرسالة التأكيد
            $nameStmt = $pdo->prepare("SELECT name FROM borrowers WHERE id = ?");
            $nameStmt->execute([$id]);
            $borrowerName = $nameStmt->fetchColumn();
            
            if ($borrowerName) {
                // 3. حذف المستعير (سجلاته المرجعة سيتم حذفها تلقائياً بسبب CASCADE)
                $delStmt = $pdo->prepare("DELETE FROM borrowers WHERE id = ?");
                $delStmt->execute([$id]);
                
                $_SESSION['success'] = 'تم حذف المستعير "' . htmlspecialchars($borrowerName) . '" بنجاح من النظام.';
            } else {
                $_SESSION['error'] = 'المستعير غير موجود في النظام.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'حدث خطأ أثناء الحذف: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'رقم غير صحيح.';
}

header('Location: ' . BASE_URL . '/borrowers/index.php');
exit();
?>
