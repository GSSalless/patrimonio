-- ============================================================================
-- Migração — Módulo 10: Investimentos (12/08/2026)
-- Carteira de investimentos do cliente (renda fixa, fundos, ações, previdência,
-- cripto, offshore...) + histórico de movimentos (aplicações/resgates/rendimentos).
-- Vínculo opcional à conta financeira (Módulo 09). valor_atual entra no
-- patrimônio consolidado; data_vencimento alimenta a Agenda (Módulo 14).
--
-- NOVA migration pós-sincronização de produção (12/08): rodar no phpMyAdmin.
-- ============================================================================

USE gestao_patrimonial;

-- ------------------------------------------------------------
-- Tabela: investimentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS investimentos (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- IV-0001 (gerado na aplicação)
  conta_id            INT NULL,                          -- vínculo à conta financeira (Mód. 09)

  nome                VARCHAR(180) NOT NULL,             -- ex.: "CDB Banco X 2027", "Tesouro IPCA+ 2035"
  classe              ENUM('renda_fixa','tesouro','fundo','multimercado','acoes','previdencia','offshore','cripto','outro') NOT NULL DEFAULT 'renda_fixa',
  instituicao         VARCHAR(120) NULL,                 -- banco/corretora/gestora (fallback do conta_id)
  emissor             VARCHAR(120) NULL,                 -- emissor do papel (renda fixa)

  -- Rentabilidade contratada (texto livre p/ flexibilidade: "110% do CDI", "IPCA + 5,5%")
  indexador           ENUM('pre','cdi','ipca','selic','cambio','misto','na') NULL,
  rentabilidade_contratada VARCHAR(80) NULL,

  -- Aplicação / valores
  data_aplicacao      DATE NULL,
  data_vencimento     DATE NULL,                         -- alimenta a Agenda (renda fixa)
  valor_aplicado      DECIMAL(15,2) NULL,
  valor_atual         DECIMAL(15,2) NULL,                -- entra no patrimônio consolidado
  quantidade          DECIMAL(18,6) NULL,                -- cotas/ações/cripto

  -- Liquidez / carência
  liquidez            ENUM('D0','D1','D2','D30','D90','vencimento','outro') NULL,
  carencia_ate        DATE NULL,

  -- Tributação
  ir_aliquota         DECIMAL(5,2) NULL,                 -- % de IR
  tem_iof             TINYINT(1) NOT NULL DEFAULT 0,
  come_cotas          TINYINT(1) NOT NULL DEFAULT 0,

  -- Custos
  taxa_administracao  DECIMAL(5,2) NULL,                 -- % a.a.
  taxa_performance    VARCHAR(80)  NULL,                 -- ex.: "20% sobre o que exceder o CDI"

  status              ENUM('ativo','resgatado','vencido') NOT NULL DEFAULT 'ativo',
  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_investimentos_cliente (cliente_id),
  INDEX idx_investimentos_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: investimento_movimentos (aplicações, resgates, rendimentos)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS investimento_movimentos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  investimento_id  INT NOT NULL,
  data             DATE NOT NULL,
  tipo             ENUM('aplicacao','resgate','rendimento','ajuste') NOT NULL DEFAULT 'aplicacao',
  valor            DECIMAL(15,2) NOT NULL,
  observacoes      VARCHAR(255) NULL,
  criado_em        DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- documentos: anexos de investimento (proposta, regulamento, extrato).
-- MODIFY com a UNIÃO dos valores atuais + 'investimento'/'proposta'/'regulamento'.
-- ------------------------------------------------------------
ALTER TABLE documentos
  MODIFY tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira','seguro','empresa','investimento')
  NOT NULL DEFAULT 'imovel';

ALTER TABLE documentos
  MODIFY categoria
  ENUM('escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto','boleto','nf','crlv','apolice','manutencao','cnpj','contrato','contrato_social','conta_financeira','extrato','testamento','identidade','comprovante_residencia','certidao','procuracao','proposta','regulamento','outro')
  NOT NULL DEFAULT 'outro';
