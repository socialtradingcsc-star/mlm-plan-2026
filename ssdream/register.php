<?php
/**
 * Social Service Dream - User Registration
 * Technology: PHP 8+, MySQL, PDO, Bootstrap 5
 * Target: C:\xampp\htdocs\ssdream\register.php
 */

$pageTitle = "Member Registration - Social Service Dream";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';

// If user already authenticated, redirect to user dashboard
if (isUserLoggedIn()) {
    header("Location: " . BASE_URL . "/user/dashboard.php");
    exit;
}

$errors = [];
$sponsorInput = sanitize($_GET['ref'] ?? ($_POST['sponsor_id'] ?? ''));
$fullNameInput = sanitize($_POST['full_name'] ?? '');
$usernameInput = Security::sanitizeUsername($_POST['username'] ?? '');
$emailInput = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$mobileInput = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security session expired. Please refresh the page and try again.";
    }

    // 2. Validate Inputs
    if (empty($fullNameInput)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($usernameInput)) {
        $errors[] = "Username is required.";
    } elseif (strlen($usernameInput) < 4 || strlen($usernameInput) > 30) {
        $errors[] = "Username must be between 4 and 30 characters (letters, numbers, underscore only).";
    }

    if (empty($emailInput)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address (e.g., name@example.com).";
    }

    if (empty($mobileInput)) {
        $errors[] = "Mobile number is required.";
    } elseif (strlen($mobileInput) !== 10) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Confirm password does not match the chosen password.";
    }

    // 3. Database operations
    if (empty($errors)) {
        $db = getDb();
        if (!$db) {
            $errors[] = "Database connection unavailable. Please ensure MySQL is running in XAMPP.";
        } else {
            try {
                // Check if username is already registered
                if (isUsernameTaken($usernameInput)) {
                    $errors[] = "Username '{$usernameInput}' is already taken. Please choose another username.";
                }

                // Check if email is already registered
                if (isEmailTaken($emailInput)) {
                    $errors[] = "Email address '{$emailInput}' is already registered. Please log in or use a different email.";
                }

                // Check if mobile number is already registered
                if (isMobileTaken($mobileInput)) {
                    $errors[] = "Mobile number '{$mobileInput}' is already registered with another account.";
                }

                // Verify Sponsor ID if supplied
                $sponsorId = null;
                $sponsorUsername = null;
                if (!empty($sponsorInput)) {
                    $sponsor = getSponsorByIdentifier($sponsorInput);
                    if ($sponsor) {
                        if ($sponsor['status'] !== 'active') {
                            $errors[] = "Sponsor account (@{$sponsor['username']}) is not active. Please use an active sponsor ID.";
                        } else {
                            $sponsorId = (int)$sponsor['id'];
                            $sponsorUsername = $sponsor['username'];
                        }
                    } else {
                        $errors[] = "Invalid Sponsor ID. The specified sponsor '{$sponsorInput}' does not exist.";
                    }
                }

                // Binary Placement Handling (Part 4)
                $binaryParentInput = sanitize($_POST['binary_parent'] ?? '');
                $binaryPositionInput = strtolower(trim(sanitize($_POST['binary_position'] ?? '')));
                $parentId = null;
                $position = null;

                if (!empty($binaryParentInput) && !empty($binaryPositionInput)) {
                    // Specific manual placement requested
                    $parentUser = getSponsorByIdentifier($binaryParentInput);
                    if (!$parentUser) {
                        $errors[] = "Specified binary parent '{$binaryParentInput}' does not exist.";
                    } else {
                        $parentId = (int)$parentUser['id'];
                        $position = $binaryPositionInput;
                        $placementCheck = validateBinaryPlacement($parentId, $position);
                        if (!$placementCheck['valid']) {
                            $errors[] = $placementCheck['message'];
                        }
                    }
                } elseif ($sponsorId !== null) {
                    // Default technical placement: Next available binary position under sponsor's tree
                    $autoSlot = findNextAvailableBinarySlot($sponsorId);
                    if ($autoSlot) {
                        $parentId = (int)$autoSlot['parent_id'];
                        $position = $autoSlot['position'];
                    }
                }

                // Proceed with account creation if no validation conflicts
                if (empty($errors)) {
                    $db->beginTransaction();

                    // Hash password using BCRYPT
                    $hashedPassword = Security::hashPassword($password);

                    // Insert user record with sponsor_id, parent_id and position
                    $insertUser = $db->prepare("
                        INSERT INTO users (
                            sponsor_id, sponsor_username, parent_id, position, 
                            username, full_name, email, mobile, password, 
                            current_level, status, created_at
                        ) VALUES (
                            :sponsor_id, :sponsor_username, :parent_id, :position,
                            :username, :full_name, :email, :mobile, :password, 
                            1, 'active', NOW()
                        )
                    ");

                    $insertUser->execute([
                        'sponsor_id'       => $sponsorId,
                        'sponsor_username' => $sponsorUsername,
                        'parent_id'        => $parentId,
                        'position'         => $position,
                        'username'         => $usernameInput,
                        'full_name'        => $fullNameInput,
                        'email'            => $emailInput,
                        'mobile'           => $mobileInput,
                        'password'         => $hashedPassword
                    ]);

                    $newUserId = (int)$db->lastInsertId();

                    // Create user profile record
                    $insertProfile = $db->prepare("
                        INSERT INTO user_profiles (user_id, created_at)
                        VALUES (:uid, NOW())
                    ");
                    $insertProfile->execute(['uid' => $newUserId]);

                    // Create user wallet record (defaults to ₹0.00, no income calculated in Part 4)
                    $insertWallet = $db->prepare("
                        INSERT INTO wallets (user_id, balance, total_income, direct_income, level_income, sponsor_income, total_withdrawn, created_at)
                        VALUES (:uid, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NOW())
                    ");
                    $insertWallet->execute(['uid' => $newUserId]);

                    // Initialize binary tree placement record
                    $insertTree = $db->prepare("
                        INSERT INTO binary_tree (user_id, parent_id, created_at)
                        VALUES (:uid, :parent_id, NOW())
                    ");
                    $insertTree->execute(['uid' => $newUserId, 'parent_id' => $parentId]);

                    // If placed under a parent, update parent's child slot in binary_tree
                    if ($parentId !== null && $position !== null) {
                        $childCol = ($position === 'left') ? 'left_child_id' : 'right_child_id';
                        $updateParent = $db->prepare("
                            UPDATE binary_tree 
                            SET {$childCol} = :uid, updated_at = NOW() 
                            WHERE user_id = :pid
                        ");
                        $updateParent->execute(['uid' => $newUserId, 'pid' => $parentId]);
                    }

                    // PART 5: Trigger 100% Direct Income to sponsor on Level 1 activation (₹1,200)
                    if ($sponsorId !== null && function_exists('calculateDirectIncome')) {
                        calculateDirectIncome($newUserId, $db);
                    }

                    $db->commit();

                    // Flash message and redirect to login
                    setFlashMessage('success', "Registration successful! Your account has been created. Please sign in with your username and password.");
                    header("Location: " . BASE_URL . "/login.php");
                    exit;
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errors[] = "An error occurred while creating your account: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="navbar-brand-badge mb-2">
                        <i class="bi bi-shield-check me-1"></i> SS DREAM
                    </span>
                    <h2 class="fw-bold text-navy" style="color: #0b192c;">Member Registration</h2>
                    <p class="text-muted small">
                        Join Social Service Dream with Joining Package (<?php echo formatCurrency(JOINING_PACKAGE_AMOUNT); ?>) and activate your member account.
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please resolve the following:</div>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/register.php" method="POST" autocomplete="off" novalidate>
                    <?php echo csrfField(); ?>

                    <div class="row g-3">
                        <!-- Sponsor ID -->
                        <div class="col-12">
                            <label class="form-label" for="sponsor_id">
                                <i class="bi bi-person-badge text-primary me-1"></i> Sponsor Username / Referral ID (Optional)
                            </label>
                            <input type="text" class="form-control" id="sponsor_id" name="sponsor_id" 
                                   value="<?php echo e($sponsorInput); ?>" placeholder="Enter Sponsor Username if you have one">
                            <div class="form-text small">Leave blank if you were not referred by an existing member.</div>
                        </div>

                        <!-- Custom Binary Placement (Optional) -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-light">
                                <a class="text-decoration-none d-flex justify-content-between align-items-center text-dark" data-bs-toggle="collapse" href="#binaryPlacementCollapse" role="button" aria-expanded="false">
                                    <span class="fw-semibold small"><i class="bi bi-diagram-3 text-primary me-1"></i> Custom Binary Placement (Optional)</span>
                                    <span class="text-muted small">Auto-allocate under Sponsor <i class="bi bi-chevron-down ms-1"></i></span>
                                </a>
                                <div class="collapse mt-2" id="binaryPlacementCollapse">
                                    <p class="text-muted text-xxs mb-2">Leave blank for default automatic placement under your sponsor's 2x2 network. Or specify a binary parent username and leg:</p>
                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <input type="text" class="form-control form-control-sm" name="binary_parent" placeholder="Parent Username (Optional)">
                                        </div>
                                        <div class="col-md-5">
                                            <select class="form-select form-select-sm" name="binary_position">
                                                <option value="">Auto (First Open Slot)</option>
                                                <option value="left">Left Leg (Position)</option>
                                                <option value="right">Right Leg (Position)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">
                                <i class="bi bi-person text-secondary me-1"></i> Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo e($fullNameInput); ?>" placeholder="e.g. Ramesh Kumar" required>
                        </div>

                        <!-- Desired Username -->
                        <div class="col-md-6">
                            <label class="form-label" for="username">
                                <i class="bi bi-at text-secondary me-1"></i> Desired Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo e($usernameInput); ?>" placeholder="e.g. ramesh99" required>
                            <div class="form-text small">Must be unique (min 4 characters).</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label" for="email">
                                <i class="bi bi-envelope text-secondary me-1"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo e($emailInput); ?>" placeholder="name@example.com" required>
                            <div class="form-text small">Must be a valid, unique email address.</div>
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-6">
                            <label class="form-label" for="mobile">
                                <i class="bi bi-phone text-secondary me-1"></i> Mobile Number (10 Digits) <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                   value="<?php echo e($mobileInput); ?>" placeholder="e.g. 9876543210" maxlength="10" required>
                            <div class="form-text small">10-digit number for SMS alerts & support.</div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label" for="password">
                                <i class="bi bi-lock text-secondary me-1"></i> Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Minimum 6 characters" required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label class="form-label" for="confirm_password">
                                <i class="bi bi-lock-fill text-secondary me-1"></i> Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Re-enter password" required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="confirm_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Business Plan Package Notice -->
                        <div class="col-12 mt-3">
                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold text-dark">Starting Package:</span> Level 1 Joining Package
                                    <div class="text-muted small">Binary Team: 2 Members &bull; 100% Direct Income Structure</div>
                                </div>
                                <span class="fs-5 fw-bold text-primary"><?php echo formatCurrency(JOINING_PACKAGE_AMOUNT); ?></span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary-ss w-100 py-2.5 fs-6 shadow">
                                <i class="bi bi-person-plus-fill me-1"></i> Complete Registration
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Already have an account? 
                        <a href="<?php echo BASE_URL; ?>/login.php" class="text-primary fw-semibold">Sign In to Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
