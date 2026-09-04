<?php
/**
 * =====================================================
 * ملف حذف كتاب
 * =====================================================
 * لا يحتوي على واجهة مستخدم (UI)، يقوم بالحذف والتوجيه
 */
require_once __DIR__ . '/../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // 1. التحقق من إمكانية الحذف (هل هناك نسخ مستعارة؟)
        // عند استخدام ON DELETE CASCADE، حذف الكتاب سيحذف سجلات استعارته التاريخية
        // لكن من الأفضل عدم السماح بحذف كتاب إذا كانت هناك نسخ مستعارة "حالياً"
        
        $checkBorrowStmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status = 'borrowed'");
        $checkBorrowStmt->execute([$id]);
        $activeBorrowings = $checkBorrowStmt->fetchColumn();
        
        if ($activeBorrowings > 0) {
            $_SESSION['error'] = 'لا يمكن حذف هذا الكتاب لوجود نسخ مستعارة منه حالياً. يرجى إرجاع النسخ أولاً.';
        } else {
            // 2. جلب مسار الصورة لمعرفة إذا احتجنا لحذفها من السيرفر
            $imgStmt = $pdo->prepare("SELECT cover_image, title FROM books WHERE id = ?");
            $imgStmt->execute([$id]);
            $book = $imgStmt->fetch();
            
            if ($book) {
                // 3. حذف الكتاب من قاعدة البيانات
                $delStmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                $delStmt->execute([$id]);
                
                // 4. حذف الصورة من المجلد إذا كانت موجودة
                if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../' . $book['cover_image'])) {
                    unlink(__DIR__ . '/../' . $book['cover_image']);
                }
                
                $_SESSION['success'] = 'تم حذف كتاب "' . htmlspecialchars($book['title']) . '" بنجاح.';
            } else {
                $_SESSION['error'] = 'الكتاب غير موجود أصلاً.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'حدث خطأ أثناء الحذف: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'رقم غير صحيح.';
}

// العودة إلى قائمة الكتب
header('Location: ' . BASE_URL . '/books/index.php');
exit();
?>
