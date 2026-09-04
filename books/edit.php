<?php
/**
 * =====================================================
 * صفحة تعديل كتاب
 * =====================================================
 */
$pageTitle = 'تعديل بيانات الكتاب';
require_once __DIR__ . '/../config.php';
requireLogin();

// التحقق من وجود رقم تعريفي (ID) صحيح
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = 'رقم غير صحيح.';
    header('Location: ' . BASE_URL . '/books/index.php');
    exit();
}

// جلب بيانات الكتاب الحالية
try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['error'] = 'الكتاب غير موجود.';
        header('Location: ' . BASE_URL . '/books/index.php');
        exit();
    }
} catch (PDOException $e) {
    die("خطأ في جلب البيانات: " . $e->getMessage());
}

// معالجة نموذج التعديل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $author = sanitize($_POST['author'] ?? '');
    $isbn = sanitize($_POST['isbn'] ?? '');
    $publisher = sanitize($_POST['publisher'] ?? '');
    $publish_year = sanitize($_POST['publish_year'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $new_quantity = (int)($_POST['quantity'] ?? 1);
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($title) || empty($author)) {
        $_SESSION['error'] = 'عنوان الكتاب واسم المؤلف حقول إجبارية.';
    } elseif ($new_quantity < 1) {
        $_SESSION['error'] = 'يجب أن تكون الكمية 1 على الأقل.';
    } else {
        try {
            // التحقق من ISBN
            if (!empty($isbn)) {
                $checkStmt = $pdo->prepare("SELECT id FROM books WHERE isbn = ? AND id != ?");
                $checkStmt->execute([$isbn, $id]);
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception("الرقم المعياري (ISBN) المسجل يتبع لكتاب آخر.");
                }
            }

            // حساب الكمية المتوفرة الجديدة
            // المعادلة: الكمية المتوفرة الجديدة = الكمية المتوفرة القديمة + (الكمية الكلية الجديدة - الكمية الكلية القديمة)
            $quantity_diff = $new_quantity - $book['quantity'];
            $new_available = $book['available_quantity'] + $quantity_diff;
            
            // التأكد من أن الكمية الإجمالية لا تقل عن الكتب المستعارة
            if ($new_available < 0) {
                throw new Exception("لا يمكن تقليل الكمية الكلية إلى هذا الحد لأن هناك نسخاً مستعارة بالفعل.");
            }

            // معالجة تغيير الصورة
            $cover_path = $book['cover_image']; // الاحتفاظ بالصورة القديمة افتراضياً
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/images/books/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $tmpName = $_FILES['cover_image']['tmp_name'];
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['cover_image']['name']));
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (in_array(mime_content_type($tmpName), $allowedTypes)) {
                    if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                        // الحذف الآمن للصورة القديمة
                        if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../' . $book['cover_image'])) {
                            unlink(__DIR__ . '/../' . $book['cover_image']);
                        }
                        $cover_path = 'assets/images/books/' . $fileName;
                    }
                } else {
                    throw new Exception('صيغة الصورة غير مدعومة (فقط JPG, PNG, WEBP).');
                }
            }

            // تحديث قاعدة البيانات
            $updateStmt = $pdo->prepare("
                UPDATE books 
                SET title=?, author=?, isbn=?, publisher=?, publish_year=?, 
                    category=?, quantity=?, available_quantity=?, description=?, cover_image=? 
                WHERE id=?
            ");
            
            $updateStmt->execute([
                $title, $author, $isbn, $publisher, 
                $publish_year ?: null, $category, 
                $new_quantity, $new_available, $description, $cover_path,
                $id
            ]);

            $_SESSION['success'] = 'تم تحديث بيانات الكتاب بنجاح.';
            header('Location: ' . BASE_URL . '/books/index.php');
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-edit"></i> تعديل الكتاب: <?= htmlspecialchars($book['title']) ?></h2>
        <a href="<?= BASE_URL ?>/books/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> إالغاء
        </a>
    </div>
    
    <div class="card-body">
        <!-- تمرير البيانات الحالية في الحقول (value) -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                
                <div class="form-group full-width">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">1. المعلومات الأساسية</h3>
                </div>
                
                <div class="form-group">
                    <label for="title">عنوان الكتاب <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? $book['title']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="author">اسم المؤلف <span class="required">*</span></label>
                    <input type="text" id="author" name="author" class="form-control" required value="<?= htmlspecialchars($_POST['author'] ?? $book['author']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="isbn">الرقم المعياري (ISBN)</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? $book['isbn']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">التصنيف</label>
                    <input type="text" id="category" name="category" class="form-control" value="<?= htmlspecialchars($_POST['category'] ?? $book['category']) ?>">
                </div>

                <div class="form-group full-width" style="margin-top:20px;">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">2. النشر والمخزون</h3>
                </div>

                <div class="form-group">
                    <label for="publisher">دار النشر</label>
                    <input type="text" id="publisher" name="publisher" class="form-control" value="<?= htmlspecialchars($_POST['publisher'] ?? $book['publisher']) ?>">
                </div>

                <div class="form-group">
                    <label for="publish_year">سنة النشر</label>
                    <input type="number" id="publish_year" name="publish_year" class="form-control" min="1000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($_POST['publish_year'] ?? $book['publish_year']) ?>">
                </div>

                <div class="form-group">
                    <label for="quantity">الكمية (عدد النسخ) <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control" required min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? $book['quantity']) ?>">
                    <small style="color:var(--text-secondary)">النسخ المتوفرة حالياً: <?= $book['available_quantity'] ?> / <?= $book['quantity'] ?></small>
                </div>
                
                <div class="form-group">
                    <label>صورة الغلاف الحالية</label>
                    <div style="margin-bottom: 10px;">
                    <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../' . $book['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>/<?= $book['cover_image'] ?>" alt="Cover" style="height: 100px; border-radius: 4px; box-shadow: var(--shadow);">
                    <?php else: ?>
                        <span style="color:var(--text-light)">لا توجد صورة</span>
                    <?php endif; ?>
                    </div>
                    <label for="cover_image">تغيير الصورة (اتركه فارغاً للاحتفاظ بالحالية)</label>
                    <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                </div>

                <div class="form-group full-width" style="margin-top:20px;">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">3. تفاصيل إضافية</h3>
                </div>

                <div class="form-group full-width">
                    <label for="description">وصف الكتاب / نبذة مختصرة</label>
                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? $book['description']) ?></textarea>
                </div>
            </div>
            
            <div class="form-group full-width" style="margin-top: 20px; text-align: left;">
                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 12px 24px;">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
