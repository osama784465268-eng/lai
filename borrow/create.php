<?php
/**
 * =====================================================
 * صفحة تسجيل عملية استعارة جديدة
 * =====================================================
 */
$pageTitle = 'استعارة جديدة';
require_once __DIR__ . '/../config.php';
requireLogin();

// جلب الكتب المتاحة للاستعارة فقط (التي كميتها المتوفرة أكبر من 0)
$books = $pdo->query("SELECT id, title, author, available_quantity FROM books WHERE available_quantity > 0 ORDER BY title ASC")->fetchAll();

// جلب جميع المستعيرين المسجلين
$borrowers = $pdo->query("SELECT id, name, national_id, phone FROM borrowers ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = (int)($_POST['book_id'] ?? 0);
    $borrower_id = (int)($_POST['borrower_id'] ?? 0);
    $due_date = sanitize($_POST['due_date'] ?? '');
    
    // للتحقق من أن تاريخ الاستحقاق في المستقبل
    $today = date('Y-m-d');
    
    if (empty($book_id) || empty($borrower_id) || empty($due_date)) {
        $_SESSION['error'] = 'يرجى تعبئة جميع الحقول المطلوبة.';
    } elseif ($due_date <= $today) {
        $_SESSION['error'] = 'يجب أن يكون تاريخ الإرجاع المتوقع موعداً في المستقبل (غداً أو بعده).';
    } else {
        try {
            $pdo->beginTransaction();
            
            // 1. التحقق من أن هذا المستعير لم يستعر نفس الكتاب حالياً وأنه لم يرجعه بعد
            $checkDup = $pdo->prepare("SELECT id FROM borrowings WHERE book_id = ? AND borrower_id = ? AND status = 'borrowed'");
            $checkDup->execute([$book_id, $borrower_id]);
            if ($checkDup->rowCount() > 0) {
                throw new Exception("المستعير يحتفظ بنسخة من هذا الكتاب حالياً ولم يقم بإرجاعها.");
            }
            
            // 2. التحقق من توفر الكتاب
            $checkBook = $pdo->prepare("SELECT available_quantity FROM books WHERE id = ? FOR UPDATE");
            $checkBook->execute([$book_id]);
            $bookData = $checkBook->fetch();
            
            if (!$bookData || $bookData['available_quantity'] <= 0) {
                throw new Exception("عذراً، هذا الكتاب نفدت الكمية المتاحة منه حالياً.");
            }
            
            // 3. إنقاص الكمية المتاحة
            $updateBook = $pdo->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?");
            $updateBook->execute([$book_id]);
            
            // 4. تسجيل الاستعارة
            $insertBorrow = $pdo->prepare("INSERT INTO borrowings (book_id, borrower_id, borrow_date, due_date, status) VALUES (?, ?, CURDATE(), ?, 'borrowed')");
            $insertBorrow->execute([$book_id, $borrower_id, $due_date]);
            
            $pdo->commit();
            $_SESSION['success'] = 'تم تسجيل عملية الاستعارة بنجاح وتم تسليم الكتاب!';
            header('Location: ' . BASE_URL . '/borrow/index.php');
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

// حساب تاريخ افتراضي (أسبوع واحد من الآن)
$defaultDueDate = date('Y-m-d', strtotime('+7 days'));
$minDate = date('Y-m-d', strtotime('+1 day'));
?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <h2><i class="fas fa-handshake"></i> تسجيل وتسليم كتاب لمستعير</h2>
        <a href="<?= BASE_URL ?>/borrow/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> إلغاء
        </a>
    </div>
    
    <div class="card-body">
        
        <?php if (empty($books)): ?>
            <div class="alert alert-error">لا توجد كتب متاحة للاستعارة حالياً (تمت استعارتها جميعاً أو لم يتم إدخالها). يرجى إضافة كتب جديدة.</div>
        <?php elseif (empty($borrowers)): ?>
            <div class="alert alert-error">لم يتم تسجيل أي مستعير في النظام. يرجى تسجيل مستعير أولاً من قسم (إدارة المستعيرين).</div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label for="book_id">اختيار الكتاب <span class="required">*</span></label>
                    <select id="book_id" name="book_id" class="form-control" required <?= empty($books) ? 'disabled' : '' ?>>
                        <option value="">-- اختر كتاباً من القائمة --</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['id'] ?>" <?= (isset($_POST['book_id']) && $_POST['book_id'] == $book['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($book['title']) ?> (متوفر: <?= $book['available_quantity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="borrower_id">بيانات المستعير <span class="required">*</span></label>
                    <select id="borrower_id" name="borrower_id" class="form-control" required <?= empty($borrowers) ? 'disabled' : '' ?>>
                        <option value="">-- اختر المستعير الذي سيستلم الكتاب --</option>
                        <?php foreach ($borrowers as $borrower): ?>
                            <option value="<?= $borrower['id'] ?>" <?= (isset($_POST['borrower_id']) && $_POST['borrower_id'] == $borrower['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($borrower['name']) ?> 
                                <?= !empty($borrower['national_id']) ? " - هوية: " . htmlspecialchars($borrower['national_id']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="due_date">تاريخ الإرجاع المتوقع <span class="required">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required 
                           min="<?= $minDate ?>" 
                           value="<?= htmlspecialchars($_POST['due_date'] ?? $defaultDueDate) ?>">
                    <small style="color:var(--text-light)">الافتراضي هو أسبوع واحد من تاريخ اليوم.</small>
                </div>
                
            </div>
            
            <div class="form-group full-width" style="margin-top: 15px;">
                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 12px 24px; width: 100%; justify-content: center;" <?= (empty($books) || empty($borrowers)) ? 'disabled' : '' ?>>
                    <i class="fas fa-check-circle"></i> تأكيد عملية الاستعارة المستوفية
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
