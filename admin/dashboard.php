<?php
/**
 * =====================================================
 * لوحة التحكم الرئيسية - Dashboard
 * =====================================================
 * تعرض إحصائيات عامة عن النظام:
 * عدد الكتب، عدد المستعيرين، العمليات، إلخ.
 */

$pageTitle = 'لوحة التحكم';
require_once __DIR__ . '/../config.php';

// حماية الصفحة: تمنع وصول أي شخص غير مسجل الدخول
requireLogin();

try {
    // 1. حساب إجمالي عدد الكتب
    $stmtBooks = $pdo->query("SELECT SUM(quantity) as total_books, SUM(available_quantity) as available_books, COUNT(id) as unique_titles FROM books");
    $booksStats = $stmtBooks->fetch();
    $totalBooks = $booksStats['total_books'] ?? 0;
    
    // 2. حساب عدد المستعيرين المسجلين
    $stmtBorrowers = $pdo->query("SELECT COUNT(id) as total_borrowers FROM borrowers");
    $totalBorrowers = $stmtBorrowers->fetch()['total_borrowers'] ?? 0;
    
    // 3. حساب إجمالي عمليات الاستعارة (الكتب غير المرجعة بعد)
    $stmtBorrowings = $pdo->query("SELECT COUNT(id) as active_borrowings FROM borrowings WHERE status = 'borrowed'");
    $activeBorrowings = $stmtBorrowings->fetch()['active_borrowings'] ?? 0;
    
    // 4. جلب أحدث عمليات الاستعارة (للعرض السريع)
    $stmtRecent = $pdo->query("
        SELECT b.id, bk.title as book_title, br.name as borrower_name, b.borrow_date, b.due_date, b.status 
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.id
        JOIN borrowers br ON b.borrower_id = br.id
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    $recentBorrowings = $stmtRecent->fetchAll();
    
} catch (PDOException $e) {
    die("خطأ في جلب الإحصائيات: " . $e->getMessage());
}

// الهيدر (شريط التنقل الجانبي والعلوي)
require_once __DIR__ . '/../includes/header.php';
?>

<!-- قسم البطاقات الإحصائية -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <p>إجمالي الكتب</p>
            <h3><?= number_format($totalBooks) ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <p>المستعيرين المسجلين</p>
            <h3><?= number_format($totalBorrowers) ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-info">
            <p>الاستعارات النشطة</p>
            <h3><?= number_format($activeBorrowings) ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon cyan">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <p>عناوين متوفرة</p>
            <h3><?= number_format($booksStats['unique_titles'] ?? 0) ?></h3>
        </div>
    </div>
</div>

<!-- قسم أحدث العمليات -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> أحدث عمليات الاستعارة</h2>
        <a href="<?= BASE_URL ?>/borrow/index.php" class="btn btn-sm btn-primary">
            تصفح الكل <i class="fas fa-arrow-left"></i>
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($recentBorrowings)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم العملية</th>
                        <th>الكتاب</th>
                        <th>المستعير</th>
                        <th>تاريخ الاستعارة</th>
                        <th>تاريخ الإرجاع استحقاقاً</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBorrowings as $borrow): ?>
                    <tr>
                        <td>#<?= $borrow['id'] ?></td>
                        <td><strong><?= htmlspecialchars($borrow['book_title']) ?></strong></td>
                        <td><?= htmlspecialchars($borrow['borrower_name']) ?></td>
                        <td><?= date('Y/m/d', strtotime($borrow['borrow_date'])) ?></td>
                        <td><?= date('Y/m/d', strtotime($borrow['due_date'])) ?></td>
                        <td>
                            <?php if ($borrow['status'] === 'borrowed'): ?>
                                <span class="badge badge-warning">مستعار</span>
                            <?php else: ?>
                                <span class="badge badge-success">تم الإرجاع</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>لا توجد عمليات استعارة مسجلة في النظام بعد.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// الفوتر
require_once __DIR__ . '/../includes/footer.php'; 
?>
