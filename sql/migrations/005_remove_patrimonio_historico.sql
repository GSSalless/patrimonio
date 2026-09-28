-- ============================================================
-- 005 · Remove a tabela de snapshot mensal do patrimônio
-- ------------------------------------------------------------
-- O gráfico de "Evolução do patrimônio sob gestão" passou a ser reconstruído
-- diretamente do histórico REAL do banco (avaliações de imóveis/bens, saldos de
-- conta e movimentos de investimento), pelos últimos 6 meses — ver
-- patrimonio_evolucao()/patrimonio_total_em_data() em includes/functions.php.
-- Com isso, a tabela de retrato mensal (introduzida na 004) ficou sem uso.
-- ============================================================
DROP TABLE IF EXISTS patrimonio_historico;
