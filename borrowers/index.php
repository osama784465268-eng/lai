<?php
/**
 * =====================================================
 * صفحة إدارة المستعيرين - عرض السجلات
 * =====================================================
 */
$pageTitle = 'إدارة المستعيرين';
require_once __DIR__ . '/../config.php';
requireLogin();

$search = sanitize($_GET['search'] ?? '');

// استعلام لجلب بيانات المستعيرين مع عدد استعاراتهم الحالية (إذا رغبنا بعرضها)
$query = "
    SELECT b.*, 
           (SELECT COUNT(*) FROM borrowings WHERE borrower_id = b.id AND status = 'borrowed') as active_borrowings
    FROM borrowers b 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (b.name LIKE ? OR b.email LIKE ? OR b.phone LIKE ? OR b.national_id LIKE ?)";
    // 4 علامات استفهام
    $params = array_fill(0, 4, "%$search%");
}

$query .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $borrowers = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = 'حدث خطأ في جلب بيانات المستعيرين.';
    $borrowers = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> قائمة المستعيرين المسجلين</h2>
        <a href="<?= BASE_URL ?>/borrowers/create.php" class="btn btn-success">
            <i class="fas fa-user-plus"></i> تسجيل مستعير جديد
        </a>
    </div>
    
    <div class="card-body">
        
        <!-- شريط البحث -->
        <form action="" method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="البحث بالاسم، الإيميل، الجوال، أو رقم الهوية..." value="<?= htmlspecialchars($search) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> بحث
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>/borrowers/index.php" class="btn btn-warning">
                    <i class="fas fa-times"></i> مسح
                </a>
            <?php endif; ?>
        </form>

        <?php if (count($borrowers) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم الهوية</th>
                            <th>اسم المستعير</th>
                            <th>معلومات التواصل</th>
                            <th>العنوان</th>
                            <th>استعارات حالية</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowers as $borrower): ?>
                        <tr>
                            <td><span style="font-family: monospace;"><?= htmlspecialchars($borrower['national_id'] ?? '---') ?></span></td>
                            <td><strong><?= htmlspecialchars($borrower['name']) ?></strong></td>
                            <td>
                                <div><i class="fas fa-phone-alt" style="color:var(--text-light); width:16px;"></i> <?= htmlspecialchars($borrower['phone'] ?? 'لا يوجد') ?></div>
                                <?php if (!empty($borrower['email'])): ?>
                                    <div style="font-size: 13px;"><i class="fas fa-envelope" style="color:var(--text-light); width:16px;"></i> <?= htmlspecialchars($borrower['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(mb_strimwidth($borrower['address'] ?? '---', 0, 30, '...')) ?></td>
                            <td>
                                <?php if ($borrower['active_borrowings'] > 0): ?>
                                    <span class="badge badge-warning"><?= $borrower['active_borrowings'] ?> كتب</span>
                                <?php else: ?>
                                    <span class="badge badge-success">لا يوجد متأخرات</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);"><?= date('Y/m/d', strtotime($borrower['created_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?= BASE_URL ?>/borrowers/edit.php?id=<?= $borrower['id'] ?>" class="btn btn-sm btn-primary" title="تعديل البيانات">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" onclick="confirmDelete('<?= BASE_URL ?>/borrowers/delete.php?id=<?= $borrower['id'] ?>', '<?= htmlspecialchars(addslashes($borrower['name'])) ?>')" class="btn btn-sm btn-danger" title="حذف">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>لا يوجد مستعيرين لعرضهم.</p>
                <?php if (empty($search)): ?>
                    <p style="margin-top: 10px;">انقر على "تسجيل مستعير جديد" للبدء.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
