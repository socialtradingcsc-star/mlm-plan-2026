<?php
/**
 * Social Service Dream - Common Footer
 */
require_once dirname(__DIR__) . '/config/config.php';
?>
</main>

<footer class="footer-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="navbar-brand-badge">
                        <i class="bi bi-shield-check me-1"></i> SS DREAM
                    </span>
                    <span class="fw-bold text-white fs-5"><?php echo APP_NAME; ?></span>
                </div>
                <p class="text-secondary small mb-3">
                    A transparent 7-level binary team structure platform built with standard business plan rules, 100% direct income, and systematic team leverage.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Binary Matrix</span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">7 Levels</span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Entry: ₹1,200</span>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-semibold mb-3">Business Plan Quick View</h6>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i><strong>Joining Package:</strong> ₹1,200 (Level 1)</li>
                    <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i><strong>Direct Income:</strong> 100%</li>
                    <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i><strong>Level Income:</strong> 50% (Level 2 - 7)</li>
                    <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i><strong>Sponsor Income:</strong> 50% (Level 2 - Unlimited)</li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-white fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/login.php">Member Login</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/admin/login.php">Admin Login</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-semibold mb-3">System & Security</h6>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2"><i class="bi bi-shield-lock text-success me-2"></i>PDO Prepared Queries</li>
                    <li class="mb-2"><i class="bi bi-key text-success me-2"></i>Bcrypt Password Hashing</li>
                    <li class="mb-2"><i class="bi bi-shield-check text-success me-2"></i>CSRF Protection Foundation</li>
                    <li class="mb-2"><i class="bi bi-server text-info me-2"></i>XAMPP / PHP 8+ Ready</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                &copy; <?php echo date('Y'); ?> <strong><?php echo APP_NAME; ?></strong>. All rights reserved. Strictly adhering to SS Dream Business Plan.
            </div>
            <div class="text-secondary small">
                Part 1 Foundation Architecture
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle with Popper JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App Script -->
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
