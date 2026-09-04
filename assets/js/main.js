/**
 * =====================================================
 * نظام إدارة المكتبة - ملف JavaScript الرئيسي
 * Library Management System - Main JavaScript File
 * =====================================================
 */

/**
 * تبديل عرض/إخفاء القائمة الجانبية (للشاشات الصغيرة)
 * Toggle sidebar visibility on mobile screens
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

/**
 * إغلاق القائمة الجانبية عند النقر خارجها (للشاشات الصغيرة)
 */
document.addEventListener('click', function (e) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (sidebar && toggleBtn) {
        // إذا النقر خارج القائمة وخارج زر التبديل
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

/**
 * نافذة تأكيد الحذف (Delete Confirmation Modal)
 * تعرض نافذة منبثقة قبل حذف عنصر للتأكيد
 */
function confirmDelete(url, itemName) {
    // إنشاء عناصر النافذة المنبثقة
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay show';
    overlay.innerHTML = `
        <div class="modal-box">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>تأكيد الحذف</h3>
            <p>هل أنت متأكد من حذف "${itemName}"؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="modal-actions">
                <a href="${url}" class="btn btn-danger">
                    <i class="fas fa-trash"></i> نعم، احذف
                </a>
                <button class="btn btn-secondary" onclick="closeModal(this)">
                    <i class="fas fa-times"></i> إلغاء
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // إغلاق عند النقر على الخلفية
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

/**
 * إغلاق النافذة المنبثقة
 */
function closeModal(btn) {
    const overlay = btn.closest('.modal-overlay');
    if (overlay) overlay.remove();
}

/**
 * إخفاء رسائل التنبيه تلقائياً بعد 5 ثوانٍ
 */
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 5000); // 5 ثوانٍ
    });
});

/**
 * تأثير حقول الإدخال عند التركيز (Focus Animation)
 */
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(function (input) {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('focused');
        });
    });
});
