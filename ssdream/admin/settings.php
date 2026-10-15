<?php
/**
 * Social Service Dream - Admin System & Business Settings Console
 * =========================================================================
 * PART 7: SYSTEM CONFIGURATION, PAYOUT PARAMETERS & COMPANY ACCOUNTS
 * Target: C:\xampp\htdocs\ssdream\admin\settings.php
 * =========================================================================
 */

$pageTitle = "System Settings - SS Dream Admin";
$pageHeading = "Platform Configuration & Payout Settings";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$alert = null;

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security session expired. Please refresh and try again.'];
    } else {
        $dataToSave = [
            'site_name'                 => trim($_POST['site_name'] ?? 'Social Service Dream'),
            'currency'                  => trim($_POST['currency'] ?? '₹'),
            'support_email'             => trim($_POST['support_email'] ?? 'support@ssdream.com'),
            'support_phone'             => trim($_POST['support_phone'] ?? '+91 98765 43210'),
            'company_contact_whatsapp'  => trim($_POST['company_contact_whatsapp'] ?? '+91 98765 43210'),
            'min_withdrawal'            => max(1, (float)($_POST['min_withdrawal'] ?? 500)),
            'tds_percent'               => max(0, min(100, (float)($_POST['tds_percent'] ?? 5))),
            'admin_charge_percent'      => max(0, min(100, (float)($_POST['admin_charge_percent'] ?? 10))),
            'company_bank_name'         => trim($_POST['company_bank_name'] ?? ''),
            'company_account_holder'    => trim($_POST['company_account_holder'] ?? ''),
            'company_account_number'    => trim($_POST['company_account_number'] ?? ''),
            'company_ifsc_code'         => strtoupper(trim($_POST['company_ifsc_code'] ?? '')),
            'company_upi_id'            => trim($_POST['company_upi_id'] ?? ''),
            'notice_board_text'         => trim($_POST['notice_board_text'] ?? ''),
            'notice_board_status'       => in_array($_POST['notice_board_status'] ?? '', ['active', 'inactive'], true) ? $_POST['notice_board_status'] : 'active'
        ];

        if (saveSystemSettings($dataToSave)) {
            $alert = ['type' => 'success', 'msg' => 'System settings have been successfully updated in database!'];
        } else {
            $alert = ['type' => 'danger', 'msg' => 'Failed to save system settings. Please verify database permissions.'];
        }
    }
}

