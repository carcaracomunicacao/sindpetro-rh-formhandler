<?php
require_once __DIR__ . '/../../autoload.php';

use App\Connection\PDOConnection;
use App\Repository\FormRepository;
use App\Repository\FormSubmissionsRepository;
use App\Repository\FieldOptionsRepository;
use App\Repository\SubmissionValuesRepository;
use App\Repository\FormFieldsRepository;
use App\Service\SubmitService;

header('Content-Type: application/json');

try {
    $db = (new PDOConnection())->getPDO();

    // Instanciamos o Service com as dependências necessárias
    $service = new SubmitService(
        $db,
        new FormRepository($db),
        new FormFieldsRepository($db),
        new FieldOptionsRepository($db),
        new FormSubmissionsRepository($db),
        new SubmissionValuesRepository($db)
    );
    // O Service processa tudo e retorna o ID da submissão ou lança exceção
    $submissionId = $service->handle($_POST, $_FILES);

    echo json_encode([
        'success' => true,
        'message' => 'Sua ficha foi registrada com sucesso!',
        'id' => $submissionId,
        'form_uuid' => $_POST['form_uuid']
    ]);
} catch (\Exception $e) {
    // Retorna o erro amigável para o SweetAlert mostrar
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
