<?php
/**
 * Social Service Dream - User Portal Footer
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
?>
        <div class="mt-5 pt-3 border-top text-center text-muted small">
            &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Member Portal - Part 1 Foundation.
        </div>
    </div> <!-- /user-content -->
</div> <!-- /user-wrapper -->

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App Script -->
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
<!-- User Script -->
<script src="<?php echo BASE_URL; ?>/user/assets/js/user.js"></script>
</body>
</html>
