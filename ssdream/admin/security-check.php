<?php
/**
 * Social Service Dream - Administrator Technical Security Health Check
 * Target: C:\xampp\htdocs\ssdream\admin\security-check.php
 * =========================================================================
 * SECTION 36: TECHNICAL SECURITY CHECKLIST PAGE
 * Diagnostic console verifying PHP session security, upload permissions,
 * CSRF token generation, database connectivity, and environment modes.
 * =========================================================================
 */

$pageTitle = "Security Health Check - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Strict Admin Auth Guard
Security::requireAdminAuth();

// Gather Technical Security Checks
$phpVersion = PHP_VERSION;
$dbStatus = Database::isConnected();
$dbError = Database::getError();

$sessionHttpOnly = ini_get('session.cookie_httponly') == '1';
$sessionUseOnlyCookies = ini_get('session.use_only_cookies') == '1';
$sessionSameSite = ini_get('session.cookie_samesite') ?: 'Not Set';

$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');
$uploadsDir = ROOT_PATH . '/uploads/kyc';
$uploadsWritable = is_dir($uploadsDir) && is_writable($uploadsDir);

$csrfActive = !empty(Security::getCsrfToken());
$appEnv = defined('APP_ENV') ? APP_ENV : 'development';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-decoration-none text-muted small">&larr; Back to Dashboard</a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">System Security Health Diagnostic</h3>
        <p class="text-muted small mb-0">Production security configuration audit and technical environment checklist.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/audit-logs.php" class="btn btn-dark btn-sm rounded-pill px-3">
        <i class="bi bi-shield-check me-1"></i> Admin Audit Logs
    </a>
</div>

<div class="row g-4 mb-4">
    <!-- 1. Authentication & Session Security -->
    <div class="col-md-6">
        <div class="card border shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-lock-fill text-primary me-2"></i> Session &amp; Cookie Security
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">Cookie HttpOnly Directive</div>
                            <div class="text-muted text-xxs">Prevents client-side XSS script cookie theft</div>
                        </div>
                        <?php if ($sessionHttpOnly): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> ENABLED (Secure)</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">DISABLED</span>
                        <?php endif; ?>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">Cookie Use Only Cookies</div>
                            <div class="text-muted text-xxs">Prevents session ID exposure in URL parameters</div>
                        </div>
                        <?php if ($sessionUseOnlyCookies): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> ENABLED</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">DISABLED</span>
                        <?php endif; ?>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">Cookie SameSite Policy</div>
                            <div class="text-muted text-xxs">Protects against Cross-Site Request Forgery</div>
                        </div>
                        <span class="badge bg-info-subtle text-info border font-monospace"><?php echo e($sessionSameSite); ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">CSRF Protection Engine</div>
                            <div class="text-muted text-xxs">Session token generator &amp; validator active</div>
                        </div>
                        <?php if ($csrfActive): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> ACTIVE</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">INACTIVE</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 2. Server Environment & Database Integrity -->
    <div class="col-md-6">
        <div class="card border shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-server text-primary me-2"></i> Database &amp; Runtime Environment
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">PHP Version</div>
                            <div class="text-muted text-xxs">Runtime version (PHP 8.0+ required)</div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border font-monospace">PHP <?php echo $phpVersion; ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">MySQL PDO Prepared Statements</div>
                            <div class="text-muted text-xxs">PDO Emulation Disabled (Native Prepared Statements)</div>
                        </div>
                        <?php if ($dbStatus): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-database-check me-1"></i> CONNECTED</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">DISCONNECTED</span>
                        <?php endif; ?>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">Application Environment (APP_ENV)</div>
                            <div class="text-muted text-xxs">Development vs Production mode setting</div>
                        </div>
                        <span class="badge bg-dark text-white text-uppercase"><?php echo e($appEnv); ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">HTTPS SSL Protocol</div>
                            <div class="text-muted text-xxs">Encrypted HTTP transport stream</div>
                        </div>
                        <?php if ($isHttps): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-lock-fill me-1"></i> HTTPS Active</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">HTTP (Local XAMPP)</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- 3. Upload Directory & Storage Permissions -->
<div class="card border shadow-sm bg-white mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
            <i class="bi bi-folder-symlink text-primary me-2"></i> Storage &amp; Upload File Permissions
        </h6>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <div class="fw-bold">KYC Upload Directory Writable</div>
                    <div class="text-muted text-xxs"><code><?php echo e($uploadsDir); ?></code></div>
                </div>
                <?php if ($uploadsWritable): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> Writable &amp; Protected</span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Not Writable</span>
                <?php endif; ?>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <div class="fw-bold">Upload Max File Size</div>
                    <div class="text-muted text-xxs">PHP ini limit for single file uploads</div>
                </div>
                <span class="badge bg-secondary-subtle text-dark border font-monospace"><?php echo e($uploadMax); ?> (Post: <?php echo e($postMax); ?>)</span>
            </li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
