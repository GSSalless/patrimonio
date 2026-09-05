-- ============================================================================
-- Migração — Módulo 09: Contas Financeiras (21/07/2026)
-- Cadastro de contas bancárias/digitais do cliente + histórico de saldos.
-- Campos de integração preparados para o vínculo futuro com o Asaas
-- (https://docs.asaas.com): identificação da conta/subconta, walletId e chave
-- de API por conta.
-- ============================================================================

USE gestao_patrimonial;

-- ------------------------------------------------------------
-- Tabela: contas_financeiras
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contas_financeiras (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id        INT NOT NULL,
  codigo            VARCHAR(10) NOT NULL,                -- CF-0001 (gerado na aplicação)
  apelido           VARCHAR(120) NOT NULL,               -- ex.: "Conta principal Itaú"
  tipo              ENUM('corrente','poupanca','pagamento','investimento','internacional','outro') NOT NULL DEFAULT 'corrente',

  -- Instituição
  instituicao       VARCHAR(120) NULL,                   -- banco / corretora / fintech
  banco_codigo      VARCHAR(10)  NULL,                   -- nº COMPE (341, 077…)
  agencia           VARCHAR(20)  NULL,
  numero_conta      VARCHAR(30)  NULL,                   -- com dígito
  moeda             CHAR(3) NOT NULL DEFAULT 'BRL',

  -- PIX
  pix_tipo          ENUM('cpf','cnpj','email','telefone','aleatoria') NULL,
  pix_chave         VARCHAR(140) NULL,

  -- Internacional
  swift             VARCHAR(20) NULL,
  iban              VARCHAR(40) NULL,
  routing           VARCHAR(20) NULL,                    -- ABA/routing number

  -- Titularidade (quando difere do cliente: empresa, holding…)
  titular_nome      VARCHAR(140) NULL,
  titular_cpf_cnpj  VARCHAR(20)  NULL,

  -- Relacionamento
  gerente_nome      VARCHAR(120) NULL,
  gerente_contato   VARCHAR(120) NULL,                   -- telefone / e-mail

  -- Saldo (espelho do registro mais recente em conta_saldos)
  saldo_atual       DECIMAL(15,2) NULL,
  saldo_data        DATE NULL,

  -- Integração (Asaas — vínculo futuro via API)
  integracao        ENUM('nenhuma','asaas') NOT NULL DEFAULT 'nenhuma',
  asaas_account_id  VARCHAR(64)  NULL,                   -- id da conta/subconta no Asaas
  asaas_wallet_id   VARCHAR(64)  NULL,                   -- walletId (split/transferências)
  asaas_api_key     VARCHAR(255) NULL,                   -- chave de API da conta

  observacoes       TEXT NULL,
  ativo             TINYINT(1) NOT NULL DEFAULT 1,
  criado_em         DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Sub-tabela: conta_saldos (histórico de saldos)
-- O registro mais recente (por data) atualiza saldo_atual/saldo_data da conta.
-- `origem` = 'asaas' será usado pela sincronização automática via API.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS conta_saldos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  conta_id    INT NOT NULL,
  data        DATE NOT NULL,
  saldo       DECIMAL(15,2) NOT NULL,
  origem      ENUM('manual','asaas') NOT NULL DEFAULT 'manual',
  observacoes VARCHAR(255) NULL,
  criado_em   DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (conta_id) REFERENCES contas_financeiras(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- documentos: aceitar uploads vinculados a contas (contrato de abertura,
-- extratos…). Enum atual + 'conta_financeira' / + 'extrato'.
-- ------------------------------------------------------------
ALTER TABLE documentos
  MODIFY tipo_referencia ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira') NOT NULL DEFAULT 'imovel';

ALTER TABLE documentos
  MODIFY categoria ENUM('escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto','boleto','nf','crlv','apolice','manutencao','cnpj','contrato','extrato','outro') NOT NULL DEFAULT 'outro';
