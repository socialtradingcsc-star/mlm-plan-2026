<?php
/**
 * Social Service Dream - User KYC Verification Foundation
 * Target: C:\xampp\htdocs\ssdream\user\kyc.php
 */

$pageTitle = "KYC Verification - Social Service Dream";
$pageHeading = "KYC Document Verification";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$errors = [];
$successMessage = '';

// Allowed extensions and MIME types
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$maxFileSize = 3 * 1024 * 1024; // 3 MB

// Fetch current KYC record
$currentKyc = getUserKyc($userId);

// Handle KYC Document Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security session expired. Please refresh the page and try again.";
    }

    $docType = sanitize($_POST['document_type'] ?? 'Aadhar Card');
    $docNumber = strtoupper(sanitize($_POST['document_number'] ?? ''));

    if (empty($docNumber)) {
        $errors[] = "Please provide the official Document Number.";
    }

    // Validate uploaded file
    if (!isset($_FILES['kyc_document']) || $_FILES['kyc_document']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = $_FILES['kyc_document']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Please select a document file to upload.";
        } else {
            $errors[] = "File upload failed with system code: " . $uploadError;
        }
    } else {
        $file = $_FILES['kyc_document'];

        // Check file size
        if ($file['size'] > $maxFileSize) {
            $errors[] = "File size exceeds the 3MB limit. Please upload a smaller file.";
        }

        // Validate extension
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExtensions, true)) {
            $errors[] = "Invalid file format. Allowed file types: JPG, JPEG, PNG, or PDF.";
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimeTypes, true)) {
            $errors[] = "Uploaded file content is not a valid image or PDF document.";
        }

        // Save safely if no errors
        if (empty($errors)) {
            $targetDir = ROOT_PATH . '/uploads/kyc';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generate cryptographically random secure filename
            $safeFileName = 'kyc_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
            $destination = $targetDir . '/' . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $relativeFilePath = 'uploads/kyc/' . $safeFileName;
                $db = getDb();

                if ($db) {
                    try {
                        // Check if record exists
                        if ($currentKyc) {
                            $stmt = $db->prepare("
                                UPDATE kyc SET 
                                    pan_card_path = :path,
                                    status = 'pending',
                                    admin_notes = NULL,
                                    updated_at = NOW()
                                WHERE user_id = :uid
                            ");
                            $stmt->execute(['path' => $relativeFilePath, 'uid' => $userId]);
                        } else {
                            $stmt = $db->prepare("
                                INSERT INTO kyc (user_id, pan_card_path, status, submitted_at)
                                VALUES (:uid, :path, 'pending', NOW())
                            ");
                            $stmt->execute(['uid' => $userId, 'path' => $relativeFilePath]);
                        }

                        // Also record Document Number in user_profiles
                        if ($docType === 'PAN Card') {
                            $upProfile = $db->prepare("UPDATE user_profiles SET pan_number = :num WHERE user_id = :uid");
                            $upProfile->execute(['num' => $docNumber, 'uid' => $userId]);
                        } elseif ($docType === 'Aadhar Card') {
                            $upProfile = $db->prepare("UPDATE user_profiles SET aadhar_number = :num WHERE user_id = :uid");
                            $upProfile->execute(['num' => $docNumber, 'uid' => $userId]);
                        }

                        setFlashMessage('success', "Your KYC document has been uploaded securely and is now pending administrator review.");
                        header("Location: " . BASE_URL . "/user/kyc.php");
                        exit;
                    } catch (PDOException $e) {
                        $errors[] = "Database error while storing KYC record: " . $e->getMessage();
                    }
                }
            } else {
                $errors[] = "Failed to store uploaded file on the server. Please check directory permissions.";
            }
        }
    }
}

// Refresh KYC data
$currentKyc = getUserKyc($userId);
$kycStatus = $currentKyc['status'] ?? 'not_submitted';

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card border shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-shield-check text-primary me-2"></i> KYC Verification Center
                </h6>

                <!-- KYC Status Badge -->
                <?php if ($kycStatus === 'approved'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i> Status: Approved
                    </span>
                <?php elseif ($kycStatus === 'pending'): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill">
                        <i class="bi bi-hourglass-split me-1"></i> Status: Pending Approval
                    </span>
                <?php elseif ($kycStatus === 'rejected'): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill">
                        <i class="bi bi-x-circle-fill me-1"></i> Status: Rejected
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill">
                        <i class="bi bi-dash-circle me-1"></i> Status: Not Submitted
                    </span>
                <?php endif; ?>
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

                <!-- Current Status Banner -->
                <?php if ($kycStatus === 'approved'): ?>
                    <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-4 text-success d-flex align-items-center gap-3">
                        <i class="bi bi-patch-check-fill fs-2"></i>
                        <div>
                            <div class="fw-bold">Your Account is Fully KYC Verified!</div>
                            <div class="small text-secondary">Your identity documents have been approved by the Social Service Dream compliance team.</div>
                        </div>
                    </div>
                <?php elseif ($kycStatus === 'pending'): ?>
                    <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-4 text-dark d-flex align-items-center gap-3">
                        <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        <div>
                            <div class="fw-bold">Document Submitted & Awaiting Verification</div>
                            <div class="small text-secondary">Submitted on: <?php echo date('d M Y, h:i A', strtotime($currentKyc['submitted_at'])); ?>. Verification typically takes 24-48 business hours.</div>
                        </div>
                    </div>
                <?php elseif ($kycStatus === 'rejected'): ?>
                    <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mb-4 text-danger d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-octagon-fill fs-2"></i>
                        <div>
                            <div class="fw-bold">Document Rejected by Administrator</div>
                            <div class="small text-secondary">Reason: <?php echo e($currentKyc['admin_notes'] ?? 'Unclear image or mismatched document number.'); ?>. Please submit a valid document below.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- KYC Upload Form -->
                <?php if ($kycStatus !== 'approved'): ?>
                    <form action="<?php echo BASE_URL; ?>/user/kyc.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <?php echo csrfField(); ?>

                        <div class="mb-3">
                            <label class="form-label" for="document_type">
                                Document Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="document_type" name="document_type" required>
                                <option value="Aadhar Card">Aadhar Card (Front & Back)</option>
                                <option value="PAN Card">PAN Card</option>
                                <option value="Voter ID">Voter ID</option>
                                <option value="Passport">Passport</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="document_number">
                                Document Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="document_number" name="document_number" 
                                   placeholder="e.g. 12-digit Aadhar or 10-digit PAN" required>
                            <div class="form-text small">Enter the exact registration number printed on your document.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="kyc_document">
                                Upload Document File <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="kyc_document" name="kyc_document" 
                                   accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="form-text small">
                                Accepted formats: <strong>JPG, JPEG, PNG, PDF</strong> (Maximum file size: 3MB).
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border mb-4 text-secondary small">
                            <i class="bi bi-shield-lock me-1 text-primary"></i>
                            <strong>Privacy Guarantee:</strong> Documents are stored in protected server storage (<code>uploads/kyc/</code>) and never exposed publicly.
                        </div>

                        <button type="submit" class="btn btn-primary-ss w-100 py-2.5 fs-6 shadow">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit Document for KYC Verification
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted small mb-0 text-center py-3">
                        <i class="bi bi-shield-check fs-4 text-success d-block mb-1"></i>
                        No further document submission required. Your KYC verification is complete.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
