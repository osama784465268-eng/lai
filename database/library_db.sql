-- =====================================================
-- نظام إدارة المكتبة الإلكترونية - Library Management System
-- ملف إنشاء قاعدة البيانات الكامل
-- =====================================================

-- حذف قاعدة البيانات إذا كانت موجودة مسبقاً (للتطوير فقط)
DROP DATABASE IF EXISTS library_db;

-- إنشاء قاعدة البيانات مع ترميز UTF-8 لدعم اللغة العربية
CREATE DATABASE IF NOT EXISTS library_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- استخدام قاعدة البيانات
USE library_db;

-- =====================================================
-- 1. جدول المستخدمين (users) - لتسجيل دخول الأدمن
-- =====================================================
-- هذا الجدول يخزن بيانات مديري النظام (الأدمن)
-- يُستخدم لنظام تسجيل الدخول والخروج بالـ Sessions
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,                          -- معرّف فريد للمستخدم
    username VARCHAR(50) NOT NULL UNIQUE,                       -- اسم المستخدم (فريد)
    password VARCHAR(255) NOT NULL,                             -- كلمة المرور المشفرة بـ password_hash
    full_name VARCHAR(100) NOT NULL,                            -- الاسم الكامل للمستخدم
    email VARCHAR(100) UNIQUE,                                  -- البريد الإلكتروني (فريد)
    role ENUM('admin') DEFAULT 'admin',                         -- صلاحية المستخدم (أدمن فقط حالياً)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP              -- تاريخ إنشاء الحساب
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. جدول الكتب (books) - لتخزين جميع بيانات الكتب
-- =====================================================
-- هذا الجدول يخزن معلومات الكتب المتوفرة في المكتبة
-- يحتوي على الكمية الكلية والكمية المتوفرة للاستعارة
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,                          -- معرّف فريد للكتاب
    title VARCHAR(255) NOT NULL,                                -- عنوان الكتاب
    author VARCHAR(150) NOT NULL,                               -- اسم المؤلف
    isbn VARCHAR(100) UNIQUE,                                   -- الرقم المعياري الدولي للكتاب (ISBN)
    publisher VARCHAR(150),                                     -- دار النشر
    publish_year YEAR,                                          -- سنة النشر
    category VARCHAR(100),                                      -- تصنيف الكتاب (مثل: علوم، أدب، تاريخ)
    quantity INT DEFAULT 1,                                     -- عدد النسخ الكلي
    available_quantity INT DEFAULT 1,                            -- عدد النسخ المتوفرة حالياً للاستعارة
    description TEXT,                                           -- وصف مختصر للكتاب
    cover_image VARCHAR(255),                                   -- مسار صورة غلاف الكتاب
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP              -- تاريخ إضافة الكتاب للنظام
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. جدول المستعيرين (borrowers) - بيانات الأشخاص المستعيرين
-- =====================================================
-- هذا الجدول يخزن بيانات الأشخاص الذين يستعيرون الكتب
-- كل مستعير له رقم هوية فريد ومعلومات تواصل
CREATE TABLE borrowers (
    id INT AUTO_INCREMENT PRIMARY KEY,                          -- معرّف فريد للمستعير
    name VARCHAR(100) NOT NULL,                                 -- اسم المستعير الكامل
    email VARCHAR(100) UNIQUE,                                  -- البريد الإلكتروني (فريد)
    phone VARCHAR(20),                                          -- رقم الهاتف
    address TEXT,                                               -- عنوان السكن
    national_id VARCHAR(20) UNIQUE,                             -- رقم الهوية الوطنية (فريد)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP              -- تاريخ تسجيل المستعير
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. جدول عمليات الاستعارة (borrowings) - ربط الكتب بالمستعيرين
-- =====================================================
-- هذا الجدول يسجل جميع عمليات الاستعارة والإرجاع
-- يربط بين جدول الكتب وجدول المستعيرين (علاقة كثير لكثير)
-- العلاقة: كتاب واحد ← عدة عمليات استعارة
-- العلاقة: مستعير واحد ← عدة عمليات استعارة
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,                          -- معرّف فريد لعملية الاستعارة
    book_id INT NOT NULL,                                       -- معرّف الكتاب المستعار (مفتاح أجنبي)
    borrower_id INT NOT NULL,                                   -- معرّف المستعير (مفتاح أجنبي)
    borrow_date DATE NOT NULL,                                  -- تاريخ بدء الاستعارة
    due_date DATE NOT NULL,                                     -- تاريخ الإرجاع المتوقع
    return_date DATE DEFAULT NULL,                              -- تاريخ الإرجاع الفعلي (NULL = لم يُرجع بعد)
    status ENUM('borrowed', 'returned') DEFAULT 'borrowed',     -- حالة الاستعارة
    notes TEXT,                                                 -- ملاحظات إضافية
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,             -- تاريخ تسجيل العملية

    -- =====================================================
    -- المفاتيح الأجنبية (Foreign Keys) - لضمان سلامة البيانات
    -- =====================================================
    -- ON DELETE CASCADE: عند حذف كتاب أو مستعير، تُحذف عمليات الاستعارة المرتبطة تلقائياً
    -- ON UPDATE CASCADE: عند تحديث معرّف كتاب أو مستعير، يتم التحديث تلقائياً
    CONSTRAINT fk_borrowings_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_borrowings_borrower
        FOREIGN KEY (borrower_id) REFERENCES borrowers(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. جدول رسائل التواصل (contact_messages) - لصفحة اتصل بنا
-- =====================================================
-- هذا الجدول يخزن الرسائل المرسلة من صفحة التواصل
-- جدول مستقل بدون علاقات مع جداول أخرى
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,                          -- معرّف فريد للرسالة
    name VARCHAR(100) NOT NULL,                                 -- اسم المرسل
    email VARCHAR(100) NOT NULL,                                -- بريد المرسل الإلكتروني
    subject VARCHAR(200),                                       -- موضوع الرسالة
    message TEXT NOT NULL,                                      -- نص الرسالة
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP              -- تاريخ الإرسال
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- إنشاء الفهارس (Indexes) - لتحسين أداء البحث
-- =====================================================
-- ملاحظة: نستخدم طول محدد للفهرس لتجنب تجاوز حد 767 بايت مع utf8mb4
-- فهرس على عنوان الكتاب والمؤلف لتسريع عمليات البحث
CREATE INDEX idx_books_title ON books(title(100));
CREATE INDEX idx_books_author ON books(author(100));
CREATE INDEX idx_books_category ON books(category(50));

-- فهرس على اسم المستعير لتسريع البحث
CREATE INDEX idx_borrowers_name ON borrowers(name(50));

-- فهرس على حالة الاستعارة لتسريع عرض الكتب المستعارة/المرجعة
CREATE INDEX idx_borrowings_status ON borrowings(status);
CREATE INDEX idx_borrowings_borrow_date ON borrowings(borrow_date);

-- =====================================================
-- إدراج بيانات تجريبية - مستخدم أدمن افتراضي
-- =====================================================
-- كلمة المرور: 1233 (مشفرة بـ password_hash في PHP)
-- تم توليد الهاش باستخدام: password_hash('1233', PASSWORD_BCRYPT)
INSERT INTO users (username, password, full_name, email, role) VALUES
('brns', '$2y$10$rWbBomfPu2RWJZXZdcGCQ.djxGQhFw4YCu.Ac7bkqj4L9lvMfsffK', 'مدير النظام', 'brns@library.com', 'admin');

-- =====================================================
-- إدراج بيانات تجريبية - كتب نموذجية
-- =====================================================
INSERT INTO books (title, author, isbn, publisher, publish_year, category, quantity, available_quantity, description) VALUES
('مقدمة ابن خلدون', 'عبد الرحمن بن خلدون', '978-1-234-56789-0', 'دار الكتب العلمية', 2010, 'تاريخ', 3, 3, 'كتاب تاريخي شهير يتناول فلسفة التاريخ والعمران البشري'),
('الأيام', 'طه حسين', '978-1-234-56789-1', 'دار المعارف', 2015, 'أدب', 2, 2, 'سيرة ذاتية للأديب الكبير طه حسين عميد الأدب العربي'),
('في ظلال القرآن', 'سيد قطب', '978-1-234-56789-2', 'دار الشروق', 2008, 'دين', 4, 4, 'تفسير شامل للقرآن الكريم بأسلوب أدبي معاصر'),
('ألف ليلة وليلة', 'مؤلف مجهول', '978-1-234-56789-3', 'دار صادر', 2012, 'أدب', 2, 2, 'مجموعة قصصية كلاسيكية من التراث العربي والشرقي'),
('تعلم البرمجة بلغة PHP', 'أحمد محمد علي', '978-1-234-56789-4', 'دار التقنية', 2022, 'تقنية', 5, 5, 'كتاب تعليمي شامل للمبتدئين في لغة PHP'),
('مبادئ قواعد البيانات', 'محمد سعيد', '978-1-234-56789-5', 'دار العلوم', 2020, 'تقنية', 3, 3, 'كتاب أكاديمي يشرح أساسيات قواعد البيانات وSQL'),
('رياضيات التحليل', 'خالد العبدالله', '978-1-234-56789-6', 'مكتبة الجامعة', 2018, 'علوم', 2, 2, 'مرجع أكاديمي في التحليل الرياضي للمرحلة الجامعية'),
('تاريخ العرب', 'فيليب حتّي', '978-1-234-56789-7', 'دار الكشاف', 2011, 'تاريخ', 3, 3, 'موسوعة تاريخية شاملة عن تاريخ العرب من الجاهلية حتى العصر الحديث');

-- =====================================================
-- إدراج بيانات تجريبية - مستعيرين نموذجيين
-- =====================================================
INSERT INTO borrowers (name, email, phone, address, national_id) VALUES
('أحمد محمد العلي', 'ahmed@email.com', '0501234567', 'الرياض - حي النزهة', '1234567890'),
('فاطمة خالد السعيد', 'fatima@email.com', '0559876543', 'جدة - حي الصفا', '0987654321'),
('عمر حسن الشمري', 'omar@email.com', '0551112233', 'الدمام - حي الفيصلية', '1122334455');

-- =====================================================
-- إدراج بيانات تجريبية - عمليات استعارة نموذجية
-- =====================================================
INSERT INTO borrowings (book_id, borrower_id, borrow_date, due_date, return_date, status, notes) VALUES
(1, 1, '2026-07-01', '2026-07-15', NULL, 'borrowed', 'استعارة لأغراض البحث'),
(5, 2, '2026-07-10', '2026-07-24', NULL, 'borrowed', 'للدراسة الذاتية'),
(2, 3, '2026-06-20', '2026-07-04', '2026-07-03', 'returned', 'تم الإرجاع في الموعد');

-- =====================================================
-- تحديث الكميات المتوفرة بناءً على الاستعارات النشطة
-- =====================================================
-- الكتاب رقم 1 (مقدمة ابن خلدون): نسخة واحدة مستعارة
UPDATE books SET available_quantity = available_quantity - 1 WHERE id = 1;
-- الكتاب رقم 5 (تعلم البرمجة): نسخة واحدة مستعارة
UPDATE books SET available_quantity = available_quantity - 1 WHERE id = 5;

-- =====================================================
-- ملخص العلاقات بين الجداول:
-- =====================================================
-- 1. users: جدول مستقل - لتسجيل دخول الأدمن
-- 2. books: جدول رئيسي - يحتوي على جميع الكتب
-- 3. borrowers: جدول رئيسي - يحتوي على جميع المستعيرين
-- 4. borrowings: جدول وسيط - يربط books و borrowers
--    - book_id → يشير إلى books(id)
--    - borrower_id → يشير إلى borrowers(id)
-- 5. contact_messages: جدول مستقل - لرسائل صفحة التواصل
--
-- العلاقات:
-- books (1) ←→ (∞) borrowings: كتاب واحد يمكن استعارته عدة مرات
-- borrowers (1) ←→ (∞) borrowings: مستعير واحد يمكنه استعارة عدة كتب
-- =====================================================
