-- ============================================================
-- 006 · Integração com o Notion (por usuário/gestor)
-- ------------------------------------------------------------
-- Cada gestor conecta o próprio Notion (OAuth). Guardamos o token e a database
-- vinculada. `notion_tarefas_map` liga cada pendência do sistema à página criada
-- no Notion (idempotência: não duplica no re-sync). Só ENVIO (sistema → Notion).
-- ============================================================
CREATE TABLE IF NOT EXISTS notion_integracoes (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id      INT NOT NULL,
  access_token    TEXT NOT NULL,
  workspace_id    VARCHAR(80)  NULL,
  workspace_name  VARCHAR(200) NULL,
  workspace_icon  VARCHAR(400) NULL,
  bot_id          VARCHAR(80)  NULL,
  database_id     VARCHAR(80)  NULL,     -- board de tarefas vinculado
  database_url    VARCHAR(400) NULL,
  ultimo_sync     DATETIME NULL,
  conectado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario (usuario_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notion_tarefas_map (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id      INT NOT NULL,
  chave           VARCHAR(64) NOT NULL,   -- hash estável da pendência (categoria|link|data|titulo)
  notion_page_id  VARCHAR(80) NOT NULL,
  atualizado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario_chave (usuario_id, chave),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
