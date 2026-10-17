<?php
/**
 * Social Service Dream - Withdrawals CSV Export
 * Target: C:\xampp\htdocs\ssdream\admin\export-withdrawals.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'status'    => trim($_GET['status'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$reportData = getReportWithdrawals($filters, 10000, 0);
$withdrawals = $reportData['records'];

$filename = 'ssdream_withdrawals_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Withdrawal ID',
    'Username',
    'Full Name',
    'Gross Amount (INR)',
    'TDS Deduction (5%)',
    'Admin Charge (5%)',
    'Net Payout (INR)',
    'Payment Mode',
    'Status',
    'Transaction Reference / UTR',
    'Requested Date'
]);

foreach ($withdrawals as $w) {
    fputcsv($output, [
        sanitizeCsvCell($w['id']),
        sanitizeCsvCell($w['username']),
        sanitizeCsvCell($w['full_name']),
        sanitizeCsvCell(number_format((float)$w['amount'], 2, '.', '')),
        sanitizeCsvCell(number_format((float)($w['tds_deduction'] ?? 0), 2, '.', '')),
        sanitizeCsvCell(number_format((float)($w['admin_charge'] ?? 0), 2, '.', '')),
        sanitizeCsvCell(number_format((float)$w['net_amount'], 2, '.', '')),
        sanitizeCsvCell($w['payment_mode'] ?? 'Bank/UPI'),
        sanitizeCsvCell(strtoupper($w['status'])),
        sanitizeCsvCell($w['transaction_ref'] ?? ('WTH-' . $w['id'])),
        sanitizeCsvCell($w['created_at'])
    ]);
}

fclose($output);
exit();
