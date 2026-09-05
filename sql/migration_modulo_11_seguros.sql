-- ============================================================================
-- Migração — Módulo 11: Seguros (11/08/2026)
-- Cadastro central de seguros/apólices do cliente. Unifica os seguros antes
-- espalhados por imóvel/veículo/outro bem e alimenta a Agenda (Módulo 14) pela
-- data de vigência (vigencia_fim).
-- Vínculo polimórfico opcional a um bem ou pessoa (item_tipo + item_id).
-- ============================================================================

USE gestao_patrimonial;

-- ------------------------------------------------------------
-- Tabela: seguros
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS seguros (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id       INT NOT NULL,
  codigo           VARCHAR(10) NOT NULL,                 -- SG-0001 (gerado na aplicação)
  tipo             ENUM('vida','saude','veiculo','residencial','imovel','embarcacao','empresarial','viagem','outro') NOT NULL DEFAULT 'outro',

  -- Bem/pessoa segurado (polimórfico; 'nenhum' = avulso, ex.: vida/saúde)
  item_tipo        ENUM('nenhum','imovel','veiculo','outro_bem','pessoa') NOT NULL DEFAULT 'nenhum',
  item_id          INT NULL,

  -- Seguradora / corretagem
  seguradora       VARCHAR(120) NULL,
  corretora        VARCHAR(120) NULL,
  corretor_nome    VARCHAR(120) NULL,
  corretor_contato VARCHAR(120) NULL,                    -- telefone / e-mail
  numero_apolice   VARCHAR(80)  NULL,

  -- Vigência (vigencia_fim alimenta a Agenda/Alertas)
  vigencia_inicio  DATE NULL,
  vigencia_fim     DATE NULL,

  -- Valores
  valor_segurado   DECIMAL(15,2) NULL,
  premio           DECIMAL(15,2) NULL,                   -- valor do prêmio (custo)
  franquia         DECIMAL(15,2) NULL,
  forma_pagamento  ENUM('anual','semestral','mensal','parcelado','unico','outro') NULL,

  -- Detalhes
  cobertura        TEXT NULL,
  beneficiarios    TEXT NULL,                            -- p/ seguro de vida
  status           ENUM('vigente','vencida','cancelada','em_cotacao') NOT NULL DEFAULT 'vigente',
  observacoes      TEXT NULL,

  ativo            TINYINT(1) NOT NULL DEFAULT 1,
  criado_em        DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_seguros_cliente (cliente_id),
  INDEX idx_seguros_vigencia (vigencia_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- documentos: permitir anexos de seguro (tipo_referencia += 'seguro').
-- Categorias já existentes cobrem apólice/boleto/outro.
-- (MODIFY com a UNIÃO dos valores para não perder enums já presentes.)
-- ------------------------------------------------------------
ALTER TABLE documentos
  MODIFY tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira','seguro')
  NOT NULL DEFAULT 'imovel';
