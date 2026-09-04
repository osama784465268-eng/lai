<?php
/**
 * =====================================================
 * صفحة إدارة الاستعارات
 * =====================================================
 */
$pageTitle = 'سجل الاستعارات';
require_once __DIR__ . '/../config.php';
requireLogin();

// فلاتر البحث
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');

$query = "
    SELECT b.id, bk.title as book_title, bk.cover_image, br.name as borrower_name, br.phone, b.borrow_date, b.due_date, b.return_date, b.status 
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    JOIN borrowers br ON b.borrower_id = br.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (bk.title LIKE ? OR br.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $borrowings = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = 'حدث خطأ في عرض بيانات الاستعارة.';
    $borrowings = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-handshake"></i> إدارة عمليات الاستعارة</h2>
        <a href="<?= BASE_URL ?>/borrow/create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> إجراء استعارة جديدة
        </a>
    </div>
    
    <div class="card-body">
        
        <form action="" method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="البحث باسم الكتاب أو المستعير..." value="<?= htmlspecialchars($search) ?>" style="flex: 1;">
            
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="">جميع الحالات</option>
                <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>مستعار (لم يُرجع)</option>
                <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>تم الإرجاع</option>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> فرز
            </button>
            <?php if (!empty($search) || !empty($status)): ?>
                <a href="<?= BASE_URL ?>/borrow/index.php" class="btn btn-warning">
                    <i class="fas fa-times"></i> مسح الفرز
                </a>
            <?php endif; ?>
        </form>

        <?php if (count($borrowings) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم #</th>
                            <th>الكتاب</th>
                            <th>المستعير</th>
                            <th>تاريخ الاستعارة</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowings as $borrow): 
                            // التحقق من التأخير
                            $isOverdue = false;
                            if ($borrow['status'] === 'borrowed' && strtotime($borrow['due_date']) < time()) {
                                $isOverdue = true;
                            }
                        ?>
                        <tr <?= $isOverdue ? 'style="background-color: #fff1f2;"' : '' ?>>
                            <td><?= $borrow['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($borrow['book_title']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($borrow['borrower_name']) ?>
                                <br>
                                <small style="color:var(--text-light);"><?= htmlspecialchars($borrow['phone']) ?></small>
                            </td>
                            <td><?= date('Y/m/d', strtotime($borrow['borrow_date'])) ?></td>
                            <td>
                                <?php if ($isOverdue): ?>
                                    <span style="color:red; font-weight:bold;">
                                        <i class="fas fa-exclamation-circle"></i> متأخر: <?= date('Y/m/d', strtotime($borrow['due_date'])) ?>
                                    </span>
                                <?php else: ?>
                                    <?= date('Y/m/d', strtotime($borrow['due_date'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($borrow['status'] === 'borrowed'): ?>
                                    <span class="badge badge-warning">مستعار</span>
                                <?php else: ?>
                                    <span class="badge badge-success">تم الإرجاع <br><small><?= date('y/m/d', strtotime($borrow['return_date'])) ?></small></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($borrow['status'] === 'borrowed'): ?>
                                    <form action="<?= BASE_URL ?>/borrow/return.php" method="POST" onsubmit="return confirm('هل أنت متأكد من استلام الكتاب وإرجاعه للمخزون؟');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $borrow['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="إرجاع الكتاب للمكتبة">
                                            <i class="fas fa-undo"></i> إرجاع الكتاب
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:var(--text-light); font-size:13px;">مكتملة</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-exchange-alt"></i>
                <p>لا توجد بيانات استعارة لعرضها.</p>
                <?php if (empty($search) && empty($status)): ?>
                    <p style="margin-top: 10px;">انقر على "إجراء استعارة جديدة" للبدء.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
