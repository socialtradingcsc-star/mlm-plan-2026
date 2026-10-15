<?php
/**
 * Social Service Dream - Official Homepage
 * Technology: PHP 8+, Bootstrap 5, MySQL, PDO
 */

$pageTitle = "Welcome to Social Service Dream - Official Business Portal";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch packages (from DB or defined plan constants)
$packages = getPackages();
$dbConnected = Database::isConnected();
$dbError = Database::getError();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Database Connection Notice (Visible if DB not yet imported, guiding the developer) -->
<?php if (!$dbConnected): ?>
<div class="container mt-2">
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3">
        <i class="bi bi-database-exclamation fs-3 text-warning"></i>
        <div>
            <strong>Database Notice:</strong> MySQL connection to database <code>ssdream</code> is pending or not yet imported into phpMyAdmin.
            <div class="small text-muted">
                Homepage is operating in safe standalone mode using official plan parameters. Import <code>database/database.sql</code> via phpMyAdmin to enable dynamic records.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====================================================
     HERO SECTION
==================================================== -->
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-patch-check-fill"></i> Official Business Plan
                </div>
                <h1 class="hero-title mb-3">
                    Welcome to <span class="hero-highlight">Social Service Dream</span>
                </h1>
                <p class="hero-subtitle mb-4">
                    A structured financial empowerment model built on a disciplined <strong>7-Level Binary Team Structure</strong>. Experience 100% Direct Income and 50% Level & Sponsor rewards through sustainable team collaboration.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary-ss btn-lg px-4 shadow">
                        <i class="bi bi-rocket-takeoff me-2"></i> Join Now for <?php echo formatCurrency(JOINING_PACKAGE_AMOUNT); ?>
                    </a>
                    <a href="#packages" class="btn btn-outline-ss btn-lg px-4">
                        <i class="bi bi-layers me-2"></i> Explore 7 Levels
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 text-white-50 small pt-2">
                    <div><i class="bi bi-shield-check text-info me-1"></i> Transparent Rules</div>
                    <div><i class="bi bi-diagram-3 text-info me-1"></i> Binary Tree Structure</div>
                    <div><i class="bi bi-cash-stack text-info me-1"></i> 100% Direct Income</div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="hero-joining-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                            Level 1 Entry Package
                        </span>
                        <span class="text-white-50 small"><i class="bi bi-people-fill me-1"></i> Team: 2 Members</span>
                    </div>
                    <div class="text-white-50 text-uppercase small letter-spacing-1 mb-1">Starting Joining Package</div>
                    <div class="price-tag-big mb-3"><?php echo formatCurrency(JOINING_PACKAGE_AMOUNT); ?></div>
                    <p class="text-slate-300 small mb-4" style="color: #cbd5e1;">
                        Start your journey with Level 1 activation. Build a 2-member binary foundation and unlock direct rewards.
                    </p>
                    <ul class="list-unstyled text-white small mb-4">
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-info"></i>
                            <span><strong>Direct Income:</strong> 100% on direct sponsored partners</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-info"></i>
                            <span><strong>Binary Position:</strong> Left & Right Leg placement</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-info"></i>
                            <span><strong>Next Step:</strong> Upgrade to Level 2 (₹2,000)</span>
                        </li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary-ss w-100 py-2 fs-6">
                        Activate Account <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================
     INCOME CATEGORIES SECTION (Strict Document Data)
