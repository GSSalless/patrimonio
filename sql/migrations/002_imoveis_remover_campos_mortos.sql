-- 002 — Remove colunas mortas de `imoveis` (6x doc_status_* + pais).
-- Idempotente (DROP COLUMN IF EXISTS). Aplicada automaticamente pelo Migrator.
-- Ver INVENTARIO_CAMPOS.md para o levantamento que justificou a remoção.
ALTER TABLE imoveis
  DROP COLUMN IF EXISTS doc_status_matricula_atualizada,
  DROP COLUMN IF EXISTS doc_status_certidao_negativa,
  DROP COLUMN IF EXISTS doc_status_habite_se,
  DROP COLUMN IF EXISTS doc_status_convencao_condominio,
  DROP COLUMN IF EXISTS doc_status_planta_aprovada,
  DROP COLUMN IF EXISTS doc_status_alvara,
  DROP COLUMN IF EXISTS pais;
