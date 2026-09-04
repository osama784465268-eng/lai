<?php
/**
 * =====================================================
 * صفحة إضافة كتاب جديد
 * =====================================================
 */
$pageTitle = 'إضافة كتاب جديد';
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. استقبال وتنظيف البيانات
    $title = sanitize($_POST['title'] ?? '');
    $author = sanitize($_POST['author'] ?? '');
    $isbn = sanitize($_POST['isbn'] ?? '');
    $publisher = sanitize($_POST['publisher'] ?? '');
    $publish_year = sanitize($_POST['publish_year'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);
    $description = sanitize($_POST['description'] ?? '');
    
    // التحقق من الحقول الإجبارية
    if (empty($title) || empty($author)) {
        $_SESSION['error'] = 'عنوان الكتاب واسم المؤلف حقول إجبارية.';
    } elseif ($quantity < 1) {
        $_SESSION['error'] = 'يجب أن تكون الكمية 1 على الأقل.';
    } else {
        try {
            // التحقق من عدم تكرار رقم ISBN إذا كان مدخلاً
            if (!empty($isbn)) {
                $checkStmt = $pdo->prepare("SELECT id FROM books WHERE isbn = ?");
                $checkStmt->execute([$isbn]);
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception("الرقم المعياري (ISBN) المسجل يتبع لكتاب آخر.");
                }
            }

            // 2. معالجة صورة الغلاف (اختياري)
            $cover_path = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                // التأكد من أن المجلد موجود
                $uploadDir = __DIR__ . '/../assets/images/books/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $tmpName = $_FILES['cover_image']['tmp_name'];
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['cover_image']['name']));
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (in_array(mime_content_type($tmpName), $allowedTypes)) {
                    if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                        $cover_path = 'assets/images/books/' . $fileName;
                    }
                } else {
                    $_SESSION['error'] = 'صيغة الصورة غير مدعومة (فقط JPG, PNG, WEBP).';
                }
            }

            if (!isset($_SESSION['error'])) {
                // 3. الإدخال في قاعدة البيانات
                $stmt = $pdo->prepare("
                    INSERT INTO books 
                    (title, author, isbn, publisher, publish_year, category, quantity, available_quantity, description, cover_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // الكمية المتوفرة تساوي الكمية الكلية عند إضافة كتاب جديد
                $stmt->execute([
                    $title, $author, $isbn, $publisher, 
                    $publish_year ?: null, $category, 
                    $quantity, $quantity, $description, $cover_path
                ]);

                $_SESSION['success'] = 'تم إضافة الكتاب بنجاح!';
                header('Location: ' . BASE_URL . '/books/index.php');
                exit();
            }

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'حدث خطأ في قاعدة البيانات أثناء الحفظ.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-plus-circle"></i> إدخال بيانات الكتاب</h2>
        <a href="<?= BASE_URL ?>/books/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> رجوع للقائمة
        </a>
    </div>
    
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                
                <!-- البيانات الأساسية -->
                <div class="form-group full-width">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">1. المعلومات الأساسية</h3>
                </div>
                
                <div class="form-group">
                    <label for="title">عنوان الكتاب <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="author">اسم المؤلف <span class="required">*</span></label>
                    <input type="text" id="author" name="author" class="form-control" required value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="isbn">الرقم المعياري (ISBN)</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" placeholder="مثال: 978-X-XXXX-XXXX-X" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
                    <small style="color:var(--text-light)">اختياري - يفضل إضافته لمنع التكرار</small>
                </div>
                
                <div class="form-group">
                    <label for="category">التصنيف</label>
                    <input type="text" id="category" name="category" list="categories_list" class="form-control" placeholder="أدب، علوم، تاريخ..." value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
                    
                    <!-- قائمة مقترحات للتصنيفات المسجلة مسبقاً في الداتابيز -->
                    <datalist id="categories_list">
                        <?php 
                        $cats = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                        foreach($cats as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <!-- تفاصيل النشر والكمية -->
                <div class="form-group full-width" style="margin-top:20px;">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">2. النشر والمخزون</h3>
                </div>

                <div class="form-group">
                    <label for="publisher">دار النشر</label>
                    <input type="text" id="publisher" name="publisher" class="form-control" value="<?= htmlspecialchars($_POST['publisher'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="publish_year">سنة النشر</label>
                    <input type="number" id="publish_year" name="publish_year" class="form-control" min="1000" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>" value="<?= htmlspecialchars($_POST['publish_year'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="quantity">الكمية (عدد النسخ) <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control" required min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? 1) ?>">
                </div>
                
                <div class="form-group">
                    <label for="cover_image">صورة الغلاف</label>
                    <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                </div>

                <!-- تفاصيل إضافية -->
                <div class="form-group full-width" style="margin-top:20px;">
                    <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">3. تفاصيل إضافية</h3>
                </div>

                <div class="form-group full-width">
                    <label for="description">وصف الكتاب / نبذة مختصرة</label>
                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="form-group full-width" style="margin-top: 20px; text-align: left;">
                <button type="submit" class="btn btn-success" style="font-size: 16px; padding: 12px 24px;">
                    <i class="fas fa-save"></i> حفظ بيانات الكتاب
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
