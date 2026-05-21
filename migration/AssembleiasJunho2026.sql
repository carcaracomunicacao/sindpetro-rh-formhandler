-- =====================================================================
-- Formulários de convocação para Assembleias — junho/2026
-- =====================================================================

-- ---------------------------------------------------------------------
-- FORM 1: Assembleia de Aposentados
-- ---------------------------------------------------------------------
INSERT INTO spfh_forms (uuid, title, description, is_active)
VALUES (
    UUID(),
    'Convocação — Assembleia de Aposentados (junho/2026)',
    'Inscreva-se para participar das assembleias de aposentados de junho de 2026. Preencha seus dados e selecione a assembleia da qual deseja participar. As vagas e a confirmação serão validadas pela secretaria.',
    TRUE
);
SET @form_aposentados := LAST_INSERT_ID();

-- Campo 1: Nome completo
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_aposentados, 'Nome completo', 'Informe seu nome conforme documento oficial.', 'text', 1, 0, 'Ex.: Maria da Silva Souza', 1);

-- Campo 2: CPF (obrigatório e único, com máscara)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order, field_mask)
VALUES (@form_aposentados, 'CPF', 'Apenas números. A máscara é aplicada automaticamente.', 'cpf', 1, 1, '000.000.000-00', 2, '000.000.000-00');

-- Campo 3: Telefone (WhatsApp)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order, field_mask)
VALUES (@form_aposentados, 'Telefone (WhatsApp)', 'Use um número com WhatsApp ativo — a confirmação será enviada por essa via.', 'text', 1, 0, '(00) 00000-0000', 3, '(00) 00000-0000');

-- Campo 4: E-mail (obrigatório e único)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_aposentados, 'E-mail', 'Será usado para envio do link da assembleia híbrida.', 'email', 1, 1, 'seuemail@exemplo.com', 4);

-- Campo 5: Cidade
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_aposentados, 'Cidade', 'Cidade onde você reside atualmente.', 'text', 1, 0, 'Ex.: Rio de Janeiro', 5);

-- Campo 6: Assembleia que irá participar (radio)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, display_order)
VALUES (@form_aposentados, 'Assembleia que irá participar', 'Selecione uma das datas disponíveis.', 'radio', 1, 0, 6);
SET @field_assembleia_apos := LAST_INSERT_ID();

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@field_assembleia_apos, '10/06 — Angra dos Reis (híbrida) — 14h', 'angra-10-06-2026'),
(@field_assembleia_apos, '23/06 — Rio de Janeiro (híbrida) — 13h', 'rio-23-06-2026');


-- ---------------------------------------------------------------------
-- FORM 2: Assembleia de Ativos
-- ---------------------------------------------------------------------
INSERT INTO spfh_forms (uuid, title, description, is_active)
VALUES (
    UUID(),
    'Convocação — Assembleia de Ativos (junho/2026)',
    'Inscreva-se para participar das assembleias de ativos de junho de 2026. Preencha seus dados e selecione a assembleia (TBG ou PBIO) da qual deseja participar.',
    TRUE
);
SET @form_ativos := LAST_INSERT_ID();

-- Campo 1: Nome completo
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_ativos, 'Nome completo', 'Informe seu nome conforme documento oficial.', 'text', 1, 0, 'Ex.: João Pereira da Silva', 1);

-- Campo 2: CPF
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order, field_mask)
VALUES (@form_ativos, 'CPF', 'Apenas números. A máscara é aplicada automaticamente.', 'cpf', 1, 1, '000.000.000-00', 2, '000.000.000-00');

-- Campo 3: Telefone (WhatsApp)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order, field_mask)
VALUES (@form_ativos, 'Telefone (WhatsApp)', 'Use um número com WhatsApp ativo — a confirmação será enviada por essa via.', 'text', 1, 0, '(00) 00000-0000', 3, '(00) 00000-0000');

-- Campo 4: E-mail
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_ativos, 'E-mail', 'Será usado para envio das informações da assembleia.', 'email', 1, 1, 'seuemail@exemplo.com', 4);

-- Campo 5: Cidade
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, placeholder, display_order)
VALUES (@form_ativos, 'Cidade', 'Cidade onde você reside atualmente.', 'text', 1, 0, 'Ex.: Rio de Janeiro', 5);

-- Campo 6: Assembleia que irá participar (radio)
INSERT INTO spfh_form_fields (form_id, label, description, field_type, is_required, is_unique, display_order)
VALUES (@form_ativos, 'Assembleia que irá participar', 'Selecione uma das datas disponíveis.', 'radio', 1, 0, 6);
SET @field_assembleia_ativ := LAST_INSERT_ID();

INSERT INTO spfh_field_options (field_id, option_label, option_value) VALUES
(@field_assembleia_ativ, '17/06 — Assembleia do TBG — 12h00', 'tbg-17-06-2026'),
(@field_assembleia_ativ, '19/06 — Assembleia do PBIO — 12h30', 'pbio-19-06-2026');


-- ---------------------------------------------------------------------
-- Consulta auxiliar: recuperar os UUIDs dos dois formulários criados
-- ---------------------------------------------------------------------
-- SELECT id, uuid, title FROM spfh_forms WHERE id IN (@form_aposentados, @form_ativos);