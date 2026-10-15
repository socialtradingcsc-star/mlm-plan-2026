<?php
/**
 * Social Service Dream - 2x2 Binary Tree Visualization
 * Part 4: 2x2 Binary Tree + Sponsor & Team Management
 * Target: C:\xampp\htdocs\ssdream\user\binary-tree.php
 */

$pageTitle = "2x2 Binary Network Tree";
$pageHeading = "Binary Tree";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$loggedInUserId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';

// Handle Target Root Node (Drill-down navigation)
$targetUserId = $loggedInUserId;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $requestedId = (int)$_GET['id'];
    // SECURITY CHECK: Verify target is inside logged-in user's downline network
    if (isUserInDownline($loggedInUserId, $requestedId)) {
        $targetUserId = $requestedId;
    } else {
        setFlashMessage('error', 'Unauthorized access: You can only view nodes within your own network.');
        header('Location: ' . BASE_URL . '/user/binary-tree.php');
        exit;
    }
} elseif (isset($_GET['user']) && !empty($_GET['user'])) {
    $requestedUser = getUserByUsername(trim($_GET['user']));
    if ($requestedUser && isUserInDownline($loggedInUserId, (int)$requestedUser['id'])) {
        $targetUserId = (int)$requestedUser['id'];
    } else {
        setFlashMessage('error', 'Unauthorized access: User not found or outside your network.');
        header('Location: ' . BASE_URL . '/user/binary-tree.php');
        exit;
    }
}

// Fetch tree data starting at $targetUserId (Depth = 3: Root + 2 children + 4 grandchildren = 7 nodes viewable per screen)
$treeRoot = getBinaryTreeHierarchy($targetUserId, 3);
$viewUser = getUserById($targetUserId);

