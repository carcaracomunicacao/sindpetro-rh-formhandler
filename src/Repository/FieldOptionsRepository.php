<?php

namespace App\Repository;

class FieldOptionsRepository extends Repository
{
    protected string $table = 'spfh_field_options'; // Change the table name
}

/*

CREATE TABLE spfh_field_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_id INT NOT NULL,
    option_label VARCHAR(255) NOT NULL, -- O que o usuário vê (ex: "Masculino")
    option_value VARCHAR(255) NOT NULL, -- O que vai pro banco (ex: "M")
    FOREIGN KEY (field_id) REFERENCES spfh_form_fields(id) ON DELETE CASCADE
);

*/