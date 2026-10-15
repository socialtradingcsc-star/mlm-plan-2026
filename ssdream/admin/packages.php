<?php
/**
 * Social Service Dream - Admin Package & Level Architecture
 * Part 3: Package + Level + Upgrade System
 * Target: C:\xampp\htdocs\ssdream\admin\packages.php
 */

$pageTitle = "Packages & Level Structure";
$pageHeading = "Packages Architecture";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$packages = getPackages();
$distribution = getLevelDistributionStats();
$pendingUpgrades = getPendingUpgradeCount();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Level Structure & Integrity Banner -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill small">
                        <i class="bi bi-shield-lock-fill me-1"></i> Core Plan Data: Locked
                    </span>
                    <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small">
                        7 Official Packages
                    </span>
                    <span class="badge bg-primary px-3 py-1 rounded-pill small">
                        Binary Matrix Structure
                    </span>
                </div>

                <h3 class="fw-bold mb-1 text-white">Official SS Dream Package Hierarchy</h3>
                <p class="text-white-50 small mb-0">
                    The package amounts, team sizes, and level parameters are permanently locked to preserve exact alignment with the Social Service Dream document.
                    Level progression is sequential; level-skipping and arbitrary package creation are prohibited.
                </p>
            </div>

            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo BASE_URL; ?>/admin/upgrades.php" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm rounded-pill">
                    <i class="bi bi-arrow-up-square-fill me-1"></i> View Upgrade Requests
                    <?php if ($pendingUpgrades > 0): ?>
                        <span class="badge bg-danger ms-1"><?php echo $pendingUpgrades; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Flash Notifications -->
<?php echo renderFlashMessages(); ?>

<!-- Packages Architecture Table -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-layers text-primary me-2"></i> Official 7-Level Package Structure
            </h5>
            <div class="text-muted small">Strictly preserved figures and requirements from business-plan document</div>
        </div>
        <span class="badge bg-light text-dark border px-3 py-1">
            Status: Active &amp; Enforced
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4">Level</th>
                        <th>Package Name</th>
                        <th>Official Amount</th>
                        <th>Team Required</th>
                        <th>Direct Income</th>
                        <th>Level Income</th>
                        <th>Sponsor Income</th>
                        <th>Active Members</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $pkg): 
                        $lvl = (int)($pkg['level_no'] ?? $pkg['level_number']);
                        $amount = (float)($pkg['amount'] ?? $pkg['price']);
                        $team = (int)$pkg['team_size'];
                        $userCount = $distribution[$lvl]['user_count'] ?? 0;
                    ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-primary px-3 py-2 rounded-circle fs-6">
                                    <?php echo $lvl; ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark fs-6">
                                <?php echo e($pkg['package_name']); ?>
                            </td>
                            <td>
                                <span class="fs-5 fw-bold text-primary">
                                    <?php echo formatCurrency($amount); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                                    <i class="bi bi-people-fill text-muted me-1"></i> <?php echo $team; ?> Members
                                </span>
                            </td>
                            <td>
                                <?php if ($lvl === 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        100%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lvl >= 2): ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        50%
                                    </span>
                                    <div class="text-muted text-xxs mt-1">L2 to L7</div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lvl >= 2): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        50%
                                    </span>
                                    <div class="text-muted text-xxs mt-1">L2 to Unlimited</div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark fs-6"><?php echo $userCount; ?></span>
                                    <span class="text-muted small">members</span>
                                </div>
                            </td>
                            <td class="pe-4 text-center">
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill small">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Plan Integrity and Database Protection Card -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-shield-check text-success me-2"></i> Database Constraints &amp; Validation
                </h5>
                <ul class="list-unstyled space-y-3 mb-0 small text-muted">
                    <li class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <div>
                            <strong class="text-dark">Strict Level Boundary (1 to 7):</strong>
                            <div>The database enforces a CHECK constraint (`level_number BETWEEN 1 AND 7`). No Level 8 or arbitrary level can be injected.</div>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <div>
                            <strong class="text-dark">Sequential Upgrade Integrity:</strong>
                            <div>Members can only upgrade to `current_level + 1`. Skipping from Level 1 directly to Level 3 or 4 is rejected at both the application and database level.</div>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <div>
                            <strong class="text-dark">Server-Side Price Authority:</strong>
                            <div>Package upgrade amounts are retrieved strictly from server-side database records, ignoring any tampered POST payloads.</div>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check2-circle text-success fs-5"></i>
                        <div>
                            <strong class="text-dark">Strict Income Engine Isolation:</strong>
                            <div>In adherence with Part 3 instructions, no income calculations are executed during level upgrade processing. The income engine will be integrated in subsequent parts.</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-pie-chart text-info me-2"></i> Member Level Distribution
                </h5>
                <div class="space-y-3">
                    <?php 
                    $totalMembers = array_sum(array_column($distribution, 'user_count'));
                    foreach ($distribution as $lvl => $d): 
                        $pct = ($totalMembers > 0) ? round(($d['user_count'] / $totalMembers) * 100, 1) : 0;
                    ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold text-dark">Level <?php echo $lvl; ?> (<?php echo formatCurrency($d['price']); ?>)</span>
                                <span class="text-muted"><?php echo $d['user_count']; ?> members (<?php echo $pct; ?>%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo max($pct, 2); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
