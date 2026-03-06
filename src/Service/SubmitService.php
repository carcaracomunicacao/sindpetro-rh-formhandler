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

    public function handle(array $postData, array $files): int
    {

        $formId = (int)$postData['form_id'];
        $fields = $this->fields->findBy(['form_id' => $formId, 'field_type' => 'cpf'], [], true);

        // CPF Validation
        // 1. Localizar o campo do tipo CPF específico para este formulário
        $cpfField = $this->fields->findBy([
            'form_id'    => (int)$postData['form_id'],
            'field_type' => 'cpf'
        ], [], true); // O 'true' garante que retorne apenas um registro (array), não uma lista

        if ($cpfField) {
            // 2. Limpar e validar o formato do CPF
            $cpf = preg_replace('/\D/', '', $postData['field_' . $cpfField['id']] ?? '');

            if (empty($cpf)) {
                throw new \Exception("O campo CPF é obrigatório.");
            }

            if (!$this->validateCPF($cpf)) {
                throw new \Exception("O número de CPF informado é inválido.");
            }

            // 3. Verificar duplicidade usando a sua nova lógica de contexto
            // Note que aqui usamos o field_id, que já é vinculado ao form_id
            $alreadySubmitted = $this->submissions->existsActiveByCpf(
                $cpfField['id'],
                $cpf,
                (int) $postData['form_id']
            );

            if ($alreadySubmitted) {
                throw new \Exception("Este CPF já enviou uma resposta para este formulário. Só é permitida uma participação por pessoa.");
            }
        }

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
            return $submissionId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
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
            throw new \Exception("Apenas arquivos PDF reais são permitidos.");
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            throw new \Exception("O arquivo PDF não pode ser maior que 10MB.");
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \Exception("Falha ao mover o arquivo para o diretório de destino.");
        }

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
