<?php
/**
 * Social Service Dream - User Profile Management
 * Target: C:\xampp\htdocs\ssdream\user\profile.php
 */

$pageTitle = "My Profile - Social Service Dream";
$pageHeading = "My Account Profile";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$errors = [];
$success = false;

// Handle Profile Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security session expired. Please refresh and try again.";
    }

    if (empty($errors)) {
        // Collect allowable editable fields only (User ID, Sponsor, Balances, and Incomes are strictly forbidden from modification)
        $profileData = [
            'address'             => sanitize($_POST['address'] ?? ''),
            'city'                => sanitize($_POST['city'] ?? ''),
            'state'               => sanitize($_POST['state'] ?? ''),
            'pincode'             => preg_replace('/[^0-9]/', '', $_POST['pincode'] ?? ''),
            'bank_name'           => sanitize($_POST['bank_name'] ?? ''),
            'account_number'      => sanitize($_POST['account_number'] ?? ''),
            'ifsc_code'           => strtoupper(sanitize($_POST['ifsc_code'] ?? '')),
            'account_holder_name' => sanitize($_POST['account_holder_name'] ?? ''),
            'upi_id'              => sanitize($_POST['upi_id'] ?? ''),
            'pan_number'          => strtoupper(sanitize($_POST['pan_number'] ?? '')),
            'aadhar_number'       => preg_replace('/[^0-9]/', '', $_POST['aadhar_number'] ?? ''),
        ];

        // Also allow updating Full Name in users table if non-empty
        $updatedFullName = sanitize($_POST['full_name'] ?? '');
        if (!empty($updatedFullName)) {
            $db = getDb();
            if ($db) {
                try {
                    $uStmt = $db->prepare("UPDATE users SET full_name = :fn WHERE id = :id");
                    $uStmt->execute(['fn' => $updatedFullName, 'id' => $userId]);
                    $_SESSION['user_fullname'] = $updatedFullName;
                } catch (PDOException $e) {
                    // Ignore or log
                }
            }
        }

        $isUpdated = updateUserProfile($userId, $profileData);
        if ($isUpdated) {
            setFlashMessage('success', "Profile information successfully updated.");
            header("Location: " . BASE_URL . "/user/profile.php");
            exit;
        } else {
            $errors[] = "Failed to update profile details. Please try again.";
        }
    }
}

// Fetch complete user and profile details
$user = getUserById($userId);
if (!$user) {
    die("User profile could not be loaded.");
}
$profile = getUserProfile($userId);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <!-- Left Column: Read-Only System Identity Details -->
    <div class="col-lg-4">
        <!-- Account Overview Card -->
        <div class="card border shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 72px; height: 72px; font-size: 28px;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h5 class="fw-bold text-navy mb-0" style="color: #0b192c;"><?php echo e($user['full_name']); ?></h5>
                <span class="badge bg-light text-secondary border font-monospace mt-1">@<?php echo e($user['username']); ?></span>

                <div class="d-flex justify-content-center gap-2 mt-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                        Status: <?php echo e(ucfirst($user['status'])); ?>
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">
                        Level <?php echo (int)$user['current_level']; ?>
                    </span>
                </div>
            </div>

            <!-- Immutable Business Plan Information -->
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted"><i class="bi bi-hash me-1"></i> User ID (Permanent):</span>
                    <span class="fw-bold font-monospace">#<?php echo (int)$user['id']; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted"><i class="bi bi-person-badge me-1"></i> Sponsor Username:</span>
                    <span class="fw-bold text-primary font-monospace">
                        <?php echo e($user['sponsor_username'] ?: 'Direct / No Sponsor'); ?>
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> Registration Date:</span>
                    <span class="text-secondary"><?php echo date('d M Y, h:i A', strtotime($user['created_at'])); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted"><i class="bi bi-shield-check me-1"></i> Binary Matrix:</span>
                    <span class="badge bg-secondary-subtle text-secondary">Position: <?php echo e(strtoupper($user['position'] ?: 'Auto')); ?></span>
                </li>
            </ul>
        </div>

        <!-- Security Rule Notice -->
        <div class="alert alert-info border-0 shadow-sm small">
            <i class="bi bi-shield-lock-fill me-1 text-primary"></i>
            <strong>Security Rule:</strong> User ID, Sponsor relationship, Wallet balances, and Package level cannot be modified by user request to protect tree integrity.
        </div>
    </div>

    <!-- Right Column: Editable Profile Information Form -->
    <div class="col-lg-8">
        <div class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Profile & Banking Information
                </h6>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm small mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/user/profile.php" method="POST" autocomplete="off">
                    <?php echo csrfField(); ?>

                    <!-- SECTION 1: Personal Contact -->
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3 border-bottom pb-2">
                        1. Personal & Contact Details
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo e($user['full_name']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="username_ro">Username (Fixed)</label>
                            <input type="text" class="form-control bg-light" id="username_ro" 
                                   value="<?php echo e($user['username']); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email_ro">Email Address</label>
                            <input type="email" class="form-control bg-light" id="email_ro" 
                                   value="<?php echo e($user['email']); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="mobile_ro">Mobile Number</label>
                            <input type="text" class="form-control bg-light" id="mobile_ro" 
                                   value="<?php echo e($user['mobile']); ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="address">Postal Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      placeholder="Door / Building, Street, Landmark"><?php echo e($profile['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="city">City / District</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo e($profile['city'] ?? ''); ?>" placeholder="e.g. Mumbai">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="state">State</label>
                            <input type="text" class="form-control" id="state" name="state" 
                                   value="<?php echo e($profile['state'] ?? ''); ?>" placeholder="e.g. Maharashtra">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="pincode">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" maxlength="6" 
                                   value="<?php echo e($profile['pincode'] ?? ''); ?>" placeholder="6 digits">
                        </div>
                    </div>

                    <!-- SECTION 2: Banking & Payout Credentials -->
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3 border-bottom pb-2">
                        2. Banking & Payout Information (For Future Withdrawals)
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                   value="<?php echo e($profile['bank_name'] ?? ''); ?>" placeholder="e.g. State Bank of India">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="account_holder_name">Account Holder Name</label>
                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                                   value="<?php echo e($profile['account_holder_name'] ?? ''); ?>" placeholder="As per passbook">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="account_number">Bank Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" 
                                   value="<?php echo e($profile['account_number'] ?? ''); ?>" placeholder="Enter account number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="ifsc_code">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" 
                                   value="<?php echo e($profile['ifsc_code'] ?? ''); ?>" placeholder="e.g. SBIN0001234">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="upi_id">UPI ID (Optional)</label>
                            <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                   value="<?php echo e($profile['upi_id'] ?? ''); ?>" placeholder="e.g. yourname@okhdfcbank">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pan_number">PAN Number</label>
                            <input type="text" class="form-control" id="pan_number" name="pan_number" maxlength="10" 
                                   value="<?php echo e($profile['pan_number'] ?? ''); ?>" placeholder="ABCDE1234F">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary-ss px-4 py-2">
                            <i class="bi bi-save2 me-1"></i> Save Profile Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
