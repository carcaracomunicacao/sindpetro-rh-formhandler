<?php

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

CREATE TABLE spfh_field_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_id INT NOT NULL,
    option_label VARCHAR(255) NOT NULL, -- O que o usuário vê (ex: "Masculino")
    option_value VARCHAR(255) NOT NULL, -- O que vai pro banco (ex: "M")
    FOREIGN KEY (field_id) REFERENCES spfh_form_fields(id) ON DELETE CASCADE
);

CREATE TABLE spfh_form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (form_id) REFERENCES spfh_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE spfh_submission_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    field_id INT NOT NULL,
    field_value TEXT, -- Armazena: texto comum, JSON de checkboxes ou path de arquivo
    FOREIGN KEY (submission_id) REFERENCES spfh_form_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES spfh_form_fields(id) ON DELETE CASCADE
) ENGINE=InnoDB;

*/
?>