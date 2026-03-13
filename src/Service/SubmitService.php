<?php

namespace App\Service;

use App\Repository\FormRepository;
use App\Repository\FormFieldsRepository;
use App\Repository\FieldOptionsRepository;
use App\Repository\FormSubmissionsRepository;
use App\Repository\SubmissionValuesRepository;
use PDO;

class SubmitService extends FormService
{
    private PDO $pdo;
    private FormSubmissionsRepository $submissions;
    private SubmissionValuesRepository $values;

    public function __construct(
        PDO $pdo,
        FormRepository $repository,
        FormFieldsRepository $fields,
        FieldOptionsRepository $fieldOptions,
        FormSubmissionsRepository $submissions,
        SubmissionValuesRepository $values
    ) {
        parent::__construct($repository, $fields, $fieldOptions);
        $this->pdo = $pdo;
        $this->submissions = $submissions;
        $this->values = $values;
    }

    private function logAttempt(string $status, int $formId, string $reason = ''): void
    {
        $logFile = __DIR__ . '/submit_attempts.log';
        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ts      = date('Y-m-d H:i:s');
        $line    = "[{$ts}] status={$status} | form_id={$formId} | ip={$ip}"
            . ($reason ? " | motivo={$reason}" : '')
            . PHP_EOL;

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    public function handle(array $postData, array $files): int
    {

        $formId = (int)$postData['form_id'];
<<<<<<< HEAD
        $fields = $this->fields->findBy(['form_id' => $formId, 'field_type' => 'cpf'], [], true);

        // CPF Validation
        // 1. Localizar o campo do tipo CPF específico para este formulário
=======
>>>>>>> dev
        $cpfField = $this->fields->findBy([
            'form_id'    => (int)$postData['form_id'],
            'field_type' => 'cpf'
        ], [], true); // O 'true' garante que retorne apenas um registro (array), não uma lista

        // Verificação de duplicidade (sem validação de formato ainda)
        if ($cpfField) {

            $cpf = preg_replace('/\D/', '', $postData['field_' . $cpfField['id']] ?? '');

            // 1. CPF Vazio
            if (empty($cpf)) {
                $this->logAttempt('REJECTED', $formId, 'CPF não informado');
                throw new \Exception("O campo CPF é obrigatório.");
            }

            // 2. Formato inválido
            if (!$this->validateCPF($cpf)) {
                $this->logAttempt('REJECTED', $formId, 'CPF inválido');
                throw new \Exception("O número de CPF informado é inválido.");
            }

<<<<<<< HEAD
            // 3. Verificar duplicidade usando a sua nova lógica de contexto
            // Note que aqui usamos o field_id, que já é vinculado ao form_id
            $alreadySubmitted = $this->submissions->existsActiveByCpf(
                $cpfField['id'],
                $cpf,
                (int) $postData['form_id']
            );

            if ($alreadySubmitted) {
=======
            // 3. Duplicidade
            $alreadySubmitted = $this->values->checkDuplicateValue($formId, $cpfField['id'], $cpf);
            if ($alreadySubmitted) {
                $this->logAttempt('REJECTED', $formId, 'CPF duplicado (checkDuplicateValue)');
>>>>>>> dev
                throw new \Exception("Este CPF já enviou uma resposta para este formulário. Só é permitida uma participação por pessoa.");
            }
        }

        // Passou todas as validações — registra tentativa e entra na transação
        $this->logAttempt('ATTEMPT', $formId);

        try {
            $this->pdo->beginTransaction();

            // 1. Criar a Submissão (Envelope)
            $submissionId = $this->submissions->create([
                'form_id' => $postData['form_id'],
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            $form = $this->repository->findBy(['id' => (int)$postData['form_id']], [], true);

            if (!$form || !isset($form['uuid'])) {
                throw new \Exception("Formulário não encontrado ou UUID inválido.");
            }

            $formUuid = $form['uuid'];

            // 2. Processar campos de texto e números
            foreach ($postData as $key => $value) {
                if (strpos($key, 'field_') !== false) {
                    $fieldId = str_replace('field_', '', $key);
                    $fieldInfo = $this->fields->findBy(['id' => $fieldId], [], true);

                    // Limpeza de máscaras (CPF, Telefone, DDD)
                    $cleanValue = $value;
                    if (in_array($fieldInfo['field_type'], ['cpf', 'number', 'telefone'])) {
                        $cleanValue = preg_replace('/\D/', '', $value);
                    }

                    $this->values->create([
                        'submission_id' => $submissionId,
                        'field_id' => $fieldId,
                        'field_value' => $cleanValue
                    ]);
                }
            }

            foreach ($files as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fieldId = (int) str_replace('field_', '', $key);

                    // Agora passamos o $fieldId como argumento
                    $fileName = $this->uploadFile($file, $formUuid, (int)$postData['form_id'], $submissionId, $fieldId);

                    $this->values->create([
                        'submission_id' => $submissionId,
                        'field_id' => $fieldId,
                        'field_value' => $fileName
                    ]);
                }
            }

            $this->pdo->commit();
            $this->logAttempt('SUCCESS', $formId, "submission_id={$submissionId}");
            return $submissionId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
<<<<<<< HEAD

            // Log do erro
            error_log(sprintf(
                "[SubmitService] Erro na submissão | form_id: %d | ip: %s | erro: %s | arquivo: %s:%d",
                $formId,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));


=======
            $this->logAttempt('ERROR', $formId, $e->getMessage());
>>>>>>> dev
            throw $e;
        }
    }

    private function uploadFile(array $file, string $formUuid, int $formId, int $submissionId, int $fieldId): string
    {
        // Define e cria o diretório: /storage/{uuid}
        $storagePath = $_SERVER['DOCUMENT_ROOT'] . "/storage/" . $formUuid;

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // NOVO FORMATO: Adicionado o $fieldId no nome
        $timestamp = date('YmdHi');
        $newFileName = "{$formId}_{$submissionId}_{$fieldId}_{$timestamp}.pdf";
        $destination = $storagePath . "/" . $newFileName;

        // Validação de segurança (MIME type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            $this->logAttempt('UPLOAD_REJECTED', $formId, "MIME inválido: {$mime} | field_id={$fieldId}");
            throw new \Exception("Apenas arquivos PDF reais são permitidos.");
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            throw new \Exception("O arquivo PDF não pode ser maior que 10MB.");
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->logAttempt('UPLOAD_ERROR', $formId, "Falha ao mover arquivo | field_id={$fieldId} | destino={$destination}");
            throw new \Exception("Falha ao mover o arquivo para o diretório de destino.");
        }

        $this->logAttempt('UPLOAD_SUCCESS', $formId, "arquivo={$newFileName} | field_id={$fieldId} | submission_id={$submissionId}");
        return $newFileName;
    }

    private function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
}
