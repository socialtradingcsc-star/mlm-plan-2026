<?php
/**
 * Social Service Dream - Member Packages & Level Upgrade
 * Part 3: Complete Package, Level & Upgrade System
 * Target: C:\xampp\htdocs\ssdream\user\packages.php
 */

$pageTitle = "Packages & Level Upgrade";
$pageHeading = "Packages & Upgrade";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';

// Handle Upgrade Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_upgrade') {
    // 1. Verify CSRF Token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', 'Security session expired. Please refresh the page and try again.');
        header('Location: ' . BASE_URL . '/user/packages.php');
        exit;
    }

    $targetLevel = (int)($_POST['target_level'] ?? 0);
    $paymentMode = sanitizeInput($_POST['payment_mode'] ?? 'Wallet Balance');
    $txRef = sanitizeInput($_POST['transaction_ref'] ?? '');

    // Submit upgrade request with server-side validation
    $result = createUpgradeRequest($userId, $targetLevel, $paymentMode, $txRef);

    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }

    header('Location: ' . BASE_URL . '/user/packages.php');
    exit;
}

// Fetch user data
$currentLevel = getUserCurrentLevel($userId);
$currentPkg = getPackageByLevel($currentLevel);
$nextLevel = ($currentLevel < 7) ? $currentLevel + 1 : null;
$nextPkg = $nextLevel ? getPackageByLevel($nextLevel) : null;
$wallet = getUserWallet($userId);
$walletBalance = $wallet ? (float)$wallet['balance'] : 0.00;
$pendingUpgrade = getUserPendingUpgrade($userId);
$upgradeHistory = getUserUpgradeHistory($userId);
$packages = getPackages();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Level Status Overview Banner -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                        <i class="bi bi-patch-check-fill me-1"></i> Account Active
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill">
                        Active Level: Level <?php echo $currentLevel; ?>
                    </span>
                    <?php if ($pendingUpgrade): ?>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                            <i class="bi bi-hourglass-split me-1"></i> Upgrade to Level <?php echo (int)$pendingUpgrade['to_level']; ?> Pending Approval
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-1 text-white">Packages & Progression Levels</h3>
                <p class="text-white-50 small mb-0">
                    Official 7-Level binary matrix structure strictly based on Social Service Dream business plan.
                    Advance through sequential packages to unlock higher level and sponsor earning privileges.
                </p>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="p-3 bg-white bg-opacity-10 rounded-3 text-center d-inline-block border border-white border-opacity-10 w-100" style="max-width: 300px;">
                    <div class="small text-white-50 text-uppercase letter-spacing-1">Current Active Package</div>
                    <div class="fs-3 fw-bold text-info mb-0">Level <?php echo $currentLevel; ?></div>
                    <div class="text-warning fw-semibold"><?php echo formatCurrency($currentPkg['amount'] ?? $currentPkg['price']); ?></div>
                    <div class="small text-white-50 mt-1">Team Required: <?php echo (int)($currentPkg['team_size'] ?? 2); ?> Members</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Upgrade Notice (if any) -->
<?php if ($pendingUpgrade): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 p-3 mb-4 rounded-3" role="alert">
        <i class="bi bi-clock-history fs-3 text-warning"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading fw-bold mb-1">Upgrade Request Pending Review</h5>
            <p class="mb-2 small">
                You have submitted a request to upgrade to <strong>Level <?php echo (int)$pendingUpgrade['to_level']; ?></strong> 
                (Amount: <strong><?php echo formatCurrency($pendingUpgrade['amount']); ?></strong>) on 
                <strong><?php echo date('M d, Y h:i A', strtotime($pendingUpgrade['created_at'])); ?></strong>.
                <?php if (!empty($pendingUpgrade['payment_mode'])): ?>
                    <br>Payment Method: <span class="badge bg-light text-dark"><?php echo e($pendingUpgrade['payment_mode']); ?></span>
                <?php endif; ?>
                <?php if (!empty($pendingUpgrade['transaction_ref'])): ?>
                    Reference / UTR: <span class="font-monospace fw-semibold"><?php echo e($pendingUpgrade['transaction_ref']); ?></span>
                <?php endif; ?>
            </p>
            <div class="small text-muted">
                <i class="bi bi-shield-check text-success me-1"></i> Our administrative team is reviewing your verification details. Your level will automatically update once approved.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Flash Message Notifications -->
<?php echo renderFlashMessages(); ?>

<!-- ====================================================
     SECTION 1: THE 7 OFFICIAL PACKAGES
