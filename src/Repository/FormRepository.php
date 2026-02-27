<?php

namespace App\Repository;

class FormRepository extends Repository
{
    protected string $table = 'spfh_forms'; // Change the table name
}

/*

CREATE TABLE spfh_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE, -- Identificador único para a URL pública (ex: /view-form.php?id=uuid)
    title VARCHAR(255) NOT NULL,
    description TEXT,
    og_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

*/