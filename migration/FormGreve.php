<?php
/*

-- 1. Criar o formulário principal
INSERT INTO spfh_forms (uuid, title, description, is_active) 
VALUES (
    UUID(), 
    'Formulário de Contribuição Assistencial para Grevistas', 
    'A coleta de dados aqui informados será para fins de apuração dos valores e identificação de pessoas com vista do eventual reembolso do valor descontado dos grevistas.\n\nSó será aceita uma resposta por CPF. Em caso de resposta duplicada, será considerada apenas a primeira resposta.', 
    1
);

-- Pegamos o ID do formulário recém-criado
SET @form_id = LAST_INSERT_ID();

-- 2. Inserir os campos com suporte a Descrição e Máscara
INSERT INTO spfh_form_fields 
(form_id, label, description, field_type, is_required, is_unique, placeholder, display_order, field_mask)
VALUES 
(@form_id, 'Nome Completo', NULL, 'text', 1, 0, 'Digite seu nome completo', 1, NULL),

(@form_id, 'CPF', 'Utilize apenas números. O sistema validará a duplicidade automaticamente.', 'cpf', 1, 1, '000.000.000-00', 2, '000.000.000-00'),

(@form_id, 'E-mail', 'Informe um e-mail válido para receber a confirmação.', 'email', 1, 0, 'seu@email.com', 3, NULL),

(@form_id, 'DDD', 'Apenas os 2 dígitos do estado.', 'number', 1, 0, 'Ex: 11', 4, '00'),

(@form_id, 'Telefone', 'Número sem o DDD.', 'text', 1, 0, '99999-9999', 5, '00000-0000'),

(@form_id, 'Imóvel de Trabalho', 'Nome do prédio ou unidade física onde exerce as atividades.', 'text', 1, 0, 'Nome do prédio ou unidade', 6, NULL),

(@form_id, 'Lotação', 'Conjunto de siglas das gerências conforme consta no localizador de pessoas da empresa.', 'text', 1, 0, 'Ex: DEPART/DIVIS', 7, NULL),

(@form_id, 'Valores Descontados', 'Informe o valor total bruto do desconto conforme aparece no seu contracheque.', 'text', 1, 0, 'R$ 0,00', 8, 'R$ num'),

(@form_id, 'Data do Contracheque', 'A data de emissão do contracheque onde consta o desconto.', 'text', 1, 0, 'DD/MM/AAAA', 9, '00/00/0000'),

(@form_id, 'Dias de Greve', 'Informe a quantidade total de dias (limite de 1 a 16).', 'number', 1, 0, '1 a 16', 10, '00'),

(@form_id, 'Ficha de Registro do Empregado (PDF)', 'Anexe o arquivo PDF da sua ficha funcional atualizada.', 'file', 1, 0, 'Selecione o arquivo PDF', 11, NULL);

*/
?>