-- ============================================================
-- 004 · Histórico mensal do patrimônio (para o gráfico de evolução)
-- ------------------------------------------------------------
-- Guarda um "retrato" do patrimônio de cada cliente por competência
-- (YYYY-MM). O gráfico de Evolução na Gestão Geral/Dashboard lê daqui.
-- O sistema grava a competência corrente automaticamente (upsert) a cada
-- acesso — começa a acumular a partir de agora, com dado REAL do banco.
-- ============================================================
CREATE TABLE IF NOT EXISTS patrimonio_historico (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id    INT NOT NULL,
  competencia   CHAR(7) NOT NULL,                 -- 'YYYY-MM'
  total         DECIMAL(15,2) NOT NULL DEFAULT 0,
  imoveis       DECIMAL(15,2) NOT NULL DEFAULT 0,
  veiculos      DECIMAL(15,2) NOT NULL DEFAULT 0,
  outros        DECIMAL(15,2) NOT NULL DEFAULT 0,
  investimentos DECIMAL(15,2) NOT NULL DEFAULT 0,
  contas        DECIMAL(15,2) NOT NULL DEFAULT 0,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cliente_competencia (cliente_id, competencia),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
