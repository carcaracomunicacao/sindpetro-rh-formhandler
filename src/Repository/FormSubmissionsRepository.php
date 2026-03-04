<?php

namespace App\Repository;

class FormSubmissionsRepository extends Repository
{
    protected string $table = 'spfh_form_submissions'; // Change the table name

    public function getDetailedSubmissions(int $formId)
    {
        // Esta query busca a submissão e concatena os campos principais
        // Você pode ajustar os nomes dos campos de acordo com os IDs do seu banco
        $sql = "SELECT 
                s.id as submission_id,
                s.submitted_at,
                s.ip_address,
                MAX(CASE WHEN f.field_type = 'text' AND f.label LIKE '%Nome%' THEN v.field_value END) as nome,
                MAX(CASE WHEN f.field_type = 'cpf' THEN v.field_value END) as cpf,
                MAX(CASE WHEN f.field_type = 'file' THEN v.field_value END) as arquivo_pdf,
                s.form_id
            FROM {$this->table} s
            JOIN spfh_submission_values v ON s.id = v.submission_id
            JOIN spfh_form_fields f ON v.field_id = f.id
            WHERE s.form_id = :form_id
            GROUP BY s.id
            ORDER BY s.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['form_id' => $formId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllSubmissionsPaginated(int $limit, int $offset, ?string $search = null, ?int $formId = null): array
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];

        $conditions = [];

        if ($formId) {
            $conditions[] = "s.form_id = :form_id";
            $params['form_id'] = $formId;
        }

        if ($search) {
            $cleanSearch = preg_replace('/\D/', '', $search);
            $searchParam = "%{$search}%";
            $conditions[] = "EXISTS (
            SELECT 1 FROM spfh_submission_values sv 
            WHERE sv.submission_id = s.id 
            AND (sv.field_value LIKE :search_raw " . ($cleanSearch ? "OR sv.field_value LIKE :search_clean" : "") . ")
        )";
            $params['search_raw'] = $searchParam;
            if ($cleanSearch) {
                $params['search_clean'] = "%{$cleanSearch}%";
            }
        }

        $whereSql = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT 
                s.id, 
                s.submitted_at, 
                s.ip_address, 
                f.title as form_title,
                f.uuid as form_uuid,
                MAX(CASE WHEN fld.field_type = 'text' AND fld.label LIKE '%Nome%' THEN v.field_value END) as nome,
                MAX(CASE WHEN fld.field_type = 'cpf' THEN v.field_value END) as cpf,
                GROUP_CONCAT(CASE WHEN fld.field_type = 'file' THEN v.field_value END SEPARATOR ',') as arquivos_pdf
            FROM {$this->table} s
            JOIN spfh_forms f ON s.form_id = f.id
            LEFT JOIN spfh_submission_values v ON s.id = v.submission_id
            LEFT JOIN spfh_form_fields fld ON v.field_id = fld.id
            {$whereSql}
            GROUP BY s.id
            ORDER BY s.submitted_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalSubmissionsCount(?int $formId = null): int
    {
        if ($formId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE form_id = :form_id");
            $stmt->bindValue(':form_id', $formId, \PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        }

        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function getFieldsByFormId(int $formId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT id, label 
        FROM spfh_form_fields 
        WHERE form_id = :form_id 
        ORDER BY id ASC
    ");
        $stmt->bindValue(':form_id', $formId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllSubmissionsForExport(int $formId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            s.id,
            s.submitted_at,
            v.field_id,
            v.field_value
        FROM {$this->table} s
        LEFT JOIN spfh_submission_values v ON s.id = v.submission_id
        WHERE s.form_id = :form_id
        ORDER BY s.id ASC, v.field_id ASC
    ");
        $stmt->bindValue(':form_id', $formId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
/*

CREATE TABLE spfh_form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (form_id) REFERENCES spfh_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci:

*/