-- ============================================================================
-- SEGURANÇA — Trocar as senhas seed por senhas fortes (produção)
--
-- As senhas de exemplo do seed (cesar123 / marcos123) são públicas (estão no
-- repositório) e NÃO podem continuar valendo em produção. Este arquivo troca os
-- hashes. Você gera o hash da SUA senha localmente — a senha em texto nunca
-- entra no banco nem no git.
--
-- PASSO 1 — Gere o hash da sua senha forte (no seu Mac, terminal):
--
--     php -r "echo password_hash('MINHA_SENHA_FORTE', PASSWORD_BCRYPT, ['cost'=>12]).PHP_EOL;"
--
--   Troque MINHA_SENHA_FORTE pela senha desejada (use uma senha longa e única).
--   Copie o resultado (começa com \$2y\$12\$...).
--
-- PASSO 2 — Cole o hash no lugar de <HASH_...> abaixo e rode no phpMyAdmin
--   (banco u250260449_cezar_db, aba SQL). NÃO há linha USE.
--
-- Dica: o acesso dos CLIENTES (ex.: Marcos) pode ser criado/redefinido pela
-- própria tela — Clientes → editar cliente → seção "Acesso do cliente" —
-- sem precisar de SQL. Este arquivo é útil principalmente para o admin (César).
-- ============================================================================

-- Admin (César) — troque pelo hash gerado no PASSO 1:
UPDATE usuarios
   SET senha_hash = '<HASH_DO_ADMIN>'
 WHERE email = 'cesar@gestaopatrimonial.com.br';

-- Cliente Marcos (opcional — ou use a tela "Acesso do cliente"):
-- UPDATE usuarios
--    SET senha_hash = '<HASH_DO_MARCOS>'
--  WHERE email = 'marcos@road.com.br';
