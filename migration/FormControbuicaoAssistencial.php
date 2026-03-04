<?php
/*

-- 1. Criar o formulário principal
INSERT INTO spfh_forms (uuid, title, description, is_active) 
VALUES (
    UUID(), 
    'Formulário de Solicitação de Reembolso para Grevistas Associados', 
    '<p>A coleta dos dados aqui informados será para fins de apuração dos valores e identificação de pessoas com vista do eventual reembolso do valor descontado dos grevistas.</p><p>Só será aceita UMA RESPOSTA POR CPF. Em caso de resposta duplicada, a primeira resposta é a que será considerada.</p><p>Conheça mais:</p><p>Entenda a Contribuição Assistencial e o regramento para a reposição das perdas dos grevistas 👉<br/><a href="https://sindipetro.org.br/contribuicao-assistencial-greve/">https://sindipetro.org.br/contribuicao-assistencial-greve/</a></p><p>Em caso de dúvidas, mande email para: <a href="mailto:contato@sindipetro.org.br">contato@sindipetro.org.br</a></p> ', 
    1
);

SET @form_id = LAST_INSERT_ID();

-- 2. Campos do formulário
INSERT INTO spfh_form_fields (form_id, label, field_type, field_mask, is_required, is_unique, placeholder, description, display_order) VALUES
-- Dados pessoais
(@form_id, 'Nome completo',                                      'text',     NULL,           1, 0, 'Sua resposta',       NULL,                                                                                      1),
(@form_id, 'CPF',                                                'cpf',      '000.000.000-00',1, 1, 'Sua resposta',       NULL,                                                                                      2),
(@form_id, 'Email para contato',                                 'email',    NULL,           1, 0, 'usuario@dominio.com',  NULL,                                                                                      3),
(@form_id, 'DDD',                                                'number',   '00',           1, 0, 'Ex: 21',             NULL,                                                                                      4),
(@form_id, 'Telefone para contato',                              'text',     '00000-0000',   1, 0, 'Apenas números',     NULL,                                                                                      5),
-- Dados profissionais
(@form_id, 'Imóvel de trabalho',                                 'text',     NULL,           1, 0, 'Sua resposta',       NULL,                                                                                      6),
(@form_id, 'Lotação',                                            'text',     NULL,           1, 0, 'Sua resposta',       'Conjunto de siglas das gerências conforme consta no localizador de pessoas da empresa',   7),
-- Dados da greve
(@form_id, 'Total dos valores que foram descontados',            'text',     'currency',     1, 0, 'Ex: 1.250,00',       'Formato x.xxx,xx',                                                                        8),
(@form_id, 'Data do contracheque',                               'text',     '00/00/0000',   1, 0, 'Formato xx/xx/xxxx', NULL,                                                                                      9),
(@form_id, 'Quantidade de dias de greve',                        'number',   NULL,           1, 0, 'Entre 1 e 16',       NULL,                                                                                     10),
-- Documentos
(@form_id, 'Ficha de registro do empregado, FRE (em PDF)',       'file',     NULL,           1, 0, NULL,                 'Apenas arquivos PDF',                                                                    11),
(@form_id, 'Contracheque Dezembro',                              'file',     NULL,           1, 0, NULL,                 'Apenas arquivos PDF',                                                                    12),
(@form_id, 'Contracheque Janeiro',                               'file',     NULL,           1, 0, NULL,                 'Apenas arquivos PDF',                                                                    13),
(@form_id, 'Contracheque Fevereiro',                             'file',     NULL,           1, 0, NULL,                 'Apenas arquivos PDF',                                                                    14),
(@form_id, 'Registro de frequência do mês de Dezembro',          'file',     NULL,           1, 0, NULL,                 'Apenas arquivos PDF',                                                                    15),
-- Dados bancários
(@form_id, 'Banco',                                              'text',     NULL,           1, 0, 'Ex: Bradesco',       NULL,                                                                                     16),
(@form_id, 'Agência',                                            'text',     NULL,           1, 0, 'Ex: 1234',           NULL,                                                                                     17),
(@form_id, 'Conta',                                              'text',     NULL,           1, 0, 'Ex: 12345-6',        NULL,                                                                                     18),
(@form_id, 'Tipo de conta',                                      'select',   NULL,           1, 0, NULL,                 NULL,                                                                                     19),
(@form_id, 'Chave Pix',                                          'text',     NULL,           0, 0, 'CPF, email, telefone ou chave aleatória', NULL,                                                               20);

-- 3. Opções do campo Tipo de conta
SET @tipo_conta_id = LAST_INSERT_ID() - 1;

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@tipo_conta_id, 'Conta Corrente', 'corrente'),
(@tipo_conta_id, 'Conta Poupança', 'poupanca'),
(@tipo_conta_id, 'Conta Salário',  'salario');

*/
