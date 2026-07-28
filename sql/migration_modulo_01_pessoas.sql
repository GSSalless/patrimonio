-- =====================================================================
-- Módulo 01 — Pessoas (cadastro PF completo + família/sucessão + testamento)
-- Estende `clientes` (âncora do sistema) e adiciona sub-tabelas 1:N.
-- Rodar no banco gestao_patrimonial (local e produção).
-- =====================================================================

-- 1) Campos ampliados na tabela clientes (Pessoa Física) ----------------
ALTER TABLE clientes
  ADD COLUMN nome_completo   VARCHAR(200) NULL AFTER nome,
  ADD COLUMN apelido         VARCHAR(80)  NULL AFTER nome_completo,
  ADD COLUMN rg              VARCHAR(20)  NULL AFTER cpf_cnpj,
  ADD COLUMN rg_orgao        VARCHAR(30)  NULL AFTER rg,
  ADD COLUMN rg_uf           VARCHAR(2)   NULL AFTER rg_orgao,
  ADD COLUMN data_nascimento DATE         NULL AFTER rg_uf,
  ADD COLUMN naturalidade    VARCHAR(80)  NULL AFTER data_nascimento,
  ADD COLUMN nacionalidade   VARCHAR(60)  NULL DEFAULT 'Brasileira' AFTER naturalidade,
  ADD COLUMN estado_civil    ENUM('solteiro','casado','divorciado','viuvo','uniao_estavel','separado') NULL AFTER nacionalidade,
  ADD COLUMN regime_bens     ENUM('comunhao_parcial','comunhao_universal','separacao_total','participacao_final','nao_aplicavel') NULL AFTER estado_civil,
  ADD COLUMN profissao       VARCHAR(80)  NULL AFTER regime_bens,
  ADD COLUMN tipo_sanguineo  ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL AFTER profissao,
  ADD COLUMN nome_pai        VARCHAR(200) NULL AFTER tipo_sanguineo,
  ADD COLUMN nome_mae        VARCHAR(200) NULL AFTER nome_pai,
  ADD COLUMN cep             VARCHAR(9)   NULL AFTER nome_mae,
  ADD COLUMN logradouro      VARCHAR(200) NULL AFTER cep,
  ADD COLUMN numero          VARCHAR(20)  NULL AFTER logradouro,
  ADD COLUMN complemento     VARCHAR(100) NULL AFTER numero,
  ADD COLUMN bairro          VARCHAR(100) NULL AFTER complemento,
  ADD COLUMN cidade          VARCHAR(100) NULL AFTER bairro,
  ADD COLUMN uf              VARCHAR(2)   NULL AFTER cidade,
  ADD COLUMN tem_testamento  TINYINT(1)   NOT NULL DEFAULT 0 AFTER uf,
  ADD COLUMN testamento_obs  TEXT         NULL AFTER tem_testamento,
  ADD COLUMN observacoes     TEXT         NULL AFTER testamento_obs;

-- 2) Telefones adicionais (o principal continua em clientes.telefone) ----
CREATE TABLE IF NOT EXISTS pessoa_telefones (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  rotulo     VARCHAR(40)  NULL,            -- ex.: Celular, Comercial, Recado
  numero     VARCHAR(20)  NOT NULL,
  whatsapp   TINYINT(1)   NOT NULL DEFAULT 0,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3) E-mails adicionais (o principal continua em clientes.email) ---------
CREATE TABLE IF NOT EXISTS pessoa_emails (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  rotulo     VARCHAR(40)  NULL,            -- ex.: Pessoal, Comercial
  email      VARCHAR(150) NOT NULL,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4) Endereços adicionais (o principal fica inline em clientes) ----------
CREATE TABLE IF NOT EXISTS pessoa_enderecos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT NOT NULL,
  rotulo      VARCHAR(40)  NULL,           -- ex.: Comercial, Praia, Rural
  cep         VARCHAR(9)   NULL,
  logradouro  VARCHAR(200) NULL,
  numero      VARCHAR(20)  NULL,
  complemento VARCHAR(100) NULL,
  bairro      VARCHAR(100) NULL,
  cidade      VARCHAR(100) NULL,
  uf          VARCHAR(2)   NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5) Família / sucessão --------------------------------------------------
CREATE TABLE IF NOT EXISTS pessoa_familiares (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id      INT NOT NULL,
  parentesco      ENUM('conjuge','ex_conjuge','pai','mae','filho','dependente','herdeiro','irmao','neto','outro') NOT NULL DEFAULT 'outro',
  nome            VARCHAR(200) NOT NULL,
  cpf             VARCHAR(14)  NULL,
  data_nascimento DATE         NULL,
  contato         VARCHAR(60)  NULL,
  observacoes     VARCHAR(255) NULL,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6) Contatos de emergência ---------------------------------------------
CREATE TABLE IF NOT EXISTS pessoa_contatos_emergencia (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT NOT NULL,
  nome        VARCHAR(200) NOT NULL,
  parentesco  VARCHAR(60)  NULL,
  telefone    VARCHAR(20)  NULL,
  observacoes VARCHAR(255) NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7) Novas categorias de documento para a pessoa (tipo_referencia='cliente')
--    O enum abaixo é a UNIÃO de todos os valores já existentes (repo + local +
--    produção) para não truncar dados em nenhum ambiente. Só adiciona:
--    testamento, identidade, comprovante_residencia, certidao, procuracao.
ALTER TABLE documentos
  MODIFY COLUMN categoria ENUM(
    'escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto',
    'boleto','nf','crlv','apolice','manutencao','cnpj','contrato',
    'conta_financeira','extrato',
    'testamento','identidade','comprovante_residencia','certidao','procuracao',
    'outro'
  ) NOT NULL DEFAULT 'outro';
