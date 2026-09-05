-- ============================================================================
-- MIGRATION — Remove colunas MORTAS da tabela `imoveis`
--
-- Levantamento de 05/09/2026 (inventário de campos por categoria):
--
-- 1) doc_status_* (6 colunas): eram gravadas pelo ImoveisController (sempre 0
--    no cadastro), mas NENHUMA tela lia esses valores. O checklist documental
--    da ficha do imóvel é calculado a partir das categorias presentes na tabela
--    `documentos` — não destas colunas. Mecanismo abandonado → removido do
--    código (método docStatus() apagado) e agora do banco.
--
-- 2) pais: coluna nunca escrita nem lida pela aplicação (o MVP é Brasil).
--    Excesso — será recriada na fase de internacionalização, se necessário.
--
-- ⚠️ ORDEM: primeiro publique o código novo (que NÃO grava mais essas colunas),
--    depois rode este SQL. Assim não há janela em que o código tente escrever
--    numa coluna já removida.
--
-- Idempotente (DROP COLUMN IF EXISTS — MariaDB/Hostinger). COMO RODAR
-- (phpMyAdmin): selecione o banco (ex.: u250260449_cezar_db), aba SQL, cole e
-- Executar. NÃO há linha USE.
-- ============================================================================

ALTER TABLE imoveis
  DROP COLUMN IF EXISTS doc_status_matricula_atualizada,
  DROP COLUMN IF EXISTS doc_status_certidao_negativa,
  DROP COLUMN IF EXISTS doc_status_habite_se,
  DROP COLUMN IF EXISTS doc_status_convencao_condominio,
  DROP COLUMN IF EXISTS doc_status_planta_aprovada,
  DROP COLUMN IF EXISTS doc_status_alvara,
  DROP COLUMN IF EXISTS pais;
