<?php
/**
 * Social Service Dream - User Portal Header
 * Target: C:\xampp\htdocs\ssdream\user\includes\header.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/security.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Enforce authentication guard
requireUserLogin();

$userId = (int)($_SESSION['user_id'] ?? 0);
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';
$userLevel = $_SESSION['current_level'] ?? 1;
$unreadCount = getUnreadNotificationCount($userId);
$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : 'Member Portal - ' . APP_NAME;
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
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/user/assets/css/user.css">
</head>
<body class="user-body">

<div class="user-wrapper">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="user-content">
        <!-- TOP BAR (User Name, Notification icon, Profile menu, Logout) -->
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom bg-white p-3 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-md-none" id="user-sidebar-toggle" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                        <?php echo e($pageHeading ?? 'Member Dashboard'); ?>
                    </h5>
                    <span class="text-muted small">SS Dream Binary Network &bull; Level <?php echo (int)$userLevel; ?></span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Notification Icon -->
                <div class="position-relative">
                    <a href="<?php echo BASE_URL; ?>/user/dashboard.php#notifications" class="btn btn-light border position-relative p-2 rounded-circle" title="Notifications">
                        <i class="bi bi-bell text-secondary fs-6"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">
                                <?php echo $unreadCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Level Badge -->
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold d-none d-sm-inline-block">
                    <i class="bi bi-award-fill me-1"></i> Level <?php echo (int)$userLevel; ?>
                </span>

                <!-- Profile Menu Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-white bg-white border dropdown-toggle d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 13px;">
                            <?php echo strtoupper(substr($userName, 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-inline fw-semibold text-dark small"><?php echo e($userName); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?php echo e($userName); ?></div>
                                <div class="text-muted small">@<?php echo e($userUsername); ?></div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/user/profile.php">
                                <i class="bi bi-person-circle text-primary me-2"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/user/wallet.php">
                                <i class="bi bi-wallet2 text-success me-2"></i> My Wallet
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/user/kyc.php">
                                <i class="bi bi-card-checklist text-info me-2"></i> KYC Verification
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/index.php" target="_blank">
                                <i class="bi bi-globe text-secondary me-2"></i> Public Website
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <!-- Logout in menu -->
                            <a class="dropdown-item py-2 text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Direct Logout Button -->
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-outline-danger btn-sm px-2.5 py-1.5 rounded-pill d-none d-sm-inline-flex align-items-center gap-1" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-lg-inline">Logout</span>
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="mb-3">
            <?php echo renderFlashMessages(); ?>
        </div>
