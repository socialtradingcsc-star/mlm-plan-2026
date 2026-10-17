<?php
/**
 * Social Service Dream - Income Earnings CSV Export
 * Target: C:\xampp\htdocs\ssdream\admin\export-income.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'type'      => trim($_GET['type'] ?? ''),
    'level'     => trim($_GET['level'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$reportData = getReportIncomes($filters, 10000, 0);
$incomes = $reportData['records'];

$filename = 'ssdream_income_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Income ID',
    'Beneficiary Username',
    'Beneficiary Name',
    'Income Type',
    'Source Username',
    'Source Name',
    'Level',
    'Amount (INR)',
    'Transaction Reference',
    'Date & Time'
]);

foreach ($incomes as $inc) {
    fputcsv($output, [
        sanitizeCsvCell($inc['id']),
        sanitizeCsvCell($inc['username']),
        sanitizeCsvCell($inc['full_name']),
        sanitizeCsvCell($inc['income_type']),
        sanitizeCsvCell($inc['source_username'] ?? 'N/A'),
        sanitizeCsvCell($inc['source_full_name'] ?? 'N/A'),
        sanitizeCsvCell('Level ' . $inc['level']),
        sanitizeCsvCell(number_format((float)$inc['amount'], 2, '.', '')),
        sanitizeCsvCell($inc['transaction_ref'] ?? ('INC-' . $inc['id'])),
        sanitizeCsvCell($inc['created_at'])
    ]);
}

fclose($output);
exit();
