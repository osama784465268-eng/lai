<?php
/**
 * =====================================================
 * صفحة إدارة الكتب - عرض وبحث
 * =====================================================
 */
$pageTitle = 'إدارة الكتب';
require_once __DIR__ . '/../config.php';
requireLogin();

// إعداد متغيرات البحث والتصفية
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');

// جلب التصنيفات المتاحة لاستخدامها في فلتر البحث (dropdown)
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// بناء استعلام البحث
$query = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = 'حدث خطأ في النظام.';
    $books = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ترويسة الصفحة وعناصر التحكم المحاذية لها -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-book"></i> جميع الكتب في المكتبة</h2>
        <a href="<?= BASE_URL ?>/books/create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> إضافة كتاب جديد
        </a>
    </div>
    
    <div class="card-body">
        
        <!-- نموذج البحث والفلترة -->
        <form action="" method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="البحث بالعنوان، المؤلف، أو الرقم المعياري (ISBN)..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="category" class="form-control" style="max-width: 200px;">
                <option value="">جميع التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> بحث
            </button>
            
            <?php if (!empty($search) || !empty($category)): ?>
                <a href="<?= BASE_URL ?>/books/index.php" class="btn btn-warning">
                    <i class="fas fa-times"></i> مسح الفرز
                </a>
            <?php endif; ?>
        </form>

        <!-- جدول عرض الكتب -->
        <?php if (count($books) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الغلاف</th>
                            <th>عنوان الكتاب</th>
                            <th>المؤلف</th>
                            <th>التصنيف</th>
                            <th>الكمية المتوفرة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $book['id'] ?></td>
                            <td>
                                <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../' . $book['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>/<?= $book['cover_image'] ?>" alt="Cover" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 50px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; border-radius: 4px; color: #94a3b8;"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($book['title']) ?></strong>
                                <?php if (!empty($book['isbn'])): ?>
                                    <br><small style="color:var(--text-light)"><?= htmlspecialchars($book['isbn']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($book['category'] ?: 'غير مصنف') ?></span></td>
                            <td>
                                <?php
                                $total = $book['quantity'];
                                $avail = $book['available_quantity'];
                                $badgeClass = $avail > 0 ? 'badge-success' : 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $avail ?> من <?= $total ?></span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?= BASE_URL ?>/books/edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-primary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- يتم استخدام دالة confirmDelete من JS لإظهار نافذة تأكيد قبل الانتقال لرابط الحذف -->
                                    <button type="button" onclick="confirmDelete('<?= BASE_URL ?>/books/delete.php?id=<?= $book['id'] ?>', '<?= htmlspecialchars(addslashes($book['title'])) ?>')" class="btn btn-sm btn-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- الحالة الفارغة -->
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>لا توجد كتب مطابقة لعملية البحث المدخلة.</p>
                <?php if (empty($search) && empty($category)): ?>
                    <p style="margin-top: 10px;">انقر على "إضافة كتاب جديد" للبدء.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
