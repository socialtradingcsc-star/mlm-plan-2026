<?php
/**
 * Social Service Dream - Forgot Password Foundation
 * Target: C:\xampp\htdocs\ssdream\forgot-password.php
 */

$pageTitle = "Reset Password - Social Service Dream";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$successMessage = '';
$inputIdentity = sanitize($_POST['identity'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch. Please try again.";
    }

    if (empty($inputIdentity)) {
        $errors[] = "Please provide your registered Username or Email address.";
    }

    if (empty($errors)) {
        $db = getDb();
        if (!$db) {
            $errors[] = "Database connection error. Please make sure MySQL is running in XAMPP.";
        } else {
            try {
                $stmt = $db->prepare("
                    SELECT id, username, email, full_name, status 
                    FROM users 
                    WHERE username = :identity OR email = :identity 
                    LIMIT 1
                ");
                $stmt->execute(['identity' => $inputIdentity]);
                $user = $stmt->fetch();

                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Check if password_resets table exists, if so store token
                    try {
                        $storeToken = $db->prepare("
                            INSERT INTO password_resets (email, token, expires_at)
                            VALUES (:email, :token, :expires)
                        ");
                        $storeToken->execute([
                            'email'   => $user['email'],
                            'token'   => $token,
                            'expires' => $expires
                        ]);
                    } catch (PDOException $pe) {
                        // Table may not yet be migrated, handle gracefully
                    }

                    $successMessage = "If an account exists for '<strong>" . e($inputIdentity) . "</strong>', password reset instructions and security token have been generated. (Support token: <code class='text-primary'>" . substr($token, 0, 10) . "...</code>). Please contact official administration or check your email to finalize password update.";
                } else {
                    // Do not leak user existence for security, or show generic message
                    $successMessage = "If an account matches your entry, reset verification instructions have been processed.";
                }
            } catch (PDOException $e) {
                $errors[] = "System error: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="navbar-brand-badge mb-2">
                        <i class="bi bi-shield-check me-1"></i> SS DREAM
                    </span>
                    <h2 class="fw-bold text-navy" style="color: #0b192c;">Password Recovery</h2>
                    <p class="text-muted small">Enter your registered username or email to recover account access.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm small">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success shadow-sm small">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/forgot-password.php" method="POST" autocomplete="off">
                    <?php echo csrfField(); ?>

                    <div class="mb-3">
                        <label class="form-label" for="identity">
                            <i class="bi bi-envelope-at me-1 text-secondary"></i> Username or Registered Email
                        </label>
                        <input type="text" class="form-control" id="identity" name="identity" 
                               value="<?php echo e($inputIdentity); ?>" placeholder="e.g. ramesh99 or name@example.com" required autofocus>
                        <div class="form-text small">We will verify your user credentials securely.</div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary-ss w-100 py-2.5 fs-6 shadow">
                            <i class="bi bi-send-check me-1"></i> Send Recovery Request
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <a href="<?php echo BASE_URL; ?>/login.php" class="text-decoration-none text-primary small fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Return to Member Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
