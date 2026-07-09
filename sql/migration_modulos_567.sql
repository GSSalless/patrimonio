-- ============================================================================
-- Migração — Finalização dos Módulos 05 (Imóveis), 06 (Veículos) e 07 (Outros)
-- Sub-tabelas de histórico: manutenções, abastecimentos, sinistros, avaliações.
-- Aplicar no banco `gestao_patrimonial`. Idempotente (IF NOT EXISTS).
-- ============================================================================

-- ---------------------------------------------------------------------------
-- MÓDULO 05 — Imóveis: histórico de manutenções
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- MÓDULO 06 — Veículos: abastecimentos
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- MÓDULO 06 — Veículos: manutenções (revisões, peças, pneus, baterias)
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- MÓDULO 06 — Veículos: sinistros / ocorrências
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- MÓDULO 07 — Outros Bens: manutenções (revisões de embarcação etc.)
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- MÓDULO 07 — Outros Bens: avaliações periódicas (histórico de valor)
-- ---------------------------------------------------------------------------
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