==================================================== -->
<section class="py-5 bg-white" id="income-plan">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
                Document Income Structure
            </span>
            <h2 class="fw-bold text-navy" style="color: #0b192c;">Official Income Categories</h2>
            <p class="text-muted">
                Carefully preserved from the official SS Dream business document. Clear, predictable, and defined.
            </p>
        </div>

        <div class="row g-4">
            <!-- 1. Direct Income -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-blue">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="badge bg-primary text-white mb-2 px-2 py-1">Category 1</div>
                    <h4 class="fw-bold mb-2">Direct Income</h4>
                    <div class="fs-2 fw-extrabold text-primary mb-2">100%</div>
                    <p class="text-muted small mb-3">
                        Earn 100% direct sponsor income for every direct member joining through your referral code.
                    </p>
                    <div class="border-top pt-2 small text-secondary">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Direct sponsor incentive
                    </div>
                </div>
            </div>

            <!-- 2. Level Income -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-cyan">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div class="badge bg-info text-dark mb-2 px-2 py-1">Category 2</div>
                    <h4 class="fw-bold mb-2">Level Income</h4>
                    <div class="fs-2 fw-extrabold text-info mb-2">50%</div>
                    <p class="text-muted small mb-3">
                        Earn 50% level income systematically distributed across <strong>Level 2 to Level 7</strong> as your binary team expands.
                    </p>
                    <div class="border-top pt-2 small text-secondary">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Applicable: Level 2 to Level 7
                    </div>
                </div>
            </div>

            <!-- 3. Sponsor Income -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-green">
                        <i class="bi bi-infinity"></i>
                    </div>
                    <div class="badge bg-success text-white mb-2 px-2 py-1">Category 3</div>
                    <h4 class="fw-bold mb-2">Sponsor Income</h4>
                    <div class="fs-2 fw-extrabold text-success mb-2">50%</div>
                    <p class="text-muted small mb-3">
                        Earn 50% sponsor income spanning from <strong>Level 2 to Unlimited Level</strong>, maximizing depth leverage.
                    </p>
                    <div class="border-top pt-2 small text-secondary">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Applicable: Level 2 to Unlimited Level
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================
     7 PACKAGE LEVELS SECTION (Strict Document Data)
==================================================== -->
<section class="py-5" id="packages" style="background-color: #f1f5f9;">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
                Preserved Plan Packages
            </span>
            <h2 class="fw-bold" style="color: #0b192c;">The 7 Package Levels & Team Sizes</h2>
            <p class="text-muted">
                Every level amount and binary team size below is taken directly from the official Social Service Dream source plan.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($packages as $pkg): ?>
            <?php 
                $lvl = (int)$pkg['level_number'];
                $isJoining = ($lvl === 1);
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="package-card <?php echo $isJoining ? 'highlighted-pkg' : ''; ?>">
                    <?php if ($isJoining): ?>
                        <span class="package-badge"><i class="bi bi-star-fill me-1"></i> Entry Package</span>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border position-absolute top-0 end-0 m-3 px-2 py-1 small">
                            Level <?php echo $lvl; ?>
                        </span>
                    <?php endif; ?>

                    <div class="package-level-title">
                        <?php echo e($pkg['package_name']); ?>
                    </div>
                    <div class="package-price">
                        <?php echo formatCurrency($pkg['price']); ?>
                    </div>

                    <ul class="package-details-list">
                        <li>
                            <span class="text-muted"><i class="bi bi-people me-1"></i> Binary Team Size:</span>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-bold"><?php echo (int)$pkg['team_size']; ?> Members</span>
                        </li>
                        <li>
                            <span class="text-muted"><i class="bi bi-award me-1"></i> Income Type:</span>
                            <span class="fw-semibold text-dark">
                                <?php if ($lvl === 1): ?>
                                    100% Direct
                                <?php else: ?>
                                    50% Level & Sponsor
                                <?php endif; ?>
                            </span>
                        </li>
                        <li>
                            <span class="text-muted"><i class="bi bi-diagram-2 me-1"></i> Structure:</span>
                            <span class="text-secondary">Binary Matrix</span>
                        </li>
                        <li>
                            <span class="text-muted"><i class="bi bi-check-circle me-1"></i> Upgrade Status:</span>
                            <span class="text-success fw-medium">Available</span>
                        </li>
                    </ul>

                    <div class="mt-auto pt-2">
                        <?php if ($isJoining): ?>
                            <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary-ss w-100 btn-sm">
                                Join with <?php echo formatCurrency($pkg['price']); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-secondary w-100 btn-sm">
                                Login to Upgrade
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====================================================
     CALL TO ACTION SECTION
==================================================== -->
<section class="py-5" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff;" id="about">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-semibold mb-3">
                    Start Today
                </span>
                <h2 class="fw-bold text-white mb-3">
                    Ready to build your team with Social Service Dream?
                </h2>
                <p class="text-slate-300 fs-6 mb-0" style="color: #cbd5e1; max-width: 650px;">
                    Activate your Level 1 package for just ₹1,200, invite 2 members in your binary team, and participate in direct, level, and sponsor income streams.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-3">
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary-ss btn-lg px-4 shadow">
                        <i class="bi bi-person-plus-fill me-1"></i> Register Now
                    </a>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-ss btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Member Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
