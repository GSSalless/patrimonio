-- ============================================================
-- DIAGNÓSTICO — Patrimônio por cliente (só LEITURA, não altera nada)
-- ------------------------------------------------------------
-- Rode no phpMyAdmin (banco de produção) para conferir o que cada cliente tem
-- cadastrado e entender o total que aparece na "Gestão Geral". Assim vemos se o
-- valor está baixo por falta de cadastro (cliente sem bens) ou por outro motivo.
--
-- Regras usadas (iguais às do sistema em patrimonio_consolidado):
--   imóveis        = SUM(valor_mercado)
--   veículos       = SUM(valor_mercado -> fipe -> aquisição)
--   outros bens    = SUM(valor_mercado -> aquisição)
--   contas (BRL)   = SUM(saldo_atual) só moeda BRL
--   investimentos  = SUM(valor_atual) dos ativos (status='ativo')
-- Só entram registros com ativo = 1.
-- ============================================================

SELECT
    c.id,
    c.nome,
    c.ativo,
    (SELECT COUNT(*) FROM imoveis             i WHERE i.cliente_id = c.id AND i.ativo = 1) AS qtd_imoveis,
    (SELECT COALESCE(SUM(i.valor_mercado),0)  FROM imoveis i WHERE i.cliente_id = c.id AND i.ativo = 1) AS val_imoveis,
    (SELECT COUNT(*) FROM veiculos            v WHERE v.cliente_id = c.id AND v.ativo = 1) AS qtd_veiculos,
    (SELECT COALESCE(SUM(COALESCE(v.valor_mercado, v.valor_fipe, v.valor_aquisicao, 0)),0)
       FROM veiculos v WHERE v.cliente_id = c.id AND v.ativo = 1) AS val_veiculos,
    (SELECT COUNT(*) FROM outros_bens         o WHERE o.cliente_id = c.id AND o.ativo = 1) AS qtd_outros,
    (SELECT COALESCE(SUM(COALESCE(o.valor_mercado, o.valor_aquisicao, 0)),0)
       FROM outros_bens o WHERE o.cliente_id = c.id AND o.ativo = 1) AS val_outros,
    (SELECT COUNT(*) FROM contas_financeiras  f WHERE f.cliente_id = c.id AND f.ativo = 1) AS qtd_contas,
    (SELECT COALESCE(SUM(CASE WHEN f.moeda = 'BRL' THEN f.saldo_atual ELSE 0 END),0)
       FROM contas_financeiras f WHERE f.cliente_id = c.id AND f.ativo = 1) AS saldo_contas_brl,
    (SELECT COUNT(*) FROM investimentos       n WHERE n.cliente_id = c.id AND n.ativo = 1 AND n.status = 'ativo') AS qtd_invest,
    (SELECT COALESCE(SUM(n.valor_atual),0)    FROM investimentos n WHERE n.cliente_id = c.id AND n.ativo = 1 AND n.status = 'ativo') AS val_invest
FROM clientes c
ORDER BY c.ativo DESC, c.nome;

-- Total geral consolidado (todos os clientes ATIVOS) — deve bater com o número
-- de "Patrimônio total sob gestão" na tela de Gestão Geral.
SELECT
    (SELECT COALESCE(SUM(valor_mercado),0) FROM imoveis WHERE ativo = 1)
  + (SELECT COALESCE(SUM(COALESCE(valor_mercado, valor_fipe, valor_aquisicao, 0)),0) FROM veiculos WHERE ativo = 1)
  + (SELECT COALESCE(SUM(COALESCE(valor_mercado, valor_aquisicao, 0)),0) FROM outros_bens WHERE ativo = 1)
  + (SELECT COALESCE(SUM(CASE WHEN moeda = 'BRL' THEN saldo_atual ELSE 0 END),0) FROM contas_financeiras WHERE ativo = 1)
  + (SELECT COALESCE(SUM(valor_atual),0) FROM investimentos WHERE ativo = 1 AND status = 'ativo')
    AS patrimonio_total_sob_gestao;

-- Quantos clientes ativos x inativos existem.
SELECT
    SUM(ativo = 1) AS clientes_ativos,
    SUM(ativo = 0) AS clientes_inativos,
    COUNT(*)       AS total_clientes
FROM clientes;
