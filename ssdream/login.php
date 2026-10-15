<?php
/**
 * Social Service Dream - Member Login
 * Technology: PHP 8+, MySQL, PDO, Secure Sessions
 * Target: C:\xampp\htdocs\ssdream\login.php
 */

$pageTitle = "Member Login - Social Service Dream";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';

// If user already logged in, redirect directly to member dashboard
if (isUserLoggedIn()) {
    header("Location: " . BASE_URL . "/user/dashboard.php");
    exit;
}

$errors = [];
$loginInput = sanitize($_POST['login_identity'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security session expired. Please refresh and try again.";
    }

    $password = $_POST['password'] ?? '';

    if (empty($loginInput)) {
        $errors[] = "Username or Email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $db = getDb();
        if (!$db) {
            $errors[] = "Database connection error. Please make sure MySQL is started in XAMPP.";
        } else {
            try {
                // Query user by username OR email
                $stmt = $db->prepare("
                    SELECT id, username, full_name, email, password, current_level, status 
                    FROM users 
                    WHERE username = :identity OR email = :identity 
                    LIMIT 1
                ");
                $stmt->execute(['identity' => $loginInput]);
                $user = $stmt->fetch();

                if ($user && Security::verifyPassword($password, $user['password'])) {
                    // Check if account is suspended/blocked
                    if ($user['status'] === 'blocked') {
                        $errors[] = "Your account has been suspended by administration. Please contact support.";
                    } else {
                        // Generate a clean session ID to prevent fixation attacks
                        session_regenerate_id(true);

                        // Store verified session variables
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id']        = (int)$user['id'];
                        $_SESSION['user_username']  = $user['username'];
                        $_SESSION['user_fullname']  = $user['full_name'];
                        $_SESSION['user_email']     = $user['email'];
                        $_SESSION['current_level']  = (int)$user['current_level'];

                        setFlashMessage('success', "Welcome back, " . $user['full_name'] . "!");
                        header("Location: " . BASE_URL . "/user/dashboard.php");
                        exit;
                    }
                } else {
                    $errors[] = "Invalid login credentials. Please check your username/email and password.";
                }
            } catch (PDOException $e) {
                $errors[] = "Authentication error: " . $e->getMessage();
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
                    <h2 class="fw-bold text-navy" style="color: #0b192c;">Member Sign In</h2>
                    <p class="text-muted small">Access your team dashboard, wallet balance, and binary status.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/login.php" method="POST" autocomplete="off">
                    <?php echo csrfField(); ?>

                    <!-- Username or Email -->
                    <div class="mb-3">
                        <label class="form-label" for="login_identity">
                            <i class="bi bi-person me-1 text-secondary"></i> Username or Email
                        </label>
                        <input type="text" class="form-control" id="login_identity" name="login_identity" 
                               value="<?php echo e($loginInput); ?>" placeholder="Enter your username or registered email" required autofocus>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="password">
                                <i class="bi bi-lock me-1 text-secondary"></i> Password
                            </label>
                            <a href="<?php echo BASE_URL; ?>/forgot-password.php" class="text-primary small text-decoration-none">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary-ss w-100 py-2.5 fs-6 shadow">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Don't have an account yet? 
                        <a href="<?php echo BASE_URL; ?>/register.php" class="text-primary fw-semibold">Join SS Dream (₹1,200)</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
