<?php
/**
 * =====================================================
 * الفوتر السفلي - Footer
 * =====================================================
 * يتم تضمينه في أسفل كل صفحة
 * يغلق عناصر HTML ويحتوي على حقوق النشر وملفات JavaScript
 */
?>
        </div> <!-- نهاية content-wrapper -->
    </main> <!-- نهاية main-content -->

    <!-- الفوتر -->
    <footer class="footer <?= isLoggedIn() ? 'with-sidebar' : '' ?>">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> - جميع الحقوق محفوظة</p>
        </div>
    </footer>

    <!-- ملف JavaScript الرئيسي -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
