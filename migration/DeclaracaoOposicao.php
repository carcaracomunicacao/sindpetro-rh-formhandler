<?php
/*
-- 1. Inserção do Formulário
INSERT INTO spfh_forms (title, description, uuid, is_active) 
VALUES (
    'Declaração de Oposição ao Desconto da Contribuição Assistencial', 
    '<p>A coleta dos dados aqui informados será para fins de identificação das pessoas que se opõem ao desconto da contribuição assistencial no âmbito interno ao Sindipetro-RJ e envio as empresas para que não seja efetivado o desconto o que restará autorizado por meio da referida declaração.</p><p>Todos os dados são necessários para registrar a manifestação à oposição ao desconto. O único meio de envio é pelo formulário, não serão aceitos outros tipos de oposição.</p><p>Não informar corretamente os dados acarretará no desconto junto à empresa.</p><p>Só será aceita UMA RESPOSTA POR CPF. Em caso de resposta duplicada, a primeira resposta é a que será considerada.</p><p>Conheça mais:</p><p>Entenda a Contribuição Assistencial e o regramento para a reposição das perdas dos grevistas 👉<br/><a href="https://sindipetro.org.br/contribuicao-assistencial-greve/">https://sindipetro.org.br/contribuicao-assistencial-greve/</a></p><p>Em caso de dúvidas, mande email para: <a href="mailto:contato@sindipetro.org.br">contato@sindipetro.org.br</a></p>',
    UUID(),
    1
);

SET @form_id = LAST_INSERT_ID();

-- 2. Inserção dos Campos do Formulário
INSERT INTO spfh_form_fields (form_id, label, field_type, field_mask, is_required, is_unique, placeholder, description, display_order) VALUES
(@form_id, 'Nome completo',                        'text',   NULL,            1, 0, 'Sua resposta',    NULL,                                                                                     1),
(@form_id, 'Empresa',                              'radio',  NULL,            1, 0, NULL,              NULL,                                                                                     2),
(@form_id, 'Imóvel de trabalho',                   'text',   NULL,            1, 0, 'Sua resposta',    NULL,                                                                                     3),
(@form_id, 'Lotação',                              'text',   NULL,            1, 0, 'Sua resposta',    'Conjunto de siglas das gerências conforme consta no localizador de pessoas da empresa',   4),
(@form_id, 'Chave',                                'text',   NULL,            1, 0, 'Sua resposta',    NULL,                                                                                     5),
(@form_id, 'Matrícula',                            'text',   NULL,            1, 0, 'Sua resposta',    NULL,                                                                                     6),
(@form_id, 'CPF',                                  'cpf',    '000.000.000-00',1, 1, 'Sua resposta',    NULL,                                                                                     7),
(@form_id, 'DDD',                                  'number', '00',            1, 0, 'Ex: 21',          NULL,                                                                                     8),
(@form_id, 'Telefone para contato',                'text',   '00000-0000',    1, 0, 'Apenas números',  NULL,                                                                                     9),
(@form_id, 'Email para contato',                   'email',  NULL,            1, 0, 'usuaro@dominio.com',    NULL,                                                                                    10),
(@form_id, 'Documento de identificação com foto',  'file',   NULL,            1, 0, NULL,              'No caso de identidade, frente e verso no mesmo arquivo. Apenas arquivos PDF',            11),
-- Dados bancários
(@form_id, 'Banco',                                'text',   NULL,            1, 0, 'Ex: Bradesco',    NULL,                                                                                    12),
(@form_id, 'Agência',                              'text',   NULL,            1, 0, 'Ex: 1234',        NULL,                                                                                    13),
(@form_id, 'Conta',                                'text',   NULL,            1, 0, 'Ex: 12345-6',     NULL,                                                                                    14),
(@form_id, 'Tipo de conta',                        'select', NULL,            1, 0, NULL,              NULL,                                                                                    15),
(@form_id, 'Chave Pix',                            'text',   NULL,            0, 0, 'CPF, email, telefone ou chave aleatória', NULL,                                                           16);

-- 3. Opções do campo Empresa
SET @field_empresa_id = (SELECT id FROM spfh_form_fields WHERE form_id = @form_id AND label = 'Empresa' LIMIT 1);

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@field_empresa_id, 'Petrobras',   'petrobras'),
(@field_empresa_id, 'PBio',        'pbio'),
(@field_empresa_id, 'Transpetro',  'transpetro'),
(@field_empresa_id, 'TBG',         'tbg');

-- 4. Opções do campo Tipo de conta
SET @field_tipo_conta_id = (SELECT id FROM spfh_form_fields WHERE form_id = @form_id AND label = 'Tipo de conta' LIMIT 1);

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@field_tipo_conta_id, 'Conta Corrente', 'corrente'),
(@field_tipo_conta_id, 'Conta Poupança', 'poupanca'),
(@field_tipo_conta_id, 'Conta Salário',  'salario');

*/
