<?php
/**
 * Social Service Dream - Users CSV Export
 * Target: C:\xampp\htdocs\ssdream\admin\export-users.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'search'    => trim($_GET['search'] ?? ''),
    'level'     => trim($_GET['level'] ?? ''),
    'status'    => trim($_GET['status'] ?? ''),
    'sponsor'   => trim($_GET['sponsor'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$reportData = getReportUsers($filters, 10000, 0);
$users = $reportData['records'];

$filename = 'ssdream_users_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

// Write CSV Header
fputcsv($output, [
    'User ID',
    'Username',
    'Full Name',
    'Mobile',
    'Email',
    'Sponsor Username',
    'Sponsor Name',
    'Current Level',
    'Status',
    'Registration Date'
]);

foreach ($users as $u) {
    fputcsv($output, [
        sanitizeCsvCell($u['id']),
        sanitizeCsvCell($u['username']),
        sanitizeCsvCell($u['full_name']),
        sanitizeCsvCell($u['mobile']),
        sanitizeCsvCell($u['email']),
        sanitizeCsvCell($u['sponsor_username'] ?? 'Root'),
        sanitizeCsvCell($u['sponsor_name'] ?? 'Root System'),
        sanitizeCsvCell('Level ' . $u['current_level']),
        sanitizeCsvCell(strtoupper($u['status'])),
        sanitizeCsvCell($u['created_at'])
    ]);
}

fclose($output);
exit();
