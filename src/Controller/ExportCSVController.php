<?php
require_once __DIR__ . '/../../autoload.php';

use App\Connection\PDOConnection;
use App\Repository\FormSubmissionsRepository;

$formId = isset($_GET['form_id']) ? (int)$_GET['form_id'] : null;
if (!$formId) {
    http_response_code(400);
    die("form_id obrigatório");
}

$db = (new PDOConnection())->getPDO();
$repo = new FormSubmissionsRepository($db);

$fields = $repo->getFieldsByFormId($formId);
$rows   = $repo->getAllSubmissionsForExport($formId);

// Agrupa por submission_id
$grouped = [];
foreach ($rows as $row) {
    $grouped[$row['id']]['submitted_at']            = $row['submitted_at'];
    $grouped[$row['id']]['values'][$row['field_id']] = $row['field_value'];
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="export_' . $formId . '_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para Excel

// Cabeçalho
$header = ['ID', 'Data'];
foreach ($fields as $field) {
    $header[] = $field['label'];
}
fputcsv($output, $header);

// Linhas
foreach ($grouped as $submissionId => $data) {
    $line = [$submissionId, date('d/m/Y H:i', strtotime($data['submitted_at']))];
    foreach ($fields as $field) {
        $line[] = $data['values'][$field['id']] ?? '';
    }
    fputcsv($output, $line);
}

fclose($output);
exit;
