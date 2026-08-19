-- ============================================================================
-- FIX PRODUÇÃO — erro 500 pós-upload (14/08/2026)
-- Cria as tabelas dos Módulos 10 (Investimentos), 04 (Fornecedores) e 02
-- (Colaboradores), que o upload de arquivos NÃO cria. Sem estas tabelas, a
-- Gestão Geral / Dashboard / Agenda / Investimentos / Fornecedores /
-- Colaboradores dão erro 500.
--
-- COMO RODAR (Hostinger phpMyAdmin):
--   1. Selecione o banco  u250260449_cezar_db  na lista à esquerda.
--   2. Aba SQL → cole TODO este arquivo → Executar.
-- NÃO há linha USE — ele roda no banco que você selecionar. Idempotente
-- (CREATE TABLE IF NOT EXISTS): rodar de novo não quebra nada.
-- ============================================================================

-- ####################################################################
-- >>> migration_modulo_10_investimentos.sql
-- ####################################################################
-- ============================================================================
-- Migração — Módulo 10: Investimentos (12/08/2026)
-- Carteira de investimentos do cliente (renda fixa, fundos, ações, previdência,
-- cripto, offshore...) + histórico de movimentos (aplicações/resgates/rendimentos).
-- Vínculo opcional à conta financeira (Módulo 09). valor_atual entra no
-- patrimônio consolidado; data_vencimento alimenta a Agenda (Módulo 14).
--
-- NOVA migration pós-sincronização de produção (12/08): rodar no phpMyAdmin.
-- ============================================================================


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

-- ####################################################################
-- >>> migration_modulo_04_fornecedores.sql
-- ####################################################################
-- ============================================================================
-- Migração — Módulo 04: Fornecedores e Parceiros (12/08/2026)
-- Prestadores/parceiros do cliente (contabilidade, jurídico, seguros, marina,
-- saúde, tecnologia...) com classificação, contrato, dados financeiros e
-- avaliação. A vigência do contrato alimenta a Agenda (Módulo 14).
--
-- NOVA migration pós-sincronização de produção — rodar no phpMyAdmin.
-- ============================================================================


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

-- ####################################################################
-- >>> migration_modulo_02_colaboradores.sql
-- ####################################################################
-- ============================================================================
-- Migração — Módulo 02: Colaboradores (12/08/2026)
-- Cadastro de RH dos colaboradores do cliente (motorista, caseiro, secretária,
-- equipe...) — dados pessoais, cargo/salário, saúde, uniformes, benefícios +
-- sub-tabelas de dependentes e histórico (férias/promoções/advertências...).
--
-- NOVA migration pós-sincronização de produção — rodar no phpMyAdmin.
-- ============================================================================


CREATE TABLE IF NOT EXISTS colaboradores (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- CO-0001 (gerado na aplicação)

  -- Dados pessoais
  nome                VARCHAR(180) NOT NULL,
  cpf                 VARCHAR(14)  NULL,
  rg                  VARCHAR(20)  NULL,
  data_nascimento     DATE NULL,
  estado_civil        ENUM('solteiro','casado','divorciado','viuvo','uniao_estavel','separado') NULL,
  tipo_sanguineo      ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
  telefone            VARCHAR(40)  NULL,
  email               VARCHAR(140) NULL,
  cep                 VARCHAR(10)  NULL,
  logradouro          VARCHAR(180) NULL,
  numero              VARCHAR(20)  NULL,
  complemento         VARCHAR(120) NULL,
  bairro              VARCHAR(120) NULL,
  cidade              VARCHAR(120) NULL,
  estado              CHAR(2)      NULL,

  -- RH / contrato
  cargo               VARCHAR(120) NULL,
  departamento        VARCHAR(120) NULL,
  gestor_nome         VARCHAR(140) NULL,
  tipo_contrato       ENUM('clt','pj','autonomo','diarista','temporario','estagio','outro') NULL,
  jornada             VARCHAR(80)  NULL,
  data_admissao       DATE NULL,
  data_demissao       DATE NULL,
  salario             DECIMAL(15,2) NULL,
  status              ENUM('ativo','experiencia','afastado','ferias','desligado') NOT NULL DEFAULT 'ativo',

  -- Escolaridade / saúde
  escolaridade        ENUM('fundamental','medio','tecnico','superior','pos','mestrado','doutorado','outro') NULL,
  formacao            VARCHAR(180) NULL,
  convenio_medico     VARCHAR(140) NULL,
  alergias            VARCHAR(255) NULL,

  -- Uniformes
  uniforme_camiseta   VARCHAR(20) NULL,
  uniforme_camisa     VARCHAR(20) NULL,
  uniforme_calca      VARCHAR(20) NULL,
  uniforme_calcado    VARCHAR(20) NULL,

  -- Benefícios
  vale_alimentacao    DECIMAL(15,2) NULL,
  plano_saude         VARCHAR(140) NULL,
  seguro_vida         VARCHAR(140) NULL,
  outros_beneficios   TEXT NULL,

  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_colaboradores_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dependentes (cônjuge, filhos...)
CREATE TABLE IF NOT EXISTS colaborador_dependentes (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id  INT NOT NULL,
  nome            VARCHAR(180) NOT NULL,
  parentesco      ENUM('conjuge','filho','filha','pai','mae','outro') NOT NULL DEFAULT 'filho',
  data_nascimento DATE NULL,
  cpf             VARCHAR(14) NULL,
  observacoes     VARCHAR(255) NULL,
  criado_em       DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Histórico de RH (salários, promoções, advertências, avaliações, férias,
-- atestados, treinamentos, faltas...) num só lugar por tipo.
CREATE TABLE IF NOT EXISTS colaborador_historico (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id  INT NOT NULL,
  data            DATE NOT NULL,
  data_fim        DATE NULL,                             -- p/ férias/afastamento (período)
  tipo            ENUM('salario','promocao','advertencia','avaliacao','ferias','atestado','treinamento','falta','beneficio','outro') NOT NULL DEFAULT 'outro',
  descricao       VARCHAR(255) NULL,
  valor           DECIMAL(15,2) NULL,                    -- p/ salário/benefício
  observacoes     VARCHAR(255) NULL,
  criado_em       DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- documentos: anexos de colaborador (contrato, RG, etc.) — só tipo_referencia
-- += 'colaborador' (categorias existentes cobrem contrato/identidade/outro).
ALTER TABLE documentos
  MODIFY tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo','condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao','conta_financeira','seguro','empresa','investimento','fornecedor','colaborador')
  NOT NULL DEFAULT 'imovel';

