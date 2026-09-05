-- ============================================================================
-- CORREÇÃO — Produção (Hostinger): colunas de link de mapa faltando em `imoveis`
--
-- Sintoma: a ficha do imóvel (imoveis/ficha) abria com layout quebrado / fundo
-- preto na web. Causa: a modal "Resumo" acessa $im['link_maps'] e
-- $im['link_street_view']; essas colunas foram adicionadas depois do import
-- inicial e faltavam no banco de produção → warning "chave indefinida" que o
-- ErrorHandler transforma em exceção → a página quebra no meio.
--
-- Importe UMA VEZ no phpMyAdmin (banco u250260449_cezar_db). Idempotente.
-- ============================================================================

ALTER TABLE imoveis
  ADD COLUMN IF NOT EXISTS link_maps        VARCHAR(800) NULL AFTER pais,
  ADD COLUMN IF NOT EXISTS link_street_view VARCHAR(800) NULL AFTER link_maps;
