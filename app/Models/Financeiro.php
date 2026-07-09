<?php
/**
 * Model financeiro — IPTU, lançamentos e faturas de condomínio.
 */
class Financeiro
{
    public static function criarIptu(int $imovelId, array $c): void
    {
        db()->prepare('INSERT INTO iptu (imovel_id, ano, inscricao_iptu, valor_total, parcelas, data_vencimento_1) VALUES (?,?,?,?,?,?)')
            ->execute([$imovelId, $c['ano'], $c['inscricao_iptu'], $c['valor_total'], $c['parcelas'], $c['data_vencimento_1']]);
    }

    public static function criarLancamento(int $imovelId, int $clienteId, array $c): void
    {
        db()->prepare('INSERT INTO lancamentos_financeiros (imovel_id, cliente_id, tipo, categoria, descricao, valor, data_competencia, pago, data_pagamento) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$imovelId, $clienteId, $c['tipo'], $c['categoria'], $c['descricao'], $c['valor'], $c['data_competencia'], $c['pago'], $c['data_pagamento'] ?? null]);
    }

    public static function criarFaturaCondominio(int $imovelId, array $c): void
    {
        db()->prepare('INSERT INTO condominio_faturas (imovel_id, competencia, valor, descricao_extra, pago, data_pagamento, arquivo_boleto) VALUES (?,?,?,?,?,?,?)')
            ->execute([$imovelId, $c['competencia'], $c['valor'], $c['descricao_extra'], $c['pago'], $c['data_pagamento'], $c['arquivo_boleto']]);
    }
}
