<?php
/**
 * Social Service Dream - Transactions Ledger CSV Export
 * Target: C:\xampp\htdocs\ssdream\admin\export-transactions.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'type'      => trim($_GET['type'] ?? ''),
    'category'  => trim($_GET['category'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$reportData = getReportTransactions($filters, 10000, 0);
$txs = $reportData['records'];

$filename = 'ssdream_transactions_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Transaction ID',
    'Username',
    'Full Name',
    'Transaction Type',
    'Category',
    'Amount (INR)',
    'Balance Before',
    'Balance After',
    'Reference Number',
    'Description',
    'Status',
    'Created Date'
]);

foreach ($txs as $t) {
    fputcsv($output, [
        sanitizeCsvCell($t['id']),
        sanitizeCsvCell($t['username']),
        sanitizeCsvCell($t['full_name']),
        sanitizeCsvCell(strtoupper($t['transaction_type'])),
        sanitizeCsvCell($t['category']),
        sanitizeCsvCell(number_format((float)$t['amount'], 2, '.', '')),
        sanitizeCsvCell(number_format((float)($t['balance_before'] ?? 0), 2, '.', '')),
        sanitizeCsvCell(number_format((float)($t['balance_after'] ?? 0), 2, '.', '')),
        sanitizeCsvCell($t['reference_no'] ?? ('TXN-' . $t['id'])),
        sanitizeCsvCell($t['description']),
        sanitizeCsvCell(strtoupper($t['status'] ?? 'COMPLETED')),
        sanitizeCsvCell($t['created_at'])
    ]);
}

fclose($output);
exit();
