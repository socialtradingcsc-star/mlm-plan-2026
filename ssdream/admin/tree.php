<?php
/**
 * Social Service Dream - Administrator Binary Network Monitoring
 * Part 4: 2x2 Binary Tree + Sponsor & Team Management
 * Target: C:\xampp\htdocs\ssdream\admin\tree.php
 */

$pageTitle = "Binary Network Tree Monitoring";
$pageHeading = "Binary Network Tree";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$db = getDb();

// Search & Target User Selection
$searchQuery = trim($_GET['search'] ?? '');
$targetUserId = 1; // Default to first user or root

if (!empty($searchQuery)) {
    // Search by User ID, Username, or Sponsor ID
    if ($db) {
        try {
            if (is_numeric($searchQuery)) {
                $stmt = $db->prepare("SELECT id FROM users WHERE id = :id OR sponsor_id = :sid LIMIT 1");
                $stmt->execute(['id' => (int)$searchQuery, 'sid' => (int)$searchQuery]);
            } else {
                $stmt = $db->prepare("SELECT id FROM users WHERE username = :u OR sponsor_username = :su LIMIT 1");
                $stmt->execute(['u' => $searchQuery, 'su' => $searchQuery]);
            }
            $foundId = $stmt->fetchColumn();
            if ($foundId) {
                $targetUserId = (int)$foundId;
            } else {
                setFlashMessage('warning', "No member found matching '{$searchQuery}'. Showing default network node.");
            }
        } catch (PDOException $e) {
            // Fallback
        }
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $targetUserId = (int)$_GET['id'];
}

// Fetch member details
$targetUser = getUserById($targetUserId);
if (!$targetUser && $db) {
    // Pick first user in database
    $first = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($first) {
        $targetUserId = (int)$first;
        $targetUser = getUserById($targetUserId);
    }
}

// Children of the inspected node
$children = getBinaryChildren($targetUserId);

// Parent of inspected node
$parentNode = null;
if (!empty($targetUser['parent_id'])) {
    $parentNode = getUserById((int)$targetUser['parent_id']);
}

// Sponsor of inspected node
$sponsorNode = null;
if (!empty($targetUser['sponsor_id'])) {
    $sponsorNode = getUserById((int)$targetUser['sponsor_id']);
}

// 2x2 Tree hierarchy for visual canvas
$treeRoot = getBinaryTreeHierarchy($targetUserId, 3);
$totalDownlineCount = getBinaryTeamCount($targetUserId);
$activeDownlineCount = getActiveBinaryTeamCount($targetUserId);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill small">
                        Administrator Monitor
                    </span>
                    <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small">
                        Read-Only Inspection
                    </span>
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill small">
                        2&times;2 Binary Integrity: Enforced
                    </span>
                </div>
                <h3 class="fw-bold mb-1 text-white">Binary Network Tree Inspection</h3>
                <p class="text-white-50 small mb-0">
                    Search and inspect any member's binary hierarchy, direct sponsor, parent node, and left/right children.
                    Manual tree manipulation is disabled to protect network structure integrity.
                </p>
            </div>

            <!-- Search Form -->
            <div class="col-lg-5 text-lg-end">
                <form action="<?php echo BASE_URL; ?>/admin/tree.php" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by Username, User ID, or Sponsor ID..." 
                           value="<?php echo e($searchQuery); ?>" required>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-3">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Flash Notifications -->
<?php echo renderFlashMessages(); ?>

<!-- Inspected Node Summary Card -->
<?php if ($targetUser): ?>
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark fs-6">
            <i class="bi bi-info-circle text-primary me-2"></i> Inspected Node: 
            <strong><?php echo e($targetUser['full_name']); ?></strong> 
            (@<?php echo e($targetUser['username']); ?> &bull; ID #<?php echo $targetUser['id']; ?>)
        </span>
        <span class="badge bg-success px-3 py-1 rounded-pill text-capitalize">
            Status: <?php echo e($targetUser['status']); ?>
        </span>
    </div>

    <div class="card-body p-4">
        <div class="row g-3">
            <!-- Sponsor Info -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Direct Sponsor</span>
                    <?php if ($sponsorNode): ?>
                        <div class="fw-bold text-dark"><?php echo e($sponsorNode['full_name']); ?></div>
                        <a href="<?php echo BASE_URL; ?>/admin/tree.php?id=<?php echo $sponsorNode['id']; ?>" class="badge bg-light text-primary border font-monospace text-decoration-none">
                            @<?php echo e($sponsorNode['username']); ?> (#<?php echo $sponsorNode['id']; ?>)
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">No Sponsor (Root)</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parent Info -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Binary Parent &amp; Leg</span>
                    <?php if ($parentNode): ?>
                        <div class="fw-bold text-dark"><?php echo e($parentNode['full_name']); ?></div>
                        <a href="<?php echo BASE_URL; ?>/admin/tree.php?id=<?php echo $parentNode['id']; ?>" class="badge bg-light text-primary border font-monospace text-decoration-none">
                            @<?php echo e($parentNode['username']); ?>
                        </a>
                        <span class="badge <?php echo ($targetUser['position'] === 'left') ? 'bg-primary' : 'bg-info text-dark'; ?> text-uppercase ms-1">
                            <?php echo e($targetUser['position']); ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted small">Root Binary Node</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Left Child -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Left Binary Child</span>
                    <?php if (!empty($children['left'])): ?>
                        <div class="fw-bold text-dark"><?php echo e($children['left']['full_name']); ?></div>
                        <a href="<?php echo BASE_URL; ?>/admin/tree.php?id=<?php echo $children['left']['id']; ?>" class="badge bg-primary text-white text-decoration-none font-monospace">
                            @<?php echo e($children['left']['username']); ?> &rarr;
                        </a>
                    <?php else: ?>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-dashed">
                            Empty / Available
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Child -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Right Binary Child</span>
                    <?php if (!empty($children['right'])): ?>
                        <div class="fw-bold text-dark"><?php echo e($children['right']['full_name']); ?></div>
                        <a href="<?php echo BASE_URL; ?>/admin/tree.php?id=<?php echo $children['right']['id']; ?>" class="badge bg-info text-dark text-decoration-none font-monospace">
                            @<?php echo e($children['right']['username']); ?> &rarr;
                        </a>
                    <?php else: ?>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-dashed">
                            Empty / Available
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top flex-wrap small">
            <div>
                <strong>Current Package:</strong> 
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    Level <?php echo (int)$targetUser['current_level']; ?> (<?php echo formatCurrency(PLAN_PACKAGES[$targetUser['current_level']]['price'] ?? 1200); ?>)
                </span>
            </div>
            <div>
                <strong>Total Downline:</strong> 
                <span class="fw-bold text-dark"><?php echo $totalDownlineCount; ?> members</span>
            </div>
            <div>
                <strong>Active Downline:</strong> 
                <span class="fw-bold text-success"><?php echo $activeDownlineCount; ?> members</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====================================================
     SECTION 2: VISUAL 2x2 CANVAS IN ADMIN
==================================================== -->
<div class="card border-0 shadow-sm overflow-hidden mb-4" style="border-radius: 14px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-diagram-3 text-primary me-2"></i> Visual Tree Hierarchy
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="btn-tree-zoom-in" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
            <button class="btn btn-sm btn-outline-secondary" id="btn-tree-zoom-out" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
            <button class="btn btn-sm btn-outline-secondary" id="btn-tree-reset" title="Reset"><i class="bi bi-arrows-angle-contract"></i></button>
        </div>
    </div>

    <div class="card-body p-4 bg-light overflow-x-auto text-center" style="min-height: 500px;" id="tree-canvas-container">
        <div id="tree-viewport" style="display: inline-block; min-width: 820px; transition: transform 0.2s ease; transform-origin: top center;">
            
            <!-- TIER 1: ROOT -->
            <div class="tree-row d-flex justify-content-center mb-4">
                <?php renderAdminTreeNode($treeRoot, 'root'); ?>
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
                <div style="width: 48%;" class="d-flex flex-column align-items-center">
                    <?php renderAdminTreeNode($treeRoot['left'] ?? null, 'left'); ?>
                </div>
                <div style="width: 48%;" class="d-flex flex-column align-items-center">
                    <?php renderAdminTreeNode($treeRoot['right'] ?? null, 'right'); ?>
                </div>
            </div>

            <!-- CONNECTOR LINE TIER 2 TO TIER 3 -->
            <div class="tree-row d-flex justify-content-around mb-3">
                <div style="width: 48%;" class="position-relative" style="height: 24px;">
                    <div style="position: absolute; left: 50%; top: 0; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: left: 25%; right: 25%; top: 12px; height: 2px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; right: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                </div>
                <div style="width: 48%;" class="position-relative" style="height: 24px;">
                    <div style="position: absolute; left: 50%; top: 0; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: left: 25%; right: 25%; top: 12px; height: 2px; background: #0066cc;"></div>
                    <div style="position: absolute; left: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                    <div style="position: absolute; right: 25%; top: 12px; width: 2px; height: 12px; background: #0066cc;"></div>
                </div>
            </div>

            <!-- TIER 3: 4 GRANDCHILDREN -->
            <div class="tree-row d-flex justify-content-between mb-4">
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderAdminTreeNode($treeRoot['left']['left'] ?? null, 'left'); ?>
                </div>
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderAdminTreeNode($treeRoot['left']['right'] ?? null, 'right'); ?>
                </div>
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderAdminTreeNode($treeRoot['right']['left'] ?? null, 'left'); ?>
                </div>
                <div style="width: 24%;" class="d-flex justify-content-center">
                    <?php renderAdminTreeNode($treeRoot['right']['right'] ?? null, 'right'); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
/**
 * Helper function to render admin visual tree card
 */
function renderAdminTreeNode(?array $node, string $leg = 'root'): void {
    if (empty($node) || ($node['is_empty'] ?? false)) {
        ?>
        <div class="tree-node-card empty-node p-3 rounded-3 text-center border border-2 border-secondary border-dashed bg-white shadow-xs" 
             style="width: 170px; min-height: 110px; opacity: 0.85;">
            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mx-auto mb-1" style="width: 32px; height: 32px;">
                <i class="bi bi-dash-lg"></i>
            </div>
            <div class="fw-bold text-muted small">Available</div>
            <span class="badge bg-light text-secondary border text-xxs text-uppercase mt-1">
                <?php echo ($leg === 'left') ? 'Left Slot' : (($leg === 'right') ? 'Right Slot' : 'Open'); ?>
            </span>
        </div>
        <?php
        return;
    }

    $uid = (int)$node['id'];
    $uName = $node['full_name'] ?? 'Member';
    $uUsername = $node['username'] ?? 'user';
    $uLevel = (int)($node['current_level'] ?? 1);
    $uStatus = $node['status'] ?? 'active';
    $uPosition = $node['position'] ?? $leg;
    ?>
    <div class="tree-node-card member-node p-3 rounded-3 text-center bg-white shadow-sm border position-relative transition-hover" 
         style="width: 170px; min-height: 110px; cursor: pointer; border-top: 3px solid #0066cc !important;"
         onclick="window.location.href='<?php echo BASE_URL; ?>/admin/tree.php?id=<?php echo $uid; ?>'">
        
        <?php if ($leg !== 'root'): ?>
            <span class="badge <?php echo ($uPosition === 'left') ? 'bg-primary' : 'bg-info text-dark'; ?> text-xxs position-absolute top-0 start-50 translate-middle rounded-pill px-2">
                <?php echo strtoupper($uPosition); ?>
            </span>
        <?php endif; ?>

        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-1" style="width: 36px; height: 36px;">
            <i class="bi bi-person-fill fs-5"></i>
        </div>

        <div class="fw-bold text-dark text-truncate small" title="<?php echo e($uName); ?>"><?php echo e($uName); ?></div>
        <div class="badge bg-light text-dark font-monospace text-xxs mb-1">@<?php echo e($uUsername); ?></div>

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
