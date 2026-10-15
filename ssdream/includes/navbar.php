<?php
/**
 * Social Service Dream - Public Navigation Bar
 */
require_once dirname(__DIR__) . '/config/config.php';
$isUserLoggedIn = !empty($_SESSION['user_logged_in']) && !empty($_SESSION['user_id']);
$userFullName = $_SESSION['user_fullname'] ?? 'User';
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/index.php">
            <span class="navbar-brand-badge">
                <i class="bi bi-shield-check me-1"></i> SS DREAM
            </span>
            <span class="d-none d-sm-inline fw-semibold text-white fs-6"><?php echo APP_NAME; ?></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#packages">
                        <i class="bi bi-layers me-1"></i> Packages (7 Levels)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#income-plan">
                        <i class="bi bi-graph-up-arrow me-1"></i> Income Plan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#about">
                        <i class="bi bi-info-circle me-1"></i> About
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if ($isUserLoggedIn): ?>
                    <a href="<?php echo BASE_URL; ?>/user/dashboard.php" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-speedometer2 me-1"></i> Member Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-sm px-3">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-ss btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Member Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary-ss btn-sm px-3">
                        <i class="bi bi-person-plus me-1"></i> Register (₹<?php echo number_format(JOINING_PACKAGE_AMOUNT); ?>)
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>/admin/login.php" class="btn btn-link text-white-50 text-decoration-none btn-sm p-1 ms-1" title="Admin Portal">
                    <i class="bi bi-lock-fill"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
