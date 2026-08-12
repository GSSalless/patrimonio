-- ============================================================================
-- Migração — Módulo 03: Empresas (12/08/2026)
-- Cadastro de empresas/holdings do cliente (razão social, CNPJ, regime, capital,
-- endereço, contabilidade) + quadro societário (empresa_socios, 1:N).
-- ============================================================================

USE gestao_patrimonial;

-- ------------------------------------------------------------
-- Tabela: empresas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS empresas (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- EM-0001 (gerado na aplicação)

  razao_social        VARCHAR(180) NOT NULL,
  nome_fantasia       VARCHAR(180) NULL,
  cnpj                VARCHAR(20)  NULL,
  natureza            ENUM('operacional','holding_patrimonial','holding_participacao','spe','outro') NOT NULL DEFAULT 'operacional',
  natureza_juridica   VARCHAR(60)  NULL,                -- LTDA, S/A, EIRELI, SLU, MEI…
  regime_tributario   ENUM('simples','lucro_presumido','lucro_real','mei','imune','outro') NULL,
  situacao            ENUM('ativa','baixada','suspensa','inapta') NOT NULL DEFAULT 'ativa',

  inscricao_estadual  VARCHAR(40)  NULL,
  inscricao_municipal VARCHAR(40)  NULL,
  cnae_principal      VARCHAR(120) NULL,
  cnaes_secundarios   TEXT NULL,
  capital_social      DECIMAL(15,2) NULL,
  data_abertura       DATE NULL,

  -- Endereço
  cep                 VARCHAR(10)  NULL,
  logradouro          VARCHAR(180) NULL,
  numero              VARCHAR(20)  NULL,
  complemento         VARCHAR(120) NULL,
  bairro              VARCHAR(120) NULL,
  cidade              VARCHAR(120) NULL,
  estado              CHAR(2)      NULL,

  -- Contato
  telefone            VARCHAR(40)  NULL,
  email               VARCHAR(140) NULL,
  site                VARCHAR(140) NULL,

  -- Contabilidade
  contador_nome       VARCHAR(140) NULL,
  contador_contato    VARCHAR(140) NULL,

  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_empresas_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: empresa_socios (quadro societário / administradores)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS empresa_socios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id    INT NOT NULL,
  nome          VARCHAR(180) NOT NULL,
  cpf_cnpj      VARCHAR(20)  NULL,
  participacao  DECIMAL(6,3) NULL,                      -- % de participação (0-100)
  funcao        ENUM('socio','administrador','socio_administrador','procurador','outro') NOT NULL DEFAULT 'socio',
  observacoes   VARCHAR(255) NULL,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- documentos: anexos de empresa (tipo_referencia += 'empresa';
--   categoria += 'contrato_social'). MODIFY com a UNIÃO dos valores atuais.
-- ------------------------------------------------------------
ALTER TABLE documentos
  MODIFY tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira','seguro','empresa')
  NOT NULL DEFAULT 'imovel';

ALTER TABLE documentos
  MODIFY categoria
  ENUM('escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto','boleto','nf','crlv','apolice','manutencao','cnpj','contrato','contrato_social','conta_financeira','extrato','testamento','identidade','comprovante_residencia','certidao','procuracao','outro')
  NOT NULL DEFAULT 'outro';
