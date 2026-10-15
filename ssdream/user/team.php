<?php
/**
 * Social Service Dream - Member Team Management
 * Part 4: 2x2 Binary Tree + Sponsor & Team Management
 * Target: C:\xampp\htdocs\ssdream\user\team.php
 */

$pageTitle = "My Team Management";
$pageHeading = "My Team";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';

// Compute real dynamic team counts from database
$currentLevel = getUserCurrentLevel($userId);
$directTeam = getDirectTeam($userId);
$directCount = count($directTeam);
$activeDirectCount = getActiveDirectTeamCount($userId);

$binaryDownline = getBinaryDownline($userId, 15);
$binaryCount = count($binaryDownline);
$activeBinaryCount = 0;
foreach ($binaryDownline as $member) {
    if (($member['status'] ?? '') === 'active') {
        $activeBinaryCount++;
    }
}

// Active team total (combined active members in downline)
$totalActiveTeam = $activeBinaryCount;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Team Overview Top Banner -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill small">
                        <i class="bi bi-shield-check me-1"></i> Network Active
                    </span>
                    <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small">
                        Active Level: Level <?php echo $currentLevel; ?>
                    </span>
                    <span class="badge bg-primary px-3 py-1 rounded-pill small">
                        2&times;2 Binary Architecture
                    </span>
                </div>

                <h3 class="fw-bold mb-1 text-white">My Team &amp; Network Management</h3>
                <p class="text-white-50 small mb-0">
                    Track your directly sponsored partners and complete 2&times;2 binary matrix downline team.
                    Real-time metrics computed directly from database records.
                </p>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="btn btn-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-diagram-3-fill me-1"></i> View Binary Tree &rarr;
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/sponsor.php" class="btn btn-outline-light px-3 py-2 rounded-pill">
                        <i class="bi bi-person-badge me-1"></i> My Sponsor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 1: THE 4 DYNAMIC TEAM METRICS (Calculated)
==================================================== -->
<div class="row g-3 mb-4">
    <!-- 1. Direct Team -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Direct Team</span>
                <div class="stat-icon icon-blue">
                    <i class="bi bi-people text-primary"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo $directCount; ?></h3>
            <span class="text-primary small d-flex align-items-center gap-1">
                <i class="bi bi-person-check-fill"></i> <?php echo $activeDirectCount; ?> Active Sponsors
            </span>
        </div>
    </div>

    <!-- 2. Binary Team -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Binary Team</span>
                <div class="stat-icon icon-cyan">
                    <i class="bi bi-diagram-3-fill text-info"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo $binaryCount; ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-layers"></i> In 2&times;2 downline matrix
            </span>
        </div>
    </div>

    <!-- 3. Active Team -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Active Team</span>
                <div class="stat-icon icon-green">
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo $totalActiveTeam; ?></h3>
            <span class="text-success small d-flex align-items-center gap-1">
                <i class="bi bi-patch-check"></i> Activated members
            </span>
        </div>
    </div>

    <!-- 4. Current Level -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Current Level</span>
                <div class="stat-icon icon-amber">
                    <i class="bi bi-award-fill text-warning"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-primary">Level <?php echo $currentLevel; ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-box"></i> <?php echo formatCurrency(PLAN_PACKAGES[$currentLevel]['price'] ?? 1200); ?> Package
            </span>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 2: DIRECT SPONSOR TEAM
==================================================== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-people-fill text-primary me-2"></i> Direct Sponsor Team
            </h5>
            <div class="text-muted small">Partners who registered directly using your personal Sponsor Link or ID</div>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
            Total Directs: <?php echo $directCount; ?>
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Join Date</th>
                        <th>Current Level</th>
                        <th>Binary Position</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($directTeam)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-person-plus fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                You have not sponsored any members yet. Share your personal referral link to invite partners!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($directTeam as $member): 
                            $status = $member['status'] ?? 'active';
                            $statusBadge = match($status) {
                                'active'  => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                default   => 'bg-danger'
                            };
                        ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?php echo $idx++; ?></td>
                                <td class="fw-bold text-dark">
                                    <?php echo e($member['full_name']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        @<?php echo e($member['username']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted font-monospace">
                                    <?php echo e($member['mobile']); ?>
                                </td>
                                <td class="small text-muted">
                                    <?php echo date('d M Y', strtotime($member['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        Level <?php echo (int)$member['current_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($member['position'])): ?>
                                        <span class="badge <?php echo ($member['position'] === 'left') ? 'bg-primary' : 'bg-indigo text-white'; ?> text-uppercase">
                                            <?php echo e($member['position']); ?>
                                        </span>
                                        <?php if (!empty($member['parent_username'])): ?>
                                            <span class="text-muted text-xxs d-block">under @<?php echo e($member['parent_username']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <span class="badge <?php echo $statusBadge; ?> text-capitalize px-2.5 py-1 rounded-pill">
                                        <?php echo e($status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 3: BINARY TEAM (Full Downline Network)
==================================================== -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-diagram-3 text-info me-2"></i> Binary Downline Team
            </h5>
            <div class="text-muted small">All members positioned in your 2&times;2 matrix downline (Levels 1 to 7+)</div>
        </div>
        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1">
            Total Downline: <?php echo $binaryCount; ?>
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4">Depth</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Package Level</th>
                        <th>Position</th>
                        <th>Parent Node</th>
                        <th>Sponsor</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($binaryDownline)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-diagram-3 fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No downline members in your binary matrix yet. New partners will be positioned under your left or right leg.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($binaryDownline as $bMember): 
                            $bStatus = $bMember['status'] ?? 'active';
                            $bStatusBadge = match($bStatus) {
                                'active'  => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                default   => 'bg-danger'
                            };
                            $posClass = ($bMember['position'] === 'left') ? 'bg-primary' : 'bg-info text-dark';
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-secondary bg-opacity-25 text-dark fw-bold">
                                        Tier <?php echo (int)($bMember['tree_level'] ?? 1); ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?php echo e($bMember['full_name']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        @<?php echo e($bMember['username']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        Level <?php echo (int)$bMember['current_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $posClass; ?> text-uppercase">
                                        <?php echo e($bMember['position'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="small text-muted font-monospace">
                                    @<?php echo e($bMember['parent_username'] ?? 'Root'); ?>
                                </td>
                                <td class="small text-muted">
                                    <?php if (!empty($bMember['sponsor_username'])): ?>
                                        <span class="badge bg-light text-dark border">
                                            @<?php echo e($bMember['sponsor_username']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <span class="badge <?php echo $bStatusBadge; ?> text-capitalize px-2.5 py-1 rounded-pill">
                                        <?php echo e($bStatus); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
