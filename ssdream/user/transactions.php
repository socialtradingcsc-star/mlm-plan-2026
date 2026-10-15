<?php
/**
 * Social Service Dream - User Transaction Ledger & History
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\user\transactions.php
 * =========================================================================
 */

$pageTitle = "Transactions - Social Service Dream";
$pageHeading = "Transaction Ledger";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$wallet = getUserWallet($userId);

// Filter parameter: 'all', 'income', 'withdrawal', 'refund'
$allowedFilters = ['all', 'income', 'withdrawal', 'refund'];
$currentFilter = strtolower(trim($_GET['type'] ?? 'all'));
if (!in_array($currentFilter, $allowedFilters)) {
    $currentFilter = 'all';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$filteredData = getUserFilteredTransactions($userId, $currentFilter, $limit, $offset);
$transactions = $filteredData['records'];
$totalRecords = $filteredData['total'];
$totalPages = ceil($totalRecords / max(1, $limit));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Wallet Quick Glance -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-primary border-3 shadow-sm py-3">
            <span class="text-muted text-xxs text-uppercase fw-semibold">Current Balance</span>
            <h4 class="fw-bold mb-0 text-navy" style="color: #0b192c;"><?php echo formatCurrency($wallet['balance'], true); ?></h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-success border-3 shadow-sm py-3">
            <span class="text-muted text-xxs text-uppercase fw-semibold">Total Credit</span>
            <h4 class="fw-bold mb-0 text-success"><?php echo formatCurrency($wallet['total_credit'], true); ?></h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-danger border-3 shadow-sm py-3">
            <span class="text-muted text-xxs text-uppercase fw-semibold">Total Debit</span>
            <h4 class="fw-bold mb-0 text-danger"><?php echo formatCurrency($wallet['total_debit'], true); ?></h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-warning border-3 shadow-sm py-3">
            <span class="text-muted text-xxs text-uppercase fw-semibold">Pending Withdrawal</span>
            <h4 class="fw-bold mb-0 text-warning"><?php echo formatCurrency($wallet['pending_withdrawal'], true); ?></h4>
        </div>
    </div>
</div>

<div class="card border shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                <i class="bi bi-receipt text-primary me-2"></i> Account Transaction Ledger
            </h6>
            <span class="text-muted small">Official audit record with exact Before and After balances.</span>
        </div>

        <!-- Filter Buttons: All, Income, Withdrawal, Refund -->
        <div class="d-flex flex-wrap gap-1">
            <a href="?type=all" class="btn btn-sm <?php echo ($currentFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                All (<?php echo ($currentFilter === 'all') ? $totalRecords : 'Total'; ?>)
            </a>
            <a href="?type=income" class="btn btn-sm <?php echo ($currentFilter === 'income') ? 'btn-success' : 'btn-outline-success'; ?>">
                <i class="bi bi-arrow-down-left me-1"></i> Income
            </a>
            <a href="?type=withdrawal" class="btn btn-sm <?php echo ($currentFilter === 'withdrawal') ? 'btn-danger' : 'btn-outline-danger'; ?>">
                <i class="bi bi-arrow-up-right me-1"></i> Withdrawal
            </a>
            <a href="?type=refund" class="btn btn-sm <?php echo ($currentFilter === 'refund') ? 'btn-info' : 'btn-outline-info'; ?>">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Refund
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Transaction ID</th>
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No records found</h6>
                                <p class="text-muted small mb-0">
                                    No <?php echo e($currentFilter); ?> transactions exist in your account.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <!-- Date -->
                                <td class="ps-4 small text-secondary">
                                    <?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?>
                                </td>

                                <!-- Transaction ID -->
                                <td class="font-monospace small fw-bold text-dark">
                                    <?php echo e($tx['reference_no'] ?? $tx['reference_id'] ?? ('TXN-' . $tx['id'])); ?>
                                </td>

                                <!-- Type -->
                                <td>
                                    <?php if ($tx['transaction_type'] === 'credit'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-arrow-down-left me-1"></i> Credit
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-arrow-up-right me-1"></i> Debit
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Amount -->
                                <td class="fw-bold fs-6 <?php echo $tx['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($tx['transaction_type'] === 'credit' ? '+' : '-') . formatCurrency($tx['amount'], true); ?>
                                </td>

                                <!-- Balance Before -->
                                <td class="font-monospace small text-muted">
                                    <?php echo formatCurrency($tx['balance_before'] ?? 0, true); ?>
                                </td>

                                <!-- Balance After -->
                                <td class="font-monospace small fw-bold text-navy" style="color: #0b192c;">
                                    <?php echo formatCurrency($tx['balance_after'] ?? $tx['amount'], true); ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php if ($tx['status'] === 'completed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i> Completed
                                        </span>
                                    <?php elseif ($tx['status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i> Failed
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Description -->
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> records)
            </span>
            <nav aria-label="Transaction pages">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?type=<?php echo urlencode($currentFilter); ?>&page=<?php echo ($page - 1); ?>">Previous</a>
                        </li>
                    <? endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?type=<?php echo urlencode($currentFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?type=<?php echo urlencode($currentFilter); ?>&page=<?php echo ($page + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