// Build Breadcrumbs from loggedInUser to targetUser
$breadcrumbs = [];
if ($targetUserId !== $loggedInUserId) {
    $curr = $targetUserId;
    $guard = 50;
    while ($curr > 0 && $guard-- > 0) {
        $u = getUserById($curr);
        if ($u) {
            array_unshift($breadcrumbs, $u);
            if ($u['id'] == $loggedInUserId) break;
            $curr = (int)$u['parent_id'];
        } else {
            break;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Binary Tree Top Controls & Breadcrumbs -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-primary text-white px-3 py-1 rounded-pill small">
                        2&times;2 Binary Architecture
                    </span>
                    <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small">
                        Viewing: @<?php echo e($viewUser['username'] ?? 'User'); ?>
                    </span>
                    <?php if ($targetUserId !== $loggedInUserId): ?>
                        <a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="badge bg-warning text-dark text-decoration-none px-3 py-1 rounded-pill">
                            <i class="bi bi-arrow-return-left me-1"></i> Return to My Root Node
                        </a>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-1 text-white">Visual Binary Network</h3>
                <p class="text-white-50 small mb-0">
                    Each position supports a maximum of 2 binary children (Left and Right). Click any active node to drill down into their sub-network.
                </p>
            </div>

            <div class="col-lg-5 text-lg-end">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 p-2 rounded-3 border border-white border-opacity-10">
                    <span class="text-white-50 small me-1">Tree Controls:</span>
                    <button class="btn btn-sm btn-light" id="btn-tree-zoom-in" title="Zoom In">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button class="btn btn-sm btn-light" id="btn-tree-zoom-out" title="Zoom Out">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-light" id="btn-tree-reset" title="Reset View">
                        <i class="bi bi-arrows-angle-contract"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Notifications -->
<?php echo renderFlashMessages(); ?>

<!-- Breadcrumbs Bar (if drilled down) -->
<?php if (!empty($breadcrumbs)): ?>
    <nav class="mb-4 bg-white p-3 rounded-3 shadow-sm border" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="text-primary fw-semibold"><i class="bi bi-house-door-fill me-1"></i> My Network Root</a></li>
            <?php foreach ($breadcrumbs as $bIdx => $bCrumb): 
                $isLast = ($bIdx === count($breadcrumbs) - 1);
            ?>
                <?php if ($isLast): ?>
                    <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">
                        @<?php echo e($bCrumb['username']); ?> (<?php echo e($bCrumb['full_name']); ?>)
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>/user/binary-tree.php?id=<?php echo $bCrumb['id']; ?>" class="text-primary">
                            @<?php echo e($bCrumb['username']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>

<!-- Tree Legend & Search Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Legend -->
        <div class="d-flex align-items-center gap-3 flex-wrap text-xs">
            <span class="text-muted fw-semibold">Legend:</span>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-primary px-2 py-1 rounded">Left Node</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-info text-dark px-2 py-1 rounded">Right Node</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-light text-muted border px-2 py-1 rounded border-dashed">Empty / Available</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-success px-2 py-1 rounded">Active</span>
            </div>
        </div>

        <!-- In-Network Search -->
        <form action="<?php echo BASE_URL; ?>/user/binary-tree.php" method="GET" class="d-flex gap-2" style="max-width: 320px;">
            <input type="text" name="user" class="form-control form-control-sm" placeholder="Search downline username..." required>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</div>

<!-- ====================================================
     SECTION 2: 2x2 VISUAL BINARY TREE CANVAS
==================================================== -->
<div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 14px;">
    <div class="card-body p-4 bg-light overflow-x-auto text-center" style="min-height: 520px;" id="tree-canvas-container">
        
        <div id="tree-viewport" style="display: inline-block; min-width: 820px; transition: transform 0.2s ease; transform-origin: top center;">
            
            <!-- TIER 1: ROOT NODE -->
            <div class="tree-row d-flex justify-content-center mb-4">
                <?php renderTreeNode($treeRoot, 'root'); ?>
            </div>

            <!-- CONNECTOR LINE TIER 1 TO TIER 2 -->
            <div class="tree-connector-wrapper mb-3 position-relative" style="height: 24px;">
                <div style="position: absolute; left: 50%; top: 0; width: 2px; height: 12px; background: #0066cc;"></div>
                <div style="position: absolute; left: 25%; right: 25%; top: 12px; height: 2px; background: #0066cc;"></div>
                <div style="position: absolute; left: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                <div style="position: absolute; right: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
            </div>

            <!-- TIER 2: 2 CHILDREN (LEFT & RIGHT) -->
            <div class="tree-row d-flex justify-content-around mb-4">
                <!-- Left Branch -->
                <div style="width: 48%;" class="d-flex flex-column align-items-center">
                    <?php renderTreeNode($treeRoot['left'] ?? null, 'left'); ?>
                </div>

                <!-- Right Branch -->
                <div style="width: 48%;" class="d-flex flex-column align-items-center">
                    <?php renderTreeNode($treeRoot['right'] ?? null, 'right'); ?>
                </div>
            </div>

            <!-- CONNECTOR LINE TIER 2 TO TIER 3 -->
            <div class="tree-row d-flex justify-content-around mb-3">
                <!-- Left Branch Connector -->
                <div style="width: 48%;" class="position-relative" style="height: 24px;">
                    <div style="position: absolute; left: 50%; top: 0; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; right: 25%; top: 12px; height: 2px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; right: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                </div>

                <!-- Right Branch Connector -->
                <div style="width: 48%;" class="position-relative" style="height: 24px;">
                    <div style="position: absolute; left: 50%; top: 0; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; right: 25%; top: 12px; height: 2px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; right: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                </div>
            </div>

            <!-- TIER 3: 4 GRANDCHILDREN (LL, LR, RL, RR) -->
            <div class="tree-row d-flex justify-content-between mb-4">
                <!-- Left-Left -->
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderTreeNode($treeRoot['left']['left'] ?? null, 'left'); ?>
                </div>
                <!-- Left-Right -->
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderTreeNode($treeRoot['left']['right'] ?? null, 'right'); ?>
                </div>
                <!-- Right-Left -->
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderTreeNode($treeRoot['right']['left'] ?? null, 'left'); ?>
                </div>
                <!-- Right-Right -->
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderTreeNode($treeRoot['right']['right'] ?? null, 'right'); ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php
/**
 * Helper function to render an individual visual tree node
 */
function renderTreeNode(?array $node, string $leg = 'root'): void {
    if (empty($node) || ($node['is_empty'] ?? false)) {
        // EMPTY / AVAILABLE NODE
        ?>
        <div class="tree-node-card empty-node p-3 rounded-3 text-center border border-2 border-secondary border-dashed bg-white shadow-xs" 
             style="width: 170px; min-height: 110px; opacity: 0.85;">
            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mx-auto mb-1" style="width: 32px; height: 32px;">
                <i class="bi bi-plus-lg fs-6"></i>
            </div>
            <div class="fw-bold text-muted small">Available</div>
            <span class="badge bg-light text-secondary border text-xxs text-uppercase mt-1">
                <?php echo ($leg === 'left') ? 'Left Slot' : (($leg === 'right') ? 'Right Slot' : 'Open'); ?>
            </span>
        </div>
        <?php
        return;
    }

    // ACTIVE MEMBER NODE
    $uid = (int)$node['id'];
    $uName = $node['full_name'] ?? 'Member';
    $uUsername = $node['username'] ?? 'user';
    $uLevel = (int)($node['current_level'] ?? 1);
    $uStatus = $node['status'] ?? 'active';
    $uPosition = $node['position'] ?? $leg;
    ?>
    <div class="tree-node-card member-node p-3 rounded-3 text-center bg-white shadow-sm border position-relative transition-hover" 
         style="width: 170px; min-height: 110px; cursor: pointer; border-top: 3px solid #0066cc !important;"
         onclick="window.location.href='<?php echo BASE_URL; ?>/user/binary-tree.php?id=<?php echo $uid; ?>'">
        
        <!-- Leg Indicator Badge -->
        <?php if ($leg !== 'root'): ?>
            <span class="badge <?php echo ($uPosition === 'left') ? 'bg-primary' : 'bg-info text-dark'; ?> text-xxs position-absolute top-0 start-50 translate-middle rounded-pill px-2">
                <?php echo strtoupper($uPosition); ?>
            </span>
        <?php endif; ?>

        <!-- Member Avatar / Icon -->
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-1" style="width: 36px; height: 36px;">
            <i class="bi bi-person-fill fs-5"></i>
        </div>

        <!-- Name & Username -->
        <div class="fw-bold text-dark text-truncate small" title="<?php echo e($uName); ?>"><?php echo e($uName); ?></div>
        <div class="badge bg-light text-dark font-monospace text-xxs mb-1">@<?php echo e($uUsername); ?></div>

        <!-- Level and Status -->
        <div class="d-flex justify-content-center align-items-center gap-1 mt-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xxs">
                Lvl <?php echo $uLevel; ?>
            </span>
            <span class="badge <?php echo ($uStatus === 'active') ? 'bg-success' : 'bg-warning text-dark'; ?> text-xxs rounded-pill">
                <?php echo e($uStatus); ?>
            </span>
        </div>
    </div>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
?>
