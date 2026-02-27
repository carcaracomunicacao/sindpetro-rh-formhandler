<?php

namespace App\Repository;

class SubmissionValuesRepository extends Repository
{
    protected string $table = 'spfh_submission_values'; // Change the table name

    public function checkDuplicateValue(int $formId, int $fieldId, string $value): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} v
            JOIN spfh_form_submissions s ON v.submission_id = s.id
            WHERE s.form_id = :form_id 
            AND v.field_id = :field_id 
            AND v.field_value = :value";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'form_id' => $formId,
            'field_id' => $fieldId,
            'value'   => $value
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}

/*

CREATE TABLE spfh_submission_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    field_id INT NOT NULL,
    field_value TEXT, -- Armazena: texto comum, JSON de checkboxes ou path de arquivo
    FOREIGN KEY (submission_id) REFERENCES spfh_form_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES spfh_form_fields(id) ON DELETE CASCADE
) ENGINE=InnoDB;

*/