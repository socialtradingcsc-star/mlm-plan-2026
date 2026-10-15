<?php
/**
 * Social Service Dream - My Sponsor Details
 * Part 4: 2x2 Binary Tree + Sponsor & Team Management
 * Target: C:\xampp\htdocs\ssdream\user\sponsor.php
 */

$pageTitle = "My Sponsor Details";
$pageHeading = "My Sponsor";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$sponsor = getUserSponsor($userId);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary text-white px-3 py-1 rounded-pill small">
                Referral Architecture
            </span>
            <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small">
                Direct Relationship
            </span>
        </div>
        <h3 class="fw-bold mb-1 text-white">My Sponsor Information</h3>
        <p class="text-white-50 small mb-0">
            Details of the partner who introduced you to the Social Service Dream business platform.
        </p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if ($sponsor): 
            $status = $sponsor['status'] ?? 'active';
            $statusBadge = match($status) {
                'active'  => 'bg-success',
                'pending' => 'bg-warning text-dark',
                default   => 'bg-danger'
            };
            $sponsorLevel = (int)($sponsor['current_level'] ?? 1);
            $pkgPrice = PLAN_PACKAGES[$sponsorLevel]['price'] ?? 1200;
        ?>
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i> Authorized Sponsor Profile
                    </span>
                    <span class="badge <?php echo $statusBadge; ?> text-capitalize px-3 py-1.5 rounded-pill">
                        Account: <?php echo e($status); ?>
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                        <div class="col-auto">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-person-circle fs-1"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="fw-bold text-dark mb-1"><?php echo e($sponsor['full_name']); ?></h4>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark border font-monospace fs-6">
                                    @<?php echo e($sponsor['username']); ?>
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                    Level <?php echo $sponsorLevel; ?> Member (<?php echo formatCurrency($pkgPrice); ?>)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sponsor Details Grid -->
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Sponsor ID</span>
                                <span class="fw-bold font-monospace text-dark fs-6">#<?php echo (int)$sponsor['id']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Package Level</span>
                                <span class="fw-bold text-primary fs-6">Level <?php echo $sponsorLevel; ?> (<?php echo e($sponsor['package_name'] ?? ('Level ' . $sponsorLevel)); ?>)</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Email Address</span>
                                <span class="fw-semibold text-dark"><?php echo e($sponsor['email']); ?></span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Mobile Contact</span>
                                <span class="fw-semibold font-monospace text-dark"><?php echo e($sponsor['mobile']); ?></span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Registration Date</span>
                                <span class="fw-semibold text-dark"><?php echo date('d F Y, h:i A', strtotime($sponsor['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Action -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="<?php echo BASE_URL; ?>/user/team.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-people me-1"></i> View My Direct Team
                        </a>
                        <a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-diagram-3 me-1"></i> View Binary Tree
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- No Sponsor Assigned (Root User) -->
            <div class="card border-0 shadow-sm text-center p-5" style="border-radius: 14px;">
                <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-shield-x fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">No Sponsor Assigned</h4>
                <p class="text-muted small max-w-md mx-auto mb-4">
                    Your account is registered as a direct root node or administrator account with no upline sponsor.
                </p>
                <div>
                    <a href="<?php echo BASE_URL; ?>/user/team.php" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-people me-1"></i> Manage My Team
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
