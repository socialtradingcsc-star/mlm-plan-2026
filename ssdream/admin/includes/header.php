<?php
/**
 * Social Service Dream - Admin Portal Header
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/security.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Enforce admin authentication guard
Security::requireAdminAuth();

$adminUsername = $_SESSION['admin_username'] ?? 'admin';
$adminFullName = $_SESSION['admin_name'] ?? 'System Administrator';
$pageTitle = isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Control Panel - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Base + Admin CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="admin-content">
        <!-- Admin Top Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-md-none" id="admin-sidebar-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h4 class="fw-bold mb-0 text-navy" style="color: #0b192c;">Administrator Control Center</h4>
                    <span class="text-muted small">SS Dream Management & Governance</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-lock-fill me-1"></i> Super Admin
                </span>
                <div class="dropdown">
                    <button class="btn btn-white bg-white border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-fill-gear text-primary"></i>
                        <span class="d-none d-sm-inline fw-semibold"><?php echo e($adminFullName); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header">Logged as @<?php echo e($adminUsername); ?></h6></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/index.php" target="_blank"><i class="bi bi-globe me-2"></i>Visit Frontend</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/admin/login.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Admin Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php echo renderFlashMessages(); ?>
