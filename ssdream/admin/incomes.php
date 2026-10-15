<?php
/**
 * Social Service Dream - Admin Income Distribution Audit & Engine Oversight
 * =========================================================================
 * PART 5: Master System Income Monitoring & Audit Ledger
 * Target: C:\xampp\htdocs\ssdream\admin\incomes.php
 * =========================================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/income-engine.php';

requireAdminLogin();

$typeFilter = strtolower(trim($_GET['type'] ?? 'all'));
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$metrics = getAdminIncomeMetrics();
$ledgerData = getAdminPaginatedIncomes($page, $limit, $typeFilter, $search);
$incomes = $ledgerData['records'];
$totalPages = $ledgerData['total_pages'];
$totalCount = $ledgerData['total'];

// Official package reference data from database
$db = getDbConnection();
$pkgStmt = $db->query("SELECT * FROM packages ORDER BY level ASC");
$packages = $pkgStmt->fetchAll(PDO::FETCH_ASSOC);

$adminPageTitle = 'Income Distribution Ledger & Audit';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-wrapper">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Breadcrumb & Title -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-xs">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Income Audit</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-dark mb-0">
                    <i class="bi bi-calculator text-primary me-2"></i>Income Distribution Audit &amp; Engine Oversight
                </h1>
                <p class="text-muted small mb-0">
                    System-wide audit ledger of all automatically distributed earnings across the 3 locked SS Dream income streams.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo BASE_URL; ?>/admin/upgrades.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-clock-history me-1"></i> Upgrade Requests
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/tree.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-diagram-3 me-1"></i> Binary Tree Monitor
                </a>
            </div>
        </div>

        <!-- Engine Status Alert -->
        <div class="card border-0 shadow-xs rounded-3 mb-4 bg-white border-start border-4 border-success">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Part 5 Income Calculation Engine: ACTIVE</h6>
                            <p class="text-muted small mb-0">
                                3 Official Rules Enforced: <strong>Direct 100%</strong> (Level 1) &bull; <strong>Level 50%</strong> (Level 2&ndash;7 with upgrade validation) &bull; <strong>Sponsor 50%</strong> (Level 2&ndash;Unlimited).
                            </p>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 text-xs font-monospace">
                        <i class="bi bi-lock-fill me-1"></i> Idempotent &amp; Duplicate-Safe
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Global System Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 bg-white h-100">
                    <div class="card-body p-3">
                        <div class="text-muted text-xs text-uppercase fw-semibold mb-1">Total System Incomes</div>
                        <h3 class="fw-bold text-dark mb-1 font-monospace">₹<?php echo number_format($metrics['total_distributed'], 2); ?></h3>
                        <span class="text-xs text-muted"><?php echo $metrics['total_count']; ?> total distribution events</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 bg-white h-100">
                    <div class="card-body p-3">
                        <div class="text-success text-xs text-uppercase fw-semibold mb-1">Direct Incomes (100%)</div>
                        <h3 class="fw-bold text-success mb-1 font-monospace">₹<?php echo number_format($metrics['total_direct'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 1 Referral Entry</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 bg-white h-100">
                    <div class="card-body p-3">
                        <div class="text-primary text-xs text-uppercase fw-semibold mb-1">Level Incomes (50%)</div>
                        <h3 class="fw-bold text-primary mb-1 font-monospace">₹<?php echo number_format($metrics['total_level'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 2 to Level 7 (Upgrade verified)</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 bg-white h-100">
                    <div class="card-body p-3">
                        <div class="text-xs text-uppercase fw-semibold mb-1" style="color: #6f42c1;">Sponsor Incomes (50%)</div>
                        <h3 class="fw-bold mb-1 font-monospace" style="color: #6f42c1;">₹<?php echo number_format($metrics['total_sponsor'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 2 to Unlimited Level</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Official Plan Commission Matrix Reference Table -->
        <div class="card border-0 shadow-xs rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-table me-2 text-primary"></i>Locked Business Rules Commission Matrix
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 text-xs">
                        <thead class="table-light text-muted text-uppercase text-xxs">
                            <tr>
                                <th class="ps-3">Level</th>
                                <th>Package Price</th>
                                <th>2&times;2 Team Size</th>
                                <th>Direct Income (100%)</th>
                                <th>Level Income (50%)</th>
                                <th>Sponsor Income (50%)</th>
                                <th class="pe-3">Max Level Potential</th>
                            </tr>
                        </thead>
                        <tbody class="font-monospace">
                            <?php foreach ($packages as $p): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark font-sans">Level <?php echo (int)$p['level']; ?></td>
                                    <td class="fw-bold">₹<?php echo number_format((float)$p['price'], 2); ?></td>
                                    <td class="font-sans"><?php echo (int)$p['team_size']; ?> members</td>
                                    <td>
                                        <?php if ((int)$p['level'] === 1): ?>
                                            <span class="text-success fw-bold">100% (₹<?php echo number_format((float)$p['price'], 2); ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$p['level'] >= 2): ?>
                                            <span class="text-primary fw-bold">50% (₹<?php echo number_format((float)$p['price'] * 0.5, 2); ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$p['level'] >= 2): ?>
                                            <span class="fw-bold" style="color: #6f42c1;">50% (₹<?php echo number_format((float)$p['price'] * 0.5, 2); ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-3 fw-bold text-dark">
                                        <?php if ((int)$p['level'] === 1): ?>
                                            ₹<?php echo number_format((float)$p['price'] * 2, 2); ?>
                                        <?php else: ?>
                                            ₹<?php echo number_format((float)$p['team_size'] * (float)$p['price'] * 0.5, 2); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Master System Income Audit Ledger -->
        <div class="card border-0 shadow-xs rounded-3 bg-white">
            <div class="card-header bg-white border-bottom p-3">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-12 col-md-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-check me-2 text-success"></i>System Distribution Ledger
                            <span class="badge bg-light text-dark border ms-2"><?php echo $totalCount; ?> records</span>
                        </h6>
                    </div>
                    <div class="col-12 col-md-8">
                        <form method="GET" action="<?php echo BASE_URL; ?>/admin/incomes.php" class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                            <!-- Stream Type Select -->
                            <select name="type" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <option value="all" <?php echo ($typeFilter === 'all') ? 'selected' : ''; ?>>All Streams</option>
                                <option value="direct" <?php echo ($typeFilter === 'direct') ? 'selected' : ''; ?>>Direct Income (100%)</option>
                                <option value="level" <?php echo ($typeFilter === 'level') ? 'selected' : ''; ?>>Level Income (50%)</option>
                                <option value="sponsor" <?php echo ($typeFilter === 'sponsor') ? 'selected' : ''; ?>>Sponsor Income (50%)</option>
                            </select>

                            <!-- Search Input -->
                            <div class="input-group input-group-sm w-auto">
                                <input type="text" name="search" class="form-control" placeholder="Search username, ref..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                                <?php if (!empty($search) || $typeFilter !== 'all'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/incomes.php" class="btn btn-outline-danger" title="Clear Filters"><i class="bi bi-x"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($incomes)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                        <p class="mb-1 fw-semibold">No income distribution records match the current filters.</p>
                        <p class="text-xs">Incomes are automatically registered upon member registration and admin upgrade approvals.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead class="table-light text-muted text-uppercase text-xxs">
                                <tr>
                                    <th class="ps-3">ID / Reference</th>
                                    <th>Income Stream</th>
                                    <th>Beneficiary (Recipient)</th>
                                    <th>Triggered By (Downline)</th>
                                    <th>Base Package</th>
                                    <th>Rate</th>
                                    <th>Amount Credited</th>
                                    <th>Date &amp; Time</th>
                                    <th class="pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomes as $inc): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-dark">
                                            #INC-<?php echo str_pad($inc['id'], 6, '0', STR_PAD_LEFT); ?>
                                            <div class="text-xxs text-muted font-monospace"><?php echo htmlspecialchars($inc['transaction_ref'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($inc['income_type'] === 'direct'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Direct 100%</span>
                                            <?php elseif ($inc['income_type'] === 'level'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Level <?php echo (int)$inc['source_level']; ?> (50%)</span>
                                            <?php elseif ($inc['income_type'] === 'sponsor'): ?>
                                                <span class="badge bg-dark-subtle text-dark border">Sponsor L<?php echo (int)$inc['source_level']; ?> (50%)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($inc['to_name'] ?? 'User'); ?></div>
                                            <div class="text-muted font-monospace text-xxs">@<?php echo htmlspecialchars($inc['to_username'] ?? 'user'); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($inc['from_name'] ?? 'Member'); ?></div>
                                            <div class="text-muted font-monospace text-xxs">@<?php echo htmlspecialchars($inc['from_username'] ?? 'member'); ?></div>
                                        </td>
                                        <td class="font-monospace">
                                            ₹<?php echo number_format((float)($inc['package_price'] ?? 1200), 2); ?>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <?php echo number_format((float)$inc['percentage'], 0); ?>%
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success font-monospace fs-6">
                                                +₹<?php echo number_format((float)$inc['amount'], 2); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo date('d M Y, h:i A', strtotime($inc['created_at'])); ?>
                                        </td>
                                        <td class="pe-3">
                                            <span class="badge bg-success text-white">
                                                <i class="bi bi-check2 me-1"></i> Credited
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination Footer -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-top p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="text-xs text-muted">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?> &bull; Total <?php echo $totalCount; ?> entries
                    </span>
                    <nav aria-label="Admin Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/admin/incomes.php?type=<?php echo urlencode($typeFilter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo ($page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/admin/incomes.php?type=<?php echo urlencode($typeFilter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/admin/incomes.php?type=<?php echo urlencode($typeFilter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo ($page + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
