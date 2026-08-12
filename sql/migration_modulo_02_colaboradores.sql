-- ============================================================================
-- Migração — Módulo 02: Colaboradores (12/08/2026)
-- Cadastro de RH dos colaboradores do cliente (motorista, caseiro, secretária,
-- equipe...) — dados pessoais, cargo/salário, saúde, uniformes, benefícios +
-- sub-tabelas de dependentes e histórico (férias/promoções/advertências...).
--
-- NOVA migration pós-sincronização de produção — rodar no phpMyAdmin.
-- ============================================================================

USE gestao_patrimonial;

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