$allSettings = getAllSystemSettings();
$getVal = fn($key, $default = '') => $allSettings[$key]['value'] ?? $default;

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-white-50 small">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-decoration-none text-gold">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Settings</li>
                </ol>
            </nav>
            <h2 class="h4 text-white fw-bold mb-0">
                <i class="bi bi-gear-fill text-gold me-2"></i> System Configuration &amp; Business Rules
            </h2>
        </div>
    </div>

    <?php if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?php echo ($alert['type'] === 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo e($alert['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

        <div class="row g-4">
            <!-- 1. General & Support Info -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                    <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
                        <h5 class="card-title text-white mb-0 fs-6">
                            <i class="bi bi-sliders me-2 text-gold"></i> General &amp; Support Contact
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Platform / Brand Title</label>
                            <input type="text" name="site_name" class="form-control bg-black text-white border-secondary" 
                                   value="<?php echo e($getVal('site_name', 'Social Service Dream')); ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Currency Symbol</label>
                                <input type="text" name="currency" class="form-control bg-black text-white border-secondary" 
                                       value="<?php echo e($getVal('currency', '₹')); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Currency Code</label>
                                <input type="text" class="form-control bg-black text-white-50 border-secondary" value="INR (Locked)" readonly disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Customer Support Email</label>
                            <input type="email" name="support_email" class="form-control bg-black text-white border-secondary" 
                                   value="<?php echo e($getVal('support_email', 'support@ssdream.com')); ?>" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Support Phone</label>
                                <input type="text" name="support_phone" class="form-control bg-black text-white border-secondary" 
                                       value="<?php echo e($getVal('support_phone', '+91 98765 43210')); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Official WhatsApp Number</label>
                                <input type="text" name="company_contact_whatsapp" class="form-control bg-black text-white border-secondary" 
                                       value="<?php echo e($getVal('company_contact_whatsapp', '+91 98765 43210')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Withdrawal & Payout Rules -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                    <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
                        <h5 class="card-title text-white mb-0 fs-6">
                            <i class="bi bi-cash-coin me-2 text-gold"></i> Payout &amp; Tax Deductions
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Minimum Withdrawal Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-black border-secondary text-gold">₹</span>
                                <input type="number" step="10" min="100" name="min_withdrawal" class="form-control bg-black text-white border-secondary" 
                                       value="<?php echo e($getVal('min_withdrawal', '500')); ?>" required>
                            </div>
                            <small class="text-white-50">Members cannot request a withdrawal lower than this value.</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">TDS Tax Deduction (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="50" name="tds_percent" class="form-control bg-black text-white border-secondary" 
                                           value="<?php echo e($getVal('tds_percent', '5')); ?>" required>
                                    <span class="input-group-text bg-black border-secondary text-white-50">%</span>
                                </div>
                                <small class="text-white-50">Default 5% TDS</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label text-white-50 small">Admin Maintenance Fee (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="50" name="admin_charge_percent" class="form-control bg-black text-white border-secondary" 
                                           value="<?php echo e($getVal('admin_charge_percent', '10')); ?>" required>
                                    <span class="input-group-text bg-black border-secondary text-white-50">%</span>
                                </div>
                                <small class="text-white-50">Default 10% maintenance</small>
                            </div>
                        </div>

                        <!-- Read-only Income distribution info -->
                        <div class="p-3 rounded bg-black border border-secondary border-opacity-25 mt-3">
                            <div class="fw-semibold text-gold small mb-1"><i class="bi bi-shield-lock me-1"></i> Core SS Dream Income Formulas (Locked)</div>
                            <ul class="text-white-50 small mb-0 ps-3">
                                <li>Direct Income: <strong class="text-white">100%</strong></li>
                                <li>Level Income (L2 → L7): <strong class="text-white">50%</strong></li>
                                <li>Sponsor Income (L2 → Unlimited): <strong class="text-white">50%</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Company Bank Accounts for Upgrades -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                    <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
                        <h5 class="card-title text-white mb-0 fs-6">
                            <i class="bi bi-bank me-2 text-gold"></i> Company Bank Details (For Upgrades)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Company Bank Name</label>
                            <input type="text" name="company_bank_name" class="form-control bg-black text-white border-secondary" 
                                   value="<?php echo e($getVal('company_bank_name', 'State Bank of India')); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Account Holder Name</label>
                            <input type="text" name="company_account_holder" class="form-control bg-black text-white border-secondary" 
                                   value="<?php echo e($getVal('company_account_holder', 'Social Service Dream Welfare Foundation')); ?>">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-7">
                                <label class="form-label text-white-50 small">Account Number</label>
                                <input type="text" name="company_account_number" class="form-control bg-black text-white border-secondary font-monospace" 
                                       value="<?php echo e($getVal('company_account_number', '39820100049281')); ?>">
                            </div>
                            <div class="col-5">
                                <label class="form-label text-white-50 small">IFSC Code</label>
                                <input type="text" name="company_ifsc_code" class="form-control bg-black text-white border-secondary font-monospace" 
                                       value="<?php echo e($getVal('company_ifsc_code', 'SBIN0001234')); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Company Official UPI ID</label>
                            <div class="input-group">
                                <span class="input-group-text bg-black border-secondary text-info"><i class="bi bi-qr-code"></i></span>
                                <input type="text" name="company_upi_id" class="form-control bg-black text-white border-secondary font-monospace" 
                                       value="<?php echo e($getVal('company_upi_id', 'ssdream@sbi')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Notice Board & Announcements -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                    <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
                        <h5 class="card-title text-white mb-0 fs-6">
                            <i class="bi bi-megaphone me-2 text-gold"></i> Dashboard Announcement Banner
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Announcement Text</label>
                            <textarea name="notice_board_text" class="form-control bg-black text-white border-secondary" rows="4"><?php echo e($getVal('notice_board_text', 'Welcome to SS Dream! Ensure your KYC is completed to enable instant withdrawals. Direct income 100%, Level & Sponsor income 50%.')); ?></textarea>
                            <small class="text-white-50">This message is prominently displayed at the top of every member dashboard.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Banner Display Status</label>
                            <select name="notice_board_status" class="form-select bg-black text-white border-secondary">
                                <option value="active" <?php echo ($getVal('notice_board_status', 'active') === 'active') ? 'selected' : ''; ?>>Active (Visible to all members)</option>
                                <option value="inactive" <?php echo ($getVal('notice_board_status', 'active') === 'inactive') ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-gold px-4 py-2 fw-bold shadow">
                <i class="bi bi-check-circle-fill me-2"></i> Save All Settings
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
