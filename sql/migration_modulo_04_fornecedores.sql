-- ============================================================================
-- Migração — Módulo 04: Fornecedores e Parceiros (12/08/2026)
-- Prestadores/parceiros do cliente (contabilidade, jurídico, seguros, marina,
-- saúde, tecnologia...) com classificação, contrato, dados financeiros e
-- avaliação. A vigência do contrato alimenta a Agenda (Módulo 14).
--
-- NOVA migration pós-sincronização de produção — rodar no phpMyAdmin.
-- ============================================================================

USE gestao_patrimonial;

CREATE TABLE IF NOT EXISTS fornecedores (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- FO-0001 (gerado na aplicação)

  tipo_pessoa         ENUM('PJ','PF') NOT NULL DEFAULT 'PJ',
  nome                VARCHAR(180) NOT NULL,             -- razão social ou nome
  nome_fantasia       VARCHAR(180) NULL,
  cpf_cnpj            VARCHAR(20)  NULL,
  categoria           ENUM('contabilidade','juridico','seguros','marina','saude','tecnologia','rh','imobiliaria','manutencao','construcao','financeiro','transporte','outro') NOT NULL DEFAULT 'outro',

  -- Contato
  contato_nome        VARCHAR(140) NULL,                 -- pessoa de contato
  telefone            VARCHAR(40)  NULL,
  email               VARCHAR(140) NULL,
  site                VARCHAR(140) NULL,

  -- Endereço
  cep                 VARCHAR(10)  NULL,
  logradouro          VARCHAR(180) NULL,
  numero              VARCHAR(20)  NULL,
  complemento         VARCHAR(120) NULL,
  bairro              VARCHAR(120) NULL,
  cidade              VARCHAR(120) NULL,
  estado              CHAR(2)      NULL,

  -- Contrato
  contrato_inicio     DATE NULL,
  contrato_fim        DATE NULL,                          -- alimenta a Agenda
  contrato_valor      DECIMAL(15,2) NULL,
  contrato_reajuste   VARCHAR(120) NULL,                  -- índice/observação de reajuste
  forma_pagamento     VARCHAR(80)  NULL,

  -- Financeiro (pagamento ao fornecedor)
  banco               VARCHAR(120) NULL,
  agencia             VARCHAR(20)  NULL,
  conta               VARCHAR(30)  NULL,
  pix_tipo            ENUM('cpf','cnpj','email','telefone','aleatoria') NULL,
  pix_chave           VARCHAR(140) NULL,

  -- Avaliação
  avaliacao_nota      TINYINT NULL,                       -- 1 a 5
  sla                 VARCHAR(180) NULL,
  avaliacao_obs       TEXT NULL,

  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_fornecedores_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- documentos: anexos de fornecedor (contrato, NF, certidão). Só tipo_referencia
-- += 'fornecedor' (categorias 'contrato'/'nf'/'certidao'/'outro' já existem).
ALTER TABLE documentos
  MODIFY tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira','seguro','empresa','investimento','fornecedor')
  NOT NULL DEFAULT 'imovel';
