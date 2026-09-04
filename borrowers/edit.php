<?php
/**
 * =====================================================
 * صفحة تعديل بيانات مستعير
 * =====================================================
 */
$pageTitle = 'تعديل بيانات المستعير';
require_once __DIR__ . '/../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = 'رقم المستعير غير صحيح.';
    header('Location: ' . BASE_URL . '/borrowers/index.php');
    exit();
}

// جلب بيانات المستعير الحالية
try {
    $stmt = $pdo->prepare("SELECT * FROM borrowers WHERE id = ?");
    $stmt->execute([$id]);
    $borrower = $stmt->fetch();
    
    if (!$borrower) {
        $_SESSION['error'] = 'المستعير غير موجود.';
        header('Location: ' . BASE_URL . '/borrowers/index.php');
        exit();
    }
} catch (PDOException $e) {
    die("خطأ في جلب البيانات: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $national_id = sanitize($_POST['national_id'] ?? '');
    
    if (empty($name)) {
        $_SESSION['error'] = 'اسم المستعير الرباعي مطلوب.';
    } else {
        try {
            // التحقق من تكرار الايميل أو رقم الهوية لمستعير "آخر"
            $checkTitle = [];
            $checkParams = [];
            
            if (!empty($email)) {
                $checkTitle[] = "(email = ? AND id != ?)";
                $checkParams[] = $email;
                $checkParams[] = $id;
            }
            if (!empty($national_id)) {
                $checkTitle[] = "(national_id = ? AND id != ?)";
                $checkParams[] = $national_id;
                $checkParams[] = $id;
            }
            
            if (count($checkTitle) > 0) {
                $checkQuery = "SELECT email, national_id FROM borrowers WHERE " . implode(" OR ", $checkTitle);
                $stmtCheck = $pdo->prepare($checkQuery);
                $stmtCheck->execute($checkParams);
                
                if ($stmtCheck->rowCount() > 0) {
                    $conflict = $stmtCheck->fetch();
                    if (!empty($email) && $conflict['email'] === $email) {
                        throw new Exception("البريد الإلكتروني مسجل مسبقاً لمستعير آخر.");
                    }
                    if (!empty($national_id) && $conflict['national_id'] === $national_id) {
                        throw new Exception("رقم الهوية مسجل مسبقاً لمستعير آخر في النظام.");
                    }
                }
            }
            
            // تحديث البيانات
            $updateStmt = $pdo->prepare("
                UPDATE borrowers 
                SET name = ?, email = ?, phone = ?, address = ?, national_id = ?
                WHERE id = ?
            ");
            
            $updateStmt->execute([
                $name, 
                $email ?: null, 
                $phone ?: null, 
                $address ?: null, 
                $national_id ?: null,
                $id
            ]);
            
            $_SESSION['success'] = 'تم تحديث بيانات المستعير بنجاح!';
            header('Location: ' . BASE_URL . '/borrowers/index.php');
            exit();
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'حدث خطأ في قاعدة البيانات أثناء التحديث.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2><i class="fas fa-user-edit"></i> تعديل بيانات المستعير: <?= htmlspecialchars($borrower['name']) ?></h2>
        <a href="<?= BASE_URL ?>/borrowers/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> إلغاء
        </a>
    </div>
    
    <div class="card-body">
        <form action="" method="POST">
            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label for="name">الاسم الكامل (يفضل رباعياً) <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $borrower['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="national_id">رقم الهوية الوطنية</label>
                    <input type="text" id="national_id" name="national_id" class="form-control" value="<?= htmlspecialchars($_POST['national_id'] ?? $borrower['national_id']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الجوال</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? $borrower['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $borrower['email']) ?>">
                </div>
                
                <div class="form-group full-width">
                    <label for="address">عنوان السكن</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? $borrower['address']) ?></textarea>
                </div>
                
            </div>
            
            <div class="form-group full-width" style="margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 12px 24px; width: 100%; justify-content: center;">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
