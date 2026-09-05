-- ============================================================================
-- ATUALIZAÇÃO DO BANCO DE PRODUÇÃO (Hostinger) — Módulos 05/06/07
-- Importe este arquivo UMA VEZ no phpMyAdmin, sobre o banco u250260449_cezar_db.
-- Idempotente: pode rodar de novo sem quebrar (IF NOT EXISTS / enums completos).
-- Gerado em 09/07/2026.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1) Sub-tabelas de histórico (6 tabelas novas)
-- ---------------------------------------------------------------------------

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

-- ---------------------------------------------------------------------------
-- 2) Enums da tabela `documentos` (docs das sub-tabelas + checklist habite-se)
--    Reaplicar é seguro: apenas redefine a lista completa de valores.
-- ---------------------------------------------------------------------------
ALTER TABLE documentos MODIFY COLUMN tipo_referencia
  ENUM('imovel','reforma','contrato','contrato_locacao','pessoa','cliente','veiculo',
       'condominio','outro_bem','manutencao','veiculo_manutencao','sinistro','bem_manutencao')
  NOT NULL DEFAULT 'imovel';

ALTER TABLE documentos MODIFY COLUMN categoria
  ENUM('escritura','matricula','iptu','contrato_compra','habite_se','laudo','foto','boleto',
       'nf','crlv','apolice','manutencao','cnpj','contrato','outro')
  NOT NULL DEFAULT 'outro';
