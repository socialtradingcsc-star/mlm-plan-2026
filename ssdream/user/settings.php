<?php
/**
 * Social Service Dream - User Profile & Account Security Settings
 * Target: C:\xampp\htdocs\ssdream\user\settings.php
 */

$pageTitle = "User Settings - SS Dream";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireUserAuth();
$userId = (int)$_SESSION['user_id'];
$user = getUserById($userId);
$db = getDb();

// Fetch Profile details
$profile = [];
if ($db) {
    try {
        $pStmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :uid LIMIT 1");
        $pStmt->execute(['uid' => $userId]);
        $profile = $pStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $profile = [];
    }
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF token verification failed.');
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $accNumber = trim($_POST['account_number'] ?? '');
        $ifsc = trim($_POST['ifsc_code'] ?? '');
        $accHolder = trim($_POST['account_holder_name'] ?? '');
        $upi = trim($_POST['upi_id'] ?? '');

        if (empty($fullName) || empty($mobile) || empty($email)) {
            setFlashMessage('error', 'Full Name, Mobile and Email fields are required.');
        } else {
            try {
                // Update users table
                $uStmt = $db->prepare("UPDATE users SET full_name = :fn, mobile = :m, email = :e, updated_at = NOW() WHERE id = :uid");
                $uStmt->execute(['fn' => $fullName, 'm' => $mobile, 'e' => $email, 'uid' => $userId]);

                // Update or Insert user_profiles table
                $pUpsert = $db->prepare("
                    INSERT INTO user_profiles (user_id, bank_name, account_number, ifsc_code, account_holder_name, upi_id, updated_at)
                    VALUES (:uid, :bn, :an, :ifsc, :ah, :upi, NOW())
                    ON DUPLICATE KEY UPDATE 
                        bank_name = :bn, account_number = :an, ifsc_code = :ifsc, account_holder_name = :ah, upi_id = :upi, updated_at = NOW()
                ");
                $pUpsert->execute([
                    'uid'  => $userId,
                    'bn'   => $bankName,
                    'an'   => $accNumber,
                    'ifsc' => $ifsc,
                    'ah'   => $accHolder,
                    'upi'  => $upi
                ]);

                $_SESSION['user_fullname'] = $fullName;
                setFlashMessage('success', 'Your profile and bank details have been updated successfully!');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to update profile: ' . $e->getMessage());
            }
        }
    }
    header('Location: ' . BASE_URL . '/user/settings.php');
    exit();
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF verification failed.');
    } else {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            setFlashMessage('error', 'Please enter your current and new password.');
        } elseif ($newPass !== $confirmPass) {
            setFlashMessage('error', 'New password and confirm password do not match.');
        } elseif (strlen($newPass) < 6) {
            setFlashMessage('error', 'New password must be at least 6 characters long.');
        } else {
            // Verify current password
            if (!password_verify($currentPass, $user['password_hash'] ?? '')) {
                setFlashMessage('error', 'Incorrect current password. Verification failed.');
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                try {
                    $passUpd = $db->prepare("UPDATE users SET password_hash = :p, updated_at = NOW() WHERE id = :uid");
                    $passUpd->execute(['p' => $newHash, 'uid' => $userId]);

                    sendNotification($userId, 'Password Changed Successfully', 'Your account login password was updated.', 'ACCOUNT');
                    setFlashMessage('success', 'Your password has been changed successfully!');
                } catch (PDOException $e) {
                    setFlashMessage('error', 'Failed to change password.');
                }
            }
        }
    }
    header('Location: ' . BASE_URL . '/user/settings.php');
    exit();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-navy mb-0" style="color: #0b192c;">Account Settings &amp; Security</h3>
        <p class="text-muted small mb-0">Manage profile details, bank/UPI payout information, and security credentials.</p>
    </div>
</div>

<?php echo renderFlashMessages(); ?>

<div class="row g-4">
    <!-- Profile & Bank Info Column -->
    <div class="col-lg-7">
        <div class="card border shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i> Profile &amp; Payout Account Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo e($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Username (Read-Only)</label>
                            <input type="text" class="form-control bg-light" value="@<?php echo e($user['username']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile Phone</label>
                            <input type="text" name="mobile" class="form-control" value="<?php echo e($user['mobile']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($user['email']); ?>" required>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-navy border-top pt-3" style="color: #0b192c;">
                        <i class="bi bi-bank text-success me-1"></i> Bank Account &amp; UPI Details (For Withdrawals)
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. State Bank of India" value="<?php echo e($profile['bank_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Account Holder Name</label>
                            <input type="text" name="account_holder_name" class="form-control" placeholder="Name as per Bank Account" value="<?php echo e($profile['account_holder_name'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Account Number</label>
                            <input type="text" name="account_number" class="form-control" placeholder="Account Number" value="<?php echo e($profile['account_number'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control text-uppercase" placeholder="e.g. SBIN0001234" value="<?php echo e($profile['ifsc_code'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">UPI ID (VPA)</label>
                        <input type="text" name="upi_id" class="form-control" placeholder="e.g. mobile@upi or username@okicici" value="<?php echo e($profile['upi_id'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Save Profile &amp; Bank Details
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security & Change Password Column -->
    <div class="col-lg-5">
        <div class="card border shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-key-fill text-danger me-2"></i> Security &amp; Change Password
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current login password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-bold">
                        <i class="bi bi-shield-lock-fill me-1"></i> Update Login Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
