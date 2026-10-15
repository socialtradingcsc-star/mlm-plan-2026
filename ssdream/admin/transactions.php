<?php
/**
 * Social Service Dream - Admin Global Transactions Ledger
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\admin\transactions.php
 * =========================================================================
 */

$pageTitle = "Global Transactions - SS Dream Admin";
$pageHeading = "Global Financial Ledger";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$db = getDb();

$filter = strtolower(trim($_GET['type'] ?? 'all'));
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($filter === 'income') {
    $where[] = "t.category IN ('direct_income', 'level_income', 'sponsor_income')";
} elseif ($filter === 'withdrawal') {
    $where[] = "t.category = 'withdrawal'";
} elseif ($filter === 'refund') {
    $where[] = "(t.reference_type = 'refund' OR t.category = 'admin_adjustment')";
} elseif ($filter === 'credit') {
    $where[] = "t.transaction_type = 'credit'";
} elseif ($filter === 'debit') {
    $where[] = "t.transaction_type = 'debit'";
}

if (!empty($search)) {
    $where[] = "(u.username LIKE :q OR u.full_name LIKE :q OR t.reference_no LIKE :q OR t.reference_id LIKE :q)";
    $params['q'] = "%{$search}%";
}

$whereSql = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

$totalRecords = 0;
$transactions = [];

if ($db) {
    try {
        $cStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            {$whereSql}
        ");
        $cStmt->execute($params);
        $totalRecords = (int)$cStmt->fetchColumn();

        $tStmt = $db->prepare("
            SELECT t.*, u.username, u.full_name, u.mobile, u.current_level 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            {$whereSql} 
            ORDER BY t.id DESC 
            LIMIT :offset, :limit
        ");
        foreach ($params as $k => $v) {
            $tStmt->bindValue(":{$k}", $v);
        }
        $tStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $tStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $tStmt->execute();
        $transactions = $tStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $transactions = [];
    }
}

$totalPages = ceil($totalRecords / max(1, $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="card border shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-receipt text-primary me-2"></i> Master Transaction Ledger &amp; Audit
                </h6>
                <span class="text-muted small">Real-time ledger audit log with exact Before and After balances.</span>
            </div>

            <form method="GET" action="" class="d-flex gap-2">
                <input type="hidden" name="type" value="<?php echo e($filter); ?>">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="q" class="form-control" placeholder="Search user, ref..." value="<?php echo e($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
                <?php if (!empty($search)): ?>
                    <a href="?type=<?php echo urlencode($filter); ?>" class="btn btn-outline-secondary btn-sm" title="Clear">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
            <a href="?type=all<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($filter === 'all') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                All (<?php echo ($filter === 'all') ? $totalRecords : 'Total'; ?>)
            </a>
            <a href="?type=income<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($filter === 'income') ? 'btn-success' : 'btn-outline-success'; ?>">
                <i class="bi bi-arrow-down-left me-1"></i> Incomes
            </a>
            <a href="?type=withdrawal<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($filter === 'withdrawal') ? 'btn-danger' : 'btn-outline-danger'; ?>">
                <i class="bi bi-arrow-up-right me-1"></i> Withdrawals
            </a>
            <a href="?type=refund<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($filter === 'refund') ? 'btn-info' : 'btn-outline-info'; ?>">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Refunds
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date &amp; Time</th>
                        <th>Reference / ID</th>
                        <th>Member</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Status</th>
                        <th class="pe-4">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No transaction records found</h6>
                                <p class="text-muted small mb-0">No entries match your search and filter criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="ps-4 small text-secondary">
                                    <?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?>
                                </td>
                                <td class="font-monospace small fw-bold text-dark">
                                    <?php echo e($tx['reference_no'] ?? $tx['reference_id'] ?? ('TXN-' . $tx['id'])); ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?php echo e($tx['full_name']); ?></div>
                                    <div class="font-monospace text-xxs text-primary">@<?php echo e($tx['username']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border text-xxs">
                                        <?php echo e(ucwords(str_replace('_', ' ', $tx['category']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'credit'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xxs">
                                            Credit
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xxs">
                                            Debit
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold <?php echo $tx['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($tx['transaction_type'] === 'credit' ? '+' : '-') . formatCurrency($tx['amount'], true); ?>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?php echo formatCurrency($tx['balance_before'] ?? 0, true); ?>
                                </td>
                                <td class="small font-monospace fw-bold text-navy" style="color: #0b192c;">
                                    <?php echo formatCurrency($tx['balance_after'] ?? $tx['amount'], true); ?>
                                </td>
                                <td>
                                    <?php if ($tx['status'] === 'completed'): ?>
                                        <span class="badge bg-success text-white text-xxs">Completed</span>
                                    <?php elseif ($tx['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark text-xxs">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white text-xxs">Failed / Refunded</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-secondary small">
                                    <?php echo e($tx['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> records)
            </span>
            <nav aria-label="Transaction pages">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?type=<?php echo urlencode($filter); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo ($page - 1); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?type=<?php echo urlencode($filter); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?type=<?php echo urlencode($filter); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo ($page + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
