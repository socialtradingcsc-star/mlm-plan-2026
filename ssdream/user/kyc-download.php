<?php
/**
 * Social Service Dream - Secure KYC Document Download & View Proxy
 * Target: C:\xampp\htdocs\ssdream\user\kyc-download.php
 * =========================================================================
 * SECTION 12: KYC DOCUMENT PROTECTION
 * Ensures only document owner or super administrator can view/download private KYC files.
 * Prevents direct public access to identity files.
 * =========================================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Must be logged in as User or Admin
$isUser = isUserLoggedIn();
$isAdmin = isAdminLoggedIn();

if (!$isUser && !$isAdmin) {
    http_response_code(403);
    die('403 Forbidden: Authentication required to view sensitive documents.');
}

$kycId = (int)($_GET['id'] ?? 0);
$docType = trim($_GET['type'] ?? 'aadhaar_front'); // 'aadhaar_front', 'aadhaar_back', 'pan_card'

if ($kycId <= 0) {
    http_response_code(404);
    die('404 Not Found: Invalid document request.');
}

$db = getDb();
if (!$db) {
    http_response_code(500);
    die('500 Internal Error: Database connection error.');
}

try {
    $stmt = $db->prepare("SELECT * FROM kyc WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $kycId]);
    $kyc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kyc) {
        http_response_code(404);
        die('404 Not Found: KYC record not found.');
    }

    // Object-Level Authorization Check
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $isOwner = ($isUser && (int)$kyc['user_id'] === $currentUserId);

    if (!$isOwner && !$isAdmin) {
        http_response_code(403);
        die('403 Forbidden: You do not have permission to access this document.');
    }

    // Determine filename column
    $filename = match($docType) {
        'aadhaar_back' => $kyc['aadhaar_back_image'] ?? $kyc['document_file'] ?? '',
        'pan_card'     => $kyc['pan_card_image'] ?? $kyc['document_file'] ?? '',
        default        => $kyc['aadhaar_front_image'] ?? $kyc['document_file'] ?? ''
    };

    if (empty($filename)) {
        http_response_code(404);
        die('404 Not Found: Document file not attached to this record.');
    }

    // Sanitize relative file path to prevent directory traversal
    $filename = basename($filename);
    $filePath = ROOT_PATH . '/uploads/kyc/' . $filename;

    if (!file_exists($filePath) || !is_file($filePath)) {
        http_response_code(404);
        die('404 Not Found: Physical document file missing on server storage.');
    }

    // Determine content type safely
    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
    $fileSize = filesize($filePath);

    // Disable caching for private documents
    header('Cache-Control: private, no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $fileSize);
    header('Content-Disposition: inline; filename="' . $filename . '"');

    readfile($filePath);
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    die('500 Internal Error: Unable to fetch document details.');
}
