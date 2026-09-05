-- ============================================================
-- IMPORTAÇÃO PARA PRODUÇÃO (Hostinger) — banco u250260449_cezar_db
-- Importar via phpMyAdmin com o banco JÁ selecionado.
-- Gerado automaticamente a partir de schema.sql + seed.sql
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- Sistema de Gestão Patrimonial — César Cordeiro
-- Schema F1 (MVP)
-- ============================================================



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
  id            INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id    INT NULL,                          -- conta de acesso (opcional)
  tipo_pessoa   ENUM('PF','PJ') NOT NULL DEFAULT 'PF',
  nome          VARCHAR(200) NOT NULL,
  cpf_cnpj      VARCHAR(18)  NOT NULL,
  email         VARCHAR(150) NULL,
  telefone      VARCHAR(20)  NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
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
  categoria        ENUM('escritura','matricula','iptu','contrato_compra','laudo','foto','boleto','nf','outro') NOT NULL DEFAULT 'outro',
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

-- ============================================================
-- Dados de teste — cliente-piloto Marcos Borges
-- ============================================================


-- Usuário César (admin) — senha: cesar123
INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES
('César Cordeiro', 'cesar@gestaopatrimonial.com.br',
 '$2y$12$bK9jpNL2tASuso9GuMBuAuSU9CCVXatns1B4l9q0OLEIF2pEcTvF2', -- cesar123
 'admin');

-- Usuário do cliente Marcos Borges — senha: marcos123
INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES
('Marcos Borges', 'marcos@road.com.br',
 '$2y$12$UJxQoZVTn65JnQtW5z03I.KxPxQpTSUUNHxPCvfkqp1ry1Ty6LR..', -- marcos123
 'cliente');

-- Cliente Marcos Borges (PF)
INSERT INTO clientes (usuario_id, tipo_pessoa, nome, cpf_cnpj, email, telefone) VALUES
(2, 'PF', 'Marcos Borges', '123.456.789-00', 'marcos@road.com.br', '(11) 99999-0001');

-- Condomínio do imóvel de exemplo
INSERT INTO condominios (nome, endereco, cep, bairro, cidade, estado) VALUES
('4D Complex', 'Av. Exemplo, 4000', '01310-100', 'Jardins', 'São Paulo', 'SP');

-- Imóvel de exemplo: "4D Complex – unid. 321"
INSERT INTO imoveis (
  cliente_id, condominio_id, codigo, nome_referencia, tipo, finalidade, situacao,
  logradouro, numero, complemento, cep, bairro, cidade, estado,
  inscricao_municipal,
  data_aquisicao, forma_aquisicao,
  valor_compra, valor_mercado, data_avaliacao_mercado,
  custo_condominio, custo_iptu_mensal
) VALUES (
  1, 1, 'IM-0001', '4D Complex – unid. 321', 'apartamento', 'residencial', 'pronto',
  'Av. Exemplo', '4000', 'Unid. 321, Torre A', '01310-100', 'Jardins', 'São Paulo', 'SP',
  'IPTU-2024-001234',
  '2020-03-15', 'compra',
  850000.00, 1200000.00, '2026-06-01',
  1200.00, 450.00
);

-- IPTU 2026
INSERT INTO iptu (imovel_id, ano, inscricao_iptu, valor_total, parcelas, data_vencimento_1, pago) VALUES
(1, 2026, 'IPTU-2024-001234', 5400.00, 10, '2026-02-10', 0);

-- Faturas de condomínio (últimos 3 meses)
INSERT INTO condominio_faturas (imovel_id, competencia, valor, pago, data_pagamento) VALUES
(1, '2026-04-01', 1150.00, 1, '2026-04-10'),
(1, '2026-05-01', 1200.00, 1, '2026-05-09'),
(1, '2026-06-01', 1350.00, 0, NULL);  -- junho em aberto

-- Reforma exemplo
INSERT INTO reformas (imovel_id, descricao, status, data_inicio, data_fim_prevista, custo_previsto, custo_realizado, fornecedor) VALUES
(1, 'Reforma da cozinha — substituição de revestimento e armários', 'em_andamento',
 '2026-05-10', '2026-07-30', 45000.00, 22000.00, 'Construtora ABC Ltda');

-- Contrato de locação (não aplicável ao 4D Complex por ser residencial do Marcos,
-- mas deixamos um exemplo comentado)
-- INSERT INTO contratos_locacao ...

-- Lançamentos financeiros de exemplo
INSERT INTO lancamentos_financeiros (imovel_id, cliente_id, tipo, categoria, descricao, valor, data_competencia, pago) VALUES
(1, 1, 'despesa', 'condominio', 'Condomínio Abril/2026',   1150.00, '2026-04-01', 1),
(1, 1, 'despesa', 'condominio', 'Condomínio Maio/2026',    1200.00, '2026-05-01', 1),
(1, 1, 'despesa', 'condominio', 'Condomínio Junho/2026',   1350.00, '2026-06-01', 0),
(1, 1, 'despesa', 'reforma',    'Reforma cozinha — 1ª parcela', 22000.00, '2026-05-15', 1);

SET FOREIGN_KEY_CHECKS=1;
