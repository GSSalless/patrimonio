-- ============================================================
-- Sistema de Gestão Patrimonial — César Cordeiro
-- Schema F1 (MVP)
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestao_patrimonial
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestao_patrimonial;

-- ------------------------------------------------------------
-- USUÁRIOS (César + contas dos clientes)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(150) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  senha_hash    VARCHAR(255) NOT NULL,
  nivel         ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CLIENTES (pessoas cujo patrimônio é gerido pelo César)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id      INT NULL,                          -- conta de acesso (opcional)
  tipo_pessoa     ENUM('PF','PJ') NOT NULL DEFAULT 'PF',
  nome            VARCHAR(200) NOT NULL,             -- nome curto/display
  nome_completo   VARCHAR(200) NULL,                 -- nome civil completo
  apelido         VARCHAR(80)  NULL,
  cpf_cnpj        VARCHAR(18)  NOT NULL,
  rg              VARCHAR(20)  NULL,
  rg_orgao        VARCHAR(30)  NULL,
  rg_uf           VARCHAR(2)   NULL,
  data_nascimento DATE         NULL,
  naturalidade    VARCHAR(80)  NULL,
  nacionalidade   VARCHAR(60)  NULL DEFAULT 'Brasileira',
  estado_civil    ENUM('solteiro','casado','divorciado','viuvo','uniao_estavel','separado') NULL,
  regime_bens     ENUM('comunhao_parcial','comunhao_universal','separacao_total','participacao_final','nao_aplicavel') NULL,
  profissao       VARCHAR(80)  NULL,
  tipo_sanguineo  ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
  nome_pai        VARCHAR(200) NULL,
  nome_mae        VARCHAR(200) NULL,
  cep             VARCHAR(9)   NULL,                 -- endereço principal (extras em pessoa_enderecos)
  logradouro      VARCHAR(200) NULL,
  numero          VARCHAR(20)  NULL,
  complemento     VARCHAR(100) NULL,
  bairro          VARCHAR(100) NULL,
  cidade          VARCHAR(100) NULL,
  uf              VARCHAR(2)   NULL,
  email           VARCHAR(150) NULL,                 -- principal (extras em pessoa_emails)
  telefone        VARCHAR(20)  NULL,                 -- principal (extras em pessoa_telefones)
  tem_testamento  TINYINT(1) NOT NULL DEFAULT 0,
  testamento_obs  TEXT         NULL,
  observacoes     TEXT         NULL,
  ativo           TINYINT(1) NOT NULL DEFAULT 1,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MÓDULO 01 — PESSOAS: sub-tabelas 1:N do cliente/pessoa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pessoa_telefones (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  rotulo     VARCHAR(40)  NULL,
  numero     VARCHAR(20)  NOT NULL,
  whatsapp   TINYINT(1)   NOT NULL DEFAULT 0,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pessoa_emails (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  rotulo     VARCHAR(40)  NULL,
  email      VARCHAR(150) NOT NULL,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pessoa_enderecos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT NOT NULL,
  rotulo      VARCHAR(40)  NULL,
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

-- ------------------------------------------------------------
-- CONDOMÍNIOS (entidade separada — vários imóveis no mesmo)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS condominios (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nome            VARCHAR(200) NOT NULL,
  cnpj            VARCHAR(20)  NULL,
  endereco        VARCHAR(300) NULL,
  cep             VARCHAR(10)  NULL,
  bairro          VARCHAR(100) NULL,
  cidade          VARCHAR(100) NULL,
  estado          CHAR(2)      NULL,
  numero_blocos   INT          NULL,
  numero_unidades INT          NULL,
  -- Síndico / administradora
  sindico_nome            VARCHAR(150) NULL,
  sindico_telefone        VARCHAR(30)  NULL,
  sindico_email           VARCHAR(150) NULL,
  mandato_fim             DATE         NULL,
  administradora          VARCHAR(150) NULL,
  administradora_telefone VARCHAR(30)  NULL,
  -- Valores
  valor_taxa          DECIMAL(10,2) NULL,
  valor_fundo_reserva DECIMAL(10,2) NULL,
  dia_vencimento      INT           NULL,
  -- Comodidades (sim/não)
  tem_piscina       TINYINT(1) NOT NULL DEFAULT 0,
  tem_churrasqueira TINYINT(1) NOT NULL DEFAULT 0,
  tem_salao_festas  TINYINT(1) NOT NULL DEFAULT 0,
  tem_academia      TINYINT(1) NOT NULL DEFAULT 0,
  tem_playground    TINYINT(1) NOT NULL DEFAULT 0,
  tem_quadra        TINYINT(1) NOT NULL DEFAULT 0,
  tem_portaria_24h  TINYINT(1) NOT NULL DEFAULT 0,
  tem_elevador      TINYINT(1) NOT NULL DEFAULT 0,
  tem_gerador       TINYINT(1) NOT NULL DEFAULT 0,
  tem_salao_jogos   TINYINT(1) NOT NULL DEFAULT 0,
  tem_coworking     TINYINT(1) NOT NULL DEFAULT 0,
  tem_pet           TINYINT(1) NOT NULL DEFAULT 0,
  observacoes       TEXT NULL,
  criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- imoveis.condominio_id (N:1) faz o vínculo imóvel → condomínio

-- ------------------------------------------------------------
-- IMÓVEIS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS imoveis (
  id                    INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id            INT NOT NULL,
  condominio_id         INT NULL,

  -- Bloco 1: Identificação
  codigo                VARCHAR(20)  NOT NULL,       -- IM-0001 (gerado pela app)
  nome_referencia       VARCHAR(200) NOT NULL,
  tipo                  ENUM('apartamento','casa','sala_comercial','terreno','galpao','loja','hotel','outro') NOT NULL,
  finalidade            ENUM('residencial','comercial','mista','locacao','investimento') NOT NULL,
  situacao              ENUM('pronto','em_construcao','na_planta') NOT NULL DEFAULT 'pronto',

  -- Bloco 2: Localização
  logradouro            VARCHAR(200) NULL,
  numero                VARCHAR(20)  NULL,
  complemento           VARCHAR(100) NULL,
  cep                   VARCHAR(10)  NULL,
  bairro                VARCHAR(100) NULL,
  cidade                VARCHAR(100) NULL,
  estado                CHAR(2)      NULL,
  pais                  VARCHAR(60)  NOT NULL DEFAULT 'Brasil',

  -- Bloco 3: Matrícula (parcial F1)
  inscricao_municipal   VARCHAR(60)  NULL,           -- nº IPTU / inscrição (exibido no Bloco 1)
  site_cartorio         VARCHAR(255) NULL,           -- link do site do cartório

  -- Bloco 4: Titularidade
  data_aquisicao        DATE         NULL,
  forma_aquisicao       ENUM('compra','heranca','doacao','permuta','integralizacao','outro') NULL,
  outros_proprietarios  VARCHAR(500) NULL,           -- nomes dos coproprietários (quando participação < 100%)

  -- Bloco 6: Financeiro
  valor_compra          DECIMAL(15,2) NULL,
  valor_entrada         DECIMAL(15,2) NULL,
  valor_financiamento   DECIMAL(15,2) NULL,
  banco_financiador     VARCHAR(150)  NULL,
  prazo_financiamento   INT           NULL,          -- meses
  taxa_juros_anual      DECIMAL(6,4)  NULL,          -- ex: 0.0899 = 8,99%
  valor_mercado         DECIMAL(15,2) NULL,
  data_avaliacao_mercado DATE          NULL,

  -- Custos mensais recorrentes (referência — lançamentos gerados separado)
  custo_condominio      DECIMAL(10,2) NULL,
  custo_iptu_mensal     DECIMAL(10,2) NULL,          -- IPTU ÷ 12 (referência)
  custo_energia         DECIMAL(10,2) NULL,
  custo_agua            DECIMAL(10,2) NULL,
  custo_internet        DECIMAL(10,2) NULL,
  custo_outros          DECIMAL(10,2) NULL,

  foto_principal        VARCHAR(300) NULL,           -- caminho relativo do arquivo
  observacoes           TEXT         NULL,
  ativo                 TINYINT(1) NOT NULL DEFAULT 1,
  criado_em             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (cliente_id)   REFERENCES clientes(id)    ON DELETE RESTRICT,
  FOREIGN KEY (condominio_id) REFERENCES condominios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- IPTU (registro anual por imóvel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS iptu (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id         INT NOT NULL,
  ano               YEAR        NOT NULL,
  inscricao_iptu    VARCHAR(60) NULL,
  valor_total       DECIMAL(10,2) NOT NULL,
  parcelas          INT          NOT NULL DEFAULT 1,
  data_vencimento_1 DATE         NULL,              -- vencimento da 1ª parcela / cota única
  pago              TINYINT(1)  NOT NULL DEFAULT 0,
  data_pagamento    DATE         NULL,
  observacoes       TEXT         NULL,
  criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONDOMÍNIO — FATURAS MENSAIS (por competência)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS condominio_faturas (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id       INT NOT NULL,
  competencia     DATE NOT NULL,                    -- primeiro dia do mês: 2026-06-01
  valor           DECIMAL(10,2) NOT NULL,
  descricao_extra VARCHAR(200)  NULL,               -- ex: "churrasqueira"
  pago            TINYINT(1)   NOT NULL DEFAULT 0,
  data_pagamento  DATE         NULL,
  arquivo_boleto  VARCHAR(300) NULL,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REFORMAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reformas (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id         INT NOT NULL,
  descricao         VARCHAR(300) NOT NULL,
  status            ENUM('planejado','em_andamento','concluido','cancelado') NOT NULL DEFAULT 'planejado',
  data_inicio       DATE         NULL,
  data_fim_prevista DATE         NULL,
  data_fim_real     DATE         NULL,
  custo_previsto    DECIMAL(12,2) NULL,
  custo_realizado   DECIMAL(12,2) NULL,
  fornecedor        VARCHAR(200) NULL,
  observacoes       TEXT         NULL,
  criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTRATOS DE LOCAÇÃO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contratos_locacao (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id         INT NOT NULL,
  locatario_nome    VARCHAR(200) NOT NULL,
  locatario_cpf_cnpj VARCHAR(18) NULL,
  data_inicio       DATE        NOT NULL,
  data_fim          DATE        NULL,
  valor_aluguel     DECIMAL(10,2) NOT NULL,
  dia_vencimento    TINYINT      NOT NULL DEFAULT 10,  -- dia do mês
  status            ENUM('ativo','encerrado','rescindido') NOT NULL DEFAULT 'ativo',
  observacoes       TEXT         NULL,
  criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LANÇAMENTOS FINANCEIROS (despesas e receitas do imóvel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lancamentos_financeiros (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id       INT NOT NULL,
  cliente_id      INT NOT NULL,
  tipo            ENUM('despesa','receita') NOT NULL,
  categoria       ENUM('iptu','condominio','energia','agua','internet','aluguel_recebido','reforma','manutencao','seguro','financiamento','outro') NOT NULL,
  descricao       VARCHAR(300) NOT NULL,
  valor           DECIMAL(12,2) NOT NULL,
  data_competencia DATE NOT NULL,
  data_pagamento  DATE NULL,
  pago            TINYINT(1) NOT NULL DEFAULT 0,
  referencia_tipo VARCHAR(50)  NULL,               -- 'reforma', 'contrato_locacao', etc.
  referencia_id   INT          NULL,
  observacoes     TEXT         NULL,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id)  REFERENCES imoveis(id)  ON DELETE CASCADE,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DOCUMENTOS (polimórfico)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS documentos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id       INT NOT NULL,
  tipo_referencia  ENUM('imovel','reforma','contrato_locacao','cliente') NOT NULL,
  referencia_id    INT NOT NULL,
  categoria        ENUM('escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto','boleto','nf','crlv','apolice','manutencao','cnpj','contrato','conta_financeira','extrato','testamento','identidade','comprovante_residencia','certidao','procuracao','outro') NOT NULL DEFAULT 'outro',
  nome_arquivo     VARCHAR(300) NOT NULL,           -- nome original
  caminho          VARCHAR(500) NOT NULL,           -- caminho relativo em /uploads/
  mime_type        VARCHAR(100) NULL,
  tamanho_bytes    INT          NULL,
  data_emissao     DATE         NULL,
  data_validade    DATE         NULL,
  descricao        VARCHAR(300) NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AVALIAÇÕES DE MERCADO (histórico de valorização)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS avaliacoes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id   INT NOT NULL,
  data        DATE          NOT NULL,
  valor       DECIMAL(15,2) NOT NULL,
  fonte       VARCHAR(150)  NULL,                  -- ex: "Avaliação CRECI", "Estimativa IA"
  observacoes TEXT          NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VEÍCULOS (Módulo 06 — Carros)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS veiculos (
  id                       INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id               INT NOT NULL,
  codigo                   VARCHAR(20) NOT NULL,              -- VE-0001
  -- Identificação
  marca                    VARCHAR(60)  NULL,
  modelo                   VARCHAR(120) NULL,
  ano_fabricacao           INT          NULL,
  ano_modelo               INT          NULL,
  cor                      VARCHAR(40)  NULL,
  combustivel              ENUM('gasolina','etanol','flex','diesel','gnv','eletrico','hibrido') NULL,
  placa                    VARCHAR(10)  NULL,
  renavam                  VARCHAR(20)  NULL,
  chassi                   VARCHAR(30)  NULL,
  -- Documentação
  vencimento_licenciamento DATE          NULL,
  multas                   VARCHAR(255)  NULL,
  -- Seguro
  seguradora               VARCHAR(120)  NULL,
  apolice                  VARCHAR(60)   NULL,
  franquia                 DECIMAL(15,2) NULL,
  vencimento_seguro        DATE          NULL,
  -- Financeiro
  data_aquisicao           DATE          NULL,
  valor_aquisicao          DECIMAL(15,2) NULL,
  valor_fipe               DECIMAL(15,2) NULL,
  valor_mercado            DECIMAL(15,2) NULL,
  -- Controle
  km_atual                 INT           NULL,
  consumo_medio            DECIMAL(6,2)  NULL,
  observacoes              TEXT          NULL,
  -- Metadados
  foto_principal           VARCHAR(300)  NULL,
  ativo                    TINYINT(1) NOT NULL DEFAULT 1,
  criado_em                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB;

-- Nota: a tabela `documentos` teve o ENUM tipo_referencia estendido com 'veiculo'
-- e categoria com 'crlv','apolice','manutencao' para suportar uploads de veículos.

-- ------------------------------------------------------------
-- OUTROS BENS (embarcações, joias, obras de arte, outros)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS outros_bens (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(20) NOT NULL,              -- OB-0001
  tipo                ENUM('embarcacao','joia','obra_de_arte','outro') NOT NULL,
  -- Identificação geral
  nome                VARCHAR(200) NOT NULL,
  descricao           TEXT         NULL,
  marca               VARCHAR(120) NULL,
  modelo              VARCHAR(120) NULL,
  ano                 INT          NULL,
  cor                 VARCHAR(60)  NULL,
  -- Embarcação
  tipo_embarcacao     ENUM('jet_ski','lancha','barco','veleiro','iate','outro') NULL,
  comprimento_m       DECIMAL(6,2) NULL,
  motor               VARCHAR(120) NULL,
  horimetro           INT          NULL,
  registro_embarcacao VARCHAR(60)  NULL,
  marina              VARCHAR(150) NULL,
  vaga_marina         VARCHAR(40)  NULL,
  mensalidade_marina  DECIMAL(10,2) NULL,
  -- Joia / Obra de Arte
  material            VARCHAR(120) NULL,
  quilates            DECIMAL(6,3) NULL,
  artista_autor       VARCHAR(150) NULL,
  dimensoes           VARCHAR(100) NULL,
  tecnica             VARCHAR(100) NULL,
  certificado         VARCHAR(200) NULL,
  -- Seguro
  seguradora          VARCHAR(120) NULL,
  apolice             VARCHAR(60)  NULL,
  franquia            DECIMAL(15,2) NULL,
  vencimento_seguro   DATE         NULL,
  -- Financeiro
  data_aquisicao      DATE         NULL,
  valor_aquisicao     DECIMAL(15,2) NULL,
  valor_mercado       DECIMAL(15,2) NULL,
  -- Metadados
  foto_principal      VARCHAR(300) NULL,
  observacoes         TEXT         NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: ENUM tipo_referencia de `documentos` estendido com 'outro_bem'.

-- ============================================================================
-- Sub-tabelas de histórico — Módulos 05/06/07 (ver sql/migration_modulos_567.sql)
-- ============================================================================

-- Módulo 05 — Imóveis: manutenções
CREATE TABLE IF NOT EXISTS manutencoes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  imovel_id     INT NOT NULL,
  data          DATE          NOT NULL,
  tipo          ENUM('eletrica','hidraulica','pintura','ar_condicionado','estrutural',
                     'jardim','elevador','seguranca','eletrodomestico','outro') NOT NULL DEFAULT 'outro',
  descricao     VARCHAR(300)  NOT NULL,
  fornecedor    VARCHAR(200)  NULL,
  valor         DECIMAL(12,2) NULL,
  garantia_ate  DATE          NULL,
  observacoes   TEXT          NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulo 06 — Veículos: abastecimentos
CREATE TABLE IF NOT EXISTS abastecimentos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  veiculo_id    INT NOT NULL,
  data          DATE          NOT NULL,
  combustivel   ENUM('gasolina','etanol','flex','diesel','gnv','eletrico') NULL,
  litros        DECIMAL(8,2)  NULL,
  valor_litro   DECIMAL(8,3)  NULL,
  valor_total   DECIMAL(10,2) NULL,
  km            INT           NULL,
  posto         VARCHAR(150)  NULL,
  tanque_cheio  TINYINT(1)    NOT NULL DEFAULT 1,
  observacoes   VARCHAR(300)  NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulo 06 — Veículos: manutenções
CREATE TABLE IF NOT EXISTS veiculo_manutencoes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  veiculo_id    INT NOT NULL,
  data          DATE          NOT NULL,
  tipo          ENUM('revisao','troca_oleo','pneus','freios','bateria','suspensao',
                     'eletrica','funilaria','peca','outro') NOT NULL DEFAULT 'revisao',
  descricao     VARCHAR(300)  NOT NULL,
  fornecedor    VARCHAR(200)  NULL,
  km            INT           NULL,
  valor         DECIMAL(12,2) NULL,
  garantia_ate  DATE          NULL,
  proxima_data  DATE          NULL,
  proxima_km    INT           NULL,
  observacoes   TEXT          NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulo 06 — Veículos: sinistros
CREATE TABLE IF NOT EXISTS sinistros (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  veiculo_id    INT NOT NULL,
  data          DATE          NOT NULL,
  tipo          ENUM('colisao','roubo','furto','avaria','vidro','terceiros','outro') NOT NULL DEFAULT 'colisao',
  descricao     VARCHAR(300)  NOT NULL,
  local         VARCHAR(200)  NULL,
  boletim_ocorrencia VARCHAR(60) NULL,
  acionou_seguro TINYINT(1)   NOT NULL DEFAULT 0,
  numero_sinistro VARCHAR(60) NULL,
  valor_prejuizo DECIMAL(12,2) NULL,
  valor_franquia DECIMAL(12,2) NULL,
  status        ENUM('aberto','em_analise','em_reparo','concluido','recusado') NOT NULL DEFAULT 'aberto',
  observacoes   TEXT          NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulo 07 — Outros Bens: manutenções
CREATE TABLE IF NOT EXISTS bem_manutencoes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  outro_bem_id  INT NOT NULL,
  data          DATE          NOT NULL,
  tipo          ENUM('revisao','motor','casco','pintura','eletrica','limpeza','peca','outro') NOT NULL DEFAULT 'revisao',
  descricao     VARCHAR(300)  NOT NULL,
  fornecedor    VARCHAR(200)  NULL,
  horimetro     INT           NULL,
  valor         DECIMAL(12,2) NULL,
  garantia_ate  DATE          NULL,
  proxima_data  DATE          NULL,
  observacoes   TEXT          NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (outro_bem_id) REFERENCES outros_bens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Módulo 07 — Outros Bens: avaliações
CREATE TABLE IF NOT EXISTS avaliacoes_bem (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  outro_bem_id  INT NOT NULL,
  data          DATE          NOT NULL,
  valor         DECIMAL(15,2) NOT NULL,
  fonte         VARCHAR(150)  NULL,
  observacoes   TEXT          NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (outro_bem_id) REFERENCES outros_bens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: ENUM tipo_referencia de `documentos` estendido com
--   'manutencao','veiculo_manutencao','sinistro','bem_manutencao' (docs das sub-tabelas de histórico).
-- Nota: ENUM categoria de `documentos` estendido com 'habite_se' (checklist documental do imóvel).

-- ============================================================================
-- Módulo 09 — Contas Financeiras (ver sql/migration_modulo_09_contas.sql)
-- ============================================================================

-- Contas bancárias/digitais do cliente. Campos asaas_* preparam o vínculo
-- futuro com o Asaas (conta digital: saldo/extrato/cobranças via API).
CREATE TABLE IF NOT EXISTS contas_financeiras (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id        INT NOT NULL,
  codigo            VARCHAR(10) NOT NULL,                -- CF-0001
  apelido           VARCHAR(120) NOT NULL,
  tipo              ENUM('corrente','poupanca','pagamento','investimento','internacional','outro') NOT NULL DEFAULT 'corrente',
  instituicao       VARCHAR(120) NULL,
  banco_codigo      VARCHAR(10)  NULL,                   -- nº COMPE
  agencia           VARCHAR(20)  NULL,
  numero_conta      VARCHAR(30)  NULL,
  moeda             CHAR(3) NOT NULL DEFAULT 'BRL',
  pix_tipo          ENUM('cpf','cnpj','email','telefone','aleatoria') NULL,
  pix_chave         VARCHAR(140) NULL,
  swift             VARCHAR(20) NULL,
  iban              VARCHAR(40) NULL,
  routing           VARCHAR(20) NULL,
  titular_nome      VARCHAR(140) NULL,                   -- quando difere do cliente
  titular_cpf_cnpj  VARCHAR(20)  NULL,
  gerente_nome      VARCHAR(120) NULL,
  gerente_contato   VARCHAR(120) NULL,
  saldo_atual       DECIMAL(15,2) NULL,                  -- espelho do conta_saldos mais recente
  saldo_data        DATE NULL,
  integracao        ENUM('nenhuma','asaas') NOT NULL DEFAULT 'nenhuma',
  asaas_account_id  VARCHAR(64)  NULL,
  asaas_wallet_id   VARCHAR(64)  NULL,
  asaas_api_key     VARCHAR(255) NULL,
  observacoes       TEXT NULL,
  ativo             TINYINT(1) NOT NULL DEFAULT 1,
  criado_em         DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Histórico de saldos (o mais recente atualiza saldo_atual/saldo_data da conta;
-- origem 'asaas' reservada para a sincronização automática via API).
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

-- Nota: ENUM tipo_referencia de `documentos` estendido com 'conta_financeira'
--   e categoria com 'extrato' (contrato de abertura e extratos da conta).

-- ============================================================================
-- Módulo 11 — Seguros (migration_modulo_11_seguros.sql · 11/08/2026)
-- Cadastro central de apólices. Vínculo polimórfico opcional (item_tipo+item_id)
-- a imóvel/veículo/outro bem/pessoa. `vigencia_fim` alimenta a Agenda (Mód. 14).
-- ============================================================================
CREATE TABLE IF NOT EXISTS seguros (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id       INT NOT NULL,
  codigo           VARCHAR(10) NOT NULL,                 -- SG-0001
  tipo             ENUM('vida','saude','veiculo','residencial','imovel','embarcacao','empresarial','viagem','outro') NOT NULL DEFAULT 'outro',
  item_tipo        ENUM('nenhum','imovel','veiculo','outro_bem','pessoa') NOT NULL DEFAULT 'nenhum',
  item_id          INT NULL,
  seguradora       VARCHAR(120) NULL,
  corretora        VARCHAR(120) NULL,
  corretor_nome    VARCHAR(120) NULL,
  corretor_contato VARCHAR(120) NULL,
  numero_apolice   VARCHAR(80)  NULL,
  vigencia_inicio  DATE NULL,
  vigencia_fim     DATE NULL,
  valor_segurado   DECIMAL(15,2) NULL,
  premio           DECIMAL(15,2) NULL,
  franquia         DECIMAL(15,2) NULL,
  forma_pagamento  ENUM('anual','semestral','mensal','parcelado','unico','outro') NULL,
  cobertura        TEXT NULL,
  beneficiarios    TEXT NULL,
  status           ENUM('vigente','vencida','cancelada','em_cotacao') NOT NULL DEFAULT 'vigente',
  observacoes      TEXT NULL,
  ativo            TINYINT(1) NOT NULL DEFAULT 1,
  criado_em        DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_seguros_cliente (cliente_id),
  INDEX idx_seguros_vigencia (vigencia_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: ENUM tipo_referencia de `documentos` estendido com 'seguro'
--   (categorias 'apolice'/'boleto'/'outro' já cobrem os anexos de seguro).

-- ============================================================================
-- Módulo 03 — Empresas (migration_modulo_03_empresas.sql · 12/08/2026)
-- Empresas/holdings do cliente + quadro societário (empresa_socios, 1:N).
-- ============================================================================
CREATE TABLE IF NOT EXISTS empresas (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- EM-0001
  razao_social        VARCHAR(180) NOT NULL,
  nome_fantasia       VARCHAR(180) NULL,
  cnpj                VARCHAR(20)  NULL,
  natureza            ENUM('operacional','holding_patrimonial','holding_participacao','spe','outro') NOT NULL DEFAULT 'operacional',
  natureza_juridica   VARCHAR(60)  NULL,
  regime_tributario   ENUM('simples','lucro_presumido','lucro_real','mei','imune','outro') NULL,
  situacao            ENUM('ativa','baixada','suspensa','inapta') NOT NULL DEFAULT 'ativa',
  inscricao_estadual  VARCHAR(40)  NULL,
  inscricao_municipal VARCHAR(40)  NULL,
  cnae_principal      VARCHAR(120) NULL,
  cnaes_secundarios   TEXT NULL,
  capital_social      DECIMAL(15,2) NULL,
  data_abertura       DATE NULL,
  cep                 VARCHAR(10)  NULL,
  logradouro          VARCHAR(180) NULL,
  numero              VARCHAR(20)  NULL,
  complemento         VARCHAR(120) NULL,
  bairro              VARCHAR(120) NULL,
  cidade              VARCHAR(120) NULL,
  estado              CHAR(2)      NULL,
  telefone            VARCHAR(40)  NULL,
  email               VARCHAR(140) NULL,
  site                VARCHAR(140) NULL,
  contador_nome       VARCHAR(140) NULL,
  contador_contato    VARCHAR(140) NULL,
  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_empresas_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresa_socios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id    INT NOT NULL,
  nome          VARCHAR(180) NOT NULL,
  cpf_cnpj      VARCHAR(20)  NULL,
  participacao  DECIMAL(6,3) NULL,
  funcao        ENUM('socio','administrador','socio_administrador','procurador','outro') NOT NULL DEFAULT 'socio',
  observacoes   VARCHAR(255) NULL,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: ENUM tipo_referencia de `documentos` estendido com 'empresa' e
--   categoria com 'contrato_social' (contrato social e alterações da empresa).

-- ============================================================================
-- Módulo 10 — Investimentos (migration_modulo_10_investimentos.sql · 12/08/2026)
-- Carteira de investimentos + histórico de movimentos. valor_atual entra no
-- patrimônio consolidado; data_vencimento alimenta a Agenda (Mód. 14).
-- ============================================================================
CREATE TABLE IF NOT EXISTS investimentos (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- IV-0001
  conta_id            INT NULL,
  nome                VARCHAR(180) NOT NULL,
  classe              ENUM('renda_fixa','tesouro','fundo','multimercado','acoes','previdencia','offshore','cripto','outro') NOT NULL DEFAULT 'renda_fixa',
  instituicao         VARCHAR(120) NULL,
  emissor             VARCHAR(120) NULL,
  indexador           ENUM('pre','cdi','ipca','selic','cambio','misto','na') NULL,
  rentabilidade_contratada VARCHAR(80) NULL,
  data_aplicacao      DATE NULL,
  data_vencimento     DATE NULL,
  valor_aplicado      DECIMAL(15,2) NULL,
  valor_atual         DECIMAL(15,2) NULL,
  quantidade          DECIMAL(18,6) NULL,
  liquidez            ENUM('D0','D1','D2','D30','D90','vencimento','outro') NULL,
  carencia_ate        DATE NULL,
  ir_aliquota         DECIMAL(5,2) NULL,
  tem_iof             TINYINT(1) NOT NULL DEFAULT 0,
  come_cotas          TINYINT(1) NOT NULL DEFAULT 0,
  taxa_administracao  DECIMAL(5,2) NULL,
  taxa_performance    VARCHAR(80)  NULL,
  status              ENUM('ativo','resgatado','vencido') NOT NULL DEFAULT 'ativo',
  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_investimentos_cliente (cliente_id),
  INDEX idx_investimentos_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Nota: ENUM tipo_referencia de `documentos` += 'investimento'; categoria +=
--   'proposta'/'regulamento' (anexos de investimento).

-- ============================================================================
-- Módulo 04 — Fornecedores e Parceiros (migration_modulo_04_fornecedores.sql · 12/08/2026)
-- Prestadores/parceiros do cliente. contrato_fim alimenta a Agenda (Mód. 14).
-- ============================================================================
CREATE TABLE IF NOT EXISTS fornecedores (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id          INT NOT NULL,
  codigo              VARCHAR(10) NOT NULL,             -- FO-0001
  tipo_pessoa         ENUM('PJ','PF') NOT NULL DEFAULT 'PJ',
  nome                VARCHAR(180) NOT NULL,
  nome_fantasia       VARCHAR(180) NULL,
  cpf_cnpj            VARCHAR(20)  NULL,
  categoria           ENUM('contabilidade','juridico','seguros','marina','saude','tecnologia','rh','imobiliaria','manutencao','construcao','financeiro','transporte','outro') NOT NULL DEFAULT 'outro',
  contato_nome        VARCHAR(140) NULL,
  telefone            VARCHAR(40)  NULL,
  email               VARCHAR(140) NULL,
  site                VARCHAR(140) NULL,
  cep                 VARCHAR(10)  NULL,
  logradouro          VARCHAR(180) NULL,
  numero              VARCHAR(20)  NULL,
  complemento         VARCHAR(120) NULL,
  bairro              VARCHAR(120) NULL,
  cidade              VARCHAR(120) NULL,
  estado              CHAR(2)      NULL,
  contrato_inicio     DATE NULL,
  contrato_fim        DATE NULL,
  contrato_valor      DECIMAL(15,2) NULL,
  contrato_reajuste   VARCHAR(120) NULL,
  forma_pagamento     VARCHAR(80)  NULL,
  banco               VARCHAR(120) NULL,
  agencia             VARCHAR(20)  NULL,
  conta               VARCHAR(30)  NULL,
  pix_tipo            ENUM('cpf','cnpj','email','telefone','aleatoria') NULL,
  pix_chave           VARCHAR(140) NULL,
  avaliacao_nota      TINYINT NULL,
  sla                 VARCHAR(180) NULL,
  avaliacao_obs       TEXT NULL,
  observacoes         TEXT NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_fornecedores_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: ENUM tipo_referencia de `documentos` += 'fornecedor'.
