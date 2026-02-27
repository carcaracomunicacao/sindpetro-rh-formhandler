<?php

namespace App\Repository;

class FormFieldsRepository extends Repository
{
    protected string $table = 'spfh_form_fields'; // Change the table name
}

/*

CREATE TABLE spfh_form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    description TEXT NULL, -- Instrução ou dica abaixo do campo
    field_type ENUM('text', 'email', 'number', 'cpf', 'textarea', 'select', 'radio', 'checkbox', 'file') NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    is_unique BOOLEAN DEFAULT FALSE,
    placeholder VARCHAR(255),
    display_order INT DEFAULT 0,
    validation_rule VARCHAR(255),
    field_mask VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (form_id) REFERENCES spfh_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
*/