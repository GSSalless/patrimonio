<?php
/**
 * Agenda e Alertas (Módulo 14).
 * Reúne todos os vencimentos dos módulos (IPTU, seguros, licenciamento,
 * contratos, revisões, documentos) numa única linha do tempo.
 *
 * Escopo:
 *  - admin com cliente selecionado → agenda desse cliente
 *  - admin sem cliente             → agenda geral (todos os clientes)
 *  - cliente                       → apenas a própria agenda
 */
class AgendaController extends Controller
{
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        // Determina o escopo (um cliente ou todos).
        $cli = ($usuario['nivel'] === 'admin') ? cliente_selecionado() : null;
        if ($usuario['nivel'] === 'cliente') {
            $stmt = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $stmt->execute([$usuario['id']]);
            $cli = $stmt->fetch() ?: null;
        }
        $cliente_id = $cli['id'] ?? null;

        $alertas = alertas_consolidado($cliente_id);

        // Agrupa por proximidade em baldes prontos para a view.
        $baldes = [
            'vencido' => ['titulo' => 'Vencidos',            'itens' => []],
            'semana'  => ['titulo' => 'Próximos 7 dias',     'itens' => []],
            'mes'     => ['titulo' => 'Próximos 30 dias',    'itens' => []],
            'depois'  => ['titulo' => 'Mais adiante',        'itens' => []],
        ];
        foreach ($alertas as $a) {
            $a['dias'] = dias_ate($a['data']);
            if ($a['dias'] < 0)       $baldes['vencido']['itens'][] = $a;
            elseif ($a['dias'] <= 7)  $baldes['semana']['itens'][]  = $a;
            elseif ($a['dias'] <= 30) $baldes['mes']['itens'][]     = $a;
            else                      $baldes['depois']['itens'][]  = $a;
        }

        $resumo = [
            'vencidos' => count($baldes['vencido']['itens']),
            'proximos' => count($baldes['semana']['itens']) + count($baldes['mes']['itens']),
            'total'    => count($alertas),
        ];

        $escopo_nome = $cli['nome'] ?? null; // null = agenda geral

        $this->view('agenda/index', compact('baldes', 'resumo', 'escopo_nome', 'cli'));
    }
}
