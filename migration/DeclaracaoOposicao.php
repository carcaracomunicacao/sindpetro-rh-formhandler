<?php
/*

-- 1. Inserção do Formulário
INSERT INTO spfh_forms (title, description, uuid, is_active) 
VALUES (
    'Declaração de Oposição ao Desconto da Contribuição Assistencial', 
    'A coleta dos dados aqui informados será para fins de identificação das pessoas que se opõem ao desconto da contribuição assistencial no âmbito interno ao Sindipetro-RJ e envio as empresas para que não seja efetivado o desconto o que restará autorizado por meio da referida declaração.

Todos os dados são necessários para registrar a manifestação à oposição ao desconto. O único meio de envio é pelo formulário, não serão aceitos outros tipos de oposição.

Não informar corretamente os dados acarretará no desconto junto à empresa.

Só será aceita UMA RESPOSTA POR CPF. Em caso de resposta duplicada, a primeira resposta é a que será considerada.

Conheça mais:
Entenda a Contribuição Assistencial e o regramento para a reposição das perdas dos grevistas 👉
https://sindipetro.org.br/contribuicao-assistencial-greve/

Em caso de dúvidas, mande email para: contato@sindipetro.org.br',
    UUID(),
    1
);

SET @form_id = LAST_INSERT_ID();

-- 2. Inserção dos Campos do Formulário
INSERT INTO spfh_form_fields (id, form_id, label, field_type, field_mask, is_required, is_unique, placeholder, description) VALUES
(NULL, @form_id, 'Nome completo', 'text', NULL, 1, 0, 'Sua resposta', NULL),
(NULL, @form_id, 'Empresa', 'radio', NULL, 1, 0, NULL, NULL), -- Definido como Radio
(NULL, @form_id, 'Imóvel de trabalho', 'text', NULL, 1, 0, 'Sua resposta', NULL),
(NULL, @form_id, 'Lotação', 'text', NULL, 1, 0, 'Sua resposta', 'Conjunto de siglas das gerências conforme consta no localizador de pessoas da empresa'),
(NULL, @form_id, 'Chave', 'text', NULL, 1, 0, 'Sua resposta', NULL),
(NULL, @form_id, 'Matrícula', 'text', NULL, 1, 0, 'Sua resposta', NULL),
(NULL, @form_id, 'CPF', 'cpf', '000.000.000-00', 1, 1, 'Sua resposta', NULL),
(NULL, @form_id, 'DDD', 'number', '00', 1, 0, 'Ex: 21', NULL),
(NULL, @form_id, 'Telefone para contato', 'text', '00000-0000', 1, 0, 'Apenas números', NULL),
(NULL, @form_id, 'Email para contato', 'email', NULL, 1, 0, 'Sua resposta', NULL),
(NULL, @form_id, 'Documento de identificação com foto', 'file', NULL, 1, 0, NULL, 'No caso de identidade, frente e verso no mesmo arquivo. Apenas arquivos PDF');

-- 3. Inserção das Opções para o campo 'Empresa'
-- Pegamos o ID do campo 'Empresa' que acabamos de inserir para este formulário específico
SET @field_empresa_id = (SELECT id FROM spfh_form_fields WHERE form_id = @form_id AND label = 'Empresa' LIMIT 1);

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@field_empresa_id, 'Petrobras', 'petrobras'),
(@field_empresa_id, 'PBio', 'pbio'),
(@field_empresa_id, 'Transpetro', 'transpetro'),
(@field_empresa_id, 'TBG', 'tbg');

*/