==================================================== -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0b192c;">Official Packages (Levels 1 to 7)</h4>
            <p class="text-muted small mb-0">Preserved strictly from the SS Dream business-plan document.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                <i class="bi bi-wallet2 me-1"></i> Available Balance: <strong><?php echo formatCurrency($walletBalance, true); ?></strong>
            </span>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($packages as $pkg): 
            $lvl = (int)($pkg['level_no'] ?? $pkg['level_number']);
            $price = (float)($pkg['amount'] ?? $pkg['price']);
            $team = (int)$pkg['team_size'];
            $isCurrent = ($lvl === $currentLevel);
            $isCompleted = ($lvl < $currentLevel);
            $isNext = ($lvl === $nextLevel);
            $isLocked = ($lvl > $currentLevel && !$isNext);
        ?>
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm transition-hover position-relative" 
                     style="border-radius: 12px; <?php echo $isCurrent ? 'border: 2px solid #0066cc !important; background-color: #f0f7ff;' : ($isNext ? 'border: 2px dashed #00b4d8 !important;' : ''); ?>">
                    
                    <!-- Top Ribbon / Status Tag -->
                    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <span class="badge <?php 
                            if ($isCurrent) echo 'bg-primary text-white';
                            elseif ($isCompleted) echo 'bg-success text-white';
                            elseif ($isNext) echo 'bg-info text-dark';
                            else echo 'bg-secondary bg-opacity-25 text-secondary';
                        ?> px-3 py-1 rounded-pill small">
                            <?php 
                                if ($isCurrent) echo '<i class="bi bi-check-circle-fill me-1"></i> Current Active';
                                elseif ($isCompleted) echo '<i class="bi bi-patch-check-fill me-1"></i> Achieved';
                                elseif ($isNext) echo '<i class="bi bi-arrow-up-circle-fill me-1"></i> Next Eligible';
                                else echo '<i class="bi bi-lock-fill me-1"></i> Locked';
                            ?>
                        </span>
                        <span class="small text-muted fw-semibold">Level <?php echo $lvl; ?> of 7</span>
                    </div>

                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-1" style="color: #0b192c;"><?php echo e($pkg['package_name']); ?></h4>
                        <div class="d-flex align-items-baseline gap-1 my-3">
                            <span class="fs-2 fw-bold" style="color: #0066cc;"><?php echo formatCurrency($price); ?></span>
                            <span class="text-muted small">One-time Upgrade</span>
                        </div>

                        <!-- Core Document Plan Specifications -->
                        <ul class="list-unstyled small mb-4 space-y-2">
                            <li class="d-flex align-items-center gap-2 mb-2 text-dark">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span>Binary Team Required: <strong><?php echo $team; ?> Members</strong></span>
                            </li>
                            <?php if ($lvl === 1): ?>
                                <li class="d-flex align-items-center gap-2 mb-2 text-dark">
                                    <i class="bi bi-stars text-warning"></i>
                                    <span>Direct Income: <strong class="text-success">100%</strong></span>
                                </li>
                                <li class="d-flex align-items-center gap-2 text-muted">
                                    <i class="bi bi-dash-circle"></i>
                                    <span>Level & Sponsor Income: Begins at Level 2</span>
                                </li>
                            <?php else: ?>
                                <li class="d-flex align-items-center gap-2 mb-2 text-dark">
                                    <i class="bi bi-diagram-3-fill text-info"></i>
                                    <span>Level Income: <strong class="text-primary">50%</strong> (Level 2 to Level 7)</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 text-dark">
                                    <i class="bi bi-graph-up-arrow text-success"></i>
                                    <span>Sponsor Income: <strong class="text-success">50%</strong> (Level 2 to Unlimited)</span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <!-- Action Button Based on State -->
                        <div class="mt-auto pt-2 border-top">
                            <?php if ($isCurrent): ?>
                                <button type="button" class="btn btn-primary w-100 disabled" style="border-radius: 8px;">
                                    <i class="bi bi-check2-circle me-1"></i> Active Package
                                </button>
                            <?php elseif ($isCompleted): ?>
                                <button type="button" class="btn btn-outline-success w-100 disabled" style="border-radius: 8px;">
                                    <i class="bi bi-check-all me-1"></i> Completed
                                </button>
                            <?php elseif ($isNext): ?>
                                <?php if ($pendingUpgrade): ?>
                                    <button type="button" class="btn btn-warning w-100 disabled text-dark" style="border-radius: 8px;">
                                        <i class="bi bi-hourglass-split me-1"></i> Verification Pending
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary w-100 shadow-sm" style="border-radius: 8px;" 
                                            data-bs-toggle="modal" data-bs-target="#upgradeModal"
                                            data-level="<?php echo $lvl; ?>" 
                                            data-name="<?php echo e($pkg['package_name']); ?>"
                                            data-amount="<?php echo $price; ?>"
                                            data-amount-formatted="<?php echo formatCurrency($price); ?>">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Upgrade to Level <?php echo $lvl; ?>
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary w-100 disabled text-muted" style="border-radius: 8px;">
                                    <i class="bi bi-lock me-1"></i> Unlock Level <?php echo ($lvl - 1); ?> First
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ====================================================
     SECTION 2: UPGRADE REQUEST MODAL
