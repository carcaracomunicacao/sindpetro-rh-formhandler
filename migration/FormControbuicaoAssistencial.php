<?php
/*

-- 1. Criar o formulário principal
INSERT INTO spfh_forms (uuid, title, description, is_active) 
VALUES (
    UUID(), 
    'Formulário de Solicitação de Reembolso para Grevistas Associados', 
    '<p>A coleta dos dados aqui informados será para fins de apuração dos valores e identificação de pessoas com vista do eventual reembolso do valor descontado dos grevistas.</p><p>Só será aceita UMA RESPOSTA POR CPF. Em caso de resposta duplicada, a primeira resposta é a que será considerada.</p><p>Conheça mais:</p><p>Entenda a Contribuição Assistencial e o regramento para a reposição das perdas dos grevistas 👉<br/><a href="https://sindipetro.org.br/contribuicao-assistencial-greve/">https://sindipetro.org.br/contribuicao-assistencial-greve/</a></p><p>Em caso de dúvidas, mande email para: <a href="mailto:contato@sindipetro.org.br">contato@sindipetro.org.br</a></p>', 
    1
);


-- Pegamos o ID do formulário recém-criado
SET @form_id = LAST_INSERT_ID();

INSERT INTO spfh_form_fields (form_id, label, field_type, field_mask, is_required, is_unique, placeholder, description) VALUES
-- 1. Nome Completo
(@form_id, 'Nome completo', 'text', NULL, 1, 0, 'Sua resposta', NULL),

-- 2. CPF (Unique)
(@form_id, 'CPF', 'cpf', '000.000.000-00', 1, 1, 'Sua resposta', NULL),

-- 3. Email
(@form_id, 'Email para contato', 'email', NULL, 1, 0, 'Sua resposta', NULL),

-- 4. DDD (Separado)
(@form_id, 'DDD', 'number', '00', 1, 0, 'Ex: 21', NULL),

-- 5. Telefone (Dinâmico 8 ou 9 dígitos)
(@form_id, 'Telefone para contato', 'text', '00000-0000', 1, 0, 'Apenas números', NULL),

-- 6. Imóvel de trabalho
(@form_id, 'Imóvel de trabalho', 'text', NULL, 1, 0, 'Sua resposta', NULL),

-- 7. Lotação
(@form_id, 'Lotação', 'text', NULL, 1, 0, 'Sua resposta', 'Conjunto de siglas das gerências conforme consta no localizador de pessoas da empresa'),

-- 8. Total de valores (Currency)
(@form_id, 'Total dos valores que foram descontados', 'text', 'currency', 1, 0, 'Ex: 1.250,00', 'Formato x.xxx,xx'),

-- 9. Data do contracheque
(@form_id, 'Data do contracheque', 'text', '00/00/0000', 1, 0, 'Formato xx/xx/xxxx', NULL),

-- 10. Quantidade de dias
(@form_id, 'Quantidade de dias de greve', 'number', NULL, 1, 0, 'Entre 1 e 16', NULL),

-- 11. Ficha de Registro (Arquivo)
(@form_id, 'Ficha de registro do empregado, FRE (em PDF)', 'file', NULL, 1, 0, NULL, 'Apenas arquivos PDF'),

-- 12. Contracheques (Arquivo)
(@form_id, 'Contracheques de dez, jan e fev', 'file', NULL, 1, 0, NULL, 'Apenas arquivos PDF'),

-- 13. Registro frequência (Arquivo)
(@form_id, 'Registro frequência do mês de dez', 'file', NULL, 1, 0, NULL, 'Apenas arquivos PDF');

*/