==================================================== -->
<?php if ($nextPkg): ?>
<div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="upgradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="upgradeModalLabel">
                    <i class="bi bi-arrow-up-circle text-info me-2"></i> Confirm Package Upgrade
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?php echo BASE_URL; ?>/user/packages.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="submit_upgrade">
                <input type="hidden" name="target_level" value="<?php echo $nextLevel; ?>">

                <div class="modal-body p-4">
                    <!-- Upgrade Summary -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Current Level:</span>
                            <span class="badge bg-secondary">Level <?php echo $currentLevel; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Target Package:</span>
                            <span class="fw-bold text-primary">Level <?php echo $nextLevel; ?> (<?php echo e($nextPkg['package_name']); ?>)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <span class="fw-bold text-dark">Official Package Amount:</span>
                            <span class="fs-4 fw-bold text-success"><?php echo formatCurrency($nextPkg['amount'] ?? $nextPkg['price']); ?></span>
                        </div>
                    </div>

                    <!-- Payment Mode Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Payment Option</label>
                        <select class="form-select" name="payment_mode" id="payment_mode_select" required>
                            <option value="Wallet Balance" <?php echo ($walletBalance >= ($nextPkg['amount'] ?? $nextPkg['price'])) ? 'selected' : ''; ?>>
                                Wallet Balance (Available: <?php echo formatCurrency($walletBalance, true); ?>)
                            </option>
                            <option value="Direct Bank / UPI Transfer">Direct Bank / UPI Transfer</option>
                            <option value="Cash / Administrator Approval">Cash Deposit / Admin Verification</option>
                        </select>
                        <div class="form-text small">
                            Official company payment accounts can be verified with administration.
                        </div>
                    </div>

                    <!-- Transaction / UTR Reference -->
                    <div class="mb-3" id="tx_ref_container">
                        <label class="form-label fw-semibold small">Transaction Reference / UTR Number <span class="text-muted fw-normal">(Optional for wallet)</span></label>
                        <input type="text" class="form-control" name="transaction_ref" placeholder="e.g. UTR123456789 or Wallet" maxlength="80">
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="form-check p-3 bg-primary-subtle rounded-3">
                        <input class="form-check-input" type="checkbox" id="termsCheck" required>
                        <label class="form-check-label small" for="termsCheck">
                            I understand that upgrading to <strong>Level <?php echo $nextLevel; ?></strong> requires sequential progression as per the Social Service Dream official business plan.
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send-check me-1"></i> Submit Upgrade Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====================================================
     SECTION 3: UPGRADE HISTORY TABLE
==================================================== -->
<div class="card border-0 shadow-sm mt-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0" style="color: #0b192c;">
                <i class="bi bi-clock-history text-primary me-2"></i> Upgrade History
            </h5>
            <div class="text-muted small">Record of all package upgrade requests and their status</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-1">
                Total: <?php echo count($upgradeHistory); ?>
            </span>
            <a href="<?php echo BASE_URL; ?>/user/upgrade-history.php" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-box-arrow-up-right me-1"></i> Full Paginated History
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4">Request ID</th>
                        <th>Progression</th>
                        <th>Package Name</th>
                        <th>Amount</th>
                        <th>Payment Mode / Ref</th>
                        <th>Requested Date</th>
                        <th>Status</th>
                        <th class="pe-4">Admin Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upgradeHistory)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-layers fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No upgrade requests on record. When you upgrade to Level 2 or higher, records will appear here.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upgradeHistory as $upg): 
                            $status = $upg['status'];
                            $badgeClass = match($status) {
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default    => 'bg-warning text-dark'
                            };
                        ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-semibold">
                                    #UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        Level <?php echo (int)$upg['from_level']; ?> &rarr; Level <?php echo (int)$upg['to_level']; ?>
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    <?php echo e($upg['package_name'] ?? ('Level ' . $upg['to_level'])); ?>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?php echo formatCurrency($upg['amount']); ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?php echo e($upg['payment_mode'] ?? 'Wallet / Direct'); ?></div>
                                    <?php if (!empty($upg['transaction_ref'])): ?>
                                        <div class="text-muted text-xxs font-monospace"><?php echo e($upg['transaction_ref']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?php echo date('d M Y, h:i A', strtotime($upg['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?> text-capitalize px-2 py-1 rounded-pill">
                                        <?php echo e($status); ?>
                                    </span>
                                </td>
                                <td class="pe-4 small text-muted">
                                    <?php echo e($upg['admin_remarks'] ?? ($status === 'pending' ? 'Awaiting verification' : '-')); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Plan Reference Card -->
<div class="card border-0 shadow-sm mt-4 bg-light" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> SS Dream Business Plan Rules Summary</h6>
        <div class="row g-3 small text-muted">
            <div class="col-md-4">
                <strong>1. Direct Income:</strong> 100% on Level 1 direct referrals.
            </div>
            <div class="col-md-4">
                <strong>2. Level Income:</strong> 50% applicable from Level 2 to Level 7.
            </div>
            <div class="col-md-4">
                <strong>3. Sponsor Income:</strong> 50% applicable from Level 2 to Unlimited Level.
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
